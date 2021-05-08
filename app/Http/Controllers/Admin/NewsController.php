<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\NewsCategory;
use App\NewsPost;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
	public function index() {
		$newsPosts = NewsPost::with('user')->with('newsCategory')->orderByDesc('created_at', )->get();

		return view('admin.news.index', compact('newsPosts'));
	}

	public function show(NewsPost $newsPost) {
		return view('admin.news.show', compact('newsPost'));
	}

	public function create() {
		$newsCategories = NewsCategory::get();

		return view('admin.news.create', compact('newsCategories'));
	}

	public function edit(NewsPost $newsPost) {
		$newsCategories = NewsCategory::get();

		return view('admin.news.edit', compact('newsPost', 'newsCategories'));
	}

	public function destroy(NewsPost $newsPost) {
		$newsPost->delete();

		return redirect(route('admin-news'))->with('message', 'Newspost deleted!');
	}
}
