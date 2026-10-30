<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Display the checkout page with order summary, shipping address, and delivery date.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $cartItems = $user->cartItems()
            ->with('product')
            ->get();

        // 1. Guard against empty cart
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'ไม่มีสินค้าในตะกร้า ไม่สามารถดำเนินการชำระเงินได้');
        }

        // 2. Guard against out-of-stock items currently in cart
        $hasStockIssues = $cartItems->contains(function (CartItem $item) {
            return ! $item->product || $item->product->stock < $item->quantity;
        });

        if ($hasStockIssues) {
            return redirect()->route('cart.index')
                ->with('error', 'มีสินค้าในตะกร้าหมดสต็อกหรือสต็อกไม่เพียงพอ กรุณาปรับปรุงรายการก่อนทำการสั่งซื้อ');
        }

        $totalAmount = $cartItems->sum(function (CartItem $item) {
            return $item->subtotal;
        });

        return view('checkout.index', compact('cartItems', 'totalAmount'));
    }

    /**
     * Process checkout with Concurrency Protection (Pessimistic Locking & DB Transaction).
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validate Form Inputs
        $validated = $request->validate([
            'shipping_address' => ['required', 'string', 'min:5', 'max:500'],
            'delivery_date' => ['required', 'date', 'after_or_equal:today'],
        ], [
            'shipping_address.required' => 'กรุณากรอกที่อยู่สำหรับจัดส่งดอกไม้',
            'shipping_address.min' => 'ที่อยู่จัดส่งต้องมีความยาวอย่างน้อย 5 ตัวอักษร',
            'delivery_date.required' => 'กรุณาเลือกวันที่ต้องการรับดอกไม้',
            'delivery_date.after_or_equal' => 'วันที่รับดอกไม้ต้องเป็นวันนี้หรือวันข้างหน้าเท่านั้น',
        ]);

        $userId = Auth::id();

        try {
            // 2. Wrap all database operations in a Transaction
            $order = DB::transaction(function () use ($validated, $userId) {
                // Fetch user's cart items
                $cartItems = CartItem::where('uid', $userId)->get();

                if ($cartItems->isEmpty()) {
                    throw new \RuntimeException('ไม่มีสินค้าในตะกร้า ไม่สามารถสั่งซื้อได้');
                }

                $orderItemsData = [];
                $totalAmount = 0.0;

                // 3. Concurrency Protection: Lock rows in database (SELECT ... FOR UPDATE)
                foreach ($cartItems as $cartItem) {
                    $product = Product::where('id', $cartItem->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $product) {
                        throw new \RuntimeException("ไม่พบข้อมูลสินค้า ID #{$cartItem->product_id} ในระบบ");
                    }

                    // Strict stock check on locked product
                    if ($product->stock < $cartItem->quantity) {
                        if ($product->stock <= 0) {
                            throw new \RuntimeException("ขออภัย สินค้า '{$product->name}' หมดแล้วในขณะนี้ (มีผู้สั่งซื้อสินค้าชิ้นนี้ไปก่อนหน้า)");
                        } else {
                            throw new \RuntimeException("ขออภัย สินค้า '{$product->name}' เหลือสต็อกเพียง {$product->stock} ชิ้น ไม่เพียงพอกับจำนวนที่ต้องการ ({$cartItem->quantity} ชิ้น)");
                        }
                    }

                    // 4. Deduct stock from products table
                    $product->decrement('stock', $cartItem->quantity);

                    $unitPrice = (float) $product->price;
                    $subtotal = $unitPrice * $cartItem->quantity;
                    $totalAmount += $subtotal;

                    $orderItemsData[] = [
                        'product_id' => $product->id,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $unitPrice,
                    ];
                }

                // 5. Create Order with initial status: 'pending'
                $order = Order::create([
                    'uid' => $userId,
                    'shipping_address' => $validated['shipping_address'],
                    'delivery_date' => $validated['delivery_date'],
                    'total_amount' => $totalAmount,
                    'status' => Order::STATUS_PENDING,
                ]);

                // 6. Create OrderItems (freezing unit_price)
                foreach ($orderItemsData as $itemData) {
                    $order->items()->create($itemData);
                }

                // 7. Clear user's cart items
                CartItem::where('uid', $userId)->delete();

                return $order;
            });

            // 8. Redirect to Person 4's order details view
            return redirect()->route('orders.show', $order->id)
                ->with('status', "สั่งซื้อสำเร็จ! คำสั่งซื้อ #{$order->id} ได้รับการบันทึกแล้ว สถานะ: รอจัดส่ง (Pending)");

        } catch (\Throwable $e) {
            // Any failure rolls back the transaction automatically
            return redirect()->route('cart.index')
                ->with('error', $e->getMessage());
        }
    }
}
