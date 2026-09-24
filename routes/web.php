<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SiteMapController;
use App\Http\Controllers\Admin\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/sitemap', [SiteMapController::class, 'index']);
Route::get('/sitemap.xml', [SiteMapController::class, 'index']);
Route::get('/rss.xml', [SiteMapController::class, 'rss']);
Route::get('/search', [SearchController::class, 'index']);

Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/kategori/{category:slug}', [CategoryController::class, 'show']);
Route::get('/tag/{tag:slug}', [CategoryController::class, 'byTag'])->name('tag.show');
Route::get('/page/{page:slug}', [PageController::class, 'show'])->name('page.show');

Route::get('/sultra', [CategoryController::class, 'sultra'])->name('sultra');
Route::get('/sultra/{region:slug}', [CategoryController::class, 'byRegion'])->name('region.show');

Route::middleware('auth')->post('/upload-image', [UploadController::class, 'image']);

Route::get('/{article:slug}', [ArticleController::class, 'show'])->name('article.show');