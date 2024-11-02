<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Service\ServiceController;

class CategoryController extends Controller
{
    protected $serviceController;

    public function __construct(ServiceController $serviceController)
    {
        $this->serviceController = $serviceController;
    }

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
        if ($request->hasFile('image')) {
            $imageUrl = $this->serviceController->uploadImage($request->file('image'));
            
            $category = Category::create([
                ...$request->except('image'),
                'image' => $imageUrl
            ]);
        } else {
            $category = Category::create($request->except('image'));
        }

        return redirect()->route('dashboard.category.index');
    }

    public function edit(Category $category)
    {
        return view('pages.dashboard.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        if ($request->hasFile('image')) {
            $imageUrl = $this->serviceController->uploadImage($request->file('image'));
            
            $category->update([
                ...$request->except('image'),
                'image' => $imageUrl
            ]);
        } else {
            $category->update($request->except('image'));
        }

        return redirect()->route('dashboard.category.index');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('dashboard.category.index')->with('success', 'Kategori berhasil dihapus');
    }
}
