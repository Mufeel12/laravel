<?php

namespace App\Http\Controllers;

use App\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use App\GifCreator;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $images = Image::where('user_id', Auth::id())
            ->orderBy('id', 'DESC')
            ->get();
        $images = Image::formatImages($images);
        return $images;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $images = $request->all();#$request->files('images');
        $images = $images['images'];
        $return = [];
        \Log::info(json_encode($images));

        // Loop through images
        foreach ($images as $image) {
            // \Log::info($image['tmp_name']);
            $validator = \Validator::make([
                'image' => $image
            ], [
                'image' => 'required|image'
            ]);
            \Log::info($validator->fails());
            if ($validator->fails()) {
                return ['error' => 'This is not an image'];
            } else {
                if ($image->isValid()) {
                    $destination_path = image_path();
                    $extension = $image->getClientOriginalExtension();
                    $filename = str_random(32) . '.' . $extension;

                    $uploadData['uid'] = $request->input('uid');
                    $uploadData['vid'] = $request->input('vid');
                    $uploadData['image'] = $image;

                    $bucket_upload = $this->uploadImage($uploadData);

                    if ($bucket_upload['success']) {
                        // $image->move(image_path(), $filename);
                        // $imagePath = url('data/images/' . $filename);
                        // todo: redirect to something else
                        list($width, $height) = getimagesize($bucket_upload['file_path']);
                        $storeImage = new Image();
                        $storeImage->user_id = $request->input('uid');
                        $storeImage->title = $image->getClientOriginalName();
                        $storeImage->filename = $filename;
                        $storeImage->path = $bucket_upload['file_path'];
                        $storeImage->width = $width;
                        $storeImage->thumbnail_width = $width;
                        $storeImage->height = $height;
                        $storeImage->thumbnail_height = $height;
                        $storeImage->save();

                        $storeImage = Image::formatImage($storeImage);
                        $return[] = $storeImage;
                    } else {
                        return ['error' => 'Failed to upload image'];
                    }
                } else {
                    return ['error' => 'This is not a valid image'];
                }
            }
        }
        return $return;
    }

    protected function uploadImage($data)
    {
        // \Log::info($data['image']);
        $user = User::find($data['uid']);
        if ($user) {
            $ownerFolder    = generate_owner_folder_name($user->email);
            $videoId        = $data['vid'];
            $imageFilePath  = $data['image']->getPathName();
            $imageFileSize  = filesize($data['image']);
            $imageFileExt   = $data['image']->getClientOriginalExtension();
            $imageFileName  = str_random(32) . '.' . $imageFileExt;

            $fileKey    = "$ownerFolder/$videoId/thumb/$imageFileName";
            return Image::uploadImageToBucket($fileKey, $imageFilePath, $imageFileSize);
        }
    }

    public function update(Request $request)
    {
        $id = $request->input('id');

        $image = Image::findOrFail($id);
        $title = $request->input('title');
        if (!empty($title))
            $image->title = $title;
        $image->update();
        return 'success';
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        if (empty($id))
            return;
        $image = Image::findOrFail($id);
        try {
            \Bkwld\Croppa\Facade::delete(image_path($image->filename));
            #File::delete(image_path('images/' . $image->filename));
            $image->delete();
        } catch (\Exception $e) {
        }

        return 'success';
    }

    public function createGif(Request $request)
    {
        $file    = $request->video_file;
        $user    = User::find($request->user_id);
        $videoId = $request->video_id;
        $frames     = [];
        $durations  = [];
        $startTime  = $request->start_time;
        $endTime    = $request->end_time;
        try {
            if ($user) {
                $created = $this->createTempDir($videoId);
                if ($created['success']) {
                    $ffmpeg = $this->ffmpegInit();
                    $ffmpegVideo = $ffmpeg->open($file);
                    for ($i = $startTime; $i < $endTime; $i++) {
                        $path = public_path("temp/frames/$videoId/frame-$i.jpeg");
                        $ffmpegVideo->frame(TimeCode::fromSeconds($i))->save($path);
                        Image::compressImage($path, $path, 75);
                        $frames[]       = $path;
                        $durations[]    = 15; // Milliseconds
                    }
                    $gif = $this->convertToGif($frames, $durations, $videoId);
                    if ($gif) {
                        $response = $this->uploadGifToBucket($gif, $user, $videoId);
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Failed to upload gif'
                        ];
                    }
                } else {
                    return $response = $created;
                }
                $this->clearTemps($videoId);
                return response()->json($response);
            }
        } catch (\Exception $e) {

            $failMessage = $e->getMessage();
            $this->clearTemps($videoId);
            return response()->json([
                'success' => false,
                'message' => $failMessage
            ]);
        }
    }

    protected function createTempDir($id)
    {
        if ($id) {
            if (!is_dir(public_path('temp'))) mkdir(public_path('temp'));
            if (!is_dir(public_path('temp/frames'))) mkdir(public_path('temp/frames'));
            if (!is_dir(public_path("temp/frames/$id"))) mkdir(public_path("temp/frames/$id"));
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Failed to create temp directory'];
    }

    protected function ffmpegInit()
    {
        return FFMpeg::create(array(
            'ffmpeg.binaries' => env('FFMPEG_PATH'),
            'ffprobe.binaries' => env('FFPROBE_PATH'),
            'timeout' => 3600,
            'ffmpeg.threads' => 12,
        ));
    }

    protected function convertToGif($frames, $durations, $id)
    {
        $gc = new GifCreator();
        $gc->create($frames, $durations, 1000);
        $gifBinary = $gc->getGif();
        $randomString = str_random(32);
        $gifPath = public_path("temp/frames/$id/$randomString.gif");

        if (file_put_contents($gifPath, $gifBinary)) {
            return [
                'path' => $gifPath,
                'name' => "$randomString.gif"
            ];
        }
        return false;
    }

    protected function uploadGifToBucket($gif, $user, $videoId)
    {
        $ownerFolder = generate_owner_folder_name($user->email);
        $fileName    = $gif['name'];
        $fileKey     = "$ownerFolder/$videoId/gifs/$fileName";
        $filePath    = $gif['path'];
        $fileSize    = $this->get_remote_file_info($filePath)['fileSize'];

        $bucket_upload = Image::uploadImageToBucket($fileKey, $filePath, $fileSize);
        if ($bucket_upload['success']) {
            return [
                'success'   => true,
                'file_path' => $bucket_upload['file_path'],
            ];
        }
        return [
            'success'   => false,
            'wasabi_details' => $bucket_upload
        ];
    }

    public function get_remote_file_info($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        $data = curl_exec($ch);
        $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [
            'fileExists' => (int) $httpResponseCode == 200,
            'fileSize' => (int) $fileSize
        ];
    }

    protected function clearTemps($id)
    {
        $path = is_dir(public_path("temp/frames/$id")) ? public_path("temp/frames/$id") : false;
        if ($path) {
            $files = glob($path.'/*');
            foreach($files as $file) {
                if(is_file($file)) unlink($file);
            }
        }
    }

    public function imageUpload(Request $request) // Regular
    {
        $file = $request->file('file');
        $folder = $request->folder;
        $user = auth()->user();
        $uniqueId = $request->unique_id;

        $extension = $file->getClientOriginalExtension();
        $filename = str_random(32) . '.' . $extension;
        $savePath = public_path('temp/room-presenters') . '/' . $filename;
        $save = $file->move(public_path('temp/room-presenters'), $filename);
        Image::compressImage($savePath, $savePath, 75);

        $ownerFolder    = generate_owner_folder_name($user->email);
        $imageFileSize  = filesize($savePath);

        $fileKey    = "$ownerFolder/$folder/$uniqueId/$filename";
        $uploaded = Image::uploadImageToBucket($fileKey, $savePath, $imageFileSize);
        if (is_file($savePath)) unlink($savePath);

        return response()->json($uploaded);
    }
}
