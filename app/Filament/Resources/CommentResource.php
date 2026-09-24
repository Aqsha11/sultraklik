<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Models\Comment;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Komentar';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->disabled(),
                TextInput::make('email')
                    ->label('Email')
                    ->disabled(),
                Textarea::make('body')
                    ->label('Komentar')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_approved')
                    ->label('Disetujui'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('article.title')
                    ->label('Berita')
                    ->limit(35)
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama'),
                TextColumn::make('body')
                    ->label('Komentar')
                    ->limit(50)
                    ->searchable(),
                IconColumn::make('is_approved')
                    ->label('Disetujui')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_approved')
                    ->label('Status')
                    ->options([
                        '1' => 'Disetujui',
                        '0' => 'Pending',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Setujui')
                        ->action(fn ($records) => $records->each->update(['is_approved' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}