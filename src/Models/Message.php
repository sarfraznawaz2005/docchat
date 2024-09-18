<?php

namespace Package\DocTalk\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $guarded = [];

    protected $connection = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = config('doctalk.db_connection');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
