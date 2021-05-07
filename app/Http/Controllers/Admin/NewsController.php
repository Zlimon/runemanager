<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NewsController extends Controller
{
	public function index() {
		$newsPosts = NewsPost::with('user')->with('category')->orderBy('created_at', 'DESC')->get();

		return view('admin.news.index', compact('newsPosts'));
	}

	public function show($newsPost) {
		$post = NewsPost::findOrFail($newsPost);

		return view('admin.news.show', compact('post'));
	}

	public function create() {
		$categories = Category::get();

		return view('admin.news.create', compact('categories'));
	}

	public function imageUpload(Request $request) {
		$imageFile = $request->file('image');

		if ($imageFile != null) {
			$validator = Validator::make($request->all(), [
				'image' => 'mimes:jpeg,bmp,png,gif',
			]);

			if ($validator->fails()) {
				return false;
			} else {
				$imageFileName = Str::uuid()->toString();

				$image = Image::create([
					'image_file_name' => $imageFileName,
					'image_file_extension' => $imageFile->getClientOriginalExtension(),
					'image_file_type' => $imageFile->getMimeType(),
					'image_file_size' => $imageFile->getSize()
				]);

				if ($image) {
					$imageFile->move('storage', $imageFileName.'.'.$imageFile->getClientOriginalExtension());

					return $image->id;
				} else {
					return redirect()->back()->withErrors(($image->errors()));
				}
			}
		} else {
			return 1;
		}
	}

	public function store(Request $request) {
		$imageId = $this->imageUpload($request);

		if ($imageId) {
			request()->validate([
				'category_id' => ['required', 'integer'],
				'title' => ['required', 'string', 'min:1', 'max:75'],
				'shortstory' => ['required', 'string', 'min:1', 'max:200'],
				'longstory' => ['required', 'string', 'min:1', 'max:50000']
			]);

			$newsPost = NewsPost::create([
				'user_id' => Auth::user()->id,
				'category_id' => request('category_id'),
				'image_id' => $imageId,
				'title' => request('title'),
				'shortstory' => request('shortstory'),
				'longstory' => request('longstory')
			]);

			return redirect(route('admin-show-newspost', $newsPost->id))->with('message', 'Newspost "'.request('title').'" posted!');
		} else {
			return redirect(route('admin-create-newspost'))->withErrors(['The image must be a file of type: jpeg, bmp, png, gif.']);
		}
	}

	public function edit($id) {
		$post = NewsPost::findOrFail($id);

		$categories = Category::get();

		return view('admin.news.edit', compact('post', 'categories'));
	}

	public function update(NewsPost $id, Request $request) {
		$imageId = $this->imageUpload($request);

		$newAuthor = User::where('name', request('user_id'))->first();

		if ($newAuthor) {
			$id->update(request()->validate([
				'category_id' => ['required', 'integer'],
				'title' => ['required', 'string', 'min:1', 'max:75'],
				'shortstory' => ['required', 'string', 'min:1', 'max:200'],
				'longstory' => ['required', 'string', 'min:1', 'max:50000']
			]));

			$id->user_id = $newAuthor->id;

			if (request('default')) {
				$id->image_id = 1;
			} elseif ($imageId != 1) {
				$id->image_id = $imageId;
			}

			$id->save();

			return redirect(route('admin-edit-newspost', $id))->with('message', 'Newspost updated!');
		} else {
			$newAuthor = User::find(request('user_id'));

			if ($newAuthor) {
				$id->update(request()->validate([
					'user_id' => ['required', 'integer'],
					'category_id' => ['required', 'integer'],
					'title' => ['required', 'string', 'min:1', 'max:75'],
					'shortstory' => ['required', 'string', 'min:1', 'max:200'],
					'longstory' => ['required', 'string', 'min:1', 'max:50000']
				]));

				if (request('default')) {
					$id->image_id = 1;
				} elseif ($imageId != 1) {
					$id->image_id = $imageId;
				}

				$id->save();

				return redirect(route('admin-edit-newspost', $id))->with('message', 'Newspost updated!');
			} else {
				return redirect(route('admin-edit-newspost', $id))->withErrors(['This user does not exist!']);
			}
		}
	}

	public function destroy(NewsPost $id) {
		$id->delete();

		return redirect(route('admin-news'))->with('message', 'Newspost deleted!');
	}
}
