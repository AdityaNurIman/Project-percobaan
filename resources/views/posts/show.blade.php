@extends('layouts.blog')

@section('content')
<main class="container mx-auto mt-6 flex gap-6">
    <section class="w-3/4 bg-white p-6 shadow-md rounded-lg">
        <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:underline">&larr; Back to posts</a>
        <h1 class="text-2xl font-bold mt-2">{{ $post->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">
            @if ($post->category)
                <a href="{{ route('home', ['category_id' => $post->category_id]) }}" class="hover:underline">{{ $post->category->name }}</a>
            @endif
        </p>
        <img src="{{ asset('images/placeholder-150x150.png') }}" alt="Post Image" class="w-full h-64 object-cover rounded my-4">
        <p class="text-gray-700 leading-relaxed">{{ $post->text }}</p>
    </section>

    <aside class="w-1/4 bg-white p-6 shadow-md rounded-lg h-fit">
        <h2 class="text-xl font-semibold mb-4">Categories</h2>
        <ul class="space-y-2">
            @foreach($categories as $category)
                <li>
                    <a href="{{ route('home', ['category_id' => $category->id]) }}"
                    class="block px-3 py-1 rounded {{ request('category_id') == $category->id ? 'bg-gray-800 text-white' : 'text-gray-600 hover:text-gray-800' }}">
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>
</main>
@endsection
