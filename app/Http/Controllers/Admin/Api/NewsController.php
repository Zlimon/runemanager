<?php

namespace App\Http\Controllers\Admin\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\NewsPost;
use App\Rules\ValidateUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
	public function store(Request $request) {
        $this->validate($request, [
            'news_category_id' => ['required', 'integer', 'exists:news_categories,id'],
            'title' => ['required', 'string', 'max:50'],
            'shortstory' => ['required', 'string', 'max:200'],
            'longstory' => ['required', 'string', 'max:50000']
            ]
        );

        // TODO put in validator (needs proper file upload in Vue)
        $imageId = Helper::imageUpload($request->file('image')); // Return 1 / default image if empty

		if (!$imageId) {
		    return response()->json(['errors' => ['image' => ['The image must be a file of type: jpeg, bmp, png, gif!']]], 422);
        }

        $newsPost = NewsPost::create([
            'user_id' => Auth::id(),
            'news_category_id' => $request->news_category_id,
            'image_id' => 1, // TODO file upload
            'title' => $request->title,
            'shortstory' => $request->shortstory,
            'longstory' => base64_encode($request->longstory),
        ]);

        return response($newsPost, 202);
	}

	public function update(NewsPost $newsPost, Request $request) {
        $this->validate($request, [
            'user_id' => ['required', new ValidateUser()],
            'news_category_id' => ['required', 'integer', 'exists:news_categories,id'],
            'title' => ['required', 'string', 'max:50'],
            'shortstory' => ['required', 'string', 'max:200'],
            'longstory' => ['required', 'string', 'max:50000']
            ]
        );

        // TODO put in validator (needs proper file upload in Vue)
        $imageId = Helper::imageUpload($request->file('image')); // Return 1 / default image if empty

		if (!$imageId) {
		    return response()->json(['errors' => ['image' => ['The image must be a file of type: jpeg, bmp, png, gif!']]], 422);
        }

		$author = User::whereName($request->user_id)->orWhere('id', $request->user_id)->pluck('id')->first();
		if ($author) {
            $newsPost->user_id = $author;
        }

		$newsPost->news_category_id = $request->news_category_id;
		$newsPost->image_id = 1; // TODO file upload
		$newsPost->title = $request->title;
		$newsPost->shortstory = $request->shortstory;
		$newsPost->longstory = base64_encode($request->longstory);

        $newsPost->update();

        return response($newsPost, 202);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  NewsPost $newsPost
     * @return \Illuminate\Http\Response
     */
    public function destroy(NewsPost $newsPost)
    {
        $newsPost->delete();

        return response('', 204);
    }
}
