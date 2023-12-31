<?php

namespace App\Models;

use Iksaku\Laravel\MassUpdate\MassUpdatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostMeta extends Model
{
    use HasFactory, MassUpdatable;

    protected $table = 'wp_postmeta';
    public $timestamps = false;

    protected $fillable = ['post_id', 'meta_key', 'meta_value', 'meta_id'];
}
