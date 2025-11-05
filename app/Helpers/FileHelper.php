<?php

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

use App\Models\File;

if(!function_exists('imageUploadS3')){
    function imageUploadS3(Request $request){
        try{
            $file = $request->file('file');
            $mime = $file->getMimeType();


            //$fileOriginalName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            // Create File Name 
            $model = new File;
            $model->save();
            $fileName = Str::random(20) . '-' . time() . '-image-' . $model->id . '.'.$fileExtension;
            

            // 2. Create the Image Manager
            $manager = new ImageManager(new Driver());

            // 3. Process the original image
            $originalImage = $manager->read($file->getRealPath());
            $path = 'temp/images/' . $fileName;
            Storage::disk('s3')->put($path, $originalImage->encode());
            $url = Storage::disk('s3')->url($path);

            $thumbnailUrl = generateImageThumbnail($path);
            $thumbPath = str_replace('temp/images/', 'temp/images/thumbnails/', $path);

            // 5. Store file record in the database
            $model->name = $fileName;
            $model->path = $path;
            $model->url = $url;
            $model->type = 'image';
            $model->mime_type = $mime;

            // Save Image thumbnail
            $model->poster_name = $fileName;
            $model->poster_path = $thumbPath;
            $model->poster_url = $thumbnailUrl;
            $model->save();
        

            return [
                'file_id'=>$model->id,
                'path' => $path,
                'preview_url' => $url,
                'thumbnail_url' => $thumbnailUrl,
            ];
        } catch(\Exception $e){
           return jsonResponse(false,'Upload failed: ' . $e->getMessage(),null,500);
        }
    }
}

if(!function_exists('generateImageThumbnail'))
{
    function generateImageThumbnail($s3Path,$width = 350,$folderName = 'temp/images')
    {
        try {
            $manager = new ImageManager(new Driver());

            // Get the image content from S3
            $imageContent = Storage::disk('s3')->get($s3Path);

            // Read, resize, and encode the image
            $image = $manager->read($imageContent);
            $image->scale(width: $width); // Default 350

            // Create a unique name for the thumbnail
                                    // 'temp/images'      'temp/images'
            $thumbPath = str_replace($folderName.'/', $folderName.'/thumbnails/', $s3Path);

            // Put the encoded thumbnail data back on S3
            Storage::disk('s3')->put($thumbPath, $image->encode());

            return Storage::disk('s3')->url($thumbPath);
        } catch (\Exception $e) {           
            return jsonResponse(false,'Exception: ' . $e->getMessage(),null,500);
        }
    }
}

if(!function_exists('documentUploadS3'))
{
    function documentUploadS3(Request $request)
    {
        try {
            $file = $request->file('file');
            $mime = $file->getMimeType();

            $fileExtension = $file->getClientOriginalExtension();

            $folder = 'temp/document/';

            // Create File Name 
            $model = new File;
            $model->save();
            $fileName = Str::random(20) . '-' . time() . '-document-' . $model->id . '.'.$fileExtension;

            $path = $folder. $fileName;

            Storage::disk('s3')->put($path, file_get_contents($file));

            $url = Storage::disk('s3')->url($path);

            // 5. Store file record in the database
            $model->name = $fileName;
            $model->path = $path;
            $model->url = $url;
            $model->type = 'document';
            $model->mime_type = $mime;
            $model->save();

            return [
                'file_id'=>$model->id,
                'path' => $path,
                'preview_url' => $url,
                'thumbnail_url' => $url,
            ];

        } catch (\Exception $e) {
            return jsonResponse(false,'Upload failed: ' . $e->getMessage(),null,500);           
        }
    }
}


if(!function_exists('videoUploadS3'))
{
    function videoUploadS3(Request $request)
    {
        try {
            $file = $request->file('file');
            $mime = $file->getMimeType();
            $extension = $file->getClientOriginalExtension();

            $model = new File;
            $model->save();

            $time = time();
            $strRandom20 = Str::random(20);
            $fileName = $strRandom20. '-' . $time . '-video-' . $model->id . '.' . $extension;
            $path = 'temp/videos/' . $fileName;

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
                $posterName = $strRandom20 . '-' . $time . '-video-' . $model->id . '.jpg';
                $posterPath = 'temp/videos/thumbnails/'.$posterName;

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
                'file_id' => $model->id,
                'path' => $path,
                'video_url' => $url,
                'poster_path' => $posterPath,
                'poster_url' => $posterUrl,
            ];
            return $data;
        } catch (\Exception $e) {
            return jsonResponse(false,'Exception: ' . $e->getMessage(),null,500);
        }
    }
}


if(!function_exists('finalizeImage'))
{
    function finalizeFile($fileId = 0,$newOriginalPath)
    {
        $return = [];
        try {
            $file = File::findOrFail($fileId);
            if(!$file)
            {
                throw new \Exception('File found in our temp table.');
            }

            $newOriginalPosterPath = $newOriginalPath.'/' . basename($file->poster_path);
            $newOriginalPath = $newOriginalPath.'/' . basename($file->path);


            // Move the original file
            if($file->type === 'video'){
                // Video Check exists
                if (Storage::disk('s3')->exists($file->path) && Storage::disk('s3')->exists($file->poster_path)) {                    
                    Storage::disk('s3')->move($file->path, $newOriginalPath);
                    $newVideoUrl = Storage::disk('s3')->url($newOriginalPath);

                    Storage::disk('s3')->move($file->poster_path, $newOriginalPosterPath);
                    $newVideoPosterUrl = Storage::disk('s3')->url($newOriginalPosterPath);

                    // Delete  thumbnails
                    $oldThumbnailPath = str_replace('temp/', 'temp/thumbnails/', $file->poster_path);
                    Storage::disk('s3')->delete($oldThumbnailPath);
                    $file->delete();

                    $return['video_path'] = $newOriginalPath;
                    $return['video_url'] =  $newVideoUrl;
                    $return['poster_path'] = $newOriginalPosterPath;
                    $return['poster_url'] = $newVideoPosterUrl;
                } else {
                    throw new \Exception('Original file not found on S3.');
                }
            } 
            
            if($file->type === 'document' && notEmpty($file->path))
            {
                if (Storage::disk('s3')->exists($file->path)) {
                    Storage::disk('s3')->move($file->path, $newOriginalPath);
                    $newUrl = Storage::disk('s3')->url($newOriginalPath);

                    $return['path'] = $newOriginalPath;
                    $return['url'] =  $newUrl;
                }
            }
            
            if($file->type === 'image' && notEmpty($file->path)){
                if (Storage::disk('s3')->exists($file->path)) {
                    Storage::disk('s3')->move($file->path, $newOriginalPath);
                    $newUrl = Storage::disk('s3')->url($newOriginalPath);

                    // Delete  thumbnails
                    if (Storage::disk('s3')->exists($file->poster_path)) {
                        Storage::disk('s3')->delete($file->poster_path);
                    }

                    $return['path'] = $newOriginalPath;
                    $return['url'] =  $newUrl;
                }
            }

            $file->delete();            
            
           
            return $return;
        } catch (\Exception $e) {
            return jsonResponse(false,'Exception: ' . $e->getMessage(),null,500);
        }
    }
}

if(!function_exists('deleteS3File')){
    function deleteS3File(string $path){
        if(empty($path)){
            return;
        }

        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }
    }
}



/**
 * Determine the file type based on MIME type.
 *
 * @param  string  $mime
 * @return string
 */
if(!function_exists('fileType')){
    
    function fileType($mime)
    {
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }

        if (str_starts_with($mime, 'application/pdf')) {
            return 'document';
        }
        if (str_starts_with($mime, 'application/')) {
            return 'document';
        }

        return 'other';
    }
}


if(!function_exists('getYoutubeVideoPoster'))
{
    function getYoutubeVideoPoster($videoId,$size = 'MAX') {
         $thumbUrls = [
            "https://i.ytimg.com/vi/{$videoId}/maxresdefault.jpg",
            "https://i.ytimg.com/vi/{$videoId}/sddefault.jpg",
            "https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg",
        ];
        if($size === 'SD') {
            return $thumbUrls[1];
        } 
        if($size === 'HQ') {
            return $thumbUrls[2];
        }

        return $thumbUrls[0];
    }
}

if(!function_exists('getViemoVideoPoster'))
{
    function getViemoVideoPoster($videoUrl) {


        $oembedUrl = 'https://vimeo.com/api/oembed.json?url=' . urlencode($videoUrl);
        $res = Http::get($oembedUrl);
        if (!$res->successful()) {  
            //'Unable to get Vimeo oEmbed info. Video may be private or URL invalid.'
            return;             
        }
        $data = $res->json();
        if(notEmpty($data['thumbnail_url'])){
            return $data['thumbnail_url'];
        }
        return;
    }
}



?>