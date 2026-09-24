<?php

namespace App\Filament\Pages;

use App\Models\Article;
use App\Models\Headline;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

class ManageHeadlines extends Page implements HasForms
{
    use InteractsWithFormActions, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static string $view = 'filament.pages.manage-headlines';

    protected static ?string $title = 'Headline';

    protected static ?string $navigationLabel = 'Headline';

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public function mount(): void
    {
        $headlines = Headline::active()->orderBy('position')->get();

        $items = collect(range(1, 6))->map(function (int $position) use ($headlines) {
            $headline = $headlines->firstWhere('position', $position);

            return [
                'position' => $position,
                'article_id' => $headline?->article_id,
            ];
        });

        $this->form->fill(['items' => $items->values()->all()]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Susunan Headline')
                    ->description('Posisi 1 = Headline Utama. Posisi 2-6 = Headline Pendamping. Simpan untuk menerapkan.')
                    ->icon('heroicon-o-star')
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('position')
                                        ->label('Posisi')
                                        ->options([
                                            1 => '1 - Headline Utama',
                                            2 => '2 - Pendamping',
                                            3 => '3 - Pendamping',
                                            4 => '4 - Pendamping',
                                            5 => '5 - Pendamping',
                                            6 => '6 - Pendamping',
                                        ])
                                        ->required(),
                                    Select::make('article_id')
                                        ->label('Berita')
                                        ->options(fn (): array => Article::query()
                                            ->published()
                                            ->orderByDesc('published_at')
                                            ->limit(100)
                                            ->get()
                                            ->mapWithKeys(fn (Article $article) => [
                                                $article->id => $article->title,
                                            ])
                                            ->all())
                                        ->searchable()
                                        ->required()
                                        ->preload(),
                                ]),
                            ])
                            ->columns(1)
                            ->defaultItems(6)
                            ->maxItems(6)
                            ->addable(false)
                            ->reorderable(true)
                            ->deletable(false),
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

        $items = collect($data['items'] ?? [])
            ->reject(fn ($item) => empty($item['article_id']))
            ->values();

        Headline::query()->delete();

        foreach ($items as $item) {
            Headline::create([
                'position' => $item['position'],
                'article_id' => $item['article_id'],
                'is_active' => true,
            ]);

            Article::query()->whereKey($item['article_id'])->update([
                'is_headline' => true,
                'is_featured' => false,
            ]);
        }

        Article::where('is_headline', true)
            ->whereNotIn('id', $items->pluck('article_id'))
            ->update(['is_headline' => false]);

        Notification::make()
            ->title('Headline berhasil diperbarui')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Headline')
                ->submit('save')
                ->icon('heroicon-o-check'),
        ];
    }
}
