<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\DemoContentSeeder;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_get_parses_grouped_and_flat_keys(): void
    {
        Setting::updateOrCreate(['group' => 'theme', 'key' => 'primary_color'], ['value' => '#123abc']);
        Setting::updateOrCreate(['group' => 'general', 'key' => 'name'], ['value' => 'SULTRAKLIK']);

        $this->assertSame('#123abc', Setting::get('theme.primary_color'));
        $this->assertSame('SULTRAKLIK', Setting::get('general.name'));
        $this->assertSame('SULTRAKLIK', Setting::get('name'));
        $this->assertSame('FALLBACK', Setting::get('theme.missing', 'FALLBACK'));
        $this->assertSame('FALLBACK', Setting::get('missing', 'FALLBACK'));
    }

    public function test_setting_set_creates_row(): void
    {
        Setting::set('theme.primary_color', '#112233');

        $this->assertDatabaseHas('settings', [
            'group' => 'theme',
            'key' => 'primary_color',
            'value' => '#112233',
        ]);
    }

    public function test_seo_settings_are_used_as_default_meta_tags(): void
    {
        $this->seed(DemoContentSeeder::class);

        Setting::set('seo.meta_title', 'Meta Title Uji Coba');
        Setting::set('seo.meta_description', 'Meta description uji coba.');
        Setting::set('seo.meta_keywords', 'uji coba, sultra');

        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Meta Title Uji Coba</title>', false)
            ->assertSee('<meta name="description" content="Meta description uji coba.">', false)
            ->assertSee('<meta name="keywords" content="uji coba, sultra">', false);
    }

    public function test_page_meta_overrides_seo_settings_and_falls_back_when_empty(): void
    {
        $this->seed(DemoContentSeeder::class);
        Setting::set('seo.meta_description', 'Deskripsi bawaan situs.');

        $page = Page::where('is_active', true)->firstOrFail();
        $url = '/page/'.$page->slug;

        $page->update(['seo_description' => 'Deskripsi khusus halaman ini.']);
        $this->get($url)
            ->assertOk()
            ->assertSee('<meta name="description" content="Deskripsi khusus halaman ini.">', false)
            ->assertDontSee('Deskripsi bawaan situs.', false);

        $page->update(['seo_description' => '']);
        $this->get($url)
            ->assertOk()
            ->assertSee('<meta name="description" content="Deskripsi bawaan situs.">', false);
    }
}
