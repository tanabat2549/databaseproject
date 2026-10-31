<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock = 10, float $price = 890.0): Product
    {
        return Product::create([
            'name' => 'Red Rose Bouquet',
            'category' => 'bouquet',
            'price' => $price,
            'stock' => $stock,
        ]);
    }

    public function test_guests_cannot_access_cart_or_checkout(): void
    {
        $this->get(route('cart.index'))->assertRedirect(route('login'));
        $this->get(route('checkout.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_cart_page(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(stock: 5);

        $user->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Red Rose Bouquet')
            ->assertSee('฿1,780.00'); // 890 * 2
    }

    public function test_out_of_stock_product_cannot_be_added_to_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(stock: 0);

        $response = $this->actingAs($user)->post(route('cart.store', $product), [
            'quantity' => 1,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('cart_items', [
            'uid' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_user_cannot_add_more_than_available_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(stock: 2);

        // First add 2
        $this->actingAs($user)->post(route('cart.store', $product), [
            'quantity' => 2,
        ])->assertSessionHas('status');

        $this->assertDatabaseHas('cart_items', [
            'uid' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Attempt to add 1 more when stock is only 2
        $this->actingAs($user)->post(route('cart.store', $product), [
            'quantity' => 1,
        ])->assertSessionHas('error');

        // Quantity should still be 2
        $this->assertSame(2, $user->cartItems()->first()->quantity);
    }

    public function test_user_can_increment_and_decrement_cart_quantity(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(stock: 5);

        $cartItem = $user->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Increment
        $this->actingAs($user)->patch(route('cart.update', $cartItem), [
            'action' => 'increment',
        ])->assertSessionHas('status');

        $this->assertSame(3, $cartItem->fresh()->quantity);

        // Decrement
        $this->actingAs($user)->patch(route('cart.update', $cartItem), [
            'action' => 'decrement',
        ])->assertSessionHas('status');

        $this->assertSame(2, $cartItem->fresh()->quantity);
    }

    public function test_decrementing_quantity_at_one_removes_item(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(stock: 5);

        $cartItem = $user->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->patch(route('cart.update', $cartItem), [
            'action' => 'decrement',
        ])->assertSessionHas('status');

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_user_can_remove_cart_item(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(stock: 5);

        $cartItem = $user->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)->delete(route('cart.destroy', $cartItem))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_checkout_page_redirects_if_cart_is_empty(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');
    }

    public function test_checkout_page_redirects_if_item_has_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(stock: 1);

        $user->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => 3, // More than stock
        ]);

        $this->actingAs($user)->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');
    }

    public function test_checkout_successfully_creates_pending_order_and_deducts_stock(): void
    {
        $user = User::factory()->create();
        $product1 = $this->makeProduct(stock: 10, price: 500);
        $product2 = Product::create([
            'name' => 'Sunflower Bouquet',
            'category' => 'bouquet',
            'price' => 350,
            'stock' => 5,
        ]);

        $user->cartItems()->create(['product_id' => $product1->id, 'quantity' => 2]);
        $user->cartItems()->create(['product_id' => $product2->id, 'quantity' => 1]);

        $deliveryDate = now()->addDays(2)->format('Y-m-d');

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_address' => '99/1 Sukhumvit Rd, Bangkok 10110',
            'delivery_date' => $deliveryDate,
        ]);

        // Total: (500 * 2) + (350 * 1) = 1350
        $this->assertDatabaseHas('orders', [
            'uid' => $user->id,
            'shipping_address' => '99/1 Sukhumvit Rd, Bangkok 10110',
            'delivery_date' => $deliveryDate,
            'total_amount' => 1350.00,
            'status' => Order::STATUS_PENDING,
        ]);

        $order = Order::where('uid', $user->id)->first();
        $response->assertRedirect(route('orders.show', $order->id));

        // Check order items
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => 500.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => 350.00,
        ]);

        // Stock must be decremented
        $this->assertSame(8, $product1->fresh()->stock); // 10 - 2
        $this->assertSame(4, $product2->fresh()->stock); // 5 - 1

        // User cart must be cleared
        $this->assertDatabaseMissing('cart_items', ['uid' => $user->id]);
    }

    public function test_checkout_concurrency_rolls_back_when_product_stock_runs_out(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(stock: 1); // Only 1 left

        $user->cartItems()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Another customer purchases the last item right before checkout executes
        $product->update(['stock' => 0]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'shipping_address' => '123 Main St, Bangkok',
            'delivery_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        // Order should NOT be created
        $this->assertDatabaseMissing('orders', ['uid' => $user->id]);

        // Stock should still be 0 (not negative)
        $this->assertSame(0, $product->fresh()->stock);

        // Cart items should NOT be deleted
        $this->assertDatabaseHas('cart_items', [
            'uid' => $user->id,
            'product_id' => $product->id,
        ]);
    }
}
