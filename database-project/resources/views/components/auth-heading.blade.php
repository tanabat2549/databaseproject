{{--
    Top of an auth form (Figma: 02 Sign Up / 03 Sign In): back link, big Thai title, one-line description,
    flash status and a summary box when validation failed.
    <x-auth-heading :title="__('เข้าสู่ระบบ')" :description="__('Welcome back.')" />
--}}
@props([
    'title',
    'description' => null,
])

<div class="flex flex-col gap-5">
    <a href="{{ route('home') }}" class="inline-flex w-fit items-center gap-2 text-sm font-medium hover:underline">
        <flux:icon.arrow-left class="size-4" />{{ __('Back') }}
    </a>

    <div class="flex flex-col gap-3">
        <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ $title }}</h1>
        @if ($description)
            <p class="text-[15px] text-gulu-muted">{{ $description }}</p>
        @endif
    </div>

    @if (session('status'))
        <div class="rounded-2xl bg-gulu-success/10 px-5 py-3 text-sm text-gulu-success" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="flex gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700" role="alert">
            <flux:icon.exclamation-circle class="size-5 shrink-0" />
            <div>
                <p class="font-semibold">{{ __('กรุณาตรวจสอบข้อมูลอีกครั้ง') }}</p>
                <ul class="mt-1 list-disc ps-4">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
