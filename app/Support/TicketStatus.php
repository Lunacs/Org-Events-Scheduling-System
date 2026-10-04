<?php

namespace App\Support;

/**
 * One vocabulary for ticket status across every portal (DESIGN.md "Status Badges").
 *
 * Each status maps to a plain-language label, a semantic tone (DaisyUI color name),
 * an icon, and who currently holds the ticket, so badges, lists, and dashboards
 * never disagree about what a status means.
 */
class TicketStatus
{
    /**
     * @var array<string, array{label: string, tone: string, icon: string, owner: string|null}>
     */
    public const MAP = [
        'received' => ['label' => 'Under OSA review', 'tone' => 'info', 'icon' => 'o-clock', 'owner' => 'OSA'],
        'pending' => ['label' => 'Under OSA review', 'tone' => 'info', 'icon' => 'o-clock', 'owner' => 'OSA'],
        'amended' => ['label' => 'Resubmitted', 'tone' => 'info', 'icon' => 'o-arrow-path', 'owner' => 'OSA'],
        'gso_review' => ['label' => 'With GSO', 'tone' => 'info', 'icon' => 'o-building-office-2', 'owner' => 'GSO'],
        'pending_osa_approval' => ['label' => 'Awaiting final approval', 'tone' => 'info', 'icon' => 'o-scale', 'owner' => 'OSA'],
        'for_revision' => ['label' => 'Needs revision', 'tone' => 'warning', 'icon' => 'o-pencil-square', 'owner' => 'Organization'],
        'needs_revision' => ['label' => 'Needs revision', 'tone' => 'warning', 'icon' => 'o-pencil-square', 'owner' => 'Organization'],
        'approved' => ['label' => 'Approved', 'tone' => 'success', 'icon' => 'o-check-circle', 'owner' => null],
        'rejected' => ['label' => 'Rejected', 'tone' => 'error', 'icon' => 'o-x-circle', 'owner' => null],
        'completed' => ['label' => 'Completed', 'tone' => 'neutral', 'icon' => 'o-check', 'owner' => null],
        'cancelled' => ['label' => 'Cancelled', 'tone' => 'neutral', 'icon' => 'o-no-symbol', 'owner' => null],
        'archived' => ['label' => 'Archived', 'tone' => 'neutral', 'icon' => 'o-archive-box', 'owner' => null],
    ];

    /**
     * Literal badge classes per tone (Tailwind can't see interpolated class names).
     *
     * @var array<string, string>
     */
    public const BADGE_CLASSES = [
        'info' => 'badge-soft badge-info',
        'success' => 'badge-soft badge-success',
        'warning' => 'badge-soft badge-warning',
        'error' => 'badge-soft badge-error',
        'neutral' => 'badge-soft',
    ];

    /**
     * @return array{label: string, tone: string, icon: string, owner: string|null}
     */
    public static function for(?string $status): array
    {
        $key = strtolower((string) $status);

        return self::MAP[$key] ?? [
            'label' => $key === '' ? 'Unknown' : ucfirst(str_replace('_', ' ', $key)),
            'tone' => 'neutral',
            'icon' => 'o-ellipsis-horizontal-circle',
            'owner' => null,
        ];
    }

    public static function label(?string $status): string
    {
        return self::for($status)['label'];
    }

    public static function tone(?string $status): string
    {
        return self::for($status)['tone'];
    }

    public static function badgeClass(?string $status): string
    {
        return self::BADGE_CLASSES[self::tone($status)];
    }

    /**
     * Chart fill per status, in the same tones as the badges. In-progress states share the
     * info hue, so each gets its own shade to stay distinguishable in charts.
     *
     * @var array<string, string>
     */
    public const CHART_COLORS = [
        'received' => '#006eb3',
        'pending' => '#006eb3',
        'amended' => '#4a93cf',
        'gso_review' => '#8bbbe6',
        'pending_osa_approval' => '#0b4a78',
        'for_revision' => '#985e07',
        'needs_revision' => '#985e07',
        'approved' => '#0b7643',
        'rejected' => '#c2272d',
        'completed' => '#5f6b7d',
        'cancelled' => '#9aa3b2',
        'archived' => '#c3c9d3',
    ];

    public static function chartColor(?string $status): string
    {
        return self::CHART_COLORS[strtolower((string) $status)] ?? '#7c8799';
    }

    /**
     * Plain-language next step for the organization that submitted the ticket.
     */
    public static function nextStepForOrganization(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'received', 'pending' => 'OSA is reviewing your request. You will be notified when there is a decision.',
            'amended' => 'OSA is reviewing your resubmitted request.',
            'gso_review' => 'OSA forwarded your request to GSO for venue and logistics approval.',
            'pending_osa_approval' => 'GSO has responded. OSA will make the final decision.',
            'for_revision', 'needs_revision' => 'OSA requested changes. Update your request and resubmit it.',
            'approved' => 'Your event is approved and on the calendar.',
            'rejected' => 'This request was not approved. Read the remarks for the reason.',
            default => '',
        };
    }
}
