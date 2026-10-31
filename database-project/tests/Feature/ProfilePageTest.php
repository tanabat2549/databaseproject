<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_profile_shows_name_email_and_join_date(): void
    {
        $user = User::factory()->create([
            'name' => 'Malee Suksan',
            'email' => 'malee@example.com',
            'created_at' => '2026-10-13 09:00:00',
        ]);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Malee Suksan')
            ->assertSee('malee@example.com')
            ->assertSee('Member since 13 Oct 2026');
    }

    public function test_profile_lists_phone_numbers_formatted(): void
    {
        $user = User::factory()->create();
        $user->phones()->create(['phone_no' => '0811111111']);
        $user->phones()->create(['phone_no' => '021234567']);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('081 111 1111')
            ->assertSee('02 123 4567');
    }

    public function test_header_user_menu_has_order_history_and_logout(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('data-test="user-menu-button"', false)
            ->assertSee(route('orders.index'), false)
            ->assertSee('data-test="logout-button"', false);
    }

    public function test_login_and_register_pages_use_the_shop_theme(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Speak in blooms.');
        $this->get(route('register'))->assertOk()->assertSee('Speak in blooms.');
    }

    public function test_register_shows_friendly_error_summary(): void
    {
        $this->followingRedirects()
            ->from(route('register'))
            ->post(route('register.store'), ['email' => 'not-an-email'])
            ->assertSee('กรุณาตรวจสอบข้อมูลอีกครั้ง');
    }
}
