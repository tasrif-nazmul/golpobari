<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PageStatus;
use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Page;
use App\Models\PageMedia;
use App\Support\Settings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Walks the whole business flow from the spec's testing list (tests 1–22):
 * browse → order with photos, coupon and bKash → admin verifies → page → publish → expire → renew.
 */
class OrderToPublishedPageTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected $seeder = DatabaseSeeder::class;

    private string $draft;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('uploads');
        Storage::fake('public');
        app(Settings::class)->set(['bkash_number' => '01712345678']);
        $this->draft = Str::random(40);
    }

    private function admin(string $role = 'owner'): Admin
    {
        return Admin::create(['name' => 'Test '.$role, 'email' => $role.'@example.test', 'password' => 'Strong-Pass-123', 'role' => $role, 'is_active' => true]);
    }

    private function upload(string $field, ?UploadedFile $file = null)
    {
        return $this->withCredentials()->withCookie('gb_draft', $this->draft)->postJson(route('order.upload'), [
            'field' => $field, 'file' => $file ?? UploadedFile::fake()->image('photo.jpg', 1600, 1200),
        ]);
    }

    /** A valid romantic-story order payload. */
    private function payload(array $media, array $overrides = []): array
    {
        return array_replace_recursive([
            'template' => 'romantic-story', 'package' => '3-months', 'occasion' => 'Anniversary',
            'customer_name' => 'Korim Uddin', 'customer_phone' => '01811223344', 'customer_email' => 'korim@example.test',
            'desired_slug' => 'korim-loves-mitu',
            'content' => [
                'your_name' => 'Korim', 'their_name' => 'Mitu', 'relationship' => 'Wife', 'start_date' => '2019-02-14',
                'story' => "We met at a book fair.\nShe laughed at my jokes.", 'special_message' => 'Happy anniversary <script>alert(1)</script>',
            ],
            'media' => $media,
            'payment_sender' => '01811223344', 'payment_trx' => 'BKX9A1B2C3', 'payment_amount' => 399, 'confirm' => '1',
        ], $overrides);
    }

    private function placeOrder(array $overrides = []): Order
    {
        $hero = $this->upload('hero_photo')->assertOk()->json('id');
        $g1 = $this->upload('gallery')->assertOk()->json('id');
        $res = $this->withCredentials()->withCookie('gb_draft', $this->draft)->postJson(route('order.store'), $this->payload(['hero_photo' => [$hero], 'gallery' => [$g1]], $overrides));
        $res->assertOk()->assertJsonPath('ok', true);

        return Order::where('order_number', $res->json('order'))->firstOrFail();
    }

    public function test_visitor_browses_home_category_and_template(): void // tests 1–3
    {
        $this->get('/')->assertOk()->assertSee('আপনার গল্পের নিজের একটা ঠিকানা।', false)->assertSee('/categories/someone-special', false);
        $this->get('/categories/someone-special')->assertOk()->assertSee('Romantic Story');
        $this->get('/templates/romantic-story')->assertOk()->assertSee(route('order.create', ['template' => 'romantic-story']), false);
        $this->get('/templates/romantic-story/render')->assertOk()->assertHeader('X-Robots-Tag', 'noindex');
        $this->get(route('order.create', ['template' => 'romantic-story']))->assertOk();
    }

    public function test_uploads_are_converted_to_webp_and_executables_are_refused(): void // test 5
    {
        $res = $this->upload('hero_photo')->assertOk();
        $media = PageMedia::findOrFail($res->json('id'));
        $this->assertStringEndsWith('.webp', $media->path);
        $this->assertSame('image/webp', $media->mime);
        Storage::disk('uploads')->assertExists([$media->path, $media->thumb_path]);
        $this->assertNull($media->order_id);

        // PHP disguised as an image, and plain text, are rejected.
        $this->upload('hero_photo', UploadedFile::fake()->createWithContent('shell.php', '<?php system($_GET["c"]); ?>'))->assertStatus(422);
        $this->upload('hero_photo', UploadedFile::fake()->createWithContent('shell.jpg', '<?php echo 1; ?>'))->assertStatus(422);
        $this->upload('hero_photo', UploadedFile::fake()->create('doc.pdf', 20, 'application/pdf'))->assertStatus(422);
        $this->assertSame(1, PageMedia::count());

        // Another browser can't see or delete this draft photo.
        $this->withCredentials()->withCookie('gb_draft', Str::random(40))->get(route('media.show', [$media, 'thumb']))->assertNotFound();
        $this->withCredentials()->withCookie('gb_draft', Str::random(40))->deleteJson(route('order.upload.delete', $media))->assertNotFound();
        $this->withCredentials()->withCookie('gb_draft', $this->draft)->get(route('media.show', [$media, 'thumb']))->assertOk();
    }

    public function test_coupons_valid_invalid_and_expired(): void // tests 6–9
    {
        $ok = $this->postJson(route('order.coupon'), ['code' => 'welcome20', 'package' => '3-months', 'phone' => '01811223344'])->assertOk();
        $this->assertEquals(399, $ok->json('original'));
        $this->assertEquals(79.8, $ok->json('discount'));
        $this->assertEquals(319.2, $ok->json('final'));

        $this->postJson(route('order.coupon'), ['code' => 'NOPE', 'package' => '3-months'])->assertStatus(422)->assertJsonPath('ok', false);

        Coupon::create(['code' => 'OLD10', 'type' => 'percent', 'value' => 10, 'is_active' => true, 'expires_at' => now()->subDay()]);
        $this->postJson(route('order.coupon'), ['code' => 'OLD10', 'package' => '3-months'])->assertStatus(422);

        Coupon::create(['code' => 'OFF', 'type' => 'fixed', 'value' => 50, 'is_active' => false]);
        $this->postJson(route('order.coupon'), ['code' => 'OFF', 'package' => '3-months'])->assertStatus(422);
    }

    public function test_order_submission_stores_everything_server_side(): void // tests 4, 10, 11
    {
        // The client claims a tiny price: it is ignored; the server prices the order itself.
        $order = $this->placeOrder(['coupon_code' => 'WELCOME20', 'payment_amount' => 1]);

        $this->assertMatchesRegularExpression('/^GB-\d{8}-\d{4,}$/', $order->order_number);
        $this->assertSame(OrderStatus::PaymentPending, $order->status);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertEquals(399, (float) $order->original_price);
        $this->assertEquals(319.2, (float) $order->final_price);
        $this->assertSame('WELCOME20', $order->coupon_code);
        $this->assertSame(1, Coupon::where('code', 'WELCOME20')->value('used_count'));
        $this->assertTrue($order->payment->amountMismatch());
        $this->assertStringNotContainsString('<script>', $order->content['special_message']);
        $this->assertSame('korim-loves-mitu', $order->desired_slug);

        $media = $order->media()->get();
        $this->assertCount(2, $media);
        $this->assertTrue($media->every(fn ($m) => $m->draft_token === null && str_starts_with($m->path, "orders/{$order->id}/")));
        $media->each(fn ($m) => Storage::disk('uploads')->assertExists($m->path));
        $this->assertGreaterThanOrEqual(1, $order->histories()->count());

        // Same transaction ID can't be used twice; same phone can't reuse a one-per-phone coupon.
        $this->draft = Str::random(40);
        $hero = $this->upload('hero_photo')->json('id');
        $this->withCredentials()->withCookie('gb_draft', $this->draft)->postJson(route('order.store'), $this->payload(['hero_photo' => [$hero]], ['desired_slug' => 'another-one']))
            ->assertStatus(422)->assertJsonValidationErrors('payment_trx');
        $this->withCredentials()->withCookie('gb_draft', $this->draft)->postJson(route('order.store'), $this->payload(['hero_photo' => [$hero]], ['desired_slug' => 'another-one', 'payment_trx' => 'BKX000NEW1', 'coupon_code' => 'WELCOME20']))
            ->assertStatus(422)->assertJsonValidationErrors('coupon_code');
    }

    public function test_order_validation_blocks_bad_input(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $hero = $this->upload('hero_photo')->json('id');
        $post = fn (array $o, array $media = null) => $this->withCredentials()->withCookie('gb_draft', $this->draft)->postJson(route('order.store'), $this->payload($media ?? ['hero_photo' => [$hero]], $o));

        $post(['desired_slug' => 'admin'])->assertStatus(422)->assertJsonValidationErrors('desired_slug');
        $post(['desired_slug' => 'order'])->assertStatus(422)->assertJsonValidationErrors('desired_slug');
        $post(['customer_phone' => '12345'])->assertStatus(422)->assertJsonValidationErrors('customer_phone');
        $post(['payment_trx' => 'x!'])->assertStatus(422)->assertJsonValidationErrors('payment_trx');
        $post(['confirm' => '0'])->assertStatus(422)->assertJsonValidationErrors('confirm');
        $post(['package' => 'free-forever'])->assertStatus(422)->assertJsonValidationErrors('package');
        $post(['content' => ['story' => '']])->assertStatus(422)->assertJsonValidationErrors('content.story');
        $post([], [])->assertStatus(422)->assertJsonValidationErrors('media.hero_photo'); // required photo missing

        // Someone else's photo id doesn't count as yours.
        $foreign = PageMedia::create(['draft_token' => Str::random(40), 'field_key' => 'hero_photo', 'path' => 'drafts/x.webp', 'mime' => 'image/webp', 'size' => 1]);
        $post([], ['hero_photo' => [$foreign->id]])->assertStatus(422)->assertJsonValidationErrors('media.hero_photo');

        app(Settings::class)->set(['orders_enabled' => '0']);
        $post([])->assertStatus(422)->assertJsonValidationErrors('template');
        $this->assertSame(0, Order::count());
    }

    public function test_admin_verifies_creates_previews_publishes_and_public_url_works(): void // tests 12–19
    {
        $order = $this->placeOrder();

        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->post(route('admin.login.attempt'), ['email' => 'owner@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $admin = $this->admin();
        $this->post(route('admin.login.attempt'), ['email' => 'OWNER@example.test', 'password' => 'Strong-Pass-123'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');

        $this->get(route('admin.dashboard'))->assertOk()->assertSee($order->order_number);
        $this->get(route('admin.orders.index'))->assertOk()->assertSee($order->order_number);
        $this->get(route('admin.orders.show', $order))->assertOk()->assertSee('BKX9A1B2C3')->assertSee('korim-loves-mitu');
        $this->get(route('admin.orders.media.zip', $order))->assertOk();

        // Can't create a page before the payment is verified.
        $this->post(route('admin.orders.page', $order))->assertSessionHas('error');
        $this->post(route('admin.payments.verify', $order->payment))->assertSessionHas('success');
        $order->refresh();
        $this->assertSame(PaymentStatus::Verified, $order->payment_status);
        $this->assertSame(OrderStatus::PaymentVerified, $order->status);
        $this->assertNotNull($order->payment->verified_at);
        $this->post(route('admin.payments.verify', $order->payment))->assertSessionHas('error'); // not twice

        $this->post(route('admin.orders.page', $order))->assertRedirect();
        $page = Page::where('order_id', $order->id)->firstOrFail();
        $this->assertSame('korim-loves-mitu', $page->slug);
        $this->assertSame(PageStatus::Draft, $page->status);
        $this->assertCount(2, $page->media);

        $this->get('/korim-loves-mitu')->assertNotFound(); // drafts aren't public
        $this->get(route('admin.pages.edit', $page))->assertOk();
        $this->get(route('admin.pages.preview', $page))->assertOk()->assertSee('Mitu')->assertSee('প্রিভিউ');

        $this->post(route('admin.pages.action', $page), ['action' => 'publish'])->assertSessionHas('success');
        $page->refresh();
        $this->assertSame(PageStatus::Active, $page->status);
        $this->assertTrue($page->expires_at->between(now()->addMonths(3)->subDay(), now()->addMonths(3)->addDay()));
        $this->assertSame(OrderStatus::Published, $order->fresh()->status);

        auth('admin')->logout();
        $html = $this->get('/korim-loves-mitu')->assertOk()->assertSee('Korim')->assertSee('Mitu')
            ->assertDontSee('<script>alert(1)</script>', false)->getContent();
        // SEO
        $this->assertStringContainsString('<link rel="canonical" href="'.url('/korim-loves-mitu').'"', $html);
        $this->assertMatchesRegularExpression('/<meta property="og:title" content="[^"]+"/', $html);
        $this->assertMatchesRegularExpression('/<meta property="og:image" content="[^"]+"/', $html);
        $this->get('/sitemap.xml')->assertOk()->assertSee(url('/korim-loves-mitu'), false);
        $this->assertSame(1, $page->fresh()->views_count);

        // Published photos are publicly viewable; the track page shows the link.
        $this->get(route('media.show', [$page->media->first(), 'full']))->assertOk();
        $this->post(route('track.lookup'), ['order_number' => $order->order_number, 'phone' => '+8801811223344'])->assertOk()->assertSee(url('/korim-loves-mitu'));
    }

    public function test_page_expires_and_renewal_reactivates_it(): void // tests 20–22
    {
        $order = $this->placeOrder();
        $this->actingAs($admin = $this->admin(), 'admin');
        $this->post(route('admin.payments.verify', $order->payment));
        $this->post(route('admin.orders.page', $order));
        $page = Page::where('order_id', $order->id)->first();
        $this->post(route('admin.pages.action', $page), ['action' => 'publish']);
        auth('admin')->logout();

        $this->travelTo($page->fresh()->expires_at->addMinute());
        // Live check works even before the cron runs…
        $this->get('/'.$page->slug)->assertStatus(410)->assertSee('এই গল্পটির সময়কাল শেষ হয়েছে।');
        // …and the scheduled command marks it expired.
        $this->artisan('golpobari:expire-pages')->assertSuccessful();
        $this->assertSame(PageStatus::Expired, $page->fresh()->status);
        $this->assertSame(OrderStatus::Expired, $order->fresh()->status);
        $this->get(route('media.show', [$page->media->first(), 'full']))->assertNotFound();

        $this->actingAs($admin, 'admin');
        $package = \App\Models\Package::where('slug', '6-months')->first();
        $this->post(route('admin.pages.renew', $page), ['mode' => 'package', 'package_id' => $package->id])->assertSessionHas('success');
        $page->refresh();
        $this->assertSame(PageStatus::Active, $page->status);
        $this->assertTrue($page->expires_at->isAfter(now()->addMonths(6)->subDay()));
        auth('admin')->logout();
        $this->get('/'.$page->slug)->assertOk()->assertSee('Mitu');
    }
}
