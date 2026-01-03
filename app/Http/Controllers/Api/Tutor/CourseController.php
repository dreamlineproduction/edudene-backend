<?php

namespace App\Http\Controllers\Api\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAsset;
use App\Models\CourseOutcome;
use App\Models\CourseRequirement;
use App\Models\CourseSeo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PhpParser\Node\Stmt\TryCatch;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
		$user = auth('sanctum')->user();

		$courses = Course::query()
			->where('user_id', $user->id)
			->with([
					'user',
					'courseType',
					'category',
					'subCategory',
					'courseChapters',
				]);
		
		if (!empty($request->search)) {
			$courses = $courses->where('title','like','%'.$request->search.'%');
		}

		$sortBy = $request->get('sort_by', 'title');
    	$sortDirection = $request->get('sort_direction', 'asc');

		if (in_array($sortBy, ['id', 'title', 'status', 'created_at'])) {
			$courses = $courses->orderBy($sortBy, $sortDirection);
		} else {
			$courses = $courses->orderBy('title', 'asc');
		}

		$perPage = (int) $request->get('per_page', 10);
    	$page = (int) $request->get('page', 1);

		$paginated = $courses->paginate($perPage, ['*'], 'page', $page);

		return jsonResponse(true, 'Categories fetched successfully', [
			'courses' => $paginated->items(),
			'total' => $paginated->total(),
			'current_page' => $paginated->currentPage(),
			'per_page' => $paginated->perPage(),
		]);

    }


    public function show(string $id){
        
        $course = $this->singleCourse($id);

        if(notEmpty($course)) {
            return jsonResponse(true,'Course data',['course'=>$course]);
        }   

        return jsonResponse(false,'Course not found in our database',null,404);
    }

	/**
     * Create course by title
    */
	public function createCourseByTitle(Request $request){
		try {
			$request->validate([
				'title' => 'required|string|max:190',
			]);
			$user = auth('sanctum')->user();

			$course = new Course();
			$course->title = $request->title;
			$course->user_id = $user->id;
			$course->slug = generateUniqueSlug($request->title, 'App\Models\Course');
			$course->save();

			return jsonResponse(true, 'Course created successfully', ['course' => $course]);
		} catch(\Exception $e){
            return jsonResponse(false, $e->getMessage(), null,500);
        }
	}

    /**
     * Save course basic information
     */
    public function saveBasicInformation(Request $request)
    {
        try{
            //
            $validation =[
                'title' => 'required|string|max:190',
                'short_description' => 'required|string|max:255',
                'description' => 'nullable|string',
                'level' => 'required|string|in:Beginner,Advanced,Intermediate',
                //'course_type_id' => 'required|integer|exists:course_types,id',
                'category_id' => 'required|integer|exists:categories,id',
                'subcategory_id' => 'nullable|integer|exists:sub_categories,id',
                'sub_sub_category_id' => 'nullable|integer|exists:sub_sub_categories,id',
				'category_level_four_id' => 'nullable|integer|exists:category_level_fours,id',
            ];

            if($request->has('type') && $request->type == 1){
                $validation['country_id'] = 'required|integer|exists:countries,id';
                $validation['state_id'] = 'required|integer|exists:states,id';
            }

            $request->validate($validation);

            $user = auth('sanctum')->user();
            $request->merge([
                'user_id' => $user->id,
                'status' => 'Draft',
                'slug' => generateUniqueSlug($request->title, 'App\Models\Course'),
            ]);
            $find = ['user_id' => $user->id, 'id' => $request->course_id];
            $course = Course::updateOrCreate($find,$request->toArray());
            return jsonResponse(true, 'Course basic info saved successfully.', $course);
        } catch(\Exception $e){
            return jsonResponse(false, $e->getMessage(), null,500);
        }
        
        
        
    }

    /**
     * Save course requirements
     */
    public function saveOutcome(Request $request)
    {
        $request->validate([     
            'course_id' => 'required|integer|exists:courses,id',     
            'outcomes' => 'required|array|min:1',
            'outcomes.*.title' => 'required|string|max:150',
        ]);

        if(empty($request->outcomes)){
            return jsonResponse(false, 'Please provide at least one outcome.', null, 422);
        }

        // Delete existing requirements and create new ones
        CourseOutcome::where('course_id', $request->course_id)->delete();

        foreach($request->outcomes as $outcome){
            CourseOutcome::create([
                'course_id' => $request->course_id,
                'title' => $outcome['title'],
            ]);
        }
        
        $course = $this->singleCourse($request->course_id);              
        return jsonResponse(true, 'Course data', $course);
    }
    

    public function savePrice(Request $request)
    {
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
        ]);

        $course = Course::find($request->course_id);

        $course->update([
            'price' => $request->price,
            'discount_price' => $request->discount_price,
        ]);

        $course = $this->singleCourse($request->course_id); 
        return jsonResponse(true, 'Course price updated successfully.', $course);
    }

    public function saveMedia(Request $request)
    {
        $validation = [
            'course_id' => 'required|integer|exists:courses,id',
            'type' => 'required|string|in:Youtube,Vimeo,Local',
        ];

        if($request->type === 'Youtube' || $request->type === 'Vimeo'){
            $validation['video_url'] = 'required|url';
        }else {
            $validation['file_id'] = 'required|integer|exists:files,id';
        }

        $request->validate($validation); 

        $videoUrl = $request->video_url;
        $remoteThumb = null;

        //$find = ['course_id' => $request->course_id];

        if(!isVimeo($videoUrl) && !isYouTube($videoUrl)){
            return jsonResponse(false,'Only YouTube and Vimeo URLs are supported.',null,422);
        }

        if($request->type === 'Youtube'){
            $videoId = getYouTubeId($videoUrl);
            if (! $videoId) {
                return jsonResponse(false,'Invalid YouTube URL/ID',null,422);                
            }

            $remoteThumb = getYoutubeVideoPoster($videoId);

            $insertData['video_url'] = $request->video_url;
        }

        if($request->type === 'Vimeo'){
            $remoteThumb =  getViemoVideoPoster($videoUrl);
            $insertData['video_url'] = $request->video_url;
        }
        
        if ($request->type !== 'Local' && empty($remoteThumb)) 
        {          
            return jsonResponse(false,'Thumbnail URL not found.',null,422);            
        }

        $courseAsset = CourseAsset::where('course_id',$request->course_id)->first();

        // Insert array
        $insertData = [];
        $insertData['course_id'] = $request->course_id;
        $insertData['type'] = $request->type;
        $insertData['video_url'] = $request->video_url;

        if($request->type === 'Local'){

            // Check is file already exists.
            if (notEmpty($courseAsset->video) && notEmpty($courseAsset->poster)) {
                // Delete old video and video poster
                deleteS3File($courseAsset->video);
                deleteS3File($courseAsset->poster);
            }

            $newPath = 'courses/course-'.$request->course_id;

            $finalizeImage = finalizeFile($request->file_id,$newPath);
            $insertData['video'] = $finalizeImage['video_path'];
            $insertData['video_url'] = $finalizeImage['video_url'];
            $insertData['poster'] = $finalizeImage['poster_path'];
            $insertData['poster_url'] = $finalizeImage['poster_url'];
        }

        
        $find = ['course_id' => $request->course_id];
        CourseAsset::updateOrCreate($find,$insertData);
        
        $course = $this->singleCourse($request->course_id);
        return jsonResponse(true,'Data retrive',$course);
    }

    public function saveSeo(Request $request)
    {
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'meta_title' => 'required|string|max:190',
            'meta_description' => 'required|string|max:255',
            'meta_keyword' => 'required|string|max:255',
        ]);

        $find = ['course_id' => $request->course_id];
        CourseSeo::updateOrCreate($find,$request->toArray());

        $course = $this->singleCourse($request->course_id);
        return jsonResponse(true, 'Course SEO information updated successfully.', $course);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $course = Course::find($id);
        if (!$course) {
            return jsonResponse(false, 'Course not found in our database', null, 404);
        }

        $course->delete();
        return jsonResponse(true, 'Course deleted successfully', null);

    }

    private function getYoutubeVideoPoster($videoId,$size = 'MAX'){
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

    private function getViemoVideoPoster($videoUrl)
    {
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


    // No Need
    private function firstReachable(array $urls): ?string
    {
        foreach ($urls as $u) {
            $res = Http::head($u);
            if ($res->successful() && $res->header('Content-Type') && str_contains($res->header('Content-Type'), 'image')) {
                return $u;
            }
            // some hosts don't allow HEAD; attempt GET but only check status
            if ($res->status() == 405) {
                $resGet = Http::get($u);
                if ($resGet->successful() && str_contains($resGet->header('Content-Type') ?? '', 'image')) {
                    return $u;
                }
            }
        }
        return null;
    }


    private function singleCourse(string $id){
         $course =  Course::where('id',$id)
            ->with([
				'user',
				'courseType',
				'category',
				'subCategory',
				'subSubCategory',
            	'courseOutcomes',
				'courseRequirements',
				'courseAsset',
				'courseSeo',
				'courseChapters.courseLessons'
            ])->first(); 

        return $course;
    }
}
