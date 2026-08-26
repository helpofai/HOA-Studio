<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Mail & Notifications Feature Test
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

namespace Tests\Feature;

use App\Features\Admin\Livewire\AdminMailNotificationPage;
use App\Features\Admin\Livewire\NotificationBell;
use App\Features\Admin\Notifications\GeneralSystemNotification;
use App\Features\Admin\Notifications\SecurityAlertNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminMailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_mail_notifications_control_panel()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.mail-notifications'));
        $response->assertStatus(200);
        $response->assertSee('hoa-mail-notifications-container', false);
    }

    public function test_non_admin_cannot_access_mail_notifications_page()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.mail-notifications'));
        $response->assertStatus(403);
    }

    public function test_admin_can_save_smtp_and_gateway_settings()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(AdminMailNotificationPage::class)
            ->set('mail_mailer', 'smtp')
            ->set('mail_host', 'smtp.sendgrid.net')
            ->set('mail_port', 587)
            ->set('mail_username', 'apikey')
            ->set('mail_password', 'SG.test-secret-key')
            ->set('mail_encryption', 'tls')
            ->set('mail_from_address', 'notifications@helpofai.com')
            ->set('mail_from_name', 'HOA Core')
            ->call('saveMailConfig')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'key' => 'mail_host',
            'value' => 'smtp.sendgrid.net',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'mail_from_address',
            'value' => 'notifications@helpofai.com',
        ]);
    }

    public function test_admin_can_send_broadcast_announcement()
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $user1 = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $user2 = User::factory()->create(['role' => 'user', 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(AdminMailNotificationPage::class)
            ->set('broadcast_title', 'System Maintenance Announcement')
            ->set('broadcast_message', 'We are upgrading our GPU clusters tonight.')
            ->set('broadcast_type', 'announcement')
            ->set('broadcast_target', 'all')
            ->set('broadcast_send_email', false)
            ->call('sendBroadcast')
            ->assertHasNoErrors();

        Notification::assertSentTo([$user1, $user2, $admin], GeneralSystemNotification::class);
    }

    public function test_notification_bell_marks_as_read_and_clears()
    {
        $user = User::factory()->create(['role' => 'user']);

        $user->notify(new GeneralSystemNotification(
            title: "Test In-App Alert",
            description: "Testing notification bell component",
            type: "info"
        ));

        $this->assertEquals(1, $user->unreadNotifications()->count());

        $notificationId = $user->unreadNotifications()->first()->id;

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('markAsRead', $notificationId)
            ->assertHasNoErrors();

        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('clearAll')
            ->assertHasNoErrors();

        $this->assertEquals(0, $user->fresh()->notifications()->count());
    }

    public function test_security_alert_notification_delivers_proper_structure()
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@helpofai.com']);

        $alert = new SecurityAlertNotification(
            title: "Brute Force Attack Blocked",
            description: "Aggressive IP automatically blocked.",
            severity: "critical",
            actionUrl: url('/admin/auth-settings'),
            actionText: "Manage IP Blacklist",
            metadata: ['ip' => '198.51.100.99', 'timestamp' => now()->toIso8601String()]
        );

        $admin->notify($alert);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => SecurityAlertNotification::class,
        ]);
    }

    public function test_admin_can_customize_and_preview_email_template()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(AdminMailNotificationPage::class)
            ->call('selectTemplate', 'welcome_registration')
            ->set('template_subject', 'Custom VIP Welcome to {app_name}')
            ->set('template_heading', 'Hello {user_name}!')
            ->set('template_body', 'Welcome aboard your custom workspace!')
            ->set('template_action_text', 'Start Creating')
            ->set('template_action_url', 'https://helpofai.com/vip')
            ->call('saveTemplate')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'key' => 'mail_tpl_welcome_registration_subject',
            'value' => 'Custom VIP Welcome to {app_name}',
        ]);

        // Test Template Compilation & Rendering
        $compiled = \App\Features\Admin\Services\MailTemplateService::getCompiledTemplate('welcome_registration');
        $this->assertEquals('Custom VIP Welcome to {app_name}', $compiled['subject']);

        $rendered = \App\Features\Admin\Services\MailTemplateService::render($compiled['subject'], [
            '{app_name}' => 'HelpOfAi Studio',
        ]);
        $this->assertEquals('Custom VIP Welcome to HelpOfAi Studio', $rendered);

        // Test Reset to Factory Defaults
        Livewire::actingAs($admin)
            ->test(AdminMailNotificationPage::class)
            ->call('selectTemplate', 'welcome_registration')
            ->call('resetTemplateToDefault')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('settings', [
            'key' => 'mail_tpl_welcome_registration_subject',
        ]);
    }
}
