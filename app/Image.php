<?php

namespace App;
use Aws\S3\S3Client;
use App\Video;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'images';

    public static function formatImages($images)
    {
        foreach ($images as $k => $v) {
            $images[$k] = self::formatImage($images[$k]);
        }
        return $images;
    }

    /**
     * Formats an image
     *
     * @param $image
     * @return mixed
     */
    public static function formatImage($image)
    {        
        $image->formatted_date = $image->created_at->format('jS F, o');
        $image->url = $image->path;
        //$image->mini_thumbnail = url(\Bkwld\Croppa\Facade::url(image_path($image->filename, true), 40, 40));
        $image->mini_thumbnail = $image->path;
        $image->title = htmlspecialchars(strip_tags($image->title));
        //$image->thumbnail = url(\Bkwld\Croppa\Facade::url(image_path($image->filename, true), 230));
        $image->thumbnail = $image->path;
        return $image;
    }

    // public function getUrlAttribute()
    // {
    //     return image_path($this->filename, true);
    // }

    /**
     * Returns image object if found
     *
     * @param $url
     * @return mixed
     */
    public static function getImageByUrl($url)
    {
        // Clean up image url
        $url = Image::cleaUpImageUrl($url);
        $urlParts = explode('/', $url);
        $filename = last($urlParts);
        $filename = self::baseFilename($filename);
        $image = Image::where('filename', $filename)->first(); #->remember(20)->first();
        return $image;
    }

    /**
     * Returns base image filename from cropped image filename
     *
     * @param $filename
     * @return string
     * @internal param $url
     */
    public static function baseFilename($filename)
    {
        $parts = explode('-', $filename);
        if (isset($parts[1])) {
            # get extension
            $extension = explode('.', last($parts))[1];
            return $parts[0] . '.' . $extension;
        }
        return $filename;
    }

    /**
     * Cleans up image url from all variables and tokens
     *
     * @param $imageUrl
     * @return string
     */
    public static function cleaUpImageUrl($imageUrl)
    {
        $urlParts = explode('/', parse_url($imageUrl, PHP_URL_PATH));
        $filename = array_pop($urlParts);
        $filename = self::baseFilename($filename);

        # put it now back
        $imageUrl = config('env.ROOT_URL') . '/' . implode('/', $urlParts) . '/' . $filename;
        return $imageUrl;
    }

    /**
     * Resizes width and height and keeps ratio
     *
     * @param $width
     * @param $height
     * @return array
     */
    public static function adjustWidthAndHeight($width, $height)
    {
        // Make sure height and width are under 1000, otherwise resize with ratio
        if ($width > 1000 || $height > 1000) {
            // Calculate w/h ratio
            $widthHeightRatio = $width / $height;
            if ($widthHeightRatio < 1) {
                $height = 1000;
                $width = round($height * $widthHeightRatio);
            } else {
                $width = 1000;
                $height = round($width * $widthHeightRatio);
            }
        }

        return [round($width), round($height)];
    }

    public static function isCroppable($url)
    {
        try {
            return (strpos($url, env('THUMBNAIL_URL')) !== false);
        } catch (\Exception $error) {return false;}
    }

    public static function uploadImageToBucket($file_key, $file_path, $file_size)
    {
        $endpoint = config('aws.endpoint');
        $bucket = config('aws.bucket');

        $s3 = S3Client::factory(array(
            'endpoint' => $endpoint,
            'region' => config('aws.region'),
            'version' => config('aws.version'),
            'credentials' => config('aws.credentials')
        ));

        try {
            $s3->putObject(array(
                'Bucket' => $bucket,
                'Key' => $file_key,
                'Body' => file_get_contents($file_path),
                'ContentLength' =>$file_size,
                'ACL' => 'public-read'
            ));

            $file_path = $s3->getObjectUrl($bucket, $file_key);
            return ['success' => true, 'file_path' => $file_path];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    public static function saveTempImage($file, $id)
    {
        $tempDir = self::createTempDir('stage-logo', $id);
        if (!$tempDir['success']) return $tempDir;
        $randomString = str_random(32).'.jpeg';
        $path = $tempDir['path'];
        $filePath = "$path/$randomString";
        if (file_put_contents($filePath, $file)) {
            return ['success' => true, 'file_path' => $filePath];
        }
        return ['success' => false];
    }

    public static function createTempDir($folderName, $id)
    {
        if ($folderName) {
            if (!is_dir(public_path('temp'))) mkdir(public_path('temp'));
            if (!is_dir(public_path("temp/$folderName"))) mkdir(public_path("temp/$folderName"));
            if (!is_dir(public_path("temp/$folderName/$id"))) mkdir(public_path("temp/$folderName/$id"));
            return ['success' => true, 'path' => public_path("temp/$folderName/$id")];
        }
        return ['success' => false, 'message' => 'Failed to create temp directory'];
    }

    public static function clearTemps($folderName, $id)
    {
        $path = is_dir(public_path("temp/$folderName/$id")) ? public_path("temp/$folderName/$id") : false;
        if ($path) {
            $files = glob($path.'/*');
            foreach($files as $file) {
                if(is_file($file)) unlink($file);
            }
        }
    }

    public static function compressImage($source, $destination, $quality)
    { 
        // Get image info 
        $imgInfo = getimagesize($source); 
        $mime = $imgInfo['mime']; 
         
        // Create a new image from file 
        switch($mime){ 
            case 'image/jpeg': 
                $image = imagecreatefromjpeg($source); 
                break; 
            case 'image/png': 
                $image = imagecreatefrompng($source); 
                break; 
            case 'image/gif': 
                $image = imagecreatefromgif($source); 
                break; 
            default: 
                $image = imagecreatefromjpeg($source); 
        } 
         
        // Save image 
        imagejpeg($image, $destination, $quality); 
         
        // Return compressed image 
        return $destination; 
    }

    public static function getFileKey($path, $email, $folder)
    {
        self::compressImage($path, $path, 75); // Compress Image
        $ownerFolder    = generate_owner_folder_name($email);
        $imageFileSize  = filesize($path);
        $imageFileExt   = 'jpeg';
        $imageFileName  = str_random(32) . '.' . $imageFileExt;

        $fileKey        = "$ownerFolder/$folder/$imageFileName";
        return [
            'path'      => $path,
            'filekey'   => $fileKey,
            'size'      => $imageFileSize
        ];
    }

    public static function deleteImages($owner, $folder)
    {
        $bucket = config('aws.bucket');
        $endpoint = config('aws.endpoint');

        /* get s3 client */
        $s3 = S3Client::factory(array(
            'endpoint' => $endpoint,
            'region' => config('aws.region'),
            'version' => config('aws.version'),
            'credentials' => config('aws.credentials')
        ));

        /* find owner and generate folder name */
        $owner_folder = generate_owner_folder_name($owner->email);
        $file_key = "$owner_folder/$folder/";

        /* try to delete objects */
        try {
            $s3->deleteMatchingObjects($bucket, $file_key);
            return ['success' => true, 'message' => 'Your image files deleted successfully.'];
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    public static function convertBase64($img_data)
    {
        list($type, $img_data) = explode(';', $img_data);
        list(, $img_data)      = explode(',', $img_data);
        return base64_decode($img_data);
    }

    public static function getPreviews($video, $user)
    {
        if (!$video->video_chapters || !count($video->video_chapters)) return [];

        $endpoint = config('aws.endpoint');
        $bucket = config('aws.bucket');
        $data = [];
        $chapters = Video::convertChaptersToSeconds($video->video_chapters);

        $s3 = S3Client::factory(array(
            'endpoint' => $endpoint,
            'region' => config('aws.region'),
            'version' => config('aws.version'),
            'credentials' => config('aws.credentials')
        ));

        $ownerFolder = generate_owner_folder_name($user->email);
        $file_key = "$ownerFolder/$video->video_id/preview/";
        $objects = $s3->getIterator('ListObjects', array(
            'Bucket' => $bucket,
            'Prefix' => "$file_key"
        ));
        foreach ($objects as $item) { 
            $image = $item['Key'];
            $second = self::getNumbersFromString($image);
            if (!strpos($image, '.vtt')) {
                $match = array_filter($chapters, function ($c) use ($second){
                  return (number_format($c / 3) == $second);
                });
                if (count($match)) $data[] = self::mapCDNUrl($item['Key']);
            }
        }
        return $data;
    }

    public static function mapCDNUrl($url, $skip = false)
    {
        $cdnHost = "stream.adilo.com/adilo-encoding";
        $parsedUrl = parse_url($url);
	    $path = preg_replace('/^(\/+)/', '', $parsedUrl['path']);
        $url = "https://" . $cdnHost . "/" . $path . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');
	    return $url;
    }

    public static function getNumbersFromString($str)
    {
        $str = substr($str, strpos($str, 'preview_'));
        if ($str) {
            preg_match_all('!\d+!', $str, $matches);
            if ($matches[0] && $matches[0][0])
            return (number_format($matches[0][0]));
        } else {
            return false;
        }
    }
}
