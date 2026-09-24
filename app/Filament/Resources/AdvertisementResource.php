<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertisementResource\Pages;
use App\Models\Advertisement;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Iklan';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    public const POSITIONS = [
        'homepage_top' => 'Homepage Top Banner (970x90)',
        'homepage_middle' => 'Homepage Middle Banner',
        'sidebar' => 'Sidebar',
        'article_top' => 'Article Top',
        'article_middle' => 'Article Middle',
        'article_bottom' => 'Article Bottom',
        'mobile' => 'Mobile Banner',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                Select::make('position')
                    ->label('Posisi')
                    ->options(self::POSITIONS)
                    ->required(),
                Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'image' => 'Gambar',
                        'code' => 'Kode (HTML/JS)',
                    ])
                    ->default('image')
                    ->live()
                    ->required(),
                FileUpload::make('image')
                    ->label('Banner')
                    ->image()
                    ->directory('ads')
                    ->visible(fn (Get $get) => $get('type') === 'image'),
                Textarea::make('code')
                    ->label('Kode Iklan')
                    ->rows(4)
                    ->visible(fn (Get $get) => $get('type') === 'code'),
                TextInput::make('url')
                    ->label('URL Tujuan')
                    ->url(),
                DateTimePicker::make('starts_at')
                    ->label('Mulai Tampil'),
                DateTimePicker::make('ends_at')
                    ->label('Selesai Tampil'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('image')
                    ->disk('public')
                    ->width(120)
                    ->height(40),
                TextColumn::make('title')
                    ->label('Iklan')
                    ->searchable(),
                TextColumn::make('position')
                    ->label('Posisi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::POSITIONS[$state] ?? $state),
                TextColumn::make('clicks')
                    ->label('Klik')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
                TextColumn::make('ends_at')
                    ->label('Berakhir')
                    ->dateTime('d M Y')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('position')
                    ->label('Posisi')
                    ->options(self::POSITIONS),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdvertisements::route('/'),
            'create' => Pages\CreateAdvertisement::route('/create'),
            'edit' => Pages\EditAdvertisement::route('/{record}/edit'),
        ];
    }
}