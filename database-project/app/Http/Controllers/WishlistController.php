<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WishlistController extends Controller
{
    /**
     * Show the products in the logged-in user's wishlist.
     */
    public function index(): View
    {
        $products = Auth::user()->wishlist()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return view('wishlist.index', compact('products'));
    }

    /**
     * Add a product to the wishlist (ignored if it is already there).
     */
    public function store(Product $product): RedirectResponse
    {
        Auth::user()->wishlist()->syncWithoutDetaching([$product->id]);

        return back()->with('status', "{$product->name} added to your wishlist.");
    }

    /**
     * Remove a product from the wishlist.
     */
    public function destroy(Product $product): RedirectResponse
    {
        Auth::user()->wishlist()->detach($product->id);

        return back()->with('status', "{$product->name} removed from your wishlist.");
    }

    /**
     * Move a wishlist product into the cart (+1) and remove it from the wishlist.
     * จะลบออกจาก wishlist ก็ต่อเมื่อเพิ่มลงตะกร้าสำเร็จเท่านั้น
     */
    public function moveToCart(Product $product): RedirectResponse
    {
        $user = Auth::user();

        if ($product->stock < 1) {
            return back()->with('error', "{$product->name} is out of stock.");
        }

        $item = CartItem::createOrFirst(
            ['uid' => $user->id, 'product_id' => $product->id],
            ['quantity' => 1],
        );

        if (! $item->wasRecentlyCreated) {
            $added = DB::transaction(function () use ($item, $product) {
                $locked = CartItem::whereKey($item->id)->lockForUpdate()->firstOrFail();

                if ($locked->quantity >= $product->stock) {
                    return false;
                }

                $locked->increment('quantity');

                return true;
            });

            if (! $added) {
                return back()->with('error', "{$product->name}: คุณมีสินค้านี้ในตะกร้าครบตามจำนวนสต็อกแล้ว");
            }
        }

        $user->wishlist()->detach($product->id);

        return redirect()->route('wishlist.index')->with('status', "{$product->name} moved to your cart.");
    }
}