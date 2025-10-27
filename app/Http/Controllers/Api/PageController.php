<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    //
    public function index(Request $request){
        $pages = Page::where('status','Active');
        
        if($request->has('is_show')){
            $pages->where('is_show',$request->is_show);
        }

        $pages = $pages->latest()->get();
        return jsonResponse(true, 'Pages fetched successfully', $pages);
    }



    // Get page by slug
    public function show($slug = null)
    {
        if(empty($slug)){
            return jsonResponse(false, 'Slug is required', null, 400);
        }

        $page = Page::where('slug', $slug)->where('status', 'Active')->first();

        if(empty($page)){
            return jsonResponse(false, 'Page not found', null, 404);
        }


        return jsonResponse(true, 'Page fetched successfully', $page);        
    }

}
