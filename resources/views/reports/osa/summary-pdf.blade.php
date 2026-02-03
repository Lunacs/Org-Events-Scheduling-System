<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OSA Report</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 16px;
            margin: 16px 0 8px;
        }

        .muted {
            color: #6b7280;
        }

        .meta {
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
        }

        .section {
            margin-top: 16px;
        }
    </style>
</head>

<body>
    <h1>OSA Report</h1>
    <div class="meta">
        <div class="muted">Type: {{ ucfirst(str_replace('_', ' ', $reportType)) }}</div>
        <div class="muted">Range: {{ $dateFrom }} — {{ $dateTo }}</div>
        @if ($organizationFilter)
            <div class="muted">Organization Filter: {{ $organizationFilter }}</div>
        @endif
        <div class="muted">Generated: {{ $generatedAt->format('Y-m-d H:i') }}</div>
    </div>

    @if ($reportType === 'approved_events' || $reportType === 'for_revision_events')
        <h2>Events</h2>
        <table>
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Title</th>
                    <th>Organization</th>
                    <th>Event Type</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $ticket)
                    @php $orgDeleted = $ticket->user?->studentOrganization?->trashed(); @endphp
                    <tr>
                        <td>{{ $ticket->ticket_number }}</td>
                        <td>{{ $ticket->title }}</td>
                        <td>{{ $orgDeleted ? 'Deleted Organization' : optional($ticket->user?->studentOrganization)->org_name ?? 'N/A' }}
                        </td>
                        <td>{{ optional($ticket->eventType)->type_name ?? 'N/A' }}</td>
                        <td>{{ ucfirst($ticket->status) }}</td>
                        <td>{{ optional($ticket->created_at)?->format('Y-m-d H:i') }}</td>
                        <td>{{ optional($ticket->updated_at)?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No records.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif ($reportType === 'org_participation')
        <h2>Organization Participation</h2>
        <table>
            <thead>
                <tr>
                    <th>Organization</th>
                    <th>Tickets Count</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $org)
                    <tr>
                        <td>{{ $org->org_name }}</td>
                        <td>{{ $org->tickets_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No records.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif (is_array($data) && $reportType === 'monthly_summary')
        <h2>Monthly Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Tickets</td>
                    <td>{{ $data['total_tickets'] }}</td>
                </tr>
                <tr>
                    <td>Approved</td>
                    <td>{{ $data['approved_tickets'] }}</td>
                </tr>
                <tr>
                    <td>For Revision</td>
                    <td>{{ $data['for_revision_tickets'] }}</td>
                </tr>
                <tr>
                    <td>Pending</td>
                    <td>{{ $data['pending_tickets'] }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p>No data available.</p>
    @endif
</body>

</html>
