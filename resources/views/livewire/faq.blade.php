<div class="py-12 bg-base-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-ui.page-header title="Frequently Asked Questions" icon="o-question-mark-circle"
            subtitle="Find answers to common questions about the PLV Event Scheduling System. Can't find what you're looking for? Contact us for more help." />

        @if ($this->hasFaqs)
            {{-- FAQ Accordion Sections --}}
            <div class="space-y-8 bg-base-200/50 rounded-2xl p-6 md:p-8 shadow-sm border border-base-300">
                @foreach ($this->faqs as $category => $faqGroup)
                    <div>
                        {{-- Category Title --}}
                        @if ($this->faqs->count() > 1 || $category !== 'General')
                            <h2 class="text-2xl font-bold text-base-content mb-6 flex items-center gap-3">
                                <x-ui.icon name="o-bookmark" class="w-6 h-6 text-primary" />
                                {{ $category }}
                            </h2>
                        @endif

                        {{-- DaisyUI Accordion --}}
                        <div class="space-y-3" x-data="{ openIndex: null }">
                            @foreach ($faqGroup as $index => $faq)
                                <div class="collapse collapse-plus bg-base-100 border border-base-300 rounded-xl shadow-sm hover:shadow-md transition-shadow"
                                    :class="{
                                        'collapse-open': openIndex ===
                                            {{ $index }},
                                        'collapse-close': openIndex !== {{ $index }}
                                    }">
                                    <div class="collapse-title text-base font-semibold text-base-content pr-12 cursor-pointer"
                                        @click="openIndex = openIndex === {{ $index }} ? null : {{ $index }}">
                                        <span class="flex items-start gap-3">
                                            <x-ui.icon name="o-question-mark-circle"
                                                class="w-5 h-5 text-accent shrink-0 mt-0.5" />
                                            {{ $faq->question }}
                                        </span>
                                    </div>
                                    <div class="collapse-content">
                                        <div class="pt-2 pl-8 text-base-content/80 prose prose-sm max-w-none">
                                            {!! nl2br(e($faq->answer)) !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Contact CTA --}}
            <div class="mt-12 text-center">
                <div class="bg-primary text-primary-content rounded-2xl p-8">
                    <h3 class="text-xl font-bold mb-3">Still have questions?</h3>
                    <p class="opacity-90 mb-6">Our team is here to help you with any additional inquiries.</p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="mailto:plv.osa.official@gmail.com"
                            class="btn bg-accent text-accent-content border-none hover:bg-accent/85 gap-2">
                            <x-ui.icon name="o-envelope" class="w-5 h-5" />
                            Contact Support
                        </a>
                        <a href="{{ route('about-us') }}" wire:navigate
                            class="btn btn-outline border-white/40 text-white hover:bg-white/10 hover:border-white gap-2">
                            <x-ui.icon name="o-user-group" class="w-5 h-5" />
                            About Us
                        </a>
                    </div>
                </div>
            </div>
        @else
            {{-- Empty State: No FAQs Available --}}
            <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-base-200 mb-6">
                    <x-ui.icon name="o-document-magnifying-glass" class="w-12 h-12 text-base-content/40" />
                </div>
                <h3 class="text-2xl font-bold text-base-content mb-3">FAQs Coming Soon</h3>
                <p class="text-base-content/70 max-w-md mx-auto mb-8">
                    We're currently preparing helpful answers to common questions. Check back soon or contact us
                    directly for assistance.
                </p>
                <a href="mailto:plv.osa.official@gmail.com" class="btn btn-primary gap-2">
                    <x-ui.icon name="o-envelope" class="w-5 h-5" />
                    Contact Us
                </a>
            </div>
        @endif

        {{-- Back to Home Link --}}
        <div class="mt-12 text-center">
            <a href="/" wire:navigate
                class="inline-flex items-center gap-2 text-base-content/60 hover:text-primary transition-colors">
                <x-ui.icon name="o-arrow-left" class="w-4 h-4" />
                <span>Back to Home</span>
            </a>
        </div>
    </div>

    {{-- Accordion Transition Styles --}}
    <style>
        /* Smooth accordion transitions */
        .collapse-content {
            transition: padding 0.3s ease-out, opacity 0.25s ease-out;
            opacity: 0;
        }

        .collapse-open>.collapse-content {
            opacity: 1;
        }

        /* Smooth plus/minus icon rotation */
        .collapse-plus>.collapse-title::after {
            transition: transform 0.3s ease-out;
        }

        .collapse-open.collapse-plus>.collapse-title::after {
            transform: rotate(45deg);
        }

        /* Add subtle scale effect on open */
        .collapse {
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        .collapse-open {
            box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.1);
        }
    </style>
</div>
