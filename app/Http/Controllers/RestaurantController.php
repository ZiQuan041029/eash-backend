<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;

class RestaurantController extends Controller
{
    /**
     * Public listing of active restaurants for the app.
     */
    public function index()
    {
        return Restaurant::query()
            ->where('is_active', true)
            ->with([
                'images',
                'amenities' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            ])
            ->orderBy('name')
            ->paginate(50);
    }

    /**
     * Public detail — only ticked + active amenities are returned.
     */
    public function show(Restaurant $restaurant)
    {
        abort_unless($restaurant->is_active, 404);

        $restaurant->load([
            'images',
            'amenities' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
        ]);

        return response()->json($restaurant);
    }
}
