<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Media';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('path')
                    ->label('File')
                    ->image()
                    ->imageEditor()
                    ->directory('media')
                    ->maxSize(10240)
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $path = is_array($state) ? $state[0] : $state;
                            $name = pathinfo($path, PATHINFO_FILENAME);
                            $set('filename', $name);
                        }
                    }),
                TextInput::make('filename')
                    ->label('Nama File')
                    ->required(),
                TextInput::make('alt')
                    ->label('Teks Alt'),
                TextInput::make('caption')
                    ->label('Caption'),
                TextInput::make('credit')
                    ->label('Kredit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('path')
                    ->disk('public')
                    ->width(64)
                    ->height(48),
                TextColumn::make('filename')
                    ->label('File')
                    ->searchable(),
                TextColumn::make('human_size')
                    ->label('Ukuran'),
                TextColumn::make('mime_type')
                    ->label('Tipe')
                    ->toggleable(),
                TextColumn::make('uploader.name')
                    ->label('Diupload Oleh')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
