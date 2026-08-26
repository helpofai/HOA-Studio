{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Ultra-Premium Glassmorphic Email Layout
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/
--}}
<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $renderedSubject }}</title>
    <!--[if mso]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <style>
        /* Base Reset */
        body {
            margin: 0;
            padding: 0;
            background-color: #020617;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #f1f5f9;
            -webkit-font-smoothing: antialiased;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        td {
            padding: 0;
        }
        img {
            border: 0;
            outline: none;
            text-decoration: none;
            display: block;
        }

        /* Outer Background */
        .email-wrapper {
            width: 100%;
            background-color: #020617;
            background-image: radial-gradient(circle at top center, #1e1b4b 0%, #020617 70%);
            padding: 48px 16px;
        }

        /* Main Card Container */
        .email-container {
            max-width: 620px;
            margin: 0 auto;
            background-color: #0b0f19;
            background-image: linear-gradient(180deg, rgba(30, 27, 75, 0.45) 0%, rgba(11, 15, 25, 0.98) 100%);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.9), 0 0 50px rgba(99, 102, 241, 0.15);
        }

        /* Top Brand Header */
        .email-header {
            padding: 38px 40px 28px 40px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: linear-gradient(180deg, rgba(99, 102, 241, 0.18) 0%, rgba(11, 15, 25, 0) 100%);
        }

        /* Website Exact Animated-Style Logo Box */
        .hoa-logo-outer-box {
            display: inline-block;
            position: relative;
            padding: 2px;
            border-radius: 16px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 35%, #ec4899 70%, #06b6d4 100%);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.45), 0 0 15px rgba(6, 182, 212, 0.3);
            margin-bottom: 14px;
        }
        .hoa-logo-inner-box {
            background-color: #020617;
            border-radius: 14px;
            padding: 8px 16px;
            text-align: center;
            display: block;
        }
        .hoa-logo-main-text {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.1em;
            color: #ffffff;
            display: block;
            line-height: 1.1;
            text-shadow: 0 0 12px rgba(129, 140, 248, 0.8);
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hoa-logo-sub-text {
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.22em;
            color: #22d3ee;
            text-transform: uppercase;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            display: block;
            margin-top: 2px;
            line-height: 1;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            font-size: 11px;
            font-weight: 600;
            color: #818cf8;
            margin-top: 4px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Content Area */
        .email-body {
            padding: 36px 40px;
        }
        .email-heading {
            font-size: 22px;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 20px 0;
            letter-spacing: -0.4px;
            line-height: 1.35;
        }
        .email-text {
            font-size: 14.5px;
            line-height: 1.75;
            color: #94a3b8;
            margin: 0 0 28px 0;
            white-space: pre-line;
        }
        .email-text strong, .email-text b {
            color: #f8fafc;
            font-weight: 700;
        }

        /* Action Button */
        .btn-wrapper {
            text-align: center;
            margin: 36px 0 28px 0;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #7c3aed 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 14.5px;
            padding: 15px 36px;
            border-radius: 16px;
            letter-spacing: 0.2px;
            box-shadow: 0 12px 28px -6px rgba(99, 102, 241, 0.55), inset 0 1px 1px rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(165, 180, 252, 0.35);
        }

        /* Footer */
        .email-footer {
            padding: 28px 40px;
            background-color: #060911;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            text-align: center;
            font-size: 11.5px;
            color: #64748b;
            line-height: 1.6;
        }
        .email-footer a {
            color: #818cf8;
            text-decoration: none;
            font-weight: 600;
        }
        .security-badge {
            display: inline-block;
            margin-top: 12px;
            font-size: 10px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header with Official Website Matching Logo Structure -->
            <div class="email-header">
                <div class="hoa-logo-outer-box">
                    <div class="hoa-logo-inner-box">
                        <span class="hoa-logo-main-text">HOA</span>
                        <span class="hoa-logo-sub-text">STUDIO</span>
                    </div>
                </div>
                <h1 class="brand-title">{{ config('app.name', 'HelpOfAi Studio') }}</h1>
                <div class="brand-subtitle">Enterprise AI Content Production Workspace</div>
            </div>

            <!-- Email Body Content -->
            <div class="email-body">
                <h2 class="email-heading">{{ $renderedHeading }}</h2>
                
                <div class="email-text">{!! nl2br(e($renderedBody)) !!}</div>

                @if (!empty($renderedActionUrl) && !empty($renderedActionText))
                    <div class="btn-wrapper">
                        <a href="{{ $renderedActionUrl }}" class="action-button" target="_blank">{{ $renderedActionText }} &rarr;</a>
                    </div>
                @endif
            </div>

            <!-- Footer Section -->
            <div class="email-footer">
                <p style="margin: 0 0 6px 0;">This email was sent to you because an account or activity was registered on <a href="{{ config('app.url', url('/')) }}" target="_blank">{{ config('app.name', 'HelpOfAi Studio') }}</a>.</p>
                <p style="margin: 0 0 6px 0;">If you have questions or need assistance, visit our <a href="{{ config('app.url', url('/')) }}" target="_blank">Help Center</a> or email <a href="mailto:support@helpofai.com">support@helpofai.com</a>.</p>
                <div class="security-badge">🔒 End-To-End Authenticated &bull; High-Throughput Notification Gateway</div>
                <p style="margin: 12px 0 0 0; font-size: 10px; color: #475569;">&copy; {{ date('Y') }} {{ config('app.name', 'HelpOfAi Studio') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
