<?php

namespace App\Http\Controllers\Admin\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\NewsPost;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
	public function store(Request $request) {
        $this->validate($request, [
            'title' => ['required', 'string', 'min:1', 'max:50'],
            'shortstory' => ['required', 'string', 'min:1', 'max:200'],
            'longstory' => ['required', 'string', 'min:1', 'max:50000']
            ]
        );

        $imageId = Helper::imageUpload($request->file('image')); // Return 1 / default image if empty

		if (!$imageId) {
		    return response()->json(['errors' => ['image' => ['The image must be a file of type: jpeg, bmp, png, gif!']]], 422);
        }

        NewsPost::create([
            'user_id' => Auth::id(),
            'news_category_id' => (request('news_category_id') ? request('news_category_id') : 1),
            'image_id' => $imageId,
            'title' => request('title'),
            'shortstory' => request('shortstory'),
            'longstory' => request('longstory'),
        ]);

        return response()->json('Newspost "'.request('title').'" posted!', 200);
	}

	public function update(NewsPost $newsPost, Request $request) {
		$author = User::whereName(request('user_id'))->orWhere('id', request('user_id'))->first();

		if (!$author) {
            return response()->json(['errors' => ['user_id' => ['This user does not exist!']]], 422);
        }

        $this->validate($request, [
            'title' => ['required', 'string', 'min:1', 'max:50'],
            'shortstory' => ['required', 'string', 'min:1', 'max:200'],
            'longstory' => ['required', 'string', 'min:1', 'max:50000']
            ]
        );

        $imageId = Helper::imageUpload($request->file('image')); // Return 1 / default image if empty

		if (!$imageId) {
		    return response()->json(['errors' => ['image' => ['The image must be a file of type: jpeg, bmp, png, gif!']]], 422);
        }

        $newsPost->update(request()->except(['image']));

		if ($author) {
            $newsPost->user_id = $author->id;
        }

        if (request('default')) {
            $newsPost->image_id = 1;
        } elseif ($imageId != 1) {
            $newsPost->image_id = $imageId;
        }

        $newsPost->save();

        return response()->json('Newspost "'.$newsPost->title.'" updated!', 200);
	}
}
