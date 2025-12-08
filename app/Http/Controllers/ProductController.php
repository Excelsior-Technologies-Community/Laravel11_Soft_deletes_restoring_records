<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Show all products WITHOUT search & pagination
    public function index()
    {
        $products = Product::where('status', '!=', 'deleted')
                           ->latest()
                           ->get();   // ✔ NO PAGINATION

        return view('products.index', compact('products'));
    }

    // Show create form
    public function create()
    {
        return view('products.create');
    }

    // Store product
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required',
            'details'   => 'required',
            'size'      => 'required',
            'color'     => 'required',
            'category'  => 'required',
            'price'     => 'required|numeric',
            'image'     => 'required|image|max:2048',
        ]);

        // Upload single image
        $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
        $request->image->move(public_path('images'), $imageName);

        Product::create([
            'name'      => $request->name,
            'details'   => $request->details,
            'size'      => $request->size,
            'color'     => $request->color,
            'category'  => $request->category,
            'price'     => $request->price,
            'image'     => 'images/' . $imageName,
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    // Edit form
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    // Update product
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'      => 'required',
            'details'   => 'required',
            'size'      => 'required',
            'color'     => 'required',
            'category'  => 'required',
            'price'     => 'required|numeric',
            'image'     => 'nullable|image|max:2048',
        ]);

        $imagePath = $product->image;

        // If new image uploaded
        if ($request->hasFile('image')) {

            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }

            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);

            $imagePath = 'images/' . $imageName;
        }

        $product->update([
            'name'      => $request->name,
            'details'   => $request->details,
            'size'      => $request->size,
            'color'     => $request->color,
            'category'  => $request->category,
            'price'     => $request->price,
            'image'     => $imagePath,
        ]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    // Soft delete
    public function destroy(Product $product)
    {
        $product->update(['status' => 'deleted']);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
