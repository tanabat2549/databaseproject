<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock = 10): Product
    {
        return Product::create([
            'name' => 'Red Rose Bouquet',
            'category' => 'bouquet',
            'price' => 890,
            'stock' => $stock,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('wishlist.index'))->assertRedirect(route('login'));
    }

    public function test_wishlist_page_shows_saved_products(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->wishlist()->attach($product->id);

        $this->actingAs($user)->get(route('wishlist.index'))
            ->assertOk()
            ->assertSee('Red Rose Bouquet');
    }

    public function test_user_can_add_a_product_once(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $this->actingAs($user)->post(route('wishlist.store', $product));
        $this->actingAs($user)->post(route('wishlist.store', $product));

        $this->assertSame(1, $user->wishlist()->count());
    }

    public function test_user_can_remove_a_product(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->wishlist()->attach($product->id);

        $this->actingAs($user)->delete(route('wishlist.destroy', $product));

        $this->assertDatabaseMissing('wishlists', ['uid' => $user->id, 'product_id' => $product->id]);
    }

    public function test_move_to_cart_adds_cart_item_and_removes_from_wishlist(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->wishlist()->attach($product->id);

        $this->actingAs($user)->post(route('wishlist.move', $product))
            ->assertRedirect(route('wishlist.index'));

        $this->assertDatabaseHas('cart_items', ['uid' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);
        $this->assertDatabaseMissing('wishlists', ['uid' => $user->id, 'product_id' => $product->id]);
    }

    public function test_move_to_cart_increments_existing_cart_item(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->cartItems()->create(['product_id' => $product->id, 'quantity' => 2]);
        $user->wishlist()->attach($product->id);

        $this->actingAs($user)->post(route('wishlist.move', $product));

        $this->assertDatabaseHas('cart_items', ['uid' => $user->id, 'product_id' => $product->id, 'quantity' => 3]);
    }

    public function test_out_of_stock_product_cannot_be_moved_to_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct(stock: 0);
        $user->wishlist()->attach($product->id);

        $this->actingAs($user)->post(route('wishlist.move', $product))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('cart_items', ['uid' => $user->id]);
        $this->assertDatabaseHas('wishlists', ['uid' => $user->id, 'product_id' => $product->id]);
    }
}
