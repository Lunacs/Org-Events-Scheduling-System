<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
    <x-ui.page-header title="Frequently asked questions"
        subtitle="Answers to common questions about submitting and tracking event requests. If you can't find what you need, email the Office of Student Affairs." />

    @if ($this->hasFaqs)
        <div class="space-y-10">
            @foreach ($this->faqs as $category => $faqGroup)
                <section @if ($this->faqs->count() > 1 || $category !== 'General') aria-labelledby="faq-{{ \Illuminate\Support\Str::slug($category) }}" @endif>
                    @if ($this->faqs->count() > 1 || $category !== 'General')
                        <h2 id="faq-{{ \Illuminate\Support\Str::slug($category) }}"
                            class="mb-3 text-lg font-semibold text-base-content">{{ $category }}</h2>
                    @endif

                    {{-- Native disclosure: keyboard and screen-reader friendly by default --}}
                    <div class="divide-y divide-base-300 rounded-box border border-base-300 bg-base-100">
                        @foreach ($faqGroup as $faq)
                            <details class="group" wire:key="faq-{{ $faq->id ?? $loop->index }}">
                                <summary
                                    class="flex cursor-pointer list-none items-start justify-between gap-4 px-4 py-4 text-left font-medium text-base-content hover:bg-base-200/50 sm:px-5 [&::-webkit-details-marker]:hidden">
                                    <span>{{ $faq->question }}</span>
                                    <x-ui.icon name="o-chevron-down"
                                        class="mt-0.5 size-5 shrink-0 text-base-content/60 transition-transform duration-200 group-open:rotate-180"
                                        aria-hidden="true" />
                                </summary>
                                <div class="max-w-[70ch] px-4 pb-5 text-[0.9375rem] leading-relaxed text-base-content/80 sm:px-5">
                                    {!! nl2br(e($faq->answer)) !!}
                                </div>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="rounded-box border border-base-300 bg-base-100">
            <x-ui.empty-state icon="o-document-magnifying-glass" title="Answers are being prepared"
                description="OSA is writing answers to common questions. In the meantime, email the office with any question about the system." />
        </div>
    @endif

    <section aria-labelledby="faq-contact" class="mt-12 rounded-box border border-base-300 bg-base-200 p-6 sm:p-8">
        <h2 id="faq-contact" class="text-lg font-semibold text-base-content">Still have a question?</h2>
        <p class="mt-1 text-base-content/70">Email the OSA team. Include your organization's name and your ticket number if you have one.</p>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="mailto:plv.osa.official@gmail.com" class="btn btn-primary">
                <x-ui.icon name="o-envelope" class="size-5" aria-hidden="true" />
                Email OSA
            </a>
            <a href="{{ route('about-us') }}" wire:navigate class="btn border-base-300 bg-base-100">About OSA</a>
        </div>
    </section>
</div>
