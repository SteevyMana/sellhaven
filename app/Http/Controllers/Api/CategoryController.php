<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::withCount('products')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        return Category::create($data);
    }

    public function show(Category $category)
    {
        return $category->loadCount('products');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => 'nullable|string',
        ]);

        $category->update($data);

        return $category;
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            abort(422, 'You cannot delete a category that has products assigned.');
        }

        $category->delete();

        return response()->json(null, 204);
    }
}