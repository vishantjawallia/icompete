<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Categories
    public function index()
    {
        $categories = cache()->remember('contest_categories', 60 * 60, function () {
            return Category::all();
        });

        return view('admin.contests.categories', compact('categories'));
    }

    // Store a new category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories|max:155',
        ]);
        $data = $request->all();
        $data['slug'] = uniqueSlug($request->name, 'categories');
        $category = Category::create($data);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Category added successfully.',
            'category' => $category,
        ]);
    }

    // Update an existing category
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'   => 'required|max:255|unique:categories,name,' . $id,
            'status' => 'required|string',
        ]);

        $category = Category::findOrFail($id);
        $category->update($request->only('name', 'status'));

        return response()->json([
            'status'   => 'success',
            'message'  => 'Category updated successfully.',
            'category' => $category,
        ]);
    }

    // Delete a category
    public function delete($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Category deleted successfully.',
        ]);
    }
}
