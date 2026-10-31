<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of flowers with filtering, search, and sorting.
     */
    public function index(Request $request): View
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:20'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'string', 'max:20'],
        ]);

        $search = $request->query('search');
        $category = $request->query('category', 'all');
        $minPrice = $request->filled('min_price') ? (float) $request->query('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->query('max_price') : null;
        $sort = $request->query('sort', 'latest');
        $inStockOnly = $request->boolean('in_stock');

        // ถ้าผู้ใช้กรอกต่ำสุดมากกว่าสูงสุด ให้สลับค่าให้อัตโนมัติ
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $query = Product::query()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->search($search)
            ->category($category)
            ->priceRange($minPrice, $maxPrice)
            ->sorted($sort);

        if ($inStockOnly) {
            $query->inStock();
        }

        $products = $query->paginate(12)->withQueryString();

        // นับต่อหมวดด้วย query เดียว
        $counts = Product::query()
            ->selectRaw('category, count(*) as c')
            ->groupBy('category')
            ->pluck('c', 'category');

        $categoryCounts = [
            'all' => (int) $counts->sum(),
            'bouquet' => (int) ($counts['bouquet'] ?? 0),
            'single' => (int) ($counts['single'] ?? 0),
            'potted' => (int) ($counts['potted'] ?? 0),
            'wreath' => (int) ($counts['wreath'] ?? 0),
        ];

        $priceRange = [
            'min' => (float) (Product::min('price') ?? 0),
            'max' => (float) (Product::max('price') ?? 1000),
        ];

        return view('products.index', [
            'products' => $products,
            'categoryCounts' => $categoryCounts,
            'selectedCategory' => $category,
            'search' => $search,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'priceRange' => $priceRange,
            'sort' => $sort,
            'inStockOnly' => $inStockOnly,
        ]);
    }

    /**
     * Display the specified product detail page with ratings and reviews.
     */
    public function show(Product $product): View
    {
        $product->load([
            'reviews' => function ($q) {
                $q->with('user')->latest();
            },
        ]);
        $product->loadAvg('reviews', 'rating');
        $product->loadCount('reviews');

        $ratingBreakdown = $product->ratingBreakdown();

        $relatedProducts = Product::query()
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->inStock()
            ->limit(4)
            ->get();

        $canReview = false;
        $hasReviewed = false;

        if (auth()->check()) {
            $user = auth()->user();

            $hasReviewed = $product->reviews->contains('uid', $user->id);

            if (! $hasReviewed) {
                // Must have a delivered order containing this product
                $canReview = DB::table('orders')
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.uid', $user->id)
                    ->where('orders.status', 'delivered')
                    ->where('order_items.product_id', $product->id)
                    ->exists();
            }
        }

        return view('products.show', [
            'product' => $product,
            'ratingBreakdown' => $ratingBreakdown,
            'relatedProducts' => $relatedProducts,
            'canReview' => $canReview,
            'hasReviewed' => $hasReviewed,
        ]);
    }
}