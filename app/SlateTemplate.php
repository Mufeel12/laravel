<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SlateTemplate extends Model
{
    protected $table = 'slate_templates';

    /**
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'template',
        'fields'
    ];

    /**
     * Slates relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function slates()
    {
        return $this->belongsToMany('App\Slate');
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

    /**
     * Fields attribute accessor
     *
     * @return array
     */
    public function getFieldsAttribute()
    {
        $value = json_decode($this->attributes['fields'], true);
        // Countdown fix empty values
        if (isset($value['countdown_date']) && empty($value['countdown_date'])) {
            $value['countdown_date'] = date('j. F, Y', (time() + 86400 * 9)); # date from now + 9 days
        }
        if (isset($value['countdown_time']) && empty($value['countdown_time'])) {
            $value['countdown_time'] = date('g:i A');
        }
        return is_null($value) ? [] : $value;
    }

    /**
     * Fields attribute accessor, special formatting for slate editor
     *
     * @return array|static
     */
    public function getEditorSectionsAttribute() {

        $value = json_decode($this->attributes['fields'], true);

        return is_null($value) ? [] : $this->formatFields($value);
    }

    /**
     * Format fields group in section, parent and children
     *
     * @param $fields
     * @return static
     */
    public function formatFields($fields) {

        $templateFields = collect($fields);

        // Get sections first
        $sections = $templateFields->unique('section')->map(function ($section) {
            return $section['section'];
        });

        // Labels for web icons
        $icons = [
            'Body' => 'view_quilt',
            'Header' => 'view_stream',
            'Main' => 'view_headline',
            'Footer' => 'view_stream'
        ];

        $array = [];

        $sections->each(function ($item) use ($templateFields, $icons, &$array) {

            // Get field elements that are not connected with anything and match this section
            $parentFields = $templateFields->filter(function ($field) use ($item) {
                return !isset($field['connectedWith']) && $field['section'] == $item;
            });

            $array[$item]['id'] = str_random(10);
            $array[$item]['section_title'] = $item;
            $array[$item]['icon'] = (isset($icons[$item]) ? $icons[$item] : false);

            // Set field-set to $array
            $array[$item]['fields'] = $parentFields->map(function($field, $key) use ($templateFields) {

                $parentFieldName = $field['name'];

                // Get children for the parent field
                $children = $templateFields->filter(function ($field) use ($parentFieldName) {
                    return isset($field['connectedWith']) && $field['connectedWith'] == $parentFieldName;
                });

                // Handle multiple color fields, merge into one
                $children = $children->each(function ($index, $key) use (&$children) {

                    if (count($children) > 1 && $children[$key]['type'] == 'color') {

                        $children->except($key)->each(function($secondChild, $secondChildKey) use ($key, &$children) {
                            if (isset($secondChild['type']) && $secondChild['type'] == 'color' && key($secondChild) != $key) {
                                // Set up array if it hasn't been done yet
                                if (isset($children[$key]['type']) && $children[$key]['type'] == 'color') {
                                    $children[$key] = [
                                        'type' => 'multipleColor',
                                        'attributes' => $children[$key]['attributes'],
                                        'colorPickerPosition' => (isset($children[$key]['colorPickerPosition']) ? $children[$key]['colorPickerPosition'] : ''),
                                        'value' => [$children[$key]]
                                    ];
                                }
                                $children->forget($secondChildKey);

                                $temp = $children[$key];
                                $temp['attributes'] = array_merge($children[$key]['attributes'], $secondChild['attributes']);
                                $temp['value'][] = $secondChild;

                                $children[$key] = $temp;
                            }
                        });

                    }
                    return $children;
                });


                // Only sets 'children' variable when children are available
                if (count($children) > 0)
                    $field['children'] = $children;

                return $field;
            });

        });

        return $array;
    }
}
