<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\ArticleResource\RelationManagers;
use App\Models\Article;
use App\Rules\YouTubeUrl;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
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
use Illuminate\Support\HtmlString;
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
                                ->placeholder('Tulis judul berita di sini...')
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                            Grid::make(2)->schema([
                                TextInput::make('slug')
                                    ->required()
                                    ->placeholder('contoh: kunjungan-walikota-kendari')
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Slug menentukan URL artikel.'),
                                Select::make('status')
                                    ->placeholder('Pilih status...')
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
                                    ->placeholder('Pilih kategori...')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')->placeholder('Nama kategori baru...')->required(),
                                        TextInput::make('slug')->placeholder('contoh: politik')->required(),
                                    ])
                                    ->required(),
                                Select::make('region_id')
                                    ->label('Wilayah')
                                    ->placeholder('Pilih wilayah...')
                                    ->relationship('region', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')->placeholder('Nama wilayah baru...')->required(),
                                        TextInput::make('slug')->placeholder('contoh: kendari')->required(),
                                    ])
                                    ->helperText('Pilih hanya untuk berita wilayah Sultra.'),
                                Select::make('author_id')
                                    ->label('Penulis')
                                    ->placeholder('Pilih penulis...')
                                    ->relationship('author', 'name')
                                    ->default(fn () => auth()->id())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                            Grid::make(2)->schema([
                                DateTimePicker::make('published_at')
                                    ->label('Tanggal Terbit')
                                    ->placeholder('Pilih tanggal & jam')
                                    ->default(now())
                                    ->displayFormat('d M Y, H:i'),
                                DateTimePicker::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->placeholder('—')
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
                                ->disk('public')
                                ->directory('featured')
                                ->maxSize(5120)
                                ->columnSpanFull(),
                            TextInput::make('image_caption')
                                ->label('Caption')
                                ->placeholder('Keterangan singkat foto...')
                                ->maxLength(255),
                            TextInput::make('image_credit')
                                ->label('Kredit Foto')
                                ->placeholder('Nama fotografer / sumber foto')
                                ->maxLength(255),
                        ]),
                    Section::make('Video YouTube')
                        ->description('Tautan YouTube saja. Video diputar langsung di halaman berita, pembaca tidak diarahkan ke YouTube.')
                        ->icon('heroicon-o-play-circle')
                        ->columnSpan(1)
                        ->schema([
                            TextInput::make('video_url')
                                ->label('Link YouTube')
                                ->placeholder('https://www.youtube.com/watch?v=xxxxxxxxxxx')
                                ->maxLength(255)
                                ->live()
                                ->rules([
                                    'nullable',
                                    'string',
                                    'max:255',
                                    new YouTubeUrl,
                                ])
                                ->helperText('Mendukung youtube.com/watch, youtu.be, /shorts, /live, dan /embed. Kosongkan bila berita tidak punya video.'),
                            Placeholder::make('video_preview')
                                ->label('')
                                ->content(fn (Get $get) => self::videoPreviewHtml($get('video_url')))
                                ->visible(fn (Get $get) => Article::youtubeId((string) $get('video_url')) !== null),
                        ]),
                    Section::make('Konten Berita')
                        ->description('Isi berita menggunakan CKEditor.')
                        ->icon('heroicon-o-pencil-square')
                        ->columnSpanFull()
                        ->schema([
                            Textarea::make('excerpt')
                                ->label('Ringkasan (Excerpt)')
                                ->placeholder('Tulis ringkasan singkat 2-3 kalimat...')
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
                                    ->placeholder('Judul khusus untuk hasil pencarian')
                                    ->helperText('Kosongkan untuk memakai judul berita.'),
                                TextInput::make('og_image')
                                    ->label('OG Image')
                                    ->placeholder('https://contoh.com/gambar.jpg')
                                    ->helperText('URL gambar khusus untuk Open Graph. Kosongkan untuk memakai foto utama.'),
                                Textarea::make('seo_description')
                                    ->label('Meta Description')
                                    ->placeholder('Deskripsi singkat untuk hasil pencarian...')
                                    ->rows(3),
                            ]),
                        ]),
                ]),
            ]);
    }

    /**
     * Pratinjau thumbnail YouTube di bawah form. ID video sudah divalidasi
     * oleh Article::youtubeId(), jadi hanya karakter aman yang dirender.
     */
    private static function videoPreviewHtml(mixed $url): HtmlString
    {
        $id = Article::youtubeId(is_string($url) ? $url : null);

        if ($id === null) {
            return new HtmlString('<span class="text-sm text-gray-400">Belum ada video.</span>');
        }

        $playIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 ml-0.5"><path d="M8 5.14v13.72L19 12 8 5.14Z"/></svg>';

        return new HtmlString(
            '<div class="flex items-center gap-3">'
            .'<div class="relative shrink-0 w-40 aspect-[16/9] rounded-lg overflow-hidden bg-gray-900">'
            .'<img src="https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg" alt="Thumbnail video" class="w-full h-full object-cover" loading="lazy">'
            .'<span class="absolute inset-0 m-auto h-9 w-9 flex items-center justify-center rounded-full bg-red-600 text-white shadow">'.$playIcon.'</span>'
            .'</div>'
            .'<div class="text-xs leading-relaxed text-gray-500">'
            .'<p class="font-semibold text-gray-700">Video siap ditonton</p>'
            .'<p>ID video: '.$id.'</p>'
            .'<p>Pemutar muncul di halaman berita, tanpa keluar dari situs.</p>'
            .'</div></div>'
        );
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
