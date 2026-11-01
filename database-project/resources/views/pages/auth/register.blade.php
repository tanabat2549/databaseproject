<x-layouts::auth :title="__('Register')">
    <x-auth-heading :title="__('สมัครสมาชิก')"
                    :description="__('Create your gulugule shop account to order flowers, save a wishlist and leave reviews.')" />

    <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
        @csrf

        <x-form-field name="email" type="email" :label="__('Email')" placeholder="you@example.com"
                      required autofocus autocomplete="email" />

        <x-form-field name="name" :label="__('Nickname')" :placeholder="__('How should we call you?')"
                      required autocomplete="name" />

        {{-- Optional: first row of user_phones. More numbers can be added on the Profile page. --}}
        <x-form-field name="phone" type="tel" :label="__('Phone (optional)')" placeholder="0812345678"
                      autocomplete="tel" />

        <x-form-field name="password" type="password" :label="__('Password')" placeholder="••••••••"
                      required autocomplete="new-password" />

        <x-form-field name="password_confirmation" type="password" :label="__('Confirm password')" placeholder="••••••••"
                      required autocomplete="new-password" />

        <x-button type="submit" class="w-full" data-test="register-user-button">{{ __('สมัครสมาชิก') }}</x-button>
    </form>

    <p class="text-center text-sm text-gulu-muted">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" class="font-semibold text-gulu-ink hover:underline">{{ __('เข้าสู่ระบบ') }}</a>
    </p>
</x-layouts::auth>
