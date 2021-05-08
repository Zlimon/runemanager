<?php

namespace App\Http\Controllers\Admin\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\NewsPost;
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
            return response()->json(
                ['The image must be a file of type: jpeg, bmp, png, gif.']
            , 422);
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
}
