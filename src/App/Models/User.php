<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель таблицы Users
 */
class User extends Model
{
    /**
     * @var bool отключает запись временных меток
     */
    public $timestamps = false;

    protected $guarded = [];

}
