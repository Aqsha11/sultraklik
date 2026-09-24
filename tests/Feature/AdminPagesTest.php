<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render_for_authenticated_user(): void
    {
        $admin = User::where('email', 'admin@sultraklik.com')->first()
            ?? User::create([
                'name' => 'Admin',
                'email' => 'admin@sultraklik.com',
                'password' => 'password',
                'role' => User::ROLE_SUPER_ADMIN,
            ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/articles')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/articles/create')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/categories')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/regions')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/tags')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/media')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/comments')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/breaking-news')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/advertisements')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/pages')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/settings-page')
            ->assertOk()
            ->assertSee('Warna Utama')
            ->assertSee('Warna Aksen');

        $this->actingAs($admin)
            ->get('/admin/manage-headlines')
            ->assertOk();
    }
}
