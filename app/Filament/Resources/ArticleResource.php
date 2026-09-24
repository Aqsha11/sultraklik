<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\ArticleResource\RelationManagers;
use App\Models\Article;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Kahusoftware\FilamentCkeditorField\CKEditor;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Berita';

    protected static ?string $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 1, 'lg' => 3])->schema([
                    Section::make('Informasi Dasar')
                        ->description('Judul, kategori, dan penulis berita.')
                        ->icon('heroicon-o-document-text')
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make('title')
                                ->label('Judul Berita')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                            Grid::make(2)->schema([
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Slug menentukan URL artikel.'),
                                Select::make('status')
                                    ->options([
                                        Article::STATUS_DRAFT => 'Draft',
                                        Article::STATUS_REVIEW => 'Review',
                                        Article::STATUS_PUBLISHED => 'Terbit',
                                        Article::STATUS_ARCHIVED => 'Arsip',
                                    ])
                                    ->default(Article::STATUS_DRAFT)
                                    ->required(),
                            ]),
                            Grid::make(3)->schema([
                                Select::make('category_id')
                                    ->label('Kategori')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')->required(),
                                        TextInput::make('slug'),
                                    ])
                                    ->required(),
                                Select::make('region_id')
                                    ->label('Wilayah')
                                    ->relationship('region', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')->required(),
                                        TextInput::make('slug'),
                                    ])
                                    ->helperText('Pilih hanya untuk berita wilayah Sultra.'),
                                Select::make('author_id')
                                    ->label('Penulis')
                                    ->relationship('author', 'name')
                                    ->default(fn () => auth()->id())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                            Grid::make(2)->schema([
                                DateTimePicker::make('published_at')
                                    ->label('Tanggal Terbit')
                                    ->default(now())
                                    ->displayFormat('d M Y, H:i'),
                                DateTimePicker::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->hidden(),
                            ]),
                        ]),
                    Section::make('Foto Utama')
                        ->description('Featured image dan keterangan foto.')
                        ->icon('heroicon-o-photo')
                        ->columnSpan(1)
                        ->schema([
                            FileUpload::make('featured_image')
                                ->label('Foto Utama')
                                ->image()
                                ->imageEditor()
                                ->directory('featured')
                                ->maxSize(5120)
                                ->columnSpanFull(),
                            TextInput::make('image_caption')
                                ->label('Caption')
                                ->maxLength(255),
                            TextInput::make('image_credit')
                                ->label('Kredit Foto')
                                ->maxLength(255),
                        ]),
                    Section::make('Konten Berita')
                        ->description('Isi berita menggunakan CKEditor.')
                        ->icon('heroicon-o-pencil-square')
                        ->columnSpanFull()
                        ->schema([
                            Textarea::make('excerpt')
                                ->label('Ringkasan (Excerpt)')
                                ->rows(3)
                                ->helperText('Ringkasan singkat yang tampil di daftar berita.'),
                            CKEditor::make('content')
                                ->label('Isi Berita')
                                ->required()
                                ->uploadUrl('/upload-image')
                                ->height('120px')
                                ->minHeight('400px')
                                ->disablePlugins([
                                    'FontColor',
                                    'FontBackgroundColor',
                                ])
                                ->placeholder('Mulai tulis berita di sini...')
                                ->columnSpanFull(),
                        ]),
                    Section::make('Tags & Prioritas')
                        ->icon('heroicon-o-tag')
                        ->columnSpanFull()
                        ->schema([
                            Grid::make(['default' => 1, 'lg' => 3])->schema([
                                CheckboxList::make('tags')
                                    ->label('Tags')
                                    ->relationship('tags', 'name')
                                    ->columns(2),
                                Toggle::make('is_headline')
                                    ->label('Headline')
                                    ->helperText('Jadikan berita ini headline (posisi dikelola di menu Headline). Berita headline tidak bisa jadi berita pilihan (tidak boleh duplikat).')
                                    ->live(),
                                Toggle::make('is_featured')
                                    ->label('Berita Pilihan')
                                    ->helperText('Ditandai sebagai berita unggulan. Berita headline tidak bisa jadi pilihan (tidak boleh duplikat).')
                                    ->disabled(fn (Get $get) => (bool) $get('is_headline')),
                            ]),
                        ]),
                    Section::make('SEO')
                        ->description('Optimasi mesin pencari untuk artikel ini.')
                        ->icon('heroicon-o-magnifying-glass-circle')
                        ->collapsible()
                        ->collapsed()
                        ->columnSpanFull()
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('seo_title')
                                    ->label('SEO Title')
                                    ->helperText('Kosongkan untuk memakai judul berita.'),
                                TextInput::make('og_image')
                                    ->label('OG Image')
                                    ->helperText('URL gambar khusus untuk Open Graph. Kosongkan untuk memakai foto utama.'),
                                Textarea::make('seo_description')
                                    ->label('Meta Description')
                                    ->rows(3),
                            ]),
                        ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('featured_image')
                    ->disk('public')
                    ->circular()
                    ->width(56),
                TextColumn::make('title')
                    ->label('Judul')
                    ->limit(45)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ?? 'gray'),
                TextColumn::make('region.name')
                    ->label('Wilayah')
                    ->badge()
                    ->color('info'),
                TextColumn::make('author.name')
                    ->label('Penulis')
                    ->toggleable(),
                Tables\Columns\ToggleColumn::make('is_headline')
                    ->label('Headline'),
                Tables\Columns\ToggleColumn::make('is_featured')
                    ->label('Pilihan'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => Article::STATUS_DRAFT,
                        'warning' => Article::STATUS_REVIEW,
                        'success' => Article::STATUS_PUBLISHED,
                        'secondary' => Article::STATUS_ARCHIVED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Article::STATUS_DRAFT => 'Draft',
                        Article::STATUS_REVIEW => 'Review',
                        Article::STATUS_PUBLISHED => 'Terbit',
                        Article::STATUS_ARCHIVED => 'Arsip',
                    }),
                TextColumn::make('views')
                    ->label('Views')
                    ->sortable(),
                TextColumn::make('comments_count')
                    ->label('Komentar')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                SelectFilter::make('region_id')
                    ->label('Wilayah')
                    ->relationship('region', 'name'),
                SelectFilter::make('author_id')
                    ->label('Penulis')
                    ->relationship('author', 'name'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Article::STATUS_DRAFT => 'Draft',
                        Article::STATUS_REVIEW => 'Review',
                        Article::STATUS_PUBLISHED => 'Terbit',
                        Article::STATUS_ARCHIVED => 'Arsip',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Terbitkan')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            $records->each(function (Article $article) {
                                $article->update([
                                    'status' => Article::STATUS_PUBLISHED,
                                    'published_at' => $article->published_at ?: now(),
                                ]);
                            });
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('archive')
                        ->label('Arsipkan')
                        ->icon('heroicon-o-archive-box')
                        ->action(fn ($records) => $records->each->update(['status' => Article::STATUS_ARCHIVED]))
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (! auth()->user()?->isEditor()) {
            $query->where('author_id', auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
