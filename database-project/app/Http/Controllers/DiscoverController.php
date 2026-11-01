<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSwipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DiscoverController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['category' => ['nullable', 'string', 'max:20']]);
        $category = $request->query('category', 'all');

        $cards = Product::query()
            ->inStock()
            ->whereNotIn('id', ProductSwipe::where('uid', Auth::id())->select('product_id'))
            ->category($category)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->inRandomOrder()
            ->limit(30)
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category_label,
                'price' => $p->formatted_price,
                'image' => $p->image_url,
                'description' => Str::limit((string) $p->description, 80),
                'rating' => $p->averageRating(),
                'reviews' => $p->reviewsCount(),
                'url' => route('products.show', $p),
            ])
            ->values();

        return view('discover.index', compact('cards', 'category'));
    }

    public function swipe(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'direction' => ['required', 'in:like,skip'],
        ]);

        $liked = $data['direction'] === 'like';

        ProductSwipe::updateOrCreate(
            ['uid' => Auth::id(), 'product_id' => $product->id],
            ['liked' => $liked],
        );

        if ($liked) {
            Auth::user()->wishlist()->syncWithoutDetaching([$product->id]);
        }

        return response()->json(['ok' => true]);
    }

    /** เริ่มปัดใหม่ทั้งหมด (ล้างประวัติการปัด ไม่ลบ wishlist) */
    public function reset(): RedirectResponse
    {
        ProductSwipe::where('uid', Auth::id())->delete();

        return redirect()->route('discover.index')->with('status', 'เริ่มปัดใหม่แล้ว');
    }
}