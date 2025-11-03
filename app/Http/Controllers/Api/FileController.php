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
           'file' => 'required|file|mimetypes:image/jpeg,image/png,image/webp|max:10240'
        ]);

        if ($validator->fails()) {
            return jsonResponse(false,$validator->errors()->first('file'));          
        }

        $responseArray =  imageUploadS3($request);

        return jsonResponse('File uploaded successfully', $responseArray);
    }


    public function uploadVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'file' => 'required|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime|max:2048000',
        ]);

        if ($validator->fails()) {
            return jsonResponse(false,$validator->errors()->first('file'));          
        }
        
     

        try {
            $file = $request->file('file');
            $mime = $file->getMimeType();
            $extension = $file->getClientOriginalExtension();

            $model = new File;
            $model->save();

            $time = time();
            $fileName = Str::random(20) . '-' . $time . '-video-' . $model->id . '.' . $extension;
            $path = 'videos/' . $fileName;

            // Upload video to S3
            Storage::disk('s3')->put($path, file_get_contents($file));
            $url = Storage::disk('s3')->url($path);

            // Generate video thumbnail using FFmpeg (if available)
            $posterUrl = null;
            $tempVideoPath = storage_path('app/temp/' . $fileName);
            $file->move(storage_path('app/temp/'), $fileName);

            $thumbnailPath = storage_path('app/temp/' . pathinfo($fileName, PATHINFO_FILENAME) . '.jpg');
            $command = "ffmpeg -i {$tempVideoPath} -ss 00:00:02 -vframes 1 {$thumbnailPath}";
            exec($command);

            if (file_exists($thumbnailPath)) {
                $thumbContent = file_get_contents($thumbnailPath);
               // $thumbName = 'thumbnails/' . Str::random(10) . '-' . time() . '.jpg';
                $posterName = Str::random(20) . '-' . $time . '-video-' . $model->id . '.jpg';
                $posterPath = 'thumbnails/'.$posterName;

                Storage::disk('s3')->put($posterPath, $thumbContent);
                $posterUrl = Storage::disk('s3')->url($posterPath);
            }

            // Save file record
            $model->name = $fileName;
            $model->path = $path;
            $model->url = $url;
            $model->type = 'video';
            $model->mime_type = $mime;
            $model->poster_url = $posterUrl;
            $model->poster_path = $posterPath;
            $model->poster_name = $posterName;
            
            $model->save();

            // Cleanup
            @unlink($tempVideoPath);
            @unlink($thumbnailPath);

            $data = [
                'video_id' => $model->id,
                'path' => $path,
                'video_url' => $url,
                'poster_url' => $posterUrl,
            ];
            return jsonResponse(true,$data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function imageTesting($fileId)
    {
        $finalizeImage =  finalizeImage($fileId, 'profile');

        $filePath = generateImageThumbnail($finalizeImage['path'], 500, 'profile');

        return jsonResponse(true, 'Tested successfully', $filePath);
    }
}
