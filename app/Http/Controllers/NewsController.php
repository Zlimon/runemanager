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

	public function show(NewsPost $newsPost) {
        $newsPost = $newsPost->with('newsCategory')->with('image')->first();

	    $newsPost->longstory = base64_decode($newsPost->longstory);

		return view('news.show', compact('newsPost'));
	}
}
