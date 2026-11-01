{{-- Logged-in user dropdown for the storefront header: avatar + name, links, log out. Used by site-header. --}}
@php($user = auth()->user())

<details class="relative" data-user-menu>
    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full py-1 pe-2 ps-1 transition hover:bg-gulu-mist [&::-webkit-details-marker]:hidden"
             data-test="user-menu-button">
        <span class="flex size-8 items-center justify-center rounded-full bg-gulu-ink text-xs font-semibold text-white">{{ $user->initials() }}</span>
        <span class="hidden max-w-32 truncate font-medium xl:inline">{{ $user->name }}</span>
        <flux:icon.chevron-down class="size-4" />
    </summary>

    <div class="absolute end-0 z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-gulu-line bg-white shadow-lg">
        <div class="flex items-center gap-3 bg-gulu-mist px-4 py-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gulu-ink text-sm font-semibold text-white">{{ $user->initials() }}</span>
            <div class="min-w-0">
                <p class="truncate font-semibold">{{ $user->name }}</p>
                <p class="truncate text-xs text-gulu-muted">{{ $user->email }}</p>
            </div>
        </div>

        <nav class="flex flex-col py-2 text-sm" aria-label="{{ __('Account') }}">
            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gulu-mist">
                <flux:icon.user class="size-4" />{{ __('My profile') }}
            </a>
            <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gulu-mist">
                <flux:icon.archive-box class="size-4" />{{ __('Order history') }}
            </a>
            <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gulu-mist">
                <flux:icon.heart class="size-4" />{{ __('Wishlist') }}
            </a>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gulu-mist">
                <flux:icon.cog-6-tooth class="size-4" />{{ __('Settings') }}
            </a>
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-gulu-line">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 text-sm font-medium text-red-700 hover:bg-red-50"
                    data-test="logout-button">
                <flux:icon.arrow-right-start-on-rectangle class="size-4" />{{ __('Log out') }}
            </button>
        </form>
    </div>
</details>

<script>
    // Close the user menu when clicking anywhere outside it.
    document.addEventListener('click', (event) => {
        document.querySelectorAll('details[data-user-menu][open]').forEach((menu) => {
            if (! menu.contains(event.target)) menu.removeAttribute('open');
        });
    });
</script>
