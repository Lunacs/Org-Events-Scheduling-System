@props(['title', 'subtitle' => null, 'icon' => null])

{{-- Public page header (About, FAQ, Data privacy): left-aligned title and lead paragraph. --}}
<header {{ $attributes->class(['mb-10']) }}>
    <h1 class="text-3xl font-semibold leading-tight tracking-[-0.02em] text-base-content sm:text-4xl">
        {{ $title }}
    </h1>

    @if ($subtitle)
        <p class="mt-3 max-w-[65ch] text-base leading-relaxed text-base-content/70 sm:text-lg">
            {{ $subtitle }}
        </p>
    @endif
</header>
