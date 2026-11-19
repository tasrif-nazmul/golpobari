<?php

namespace Tests\Feature;

use App\Enums\PageStatus;
use App\Enums\PageVisibility;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Page;
use App\Models\Template;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Test 23: invalid or unauthorised requests are blocked. */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected $seeder = DatabaseSeeder::class;

    private function page(array $attrs = []): Page
    {
        return Page::create($attrs + [
            'template_id' => Template::where('slug', 'love-letter')->value('id'), 'slug' => 'secret-letter', 'title' => 'A letter',
            'status' => PageStatus::Active, 'visibility' => PageVisibility::Public, 'content' => ['to_name' => 'Nodi', 'letter' => 'Hello'],
            'published_at' => now(), 'expires_at' => now()->addMonth(),
        ]);
    }

    public function test_no_admin_account_is_seeded(): void
    {
        $this->assertSame(0, Admin::count());
    }

    public function test_guests_cannot_reach_admin_routes(): void
    {
        foreach (['/admin', '/admin/orders', '/admin/settings', '/admin/pages', '/admin/admins'] as $u) {
            $this->get($u)->assertRedirect(route('admin.login'));
        }
        $this->post('/admin/payments/1/verify')->assertRedirect(route('admin.login'));
        $this->get(route('admin.login'))->assertOk()->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_staff_cannot_manage_settings_catalogue_or_admins(): void
    {
        $staff = Admin::create(['name' => 'S', 'email' => 's@example.test', 'password' => 'Strong-Pass-123', 'role' => 'staff', 'is_active' => true]);
        $this->actingAs($staff, 'admin');
        $this->get('/admin/orders')->assertOk();
        $this->get('/admin/settings')->assertForbidden();
        $this->put('/admin/settings/payment', ['bkash_number' => '01999999999'])->assertForbidden();
        $this->get('/admin/packages')->assertForbidden();
        $this->get('/admin/admins')->assertForbidden();
        $this->post('/admin/admins', ['name' => 'x', 'email' => 'x@x.test', 'role' => 'owner'])->assertForbidden();
    }

    public function test_inactive_admin_cannot_sign_in_and_login_is_rate_limited(): void
    {
        Admin::create(['name' => 'Off', 'email' => 'off@example.test', 'password' => 'Strong-Pass-123', 'role' => 'owner', 'is_active' => false]);
        $this->post(route('admin.login.attempt'), ['email' => 'off@example.test', 'password' => 'Strong-Pass-123'])->assertSessionHasErrors('email');
        $this->assertGuest('admin');

        for ($i = 0; $i < 6; $i++) {
            $res = $this->post(route('admin.login.attempt'), ['email' => 'off@example.test', 'password' => 'nope'.$i]);
        }
        $res->assertStatus(429);
    }

    public function test_create_admin_command_refuses_weak_passwords(): void
    {
        $this->artisan('golpobari:create-admin', ['--from-env' => true])->assertFailed();
        putenv('ADMIN_NAME=Owner'); putenv('ADMIN_EMAIL=o@example.test'); putenv('ADMIN_PASSWORD=password');
        $_ENV['ADMIN_NAME'] = 'Owner'; $_ENV['ADMIN_EMAIL'] = 'o@example.test'; $_ENV['ADMIN_PASSWORD'] = 'password';
        $this->artisan('golpobari:create-admin', ['--from-env' => true])->assertFailed();
        $this->assertSame(0, Admin::count());
        putenv('ADMIN_PASSWORD'); unset($_ENV['ADMIN_PASSWORD'], $_ENV['ADMIN_NAME'], $_ENV['ADMIN_EMAIL']);
    }

    public function test_page_states_and_private_access(): void
    {
        $page = $this->page();
        $this->get('/secret-letter')->assertOk();

        foreach ([PageStatus::Draft, PageStatus::Pending, PageStatus::Archived] as $s) {
            $page->update(['status' => $s]);
            $this->get('/secret-letter')->assertNotFound();
        }
        $page->update(['status' => PageStatus::Suspended]);
        $this->get('/secret-letter')->assertStatus(410);

        $page->update(['status' => PageStatus::Active, 'visibility' => PageVisibility::Unlisted]);
        $this->get('/secret-letter')->assertOk()->assertSee('noindex', false);
        $this->get('/sitemap.xml')->assertDontSee('secret-letter');

        $page->update(['visibility' => PageVisibility::Private, 'access_code' => Hash::make('mitu2024')]);
        $this->get('/secret-letter')->assertOk()->assertDontSee('Hello')->assertSee('access_code', false);
        $this->post('/secret-letter/unlock', ['access_code' => 'wrong'])->assertSessionHasErrors('access_code');
        $this->post('/secret-letter/unlock', ['access_code' => 'mitu2024'])->assertRedirect('/secret-letter');
        $this->get('/secret-letter')->assertOk()->assertSee('Hello');
    }

    public function test_order_lookup_needs_the_matching_phone(): void
    {
        $order = Order::create([
            'order_number' => 'GB-20260101-1001', 'customer_name' => 'A', 'customer_phone' => '01811223344',
            'template_id' => Template::value('id'), 'original_price' => 399, 'discount_amount' => 0, 'final_price' => 399,
            'status' => 'payment_pending', 'payment_status' => 'pending', 'content' => [],
        ]);
        $this->post(route('track.lookup'), ['order_number' => $order->order_number, 'phone' => '01900000000'])->assertSessionHasErrors('order_number');
        $this->post(route('track.lookup'), ['order_number' => $order->order_number, 'phone' => '01811223344'])->assertOk()->assertSee($order->order_number);
        // Confirmation page is only for the browser that placed the order.
        $this->get(route('order.confirmation', $order))->assertRedirect(route('track'));
    }

    public function test_reserved_and_malformed_slugs_do_not_reach_pages(): void
    {
        $this->postJson(route('order.slug'), ['slug' => 'admin'])->assertJsonPath('ok', false);
        $this->postJson(route('order.slug'), ['slug' => 'sitemap'])->assertJsonPath('ok', false);
        $this->postJson(route('order.slug'), ['slug' => 'Korim & Mitu!'])->assertJsonPath('slug', 'korim-mitu')->assertJsonPath('ok', true);
        $this->get('/no-such-page')->assertNotFound();
        $this->get('/../.env')->assertNotFound();
        $this->get('/.env')->assertNotFound();
    }

    public function test_preview_escapes_customer_html(): void
    {
        $html = $this->post(route('order.preview'), ['template' => 'love-letter', 'content' => ['to_name' => '<img src=x onerror=alert(1)>', 'letter' => '<b>hi</b>']])
            ->assertOk()->getContent();
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringNotContainsString('<b>hi</b>', $html);
    }
}
