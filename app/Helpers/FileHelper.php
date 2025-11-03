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
            $path = 'temp/' . $fileName;
            Storage::disk('s3')->put($path, $originalImage->encode());
            $url = Storage::disk('s3')->url($path);

            $thumbnailUrl = generateImageThumbnail($path);
            $thumbPath = str_replace('temp/', 'temp/thumbnails/', $path);

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
            echo $e->getMessage();
            exit;
        }
    }
}

if(!function_exists('generateImageThumbnail'))
{
    function generateImageThumbnail($s3Path,$width = 350,$folderName = 'temp')
    {
        try {
            $manager = new ImageManager(new Driver());

            // Get the image content from S3
            $imageContent = Storage::disk('s3')->get($s3Path);

            // Read, resize, and encode the image
            $image = $manager->read($imageContent);
            $image->scale(width: $width); // Default 350

            // Create a unique name for the thumbnail
                                    // 'temp/'      'temp/'
            $thumbPath = str_replace($folderName.'/', $folderName.'/thumbnails/', $s3Path);

            // Put the encoded thumbnail data back on S3
            Storage::disk('s3')->put($thumbPath, $image->encode());

            return Storage::disk('s3')->url($thumbPath);
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }
}

if(!function_exists('finalizeImage'))
{
    function finalizeImage($fileId = 0,$newOriginalPath)
    {
        try {
            $file = File::findOrFail($fileId);
            if(!$file)
            {
                throw new \Exception('File found in our temp table.');
            }

            $newOriginalPath = $newOriginalPath.'/' . basename($file->path);

            // Move the original file
            if (Storage::disk('s3')->exists($file->path)) {
                Storage::disk('s3')->move($file->path, $newOriginalPath);

                // Delete  thumbnails
                $oldThumbnailPath = str_replace('temp/', 'temp/thumbnails/', $file->path);
                Storage::disk('s3')->delete($oldThumbnailPath);
                $file->delete();

            } else {
                throw new \Exception('Original file not found on S3.');
            }


            return [
                'path' => $newOriginalPath,
                'url' => $file->url,
            ];
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
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
        if (! $res->successful()) {  
            return jsonResponse(false,'Unable to get Vimeo oEmbed info. Video may be private or URL invalid.',422);
        }
        $data = $res->json();
        if(notEmpty($data['thumbnail_url'])){
            return $data['thumbnail_url'];
        }
        return;
    }
}



?>