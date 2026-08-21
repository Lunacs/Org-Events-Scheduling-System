@props(['title', 'subtitle' => null, 'icon' => null])

<div class="text-center mb-12">
    @if ($icon)
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary text-primary-content mb-6">
            <x-ui.icon :name="$icon" class="w-8 h-8" />
        </div>
    @endif

    <h1 class="text-4xl font-extrabold font-heading text-base-content sm:text-5xl mb-4">
        {{ $title }}
    </h1>

    @if ($subtitle)
        <p class="mt-4 max-w-2xl text-lg text-base-content/70 mx-auto leading-relaxed">
            {{ $subtitle }}
        </p>
    @endif

    <div class="mt-8 flex justify-center">
        <div class="h-1 w-16 rounded-full bg-accent"></div>
    </div>
</div>
