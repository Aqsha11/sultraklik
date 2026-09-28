<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsAccessByRole;
use App\Filament\Resources\BreakingNewsResource\Pages;
use App\Models\BreakingNews;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BreakingNewsResource extends Resource
{
    use RestrictsAccessByRole;

    protected static ?string $model = BreakingNews::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Breaking News';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->placeholder('Tulis teks breaking news di sini...')
                    ->maxLength(255),
                TextInput::make('url')
                    ->label('URL (opsional)')
                    ->placeholder('https://... (link tujuan)')
                    ->helperText('Link tujuan jika breaking news diklik.'),
                DateTimePicker::make('starts_at')
                    ->label('Mulai')
                    ->placeholder('Pilih tanggal & jam')
                    ->helperText('Kosongkan agar langsung tayang.')
                    ->default(now()),
                DateTimePicker::make('ends_at')
                    ->label('Berakhir')
                    ->placeholder('Pilih tanggal & jam')
                    ->helperText('Kosongkan agar tidak pernah kedaluwarsa. Baris dengan waktu awal/akhir yang terlewat akan disembunyikan dari ticker.'),
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
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->dateTime('d M Y, H:i'),
                TextColumn::make('ends_at')
                    ->label('Berakhir')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('—'),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([])
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
            'index' => Pages\ListBreakingNews::route('/'),
            'create' => Pages\CreateBreakingNews::route('/create'),
            'edit' => Pages\EditBreakingNews::route('/{record}/edit'),
        ];
    }
}
