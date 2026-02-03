<?php

namespace Database\Seeders;

use App\Models\ContentSection;
use Illuminate\Database\Seeder;

class ContentSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'section_key' => 'announcements',
                'section_type' => ContentSection::TYPE_ANNOUNCEMENT,
                'title' => 'Announcements',
                'content' => '<div class="space-y-3">
                    <p><strong>Welcome to the PLV Event Scheduling System!</strong></p>
                    <p>This platform streamlines the event request and approval process for all student organizations.</p>
                    <ul>
                        <li>Submit event requests with all required documentation</li>
                        <li>Track your request status in real-time</li>
                        <li>Receive notifications on approval updates</li>
                    </ul>
                    <p>For questions or assistance, please contact the OSA office.</p>
                </div>',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'section_key' => 'terms_conditions',
                'section_type' => ContentSection::TYPE_TERMS_CONDITIONS,
                'title' => 'Terms & Conditions',
                'content' => '<div class="space-y-3">
                    <p>By submitting an event request through this system, you agree to the following terms:</p>
                    <ol>
                        <li><strong>Accuracy of Information:</strong> All information provided must be accurate and complete.</li>
                        <li><strong>Compliance:</strong> Events must comply with university policies and guidelines.</li>
                        <li><strong>Responsibility:</strong> The requesting organization is responsible for the conduct of the event.</li>
                        <li><strong>Cancellation:</strong> Any changes or cancellations must be communicated at least 48 hours in advance.</li>
                        <li><strong>Documentation:</strong> All required documentary requirements must be submitted for approval.</li>
                    </ol>
                </div>',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'section_key' => 'documentary_requirements',
                'section_type' => ContentSection::TYPE_DOCUMENTARY_REQUIREMENTS,
                'title' => 'Documentary Requirements',
                'content' => '<div class="space-y-3">
                    <p>Please prepare the following documents before submitting your event request:</p>
                    <ul>
                        <li><strong>Letter of Request</strong> - Addressed to the OSA Director</li>
                        <li><strong>Event Proposal</strong> - Detailed description of the event</li>
                        <li><strong>Budget Proposal</strong> - Itemized budget breakdown</li>
                        <li><strong>Program Flow</strong> - Timeline of activities</li>
                        <li><strong>List of Participants</strong> - Expected attendees</li>
                        <li><strong>Venue Layout</strong> - If applicable</li>
                    </ul>
                    <p><em>Note: Additional documents may be requested depending on the event type.</em></p>
                </div>',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'section_key' => 'event_guidelines',
                'section_type' => ContentSection::TYPE_PAGE_CONTENT,
                'title' => 'Event Guidelines',
                'content' => '<div class="space-y-3">
                    <p>All events must adhere to the following guidelines:</p>
                    <ul>
                        <li>Events must align with the university\'s mission and values</li>
                        <li>Safety protocols must be in place and followed</li>
                        <li>Proper venue setup and cleanup is required</li>
                        <li>All promotional materials must be approved</li>
                        <li>Post-event reports must be submitted within 7 days</li>
                    </ul>
                </div>',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'section_key' => 'faq',
                'section_type' => ContentSection::TYPE_FAQ,
                'title' => 'Frequently Asked Questions',
                'content' => '<div class="space-y-4">
                    <div>
                        <p><strong>Q: How long does the approval process take?</strong></p>
                        <p>A: The standard approval process takes 5-7 working days, depending on the complexity of the event.</p>
                    </div>
                    <div>
                        <p><strong>Q: Can I edit my request after submission?</strong></p>
                        <p>A: Yes, you can edit requests that are pending review. Once approved or rejected, changes cannot be made.</p>
                    </div>
                    <div>
                        <p><strong>Q: Who can submit event requests?</strong></p>
                        <p>A: Only registered officers of recognized student organizations can submit event requests.</p>
                    </div>
                    <div>
                        <p><strong>Q: What happens if my event is rejected?</strong></p>
                        <p>A: You will receive feedback on why the event was rejected and can resubmit with the necessary changes.</p>
                    </div>
                </div>',
                'is_active' => true,
                'display_order' => 1,
            ],
        ];

        foreach ($sections as $section) {
            ContentSection::updateOrCreate(
                ['section_key' => $section['section_key']],
                $section
            );
        }

        $this->command->info('Content sections seeded successfully!');
    }
}
