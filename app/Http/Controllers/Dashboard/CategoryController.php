<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('pages.dashboard.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('pages.dashboard.categories.create');
    }

    public function store(Request $request)
    {
        $category = Category::create($request->all());
        return redirect()->route('dashboard.category.index')->with('success', 'Kategori berhasil ditambahkan');
    }

    public function edit(Category $category)
    {
        return view('pages.dashboard.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($request->all());
        return redirect()->route('dashboard.category.index')->with('success', 'Kategori berhasil diubah');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('dashboard.category.index')->with('success', 'Kategori berhasil dihapus');
    }
}
