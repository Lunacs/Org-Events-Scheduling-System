<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
    <x-ui.page-header title="About the Office of Student Affairs"
        subtitle="The Office of Student Affairs (OSA) coordinates and safeguards campus life at Pamantasan ng Lungsod ng Valenzuela, and this system is how that work gets done." />

    {{-- What OSA does --}}
    <section aria-labelledby="what-we-do" class="space-y-4">
        <h2 id="what-we-do" class="text-xl font-semibold text-base-content">What we do</h2>
        <p class="max-w-[70ch] leading-relaxed text-base-content/80">
            OSA oversees student organizations and the events they run &mdash; from small department gatherings to
            university-wide activities. Every event needs a venue, a schedule that doesn't clash with campus
            operations, and sign-off from the offices responsible for safety and logistics.
        </p>
        <p class="max-w-[70ch] leading-relaxed text-base-content/80">
            Before this system existed, that meant paper forms, physical signatures, and a lot of walking between
            offices. The Organization Events Scheduling System replaces that process with a single online
            workflow: organizations submit a request, OSA and GSO review it, and everyone can see where a request
            stands without asking around.
        </p>
    </section>

    {{-- The workflow is a real sequence, so the steps are numbered --}}
    <section aria-labelledby="how-it-works" class="mt-12">
        <h2 id="how-it-works" class="text-xl font-semibold text-base-content">How a request moves through the system</h2>
        <ol class="mt-6 grid gap-6 sm:grid-cols-3">
            @foreach ([
                ['title' => 'The organization submits', 'body' => 'A student organization fills in an event request with the schedule, venue, budget, and supporting documents.'],
                ['title' => 'OSA and GSO review', 'body' => 'OSA checks the request against campus policy and schedule conflicts, and forwards venue or logistics questions to GSO.'],
                ['title' => 'OSA decides', 'body' => 'Approved events appear on the shared calendar. If changes are needed, the organization revises and resubmits.'],
            ] as $i => $step)
                <li class="border-t-2 border-primary pt-4">
                    <p class="text-sm font-semibold tabular-nums text-primary">Step {{ $i + 1 }}</p>
                    <h3 class="mt-1 font-semibold text-base-content">{{ $step['title'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-base-content/70">{{ $step['body'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Developers --}}
    <section aria-labelledby="developers" class="mt-14">
        <h2 id="developers" class="text-xl font-semibold text-base-content">Meet the developers</h2>
        <p class="mt-1 text-sm text-base-content/70">The team of developers who built this system.</p>

        <ul class="mt-6 grid grid-cols-2 gap-6 sm:grid-cols-3">
            @foreach ($developers as $dev)
                <li class="flex items-center gap-3">
                    <img class="size-14 shrink-0 rounded-full object-cover ring-1 ring-base-300"
                        src="{{ asset($dev['image']) }}" alt="" width="112" height="112" loading="lazy">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-base-content">{{ $dev['name'] }}</p>
                        <p class="text-xs text-base-content/70">{{ $dev['role'] }}</p>
                        @if (! empty($dev['facebook']))
                            <a href="{{ $dev['facebook'] }}" target="_blank" rel="noopener noreferrer"
                                class="mt-0.5 inline-flex items-center gap-1 text-xs text-base-content/70 hover:text-base-content hover:underline">
                                <i class="fa-brands fa-facebook" aria-hidden="true"></i>
                                Facebook<span class="sr-only"> profile of {{ $dev['name'] }} (opens in a new tab)</span>
                            </a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Contact --}}
    <section aria-labelledby="contact" class="mt-14 rounded-box border border-base-300 bg-base-200 p-6 sm:p-8">
        <h2 id="contact" class="text-lg font-semibold text-base-content">Have a question about the system?</h2>
        <p class="mt-1 text-base-content/70">Email the OSA team or check the answers to common questions first.</p>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="mailto:plv.osa.official@gmail.com" class="btn btn-primary">
                <x-ui.icon name="o-envelope" class="size-5" aria-hidden="true" />
                Email OSA
            </a>
            <a href="{{ route('faq') }}" wire:navigate class="btn border-base-300 bg-base-100">
                Read the FAQ
            </a>
        </div>
    </section>
</div>
