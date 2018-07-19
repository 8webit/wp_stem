<?php 
namespace _8webit\wp_stem;

class Meta{
    /**
     * create new,update or delete meta value with post id
     *
     * @param $post_id integer
     * @param $meta_key string
     * @param string $new_value string
     * @param string $old_value string
     */
    public static function sync ($post_id,$meta_key,$new_value="",$old_value="") {
        $new_value = Sanitize::text($new_value);

        if (!isset($old_value)) { 
            add_post_meta($post_id, $meta_key, $new_value);
        } else if (!empty($new_value) && $old_value != $new_value) {
            //update meta
            update_post_meta($post_id, $meta_key, $new_value);
        } else if (empty($new_value)) { 
            //delete meta
            delete_post_meta($post_id, $meta_key);
        }
    }

    /**
     * get post meta without pass post id.
     * if $post_id not passed get_the_ID() function will be used to retrieve current id
     *
     * @param $key
     * @param string $post_id
     * @param bool $multiply
     * @return mixed
     */
    public static function get($key,$post_id='',$multiply=true){
        $post_id = !empty($post_id) ? $post_id : get_the_ID();

        return Sanitize::array(get_post_meta($post_id,$key,$multiply));
    }

    
    /**
    * when you want to mirror wordpress options field
    * inside custom options page,this function helps 
    * to update both fields and returns value.
    * when checking values,hight prioritety has mirror_options 
    *
    * @param String $wp_option
    * @param String $mirror_option
    * @return String
    */
    public static function mirror_option($wp_option,$mirror_option){
        $wp_option_value = get_option($wp_option);
        $mirror_option_value = esc_attr(get_option($mirror_option));
        
        $mirror_option_value = !empty($mirror_option_value) ? $mirror_option_value : $wp_option_value;

        if($mirror_option_value != $wp_option_value){
            update_option( $wp_option, $mirror_option_value);
        }

        return $mirror_option_value;
    }
}