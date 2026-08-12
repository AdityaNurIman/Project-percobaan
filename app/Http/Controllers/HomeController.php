<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();

        $posts = Post::when($request->has('category_id'), function ($query) use ($request) {
            $query->where('category_id', $request->category_id);
        })->latest()->get();

        return view('home', compact('categories', 'posts'));
    }

    public function show(Post $post)
    {
        $categories = Category::all();

        return view('posts.show', compact('post', 'categories'));
    }
}
