<?php

use Illuminate\Support\Facades\Route;
use Package\DocTalk\Livewire\Pages\Admin\AddDocs;
use Package\DocTalk\Livewire\Pages\Admin\Dashboard;
use Package\DocTalk\Livewire\Pages\Admin\Settings;
use Package\DocTalk\Livewire\Pages\Chat;

// Normal users routes group with chat middleware
Route::group([
    'prefix' => config('doctalk.path', 'doctalk'),
    'middleware' => array_merge(['web'], (array)config('doctalk.chat_middleware')),
], function () {
    Route::get('/chat/{conversation?}', Chat::class)->name('doctalk.chat');
});

// Admin users routes group with admin middleware
Route::group([
    'prefix' => config('doctalk.path', 'doctalk') . '/admin',
    'middleware' => array_merge(['web'], (array)config('doctalk.admin_middleware')),
], function () {
    Route::get('/', Dashboard::class)->name('doctalk.admin');
    Route::get('/docs', AddDocs::class)->name('doctalk.docs');
    Route::get('/settings', Settings::class)->name('doctalk.settings');
});
