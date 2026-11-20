<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Support\TemplateRegistry;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Template catalogue, the order form's page map and the live preview. */
class TemplatesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected $seeder = DatabaseSeeder::class;

    public function test_every_template_renders_its_sample_and_marks_its_fields(): void
    {
        $this->assertGreaterThanOrEqual(20, Template::count());
        foreach (Template::with('fields')->get() as $t) {
            $html = $this->get(route('templates.render', $t))->assertOk()->getContent();
            $this->assertStringContainsString('data-f="', $html, "{$t->slug} has no field markers");
        }
    }

    public function test_every_layout_field_has_a_place_in_the_page_map(): void
    {
        foreach (TemplateRegistry::layouts() as $key => $layout) {
            $guide = TemplateRegistry::guide($key, $layout['fields']);
            $mapped = collect($guide['sections'])->flatMap(fn ($s) => $s['fields'])->all();
            $this->assertEqualsCanonicalizing(collect($layout['fields'])->pluck('key')->all(), $mapped, "{$key}: fields and sections differ");
            $this->assertNotContains('আরও কিছু', collect($guide['sections'])->pluck('title')->all(), "{$key}: a field has no section");
        }
    }

    public function test_order_form_gets_sections_and_examples(): void
    {
        $res = $this->get(route('order.create', ['template' => 'romantic-story']))->assertOk()->assertSee('পেজে কোথায়?');
        $tpl = collect($res->viewData('boot')['catalog'])->flatMap(fn ($c) => $c['templates'])->firstWhere('slug', 'romantic-story');
        $this->assertSame('প্রথম পাতা', $tpl['guide']['sections'][0]['title']);
        $this->assertContains('hero_photo', $tpl['guide']['sections'][0]['fields']);
        $this->assertSame('Mitu', $tpl['guide']['examples']['your_name']['text']);
    }

    public function test_live_preview_fills_empty_fields_with_faded_samples(): void
    {
        $html = $this->post(route('order.preview'), ['template' => 'romantic-story', 'fill' => true, 'content' => ['your_name' => 'Korim <b>x</b>']])
            ->assertOk()->getContent();
        $this->assertStringContainsString('Korim', $html);
        $this->assertStringNotContainsString('<b>x</b>', $html);
        $this->assertStringContainsString('data-f="their_name" data-ph', $html); // sample name, faded
        $this->assertStringContainsString('data-f="your_name">', $html);           // the customer's own text is not faded
        $this->assertStringContainsString('data-draft', $html);
    }

    public function test_sync_command_adds_templates_without_touching_existing_ones(): void
    {
        $t = Template::where('slug', 'romantic-story')->first();
        $t->update(['name' => 'Edited by admin']);
        Template::where('slug', 'eid-mubarak')->first()->fields()->delete();
        Template::where('slug', 'eid-mubarak')->delete();

        $this->artisan('golpobari:sync-templates')->assertSuccessful();

        $this->assertSame('Edited by admin', $t->fresh()->name);
        $eid = Template::where('slug', 'eid-mubarak')->firstOrFail();
        $this->assertSame('festival-greeting', $eid->layout);
        $this->assertGreaterThan(0, $eid->fields()->count());
    }

    public function test_sample_pages_play_their_song_and_the_guide_names_it(): void
    {
        $html = $this->get(route('templates.render', Template::where('slug', 'romantic-story')->firstOrFail()))->assertOk()->getContent();
        $this->assertStringContainsString('data-music', $html);
        $this->assertStringContainsString('audio/prem.mp3', $html);
        $this->assertFileExists(public_path('audio/prem.mp3'));

        $guide = TemplateRegistry::guide('romantic-story', TemplateRegistry::layouts()['romantic-story']['fields']);
        $this->assertSame(['music'], collect($guide['sections'])->firstWhere('title', 'পেজের গান')['fields']);
    }

    public function test_customers_can_upload_their_own_song_but_not_a_disguised_file(): void
    {
        Storage::fake('uploads');
        Storage::fake('public');
        $draft = Str::random(40);
        $send = fn ($file) => $this->withCredentials()->withCookie('gb_draft', $draft)
            ->postJson(route('order.upload'), ['field' => 'music', 'file' => $file]);

        $mp3 = "ID3\x03\x00\x00\x00\x00\x00\x00".str_repeat("\xff\xfb\x90\x00", 64);
        $send(UploadedFile::fake()->createWithContent('amar-gan.mp3', $mp3))->assertOk()->assertJsonPath('name', 'amar-gan.mp3');
        $send(UploadedFile::fake()->createWithContent('shell.mp3', '<?php echo 1; ?>'))->assertStatus(422);
        $send(UploadedFile::fake()->createWithContent('song.php', $mp3))->assertStatus(422);
    }
}
