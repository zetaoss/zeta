<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'zetawiki.z_comment';

    public $timestamps = false;
}
