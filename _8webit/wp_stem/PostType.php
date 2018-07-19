<?php
namespace _8webit\wp_stem;
use _8webit\wp_stem\Renderer\Meta_Box;

/**
 * Class PostType
 *
 * class for creating post type
 *
 * to create post type just use
 * PostType::create(your_post_type,[is_public],[has_archive])
 * 
 * @since 1.0.0
 */

class PostType
{
    use Instance;

    private static $options = array();

    /**
     * saves state when function chaining happens
     * 
     */
    private static $state = array(
        'post_type'     => '',
        'metabox_id'    => '',
    );

    protected function __construct() {}

    /**
     * used for initializing in functions.php.
     * loads postype and metabox
     */
    public static function init() {
        add_action('init', array(self::get_instance(), 'load_post_type'));
        add_action('add_meta_boxes', array(self::get_instance(), 'load_meta_boxes'));
    }

    /**
     * loads post type.
     * hooked to 'init' action in init function.
     */
    function load_post_type() {
        $options = self::get_all_options();
        foreach ($options as $post_type => $option) {
            unset($option['meta_boxes']);

            if(!post_type_exists($post_type)){
                register_post_type($post_type, $option);
            }
        }
    }


    /**
     * loads  meta boxes for post types.
     * hooked to 'add_meta_boxes' action in init function
     */
    function load_meta_boxes() {
        $options = self::get_all_options();
        $post_id  = get_the_ID();
      
        if (!empty($options)) {
            foreach ($options as $post_type => $option) {
                // if id is passed to add function
                if(is_numeric($post_type) && ($post_type != $post_id)){
                    return;
                }else if( strpos($post_type,'template') === 0 && ($post_type != get_page_template_slug()) ){
                   return ;
                }
                
                if(is_numeric($post_type) || strpos($post_type,'template') === 0){
                    $post_type = get_post_type();
                }

                if(isset($option['meta_boxes'])) {
                    Meta_Box::render($option['meta_boxes'], $post_type);
                }
            }
        }

        $meta_boxes = Meta_Box::get_meta_boxes();

        if (!empty($meta_boxes)) {
            foreach ($meta_boxes as $value) {
                add_meta_box(
                    $value['id'],
                    $value['title'],
                    $value['callback'],
                    $value['screen'],
                    $value['context'],
                    $value['priority'],
                    $value['callback_args']
                );
            }
        }
    }


    /**
     * used for hook for register fields for already
     * created post type,post id, or page template
     *
     * @param $arg string | int  post type,post id or page template
     * @return Instance
     */
    public static function add($arg){
        self::set_state_post_type($arg);

        return self::get_instance();
    }

    /**
     * creates the post type
     *
     * @param string $post_type_slug
     * @param array $options  same values as register_post_type() $args parameter.
     * @param bool $extend_defaults
     * @return Instance
     */
    public static function create($post_type_slug, $options = array(), $extend_defaults = true) {
        $ucfirst_post_type = ucfirst($post_type_slug);

        $post_type_defaults = array(
            'labels' => array(
                'name' => __($ucfirst_post_type),
                'singular_name' => __($ucfirst_post_type),
                'add_new_item' => __("Add New " . $ucfirst_post_type),
                'edit_item' => __("Edit " . $ucfirst_post_type),
                'new_item' => __("New " . $ucfirst_post_type),
                'view_item' => __("View " . $ucfirst_post_type),
                'search_items' => __("Search " . $ucfirst_post_type),
                'all_items' => __("All " . $ucfirst_post_type),
                'archives' => __($ucfirst_post_type . ' Archives'),
                'attributes' => __($ucfirst_post_type . ' Attributes'),
                'uploaded_to_this_item' => __("Uploaded To this " . $ucfirst_post_type)
            ),
            'public' => true,
            'has_archive' => false,
            'supports' => array('title', 'editor', 'thumbnail')
        );

        if (!empty($options)) {
            $options = $extend_defaults ? array_merge($post_type_defaults, $options) : $options;
        } else {
            $options = $post_type_defaults;
        }

        self::set_state_post_type(strtolower($post_type_slug));

        self::add_post_type(self::get_state_post_type(), $options);

        return self::get_instance();
    }


    /**
     * creates meta box for post type.
     * if no screen parameters passed,latest created post type value will be used.
     *
     * @link https://developer.wordpress.org/reference/functions/add_meta_box/
     *
     * @param array $options contains same parameters as add_meta_box
     * @return Instance|PostType
     */
    public static function meta_box($options = array()) {
        $post_type = self::get_state_post_type();

        self::set_state_meta_box_id($options['id']);
        self::add_meta_box($post_type,$options['id'],$options);

        return self::get_instance();
    }

    /**
     * adds field to post type in meta box
     *
     * @param array $field
     * @return Instance|PostType
     * @internal param array $fields
     * @internal param string $post_id
     * @internal param string $post_type
     */
    public static function field($field = array()) {
		$post_type = self::get_state_post_type();
		$meta_box_id = self::get_state_meta_box_id();

		$defaults = array();

		$field['id'] = isset($field['id']) ? $field['id'] : $field['name'] .'_id';

		if(isset($field['cloneable'])){
		   $cloneable_defaults = array(
		       "options"   => array(
		           "title"                 => __($field["label"]." Cloning box"),
		           "add_button"            => __("Add Another Clone"),
		           "add_button_icon"       => 'dashicons dashicons-plus',
		           "remove_button_icon"    => 'dashicons dashicons-trash',
		       )
		   );

		   if(isset($field['options'])) {
		       $defaults['options'] = array_merge($cloneable_defaults['options'], $field['options']);
		   }else{
		       $defaults['options'] = $cloneable_defaults['options'];
		   }
		}

		$field = array_merge($field,$defaults);

		self::add_field_to_meta_box($post_type,$meta_box_id,$field['name'],$field);

		return self::get_instance();
    }


    /**
     * adds cloneable field group to meta box
     *
     * @param array $group
     * @return Instance|PostType
     * @internal param array $fields
     */
    public static function cloneable_group($group = array()) { 
        $post_type = self::get_state_post_type();
        $meta_box_id = self::get_state_meta_box_id();

        $group_defaults = array(
            "options"   => array(
                "title"                 => __("Cloneable Group"),
                "add_button"            => __("Add Another  Group"),
                "remove_button"         => __("Remove Group"),
                "add_button_icon"       => 'dashicons dashicons-plus',
                "remove_button_icon"    => 'dashicons dashicons-trash',
            )
        );

        if(isset($group['options'])) {
            $group['options'] = array_merge($group_defaults['options'], $group['options']);
        }else{
            $group['options'] = $group_defaults['options'];
        }

        self::add_field_to_meta_box($post_type,$meta_box_id,$group[0],$group);

        return self::get_instance();
    }

    /**
     * sets taxonomy for given Post Type
     *
     * @param $options_arr
     * @return Instance|PostType
     */
    public static function taxonomy($options_arr) {
        $post_type = self::get_state_post_type();

        self::set_taxonomy($post_type, $options_arr);

        return self::get_instance();
    }

    /**
     * adding support for post type
     * default supports are : title, editor, thumbnail
     *
     * @param array $options
     * @param bool $extend_defaults
     * @return Instance|PostType
     */
    public static function supports($options = array(), $extend_defaults = true) {
        $post_type = self::get_state_post_type();

        $defaults = array('title', 'editor', 'thumbnail');
        $supports = array();

        if (is_array($options)) {
            $supports = $extend_defaults ? array_merge($defaults, $options) : $options;
        }

        self::set_support($post_type, $supports);

        return self::get_instance();
    }

    private static function add_post_type($key, $options) {
        self::$options[$key] = $options;
    }

    public static function get_saved_option($post_type) {
       return isset(self::$options[$post_type])
            ? self::$options[$post_type]
            : false;
    }


    public static function add_meta_box($post_type,$id,$options){
        self::$options[$post_type]['meta_boxes'][$id] = $options;
    }
    public static function get_meta_box($post_type,$id){
        return self::$options[$post_type]['meta_boxes'][$id];
    }

    public static function add_field_to_meta_box($post_type,$meta_box_id,$field_name,$field_options){
        self::$options[$post_type]['meta_boxes'][$meta_box_id]['fields'][$field_name] = $field_options;
    }

    public static function get_fields_from_meta_box($post_type, $meta_box_id){
        return self::$options[$post_type]['meta_boxes'][$meta_box_id]['fields'];
    }

    public static function get_all_options() {
        return self::$options;
    }

    public static function set_taxonomy($key, $value) {
        self::$options[$key]['taxonomies'] = $value;
    }

    public static function set_support($key, $value) {
        self::$options[$key]['supports'] = $value;
    }

    private static function set_state_post_type($post_type){
        self::$state['post_type'] = $post_type;
    }

    private static function get_state_post_type(){
        return self::$state['post_type'];
    }

    private static function set_state_meta_box_id($id){
        self::$state['meta_box_id'] = $id;
    }

    private static function get_state_meta_box_id(){
        return self::$state['meta_box_id'];
    }

}


