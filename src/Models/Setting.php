<?php

namespace Package\DocTalk\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    protected $connection = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = config('doctalk.db_connection');
    }
}
