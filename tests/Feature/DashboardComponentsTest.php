<?php

use Illuminate\Support\Facades\Blade;

/*
 * Guards the shared dashboard component vocabulary: token-driven, dark-correct,
 * and free of the banned impeccable/frontend-design anti-patterns.
 */

$bannedPatterns = [
    'border-l-4',        // side-stripe border (absolute ban)
    'text-gray-',        // hardcoded gray with no dark variant
    'hover:scale-',      // scale-hover
    'bg-white',          // hardcoded surface, breaks dark mode
    'blur-2xl',          // decorative glow blob
    'from-primary/5',    // gradient card background
];

it('renders stat-card with token classes and no banned patterns', function () use ($bannedPatterns) {
    $html = Blade::render(
        '<x-dashboard.stat-card title="Pending" value="12" description="Awaiting review" icon="o-clock" color="warning" />'
    );

    expect($html)->toContain('Pending')
        ->and($html)->toContain('12')
        ->and($html)->toContain('bg-warning/10')
        ->and($html)->toContain('rounded-box')
        ->and($html)->toContain('text-base-content');

    foreach ($bannedPatterns as $banned) {
        expect($html)->not->toContain($banned);
    }
});

it('renders stat-card drill-in link when action props are provided', function () {
    $html = Blade::render(
        '<x-dashboard.stat-card title="For Revision" value="3" icon="o-x-circle" color="error" actionLabel="View" actionLink="/admin/tickets" />'
    );

    expect($html)->toContain('View')
        ->and($html)->toContain('/admin/tickets')
        ->and($html)->toContain('border-t border-base-300')
        ->and($html)->toContain('focus-visible:ring-2');
});

it('renders page-header with title, subtitle and actions slot, no glow blob', function () use ($bannedPatterns) {
    $html = Blade::render(
        '<x-dashboard.page-header title="Dashboard" subtitle="Overview" icon="o-squares-2x2"><x-slot:actions>ACT</x-slot:actions></x-dashboard.page-header>'
    );

    expect($html)->toContain('Dashboard')
        ->and($html)->toContain('Overview')
        ->and($html)->toContain('ACT')
        ->and($html)->toContain('text-dash-title');

    foreach ($bannedPatterns as $banned) {
        expect($html)->not->toContain($banned);
    }
});

it('renders section with heading and view-all link', function () {
    $html = Blade::render(
        '<x-dashboard.section title="Recent" actionLabel="View all" actionLink="/x">BODY</x-dashboard.section>'
    );

    expect($html)->toContain('Recent')
        ->and($html)->toContain('View all')
        ->and($html)->toContain('BODY')
        ->and($html)->toContain('rounded-box border border-base-300 bg-base-100');
});

it('renders quick-action-card as a flat token surface, no gradient or scale', function () use ($bannedPatterns) {
    $html = Blade::render(
        '<x-dashboard.quick-action-card title="Submit" description="New ticket" icon="o-document-plus" link="/submit" color="primary" />'
    );

    expect($html)->toContain('Submit')
        ->and($html)->toContain('/submit')
        ->and($html)->toContain('hover:bg-base-200')
        ->and($html)->not->toContain('bg-gradient-to-br')
        ->and($html)->not->toContain('group-hover:scale-110');

    foreach ($bannedPatterns as $banned) {
        expect($html)->not->toContain($banned);
    }
});

it('renders action-queue band with count and queue items', function () use ($bannedPatterns) {
    $html = Blade::render(<<<'BLADE'
        <x-dashboard.action-queue title="Needs your action" :count="2" actionLabel="View all" actionLink="/all">
            <x-dashboard.queue-item title="Org Fair" reference="TKT-001" badge="Pending" badgeClass="badge-warning" href="/t/1">
                <x-slot:meta>Science Club</x-slot:meta>
            </x-dashboard.queue-item>
        </x-dashboard.action-queue>
    BLADE);

    expect($html)->toContain('Needs your action')
        ->and($html)->toContain('Org Fair')
        ->and($html)->toContain('TKT-001')
        ->and($html)->toContain('Science Club')
        ->and($html)->toContain('badge-primary')
        ->and($html)->toContain('divide-base-300');

    foreach ($bannedPatterns as $banned) {
        expect($html)->not->toContain($banned);
    }
});
