<?php

namespace App\Http\Controllers;

use App\NewsPost;

class NewsController extends Controller
{
    public function index()
    {
        $newsPosts = NewsPost::get();

        return view('news.index', compact('newsPosts'));
    }

    public function show($newsPost)
    {
        $post = NewsPost::findOrFail($newsPost);

        return view('news.show', compact('post'));
    }
}
