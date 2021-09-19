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
		$newsPosts = NewsPost::with('user')->with('newsCategory')->orderByDesc('created_at')->get();

		return view('admin.news.index', compact('newsPosts'));
	}

	public function show(NewsPost $newsPost) {
	    $newsPost->longstory = base64_decode($newsPost->longstory);

		return view('admin.news.show', compact('newsPost'));
	}

	public function create() {
		$newsCategories = NewsCategory::get();

		return view('admin.news.create', compact('newsCategories'));
	}

	public function edit(NewsPost $newsPost) {
	    $users = User::all();
		$newsCategories = NewsCategory::get();

		$newsPost->longstory = base64_decode($newsPost->longstory);

		return view('admin.news.edit', compact('users','newsCategories', 'newsPost'));
	}

	public function destroy(NewsPost $newsPost) {
		$newsPost->delete();

		return redirect(route('admin-news'))->with('message', 'Newspost deleted!');
	}

	public function createCategory(Request $request) {
        $this->validate($request, [
            'category' => ['required', 'string'],
            ]
        );

        $newsCategory = new NewsCategory();

        $newsCategory->category = $request->category;

        $newsCategory->save();

		return redirect(route('admin-news'))->with('message', 'Created news category!');
	}
}
