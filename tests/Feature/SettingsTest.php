<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}