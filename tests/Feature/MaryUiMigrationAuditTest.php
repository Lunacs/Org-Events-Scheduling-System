<?php

use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| MaryUI → Tailwind migration audit
|--------------------------------------------------------------------------
|
| This audit powers the per-phase migration gate (Requirements 9.1, 10.1). It
| scans `resources/views/` for `<x-mary-` occurrences and reports how many
| remain, scoped per phase directory (public, student-org, osa, gso,
| superadmin) and repository-wide.
|
| The migration has not happened yet, so the audit runs in NON-BLOCKING
| reporting mode: it never fails the suite for existing occurrences. As each
| phase completes, add its key to `maryUiCompletedPhases()` to switch on a
| zero-occurrence assertion for that phase. Once every phase is done, flip
| `maryUiRepositoryShouldBeClean()` to `true` to enforce a repository-wide
| zero result (the final gate for task 9.3).
|
*/

/**
 * Directory / file scopes that make up each migration phase.
 *
 * @return array<string, array<int, string>>
 */
function maryUiPhasePaths(): array
{
    $views = fn (string $relative): string => resource_path('views/'.$relative);

    return [
        // Phase 1 — Public-facing pages and shared layouts.
        'public' => [
            $views('components/layouts'),
            $views('livewire/about-us.blade.php'),
            $views('livewire/faq.blade.php'),
            $views('livewire/data-privacy-notice.blade.php'),
            $views('livewire/notification-dropdown.blade.php'),
            $views('livewire/pages'),
            $views('errors'),
        ],
        // Phase 2 — Student Org pages.
        'student-org' => [
            $views('livewire/student-org'),
            $views('components/student-org'),
        ],
        // Phase 3 — OSA pages.
        'osa' => [
            $views('osa'),
            $views('livewire/osa'),
            $views('components/osa'),
            $views('reports/osa'),
        ],
        // Phase 4 — GSO pages.
        'gso' => [
            $views('livewire/gso'),
            $views('reports/gso'),
        ],
        // Phase 5 — Superadmin pages.
        'superadmin' => [
            $views('superadmin'),
            $views('livewire/superadmin'),
            $views('components/superadmin'),
        ],
    ];
}

/**
 * Phases whose views must be free of `<x-mary-` tags. Add a phase key here as
 * soon as its migration is complete to enable a hard zero-occurrence gate.
 *
 * @return array<int, string>
 */
function maryUiCompletedPhases(): array
{
    return [
        'public',
        'student-org',
        'osa',
        'gso',
        'superadmin',
    ];
}

/**
 * Whether the entire `resources/views/` tree is expected to be free of MaryUI
 * tags. Flip to `true` once Phase 5 is complete (task 9.3).
 */
function maryUiRepositoryShouldBeClean(): bool
{
    return true;
}

/**
 * Scan the given view directories/files for `<x-mary-` occurrences.
 *
 * @param  array<int, string>  $paths  Absolute directory or file paths.
 * @return array<int, array{file: string, line: int, text: string}>
 */
function maryUiOccurrences(array $paths): array
{
    $occurrences = [];

    foreach ($paths as $path) {
        if (! File::exists($path)) {
            continue;
        }

        $files = File::isDirectory($path)
            ? File::allFiles($path)
            : [new SplFileInfo($path)];

        foreach ($files as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $lines = preg_split('/\r\n|\r|\n/', File::get($file->getPathname())) ?: [];

            foreach ($lines as $index => $line) {
                if (str_contains($line, '<x-mary-')) {
                    $occurrences[] = [
                        'file' => $file->getPathname(),
                        'line' => $index + 1,
                        'text' => trim($line),
                    ];
                }
            }
        }
    }

    return $occurrences;
}

/**
 * Build a human-readable failure message listing each remaining occurrence.
 *
 * @param  array<int, array{file: string, line: int, text: string}>  $occurrences
 */
function maryUiReport(string $scope, array $occurrences): string
{
    if ($occurrences === []) {
        return "No <x-mary- tags remain in {$scope}.";
    }

    $lines = array_map(
        fn (array $o): string => sprintf('  %s:%d  %s', $o['file'], $o['line'], $o['text']),
        $occurrences,
    );

    return sprintf(
        "Found %d <x-mary- occurrence(s) in %s:\n%s",
        count($occurrences),
        $scope,
        implode("\n", $lines),
    );
}

it('provides a working MaryUI scanner that reports zero for a clean scope', function () {
    // The emails views never used MaryUI; this proves the "assert zero per
    // scope" mechanism is sound and stable across the whole migration.
    $occurrences = maryUiOccurrences([resource_path('views/emails')]);

    expect($occurrences)->toBe([], maryUiReport('resources/views/emails', $occurrences));
});

it('reports remaining MaryUI occurrences per phase (non-blocking audit)', function () {
    $summary = [];

    foreach (maryUiPhasePaths() as $phase => $paths) {
        $summary[$phase] = count(maryUiOccurrences($paths));
    }

    $summary['repository-wide'] = count(maryUiOccurrences([resource_path('views')]));

    // Non-blocking: the audit always succeeds. Per-phase assertions are turned
    // on via maryUiCompletedPhases() as phases complete.
    expect($summary)->each->toBeGreaterThanOrEqual(0);
});

test('completed phases contain zero MaryUI occurrences', function (string $phase) {
    $paths = maryUiPhasePaths()[$phase];
    $occurrences = maryUiOccurrences($paths);

    expect($occurrences)->toBe([], maryUiReport("the {$phase} phase views", $occurrences));
})
    ->with(maryUiCompletedPhases() ?: ['(no phases completed yet)'])
    ->skip(
        maryUiCompletedPhases() === [],
        'No phases marked complete yet — add phase keys to maryUiCompletedPhases() as each phase finishes.',
    );

it('has zero MaryUI tags anywhere under resources/views (final gate)', function () {
    $occurrences = maryUiOccurrences([resource_path('views')]);

    expect($occurrences)->toBe([], maryUiReport('resources/views', $occurrences));
})->skip(
    ! maryUiRepositoryShouldBeClean(),
    'Repository-wide MaryUI removal not expected yet — flip maryUiRepositoryShouldBeClean() once Phase 5 completes.',
);
