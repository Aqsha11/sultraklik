<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithFormActions, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings-page';

    protected static ?string $title = 'Pengaturan';

    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'name' => Setting::get('general.name', 'SULTRAKLIK'),
            'tagline' => Setting::get('general.tagline', 'Portal Berita Sulawesi Tenggara'),
            'description' => Setting::get('general.description', ''),
            'email' => Setting::get('general.email', ''),
            'phone' => Setting::get('general.phone', ''),
            'address' => Setting::get('general.address', ''),
            'instagram' => Setting::get('social.instagram', ''),
            'facebook' => Setting::get('social.facebook', ''),
            'twitter' => Setting::get('social.twitter', ''),
            'youtube' => Setting::get('social.youtube', ''),
            'primary_color' => Setting::get('theme.primary_color', '#dc2626'),
            'accent_color' => Setting::get('theme.accent_color', '#dc2626'),
            'logo' => Setting::get('theme.logo', ''),
            'favicon' => Setting::get('theme.favicon', ''),
            'share_image' => Setting::get('theme.share_image', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Umum')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nama Website')->required(),
                        TextInput::make('tagline')->label('Tagline')->required(),
                        Textarea::make('description')->label('Deskripsi')->columnSpanFull(),
                        TextInput::make('email')->label('Email'),
                        TextInput::make('phone')->label('Telepon/WhatsApp'),
                        Textarea::make('address')->label('Alamat Redaksi')->columnSpanFull(),
                    ]),
                Section::make('Media Sosial')
                    ->icon('heroicon-o-share')
                    ->columns(2)
                    ->schema([
                        TextInput::make('instagram'),
                        TextInput::make('facebook'),
                        TextInput::make('twitter')->label('X (Twitter)'),
                        TextInput::make('youtube'),
                    ]),
                Section::make('Logo & Favicon')
                    ->icon('heroicon-o-photo')
                    ->description('Logo tampil di header & footer website; favicon adalah ikon kecil di tab browser.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo Website')
                            ->image()
                            ->disk('public')
                            ->directory('uploads/settings')
                            ->visibility('public')
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth(400)
                            ->maxSize(2048)
                            ->helperText('PNG/JPG/WebP transparan, maks 2 MB.'),
                        FileUpload::make('favicon')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('uploads/settings')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/svg+xml'])
                            ->maxSize(512)
                            ->helperText('PNG/ICO/SVG. Ukuran kecil (32x32 px) paling ideal.'),
                        FileUpload::make('share_image')
                            ->label('Gambar Default Saat Dibagikan (OG Image)')
                            ->image()
                            ->disk('public')
                            ->directory('uploads/settings')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Thumbnail saat link dibagikan ke WhatsApp/Facebook/X. Ideal 1200x630 px. Dipakai untuk semua halaman yang tak punya gambar berita.')
                            ->columnSpanFull(),
                    ]),
                Section::make('Tampilan')
                    ->icon('heroicon-o-swatch')
                    ->description('Warna utama dipakai di seluruh tampilan website (header, menu, badge berita, link). Warna aksen untuk link & kutipan di dalam isi berita.')
                    ->columns(2)
                    ->schema([
                        ColorPicker::make('primary_color')
                            ->label('Warna Utama')
                            ->helperText('Contoh: #dc2626 (merah SULTRAKLIK)')
                            ->required(),
                        ColorPicker::make('accent_color')
                            ->label('Warna Aksen')
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
        } catch (Halt $exception) {
            return;
        }

        foreach ($data as $key => $value) {
            $group = match ($key) {
                'instagram', 'facebook', 'twitter', 'youtube' => 'social',
                'primary_color', 'accent_color', 'logo', 'favicon', 'share_image' => 'theme',
                default => 'general',
            };
            $value = $value ?? '';
            $setting = Setting::query()->updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['value' => $value]
            );
            $setting->save();
        }

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->submit('save')
                ->icon('heroicon-o-check'),
        ];
    }
}
