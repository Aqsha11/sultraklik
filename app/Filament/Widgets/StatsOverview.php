<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Media;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Berita', number_format(Article::count()))
                ->description('Semua status')
                ->chart(Article::query()
                    ->selectRaw('DATE(created_at) as d, count(*) as c')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->groupBy('d')
                    ->pluck('c')
                    ->values()
                    ->all())
                ->color('primary'),
            Stat::make('Draft', number_format(Article::where('status', Article::STATUS_DRAFT)->count()))
                ->description('Belum dipublikasikan')
                ->color('gray'),
            Stat::make('Terbit', number_format(Article::where('status', Article::STATUS_PUBLISHED)->count()))
                ->description('Berita online')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Total Views', number_format(Article::where('status', Article::STATUS_PUBLISHED)->sum('views')))
                ->description('Dari seluruh artikel terbit')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
            Stat::make('Komentar', number_format(Comment::count()))
                ->description(Comment::where('is_approved', false)->count().' menunggu persetujuan')
                ->color('warning'),
            Stat::make('Media', number_format(Media::count()))
                ->description('File di perpustakaan')
                ->color('gray'),
        ];
    }
}
