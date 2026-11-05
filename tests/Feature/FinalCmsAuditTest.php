<?php

namespace Tests\Feature;

use App\Models\{Admin,CmsPage,CmsSection,CmsItem,CmsMedia};
use App\Services\WebsiteImporter;
use Illuminate\Support\Facades\{DB,Hash,Storage};
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FinalCmsAuditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array','cache.default'=>'array']);
        DB::purge('sqlite');
        $this->artisan('migrate',['--path'=>['database/migrations/2026_11_05_000000_ensure_cms_admin_auth_tables.php','database/migrations/2026_11_05_000001_create_website_cms_tables.php'],'--force'=>true])->assertExitCode(0);
        app(WebsiteImporter::class)->import();
        $this->actingAs(Admin::create(['name'=>'Audit owner','email'=>'audit@example.test','password'=>Hash::make('independent-audit-only'),'role_id'=>0]),'admin');
    }

    private function payload($record,array $changes=[]): array
    {
        return array_replace(['label'=>$record->label,'position'=>$record->position,'is_visible'=>1,'content'=>$record->content],$changes);
    }

    public function test_invalid_or_reserved_page_urls_are_rejected_without_creating_redirects(): void
    {
        $page=CmsPage::where('key','about')->firstOrFail();
        foreach(['//example.test','/about','a/../b','a//b','custom?x=1','index.html','admin','login','appointment','website/assets/test','password/reset','services.html?x=1'] as $slug){
            $this->putJson(route('cms.pages.update',$page),['title'=>$page->title,'slug'=>$slug,'is_visible'=>1])->assertUnprocessable();
            $this->assertSame('about.html',$page->fresh()->slug);
        }
        $this->assertDatabaseCount('cms_redirects',0);
        $this->get('/service-details.html?service[]=exam')->assertNotFound();
        $this->get('/article-details.html?article[]=1')->assertNotFound();
        $this->put(route('cms.pages.update',$page),['title'=>$page->title,'slug'=>'0','is_visible'=>1])->assertSessionHasNoErrors();
        $this->get('/about.html')->assertRedirect('/0');$this->get('/0')->assertOk();
    }

    public function test_template_copy_resets_seo_and_published_copy_is_accessible(): void
    {
        $source=CmsPage::where('key','about')->firstOrFail();$source->update(['seo'=>['canonical'=>'https://example.test/source','title'=>'Source override','noindex'=>true]]);
        $this->post(route('cms.pages.store'),['template_id'=>$source->id,'title'=>'Independent new page','slug'=>'audit-new-page'])->assertSessionHasNoErrors();
        $copy=CmsPage::where('slug','audit-new-page')->firstOrFail();$copy->update(['is_visible'=>true]);
        $this->get('/audit-new-page')->assertOk()->assertSee('<title>Independent new page</title>',false)->assertDontSee('https://example.test/source',false);
    }

    public function test_referenced_media_cannot_be_deleted_when_stored_as_an_absolute_url_or_with_query(): void
    {
        Storage::fake('public');
        $this->post(route('cms.media.store'),['file'=>UploadedFile::fake()->image('audit-media.png')])->assertSessionHasNoErrors();
        $media=CmsMedia::where('name','audit-media.png')->firstOrFail();$section=CmsSection::where('key','index-s1')->firstOrFail();
        $key=collect($section->schema)->search(fn($field)=>$field['type']==='image');
        foreach([url($media->path),$media->path.'?v=2'] as $reference){$content=$section->content;$content[$key]=$reference;$section->update(['content'=>$content]);$this->delete(route('cms.media.destroy',$media))->assertStatus(422);Storage::disk('public')->assertExists(substr($media->path,9));}
    }

    public function test_rejected_array_values_do_not_break_the_content_editor(): void
    {
        $section=CmsSection::where('key','index-s1')->firstOrFail();$key=collect($section->schema)->search(fn($field)=>$field['type']==='text');$content=$section->content;$content[$key]=['unexpected'];
        $this->from(route('cms.sections.edit',$section))->put(route('cms.sections.update',$section),$this->payload($section,['content'=>$content]))->assertSessionHasErrors('content.'.$key);
        $this->get(route('cms.sections.edit',$section))->assertOk();
    }

    public function test_saving_unchanged_content_preserves_significant_inline_whitespace(): void
    {
        foreach([CmsSection::where('key','index-s1')->firstOrFail(),CmsSection::where('key','index-s3')->firstOrFail()->items()->firstOrFail()] as $record){
            $before=$record->content;$route=$record instanceof CmsSection?'cms.sections.update':'cms.items.update';
            $this->put(route($route,$record),$this->payload($record))->assertSessionHasNoErrors();
            $this->assertSame($before,$record->fresh()->content);
        }
    }

    public function test_legacy_profile_cannot_escalate_editor_permissions_or_set_a_raw_password(): void
    {
        $role=\App\Models\Role::create(['name'=>'Audit editor','section'=>'cms']);
        $editor=Admin::create(['name'=>'Editor','email'=>'audit-editor@example.test','password'=>Hash::make('editor-audit-password'),'role_id'=>$role->id]);$password=$editor->password;
        $this->actingAs($editor,'admin')->post('/admin/profile/update',['name'=>'Edited name','email'=>$editor->email,'role_id'=>0,'password'=>'raw-password'])->assertOk();
        $this->assertEquals($role->id,$editor->fresh()->role_id);$this->assertSame($password,$editor->fresh()->password);
        $this->get('/admin/cms/users')->assertForbidden();
    }

    public function test_every_collection_group_supports_add_edit_render_hide_sort_and_delete(): void
    {
        $matrix=[];
        foreach(CmsSection::with('items','page')->get() as $section){foreach($section->groups as $group){
            $source=$section->items->firstWhere('group_key',$group['key']);$this->assertNotNull($source,$section->key.'/'.$group['key']);
            $before=CmsItem::max('id');$this->post(route('cms.items.store',$section),['source_id'=>$source->id])->assertSessionHasNoErrors()->assertRedirect();
            $copy=CmsItem::where('id','>',$before)->where('group_key',$source->group_key)->firstOrFail();
            $field=collect($copy->schema)->search(fn($field)=>$field['type']==='text');
            $marker='AUDIT_'.$section->id.'_'.str_replace('-','_',$group['key']);$content=$copy->content;
            if($field!==false)$content[$field]=$marker;
            $this->put(route('cms.items.update',$copy),$this->payload($copy,['content'=>$content,'position'=>9876]))->assertSessionHasNoErrors();
            $copy->refresh();$this->assertSame(9876,$copy->position);
            $url=$section->page?'/'.$section->page->slug:'/';$public=$this->get($url)->assertOk();
            if($field!==false)$public->assertSee($marker,false);
            $this->put(route('cms.items.update',$copy),$this->payload($copy,['is_visible'=>0]))->assertSessionHasNoErrors();
            if($field!==false)$this->get($url)->assertDontSee($marker,false);
            $this->delete(route('cms.items.destroy',$copy))->assertRedirect();$this->assertDatabaseMissing('cms_items',['id'=>$copy->id]);
            $matrix[]=['section'=>$section->key,'group'=>$group['key'],'label'=>$group['label'],'nested'=>(bool)$group['parent_group'],'public_url'=>$url,'text_render_checked'=>$field!==false,'crud'=>'passed'];
        }}
        file_put_contents(storage_path('app/cms-verification/final-crud-matrix.json'),json_encode($matrix,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    }

    public function test_every_section_text_field_is_editable_and_reaches_its_public_page(): void
    {
        $coverage=[];
        foreach(CmsSection::with('page')->get() as $section){
            $original=$section->content;$changed=$original;$markers=[];
            foreach($section->schema as $key=>$field){if($field['type']==='text'){$changed[$key]='SECTION_AUDIT_'.$section->id.'_'.$key;$markers[]=$changed[$key];}}
            if(!$markers)continue;
            $this->put(route('cms.sections.update',$section),$this->payload($section,['content'=>$changed]))->assertSessionHasNoErrors();
            $url=$section->page?'/'.$section->page->slug:'/';$response=$this->get($url)->assertOk();
            foreach($markers as $marker)$response->assertSee($marker,false);
            $coverage[]=['section'=>$section->key,'url'=>$url,'fields'=>count($markers)];
            $section->update(['content'=>$original]);
        }
        file_put_contents(storage_path('app/cms-verification/final-section-coverage.json'),json_encode($coverage,JSON_PRETTY_PRINT));
    }

    public function test_owner_can_create_update_and_delete_editors_and_editors_cannot_create_owners(): void
    {
        \App\Models\Role::create(['name'=>'Website content editor','section'=>'manage_staffs']);
        $this->post(route('cms.users.store'),['name'=>'Created editor','email'=>'created-editor@example.test','password'=>'audit-password-12345','access'=>'editor'])->assertSessionHasNoErrors();
        $editor=Admin::where('email','created-editor@example.test')->firstOrFail();$this->assertTrue(Hash::check('audit-password-12345',$editor->password));$this->assertFalse($editor->IsSuper());$this->assertTrue($editor->sectionCheck('cms'));
        $this->put(route('cms.users.update',$editor),['name'=>'Updated editor','email'=>$editor->email,'password'=>'new-audit-password-123'])->assertSessionHasNoErrors();$this->assertTrue(Hash::check('new-audit-password-123',$editor->fresh()->password));
        $owner=auth('admin')->user();$this->actingAs($editor,'admin')->post(route('cms.users.store'),['name'=>'Escalation','email'=>'forbidden@example.test','password'=>'audit-password-12345','access'=>'owner'])->assertForbidden();
        $this->actingAs($owner,'admin')->delete(route('cms.users.destroy',$editor))->assertRedirect();$this->assertDatabaseMissing('admins',['id'=>$editor->id]);
        $this->get('/admin/cms/pages?page=2')->assertOk()->assertSee('article-details.html?article=8',false);
    }

    public function test_login_is_rate_limited_and_content_permissions_are_not_granted_to_public_users(): void
    {
        $this->post('/admin/logout')->assertRedirect();
        for($i=0;$i<5;$i++)$this->post('/admin/login',['email'=>'unknown@example.test','password'=>'wrong-audit-password'])->assertRedirect();
        $this->post('/admin/login',['email'=>'unknown@example.test','password'=>'wrong-audit-password'])->assertStatus(429);
        foreach(['/admin/cms','/admin/cms/pages','/admin/cms/globals','/admin/cms/media','/admin/cms/submissions','/admin/cms/users'] as $url)$this->get($url)->assertRedirect('/admin/login');
        $this->put(route('cms.sections.update',CmsSection::first()),['label'=>'Forbidden'])->assertRedirect('/admin/login');
    }

    public function test_parent_and_page_deletion_cascade_and_hidden_parent_hides_its_children(): void
    {
        $section=CmsSection::where('key','global-footer')->with('items')->firstOrFail();$group=collect($section->groups)->first(fn($g)=>$g['parent_group']!==null);$child=$section->items->firstWhere('group_key',$group['key']);$parent=$child->parent;
        $content=$child->content;$text=collect($child->schema)->search(fn($f)=>$f['type']==='text');$content[$text]='NESTED_VISIBILITY_AUDIT';$this->put(route('cms.items.update',$child),$this->payload($child,['content'=>$content]))->assertSessionHasNoErrors();$this->get('/')->assertSee('NESTED_VISIBILITY_AUDIT');
        $this->put(route('cms.items.update',$parent),$this->payload($parent,['is_visible'=>0]))->assertSessionHasNoErrors();$this->get('/')->assertDontSee('NESTED_VISIBILITY_AUDIT');$this->delete(route('cms.items.destroy',$parent))->assertRedirect();$this->assertDatabaseMissing('cms_items',['id'=>$child->id]);
        $page=CmsPage::where('key','about')->firstOrFail();$sections=$page->sections->pluck('id');$this->delete(route('cms.pages.destroy',$page))->assertRedirect();foreach($sections as $id){$this->assertDatabaseMissing('cms_sections',['id'=>$id]);$this->assertDatabaseMissing('cms_items',['section_id'=>$id]);}
    }

    public function test_disabling_global_settings_disables_its_overrides(): void
    {
        $settings=CmsSection::where('key','global-site')->firstOrFail();$content=$settings->content;$content['email']='override@example.test';
        $this->put(route('cms.sections.update',$settings),$this->payload($settings,['content'=>$content]))->assertSessionHasNoErrors();$this->get('/contact.html')->assertSee('override@example.test');
        $this->put(route('cms.sections.update',$settings),$this->payload($settings->fresh(),['is_visible'=>0]))->assertSessionHasNoErrors();$this->get('/contact.html')->assertDontSee('override@example.test')->assertSee('info@drmohamedbaz.com');
    }
}
