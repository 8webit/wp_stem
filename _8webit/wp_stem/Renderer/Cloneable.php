<?php

namespace _8webit\wp_stem\Renderer;

use _8webit\wp_stem\Enqueue_Scripts;

class Cloneable{
    protected function __constructor() {}

    public static function single($field,$options){
         $field['id'] = isset($field['id']) ? $field['id'] : $field['name'] .'_id';

        $html ='<div class="cloneable">';
        $html .= isset($options['title']) ? 
                    '<h3 class="title" >' .$options['title'].  '</h3>' :  '';

        if (is_array($field['value'])) {
            $html .= self::single_clones($field,$options);
        } else {
            $html .= self::single_clone($field,$options);
        }

        $html .= self::render_add_button($options['add_button'],$options['add_button_icon']);
        $html .= '</div>'; // end of .cloneable

        return $html;
    }

    private static function single_clone($field,$options,$hide_remove_btn=false){
        $render_field_html = Advanced_Field::render_field($field);
        $remove_btn_html = self::render_remove_button($options['remove_button_icon'],$hide_remove_btn);
        $html = Field::append($render_field_html,$remove_btn_html);

        return $html;
    }

    private static function single_clones($field,$options){
        $html = '';
        $i = 2;
        $clone = $field;

        $hide_remove_btn = false;

        foreach ($field['value'] as $value) {
            $clone['value'] = $value;
            
            $html .= self::single($clone,$options,$hide_remove_btn);

            $clone['name'] = $field['name'] .'_'. $i;
            $clone['id'] = $field['id'] .'_'.$i;
            $clone['label'] = $field['label'] .' #'. $i;
            $i++;
        }

        return $html;  
    }

    public static function group($group){
        Enqueue_Scripts::admin(
            'cloneable_field.js',
            Enqueue_Scripts::get_js_uri(),
            false
        );

        $html = '<div class="cloneable-group">';

        $options = $group['options'];
        $fields = $group['fields'];
        $meta_values = $group['value'];

        $html .= isset($options['title'])
            ? '<h3 class="title" >' .$options['title'].  '</h3>'
            :'';

        // save meta values to field values
        if(isset($group['value'])){
            foreach($fields as $key => $field){
                if(isset($meta_values[$field['name']])){
                    $fields[$key]['value'] = $meta_values[$field['name']];
                }
            }
        }

        if(isset($field[0]['value']) && is_array($fields[0]['value'])){
            $html .= self::group_cloneable_values($fields,$options);
        }

        $html .= self::render_add_button($options['add_button'],$options['add_button_icon']);
        $html .= '</div>'; // end of .cloneable-group

        return $html;
    }

    private static function group_cloneable_values($group,$options){
        Enqueue_Scripts::admin(
            'cloneable_field.js',
            Enqueue_Scripts::get_js_uri(),
            false
        );

        $i = 2;
        $hide_remove_btn = false;
        $html = '';
        $cloned = array();

        // render clone with actions buttons
        for($j=0; $j < count($group[0]['value']); $j++) {
            $html .= '<div class="cloneable-entry" >';

            // create clone and render it
            foreach ($group as $field) {
                $field['id'] = isset($field['id']) ? $field['id'] : $field['name'] .'_id';

                $cloned = $field;

                if($j > 0) {
                    $cloned['name']  = $field['name'] . '_' . $j;
                    $cloned['id']    = $field['id'] . '_' . $j;
                    $cloned['value'] = $field['value'][ $j ];
                }

                $html .= Advanced_Field::render($cloned);
                $i++;
            }

            $html .= self::render_remove_button($options['remove_button_icon'],$hide_remove_btn);
            $html .= '</div>';

            $hide_remove_btn = false;
        }

        return $html;
    }

    /**
     * @since 1.0.0
     *
     * @param string $text
     * @param string $icon glyphicon class
     * @param bool $hide
     *
     * @return string
     */
    public static function render_add_button($text,$icon,$hide=false){
        $class = 'button button-primary button-add-clone ';
        $class .= $hide ? ' hide ' : '';

        $html  = '<div class="meta-field-action">';
        $html .= '<a href="javascript:void(0);" class="' .$class. '">';
        $html .= '<span class="' .$icon. '" ></span>';
        $html .= $text;
        $html .= '</a>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @since 1.0.0
     *
     * @param string $icon glyphicon class
     * @param bool $hide
     *
     * @return string
     */
    public static function render_remove_button($icon, $hide=false){
        $class = 'button button-danger button-remove-clone ';
        $class .= $hide ? ' hide ' : '';

        $html = '<div class="meta-field-action">';
        $html .= '<a href="javascript:void(0);" class="' .$class. '">';
        $html .=  '<span class="' .$icon. '" ></span>';
        $html .= '</a>';
        $html .= '</div>';

        return $html;
    }
}