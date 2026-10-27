<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

            </div>
        </form>

        {{-- Phone numbers (user_phones): normal forms handled by PhoneController --}}
        @php($phoneErrors = session('errors')?->get('phone_no') ?? [])

        <section class="my-10 w-full space-y-4">
            <div>
                <flux:heading>{{ __('Phone numbers') }}</flux:heading>
                <flux:subheading>{{ __('Add one or more phone numbers we can contact you on') }}</flux:subheading>
            </div>

            @if (in_array(session('status'), ['Phone number added.', 'Phone number removed.']))
                <flux:text class="font-medium !dark:text-green-400 !text-green-600">{{ session('status') }}</flux:text>
            @endif

            <ul class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                @forelse (auth()->user()->phones as $phone)
                    <li class="flex items-center justify-between px-4 py-2">
                        <span>{{ $phone->phone_no }}</span>

                        <form method="POST" action="{{ route('phones.destroy', $phone->phone_no) }}"
                              onsubmit="return confirm('{{ __('Remove this phone number?') }}')">
                            @csrf
                            @method('DELETE')
                            <flux:button type="submit" variant="danger" size="sm" data-test="delete-phone-button">
                                {{ __('Remove') }}
                            </flux:button>
                        </form>
                    </li>
                @empty
                    <li class="px-4 py-2 text-sm text-zinc-500">{{ __('No phone numbers yet.') }}</li>
                @endforelse
            </ul>

            <form method="POST" action="{{ route('phones.store') }}" class="flex items-end gap-2">
                @csrf
                <div class="flex-1">
                    <flux:input name="phone_no" :label="__('New phone number')" type="tel"
                                :value="old('phone_no')" placeholder="0812345678" required />
                </div>
                <flux:button type="submit" variant="primary" data-test="add-phone-button">{{ __('Add') }}</flux:button>
            </form>

            @foreach ($phoneErrors as $message)
                <flux:text class="text-sm !text-red-600 !dark:text-red-400">{{ $message }}</flux:text>
            @endforeach
        </section>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
