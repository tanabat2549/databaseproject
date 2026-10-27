<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of flowers with filtering, search, and sorting.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $category = $request->query('category', 'all');
        $minPrice = $request->filled('min_price') ? (float) $request->query('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->query('max_price') : null;
        $sort = $request->query('sort', 'latest');
        $inStockOnly = $request->boolean('in_stock');

        // Base query with average ratings and review counts
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

        // Calculate count per category for filter pills
        $categoryCounts = [
            'all' => Product::count(),
            'bouquet' => Product::where('category', 'bouquet')->count(),
            'single' => Product::where('category', 'single')->count(),
            'potted' => Product::where('category', 'potted')->count(),
            'wreath' => Product::where('category', 'wreath')->count(),
        ];

        // Overall price boundary in catalog
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
        // Load reviews with user info, sorted newest first
        $product->load([
            'reviews' => function ($q) {
                $q->with('user')->latest();
            },
        ]);
        $product->loadAvg('reviews', 'rating');
        $product->loadCount('reviews');

        // Rating breakdown for star distribution bars
        $ratingBreakdown = $product->ratingBreakdown();

        // Related flowers from the same category
        $relatedProducts = Product::query()
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->inStock()
            ->limit(4)
            ->get();

        // Determine if current authenticated user is eligible to review
        $canReview = false;
        $hasReviewed = false;

        if (auth()->check()) {
            $user = auth()->user();

            $hasReviewed = $product->reviews->contains('uid', $user->id);

            if (!$hasReviewed) {
                // Must have a delivered order containing this product
                $canReview = \Illuminate\Support\Facades\DB::table('orders')
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
