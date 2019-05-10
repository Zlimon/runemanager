<?php

namespace RuneManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Validator;
use RuneManager\NewsPost;
use RuneManager\Category;
use RuneManager\Image;

class NewsController extends Controller
{
    public function index() {
        $newsPosts = NewsPost::get();

        return view('news.index', compact('newsPosts'));
    }

    public function show($newsPost) {
        $post = NewsPost::findOrFail($newsPost);

        return view('news.show', compact('post'));
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
                return redirect()->back()->withErrors(($validator->errors()));
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

                    return $this->store($image->id);
                } else {
                    return redirect()->back()->withErrors(($image->errors()));
                }
            }
        } else {
            return $this->store(1);
        }
    }

    public function store($imageId) {
        request()->validate([
            'category_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'min:1', 'max:75'],
            'shortstory' => ['required', 'string', 'min:1', 'max:190'],
            'longstory' => ['required', 'string', 'min:1', 'max:5000']
        ]);

        $newsPost = NewsPost::create([
            'user_id' => Auth::user()->id,
            'category_id' => request('category_id'),
            'image_id' => $imageId,
            'title' => request('title'),
            'shortstory' => request('shortstory'),
            'longstory' => request('longstory')
        ]);

        return redirect(route('show-newspost', $newsPost->id))->with('message', 'Newspost "'.request('title').'" posted!');
    }
}
