<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AmenityController extends Controller
{
    /**
     * All amenities — used to render the checkbox options in the admin form.
     */
    public function index()
    {
        return Amenity::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['name']);

        return response()->json(Amenity::create($data), 201);
    }

    public function update(Request $request, Amenity $amenity)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'icon' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('amenities')->ignore($amenity->id)],
        ]);

        $amenity->update($data);

        return response()->json($amenity);
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->delete();

        return response()->json(['ok' => true]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'amenity';
        $slug = $base;
        $i = 2;
        while (Amenity::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
