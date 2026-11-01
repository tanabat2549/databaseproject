<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * หน้าแรก: Hero / หมวดหมู่ / Best-selling ทั้งหมดดึงจากสินค้าในฐานข้อมูล (seed)
     */
    public function index(): View
    {
        // Best-selling bouquets: เรียงจากจำนวนที่ขายได้จริง (order_items)
        // ถ้ายังไม่มียอดขาย จะเรียงต่อด้วยคะแนนรีวิว แล้วตาม id
        $bestSellers = Product::query()
            ->where('category', 'bouquet')
            ->withSum('orderItems as sold_count', 'quantity')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('sold_count')
            ->orderByDesc('reviews_avg_rating')
            ->orderBy('id')
            ->limit(4)
            ->get();

        // สินค้าแนะนำใน Hero = อันดับ 1 ของ best-selling
        $featured = $bestSellers->first();

        // การ์ดหมวดหมู่: จำนวนสินค้า + รูปปกจากสินค้าขายดีที่สุดของหมวดนั้น
        $counts = Product::query()
            ->selectRaw('category, count(*) as c')
            ->groupBy('category')
            ->pluck('c', 'category');

        $labels = [
            'bouquet' => 'Bouquets',
            'single' => 'Single stems',
            'potted' => 'Potted plants',
            'wreath' => 'Wreaths',
        ];

        $categories = [];
        foreach ($labels as $key => $label) {
            $cover = Product::query()
                ->where('category', $key)
                ->inStock()
                ->withSum('orderItems as sold_count', 'quantity')
                ->orderByDesc('sold_count')
                ->orderBy('id')
                ->first()
                ?? Product::where('category', $key)->orderBy('id')->first();

            $categories[] = [
                'key' => $key,
                'label' => $label,
                'count' => (int) ($counts[$key] ?? 0),
                'image' => $cover?->image_url,
            ];
        }

        return view('home', [
            'featured' => $featured,
            'bestSellers' => $bestSellers,
            'categories' => $categories,
            'totalProducts' => (int) $counts->sum(),
        ]);
    }
}