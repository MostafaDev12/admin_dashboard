<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PageModel;
use App\Models\Partner;
use App\Models\Role;
use App\Models\Service;
use App\Models\Slider;
use App\Support\SafeUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class LegacySecurityTest extends CmsFeatureTestCase
{
    private const MODULES = [
        'slider' => [Slider::class, 'assets/images/slider'],
        'partners' => [Partner::class, 'assets/images/partners'],
        'services' => [Service::class, 'assets/images/services'],
        'models' => [PageModel::class, 'assets/images/models'],
    ];

    private array $uploaded = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2024_08_03_000000_create_generalsettings_table.php',
            'database/migrations/2024_08_06_000000_create_sliders_table.php',
            'database/migrations/2024_08_06_000000_create_partners_table.php',
            'database/migrations/2024_08_06_000000_create_services_table.php',
            'database/migrations/2024_08_06_000000_create_models_table.php',
            'database/migrations/2024_08_07_000000_create_pagesettings_table.php',
            'database/migrations/2024_08_10_202447_add_portfolio_column_to_pagesettings_table.php',
        ], '--force' => true])->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        foreach ($this->uploaded as [$directory, $name]) {
            SafeUpload::delete($directory, $name);
        }
        parent::tearDown();
    }

    public function test_no_registered_route_points_to_a_missing_method(): void
    {
        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (str_contains($action, '@')) {
                [$class, $method] = explode('@', $action, 2);
                $this->assertTrue(method_exists($class, $method), $route->uri().' => '.$action);
            }
        }
        foreach (['admin-gs-load2', 'admin-gs-is-admin-loader', 'admin.logout', 'updateProfile', 'updatePassword', 'admin.forgot', 'admin.forgot.submit', 'admin-gs-home_video'] as $retired) {
            $this->assertFalse(Route::has($retired), $retired);
        }
    }

    public function test_no_view_or_controller_still_calls_a_retired_route(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $source = $file->getContents();
            foreach (['admin-gs-load2', 'admin-gs-is-admin-loader', "route('admin.logout')", "route('updateProfile", "route('updatePassword", 'admin.forgot'] as $retired) {
                $this->assertStringNotContainsString($retired, $source, $file->getRelativePathname());
            }
        }
    }

    public function test_state_changing_get_requests_no_longer_exist(): void
    {
        $slider = Slider::create(['title_ar' => 'keep']);
        $staff = $this->adminWithRole('slider', 'keep@example.test');
        foreach ([null, $this->owner()] as $actor) {
            if ($actor) {
                $this->actingAs($actor, 'admin');
            }
            foreach (['/admin/logout', '/admin/cache/clear', '/admin/role/delete/1', '/admin/staff/delete/'.$staff->id, '/admin/slider/delete/'.$slider->id, '/admin/models/delete/1', '/admin/partners/delete/1', '/admin/services/delete/1'] as $url) {
                $this->get($url)->assertStatus(405);
            }
            foreach (['/admin/general-settings/admin/loader/1', '/admin/general-settings/Adminloader'] as $url) {
                $this->get($url)->assertNotFound();
            }
            foreach (['/admin/update-profile/1', '/admin/update-password/1'] as $url) {
                $this->post($url, ['password' => 'x'])->assertNotFound();
            }
        }
        $this->assertDatabaseHas('sliders', ['id' => $slider->id]);
        $this->assertDatabaseHas('admins', ['id' => $staff->id]);
        $this->assertAuthenticated('admin');
    }

    public function test_get_routes_are_free_of_mutating_paths_and_admin_writes_are_protected(): void
    {
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (! str_starts_with($uri, 'admin')) {
                continue;
            }
            if (in_array('GET', $route->methods(), true) && $uri !== 'admin/forgot') {
                $this->assertDoesNotMatchRegularExpression('~(?:^|/)(?:delete|destroy|logout|clear|loader/)~', $uri, $uri);
            }
            $this->assertContains('web', $route->gatherMiddleware(), $uri);
            if (array_diff($route->methods(), ['GET', 'HEAD']) && ! in_array($uri, ['admin/login', 'admin/forgot'], true)) {
                $this->assertContains('auth.admin', $route->gatherMiddleware(), $uri);
            }
        }
    }

    public function test_csrf_token_is_required_for_converted_routes(): void
    {
        $slider = Slider::create(['title_ar' => 'keep']);
        $this->asOwner();
        // CSRF checks are skipped only under the "testing" environment name.
        $this->app['env'] = 'local';
        $this->delete('/admin/slider/delete/'.$slider->id)->assertStatus(419);
        $this->post('/admin/cache/clear')->assertStatus(419);
        $this->post('/admin/logout')->assertStatus(419);
        $this->delete(route('cms.sections.destroy', $this->section('about-s1')))->assertStatus(419);
        $this->app['env'] = 'testing';
        $this->assertDatabaseHas('sliders', ['id' => $slider->id]);
        $this->assertAuthenticated('admin');
    }

    public function test_converted_delete_routes_work_and_stay_permission_checked(): void
    {
        foreach (self::MODULES as $module => [$model]) {
            $record = $model::create(['photo' => null]);
            $url = "/admin/{$module}/delete/{$record->id}";
            $this->delete($url)->assertRedirect('/admin/login');
            $this->actingAs($this->adminWithRole('cms', $module.'-editor@example.test'), 'admin');
            $this->delete($url)->assertRedirect(route('admin.dashboard'));
            $this->assertDatabaseHas($record->getTable(), ['id' => $record->id]);
            $this->asOwner()->delete($url)->assertOk();
            $this->assertDatabaseMissing($record->getTable(), ['id' => $record->id]);
            $this->post('/admin/logout');
        }
        $role = Role::create(['name' => 'Disposable', 'section' => 'slider']);
        $this->asOwner()->delete('/admin/role/delete/'.$role->id)->assertOk();
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_cache_clear_requires_post_and_the_super_permission(): void
    {
        Artisan::shouldReceive('call')->times(4)->andReturn(0);
        $this->actingAs($this->adminWithRole('cms'), 'admin')->post('/admin/cache/clear')->assertRedirect(route('admin.dashboard'));
        $this->post('/admin/logout');
        $this->asOwner()->post('/admin/cache/clear')->assertRedirect(route('admin.dashboard'));
    }

    public function test_legacy_screens_call_the_converted_routes(): void
    {
        $this->asOwner();
        $screen = $this->get('/admin/slider')->assertOk();
        $screen->assertSee('name="csrf-token"', false);
        $screen->assertSee('<form method="POST" action="'.route('cms.logout').'">', false);
        $screen->assertSee('<form method="POST" action="'.route('admin-cache-clear').'">', false);
        $screen->assertDontSee('href="'.url('/admin/logout').'"', false);
        Slider::create(['title_ar' => 'listed']);
        $this->getJson('/admin/slider/datatables')->assertOk()->assertSee('slider\/delete', false);
        $script = file_get_contents(public_path('build/js/myscript.js'));
        $modal = substr($script, strpos($script, "$('#confirm-delete .btn-ok')"), 400);
        $this->assertStringContainsString('type:"DELETE"', $modal);
        $this->assertStringContainsString('X-CSRF-TOKEN', $modal);
    }

    public function test_every_retained_legacy_screen_renders(): void
    {
        $this->asOwner();
        $broken = [];
        $staff = $this->adminWithRole('slider', 'listed@example.test');
        $role = $staff->role_id;
        $slider = Slider::create(['title_ar' => 'x']);
        $partner = Partner::create(['photo' => null]);
        $service = Service::create(['photo' => null]);
        $model = PageModel::create(['photo' => null]);
        $this->get('/admin/password')->assertRedirect('/admin/profile');
        $this->get('/admin/general-settings/home_video')->assertNotFound();
        foreach (["/admin/staff/show/{$staff->id}", "/admin/staff/edit/{$staff->id}", "/admin/role/edit/{$role}", "/admin/slider/edit/{$slider->id}", "/admin/partners/edit/{$partner->id}", "/admin/services/edit/{$service->id}", "/admin/models/edit/{$model->id}", '/admin/profile', '/admin/role', '/admin/role/create', '/admin/staff', '/admin/staff/create', '/admin/slider', '/admin/slider/create', '/admin/models', '/admin/models/create', '/admin/partners', '/admin/partners/create', '/admin/services', '/admin/services/create', '/admin/general-settings/logo', '/admin/general-settings/contents', '/admin/general-settings/contact_messages', '/admin/page-settings/about_us', '/admin/page-settings/portfolio'] as $url) {
            $status = $this->get($url)->getStatusCode();
            if ($status !== 200) {
                $broken[$url] = $status;
            }
        }
        $this->assertSame([], $broken);
    }

    public function test_legacy_forms_carry_a_csrf_token(): void
    {
        $this->asOwner();
        foreach (['/admin/staff/create', '/admin/role/create', '/admin/slider/create', '/admin/partners/create', '/admin/services/create', '/admin/models/create', '/admin/profile', '/admin/general-settings/logo', '/admin/page-settings/about_us'] as $url) {
            $this->get($url)->assertOk()->assertSee('name="_token"', false);
        }
    }

    public function test_executable_and_unsafe_uploads_are_rejected_by_every_legacy_module(): void
    {
        $this->asOwner();
        foreach (self::MODULES as $module => [$model]) {
            foreach ([
                UploadedFile::fake()->create('shell.php', 1, 'application/x-php'),
                UploadedFile::fake()->create('shell.phtml', 1, 'application/x-httpd-php'),
                UploadedFile::fake()->image('disguised.php'),
                UploadedFile::fake()->create('vector.svg', 1, 'image/svg+xml'),
                UploadedFile::fake()->create('page.html', 1, 'text/html'),
            ] as $file) {
                $this->post("/admin/{$module}/create", ['title_ar' => 'x', 'photo' => $file])->assertOk()->assertJsonValidationErrors('photo');
            }
            $this->assertSame(0, $model::count(), $module);
        }
    }

    public function test_client_cannot_inject_a_stored_upload_path(): void
    {
        $this->asOwner();
        foreach (self::MODULES as $module => [$model]) {
            $this->post("/admin/{$module}/create", ['title_ar' => 'x', 'photo' => '../../../.env'])->assertOk()->assertJsonValidationErrors('photo');
            $record = $model::create(['photo' => null]);
            $this->post("/admin/{$module}/update/{$record->id}", ['title_ar' => 'y', 'photo' => '../../../.env'])->assertOk()->assertJsonValidationErrors('photo');
            $this->assertNull($record->fresh()->getRawOriginal('photo'));
        }
        $this->assertFileExists(base_path('composer.json'));
    }

    public function test_accepted_images_get_generated_names(): void
    {
        $this->asOwner();
        foreach (self::MODULES as $module => [$model, $directory]) {
            $this->assertIsString($this->post("/admin/{$module}/create", ['title_ar' => 'x', 'photo' => UploadedFile::fake()->image('clinic photo.png')])->assertOk()->json());
            $stored = $model::firstOrFail()->getRawOriginal('photo');
            $this->uploaded[] = [$directory, $stored];
            $this->assertMatchesRegularExpression('~^[0-9a-f-]{36}\.png$~', $stored);
            $this->assertFileExists(public_path($directory.'/'.$stored));
        }
    }

    public function test_stored_paths_cannot_reach_files_outside_the_upload_folder(): void
    {
        $outside = public_path('assets/images/safe-upload-probe.txt');
        file_put_contents($outside, 'keep');
        try {
            SafeUpload::delete('assets/images/admins', '../safe-upload-probe.txt');
            $this->assertFileExists($outside);
            // The real attack: a staff record already holding a traversal value is deleted.
            $staff = Admin::create(['name' => 'Target', 'email' => 'target@example.test', 'password' => Hash::make('target-test-password'), 'role_id' => 5, 'photo' => '../safe-upload-probe.txt']);
            $this->asOwner()->delete('/admin/staff/delete/'.$staff->id)->assertOk();
            $this->assertFileExists($outside);
        } finally {
            @unlink($outside);
        }
    }

    public function test_replacing_an_image_keeps_the_previous_file_as_before(): void
    {
        $this->asOwner();
        $this->post('/admin/slider/create', ['title_ar' => 'x', 'photo' => UploadedFile::fake()->image('first.png')])->assertOk();
        $slider = Slider::firstOrFail();
        $first = $slider->getRawOriginal('photo');
        $this->uploaded[] = ['assets/images/slider', $first];
        $this->post('/admin/slider/update/'.$slider->id, ['title_ar' => 'y', 'photo' => UploadedFile::fake()->image('second.png')])->assertOk();
        $second = $slider->fresh()->getRawOriginal('photo');
        $this->uploaded[] = ['assets/images/slider', $second];
        $this->assertNotSame($first, $second);
        $this->assertFileExists(public_path('assets/images/slider/'.$first));
    }

    public function test_settings_uploads_are_validated_and_cannot_set_stored_paths(): void
    {
        $this->asOwner();
        foreach ([['logo_en', 'shell.php'], ['favicon', 'shell.phtml'], ['home_video', 'shell.php']] as [$field, $name]) {
            $this->post('/admin/general-settings/update/all', [$field => UploadedFile::fake()->create($name, 1, 'application/x-php')])->assertOk()->assertJsonValidationErrors($field);
        }
        $this->post('/admin/general-settings/update/all', ['favicon' => '../../.env'])->assertOk()->assertJsonValidationErrors('favicon');
        $this->post('/admin/page-settings/update/all', ['about_photo' => UploadedFile::fake()->create('shell.php', 1, 'application/x-php')])->assertOk()->assertJsonValidationErrors('about_photo');
        $this->post('/admin/page-settings/update/all', ['about_photo' => '../../.env'])->assertOk()->assertJsonValidationErrors('about_photo');
    }

    public function test_staff_manager_cannot_create_or_become_an_owner(): void
    {
        $manager = $this->adminWithRole('manage_staffs', 'manager@example.test');
        $powerful = Role::create(['name' => 'Powerful', 'section' => 'super , manage_staffs']);
        $this->actingAs($manager, 'admin');
        $photo = fn () => UploadedFile::fake()->image('staff.png');
        $base = fn (array $extra) => $extra + ['name' => 'New', 'email' => 'new@example.test', 'password' => 'long-enough-password', 'photo' => $photo()];

        foreach ([0, $powerful->id, 999999] as $role) {
            $this->post('/admin/staff/create', $base(['role_id' => $role]))->assertOk()->assertJsonValidationErrors('role_id');
            $this->assertDatabaseMissing('admins', ['email' => 'new@example.test']);
        }
        $this->post('/admin/staff/create', $base([]))->assertOk()->assertJsonValidationErrors('role_id');
        $this->post('/admin/staff/create', ['name' => 'New', 'email' => 'new@example.test', 'role_id' => $manager->role_id, 'password' => 'short', 'photo' => $photo()])->assertOk()->assertJsonValidationErrors('password');

        $staff = Admin::create(['name' => 'Target', 'email' => 'target@example.test', 'password' => Hash::make('target-test-password'), 'role_id' => $manager->role_id]);
        $this->post('/admin/staff/update/'.$staff->id, ['role_id' => 0])->assertOk()->assertJsonValidationErrors('role_id');
        $this->post('/admin/staff/update/'.$staff->id, ['role_id' => $powerful->id])->assertOk()->assertJsonValidationErrors('role_id');
        $this->assertEquals($manager->role_id, $staff->fresh()->role_id);
        $this->assertFalse($staff->fresh()->IsSuper());

        // The manager's own record is refused outright.
        $this->post('/admin/staff/update/'.$manager->id, ['role_id' => 0])->assertOk();
        $this->assertFalse($manager->fresh()->IsSuper());
    }

    public function test_staff_manager_cannot_change_or_remove_owners(): void
    {
        $owner = $this->owner();
        $otherOwner = Admin::create(['name' => 'Second owner', 'email' => 'second@example.test', 'password' => Hash::make('second-test-password'), 'role_id' => 0]);
        $manager = $this->adminWithRole('manage_staffs', 'manager@example.test');
        $before = $otherOwner->password;
        $this->actingAs($manager, 'admin');

        $this->get('/admin/staff/edit/'.$otherOwner->id)->assertForbidden();
        $this->post('/admin/staff/update/'.$otherOwner->id, ['password' => 'attacker-chosen-password'])->assertForbidden();
        $this->delete('/admin/staff/delete/'.$otherOwner->id)->assertForbidden();
        $this->delete('/admin/staff/delete/'.$manager->id)->assertForbidden();
        $this->assertSame($before, $otherOwner->fresh()->password);
        $this->assertDatabaseHas('admins', ['id' => $otherOwner->id]);
        $ids = collect($this->getJson('/admin/staff/datatables')->assertOk()->json('data'))->pluck('id');
        $this->assertFalse($ids->contains($owner->id));
        $this->assertFalse($ids->contains($otherOwner->id));
    }

    public function test_staff_update_ignores_privileged_or_unknown_fields(): void
    {
        $manager = $this->adminWithRole('manage_staffs', 'manager@example.test');
        $staff = Admin::create(['name' => 'Target', 'email' => 'target@example.test', 'password' => Hash::make('target-test-password'), 'role_id' => $manager->role_id]);
        $token = $staff->remember_token;
        $this->actingAs($manager, 'admin')->post('/admin/staff/update/'.$staff->id, ['name' => 'Renamed', 'remember_token' => 'forged', 'created_at' => '2000-01-01', 'password' => ''])->assertOk();
        $fresh = $staff->fresh();
        $this->assertSame('Renamed', $fresh->name);
        $this->assertSame($token, $fresh->remember_token);
        $this->assertTrue(Hash::check('target-test-password', $fresh->password));
        $this->assertNotSame('2000-01-01', $fresh->created_at->format('Y-m-d'));
    }

    public function test_owner_can_still_assign_any_role_through_the_legacy_screen(): void
    {
        $role = Role::create(['name' => 'Powerful', 'section' => 'super']);
        $this->asOwner();
        $staff = Admin::create(['name' => 'Target', 'email' => 'target@example.test', 'password' => Hash::make('target-test-password'), 'role_id' => 99]);
        $this->assertIsString($this->post('/admin/staff/update/'.$staff->id, ['role_id' => $role->id])->assertOk()->json());
        $this->assertEquals($role->id, $staff->fresh()->role_id);
    }

    public function test_role_screen_accepts_only_explicit_fields(): void
    {
        $this->asOwner();
        $this->assertIsString($this->post('/admin/role/create', ['name' => 'Sliders only', 'section' => ['slider', 'partners'], 'id' => 999])->assertOk()->json());
        $role = Role::where('name', 'Sliders only')->firstOrFail();
        $this->assertNotSame(999, $role->id);
        $this->assertSame('slider , partners', $role->section);
        $this->post('/admin/role/create', ['name' => 'Bad', 'section' => 'not-an-array'])->assertOk()->assertJsonValidationErrors('section');
        $this->post('/admin/role/create', ['section' => ['slider']])->assertOk()->assertJsonValidationErrors('name');
        $this->assertIsString($this->post('/admin/role/edit/'.$role->id, ['name' => 'Renamed'])->assertOk()->json());
        $this->assertSame('', $role->fresh()->section);
    }

    public function test_editor_cannot_reach_owner_or_legacy_management(): void
    {
        $editor = $this->adminWithRole('cms');
        $this->actingAs($editor, 'admin');
        foreach (['/admin/role', '/admin/staff', '/admin/slider', '/admin/partners', '/admin/services', '/admin/models', '/admin/general-settings/logo', '/admin/page-settings/about_us'] as $url) {
            $this->get($url)->assertRedirect(route('admin.dashboard'));
        }
        $this->post('/admin/profile/update', ['role_id' => 0])->assertOk();
        $this->assertFalse($editor->fresh()->IsSuper());
        $this->get(route('cms.users'))->assertForbidden();
        $this->post(route('cms.users.store'), ['name' => 'x', 'email' => 'x@example.test', 'password' => 'long-enough-password', 'access' => 'owner'])->assertForbidden();
    }

    public function test_changing_a_password_ends_other_sessions_but_keeps_the_current_one(): void
    {
        $editor = $this->adminWithRole('cms');
        $this->actingAs($editor, 'admin')->get('/admin/cms')->assertOk();
        // Another device or an owner resets this account's password.
        $editor->fresh()->changePassword('replacement-test-password');
        $this->actingAs($editor->fresh(), 'admin')->get('/admin/cms')->assertRedirect('/admin/login');
        $this->assertGuest('admin');
        $this->assertNotSame($editor->remember_token, $editor->fresh()->remember_token);

        $owner = $this->owner();
        $this->actingAs($owner, 'admin')->get('/admin/cms')->assertOk();
        $this->postJson('/admin/password/update', ['cpass' => 'owner-test-password', 'newpass' => 'updated-owner-password', 'renewpass' => 'updated-owner-password'])->assertOk();
        $this->actingAs($owner->fresh(), 'admin')->get('/admin/cms')->assertOk();
    }

    public function test_staff_screen_password_change_also_invalidates_remember_me(): void
    {
        $manager = $this->adminWithRole('manage_staffs', 'manager@example.test');
        $staff = Admin::create(['name' => 'Target', 'email' => 'target@example.test', 'password' => Hash::make('target-test-password'), 'role_id' => $manager->role_id, 'remember_token' => 'old-token']);
        $this->actingAs($manager, 'admin')->post('/admin/staff/update/'.$staff->id, ['password' => 'replacement-test-password'])->assertOk();
        $fresh = $staff->fresh();
        $this->assertTrue(Hash::check('replacement-test-password', $fresh->password));
        $this->assertNotSame('old-token', $fresh->remember_token);
    }

    public function test_language_switch_rejects_unknown_locales(): void
    {
        $this->get('/index/../../secrets')->assertNotFound();
        $this->get('/index/not-a-locale')->assertNotFound();
        $this->from('/about.html')->get('/index/en')->assertRedirect('/about.html');
    }

    public function test_public_contact_api_rejects_header_injection(): void
    {
        $this->postJson('/api/contact-submit', ['name' => "Attacker\r\nBcc: victim@example.test", 'phone' => '123'])->assertOk()->assertJsonStructure(['errors' => ['name']]);
    }
}
