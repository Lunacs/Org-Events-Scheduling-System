<div class="py-12 bg-base-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-ui.page-header title="About the Office of Student Affairs" icon="o-building-office-2"
            subtitle="The Office of Student Affairs (OSA) coordinates and safeguards campus life at Pamantasan ng Lungsod ng Valenzuela, and this system is how that work gets done." />

        {{-- Mission --}}
        <div class="bg-base-200/50 rounded-2xl p-6 md:p-8 shadow-sm border border-base-300 space-y-4">
            <h2 class="text-2xl font-bold text-base-content flex items-center gap-3">
                <x-ui.icon name="o-flag" class="w-6 h-6 text-primary" />
                What We Do
            </h2>
            <p class="text-base-content/80 leading-relaxed">
                OSA oversees student organizations and the events they run &mdash; from small department gatherings to
                university-wide activities. Every event needs a venue, a schedule that doesn't clash with campus
                operations, and sign-off from the offices responsible for safety and logistics.
            </p>
            <p class="text-base-content/80 leading-relaxed">
                Before this system existed, that meant paper forms, physical signatures, and a lot of walking between
                offices. The Organization Events Scheduling System replaces that process with a single online
                workflow: organizations submit a request, OSA and GSO review it, and everyone can see where a request
                stands without asking around.
            </p>
        </div>

        {{-- How the workflow works --}}
        <div class="mt-8 bg-base-200/50 rounded-2xl p-6 md:p-8 shadow-sm border border-base-300 space-y-4">
            <h2 class="text-2xl font-bold text-base-content flex items-center gap-3">
                <x-ui.icon name="o-arrow-path" class="w-6 h-6 text-primary" />
                How a Request Moves Through the System
            </h2>
            <div class="grid gap-6 sm:grid-cols-3">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-primary text-primary-content">
                        <x-ui.icon name="o-pencil-square" class="w-5 h-5" />
                    </div>
                    <h3 class="font-semibold text-base-content">Organization submits</h3>
                    <p class="text-sm text-base-content/70">A student organization creates an event request with
                        schedule, venue, and supporting documents.</p>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-primary text-primary-content">
                        <x-ui.icon name="o-clipboard-document-check" class="w-5 h-5" />
                    </div>
                    <h3 class="font-semibold text-base-content">OSA and GSO review</h3>
                    <p class="text-sm text-base-content/70">Both offices check the request against campus policy,
                        scheduling conflicts, and venue availability.</p>
                </div>
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-accent text-accent-content">
                        <x-ui.icon name="o-check-badge" class="w-5 h-5" />
                    </div>
                    <h3 class="font-semibold text-base-content">Event is approved</h3>
                    <p class="text-sm text-base-content/70">Once cleared, the event appears on the shared calendar and
                        the organization is notified.</p>
                </div>
            </div>
        </div>

        {{-- Development Team --}}
        <div class="mt-8">
            <h2 class="text-2xl font-bold text-base-content mb-1 flex items-center gap-3">
                <x-ui.icon name="o-code-bracket" class="w-6 h-6 text-primary" />
                Meet the developers
            </h2>
            <p class="text-sm text-base-content/60 mb-6">The team of developers who built this system.</p>

            <div class="grid gap-6 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($developers as $dev)
                    <div class="group flex flex-col items-center text-center">
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden shadow-sm ring-1 ring-base-300 group-hover:ring-accent transition-all mb-3">
                            <img class="w-full h-full object-cover" src="{{ asset($dev['image']) }}"
                                alt="{{ $dev['name'] }}" loading="lazy">
                        </div>
                        <h3 class="text-sm font-semibold text-base-content">{{ $dev['name'] }}</h3>
                        <p class="text-xs text-base-content/60 mb-2">{{ $dev['role'] }}</p>
                        @if (!empty($dev['facebook']))
                            <a href="{{ $dev['facebook'] }}" target="_blank" rel="noopener noreferrer"
                                class="text-base-content/40 hover:text-primary transition-colors"
                                aria-label="{{ $dev['name'] }} on Facebook">
                                <i class="fa-brands fa-facebook text-sm"></i>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Contact CTA --}}
        <div class="mt-12 text-center">
            <div class="bg-primary text-primary-content rounded-2xl p-8">
                <h3 class="text-xl font-bold mb-3">Have a question about the system?</h3>
                <p class="opacity-90 mb-6">Reach out and the OSA team will get back to you.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="mailto:plv.osa.official@gmail.com"
                        class="btn bg-accent text-accent-content border-none hover:bg-accent/85 gap-2">
                        <x-ui.icon name="o-envelope" class="w-5 h-5" />
                        Contact OSA
                    </a>
                    <a href="{{ route('faq') }}" wire:navigate
                        class="btn btn-outline border-white/40 text-white hover:bg-white/10 hover:border-white gap-2">
                        <x-ui.icon name="o-question-mark-circle" class="w-5 h-5" />
                        View FAQ
                    </a>
                </div>
            </div>
        </div>

        {{-- Back to Home Link --}}
        <div class="mt-12 text-center">
            <a href="/" wire:navigate
                class="inline-flex items-center gap-2 text-base-content/60 hover:text-primary transition-colors">
                <x-ui.icon name="o-arrow-left" class="w-4 h-4" />
                <span>Back to Home</span>
            </a>
        </div>
    </div>
</div>
