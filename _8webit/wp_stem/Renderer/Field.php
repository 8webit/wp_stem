<?php
namespace _8webit\wp_stem\Renderer;

use _8webit\wp_stem\Sanitize;

/**
 * Responsible For render fields
 *
 * @since 1.0.0
 * @package _8webit\wp_stem\Renderer
 */
class Field{
     protected function __construct(){}

    /**
     * generates input or textarea field
     *
     * @param $field
     * @return string HTML
     */
    public static function render_field($field){
        if(isset($field['type'])) {
            return;
        }

        $field = Sanitize::array($field);

        $wrapper = isset($field['wrapper']) ? $field['wrapper'] : true;
        $wrapper_class = isset($field['wrapper_class']) ? $field['wrapper_class'] : 'field-wrapper';

        switch ($field['type']){
            case 'textarea':
                return self::textarea($field,$wrapper,$wrapper_class);
                break;
            default:
                return self::input($field,$wrapper,$wrapper_class);
        }
    }

    public static function select($field) {
	    $select = '<select name="' . $field['name'] . '"';

	    if(isset($field['class']) && !empty($field['class'])){
	    	$select .= ' class="' .$field['class']. '" >';
	    }else{
	    	$select .= '>';
	    }

	    if(isset($field['value']) && !empty($field['value'])) {
		    $select .= '<option value="' . $field['value'] . '">' . $field['value'] . '</option>';
	    }

	    $select .= '</select>';

	    return $select;
    }



    /**
     * generates input field HTML
     *
     * @param $args
     * @param bool $field_wrapper
     * @param string $field_wrapper_class
     * @return string HTML
     */
    public static function input($args,$field_wrapper=true,$field_wrapper_class="field-wrapper"){
        $html = '';
        $label = '';
        $attribute_html = '';
 
        foreach ($args as $atr => $value) {
            if ($atr == 'label' && isset($args['id'])) {
                $label = self::label($args['id'],$value);
            }else{
                 $attribute_html .= $atr . '="' . $value . '" ';
            }
        }

        $html .= $label;

        if($field_wrapper) {
           $input = '<input ' . $attribute_html . '/>';
           $html .= self::wrapper($input,'div',$field_wrapper_class);
        }else{
        	$html .= '<input ' . $attribute_html . '/>';
        }

        return $html;
    }

    /**
     * generates textarea HTML
     *
     * @param $args
     * @param bool $field_wrapper
     * @param string $field_wrapper_class
     * @return string
     */
    public static function textarea($args,$field_wrapper=true,$field_wrapper_class="field-wrapper"){
        $html = '';
        $label = '';
        $attributes_html = '';

        foreach ($args as $atr => $value) {  
            if ($atr == 'label' && isset($args['id'])) {
                $label.= self::label($args['id'],$value);
            }else{
                   $attributes_html .= $atr. '="'.$value.'"  ';   
            }
        }

        $html .= $label;

        if($field_wrapper) {
            $textarea = '<textarea ' . $attributes_html . '>' . $args['value'] . '</textarea>';
            $html .= self::wrapper($textarea,'div',$field_wrapper_class);
        }

        return $html;
    }

        // return $doc->saveHTML();
    /**
     * generates label html
     *
     * @param $id
     * @param $value
     * @param bool $wrapper adds wrapper to label
     * @param string $wrapper_class
     * @return string
     */
    public static function label($id,$value,$wrapper=true,$wrapper_class='label-wrapper'){
        $html = '';
        $label  = '<label for="' . $id . '">' . $value . '</label>';

        if($wrapper) {
            $html .= self::wrapper($label,'div',$wrapper_class); // adds wrapper around label
        }else{
            $html = $label;
        }

        return $html;
    }

    /**
     * adds wrapper tag for given html
     *
     * @param $content_html
     * @param $wrapper_tag
     * @param string $wrapper_class
     * @return string
     */
    public static function wrapper($content_html,$wrapper_tag='div',$wrapper_class='field-wrapper'){
        $html ='';
        $html .= '<'.$wrapper_tag.' class="' .$wrapper_class. '">';
        $html .= $content_html;
        $html .= '</'.$wrapper_tag.'>';

        return $html;
    }


    public static function append($element,$content){
        $counter = 0;
        for($i=1; $i < strlen($element); $i++){
            if($element[$i] !==" "){
                $counter++;
            }else{
                break;
            }
        }
        $counter = $counter + 3;
        $insert_index = strlen($element)-$counter;
       
        $result = substr($element, 0, $insert_index) . $content . substr($element, $insert_index);
        return $result;
    }

    function get_checked_field($needle, $heystrack){
        $result = array();
    
        foreach($heystrack as $value){
            $result[$value] = $needle == $value ? 'checked="checked"' : '';
        }
    
        return $result;
    }
}