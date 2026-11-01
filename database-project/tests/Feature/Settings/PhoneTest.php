<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_add_phone_numbers(): void
    {
        $this->post(route('phones.store'), ['phone_no' => '0812345678'])
            ->assertRedirect(route('login'));
    }

    public function test_user_can_add_multiple_phone_numbers(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('phones.store'), ['phone_no' => '0812345678'])
            ->assertRedirect(route('profile.show'));
        $this->actingAs($user)->post(route('phones.store'), ['phone_no' => '0898765432']);

        $this->assertSame(2, $user->phones()->count());
    }

    public function test_user_cannot_add_the_same_phone_twice(): void
    {
        $user = User::factory()->create();
        $user->phones()->create(['phone_no' => '0812345678']);

        $this->actingAs($user)->post(route('phones.store'), ['phone_no' => '0812345678'])
            ->assertSessionHasErrors('phone_no');
    }

    public function test_two_users_can_share_a_phone_number(): void
    {
        User::factory()->create()->phones()->create(['phone_no' => '0812345678']);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('phones.store'), ['phone_no' => '0812345678'])
            ->assertSessionHasNoErrors();
    }

    public function test_invalid_phone_format_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('phones.store'), ['phone_no' => 'abc'])
            ->assertSessionHasErrors('phone_no');
    }

    public function test_user_can_only_delete_their_own_phone(): void
    {
        $owner = User::factory()->create();
        $owner->phones()->create(['phone_no' => '0812345678']);
        $other = User::factory()->create();

        $this->actingAs($other)->delete(route('phones.destroy', '0812345678'));
        $this->assertSame(1, $owner->phones()->count());

        $this->actingAs($owner)->delete(route('phones.destroy', '0812345678'))
            ->assertRedirect(route('profile.show'));
        $this->assertSame(0, $owner->phones()->count());
    }

    public function test_profile_page_lists_phone_numbers(): void
    {
        $user = User::factory()->create();
        $user->phones()->create(['phone_no' => '0812345678']);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('0812345678');
    }
}
