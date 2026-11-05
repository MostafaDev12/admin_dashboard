<?php

namespace Tests\Feature;

use App\Models\CmsSection;
use App\Services\CmsMessages;
use App\Services\WebsiteImporter;

class ManagedMessagesTest extends CmsFeatureTestCase
{
    private function section_(): CmsSection
    {
        return $this->section(CmsMessages::SECTION);
    }

    private function setMessages(array $changes, ?int $visible = null): void
    {
        $section = $this->section_();
        $this->put(route('cms.sections.update', $section), $this->payload($section, array_filter([
            'content' => array_replace($section->content, $changes),
            'is_visible' => $visible,
        ], fn ($value) => $value !== null)))->assertSessionHasNoErrors();
        $this->app->forgetInstance(CmsMessages::class);
    }

    private function browserMessages(string $url = '/'): array
    {
        preg_match('~window\.websiteMessages=(\{.*?\});</script>~', $this->publicHtml($url), $match);

        return json_decode($match[1], true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_defaults_keep_the_original_wording(): void
    {
        $this->postJson('/appointment', ['name' => 'Patient', 'phone' => '+201001234567'])->assertCreated()->assertJsonPath('message', 'تم إرسال طلبك بنجاح. سنتواصل معك لتأكيد الموعد.');
        $messages = $this->browserMessages();
        $this->assertSame('تعذر إرسال الطلب.', $messages['submit_error']);
        $this->assertSame('تعذر الاتصال. يرجى المحاولة مرة أخرى.', $messages['network_error']);
    }

    public function test_every_message_is_listed_and_editable_in_the_dashboard(): void
    {
        $this->asOwner();
        $this->get(route('cms.messages'))->assertRedirect(route('cms.sections.edit', $this->section_()));
        $screen = $this->get(route('cms.sections.edit', $this->section_()))->assertOk();
        foreach ($this->section_()->schema as $key => $field) {
            $this->assertSame('message', $field['type']);
            $screen->assertSee($field['label']);
            $screen->assertSee('name="content['.$key.']"', false);
        }
        $this->get(route('cms.globals'))->assertOk()->assertSee('Form &amp; system messages', false);
    }

    public function test_edited_success_messages_reach_json_and_redirect_responses(): void
    {
        $this->asOwner()->setMessages(['appointment_success' => 'EDITED_SUCCESS']);
        $this->postJson('/appointment', ['name' => 'Patient', 'phone' => '+201001234567'])->assertCreated()->assertJsonPath('message', 'EDITED_SUCCESS');
        $this->from('/contact.html')->post('/appointment', ['name' => 'Patient', 'phone' => '+201001234567'])->assertRedirect('/contact.html');
        $this->get('/contact.html')->assertSee('EDITED_SUCCESS');
    }

    public function test_edited_browser_messages_reach_the_form_script(): void
    {
        $this->asOwner()->setMessages(['submit_error' => 'EDITED_SUBMIT', 'network_error' => 'EDITED_NETWORK', 'session_expired' => 'EDITED_SESSION', 'rate_limited' => 'EDITED_RATE']);
        $messages = $this->browserMessages('/contact.html');
        $this->assertSame('EDITED_SUBMIT', $messages['submit_error']);
        $this->assertSame('EDITED_NETWORK', $messages['network_error']);
        $this->assertSame('EDITED_SESSION', $messages['session_expired']);
        $this->assertSame('EDITED_RATE', $messages['rate_limited']);
        // Server-only wording does not need to be sent to every browser.
        $this->assertArrayNotHasKey('validation_name_required', $messages);
    }

    public function test_edited_validation_wording_is_used_while_the_rules_stay_fixed(): void
    {
        $this->asOwner()->setMessages(['validation_name_required' => 'EDITED_NAME', 'validation_phone_invalid' => 'EDITED_PHONE', 'validation_date_past' => 'EDITED_DATE', 'validation_email_invalid' => 'EDITED_EMAIL']);
        $response = $this->postJson('/appointment', ['phone' => 'not a phone', 'email' => 'nope', 'date' => '2000-01-01'])->assertUnprocessable();
        $response->assertJsonPath('errors.name.0', 'EDITED_NAME');
        $response->assertJsonPath('errors.phone.0', 'EDITED_PHONE');
        $response->assertJsonPath('errors.date.0', 'EDITED_DATE');
        $response->assertJsonPath('errors.email.0', 'EDITED_EMAIL');
        $this->assertDatabaseCount('cms_submissions', 0);
    }

    public function test_messages_cannot_break_out_of_the_script_or_inject_markup(): void
    {
        $attack = '</script><script>alert(1)</script><img src=x onerror=alert(2)>';
        $this->asOwner()->setMessages(['network_error' => $attack, 'appointment_success' => $attack]);
        $html = $this->publicHtml('/contact.html');
        $this->assertStringNotContainsString('<script>alert(1)', $html);
        $this->assertSame($attack, $this->browserMessages('/contact.html')['network_error']);

        $this->from('/contact.html')->post('/appointment', ['name' => 'Patient', 'phone' => '+201001234567']);
        $html = $this->publicHtml('/contact.html');
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('&lt;img src=x', $html);
    }

    public function test_empty_or_hidden_messages_fall_back_to_the_defaults(): void
    {
        $this->asOwner()->setMessages(['submit_error' => '']);
        $this->assertSame('تعذر إرسال الطلب.', $this->browserMessages()['submit_error']);
        $this->setMessages(['submit_error' => 'EDITED_SUBMIT'], 0);
        $this->assertSame('تعذر إرسال الطلب.', $this->browserMessages()['submit_error']);
    }

    public function test_managed_copy_is_never_overwritten_by_imports_and_new_messages_are_added(): void
    {
        $this->asOwner()->setMessages(['submit_error' => 'KEEP_THIS']);
        $section = $this->section_();
        $content = $section->content;
        unset($content['error_404']);
        $section->update(['content' => $content]);
        app(WebsiteImporter::class)->import();
        $this->assertSame('KEEP_THIS', $section->fresh()->content['submit_error']);
        $this->assertSame('الصفحة غير موجودة.', $section->fresh()->content['error_404']);
    }

    public function test_public_scripts_hold_no_hardcoded_customer_copy(): void
    {
        foreach (['public/website/assets/js/forms.js', 'resources/js/website-forms.js'] as $file) {
            $source = file_get_contents(base_path($file));
            $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', $source, $file);
            foreach (['session_expired', 'rate_limited', 'submit_error', 'network_error'] as $key) {
                $this->assertStringContainsString('messages.'.$key, $source, $file);
            }
        }
        $this->assertSame(file_get_contents(base_path('resources/js/website-forms.js')), file_get_contents(public_path('website/assets/js/forms.js')));
        foreach (['app/Http/Controllers/WebsiteController.php', 'resources/views/website/page.blade.php'] as $file) {
            $this->assertDoesNotMatchRegularExpression('/\p{Arabic}/u', file_get_contents(base_path($file)), $file);
        }
    }

    public function test_error_pages_use_managed_copy(): void
    {
        $this->asOwner()->setMessages(['error_404' => 'EDITED_NOT_FOUND']);
        $this->get('/this-page-does-not-exist')->assertNotFound()->assertSee('EDITED_NOT_FOUND');
        $this->app['env'] = 'local';
        $this->post('/appointment', ['name' => 'x', 'phone' => '1234567'])->assertStatus(419)->assertSee(app(CmsMessages::class)->get('error_419'));
        $this->app['env'] = 'testing';
    }

    public function test_messages_are_available_when_the_database_is_not(): void
    {
        $messages = new CmsMessages();
        config(['database.connections.sqlite.database' => storage_path('app/definitely-missing/nothing.sqlite')]);
        \Illuminate\Support\Facades\DB::purge('sqlite');
        $this->assertSame('حدث خطأ غير متوقع. يرجى المحاولة لاحقًا.', $messages->get('error_500'));
    }

    public function test_anonymous_visitors_cannot_edit_messages(): void
    {
        $section = $this->section_();
        $this->put(route('cms.sections.update', $section), $this->payload($section))->assertRedirect('/admin/login');
        $this->get(route('cms.messages'))->assertRedirect('/admin/login');
    }
}
