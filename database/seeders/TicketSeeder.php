<?php

namespace Database\Seeders;

use App\Http\Middleware\AutoCompleteTickets;
use App\Models\Event_Type;
use App\Models\Fund_Sources;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Venue;
use App\Services\Cache\CalendarCacheService;
use App\Services\Cache\DashboardCacheService;
use App\Services\Cache\EventCacheService;
use Database\Seeders\Support\TicketWorkflow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Seeds tickets by replaying the real approval workflow, so every ticket comes
 * with the approvals, history, events, schedules, comments, attachments,
 * transaction logs and notifications the app itself would have written.
 *
 * Steps are given in days ago and run on one shared timeline, so ticket ids,
 * ticket numbers and timestamps follow the order things happened in.
 */
class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Ticket::withTrashed()->exists()) {
            $this->command?->warn('Tickets already exist. Skipping ticket seeder.');

            return;
        }

        $osa = User::query()->where('email', 'osa@plv.edu.ph')->first();
        $gso = User::query()->where('email', 'gso@plv.edu.ph')->first();

        if (! $osa || ! $gso || Event_Type::query()->doesntExist() || Venue::query()->doesntExist()) {
            $this->command?->warn('No OSA/GSO users, event types or venues found. Skipping ticket seeder.');

            return;
        }

        DB::disableQueryLog();

        $workflow = new TicketWorkflow;
        $scenarios = $this->scenarios();
        $tickets = [];

        try {
            foreach ($this->timeline($scenarios) as [$daysAgo, $key, $step]) {
                $scenario = $scenarios[$key];
                $at = now()->subMinutes((int) round($daysAgo * 24 * 60));
                $owner = User::query()->where('email', $scenario['owner'])->firstOrFail();

                match ($step[0]) {
                    'submit' => $tickets[$key] = $workflow->submit($owner, $this->ticketAttributes($scenario), $at),
                    'resubmit' => $workflow->resubmit($tickets[$key], $owner, $this->ticketAttributes($scenario, $step[2]), $at),
                    'forward' => $workflow->forward($tickets[$key], $osa, $step[2], $at),
                    'gso' => $workflow->gsoDecide($tickets[$key], $gso, $step[2], $step[3], $at),
                    'revise' => $workflow->requestRevision($tickets[$key], $osa, $step[2], $at),
                    'approve' => $workflow->approve($tickets[$key], $osa, $step[2], $at),
                    'comment' => $workflow->comment($tickets[$key], match ($step[2]) {
                        'osa' => $osa,
                        'gso' => $gso,
                        default => $owner,
                    }, $step[3], $at),
                };
            }
        } finally {
            Carbon::setTestNow();
            Auth::forgetUser();
        }

        // Past approved events are completed by the same job the daily schedule runs.
        app(AutoCompleteTickets::class)->autoCompleteTickets();

        // Notifications older than two days have been read.
        DB::table('notifications')
            ->whereNull('read_at')
            ->where('created_at', '<', now()->subDays(2))
            ->update(['read_at' => DB::raw('DATE_ADD(created_at, INTERVAL 2 HOUR)')]);

        DashboardCacheService::clearAllDashboards();
        CalendarCacheService::clearAllCalendar();
        EventCacheService::clearAllEventRelatedCaches();

        $this->command?->info('Created '.Ticket::count().' tickets across every workflow status.');
    }

    /**
     * Every step of every scenario, oldest first.
     *
     * @param  array<string, array<string, mixed>>  $scenarios
     * @return list<array{0: float|int, 1: string, 2: list<mixed>}>
     */
    private function timeline(array $scenarios): array
    {
        $timeline = [];

        foreach ($scenarios as $key => $scenario) {
            foreach ($scenario['steps'] as $step) {
                $timeline[] = [$step[1], $key, $step];
            }
        }

        usort($timeline, static fn (array $a, array $b): int => $b[0] <=> $a[0]);

        return $timeline;
    }

    /**
     * Ticket columns for a scenario, as the submit form stores them.
     *
     * @param  array<string, mixed>  $scenario
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function ticketAttributes(array $scenario, array $overrides = []): array
    {
        $scenario = [...$scenario, ...$overrides];
        $dateFrom = now()->addDays($scenario['starts_in']);
        [$plvParticipants, $externalParticipants] = $scenario['participants'];
        [$timeFrom, $timeTo] = $scenario['time'];

        return [
            'event_type_id' => Event_Type::query()->where('type_name', $scenario['type'])->value('event_type_id'),
            'title' => $scenario['title'],
            'description' => $scenario['description'],
            'adviser_contact' => $scenario['adviser_contact'],
            'plv_participants' => $plvParticipants,
            'external_participants' => $externalParticipants ?: null,
            'total_participants' => $plvParticipants + $externalParticipants,
            'venue_requested' => isset($scenario['venue'])
                ? Venue::query()->where('venue_name', $scenario['venue'])->value('venue_id')
                : null,
            'venue_other' => $scenario['venue_other'] ?? null,
            'special_requirements' => $scenario['special_requirements'] ?? null,
            'igp_requested' => isset($scenario['igp_details']),
            'igp_details' => $scenario['igp_details'] ?? null,
            ...($scenario['off_campus'] ?? []),
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateFrom->copy()->addDays(($scenario['days'] ?? 1) - 1)->format('Y-m-d'),
            'time_from' => $timeFrom,
            'time_to' => $timeTo,
            'estimated_budget' => $scenario['budget'],
            'budget_breakdown' => $scenario['budget_breakdown'] ?? null,
            'additional_notes' => $scenario['additional_notes'] ?? null,
            'fund_source_id' => Fund_Sources::query()->where('source_name', $scenario['fund'])->value('source_id'),
        ];
    }

    /**
     * One scenario per ticket, covering every status the workflow can leave a ticket in.
     *
     * Steps: [action, days ago, ...arguments]. Event dates are in days from today.
     *
     * @return array<string, array<string, mixed>>
     */
    private function scenarios(): array
    {
        return [
            // received: waiting for OSA
            'leadership-training' => [
                'owner' => 'student@plv.edu.ph',
                'title' => 'Leadership Training Workshop',
                'description' => 'Interactive workshop on leadership skills, team building, and organizational management for student leaders.',
                'type' => 'Training, Rehearsals, Practices',
                'venue' => 'PLV Conference Hall',
                'starts_in' => 12,
                'time' => ['08:00', '17:00'],
                'participants' => [80, 0],
                'budget' => 12000,
                'budget_breakdown' => 'Speaker honorarium: 5,000; Training kits: 4,500; Snacks: 2,500',
                'fund' => 'Organizational Funds',
                'adviser_contact' => '09182345601',
                'steps' => [
                    ['submit', 1.2],
                ],
            ],
            'community-outreach' => [
                'owner' => 'john.martinez@plv.edu.ph',
                'title' => 'Community Outreach Program',
                'description' => 'Outreach program providing learning materials and tutoring sessions to children in a partner barangay.',
                'type' => 'Off-Campus Activities',
                'venue_other' => 'Barangay Malinta Covered Court, Valenzuela City',
                'starts_in' => 3,
                'time' => ['08:00', '15:00'],
                'participants' => [40, 60],
                'budget' => 18000,
                'budget_breakdown' => 'School supplies: 10,000; Transportation: 5,000; Meals: 3,000',
                'fund' => 'Organizational Funds',
                'adviser_contact' => '09182345602',
                'off_campus' => [
                    'oc_tsp' => 'outsourced',
                    'oc_transportation_type' => 'Coaster',
                    'oc_driver_name' => 'Ramon Santos',
                    'oc_vehicle_plate_number' => 'NAB 4521',
                    'oc_driver_contact_number' => '09181112233',
                ],
                'steps' => [
                    ['submit', 0.6],
                ],
            ],

            // gso_review: forwarded, GSO approvals pending at each priority level
            'environmental-campaign' => [
                'owner' => 'lily.garcia@plv.edu.ph',
                'title' => 'Environmental Awareness Campaign',
                'description' => 'Campus-wide campaign promoting sustainability, waste segregation, and green initiatives.',
                'type' => 'General Assemblies and Similar Activities',
                'venue' => 'PLV Open Field - Main',
                'starts_in' => 2,
                'time' => ['07:00', '12:00'],
                'participants' => [250, 0],
                'budget' => 8000,
                'fund' => 'University/Appropriate Government Funds',
                'adviser_contact' => '09182345603',
                'special_requirements' => 'Two tents and a portable sound system.',
                'steps' => [
                    ['submit', 4.5],
                    ['forward', 3.8, 'Outdoor setup on the open field needs GSO clearance for tents and the sound system.'],
                ],
            ],
            'tech-summit' => [
                'owner' => 'lexerichsontalavera@plv.edu.ph',
                'title' => 'Annual Tech Summit',
                'description' => 'Technology summit with keynote speakers, workshops, and networking sessions for IT students.',
                'type' => 'General Assemblies and Similar Activities',
                'venue' => 'PLV Auditorium',
                'starts_in' => 5,
                'days' => 2,
                'time' => ['08:00', '17:00'],
                'participants' => [300, 50],
                'budget' => 45000,
                'budget_breakdown' => 'Speakers: 20,000; Stage and LED wall: 15,000; Kits: 10,000',
                'fund' => 'Organizational Funds',
                'adviser_contact' => '09182345604',
                'special_requirements' => 'Stage lighting, LED wall, and four wireless microphones.',
                'steps' => [
                    ['submit', 6.2],
                    ['forward', 5.1, 'Requesting GSO review of the auditorium booking, stage lighting and LED wall.'],
                ],
            ],
            'sports-week' => [
                'owner' => 'ana.delacruz@plv.edu.ph',
                'title' => 'Sports Week Competition',
                'description' => 'Inter-department competition featuring basketball, volleyball, badminton, and other athletic events.',
                'type' => 'General Assemblies and Similar Activities',
                'venue' => 'PLV Open Field - Annex',
                'starts_in' => 10,
                'days' => 3,
                'time' => ['07:00', '18:00'],
                'participants' => [400, 0],
                'budget' => 30000,
                'fund' => 'University/Appropriate Government Funds',
                'adviser_contact' => '09182345605',
                'steps' => [
                    ['submit', 3.3],
                    ['forward', 2.1, 'Multi-day use of the annex field. Please confirm maintenance and utility support.'],
                ],
            ],

            // pending_osa_approval: GSO has answered, OSA decides
            'music-arts-festival' => [
                'owner' => 'john.martinez@plv.edu.ph',
                'title' => 'Music and Arts Festival',
                'description' => 'Showcase of student talent in music, dance, painting, and the performing arts.',
                'type' => 'General Assemblies and Similar Activities',
                'venue' => 'PLV Auditorium',
                'starts_in' => 14,
                'time' => ['13:00', '20:00'],
                'participants' => [350, 80],
                'budget' => 38000,
                'fund' => 'Organizational Funds',
                'adviser_contact' => '09182345606',
                'steps' => [
                    ['submit', 8.4],
                    ['forward', 7.6, 'Forwarding for venue, sound system and electrical load review.'],
                    ['gso', 5.2, 'approved', 'Auditorium is available. A sound technician is assigned and the electrical load is within capacity.'],
                ],
            ],
            'acquaintance-night' => [
                'owner' => 'student@plv.edu.ph',
                'title' => 'Freshmen Acquaintance Night',
                'description' => 'Evening welcome program for first-year members with performances and games.',
                'type' => 'General Assemblies and Similar Activities',
                'venue' => 'PLV Open Field - Main',
                'starts_in' => 18,
                'time' => ['17:00', '22:00'],
                'participants' => [500, 0],
                'budget' => 25000,
                'fund' => 'Organizational Funds',
                'adviser_contact' => '09182345607',
                'steps' => [
                    ['submit', 6.5],
                    ['forward', 5.9, 'Evening event on the open field. Please check lighting and security coverage.'],
                    ['gso', 3.4, 'for_revision', 'The open field has no evening lighting. Move the program to the auditorium or provide rented lighting and a generator plan.'],
                    ['comment', 3.3, 'gso', 'We can lend two floodlights, but a generator is still needed for the stage. Please coordinate with GSO.'],
                ],
            ],

            // for_revision, then revised and resubmitted as the same ticket (amended)
            'innovation-hackathon' => [
                'owner' => 'student@plv.edu.ph',
                'title' => 'Innovation Challenge Hackathon',
                'description' => 'Two-day hackathon where student teams build technology solutions to community problems.',
                'type' => 'Training, Rehearsals, Practices',
                'venue' => 'PLV Conference Hall',
                'starts_in' => 21,
                'days' => 2,
                'time' => ['08:00', '20:00'],
                'participants' => [120, 30],
                'budget' => 20000,
                'fund' => 'Organizational Funds',
                'adviser_contact' => '09182345608',
                'steps' => [
                    ['submit', 7.1],
                    ['revise', 6.0, 'Please attach the competition mechanics and the CVs of the invited judges, and reduce the participant count to fit the conference hall.'],
                    ['comment', 5.9, 'osa', 'The conference hall seats 120, so combined participants must not exceed that.'],
                    ['comment', 5.2, 'owner', 'Noted. We will cap participants at 120 and attach the mechanics and the judges\' CVs.'],
                    ['resubmit', 4.8, [
                        'participants' => [100, 20],
                        'additional_notes' => 'Resubmitted with the mechanics, the judges\' CVs, and a participant cap of 120.',
                    ]],
                ],
            ],

            // for_revision after GSO asked for changes and OSA agreed
            'shirt-preorder' => [
                'owner' => 'lily.garcia@plv.edu.ph',
                'title' => 'Organization Shirt Pre-order Distribution',
                'description' => 'Distribution of pre-ordered organization shirts to members.',
                'type' => 'Organization Shirts / IGP',
                'venue' => 'PLV Conference Hall',
                'starts_in' => 25,
                'time' => ['09:00', '16:00'],
                'participants' => [200, 0],
                'budget' => 60000,
                'fund' => 'Organizational Funds',
                'adviser_contact' => '09182345609',
                'igp_details' => 'Pre-order of 200 dri-fit organization shirts at 300 pesos each. Proceeds go to the organization fund.',
                'steps' => [
                    ['submit', 9.0],
                    ['forward', 8.2, 'Distribution booth near the conference hall. Forwarding for space and table allocation.'],
                    ['gso', 6.7, 'for_revision', 'The conference hall area is reserved for enrollment that week. Please choose another distribution area.'],
                    ['revise', 5.5, 'GSO could not allocate the area. Revise the distribution venue and attach the signed supplier contract before resubmitting.'],
                ],
            ],

            // approved: on the calendar this month and next
            'cultural-festival' => [
                'owner' => 'student@plv.edu.ph',
                'title' => 'Cultural Festival Celebration',
                'description' => 'Celebrating diversity through cultural performances, traditional food, and art exhibits.',
                'type' => 'General Assemblies and Similar Activities',
                'venue' => 'PLV Auditorium',
                'starts_in' => 7,
                'time' => ['09:00', '17:00'],
                'participants' => [350, 40],
                'budget' => 35000,
                'fund' => 'Organizational Funds',
                'adviser_contact' => '09182345610',
                'steps' => [
                    ['submit', 10.2],
                    ['approve', 8.6, 'Approved. Coordinate the final program flow with OSA three days before the event.'],
                ],
            ],
            'career-workshop' => [
                'owner' => 'lexerichsontalavera@plv.edu.ph',
                'title' => 'Career Development Workshop',
                'description' => 'Industry professionals discuss career paths, resume building, and interview techniques.',
                'type' => 'Training, Rehearsals, Practices',
                'venue' => 'PLV Conference Hall',
                'starts_in' => 20,
                'time' => ['13:00', '17:00'],
                'participants' => [110, 5],
                'budget' => 9000,
                'fund' => 'University/Appropriate Government Funds',
                'adviser_contact' => '09182345611',
                'steps' => [
                    ['submit', 9.5],
                    ['forward', 8.8, 'Forwarding for the conference hall booking and projector setup.'],
                    ['gso', 7.2, 'approved', 'Conference hall booked. The projector and sound system will be ready by 12:00.'],
                    ['approve', 6.4, 'Final approval granted after GSO confirmed the venue.'],
                ],
            ],
            'coastal-cleanup' => [
                'owner' => 'ana.delacruz@plv.edu.ph',
                'title' => 'Coastal Clean-up Drive',
                'description' => 'Volunteer clean-up drive with a partner environmental group.',
                'type' => 'Off-Campus Activities',
                'venue_other' => 'Manila Bay Dolphin Beach, Manila',
                'starts_in' => 35,
                'time' => ['06:00', '12:00'],
                'participants' => [60, 0],
                'budget' => 15000,
                'fund' => 'Organizational Funds',
                'adviser_contact' => '09182345612',
                'off_campus' => [
                    'oc_tsp' => 'in-house',
                    'oc_transportation_type' => 'University bus',
                ],
                'steps' => [
                    ['submit', 5.0],
                    ['approve', 3.1, 'Approved. Submit signed waivers for all participants before departure.'],
                ],
            ],

            // approved, held this week: stays approved during the reschedule grace week
            'financial-literacy-webinar' => [
                'owner' => 'lily.garcia@plv.edu.ph',
                'title' => 'Online Financial Literacy Webinar',
                'description' => 'Webinar on budgeting, saving, and investing basics for students.',
                'type' => 'Online Activities',
                'venue_other' => 'Zoom (online)',
                'starts_in' => -3,
                'time' => ['14:00', '16:00'],
                'participants' => [150, 50],
                'budget' => 2000,
                'fund' => 'Organizational Funds',
                'adviser_contact' => '09182345613',
                'steps' => [
                    ['submit', 20.0],
                    ['approve', 17.5, 'Approved. Share the webinar link with OSA for monitoring.'],
                ],
            ],

            // completed: approved, held three weeks ago, then auto-completed
            'general-assembly' => [
                'owner' => 'student@plv.edu.ph',
                'title' => 'First General Assembly',
                'description' => 'Opening general assembly introducing the officers and the plan for the academic year.',
                'type' => 'General Assemblies and Similar Activities',
                'venue' => 'PLV Auditorium',
                'starts_in' => -21,
                'time' => ['08:00', '12:00'],
                'participants' => [400, 0],
                'budget' => 10000,
                'fund' => 'Organizational Funds',
                'adviser_contact' => '09182345614',
                'steps' => [
                    ['submit', 45.0],
                    ['approve', 40.0, 'Approved. Submit the post-event report within 7 days after the event.'],
                ],
            ],
        ];
    }
}
