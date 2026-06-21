<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RestaurantController extends Controller
{
    public function index()
    {
        return Restaurant::query()
            ->with(['images', 'amenities'])
            ->orderByDesc('id')
            ->paginate(50);
    }

    public function show(Restaurant $restaurant)
    {
        return response()->json($this->present($restaurant));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $restaurant = Restaurant::create([
            ...Arr::except($data, ['amenity_ids', 'images']),
            'slug' => $data['slug'] ?? $this->uniqueSlug($data['name']),
        ]);

        $restaurant->amenities()->sync($data['amenity_ids'] ?? []);
        $this->syncImages($restaurant, $data['images'] ?? null);

        return response()->json($this->present($restaurant), 201);
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $data = $this->validateData($request, $restaurant);

        $restaurant->update(Arr::except($data, ['amenity_ids', 'images']));

        // Checkbox selection — the ticked amenities replace the previous set.
        if (array_key_exists('amenity_ids', $data)) {
            $restaurant->amenities()->sync($data['amenity_ids'] ?? []);
        }

        if (array_key_exists('images', $data)) {
            $this->syncImages($restaurant, $data['images'] ?? []);
        }

        return response()->json($this->present($restaurant));
    }

    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();

        return response()->json(['ok' => true]);
    }

    private function validateData(Request $request, ?Restaurant $restaurant = null): array
    {
        $required = $restaurant ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$required, 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('restaurants')->ignore($restaurant?->id)],
            'subtitle' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string'],
            'contact_number' => ['sometimes', 'nullable', 'string', 'max:64'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:16'],
            'rating' => ['sometimes', 'nullable', 'numeric', 'between:0,5'],
            'operating_hours' => ['sometimes', 'nullable', 'array'],
            'operating_hours.*' => ['string'],
            'qr_data' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],

            'amenity_ids' => ['sometimes', 'nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],

            'images' => ['sometimes', 'nullable', 'array'],
            'images.*.url' => ['required_with:images', 'string'],
            'images.*.sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);
    }

    /**
     * Replace the image set with the supplied list (kept simple for the admin
     * form, which sends the full ordered list each save).
     */
    private function syncImages(Restaurant $restaurant, ?array $images): void
    {
        if ($images === null) {
            return;
        }

        $restaurant->images()->delete();

        foreach (array_values($images) as $index => $image) {
            $restaurant->images()->create([
                'url' => $image['url'],
                'sort_order' => $image['sort_order'] ?? $index,
            ]);
        }
    }

    private function present(Restaurant $restaurant): array
    {
        $restaurant->load(['images', 'amenities']);

        return [
            ...$restaurant->toArray(),
            'amenity_ids' => $restaurant->amenities->pluck('id')->all(),
        ];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'restaurant';
        $slug = $base;
        $i = 2;
        while (Restaurant::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
