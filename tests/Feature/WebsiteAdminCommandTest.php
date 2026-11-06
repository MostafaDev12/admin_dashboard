<?php

namespace Tests\Feature;

use App\Models\Admin;

class WebsiteAdminCommandTest extends CmsFeatureTestCase
{
    public function test_login_controls_target_the_admin_recovery_and_remember_workflows(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//input[@type="checkbox"][@name="remember"][@value="1"]')->length);
        $this->assertSame(1, $xpath->query('//a[contains(@href,"/admin/forgot")]')->length);
        $this->get('/admin/forgot')->assertRedirect('/admin/login')->assertSessionHasErrors('email');
    }

    public function test_existing_account_is_unchanged_when_replacement_is_declined(): void
    {
        $admin = Admin::create([
            'name' => 'Existing editor',
            'email' => 'existing@example.test',
            'password' => 'existing-hash-for-preservation-test',
            'role_id' => 7,
        ]);
        $before = $admin->fresh()->getAttributes();

        $this->artisan('website:admin', ['email' => $admin->email])
            ->expectsConfirmation('This administrator already exists. Replace its password, name and role?', 'no')
            ->expectsOutput('Existing administrator was not changed.')
            ->assertExitCode(1);

        $this->assertSame($before, $admin->fresh()->getAttributes());
    }

    public function test_invalid_email_cannot_create_an_account(): void
    {
        $before = Admin::count();
        $this->artisan('website:admin', ['email' => 'invalid'])
            ->expectsOutput('A valid email is required.')
            ->assertExitCode(1);
        $this->assertSame($before, Admin::count());
    }
}
