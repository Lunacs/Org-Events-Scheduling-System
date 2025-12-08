<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>GSO - Reports & Analytics</title>
    <style>
        /* Basic page setup */
        @page { margin: 24mm 16mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; font-size: 12px; line-height: 1.3; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e6f4ef; padding-bottom: 8px; margin-bottom: 12px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .logo { width: 56px; height: 56px; background: #e6f4ef; border-radius: 6px; display: inline-block; text-align: center; line-height: 56px; font-weight: bold; color: #047857; }
        h1 { font-size: 18px; margin: 0; color: #0f5132; }
        .sub { font-size: 11px; color: #4b5563; }

        /* Meta */
        .meta { margin-top: 8px; margin-bottom: 14px; }
        .meta .row { display: flex; gap: 12px; font-size: 11px; color: #374151; }

        /* Summary cards */
        .stats { display: flex; gap: 10px; margin: 10px 0 18px 0; }
        .card { flex: 1; background: #ffffff; border: 1px solid #e6e9ee; padding: 10px; border-radius: 6px; }
        .card .label { font-size: 11px; color: #6b7280; }
        .card .value { font-size: 20px; color: #0f5132; font-weight: 700; margin-top: 6px; }

        /* Breakdown */
        .breakdown { margin-top: 8px; }
        .breakdown table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .breakdown th, .breakdown td { padding: 6px 8px; border-bottom: 1px solid #eef2f7; text-align: left; font-size: 11px; }

        /* Main records table */
        table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 11px; }
        thead th { background: #f3faf6; color: #065f46; padding: 8px; border: 1px solid #e6eef0; }
        tbody td { padding: 6px 8px; border: 1px solid #eef2f7; vertical-align: top; }
        tbody tr:nth-child(even) { background: #fbfdfe; }

        .decision.approved { color: #065f46; background: #ecfdf5; padding: 4px 6px; border-radius: 4px; display: inline-block; }
        .decision.for_revision { color: #7f1d1d; background: #fff1f2; padding: 4px 6px; border-radius: 4px; display: inline-block; }

        /* Small footer */
        footer { margin-top: 18px; font-size: 10px; color: #6b7280; text-align: right; }

        /* Avoid breaking table rows across pages */
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <header class="header">
        <div class="brand">
            <div>
                <h1>GSP Reports &amp; Analytics</h1>
                <div class="sub">Office of Student Affairs — Generated report for administrative review</div>
            </div>
        </div>
        <div style="text-align: right">
            <div style="font-size:12px;color:#374151;">Generated: <strong>{{ $generatedAt->format('Y-m-d H:i') }}</strong></div>
            @if($rangeStart || $rangeEnd)
                <div style="font-size:11px;color:#6b7280;">Date range: {{ optional($rangeStart)->format('Y-m-d') ?? 'N/A' }} — {{ optional($rangeEnd)->format('Y-m-d') ?? 'N/A' }}</div>
            @endif
        </div>
    </header>

    <section class="meta">
        <div class="row">
            <div style="flex:1"><strong>Prepared by:</strong> GSO Admin</div>
            <div style="flex:1"><strong>Records:</strong> {{ count($records) }}</div>
            <div style="flex:1"><strong>Office:</strong> General Student Office</div>
        </div>
    </section>

    <section class="stats">
        <div class="card">
            <div class="label">Total Approved</div>
            <div class="value">{{ $stats['totalApproved'] }}</div>
        </div>
        <div class="card">
            <div class="label">Total For Revision</div>
            <div class="value">{{ $stats['totalForRevision'] }}</div>
        </div>
        <div class="card">
            <div class="label">Approval Rate</div>
            <div class="value">{{ $stats['approvalRate'] }}%</div>
        </div>
        <div class="card">
            <div class="label">Avg. Response Time</div>
            <div class="value">{{ $stats['avgResponseTime'] }} hrs</div>
        </div>
    </section>

    @if(!empty($breakdown))
        <section class="breakdown">
            <strong>Request Type Breakdown</strong>
            <table>
                <thead>
                    <tr>
                        <th style="width:60%">Request Type</th>
                        <th style="width:20%">Count</th>
                        <th style="width:20%">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($breakdown as $item)
                        <tr>
                            <td>{{ $item['type'] }}</td>
                            <td>{{ $item['count'] }}</td>
                            <td>{{ $item['percentage'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    <section style="margin-top:12px;">
        <strong>Approval Records</strong>
        <table>
            <thead>
                <tr>
                    <th style="width:9%">Date</th>
                    <th style="width:11%">Ticket ID</th>
                    <th style="width:22%">Event</th>
                    <th style="width:18%">Organization</th>
                    <th style="width:12%">Request Type</th>
                    <th style="width:9%">Decision</th>
                    <th style="width:10%">Response (hrs)</th>
                    <th style="width:9%">Comments</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record['date'] }}</td>
                        <td>{{ $record['ticketId'] }}</td>
                        <td>{{ $record['eventName'] }}</td>
                        <td>{{ $record['organization'] }}</td>
                        <td>{{ $record['requestType'] }}</td>
                        <td>
                            @if(strtolower($record['decision']) === 'approved')
                                <span class="decision approved">{{ $record['decision'] }}</span>
                            @else
                                <span class="decision for_revision">{{ $record['decision'] }}</span>
                            @endif
                        </td>
                        <td style="text-align:center">{{ $record['responseTime'] }}</td>
                        <td>{{ Illuminate\Support\Str::limit($record['comments'], 120) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No records available for the selected criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <footer>Generated by Org Events Scheduling System • {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</footer>
</body>
</html>
