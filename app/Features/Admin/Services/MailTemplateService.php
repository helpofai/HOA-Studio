<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - System Email Template Registry
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

namespace App\Features\Admin\Services;

use Illuminate\Support\Facades\DB;

class MailTemplateService
{
    /**
     * Get all registered system email templates with default values and available placeholders.
     *
     * @return array<string, array{
     *     name: string,
     *     category: string,
     *     icon: string,
     *     description: string,
     *     default_subject: string,
     *     default_heading: string,
     *     default_body: string,
     *     default_action_text: string,
     *     default_action_url: string,
     *     placeholders: array<string, string>
     * }>
     */
    public static function getTemplates(): array
    {
        return [
            'login_detected' => [
                'name' => 'Login Detected (Security Alert)',
                'category' => 'Security & Auth',
                'icon' => '🛡️',
                'description' => 'Dispatched when a new login occurs from a new IP, device, or geographic location.',
                'default_subject' => '🚨 Security Notice: New Login Detected on {app_name}',
                'default_heading' => 'New Login Detected on Your Account',
                'default_body' => "Hello {user_name},\n\nWe detected a successful login to your {app_name} account.\n\n• Device / Browser: {user_agent}\n• IP Address: {ip_address}\n• Incident Time: {timestamp}\n• Location / Network: {location}\n\nIf this was you, no action is needed. If you did NOT initiate this login, please secure your account immediately by changing your password.",
                'default_action_text' => 'Review Account Security',
                'default_action_url' => '{app_url}/profile',
                'placeholders' => [
                    '{user_name}' => 'User display name',
                    '{user_email}' => 'User email address',
                    '{app_name}' => 'Platform name',
                    '{app_url}' => 'Platform root URL',
                    '{ip_address}' => 'Client IP address',
                    '{user_agent}' => 'Browser and operating system',
                    '{timestamp}' => 'Date and time of login',
                    '{location}' => 'Estimated city/network',
                ],
            ],

            'welcome_registration' => [
                'name' => 'Welcome Email (New Registration)',
                'category' => 'Onboarding & Auth',
                'icon' => '✨',
                'description' => 'Dispatched to newly registered users immediately after account creation.',
                'default_subject' => '🎉 Welcome to {app_name} — Your AI Content Workspace',
                'default_heading' => 'Welcome to HelpOfAi Studio!',
                'default_body' => "Hello {user_name},\n\nWelcome to {app_name}! Your account has been activated with **{plan_name}** tier and **{monthly_words} monthly AI words**.\n\nYou can now:\n• Generate high-impact copywriting and transform content with 15+ AI tools.\n• Ingest knowledge base files and policy documents with RAG vector search.\n• Train and synthesize custom Brand Voices for your business.\n• Export to Word (.docx), Markdown, HTML, or share password-gated public links.",
                'default_action_text' => 'Open My Workspace',
                'default_action_url' => '{app_url}/dashboard',
                'placeholders' => [
                    '{user_name}' => 'User display name',
                    '{user_email}' => 'User email address',
                    '{app_name}' => 'Platform name',
                    '{app_url}' => 'Platform root URL',
                    '{plan_name}' => 'User subscription plan (Starter / Pro / Enterprise)',
                    '{monthly_words}' => 'Initial monthly word quota',
                ],
            ],

            'forgot_password' => [
                'name' => 'Password Reset (Forgot Password)',
                'category' => 'Security & Auth',
                'icon' => '🔑',
                'description' => 'Dispatched when a user requests a password recovery link.',
                'default_subject' => '🔒 Reset Your {app_name} Password',
                'default_heading' => 'Password Reset Request',
                'default_body' => "Hello {user_name},\n\nYou are receiving this email because we received a password reset request for your {app_name} account.\n\nThis password reset link will expire in {expire_minutes} minutes. If you did not request a password reset, no further action is required.",
                'default_action_text' => 'Reset Password',
                'default_action_url' => '{reset_url}',
                'placeholders' => [
                    '{user_name}' => 'User display name',
                    '{user_email}' => 'User email address',
                    '{app_name}' => 'Platform name',
                    '{reset_url}' => 'Secure one-time password reset link',
                    '{expire_minutes}' => 'Token expiration in minutes',
                ],
            ],

            'email_verification' => [
                'name' => 'Email Address Verification',
                'category' => 'Onboarding & Auth',
                'icon' => '✉️',
                'description' => 'Dispatched to confirm and verify user email ownership.',
                'default_subject' => '📬 Verify Your Email Address for {app_name}',
                'default_heading' => 'Verify Your Email Address',
                'default_body' => "Hello {user_name},\n\nPlease click the button below to verify your email address and unlock full platform capabilities on {app_name}.\n\nIf you did not create an account, no further action is required.",
                'default_action_text' => 'Verify Email Address',
                'default_action_url' => '{verify_url}',
                'placeholders' => [
                    '{user_name}' => 'User display name',
                    '{user_email}' => 'User email address',
                    '{app_name}' => 'Platform name',
                    '{verify_url}' => 'Signed verification URL',
                ],
            ],

            'account_details' => [
                'name' => 'Account Details & Credentials Notice',
                'category' => 'Account Management',
                'icon' => '👤',
                'description' => 'Dispatched when an administrator creates an account or issues updated credentials.',
                'default_subject' => '🔐 Your {app_name} Account Credentials',
                'default_heading' => 'Your Account Information',
                'default_body' => "Hello {user_name},\n\nAn account has been created/updated for you on {app_name}.\n\n• Login Email: **{user_email}**\n• Assigned Role: **{user_role}**\n• Assigned Plan: **{plan_name}** ({monthly_words} words)\n• Temporary Password: **{temporary_password}**\n\nPlease log in and change your password immediately from your profile settings.",
                'default_action_text' => 'Log In to Studio',
                'default_action_url' => '{app_url}/login',
                'placeholders' => [
                    '{user_name}' => 'User display name',
                    '{user_email}' => 'User email address',
                    '{temporary_password}' => 'Assigned password',
                    '{user_role}' => 'User role (User / Admin)',
                    '{plan_name}' => 'Assigned subscription plan',
                    '{monthly_words}' => 'Monthly word quota',
                    '{app_name}' => 'Platform name',
                    '{app_url}' => 'Platform root URL',
                ],
            ],

            'quota_low_warning' => [
                'name' => 'Quota Low & Depletion Alert',
                'category' => 'Billing & Quotas',
                'icon' => '⚡',
                'description' => 'Dispatched when a user has consumed >= 90% of their monthly AI generation limit.',
                'default_subject' => '⚠️ Action Required: Your AI Word Quota is Running Low on {app_name}',
                'default_heading' => 'AI Word Quota Running Low',
                'default_body' => "Hello {user_name},\n\nYou have used **{used_percentage}%** of your monthly AI quota on {app_name}.\n\n• Words Remaining: **{remaining_words}** / {total_words}\n• Current Plan: **{plan_name}**\n\nTo prevent interruption of AI transformations and editor completions, you can upgrade your plan or configure your own API keys (BYOK).",
                'default_action_text' => 'Manage Quota & Plans',
                'default_action_url' => '{app_url}/dashboard/usage',
                'placeholders' => [
                    '{user_name}' => 'User display name',
                    '{remaining_words}' => 'Unused words remaining',
                    '{total_words}' => 'Total monthly quota',
                    '{used_percentage}' => 'Percentage of quota used',
                    '{plan_name}' => 'User subscription plan',
                    '{app_name}' => 'Platform name',
                    '{app_url}' => 'Platform root URL',
                ],
            ],

            'plan_upgraded' => [
                'name' => 'Plan Upgraded / Word Quota Granted',
                'category' => 'Billing & Quotas',
                'icon' => '💎',
                'description' => 'Dispatched to user when an admin or billing upgrade increases their subscription tier or word quota.',
                'default_subject' => '🚀 Congratulations! Your {app_name} Plan has been Upgraded to {new_plan}',
                'default_heading' => 'Your Plan has Been Upgraded!',
                'default_body' => "Hello {user_name},\n\nGreat news! Your account on {app_name} has been upgraded.\n\n• New Tier: **{new_plan}**\n• Monthly AI Word Quota: **{monthly_words} words**\n• Bonus Words Added: **+{bonus_words} words**\n\nEnjoy accelerated AI generation speeds, higher concurrency limits, and expanded vector knowledge storage.",
                'default_action_text' => 'Go to My Workspace',
                'default_action_url' => '{app_url}/dashboard',
                'placeholders' => [
                    '{user_name}' => 'User display name',
                    '{new_plan}' => 'New plan name (e.g. Pro, Enterprise)',
                    '{monthly_words}' => 'New monthly word quota',
                    '{bonus_words}' => 'Bonus words granted',
                    '{app_name}' => 'Platform name',
                    '{app_url}' => 'Platform root URL',
                ],
            ],

            'account_banned' => [
                'name' => 'Account Suspension / Ban Notice',
                'category' => 'Security & Governance',
                'icon' => '🚫',
                'description' => 'Dispatched to a user when their account is suspended or banned by an administrator.',
                'default_subject' => '⚠️ Important: Your {app_name} Account has been Suspended',
                'default_heading' => 'Account Suspension Notice',
                'default_body' => "Hello {user_name},\n\nYour account on {app_name} has been temporarily or permanently suspended.\n\n• Reason for Suspension: {ban_reason}\n• Effective Date: {timestamp}\n\nDuring suspension, access to document editing, AI transformations, and workspace features is restricted. If you believe this was an error, please reach out to our support team.",
                'default_action_text' => 'Contact Support',
                'default_action_url' => 'mailto:support@helpofai.com',
                'placeholders' => [
                    '{user_name}' => 'User display name',
                    '{ban_reason}' => 'Reason for suspension',
                    '{timestamp}' => 'Date of action',
                    '{app_name}' => 'Platform name',
                    '{app_url}' => 'Platform root URL',
                ],
            ],

            'account_unbanned' => [
                'name' => 'Account Restored / Unbanned Notice',
                'category' => 'Security & Governance',
                'icon' => '✅',
                'description' => 'Dispatched to a user when an administrator restores their suspended account.',
                'default_subject' => '✨ Good News: Your {app_name} Account has been Re-Activated',
                'default_heading' => 'Account Re-Activated',
                'default_body' => "Hello {user_name},\n\nYour account on {app_name} has been reviewed and successfully restored to active standing.\n\nYou may now log in and continue utilizing your workspace, documents, and AI tools as normal.",
                'default_action_text' => 'Log In to Studio',
                'default_action_url' => '{app_url}/login',
                'placeholders' => [
                    '{user_name}' => 'User display name',
                    '{app_name}' => 'Platform name',
                    '{app_url}' => 'Platform root URL',
                ],
            ],

            'admin_new_user_alert' => [
                'name' => 'Admin Notice: New User Registration Alert',
                'category' => 'Admin Notifications',
                'icon' => '👥',
                'description' => 'Dispatched to administrators when a new user signs up on the platform.',
                'default_subject' => '👥 [Admin Alert] New User Registration: {user_name}',
                'default_heading' => 'New User Registered on Platform',
                'default_body' => "Hello Administrator,\n\nA new user account has just been registered on {app_name}.\n\n• User Name: **{user_name}**\n• User Email: **{user_email}**\n• Assigned Plan: **{plan_name}** ({monthly_words} words)\n• Registered IP: **{ip_address}**\n• Registration Time: {timestamp}",
                'default_action_text' => 'Manage User in Admin Panel',
                'default_action_url' => '{app_url}/admin/users',
                'placeholders' => [
                    '{user_name}' => 'New user name',
                    '{user_email}' => 'New user email',
                    '{plan_name}' => 'Assigned initial plan',
                    '{monthly_words}' => 'Initial word quota',
                    '{ip_address}' => 'Origin IP address',
                    '{timestamp}' => 'Registration timestamp',
                    '{app_name}' => 'Platform name',
                    '{app_url}' => 'Platform root URL',
                ],
            ],

            'admin_ip_blocked_alert' => [
                'name' => 'Admin Notice: Malicious IP Auto-Blacklisted',
                'category' => 'Admin Notifications',
                'icon' => '🛡️',
                'description' => 'Dispatched to administrators when brute-force rate limiters automatically blacklist an aggressive IP.',
                'default_subject' => '🚨 [Security Alert] IP Address Auto-Blacklisted: {ip_address}',
                'default_heading' => 'Malicious IP Auto-Blacklisted',
                'default_body' => "Hello Administrator,\n\nOur automated security engine detected repetitive brute-force failures and has blacklisted the offending network.\n\n• Blocked IP Address: **{ip_address}**\n• Targeted Account Email: **{target_email}**\n• Failure Hits: **{failure_count} attempts**\n• Auto-Block Duration: **{duration_hours} hours**\n• Action Timestamp: {timestamp}",
                'default_action_text' => 'Review Blocked IP Blacklist',
                'default_action_url' => '{app_url}/admin/auth-settings',
                'placeholders' => [
                    '{ip_address}' => 'Blacklisted IP address',
                    '{target_email}' => 'Targeted email account',
                    '{failure_count}' => 'Number of failed attempts',
                    '{duration_hours}' => 'Block duration in hours',
                    '{timestamp}' => 'Incident timestamp',
                    '{app_name}' => 'Platform name',
                    '{app_url}' => 'Platform root URL',
                ],
            ],

            'system_maintenance' => [
                'name' => 'System Maintenance & Update Notice',
                'category' => 'System & Announcements',
                'icon' => '🛠️',
                'description' => 'Dispatched to inform users or admins of scheduled platform maintenance or downtime windows.',
                'default_subject' => '🛠️ Notice: Scheduled System Maintenance on {app_name}',
                'default_heading' => 'Upcoming Scheduled Maintenance',
                'default_body' => "Hello {user_name},\n\nWe will be performing a scheduled infrastructure upgrade to enhance AI processing throughput and system performance.\n\n• Maintenance Window: **{maintenance_window}**\n• Expected Downtime: **{estimated_duration}**\n• Impacted Services: {impacted_services}\n\nAll existing documents, versions, and workspace data will remain secure. Thank you for your patience!",
                'default_action_text' => 'Check System Status',
                'default_action_url' => '{app_url}/dashboard',
                'placeholders' => [
                    '{user_name}' => 'User display name',
                    '{maintenance_window}' => 'Scheduled start and end time',
                    '{estimated_duration}' => 'Estimated duration (e.g. 15-30 minutes)',
                    '{impacted_services}' => 'Impacted features (e.g. AI Stream, Document Ingestion)',
                    '{app_name}' => 'Platform name',
                    '{app_url}' => 'Platform root URL',
                ],
            ],
        ];
    }

    /**
     * Get compiled template by key with database overrides merged.
     *
     * @param string $templateKey
     * @return array{
     *     subject: string,
     *     heading: string,
     *     body: string,
     *     action_text: string,
     *     action_url: string
     * }
     */
    public static function getCompiledTemplate(string $templateKey): array
    {
        $templates = self::getTemplates();
        $default = $templates[$templateKey] ?? [
            'default_subject' => 'Notification from ' . config('app.name'),
            'default_heading' => 'System Notification',
            'default_body' => 'You have received a new notification.',
            'default_action_text' => 'Open Application',
            'default_action_url' => config('app.url'),
        ];

        $overrides = DB::table('settings')
            ->where('key', 'like', "mail_tpl_{$templateKey}_%")
            ->pluck('value', 'key');

        return [
            'subject' => $overrides["mail_tpl_{$templateKey}_subject"] ?? $default['default_subject'],
            'heading' => $overrides["mail_tpl_{$templateKey}_heading"] ?? $default['default_heading'],
            'body' => $overrides["mail_tpl_{$templateKey}_body"] ?? $default['default_body'],
            'action_text' => $overrides["mail_tpl_{$templateKey}_action_text"] ?? $default['default_action_text'],
            'action_url' => $overrides["mail_tpl_{$templateKey}_action_url"] ?? $default['default_action_url'],
        ];
    }

    /**
     * Render template string with variable replacements.
     *
     * @param string $content
     * @param array<string, string> $variables
     * @return string
     */
    public static function render(string $content, array $variables = []): string
    {
        $globalVariables = [
            '{app_name}' => config('app.name', 'HelpOfAi Studio'),
            '{app_url}' => config('app.url', url('/')),
            '{timestamp}' => now()->format('Y-m-d H:i:s T'),
        ];

        $merged = array_merge($globalVariables, $variables);

        return str_replace(array_keys($merged), array_values($merged), $content);
    }
}
