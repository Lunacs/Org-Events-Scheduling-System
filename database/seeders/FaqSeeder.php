<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            // Event Submission Category
            [
                'question' => 'How long does the approval process take?',
                'answer' => 'The standard approval process takes 5-7 working days, depending on the complexity of the event. Events requiring venue approval from GSO may take additional time.',
                'category' => 'Event Submission',
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'question' => 'Can I edit my request after submission?',
                'answer' => 'Yes, you can edit requests that are pending review or when revision is requested. Once your event is approved or rejected, changes cannot be made directly. You would need to submit a new request.',
                'category' => 'Event Submission',
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'question' => 'Who can submit event requests?',
                'answer' => 'Only registered officers of recognized student organizations can submit event requests. You must be logged in with an authorized account associated with your organization.',
                'category' => 'Event Submission',
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'question' => 'What documents are required for event submission?',
                'answer' => 'Required documents typically include: Activity Proposal, Letter of Request addressed to the OSA Director, and any venue-specific requirements. Documentary requirements may vary depending on the event type.',
                'category' => 'Event Submission',
                'display_order' => 4,
                'is_active' => true,
            ],

            // Approval Process Category
            [
                'question' => 'What happens if my event is rejected?',
                'answer' => 'You will receive feedback on why the event was rejected. Depending on the reason, you may be able to address the concerns and resubmit with the necessary changes. Check the comments section for specific feedback from OSA.',
                'category' => 'Approval Process',
                'display_order' => 5,
                'is_active' => true,
            ],
            [
                'question' => 'What does "Needs Revision" status mean?',
                'answer' => 'This status indicates that OSA has reviewed your request but requires some changes or additional information before approval. Check the comments section for specific instructions on what needs to be revised.',
                'category' => 'Approval Process',
                'display_order' => 6,
                'is_active' => true,
            ],
            [
                'question' => 'Why was my event forwarded to GSO?',
                'answer' => 'Events that require specific venues managed by the General Services Office (GSO) are forwarded for venue approval. This ensures the venue is available and suitable for your event.',
                'category' => 'Approval Process',
                'display_order' => 7,
                'is_active' => true,
            ],

            // Calendar & Scheduling Category
            [
                'question' => 'How do I check venue availability?',
                'answer' => 'You can view the Event Calendar to see all approved events and their scheduled venues. This helps you choose available dates and times when submitting your event request.',
                'category' => 'Calendar & Scheduling',
                'display_order' => 8,
                'is_active' => true,
            ],
            [
                'question' => 'Can I reschedule an approved event?',
                'answer' => 'Yes, approved events can be rescheduled by submitting a reschedule request. The request will go through the approval process again to ensure the new date and venue are available.',
                'category' => 'Calendar & Scheduling',
                'display_order' => 9,
                'is_active' => true,
            ],

            // General Category
            [
                'question' => 'How do I contact OSA for support?',
                'answer' => 'You can reach the Office of Student Affairs via email at plv.osa.official@gmail.com or visit the OSA office during office hours. For urgent matters, please call the OSA hotline.',
                'category' => 'General',
                'display_order' => 10,
                'is_active' => true,
            ],
            [
                'question' => 'What happens to my event data after the event date passes?',
                'answer' => 'Completed events are archived for record-keeping purposes. You can still view your event history in the History section of your dashboard. Post-event reports should be submitted within 7 days after the event.',
                'category' => 'General',
                'display_order' => 11,
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }

        $this->command->info('FAQs seeded successfully!');
    }
}
