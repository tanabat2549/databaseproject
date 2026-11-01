<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Rule: user must have a DELIVERED order that contains this product
        $hasDelivered = Order::where('uid', Auth::id())
            ->where('status', Order::STATUS_DELIVERED)
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        if (! $hasDelivered) {
            return back()->with('error', 'รีวิวได้เฉพาะสินค้าที่อยู่ในออเดอร์ที่ได้รับแล้ว (delivered) เท่านั้น');
        }

        // one review per user per product (re-submitting edits the old one)
        Review::updateOrCreate(
            ['uid' => Auth::id(), 'product_id' => $product->id],
            $data
        );

        return back()->with('status', 'บันทึกรีวิวเรียบร้อยแล้ว');
    }
}
