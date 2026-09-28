<?php

namespace App\Filament\Pages\Auth;

use App\Models\Setting;
use App\Support\Turnstile;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    private const ACCOUNT_ATTEMPT_LIMIT = 8;

    private const ACCOUNT_ATTEMPT_DECAY = 900;

    private const EMAIL_ATTEMPT_LIMIT = 20;

    private const EMAIL_ATTEMPT_DECAY = 3600;

    protected static string $view = 'filament.pages.auth.login';

    /**
     * Token Cloudflare Turnstile, diisi dari widget lewat komponen blade
     * <x-turnstile livewire="turnstileToken" />. Kosong saat captcha nonaktif.
     */
    public ?string $turnstileToken = null;

    public function authenticate(): ?LoginResponse
    {
        // Captcha dicek paling awal: bot tidak pernah sampai ke percobaan
        // password, jadi tidak ikut menghabiskan kuota rate limit akun.
        //
        // Token diambil dari $this->turnstileToken, bukan dari body request:
        // form login milik Livewire, dan widget menulis token ke state
        // Livewire lewat $wire.set().
        if (! Turnstile::verify(request(), Turnstile::ACTION_LOGIN, $this->turnstileToken)) {
            $this->notifyCaptchaFailed();

            return null;
        }

        if ($this->isAccountThrottled()) {
            $this->notifyThrottled();

            return null;
        }

        try {
            $response = parent::authenticate();
        } catch (ValidationException $e) {
            // Token Turnstile sudah dipakai oleh verifikasi di atas, jadi tidak
            // bisa dipakai ulang. Tanpa reset di sini, satu saja ketikan salah
            // akan membuat admin terkunci selamanya karena widget masih
            // menampilkan "sukses" dan tidak menghasilkan token baru.
            $this->invalidateCaptcha();

            throw $e;
        }

        if (Filament::auth()->check()) {
            $this->clearAccountLimiters();
        }

        return $response;
    }

    protected function throwFailureValidationException(): never
    {
        $email = $this->email();

        if ($email !== null) {
            RateLimiter::hit($this->accountRateLimitKey($email), self::ACCOUNT_ATTEMPT_DECAY);
            RateLimiter::hit($this->emailRateLimitKey($email), self::EMAIL_ATTEMPT_DECAY);
        }

        parent::throwFailureValidationException();
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Masuk';
    }

    public function getHeading(): string|Htmlable
    {
        return Setting::get('general.name', 'SULTRAKLIK');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Masuk untuk mengelola portal berita '.Setting::get('general.name', 'SULTRAKLIK');
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label('Alamat Email')
            ->placeholder('admin@sultraklik.id')
            ->prefixIcon('heroicon-m-envelope', isInline: true);
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->label('Kata Sandi')
            ->placeholder('Masukkan password Anda')
            ->prefixIcon('heroicon-m-lock-closed', isInline: true)
            ->inlineSuffix()
            ->extraInputAttributes([
                'x-on:focus' => 'caps = $event.target.getModifierState(\'CapsLock\')',
                'x-on:blur' => 'caps = false',
            ], merge: true);
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->label('Ingat saya')
            ->helperText('Tetap masuk di perangkat ini selama 30 hari.');
    }

    public function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->label('Masuk')
            ->icon('heroicon-m-arrow-right');
    }

    private function isAccountThrottled(): bool
    {
        $email = $this->email();

        if ($email === null) {
            return false;
        }

        return RateLimiter::tooManyAttempts($this->accountRateLimitKey($email), self::ACCOUNT_ATTEMPT_LIMIT)
            || RateLimiter::tooManyAttempts($this->emailRateLimitKey($email), self::EMAIL_ATTEMPT_LIMIT);
    }

    private function notifyThrottled(): void
    {
        Notification::make()
            ->title('Terlalu banyak percobaan masuk')
            ->body('Terlalu banyak percobaan masuk untuk akun ini. Silakan coba lagi beberapa saat lagi.')
            ->danger()
            ->persistent()
            ->send();
    }

    private function notifyCaptchaFailed(): void
    {
        $this->invalidateCaptcha();

        Notification::make()
            ->title('Verifikasi keamanan gagal')
            ->body('Captcha belum tercentang atau sudah kedaluwarsa. Silakan coba lagi.')
            ->danger()
            ->persistent()
            ->send();
    }

    /**
     * Buang token yang sudah dipakai dan surat widget merender ulang.
     */
    private function invalidateCaptcha(): void
    {
        if (! Turnstile::enabled()) {
            return;
        }

        $this->turnstileToken = null;

        $this->dispatch('sl-turnstile-failed');
    }

    private function clearAccountLimiters(): void
    {
        $email = $this->email();

        if ($email === null) {
            return;
        }

        RateLimiter::clear($this->accountRateLimitKey($email));
        RateLimiter::clear($this->emailRateLimitKey($email));
    }

    private function accountRateLimitKey(string $email): string
    {
        return 'filament-login:account:'.sha1($email.'|'.request()->ip());
    }

    private function emailRateLimitKey(string $email): string
    {
        return 'filament-login:email:'.sha1($email);
    }

    private function email(): ?string
    {
        $email = $this->data['email'] ?? null;

        if (! is_string($email)) {
            return null;
        }

        $email = mb_strtolower(trim($email));

        return $email === '' ? null : $email;
    }
}
