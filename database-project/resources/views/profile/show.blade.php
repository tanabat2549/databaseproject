{{-- Member info page (Figma: 13 Profile). Data from ProfileController@show. --}}
@php
    // 0812345678 -> 081 234 5678, 021234567 -> 02 123 4567
    $formatPhone = fn (string $no) => strlen($no) === 10
        ? substr($no, 0, 3).' '.substr($no, 3, 3).' '.substr($no, 6)
        : substr($no, 0, 2).' '.substr($no, 2, 3).' '.substr($no, 5);

    $shortcuts = [
        ['route' => 'orders.index', 'icon' => 'cube', 'title' => __('Orders'),
         'text' => trans_choice(':count order placed · track delivery status|:count orders placed · track delivery status', $user->orders_count)],
        ['route' => 'orders.index', 'icon' => 'star', 'title' => __('Reviews'),
         'text' => trans_choice(':count review written · review from a delivered order|:count reviews written · review from a delivered order', $user->reviews_count)],
        ['route' => 'wishlist.index', 'icon' => 'heart', 'title' => __('Wishlist'),
         'text' => trans_choice(':count flower saved for later|:count flowers saved for later', $user->wishlist_count)],
    ];
@endphp

<x-layouts::shop :title="__('Profile')">
    <div class="flex flex-col gap-6">
        <x-button variant="outline" size="sm" :href="route('home')" class="w-fit">
            <flux:icon.arrow-left class="size-4" />{{ __('Back') }}
        </x-button>

        {{-- Member card --}}
        <section class="flex flex-col gap-6 rounded-3xl bg-linear-to-r from-[#cfc0d6] to-[#d8b8e0] p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8">
            <div class="flex items-center gap-5">
                <span class="flex size-20 shrink-0 items-center justify-center rounded-full bg-gulu-ink font-display text-3xl text-white">
                    {{ $user->initials() }}
                </span>
                <div class="min-w-0">
                    <h1 class="truncate font-display text-4xl font-medium text-white sm:text-5xl">{{ $user->name }}</h1>
                    <p class="truncate text-sm text-white/90">{{ $user->email }}</p>
                    <p class="mt-1 text-xs text-white/80" data-test="member-since">
                        {{ __('Member since :date', ['date' => $user->created_at->format('j M Y')]) }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <x-button variant="light" size="sm" :href="route('profile.edit')">
                    <flux:icon.pencil-square class="size-4" />{{ __('Edit profile') }}
                </x-button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-button type="submit" variant="light" size="sm" data-test="profile-logout-button">
                        <flux:icon.arrow-right-start-on-rectangle class="size-4" />{{ __('Log Out') }}
                    </x-button>
                </form>
            </div>
        </section>

        {{-- Shortcuts --}}
        <section class="grid gap-4 md:grid-cols-3">
            @foreach ($shortcuts as $card)
                <a href="{{ route($card['route']) }}"
                   class="flex flex-col gap-3 rounded-3xl border border-gulu-line p-6 transition hover:border-gulu-lilac hover:bg-gulu-mist">
                    <flux:icon :name="$card['icon']" class="size-6" />
                    <span class="font-display text-4xl">{{ $card['title'] }}</span>
                    <span class="text-sm text-gulu-muted">{{ $card['text'] }}</span>
                </a>
            @endforeach
        </section>

        {{-- Phone numbers (user_phones) --}}
        <section class="rounded-3xl bg-gulu-mist p-6 sm:p-8" aria-labelledby="phones-heading">
            <details @if ($errors->has('phone_no')) open @endif class="group">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 [&::-webkit-details-marker]:hidden">
                    <h2 id="phones-heading" class="font-display text-4xl">{{ __('Phone numbers') }}</h2>
                    <span class="inline-flex items-center gap-2 rounded-full border border-gulu-ink px-4 py-2 text-sm font-medium transition hover:bg-gulu-ink hover:text-white"
                          data-test="add-phone-toggle">
                        <flux:icon.plus class="size-4 group-open:hidden" />
                        <flux:icon.x-mark class="hidden size-4 group-open:block" />
                        <span class="group-open:hidden">{{ __('add number') }}</span>
                        <span class="hidden group-open:inline">{{ __('cancel') }}</span>
                    </span>
                </summary>

                <form method="POST" action="{{ route('phones.store') }}"
                      class="mt-5 flex flex-col gap-3 rounded-2xl bg-white p-5 sm:flex-row sm:items-start">
                    @csrf
                    <div class="flex-1">
                        <x-form-field name="phone_no" type="tel" :label="__('New phone number')" placeholder="0812345678"
                                      required inputmode="numeric" maxlength="10" />
                    </div>
                    <x-button type="submit" class="sm:mt-7" data-test="add-phone-button">{{ __('Save number') }}</x-button>
                </form>
            </details>

            <ul class="mt-5 flex flex-col gap-3">
                @forelse ($phones as $phone)
                    <li class="flex items-center justify-between gap-4 rounded-2xl bg-white px-5 py-4">
                        <span class="text-base font-medium tracking-wide">{{ $formatPhone($phone->phone_no) }}</span>

                        <form method="POST" action="{{ route('phones.destroy', $phone->phone_no) }}"
                              onsubmit="return confirm('{{ __('Remove this phone number?') }}')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="outline" size="sm" data-test="delete-phone-button">
                                <flux:icon.trash class="size-4" />{{ __('remove') }}
                            </x-button>
                        </form>
                    </li>
                @empty
                    <li class="rounded-2xl border border-dashed border-gulu-line bg-white px-5 py-6 text-center text-sm text-gulu-muted">
                        {{ __('No phone numbers yet. Add one so the florist can reach you about your delivery.') }}
                    </li>
                @endforelse
            </ul>
        </section>
    </div>
</x-layouts::shop>
