<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestArticles extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Berita Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(Article::query()->with(['category', 'author'])->orderByDesc('created_at')->limit(10))
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->limit(50),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge(),
                TextColumn::make('author.name')
                    ->label('Penulis'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'published' => 'Terbit',
                        'archived' => 'Arsip',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'review',
                        'success' => 'published',
                        'secondary' => 'archived',
                    ]),
                IconColumn::make('is_headline')
                    ->label('Headline')
                    ->boolean(),
                TextColumn::make('views')
                    ->label('Views'),
            ])
            ->paginated(false);
    }
}