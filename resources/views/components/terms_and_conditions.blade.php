{{--
    Terms and Conditions Component
    CMS-powered with fallback to hardcoded content
--}}

@php
    $termsSection = \App\Models\ContentSection::getByKey('terms_conditions');
@endphp

@if ($termsSection && $termsSection->is_active)
    {{-- CMS-powered Terms & Conditions --}}
    <div class="overflow-hidden">
        {{-- Alert Banner --}}
        <div class="bg-warning/10 border-l-4 border-warning p-3 md:p-4 rounded-r-lg mb-4">
            <div class="flex items-start gap-3">
                <x-ui.icon name="o-exclamation-triangle" class="w-5 h-5 text-warning shrink-0 mt-0.5" />
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm md:text-base text-base-content">Please read carefully before
                        proceeding</p>
                    <p class="text-xs md:text-sm text-base-content/70 mt-0.5">By checking the agreement box, you
                        acknowledge and accept all terms below</p>
                </div>
            </div>
        </div>

        {{-- CMS Content --}}
        <div
            class="text-sm md:text-base text-base-content overflow-hidden max-h-[28rem] overflow-y-auto pr-2 custom-scrollbar">
            <x-content-section key="terms_conditions" :show-title="false" />
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: oklch(var(--bc) / 0.2);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: oklch(var(--bc) / 0.3);
        }
    </style>
@else
    {{-- Fallback: Hardcoded Terms & Conditions --}}
    <div class="overflow-hidden">
        {{-- Alert Banner --}}
        <div class="bg-warning/10 border-l-4 border-warning p-3 md:p-4 rounded-r-lg mb-4">
            <div class="flex items-start gap-3">
                <x-ui.icon name="o-exclamation-triangle" class="w-5 h-5 text-warning shrink-0 mt-0.5" />
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm md:text-base text-base-content">Please read carefully before
                        proceeding
                    </p>
                    <p class="text-xs md:text-sm text-base-content/70 mt-0.5">By checking the agreement box, you
                        acknowledge
                        and accept all terms below</p>
                </div>
            </div>
        </div>

        {{-- Terms List with Better Spacing --}}
        <div
            class="text-sm md:text-base text-base-content overflow-hidden max-h-[28rem] overflow-y-auto pr-2 custom-scrollbar">
            {{-- Term 1 --}}
            <div class="flex gap-3 bg-base-200/50 p-3 rounded-lg hover:bg-base-200 transition-colors">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-primary text-primary-content text-sm font-bold shadow-sm">
                    1</div>
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="font-semibold text-base-content mb-1">No disruption of classes</p>
                    <p class="text-xs md:text-sm text-base-content/70 break-words leading-relaxed">In cases where
                        classes will be affected, permission to excuse students from classes shall be approved by the
                        respective Deans through the endorsement of the Chairperson.</p>
                </div>
            </div>

            {{-- Term 2 --}}
            <div class="flex gap-3 bg-base-200/50 p-3 rounded-lg hover:bg-base-200 transition-colors">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-primary text-primary-content text-sm font-bold shadow-sm">
                    2</div>
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="font-semibold text-base-content">Observe University rules and regulations</p>
                </div>
            </div>

            {{-- Term 3 --}}
            <div class="flex gap-3 bg-base-200/50 p-3 rounded-lg hover:bg-base-200 transition-colors">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-primary text-primary-content text-sm font-bold shadow-sm">
                    3</div>
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="font-semibold text-base-content mb-1">Venue availability</p>
                    <p class="text-xs md:text-sm text-base-content/70 break-words leading-relaxed">Requested venue is
                        available. However, activities/events considered as local, national and/or international that
                        may utilize similar scheduled events shall be given priority. Requesting student organizations
                        shall be compelled to request a different venue or consider rescheduling of events.</p>
                </div>
            </div>

            {{-- Term 4 - Fund Source with Nested Items --}}
            <div class="flex gap-3 bg-base-200/50 p-3 rounded-lg hover:bg-base-200 transition-colors">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-primary text-primary-content text-sm font-bold shadow-sm">
                    4</div>
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="font-semibold text-base-content mb-2">Fund Source</p>
                    <div class="space-y-2 ml-1">
                        <div class="flex gap-2 pl-3 border-l-2 border-primary/30">
                            <span
                                class="flex-shrink-0 text-xs font-bold text-primary-content bg-primary px-2 py-0.5 rounded shadow-sm">4.1</span>
                            <p
                                class="text-xs md:text-sm text-base-content/70 break-words flex-1 min-w-0 leading-relaxed">
                                If fund source is borne from organizational funds, the level of approval is until the
                                OSA Dean, provided the activity/event is within the University; otherwise, the approval
                                shall be elevated within the jurisdiction of the University President.</p>
                        </div>
                        <div class="flex gap-2 pl-3 border-l-2 border-primary/30">
                            <span
                                class="flex-shrink-0 text-xs font-bold text-primary-content bg-primary px-2 py-0.5 rounded shadow-sm">4.2</span>
                            <p
                                class="text-xs md:text-sm text-base-content/70 break-words flex-1 min-w-0 leading-relaxed">
                                If fund source is borne from the University or any government funding source, the
                                approval is automatically elevated within the jurisdiction of the University President.
                            </p>
                        </div>
                        <div class="flex gap-2 pl-3 border-l-2 border-primary/30">
                            <span
                                class="flex-shrink-0 text-xs font-bold text-primary-content bg-primary px-2 py-0.5 rounded shadow-sm">4.3</span>
                            <p
                                class="text-xs md:text-sm text-base-content/70 break-words flex-1 min-w-0 leading-relaxed">
                                A copy of the current Financial Statement is a required document that should be attached
                                with the request.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Term 5 --}}
            <div class="flex gap-3 bg-base-200/50 p-3 rounded-lg hover:bg-base-200 transition-colors">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-primary text-primary-content text-sm font-bold shadow-sm">
                    5</div>
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="text-xs md:text-sm text-base-content/70 break-words leading-relaxed">Inform the Incident
                        Command Preparedness Office of the activity/event.</p>
                </div>
            </div>

            {{-- Term 6 --}}
            <div class="flex gap-3 bg-base-200/50 p-3 rounded-lg hover:bg-base-200 transition-colors">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-primary text-primary-content text-sm font-bold shadow-sm">
                    6</div>
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="text-xs md:text-sm text-base-content/70 break-words leading-relaxed">Ensure to document
                        the activity/event to update the OSA Accomplishment Report.</p>
                </div>
            </div>

            {{-- Term 7 --}}
            <div class="flex gap-3 bg-base-200/50 p-3 rounded-lg hover:bg-base-200 transition-colors">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-primary text-primary-content text-sm font-bold shadow-sm">
                    7</div>
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="text-xs md:text-sm text-base-content/70 break-words leading-relaxed">Any change caused by
                        the requesting party shall be subjected to submit an updated form.</p>
                </div>
            </div>
        </div>

        {{-- Certification Statement --}}
        <div class="mt-4 pt-4 border-t-2 border-base-content/10">
            <div class="bg-info/10 border-l-4 border-info p-3 md:p-4 rounded-r-lg">
                <div class="flex gap-3">
                    <x-ui.icon name="o-shield-check" class="w-5 h-5 text-info flex-shrink-0 mt-1" />
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-base-content mb-2 text-sm md:text-base flex items-center gap-2">
                            Certification Statement
                        </p>
                        <p class="text-xs md:text-sm text-base-content/80 break-words leading-relaxed">
                            I hereby certify that the details provided herein are <span
                                class="font-semibold text-base-content">true and accurate</span> to the best of my
                            knowledge. The university shall exercise due diligence; thereby, the administrator and its
                            faculty member shall not be held liable for any loss, injury, or damage beyond its control,
                            including but not limited to the actions of third parties or actions of students that are
                            contrary to the Student Code of Conduct, university policies, or directives.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: oklch(var(--bc) / 0.2);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: oklch(var(--bc) / 0.3);
        }
    </style>
@endif
