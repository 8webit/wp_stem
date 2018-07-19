<?php
namespace _8webit\wp_stem;

class Sanitize{
    /**
     * @since 1.0.1
     *
     * @param $arg
     * @return string
     */
    public static function text($arg){
        return esc_html(sanitize_text_field($arg));
    }

    /**
     * @since 1.0.1
     *
     * @param $arr
     * @return array|bool
     */
    public static function array($arr){
        if(!is_array($arr)){
            return false;
        }
        array_walk_recursive($arr, function(&$item){
            $item = esc_html(sanitize_text_field($item));
        });

        return $arr;
    }
}