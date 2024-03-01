<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

use PhpOffice\PhpSpreadsheet;

use App\Http\Requests\PostRequest;

use App\Services\PostsTakerService;
use App\Services\PostsSaverService;

class PostController extends Controller
{
    public function savePosts(PostRequest $request)
    {
        $posts = isset($request->file) ? 
            PostsTakerService::takeFromFile($request->file) : 
            PostsTakerService::takeFromApi();

        PostsSaverService::save($posts);
    }
}
