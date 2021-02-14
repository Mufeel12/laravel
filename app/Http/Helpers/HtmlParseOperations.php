<?php
/**
 * Created by PhpStorm.
 * User: erwinflaming
 * Date: 11/07/15
 * Time: 23:20
 */

if (!function_exists('parseHtmlForm')) {
    /**
     * Parsing relevant form inputs from HTML string
     *
     * Inputs of type hidden, text, password, textarea, checkbox,
     * radio, submit, reset, button, image, select and its options.
     *
     * @param string $html
     * @return array $inputs
     */
    function parseHtmlForm($html)
    {
        $html = str_replace("\\'", '"', $html);
        $dom = \Sunra\PhpSimple\HtmlDomParser::str_get_html($html);
        $form = $dom->find('form');
        $method = 'get';
        if (isset($form[0]->attr['method']))
            $method = strtolower(str_replace("\\'", "", $form[0]->attr['method']));
        $inputs = [
            'meta' => [
                'action' => str_replace("\\'", "", $form[0]->attr['action']),
                'method' => $method
            ],
            'email' => '',
            'name' => '',
            'submit' => '',
            'additional' => []
        ];
        // stuff that is not to be found in mail type string
        $notMailTypeNeedles = [
            'submit',
            'hidden',
            'password',
            'checkbox',
            'radio',
            'reset',
            'button',
            'image',
            'select'
        ];
        ### get all DOM inputs ###
        $domInputs = $dom->find('input');
        foreach ($domInputs as $input) {
            $input->type = str_replace("\\'", "", $input->type);
            $input->name = str_replace("\\'", "", $input->name);

            if ((strstr(strtolower($input->name), 'mail') != false)
                && !strposarray($input->name, $notMailTypeNeedles)) {
                $inputs['email'] = ($input->name);
            }
            elseif ((strstr(strtolower($input->name), 'name') != false)
                && !strposarray($input->name, $notMailTypeNeedles)) {
                $inputs['name'] = str_replace("\\'", "", $input->name);
            }
            elseif($input->type == "submit") {
                $inputs['submit'] = $input->name;
            }
            else {
                $additionalInput['name'] = str_replace("\\'", "", $input->name);
                $additionalInput['value'] = str_replace("\\'", "", $input->value);
                $additionalInput['type'] = str_replace("\\'", "", $input->type);
                $inputs['additional'][] = $additionalInput;
            }
        }
        return $inputs;
    }
}