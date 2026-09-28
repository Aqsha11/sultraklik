<?php

namespace App\Filament\Concerns;

use LogicException;

/**
 * Membatasi akses halaman admin berdasarkan peran user. Resource/Filament page
 * yang memakai trait ini akan otomatis 403 dan disembunyikan dari navigasi
 * bila user tidak memenuhi peran yang diminta.
 */
trait RestrictsAccessByRole
{
    /** @var list<string> Nama method User yang boleh diakses. */
    protected static array $availableRoleChecks = ['isSuperAdmin', 'isAdmin', 'isEditor'];

    protected static function requiredRoleCheck(): string
    {
        return 'isEditor';
    }

    public static function canAccess(): bool
    {
        $check = static::requiredRoleCheck();

        if (! in_array($check, static::$availableRoleChecks, true)) {
            throw new LogicException(static::class.' memakai peran tidak dikenal: '.$check);
        }

        $user = auth()->user();

        return $user !== null && (bool) $user->{$check}();
    }
}
