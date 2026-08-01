<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedPostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Version 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public / Guest Auth routes
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    });

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::get('/user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

        // Explore & Search
        Route::get('/explore/posts', [ExploreController::class, 'posts'])->name('explore.posts');
        Route::get('/explore/users', [ExploreController::class, 'users'])->name('explore.users');

        // Saved Posts / Bookmarks
        Route::get('/saved-posts', [SavedPostController::class, 'index'])->name('saved-posts.index');
        Route::post('/posts/{post}/save', [SavedPostController::class, 'store'])->name('posts.save.store');
        Route::delete('/posts/{post}/save', [SavedPostController::class, 'destroy'])->name('posts.save.destroy');

        // Posts
        Route::apiResource('posts', PostController::class);

        // Likes
        Route::post('/posts/{post}/likes', [LikeController::class, 'store'])->name('posts.likes.store');
        Route::delete('/posts/{post}/likes', [LikeController::class, 'destroy'])->name('posts.likes.destroy');

        // Comments
        Route::get('/posts/{post}/comments', [CommentController::class, 'index'])->name('posts.comments.index');
        Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
        Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

        // Profiles
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/users/{username}', [ProfileController::class, 'showByUsername'])->name('users.profile');
        Route::get('/users/{username}/posts', [ProfileController::class, 'userPosts'])->name('users.posts');
    });
});
