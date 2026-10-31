<x-layouts::auth :title="__('Log in')">
    <x-auth-heading :title="__('เข้าสู่ระบบ')" :description="__('Welcome back. Sign in to continue your order.')" />

    <x-passkey-verify />

    <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
        @csrf

        <x-form-field name="email" type="email" :label="__('Email')" placeholder="you@example.com"
                      required autofocus autocomplete="email" />

        <x-form-field name="password" type="password" :label="__('Password')" placeholder="••••••••"
                      required autocomplete="current-password" />

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-gulu-muted">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))
                       class="size-4 rounded border-gulu-line accent-gulu-ink">
                {{ __('Remember me') }}
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="font-medium hover:underline">{{ __('Forgot password?') }}</a>
            @endif
        </div>

        <x-button type="submit" class="w-full" data-test="login-button">{{ __('เข้าสู่ระบบ') }}</x-button>
    </form>

    <p class="text-center text-sm text-gulu-muted">
        {{ __('New to gulugule shop?') }}
        <a href="{{ route('register') }}" class="font-semibold text-gulu-ink hover:underline">{{ __('สมัครสมาชิก') }}</a>
    </p>
</x-layouts::auth>
