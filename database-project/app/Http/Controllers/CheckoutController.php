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
        $cartItems = Auth::user()->cartItems()
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'ไม่มีสินค้าในตะกร้า ไม่สามารถดำเนินการชำระเงินได้');
        }

        $hasStockIssues = $cartItems->contains(fn (CartItem $item) => $item->has_stock_issue);

        if ($hasStockIssues) {
            return redirect()->route('cart.index')
                ->with('error', 'มีสินค้าในตะกร้าหมดสต็อกหรือสต็อกไม่เพียงพอ กรุณาปรับปรุงรายการก่อนทำการสั่งซื้อ');
        }

        $totalAmount = CartItem::totalFor($cartItems);

        return view('checkout.index', compact('cartItems', 'totalAmount'));
    }

    /**
     * Process checkout: DB transaction + pessimistic locks (ordered, deadlock-safe, retried).
     */
    public function store(Request $request): RedirectResponse
    {
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
            // retry 3 ครั้งเมื่อเจอ deadlock
            $order = DB::transaction(function () use ($validated, $userId) {
                // 1. ล็อกตะกร้าของ user (กันกดซ้ำ: request ที่สองจะรอแล้วเจอตะกร้าว่าง)
                $cartItems = CartItem::where('uid', $userId)
                    ->orderBy('product_id')
                    ->lockForUpdate()
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw new \DomainException('empty-cart');
                }

                // 2. ล็อกสินค้าทั้งหมดในคำสั่งเดียว เรียงตาม id เสมอ (กัน deadlock)
                $products = Product::whereIn('id', $cartItems->pluck('product_id'))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $orderItemsData = [];
                $totalAmount = '0.00';

                foreach ($cartItems as $cartItem) {
                    if ($cartItem->quantity <= 0) {
                        throw new \RuntimeException('จำนวนสินค้าในตะกร้าไม่ถูกต้อง');
                    }

                    $product = $products->get($cartItem->product_id);

                    if (! $product) {
                        throw new \RuntimeException("ไม่พบข้อมูลสินค้า ID #{$cartItem->product_id} ในระบบ");
                    }

                    if ($product->stock < $cartItem->quantity) {
                        if ($product->stock <= 0) {
                            throw new \RuntimeException("ขออภัย สินค้า '{$product->name}' หมดแล้วในขณะนี้ (มีผู้สั่งซื้อสินค้าชิ้นนี้ไปก่อนหน้า)");
                        }

                        throw new \RuntimeException("ขออภัย สินค้า '{$product->name}' เหลือสต็อกเพียง {$product->stock} ชิ้น ไม่เพียงพอกับจำนวนที่ต้องการ ({$cartItem->quantity} ชิ้น)");
                    }

                    $product->decrement('stock', $cartItem->quantity);

                    // คำนวณด้วย bcmath (ไม่มี float error)
                    $unitPrice = (string) $product->price;
                    $subtotal = bcmul($unitPrice, (string) $cartItem->quantity, 2);
                    $totalAmount = bcadd($totalAmount, $subtotal, 2);

                    $orderItemsData[] = [
                        'product_id' => $product->id,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $unitPrice,
                    ];
                }

                $order = Order::create([
                    'uid' => $userId,
                    'shipping_address' => $validated['shipping_address'],
                    'delivery_date' => $validated['delivery_date'],
                    'total_amount' => $totalAmount,
                    'status' => Order::STATUS_PENDING,
                ]);

                foreach ($orderItemsData as $itemData) {
                    $order->items()->create($itemData);
                }

                CartItem::where('uid', $userId)->delete();

                return $order;
            }, 3);

            return redirect()->route('orders.show', $order->id)
                ->with('status', "สั่งซื้อสำเร็จ! คำสั่งซื้อ #{$order->id} ได้รับการบันทึกแล้ว สถานะ: รอจัดส่ง (Pending)");
        } catch (\DomainException $e) {
            // ตะกร้าว่าง: อาจเป็นเพราะเพิ่งกดสั่งซื้อไปแล้ว (double submit) จึงพาไปดูประวัติคำสั่งซื้อ
            return redirect()->route('orders.index')
                ->with('error', 'ไม่มีสินค้าในตะกร้า หากเพิ่งกดสั่งซื้อ กรุณาตรวจสอบในประวัติคำสั่งซื้อ');
        } catch (\RuntimeException $e) {
            // ข้อความที่เราตั้งเองเท่านั้นที่แสดงให้ผู้ใช้
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('cart.index')
                ->with('error', 'เกิดข้อผิดพลาดในการสั่งซื้อ กรุณาลองใหม่อีกครั้ง');
        }
    }
}