<?php
namespace _8webit\wp_stem;

/**
 * This class is responsible for saving data of cloneable or fonts fields.
 * hooked on 'save_post' hook
 *
 * @since 1.0.0
 *
 * @package _8webit\wp_stem
 */
class Save_Post_Hook{
    use Instance;

    public static function init(){
        add_action('save_post', array(self::get_instance(), 'on_save_post'));
    }

    function on_save_post($post_id) {
        $saved_options = self::get_options($post_id);

        if($saved_options) {
            foreach ($saved_options['meta_boxes'] as $meta_box) {
                $fields = $meta_box['fields'];

                $nonce_action = 'meta_box_' . $meta_box['id'];
                $nonce_name = 'nonce_' . $meta_box['id'];
                $nonce_value = isset($_POST[$nonce_name]) ? $_POST[$nonce_name] : false;

                if (current_user_can('edit_posts') && wp_verify_nonce($nonce_value, $nonce_action) && !empty($fields)) {
                        foreach ($fields as $key => $field) { 
                            $post_meta_key = isset($field['name']) ? $field['name'] : $field[0];

                            $old_meta_value = isset($field['name']) ? Meta::get($field['name']) : $field[0];
                            $new_meta_value = isset($_POST[$post_meta_key]) ? $_POST[$post_meta_key] : '';

                            // get new meta value from $_POST for ...
                            if (isset($field['cloneable'])) { // single cloneable
                                $new_meta_value = self::find_cloneable($post_meta_key);
                            } else if (isset($field['fields'])) { // group cloneable
                                $new_meta_value = self::find_group_cloneable($field['fields']);
                            }else if($field['type'] === 'font'){
                                $new_meta_value = self::find_font($field);
                            }

                            Meta::sync($post_id,$post_meta_key,$new_meta_value,$old_meta_value);
                        } //end of foreach
                    }
            }
        }
    }

    private static function get_options($post_id){
        $post_type = get_post_type($post_id);
        
        $saved_options = PostType::get_saved_option($post_type);

        if($saved_options == false){
            $saved_options =  PostType::get_saved_option($post_id);

            if($saved_options == false){
                $saved_options =  PostType::get_saved_option(get_page_template_slug( $post_id ) );
            }
        }

        return $saved_options;
    }

    // finds cloneable fields in $_POST with field name prefix
    private static function find_cloneable($post_meta_key){
        $meta_values = array();

        foreach ($_POST as $_post_key => $value) {
            if (strpos($_post_key, $post_meta_key) === 0) {
                $meta_values[] = $value;
            }
        }

        return $meta_values;
    }

    // finds cloneable fields in $_POST with Group ID prefix
    // and ands it $new_meta_value array
    private static function find_group_cloneable($fields){
        $meta_values = array();

        foreach ($fields as $group_field) {
            
            foreach ($_POST as $key => $value){
                    $name = $group_field['name'];

                    if (strpos($key, $name) === 0) {
                        $meta_values[$name][] = $value;
                    }
                }//$_POST loop
    
        } // group loop

        return $meta_values;
    }

    private static function find_font($field){
        $result = array();
        $key = $field['name'];
        
        $result['family']  = $_POST[$key. '_family'];
        $result['variant'] = $_POST[$key. '_variant'];
        $result['subset']  = $_POST[$key.  '_subset'];
        $result['size']    = $_POST[$key. '_size'];
        
        return $result;
    }
}