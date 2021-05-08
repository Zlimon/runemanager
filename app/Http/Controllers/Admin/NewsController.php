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

	public function imageUpload(Request $request) {
		$imageFile = $request->file('image');

		if ($imageFile == null) {
            return 1;
        }

        $validator = Validator::make($request->all(), [
            'image' => 'mimes:jpeg,bmp,png,gif',
        ]);

        if ($validator->fails()) {
            return false;
        }

        $imageFileName = Str::uuid()->toString();

        $image = Image::create([
            'image_file_name' => $imageFileName,
            'image_file_extension' => $imageFile->getClientOriginalExtension(),
            'image_file_type' => $imageFile->getMimeType(),
            'image_file_size' => $imageFile->getSize()
        ]);

        if (!$image) {
            return redirect()->back()->withErrors(($image->errors()));
        }

        $imageFile->move('storage', $imageFileName.'.'.$imageFile->getClientOriginalExtension());

        return $image->id;
	}

	public function edit(NewsPost $newsPost) {
		$newsCategories = NewsCategory::get();

		return view('admin.news.edit', compact('newsPost', 'newsCategories'));
	}

	public function update(NewsPost $newsPost, Request $request) {
		$newAuthor = User::whereName(request('user'))->orWhere('id', request('user'))->first();

		if (!$newAuthor) {
            return redirect(route('admin-edit-newspost', $newsPost))->withErrors(['This user does not exist!']);
        }

        $newsPost->update(request()->validate([
            'news_category_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'min:1', 'max:75'],
            'shortstory' => ['required', 'string', 'min:1', 'max:200'],
            'longstory' => ['required', 'string', 'min:1', 'max:50000']
        ]));

        $imageId = $this->imageUpload($request);

		if (!$imageId) {
            return redirect(route('admin-create-newspost'))->withErrors(
                ['The image must be a file of type: jpeg, bmp, png, gif.']
            );
        }

		if ($newAuthor) {
            $newsPost->user_id = $newAuthor->id;
        }

        if (request('default')) {
            $newsPost->image_id = 1;
        } elseif ($imageId != 1) {
            $newsPost->image_id = $imageId;
        }

        $newsPost->save();

        return redirect(route('admin-edit-newspost', $newsPost))->with('message', 'Newspost updated!');
	}

	public function destroy(NewsPost $newsPost) {
		$newsPost->delete();

		return redirect(route('admin-news'))->with('message', 'Newspost deleted!');
	}
}
