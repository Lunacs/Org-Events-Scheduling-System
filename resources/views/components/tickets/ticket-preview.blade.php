@props(['ticket'])

{{-- Ticket Details - Refactored into modular components --}}
<div class="space-y-6">
    {{-- Organization Information Section --}}
    <x-tickets.sections.organization-info :ticket="$ticket" />

    {{-- Event Details Section --}}
    <x-tickets.sections.event-details :ticket="$ticket" />

    {{-- Schedule & Venue Section --}}
    <x-tickets.sections.schedule-venue :ticket="$ticket" />

    {{-- Budget Information Section --}}
    <x-tickets.sections.budget-info :ticket="$ticket" />

    {{-- Additional Information Section (conditional) --}}
    <x-tickets.sections.additional-info :ticket="$ticket" />

    {{-- Attachments Section --}}
    <x-tickets.sections.attachments-list :ticket="$ticket" />
</div>
