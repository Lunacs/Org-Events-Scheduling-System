<?php

namespace App\Livewire\Gso;

use App\Models\Office_Approval;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Component
{
    #[Title('GSO Reports & Analytics')]
    #[Layout('components.layouts.gso-layout')]
    public array $reportSeed = [];

    public function mount(): void
    {
        $this->reportSeed = $this->buildReportSeed();
    }

    public function render()
    {
        return view('livewire.gso.reports', [
            'reportSeed' => $this->reportSeed,
        ]);
    }

    public function export(string $format, string $timePeriod, ?string $start = null, ?string $end = null, ?string $search = null)
    {
        $officeId = Auth::user()?->office_id;

        $approvals = $this->filteredApprovals($officeId, $timePeriod, $start, $end, $search);

        if ($approvals->isEmpty()) {
            return $this->emptyExport($format);
        }

        $records = $this->formatRecords($approvals);
        $stats = $this->computeStats($approvals);
        $breakdown = $this->computeBreakdown($approvals);
        [$rangeStart, $rangeEnd] = $this->resolveRange($timePeriod, $start, $end);

        if ($format === 'csv') {
            return $this->exportCsv($records, $stats, $rangeStart, $rangeEnd);
        }

        if ($format === 'pdf') {
            return $this->exportPdf($records, $stats, $breakdown, $rangeStart, $rangeEnd);
        }

        abort(400, 'Unsupported export format.');
    }

    protected function buildReportSeed(): array
    {
        $officeId = Auth::user()?->office_id;

        $approvals = $this->approvalsQuery($officeId)->get();

        return [
            'generated_at' => Carbon::now()->toIso8601String(),
            'records' => $this->formatRecords($approvals),
        ];
    }

    protected function approvalsQuery(?int $officeId): Builder
    {
        return Office_Approval::query()
            ->with([
                'ticket.eventType',
                'ticket.user.studentOrganization',
            ])
            ->whereIn('decision', ['approved', 'Approved', 'rejected', 'Rejected'])
            ->when($officeId, fn(Builder $query) => $query->where('office_id', $officeId));
    }

    protected function filteredApprovals(?int $officeId, string $timePeriod, ?string $start, ?string $end, ?string $search): Collection
    {
        $query = $this->approvalsQuery($officeId);

        [$rangeStart, $rangeEnd] = $this->resolveRange($timePeriod, $start, $end);

        if ($rangeStart && $rangeEnd) {
            $query->whereBetween('updated_at', [$rangeStart, $rangeEnd]);
        }

        if ($search) {
            $term = '%' . Str::lower(trim($search)) . '%';

            $query->where(function (Builder $builder) use ($term) {
                $builder->whereHas('ticket', function (Builder $ticketQuery) use ($term) {
                    $ticketQuery
                        ->whereRaw('LOWER(ticket_number) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(title) LIKE ?', [$term])
                        ->orWhereHas('user.studentOrganization', fn(Builder $orgQuery) => $orgQuery->whereRaw('LOWER(org_name) LIKE ?', [$term]));
                });
            });
        }

        return $query->orderByDesc('updated_at')->get();
    }

    protected function formatRecords(Collection $approvals): array
    {
        return $approvals->map(function (Office_Approval $approval) {
            $ticket = $approval->ticket;
            $decidedAt = $approval->updated_at ?? $approval->created_at ?? Carbon::now();
            $submittedAt = $approval->created_at ?? $decidedAt;

            $responseHours = $submittedAt->diffInMinutes($decidedAt) / 60;

            return [
                'id' => $approval->id,
                'decided_at' => $decidedAt->toIso8601String(),
                'date' => $decidedAt->format('Y-m-d'),
                'ticketId' => $ticket?->ticket_number ?? 'N/A',
                'ticketDetailsUrl' => $ticket ? route('gso.ticket-details', $ticket) : null,
                'eventName' => $ticket?->title ?? 'N/A',
                'organization' => $ticket?->user?->studentOrganization?->org_name
                    ?? $ticket?->user?->name
                    ?? 'N/A',
                'requestType' => $ticket?->eventType?->type_name ?? 'Unspecified',
                'decision' => Str::title($approval->decision),
                'responseTime' => round($responseHours, 1),
                'comments' => $approval->remarks ?? '',
            ];
        })->values()->all();
    }

    protected function computeStats(Collection $approvals): array
    {
    $approved = $approvals->filter(fn(Office_Approval $approval) => strcasecmp($approval->decision, 'approved') === 0)->count();
    $rejected = $approvals->filter(fn(Office_Approval $approval) => strcasecmp($approval->decision, 'rejected') === 0)->count();
        $total = max($approved + $rejected, 1);

        $avgResponse = $approvals
            ->map(function (Office_Approval $approval) {
                $decidedAt = $approval->updated_at ?? $approval->created_at ?? Carbon::now();
                $submittedAt = $approval->created_at ?? $decidedAt;

                return $submittedAt->diffInMinutes($decidedAt) / 60;
            })
            ->average();

        return [
            'totalApproved' => $approved,
            'totalRejected' => $rejected,
            'approvalRate' => (int) round(($approved / $total) * 100),
            'avgResponseTime' => round($avgResponse ?? 0, 1),
        ];
    }

    protected function computeBreakdown(Collection $approvals): array
    {
        $colors = ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#6366f1', '#14b8a6'];

        $grouped = $approvals
            ->groupBy(fn(Office_Approval $approval) => Str::title($approval->ticket?->eventType?->type_name ?? 'Unspecified'))
            ->map(function (Collection $collection) {
                return $collection->count();
            });

        $total = $grouped->sum();

        $index = 0;

        return $grouped
            ->map(function (int $count, string $type) use (&$index, $colors, $total) {
                $color = $colors[$index % count($colors)];
                $index++;

                return [
                    'type' => $type,
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                    'color' => $color,
                ];
            })
            ->values()
            ->all();
    }

    protected function resolveRange(string $timePeriod, ?string $start, ?string $end): array
    {
        $now = Carbon::now();

        return match ($timePeriod) {
            'this_week' => [
                $now->copy()->startOfWeek(Carbon::SUNDAY),
                $now->copy()->endOfWeek(Carbon::SATURDAY),
            ],
            'this_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            'last_month' => [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
            'this_quarter' => [
                $now->copy()->firstOfQuarter(),
                $now->copy()->lastOfQuarter(),
            ],
            'this_year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
            ],
            'custom' => $this->resolveCustomRange($start, $end),
            default => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
        };
    }

    protected function resolveCustomRange(?string $start, ?string $end): array
    {
        if (! $start || ! $end) {
            return [null, null];
        }

        try {
            $rangeStart = Carbon::parse($start)->startOfDay();
            $rangeEnd = Carbon::parse($end)->endOfDay();
        } catch (\Throwable) {
            return [null, null];
        }

        return [$rangeStart, $rangeEnd];
    }

    protected function exportCsv(array $records, array $stats, ?Carbon $rangeStart, ?Carbon $rangeEnd): StreamedResponse
    {
        $fileName = 'gso-report-' . Carbon::now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($records, $stats, $rangeStart, $rangeEnd) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['GSO Reports & Analytics']);
            fputcsv($handle, ['Generated At', Carbon::now()->format('Y-m-d H:i')]);

            if ($rangeStart || $rangeEnd) {
                fputcsv($handle, ['Date Range',
                    optional($rangeStart)->format('Y-m-d') ?? 'N/A',
                    optional($rangeEnd)->format('Y-m-d') ?? 'N/A',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Totals']);
            fputcsv($handle, ['Approved', $stats['totalApproved']]);
            fputcsv($handle, ['Rejected', $stats['totalRejected']]);
            fputcsv($handle, ['Approval Rate (%)', $stats['approvalRate']]);
            fputcsv($handle, ['Average Response Time (hrs)', $stats['avgResponseTime']]);
            fputcsv($handle, []);

            fputcsv($handle, ['Date', 'Ticket ID', 'Event', 'Organization', 'Request Type', 'Decision', 'Response Time (hrs)', 'Comments']);

            foreach ($records as $record) {
                fputcsv($handle, [
                    $record['date'],
                    $record['ticketId'],
                    $record['eventName'],
                    $record['organization'],
                    $record['requestType'],
                    $record['decision'],
                    $record['responseTime'],
                    $record['comments'],
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function exportPdf(array $records, array $stats, array $breakdown, ?Carbon $rangeStart, ?Carbon $rangeEnd)
    {
        if (! class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            throw new \RuntimeException('PDF export requires the barryvdh/laravel-dompdf package.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.gso.summary-pdf', [
            'records' => $records,
            'stats' => $stats,
            'breakdown' => $breakdown,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'generatedAt' => Carbon::now(),
        ])->setPaper('a4', 'portrait');

        $fileName = 'gso-report-' . Carbon::now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(fn() => print($pdf->output()), $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    protected function emptyExport(string $format)
    {
        $fileName = 'gso-report-empty-' . Carbon::now()->format('YmdHis') . '.' . $format;

        return response()->streamDownload(function () use ($format) {
            if ($format === 'csv') {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['No records available for the selected criteria.']);
                fclose($handle);
            } else {
                echo 'No records available for the selected criteria.';
            }
        }, $fileName, [
            'Content-Type' => $format === 'csv' ? 'text/csv' : 'text/plain',
        ]);
    }
}
