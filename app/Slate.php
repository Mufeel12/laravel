<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slate extends Model
{
    protected $table = 'slates';

    protected $fillable = ['*'];

    /**
     * Template relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template()
    {
        return $this->belongsTo('App\SlateTemplate');
    }

    /**
     * Video relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function video()
    {
        return $this->belongsTo('App\Video');
    }

    /**
     * Fields attribute mutator
     *
     * @param array $value
     */
    public function setFieldsAttribute($value)
    {
        $this->attributes['fields'] = is_array($value) ? json_encode($value) : json_encode([]);
    }

    public function getTemplateFieldsAttribute()
    {
        $value = json_decode($this->attributes['fields'], true);

        return is_null($value) ? [] : $value;
    }

    /**
     * Edit fields attribute accessor
     *
     * @return array
     */
    public function getFieldsAttribute()
    {
        $fields = json_decode($this->attributes['fields'], true);

        return is_null($fields) ? [] : $fields;

        /*$slateFields = json_decode($this->attributes['fields'], true);

        $slateFields = collect($slateFields);

        $templateFields = collect($this->template->fields);

        $fields = $slateFields->map(function ($item, $key) use ($templateFields) {

            $templateField = $templateFields->where('name', $key)->first();

            $templateField['value'] = $item;

            if ((isset($templateField['type'])) && $templateField['type'] == 'image') {
                $templateField['image'] = Image::getImageByUrl($templateField['value']);
            }

            return $templateField;
        });

        return is_null($fields) ? [] : $fields;*/
    }

    /**
     * Returns Thumbnail url
     *
     * @return string
     */
    public function getThumbnailAttribute()
    {
        $path = $this->getThumbnailPath();

        if (\File::exists($path))
            $url = \Bkwld\Croppa\Facade::url($this->getThumbnailPath($this, true), 350, 337);
        else {
            $this->createThumbnail($this);
            $url = asset('img/slatePreview/' . $this->template->template . '.png');
        }

        return $url;
    }

    /**
     * Returns
     * @param bool $slate
     * @param bool $asUrl
     * @return string
     */
    public function getThumbnailPath($slate = false, $asUrl = false)
    {
        if ($slate)
            $slateId = $slate->id;
        else
            $slateId = $this->attributes['id'];

        return slates_path('slates/' . $slateId . '.jpg', $asUrl);
    }

    /**
     * Creates a thumbnail for slate
     *
     * @param Slate $slate
     * @return bool
     * @throws \Exception
     */
    public function createThumbnail(Slate $slate)
    {
        post_without_wait(route('slates.generateThumbnail'), ['_token' => csrf_token(), 'id' => $slate->id]);
    }

    /**
     * Deletes a thumbnail image
     *
     * @param Slate $slate
     */
    public function deleteThumbnail(Slate $slate)
    {
        $thumbnailPath = $this->getThumbnailPath($slate);
        if(file_exists($thumbnailPath)){
            \Bkwld\Croppa\Facade::delete($thumbnailPath);
        }
    }
}
