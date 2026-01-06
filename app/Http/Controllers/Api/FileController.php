<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);            

        if ($validator->fails()) {
            return jsonResponse(false,$validator->errors()->first('file'),null,400);          
        }

        $responseArray =  imageUploadS3($request);

        return jsonResponse(true,'File uploaded successfully', $responseArray);
    }


    public function uploadVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'file' => 'required|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime|max:2048000',
        ]);

        if ($validator->fails()) {
            return jsonResponse(false,$validator->errors()->first('file'),null,400);          
        }
        
        $responseArray =  videoUploadS3($request);
        return jsonResponse(true,'Video uploaded successfully', $responseArray);
    }


    public function uploadDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return jsonResponse(false,$validator->errors()->first('file'),null,400);          
        }

        $responseArray =  documentUploadS3($request);
        return jsonResponse(true,'Document uploaded successfully', $responseArray);
    }

    // public function imageTesting($fileId)
    // {
    //     $finalizeImage =  finalizeFile($fileId, 'profile');

    //     $filePath = generateImageThumbnail($finalizeImage['path'], 500, 'profile');

    //     return jsonResponse(true, 'Tested successfully', $filePath);
    // }
}
