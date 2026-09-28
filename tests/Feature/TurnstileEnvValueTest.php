<?php

namespace Tests\Feature;

use App\Support\Turnstile;
use Tests\TestCase;

class TurnstileEnvValueTest extends TestCase
{
    private const SITE_KEY = '1x00000000000000000000AA';

    private const SECRET = '1x0000000000000000000000000000000AA';

    protected function tearDown(): void
    {
        putenv('TURNSTILE_ENABLED');
        unset($_ENV['TURNSTILE_ENABLED'], $_SERVER['TURNSTILE_ENABLED']);

        parent::tearDown();
    }

    public function test_env_string_false_actually_disables_the_captcha(): void
    {
        putenv('TURNSTILE_ENABLED=false');
        $_ENV['TURNSTILE_ENABLED'] = 'false';
        $_SERVER['TURNSTILE_ENABLED'] = 'false';

        $resolved = require base_path('config/turnstile.php');
        $resolved['site_key'] = self::SITE_KEY;
        $resolved['secret_key'] = self::SECRET;

        config($resolved);

        $this->assertIsBool(config('turnstile.enabled'));
        $this->assertFalse(config('turnstile.enabled'));
        $this->assertFalse(Turnstile::enabled());
    }

    public function test_env_string_true_actually_enables_the_captcha(): void
    {
        putenv('TURNSTILE_ENABLED=true');
        $_ENV['TURNSTILE_ENABLED'] = 'true';
        $_SERVER['TURNSTILE_ENABLED'] = 'true';

        $resolved = require base_path('config/turnstile.php');
        $resolved['site_key'] = self::SITE_KEY;
        $resolved['secret_key'] = self::SECRET;

        config($resolved);

        $this->assertTrue(Turnstile::enabled());
    }
}
