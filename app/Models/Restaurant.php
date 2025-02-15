<?php

namespace App\Models;

use App\Models\Traits\HasSearch;
use App\Models\Traits\HasSort;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory, HasSearch, HasSort;

    protected $guarded = [];

    public function plates()
    {
        return $this->hasMany(Plate::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function searchFields()
    {
        return ['name', 'description'];
    }

    public function sortFields()
    {
        return ['id', 'name', 'description', 'created_at'];
    }
}
