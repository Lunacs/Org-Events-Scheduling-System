<?php

use App\Support\AttachmentMimeType;

test('resolves application/pdf from extension when stored type is octet-stream', function (): void {
    expect(AttachmentMimeType::resolve('fun id.pdf', 'application/octet-stream', null))
        ->toBe('application/pdf');
});

test('preserves non-octet-stream preferred mime', function (): void {
    expect(AttachmentMimeType::resolve('file.bin', 'application/pdf', null))
        ->toBe('application/pdf');
});

test('uses extension map when preferred mime is missing', function (): void {
    expect(AttachmentMimeType::resolve('sheet.xlsx', null, null))
        ->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

test('uses fallback mime when extension unknown and preferred is octet-stream', function (): void {
    expect(AttachmentMimeType::resolve('unknown.zzz', 'application/octet-stream', 'image/png'))
        ->toBe('image/png');
});

test('returns octet-stream when nothing else matches', function (): void {
    expect(AttachmentMimeType::resolve('unknown.zzz', 'application/octet-stream', 'application/octet-stream'))
        ->toBe('application/octet-stream');
});
