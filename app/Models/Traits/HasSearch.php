<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;


trait HasSearch
{
    public function searchFields()
    {
        return [];
    }

    public function scopeSearch(Builder $builder, $search = '')
    {
        $search = $search ?: request()->get('search');
        if(!$search) return;
        $fields = $this->searchFields();

        $builder->where(function (Builder $builder) use ($fields, $search) {
            //dos palabras
            foreach (explode(' ', $search) as $word) {
                $builder->whereAny($fields, operator:'LIKE', value:"%{$word}%");
            }
        });
    }
}
