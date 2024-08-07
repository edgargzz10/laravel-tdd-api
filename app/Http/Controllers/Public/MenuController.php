<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuPublicResource;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function show(Menu $menu)
    {

        return jsonResponse(['menu' => MenuPublicResource::make($menu)]);

    }
}
