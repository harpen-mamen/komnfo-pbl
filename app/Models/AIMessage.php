<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AIMessage extends Model
{
    protected $table = 'ai_messages';

    protected $fillable = ['conversation_id', 'role', 'content', 'token_input', 'token_output'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AIConversation::class, 'conversation_id');
    }
}
