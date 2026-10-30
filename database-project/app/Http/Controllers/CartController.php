<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Check if any product in the cart currently exceeds available stock
        $hasStockIssues = $cartItems->contains(function (CartItem $item) {
            return ! $item->product || $item->product->stock < $item->quantity;
        });

        // Calculate total amount (only for items with valid stock, or all items)
        $totalAmount = $cartItems->sum(function (CartItem $item) {
            return $item->subtotal;
        });

        return view('cart.index', compact('cartItems', 'totalAmount', 'hasStockIssues'));
    }

    /**
     * Add a product to the user's cart with out-of-stock validation.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $user = Auth::user();

        // 1. Check if product is completely out of stock
        if ($product->stock < 1) {
            return back()->with('error', "ขออภัย สินค้า '{$product->name}' หมดแล้วในขณะนี้");
        }

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $requestedQuantity = (int) ($validated['quantity'] ?? 1);

        // Find existing cart item for this user & product
        $cartItem = CartItem::firstOrNew([
            'uid' => $user->id,
            'product_id' => $product->id,
        ]);

        $currentQuantity = $cartItem->exists ? $cartItem->quantity : 0;
        $targetQuantity = $currentQuantity + $requestedQuantity;

        // 2. Prevent adding more than available in inventory
        if ($targetQuantity > $product->stock) {
            $availableToAdd = max(0, $product->stock - $currentQuantity);

            if ($availableToAdd <= 0) {
                return back()->with('error', "คุณมีสินค้า '{$product->name}' ในตะกร้าครบตามจำนวนสต็อกที่มีอยู่แล้ว ({$product->stock} ชิ้น)");
            }

            return back()->with('error', "ไม่สามารถเพิ่ม {$requestedQuantity} ชิ้นได้ เนื่องจากสินค้าเหลือสต็อกอีกเพียง {$availableToAdd} ชิ้น (คุณมีในตะกร้าแล้ว {$currentQuantity} ชิ้น)");
        }

        $cartItem->quantity = $targetQuantity;
        $cartItem->save();

        return back()->with('status', "เพิ่ม '{$product->name}' ลงในตะกร้าเรียบร้อยแล้ว ({$cartItem->quantity} ชิ้น)");
    }

    /**
     * Update the quantity of a cart item (+ / - or set quantity).
     */
    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        // Authorize that this cart item belongs to logged-in user
        if ($cartItem->uid !== Auth::id()) {
            abort(403);
        }

        $cartItem->load('product');
        $product = $cartItem->product;

        if (! $product) {
            $cartItem->delete();
            return back()->with('error', 'ไม่พบสินค้านี้ในระบบ ได้นำออกจากตะกร้าเรียบร้อยแล้ว');
        }

        $action = $request->input('action'); // 'increment' or 'decrement'

        if ($action === 'increment') {
            if ($cartItem->quantity + 1 > $product->stock) {
                return back()->with('error', "ไม่สามารถเพิ่มได้ สินค้า '{$product->name}' เหลือสต็อกเพียง {$product->stock} ชิ้น");
            }

            $cartItem->increment('quantity');

            return back()->with('status', "เพิ่มจำนวน '{$product->name}' เรียบร้อยแล้ว");
        }

        if ($action === 'decrement') {
            if ($cartItem->quantity <= 1) {
                $cartItem->delete();

                return back()->with('status', "ลบ '{$product->name}' ออกจากตะกร้าเรียบร้อยแล้ว");
            }

            $cartItem->decrement('quantity');

            return back()->with('status', "ลดจำนวน '{$product->name}' เรียบร้อยแล้ว");
        }

        // Direct quantity update
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $newQuantity = (int) $validated['quantity'];

        if ($newQuantity > $product->stock) {
            return back()->with('error', "สินค้า '{$product->name}' มีสต็อกไม่เพียงพอ (เหลือ {$product->stock} ชิ้น)");
        }

        $cartItem->update(['quantity' => $newQuantity]);

        return back()->with('status', "อัปเดตจำนวนสินค้าเรียบร้อยแล้ว");
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(CartItem $cartItem): RedirectResponse
    {
        if ($cartItem->uid !== Auth::id()) {
            abort(403);
        }

        $productName = $cartItem->product?->name ?? 'สินค้า';
        $cartItem->delete();

        return back()->with('status', "ลบ '{$productName}' ออกจากตะกร้าเรียบร้อยแล้ว");
    }
}
