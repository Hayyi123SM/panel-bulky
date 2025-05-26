<?php

namespace App\Http\Controllers\Api\Video;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Video\GetVideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Videos
 *
 * Handles video-related operations such as retrieving video collections,
 * incrementing view counts, and retrieving the next available video.
 */
class VideoController extends Controller
{
    /**
     * List Video
     *
     * Retrieves a collection of videos based on the provided request.
     *
     * @param GetVideoRequest $request The request object containing the parameters for the video retrieval.
     * @return AnonymousResourceCollection The resource representing the collection of videos.
     */
    public function index(GetVideoRequest $request)
    {
        $videos = $request->paginate
            ? Video::paginate($request->per_page ?? 15)
            : Video::take($request->take ?? 15)->get();

        return VideoResource::collection($videos);
    }


    /**
     * Show Video
     *
     * Increments the view count of a video and returns a resource representation of the video.
     *
     * @param Video $video The video to show.
     * @return VideoResource The resource representation of the video.
     */
    public function show(Video $video)
    {
        $video->increment('view_count');
        return new VideoResource($video);
    }

    /**
     * Next Video
     *
     * Retrieves the next video after the provided video.
     *
     * This method takes a Video model instance as a parameter and retrieves the next video
     * that was created after the provided video. The videos are ordered by their creation
     * date in ascending order. If there are no more videos available after the provided
     * video, a JSON response with a 404 status code and a message indicating the lack of
     * availability is returned. The view count of the retrieved next video is incremented
     * before returning it as a VideoResource instance.
     *
     * @param Video $video The video model instance to get the next video after.
     * @return VideoResource|JsonResponse The next video as a VideoResource
     *                                                    instance or a JSON response if no
     *                                                    more videos are available.
     */
    public function next(Video $video)
    {
        $nextVideo = Video::where('created_at', '>', $video->created_at)
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$nextVideo) {
            return response()->json(['message' => 'No more videos available'], 404);
        }

        $nextVideo->increment('view_count');

        return new VideoResource($nextVideo);
    }

}
