<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('chat');
});

Route::post('/upload-document', [DocumentController::class, 'upload'])->name('document.upload');
Route::get('/documents', [DocumentController::class, 'list'])->name('document.list');
Route::post('/chat', [ChatController::class, 'chat'])->name('chat.send');
Route::get('/chat/history', [ChatController::class, 'listHistory'])->name('chat.history');
Route::get('/chat/history/{threadId}', [ChatController::class, 'loadConversation'])->name('chat.load');
