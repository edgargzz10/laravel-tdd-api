<?php

namespace Tests\Unit;

use App\Models\Menu;
use App\Models\Plate;
use App\Models\Restaurant;
use PHPUnit\Framework\TestCase;

class IsUsingHasSortTraitTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_restaurant_model_is_using_the_has_sort_trait(): void
    {
        $restaurant = new Restaurant();

        $this->assertTrue(method_exists($restaurant, 'scopeSort'));
    }

    public function test_plate_model_is_using_the_has_sort_trait(): void
    {
        $restaurant = new Plate();

        $this->assertTrue(method_exists($restaurant, 'scopeSort'));
    }

    public function test_menu_model_is_using_the_has_sort_trait(): void
    {
        $restaurant = new Menu();

        $this->assertTrue(method_exists($restaurant, 'scopeSort'));
    }
}
