@props([
    'variant' => 'default', // default, osa, gso, student-org, superadmin, viewer
    // Compact: the one-line app-shell footer. Full: the public-site footer.
    'compact' => false,
])

{{--
    x-footer — DESIGN.md "App Shell": inside the portals the footer is one quiet line;
    it never repeats the sidebar. Public pages get the fuller office/contact footer.
--}}
@php
    $links = [
        ['label' => 'FAQ', 'href' => route('faq')],
        ['label' => 'About OSA', 'href' => route('about-us')],
        ['label' => 'Data privacy', 'href' => route('data-privacy')],
    ];
@endphp

@if ($compact)
    <footer class="border-t border-base-300">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-4 text-xs text-base-content/70 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} Office of Student Affairs &middot; Pamantasan ng Lungsod ng Valenzuela</p>
            <nav aria-label="Footer" class="flex flex-wrap gap-x-4 gap-y-1">
                @foreach ($links as $link)
                    <a href="{{ $link['href'] }}" wire:navigate class="hover:text-base-content hover:underline">{{ $link['label'] }}</a>
                @endforeach
                <a href="mailto:plv.osa.official@gmail.com" class="hover:text-base-content hover:underline">Contact OSA</a>
            </nav>
        </div>
    </footer>
@else
    <footer class="border-t border-base-300 bg-base-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 py-10 md:grid-cols-[2fr_1fr_1fr]">
                <div>
                    <div class="mb-3 flex items-center gap-3">
                        <img src="{{ asset('images/optimized/osa-logo.webp') }}" alt="" class="size-10 rounded-field"
                            width="200" height="200" loading="lazy">
                        <div>
                            <p class="font-semibold text-base-content">PLV Event Scheduling</p>
                            <p class="text-sm text-base-content/70">Office of Student Affairs</p>
                        </div>
                    </div>
                    <p class="max-w-sm text-sm text-base-content/70">
                        Submit, track, and approve student organization events at Pamantasan ng Lungsod ng Valenzuela.
                    </p>
                </div>

                <div>
                    <h2 class="mb-3 text-sm font-semibold text-base-content">Information</h2>
                    <ul class="space-y-2 text-sm">
                        @foreach ($links as $link)
                            <li><a href="{{ $link['href'] }}" wire:navigate
                                    class="text-base-content/70 transition-colors hover:text-base-content">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h2 class="mb-3 text-sm font-semibold text-base-content">Contact</h2>
                    <ul class="space-y-2 text-sm text-base-content/70">
                        <li class="flex items-center gap-2">
                            <x-ui.icon name="o-building-office-2" class="size-4 shrink-0" aria-hidden="true" />
                            Office of Student Affairs, PLV
                        </li>
                        <li class="flex items-center gap-2">
                            <x-ui.icon name="o-envelope" class="size-4 shrink-0" aria-hidden="true" />
                            <a href="mailto:plv.osa.official@gmail.com" class="hover:text-base-content hover:underline">plv.osa.official@gmail.com</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-base-300 py-4 text-xs text-base-content/70">
                &copy; {{ date('Y') }} Office of Student Affairs &middot; Pamantasan ng Lungsod ng Valenzuela
            </div>
        </div>
    </footer>
@endif
