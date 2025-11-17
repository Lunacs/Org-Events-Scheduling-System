<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4fd1c5;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 24px;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 12px;
            color: #718096;
            margin-bottom: 10px;
        }

        .header .meta {
            font-size: 10px;
            color: #a0aec0;
        }

        .overview {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }

        .overview-row {
            display: table-row;
        }

        .stat-card {
            display: table-cell;
            width: 25%;
            padding: 12px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #4fd1c5;
            display: block;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 9px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2d3748;
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #4fd1c5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead {
            background-color: #4fd1c5;
            color: white;
        }

        table thead th {
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table tbody td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }

        table tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }

        table tbody tr:hover {
            background-color: #edf2f7;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #78350f;
        }

        .badge-error {
            background-color: #fed7d7;
            color: #742a2a;
        }

        .badge-info {
            background-color: #bee3f8;
            color: #2c5282;
        }

        .footer {
            position: fixed;
            bottom: 15px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 8px;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #a0aec0;
        }

        .empty-state svg {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
        }

        @page {
            margin: 15mm;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">
            Period: {{ $date_from }} to {{ $date_to }}
        </div>
        <div class="meta">
            Generated on {{ $generated_at }} by PLV Event Scheduling System
        </div>
    </div>

    {{-- Overview Statistics --}}
    @if (isset($overview) && !empty($overview))
        <div class="overview">
            <div class="overview-row">
                <div class="stat-card">
                    <span class="stat-value">{{ $overview['total_events'] ?? 0 }}</span>
                    <span class="stat-label">Total Events</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value">{{ $overview['approved_events'] ?? 0 }}</span>
                    <span class="stat-label">Approved Events</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value">{{ $overview['total_tickets'] ?? 0 }}</span>
                    <span class="stat-label">Total Tickets</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value">{{ $overview['active_orgs'] ?? 0 }}</span>
                    <span class="stat-label">Active Organizations</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Events Table --}}
    <div class="section-title">Events List</div>

    @if (count($events) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Ticket #</th>
                    <th style="width: 20%;">Event Title</th>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 15%;">Type</th>
                    <th style="width: 20%;">Organization</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 11%;">Venue</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($events as $event)
                    <tr>
                        <td>{{ $event['Ticket Number'] }}</td>
                        <td><strong>{{ $event['Event Title'] }}</strong></td>
                        <td>{{ $event['Date From'] }}</td>
                        <td>{{ $event['Event Type'] }}</td>
                        <td>{{ $event['Organization'] }}</td>
                        <td>
                            @php
                                $statusClass = match (strtolower($event['Status'])) {
                                    'approved' => 'badge-success',
                                    'pending' => 'badge-warning',
                                    'rejected', 'cancelled' => 'badge-error',
                                    'rescheduled' => 'badge-info',
                                    default => 'badge-info',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $event['Status'] }}</span>
                        </td>
                        <td>{{ $event['Venue'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <p style="font-size: 12px; color: #718096;">No events found for the selected period.</p>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>PLV Event Scheduling System - Office of Student Affairs</p>
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>

</html>
