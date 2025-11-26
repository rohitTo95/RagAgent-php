<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatHistory extends Model
{
    protected $connection = 'ragagent';
    protected $table = 'chat_history';
    
    protected $fillable = [
        'thread_id',
        'messages',
        'title',
    ];

    protected $casts = [
        'messages' => 'array',
    ];
}
