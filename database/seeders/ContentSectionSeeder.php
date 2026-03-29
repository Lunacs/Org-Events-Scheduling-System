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
                'section_key' => 'ticket_guidelines',
                'section_type' => ContentSection::TYPE_TICKET_GUIDELINES,
                'title' => 'Ticket Submission Guidelines',
                'content' => '<div class="space-y-3">
                    <p>Please review the following guidelines before submitting your event request:</p>
                    <ul>
                        <li>Submit your event request at least <strong>2 weeks</strong> before the scheduled date.</li>
                        <li>Ensure all required information and documentary attachments are complete before submission.</li>
                        <li>Events must comply with university policies and guidelines.</li>
                        <li>The requesting organization is responsible for the conduct of the event.</li>
                        <li>Any changes or cancellations must be communicated to OSA at least 48 hours in advance.</li>
                        <li>Post-event reports must be submitted within 7 days after the event.</li>
                    </ul>
                    <p><em>For questions or assistance, please contact the OSA office.</em></p>
                </div>',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'section_key' => 'reschedule_guidelines',
                'section_type' => ContentSection::TYPE_RESCHEDULE_GUIDELINES,
                'title' => 'Reschedule Request Guidelines',
                'content' => '<div class="space-y-2">
                    <ul>
                        <li>Reschedule requests must be submitted at least <strong>2 days</strong> before the current event date</li>
                        <li>All reschedule requests are subject to approval by OSA and GSO</li>
                        <li>Venue availability will be checked for your new requested date</li>
                        <li>You will be notified via email about the status of your request</li>
                        <li>Frequent reschedule requests may affect future event approvals</li>
                    </ul>
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
