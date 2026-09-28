<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsAccessByRole;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    use RestrictsAccessByRole;

    protected static function requiredRoleCheck(): string
    {
        return 'isSuperAdmin';
    }

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $navigationGroup = 'Manajemen';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->placeholder('Nama lengkap pengguna...')
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->placeholder('nama@contoh.com')
                    ->unique(ignoreRecord: true),
                Select::make('role')
                    ->label('Peran')
                    ->placeholder('Pilih peran...')
                    ->options([
                        User::ROLE_SUPER_ADMIN => 'Super Admin',
                        User::ROLE_ADMIN => 'Admin',
                        User::ROLE_EDITOR => 'Editor',
                        User::ROLE_REPORTER => 'Reporter',
                    ])
                    ->required()
                    ->default(User::ROLE_REPORTER)
                    ->disabled(fn (): bool => $form->getRecord()?->id === auth()->id())
                    ->helperText('Peran akun sendiri tidak dapat diubah dari sini.')
                    ->native(false),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->placeholder('Kosongkan jika tidak diubah')
                    ->revealable()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    // Sama dengan aturan UserSeeder di produksi. Field ini
                    // nullable saat edit, jadi minLength hanya jalan kalau
                    // memang ada password baru yang dikirim.
                    ->minLength(12)
                    ->helperText('Minimal 12 karakter.')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->colors([
                        'danger' => User::ROLE_SUPER_ADMIN,
                        'warning' => User::ROLE_ADMIN,
                        'info' => User::ROLE_EDITOR,
                        'gray' => User::ROLE_REPORTER,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        User::ROLE_SUPER_ADMIN => 'Super Admin',
                        User::ROLE_ADMIN => 'Admin',
                        User::ROLE_EDITOR => 'Editor',
                        User::ROLE_REPORTER => 'Reporter',
                    }),
                TextColumn::make('articles_count')
                    ->label('Berita')
                    ->counts('articles'),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->id !== auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('articles');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
