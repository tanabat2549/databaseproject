<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // Order History
    public function index()
    {
        $orders = Order::where('uid', Auth::id())
            ->withCount('items')
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    // Order Detail (+ review form for delivered orders)
    public function show(string $id)
    {
        $order = Order::where('uid', Auth::id())
            ->with('items.product')
            ->findOrFail($id);

        // existing reviews by this user for products in this order, keyed by product_id
        $reviews = Review::where('uid', Auth::id())
            ->whereIn('product_id', $order->items->pluck('product_id'))
            ->get()
            ->keyBy('product_id');

        return view('orders.show', compact('order', 'reviews'));
    }

    // "ฉันได้รับสินค้าแล้ว": pending -> delivered
    public function receive(string $id)
    {
        $updated = Order::where('uid', Auth::id())
            ->where('id', $id)
            ->where('status', Order::STATUS_PENDING)
            ->update(['status' => Order::STATUS_DELIVERED]);

        if (! $updated) {
            return back()->with('error', 'ไม่สามารถยืนยันได้ ออเดอร์นี้ไม่ได้อยู่ในสถานะรอรับสินค้า');
        }

        return redirect()->route('orders.show', $id)
            ->with('status', 'ยืนยันรับสินค้าเรียบร้อยแล้ว ขอบคุณที่ใช้บริการ');
    }
}
