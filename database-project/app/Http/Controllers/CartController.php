<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Display the authenticated user's shopping cart.
     */
    public function index(): View
    {
        $cartItems = Auth::user()->cartItems()
            ->with('product')
            ->orderBy('id', 'desc')
            ->get();

        $hasStockIssues = $cartItems->contains(fn (CartItem $item) => $item->has_stock_issue);

        // string แบบ bcmath เช่น "1780.00" (view ใช้ number_format ได้ตามเดิม)
        $totalAmount = CartItem::totalFor($cartItems);

        return view('cart.index', compact('cartItems', 'totalAmount', 'hasStockIssues'));
    }

    /**
     * Add a product to the cart (race-safe: unique index + row lock).
     * ต้องมี unique index (uid, product_id) ใน cart_items (ดู migration)
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $requested = (int) ($validated['quantity'] ?? 1);
        $uid = Auth::id();

        if ($product->stock < 1) {
            return back()->with('error', "ขออภัย สินค้า '{$product->name}' หมดแล้วในขณะนี้");
        }

        if ($requested > $product->stock) {
            return back()->with('error', "สินค้า '{$product->name}' เหลือสต็อกเพียง {$product->stock} ชิ้น");
        }

        // ถ้ากดพร้อมกัน: อีก request จะชน unique แล้วได้แถวที่มีอยู่แล้วกลับมา (ไม่เกิด Duplicate Key Error)
        $item = CartItem::createOrFirst(
            ['uid' => $uid, 'product_id' => $product->id],
            ['quantity' => $requested],
        );

        if (! $item->wasRecentlyCreated) {
            try {
                $item = DB::transaction(function () use ($item, $product, $requested) {
                    $locked = CartItem::whereKey($item->id)->lockForUpdate()->firstOrFail();
                    $target = $locked->quantity + $requested;

                    if ($target > $product->stock) {
                        $available = max(0, $product->stock - $locked->quantity);

                        throw new \DomainException(
                            $available <= 0
                                ? "คุณมีสินค้า '{$product->name}' ในตะกร้าครบตามจำนวนสต็อกที่มีอยู่แล้ว ({$product->stock} ชิ้น)"
                                : "ไม่สามารถเพิ่ม {$requested} ชิ้นได้ เนื่องจากสินค้าเหลือสต็อกอีกเพียง {$available} ชิ้น (คุณมีในตะกร้าแล้ว {$locked->quantity} ชิ้น)"
                        );
                    }

                    $locked->update(['quantity' => $target]);

                    return $locked;
                });
            } catch (\DomainException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        return back()->with('status', "เพิ่ม '{$product->name}' ลงในตะกร้าเรียบร้อยแล้ว ({$item->quantity} ชิ้น)");
    }

    /**
     * Update the quantity of a cart item (+ / - or set quantity), race-safe.
     */
    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        if ((int) $cartItem->uid !== (int) Auth::id()) {
            abort(403);
        }

        $request->validate([
            'action' => ['nullable', Rule::in(['increment', 'decrement'])],
        ]);

        $action = $request->input('action');
        $newQuantity = null;

        if (! $action) {
            $newQuantity = (int) $request->validate([
                'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            ])['quantity'];
        }

        [$type, $message] = DB::transaction(function () use ($cartItem, $action, $newQuantity) {
            // ล็อกแถวตะกร้าก่อน แล้วค่อยอ่านจำนวนล่าสุด (ลำดับเดียวกับ checkout: cart -> product)
            $locked = CartItem::whereKey($cartItem->id)
                ->where('uid', Auth::id())
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                return ['error', 'ไม่พบสินค้านี้ในตะกร้าแล้ว'];
            }

            $product = Product::find($locked->product_id);

            if (! $product) {
                $locked->delete();

                return ['error', 'ไม่พบสินค้านี้ในระบบ ได้นำออกจากตะกร้าเรียบร้อยแล้ว'];
            }

            if ($action === 'increment') {
                if ($locked->quantity + 1 > $product->stock) {
                    return ['error', "ไม่สามารถเพิ่มได้ สินค้า '{$product->name}' เหลือสต็อกเพียง {$product->stock} ชิ้น"];
                }

                $locked->increment('quantity');

                return ['status', "เพิ่มจำนวน '{$product->name}' เรียบร้อยแล้ว"];
            }

            if ($action === 'decrement') {
                if ($locked->quantity <= 1) {
                    $locked->delete();

                    return ['status', "ลบ '{$product->name}' ออกจากตะกร้าเรียบร้อยแล้ว"];
                }

                $locked->decrement('quantity');

                return ['status', "ลดจำนวน '{$product->name}' เรียบร้อยแล้ว"];
            }

            // Direct quantity update
            if ($newQuantity > $product->stock) {
                return ['error', "สินค้า '{$product->name}' มีสต็อกไม่เพียงพอ (เหลือ {$product->stock} ชิ้น)"];
            }

            $locked->update(['quantity' => $newQuantity]);

            return ['status', 'อัปเดตจำนวนสินค้าเรียบร้อยแล้ว'];
        });

        return back()->with($type, $message);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(CartItem $cartItem): RedirectResponse
    {
        if ((int) $cartItem->uid !== (int) Auth::id()) {
            abort(403);
        }

        $productName = $cartItem->product?->name ?? 'สินค้า';
        $cartItem->delete();

        return back()->with('status', "ลบ '{$productName}' ออกจากตะกร้าเรียบร้อยแล้ว");
    }
}