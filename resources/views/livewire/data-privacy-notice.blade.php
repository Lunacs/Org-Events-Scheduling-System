<div class="py-12 bg-base-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-ui.page-header title="Data Privacy Notice" icon="o-shield-check"
            subtitle="We value your privacy. This notice explains how the PLV Event Scheduling System collects, uses, and protects your personal data in compliance with the Data Privacy Act of 2012 (RA 10173)." />

        {{-- Content Card --}}
        <div class="space-y-8 bg-base-200/50 rounded-2xl p-6 md:p-8 shadow-sm border border-base-300">
            <section>
                <h2 class="text-2xl font-bold text-base-content mb-3">What Personal Data We Collect</h2>
                <p class="text-base-content/80 leading-relaxed mb-3">
                    When you use the system, we may collect the following categories of personal information:
                </p>
                <ul class="list-disc list-inside space-y-2 text-base-content/80">
                    <li>Identity details such as your full name, student or employee number, and organization role.</li>
                    <li>Contact information including your institutional email address and phone number.</li>
                    <li>Account credentials used to authenticate and secure your access.</li>
                    <li>Event and scheduling activity generated as you create, submit, or manage requests.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-base-content mb-3">How We Use Your Personal Data</h2>
                <p class="text-base-content/80 leading-relaxed">
                    Your personal data is processed to authenticate your account, coordinate event scheduling,
                    communicate approvals and updates, and maintain the security and integrity of the system. We only
                    process data for legitimate, clearly defined purposes tied to the operation of the platform.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-base-content mb-3">How We Protect Your Data</h2>
                <p class="text-base-content/80 leading-relaxed">
                    We apply organizational, physical, and technical security measures, including access controls and
                    encryption of sensitive information, to safeguard your personal data against unauthorized access,
                    disclosure, alteration, or loss.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-base-content mb-3">Your Rights as a Data Subject</h2>
                <p class="text-base-content/80 leading-relaxed mb-3">
                    Under the Data Privacy Act of 2012, you are entitled to the following rights:
                </p>
                <ul class="list-disc list-inside space-y-2 text-base-content/80">
                    <li>The right to be informed about how your personal data is processed.</li>
                    <li>The right to access the personal data we hold about you.</li>
                    <li>The right to correct inaccurate or outdated information.</li>
                    <li>The right to object to processing and to request erasure or blocking where applicable.</li>
                    <li>The right to lodge a complaint with the National Privacy Commission.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-base-content mb-3">Retention of Personal Data</h2>
                <p class="text-base-content/80 leading-relaxed">
                    We retain your personal data only for as long as necessary to fulfill the purposes described in this
                    notice or as required by applicable laws and institutional policies. Once your data is no longer
                    needed, it is securely disposed of or anonymized.
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-base-content mb-3">Contact Us</h2>
                <p class="text-base-content/80 leading-relaxed">
                    If you have questions about this notice or wish to exercise your data privacy rights, please reach
                    out to our Data Protection Officer at
                    <a href="mailto:plv.osa.official@gmail.com"
                        class="link link-primary">plv.osa.official@gmail.com</a>.
                </p>
            </section>
        </div>

        {{-- Back to Home Link --}}
        <div class="mt-12 text-center">
            <a href="/" wire:navigate
                class="inline-flex items-center gap-2 text-base-content/60 hover:text-primary transition-colors">
                <span>Back to Home</span>
            </a>
        </div>
    </div>
</div>
