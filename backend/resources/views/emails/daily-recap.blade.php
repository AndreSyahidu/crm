<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily CRM Recap</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #007bff;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #007bff;
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .stat-row {
            display: table-row;
        }
        .stat-label {
            display: table-cell;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: 500;
        }
        .stat-value {
            display: table-cell;
            padding: 10px 15px;
            text-align: right;
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
            color: #007bff;
        }
        .highlight {
            background-color: #d4edda !important;
            color: #155724 !important;
        }
        .warning {
            background-color: #fff3cd !important;
            color: #856404 !important;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            color: #666;
            font-size: 14px;
        }
        .cta-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .cta-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Daily CRM Recap</h1>
            <p>{{ $date }}</p>
        </div>

        <!-- Overall Team Statistics -->
        <div class="section">
            <h2>📈 Team Performance</h2>
            <div class="stats-grid">
                <div class="stat-row">
                    <div class="stat-label">New Leads</div>
                    <div class="stat-value">{{ $stats['new_leads'] }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Qualified Leads</div>
                    <div class="stat-value">{{ $stats['qualified_leads'] }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Won Deals</div>
                    <div class="stat-value highlight">{{ $stats['won_deals'] }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Lost Deals</div>
                    <div class="stat-value">{{ $stats['lost_deals'] }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value highlight">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Activity -->
        <div class="section">
            <h2>💬 WhatsApp Activity</h2>
            <div class="stats-grid">
                <div class="stat-row">
                    <div class="stat-label">Messages Sent</div>
                    <div class="stat-value">{{ $stats['whatsapp_sent'] }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Messages Received</div>
                    <div class="stat-value">{{ $stats['whatsapp_received'] }}</div>
                </div>
            </div>
        </div>

        <!-- Your Personal Stats -->
        <div class="section">
            <h2>👤 Your Performance</h2>
            <div class="stats-grid">
                <div class="stat-row">
                    <div class="stat-label">Your New Leads</div>
                    <div class="stat-value">{{ $userStats['my_new_leads'] }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Your Won Deals</div>
                    <div class="stat-value highlight">{{ $userStats['my_won_deals'] }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Tasks Due Today</div>
                    <div class="stat-value @if($userStats['my_tasks_due_today'] > 0) warning @endif">
                        {{ $userStats['my_tasks_due_today'] }}
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Overdue Tasks</div>
                    <div class="stat-value @if($userStats['my_overdue_tasks'] > 0) warning @endif">
                        {{ $userStats['my_overdue_tasks'] }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div style="text-align: center;">
            <a href="{{ config('app.url') }}" class="cta-button">
                Open CRM Dashboard
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is an automated daily recap from your WhatsApp CRM system.</p>
            <p style="margin-top: 10px; font-size: 12px; color: #999;">
                © {{ date('Y') }} WhatsApp CRM. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
