<?php
namespace _8webit\wp_stem\Renderer;

use _8webit\wp_stem\Meta;

/**
 * Responsible for render meta box
 * @since 1.0.0
 * @package _8webit\wp_stem\Renderer
 */
class Meta_Box {
    protected static $post_type;

    /**
     * last meta box key used in function chaining
     *
     * @var string
     */
    protected static $meta_box_id;

    /**
     * saves  meta box options,which is generated by current class.
     * rendered by PostType class
     *
     * Tip: When you read 'saves it in meta box' meant this Property
     *
     * @see PostType::render_meta_boxes()
     *
     * @var array
     */
    private static $meta_boxes = array();

    /**
     * saves post meta options,which is generated by current class
     *
     * @var array
     */
    private static $post_metas = array();

    /**
     * instance of current class
     *
     * @var Meta_Box
     */
    private static $instance = null;

    /**
     * returns instance of current class
     *
     * @return Meta_Box
     */
    public static function get_instance()
    {
        if(self::$instance == null){
            self::$instance = new self;
        }

        return self::$instance;
    }

    protected  function __construct(){}

    /**
     * Renders meta box with  fields and Saves it in meta boxes
     *
     * @see Meta_Box::$meta_boxes
     *
     * @param $meta_boxes
     * @param $post_id
     * @param $post_type
     */
    public static function render($meta_boxes, $post_id, $post_type){
        $default_callback = array(self::get_instance(), 'meta_box_display_callback');

        foreach($meta_boxes as $meta_box) {

            self::$meta_box_id = $meta_box['id'];

            if (isset($meta_box['fields'])) {
                self::render_fields($meta_box['fields'],self::$meta_box_id);
            }

            self::set_meta_box(self::$meta_box_id, array(
                'id' => $meta_box['id'],
                'title' => $meta_box['title'],
                'callback' => !empty($meta_box['callback']) ? $meta_box['callback'] : $default_callback,
                'screen' => !empty($meta_box['screen']) ? $meta_box['screen'] : $post_type,
                'context' => !empty($meta_box['context']) ? $meta_box['context'] : 'advanced',
                'priority' => !empty($meta_box['priority']) ? $meta_box['priority'] : 'default',
                'callback_args' => !empty($meta_box['callback_args']) ?
                    $meta_box['callback_args'] : self::get_meta_box_callback_args(self::$meta_box_id)
            ));
        }
    }


    /**
     * display callback for  meta box
     *
     *
     * @param $post
     * @param $meta_box
     */
    public static function meta_box_display_callback($post, $meta_box)
    {
        if (!empty($meta_box['args'])) {
            foreach ($meta_box['args'] as $value) {
                echo $value;
            }
        }
    }

	/**
	 * renders fields for meta box and saves it in meta box callback ags
	 *
	 * @param $fields
	 * @param $meta_box_id
	 */
    protected static function render_fields( $fields, $meta_box_id ){
        foreach ( $fields as $key => $field) {
            $html = '';

            // if is group cloneable fin meta_value with group_id,else field[name]
            $meta_value = isset($field['fields'])
                ? Meta::get($field[0])
                : Meta::get($field['name']);
           
            if(!empty($meta_value)){
                $field['value'] = $meta_value;
            }else{
                $field['value'] = isset($field['value']) ? $field['value'] : '';
            }
                                
           $html .= Advanced_Field::render($field);

            self::set_post_meta( $meta_box_id, $field );
            self::set_meta_box_callback_args( $meta_box_id, $html );
        }
        self::add_nonce_to_callback_args();
    }



    /**
     * adds nonce field html to meta box callback args
     */
    protected static function add_nonce_to_callback_args(){

        $nonce_action = 'meta_box_'.self::$meta_box_id;
        $nonce_name = 'nonce_'.self::$meta_box_id;
        $nonce_value = wp_create_nonce($nonce_action);

        $nonce_html =  Field::render_field(array(
            'name'  => $nonce_name,
            'value' => $nonce_value,
            'type'  => 'hidden'
        ));

        self::set_meta_box_callback_args(self::$meta_box_id,$nonce_html);
    }


    public static function get_meta_boxes(){
        return self::$meta_boxes;
    }

    public static function get_post_metas(){
        return self::$post_metas;
    }

    public static function get_meta_box_callback_args($meta_box_key){
        return self::$meta_boxes[$meta_box_key]['callback_args'];
    }

    protected static function set_post_meta($key, $input){
        self::$post_metas[$key] = $input;
    }

    protected static function set_meta_box($key,$args){
        self::$meta_boxes[$key] = $args;
    }

    protected static function set_meta_box_callback_args($meta_box_id,$value){
        self::$meta_boxes[$meta_box_id]['callback_args'][]= $value;
    }

}
