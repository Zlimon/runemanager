<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuneManager\NewsPost;
use RuneManager\Category;

class NewsController extends Controller
{
    public function show($newsPost) {
        $post = NewsPost::findOrFail($newsPost);

        return view('news.show', compact('post'));
    }

    public function create() {
        $categories = Category::get();

        return view('admin.news.create', compact('categories'));
    }

    public function store() {
        request()->validate([
            'category_id' => ['required', 'integer'],
            //'image' => ['required'],
            'title' => ['required', 'string', 'min:5', 'max:75'],
            'shortstory' => ['required', 'string', 'min:1', 'max:190'],
            'longstory' => ['required', 'string', 'min:1', 'max:5000']
        ]);

        $newsPost = NewsPost::create([
            'user_id' => Auth::user()->id,
            'category_id' => request('category_id'),
            'image' => '0',
            'title' => request('title'),
            'shortstory' => request('shortstory'),
            'longstory' => request('longstory')
        ]);

        return redirect(route('show-newspost', $newsPost->id))->with('message', 'Newspost "'.request('title').'" posted!');
    }
}
