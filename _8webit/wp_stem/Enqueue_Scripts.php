<?php
namespace _8webit\wp_stem;

use Exception;

/**
 * Responsible for load scripts and style in user or admin pages.
 * to enqueue script or file to users pages use "Enqueue_Scripts::enqueue('your_file_name.filetype')".
 * to enqueue in admin pages use "Enqueue_Scripts::admin_enqueue('your_file_name.filetype')".
 *
 * script should locate in $your_theme_dir/assets/js
 * styles should locate in $your_theme_dir/assets/css
 *
 * @see Enqueue_Scripts::$dir
 *
 * @since 1.0.0
 *
 */
Class Enqueue_Scripts{
    use Instance;

    private static $asset_uri = 'vendor/8webit/wp_stem/_8webit/wp_stem/assets';

    // default folder where styles and scripts is located in own folders(javascript in 'js' and css in 'css')
    private static $dir = '/assets';

    private static $user_scripts = array();
    private static $user_styles  = array();

    private static $admin_scripts = array();
    private static $admin_styles = array();

    private static $specific_pages = array();

    private static $admin_last_script_state = '';

    private function __construct(){}

    /**
     *  Registers hooks to enqueue registered scripts
     * @since 1.0.0
     */
    public static function init()
    {
        add_action('wp_enqueue_scripts', array(Enqueue_Scripts::get_instance(), 'load_user_assets'));

        if(current_user_can('edit_posts')){
            add_action('admin_enqueue_scripts',array(Enqueue_Scripts::get_instance(),'load_admin_assets'));
        }
    }

    /**
     * Enqueues scripts and styles to users pages from assets folder.
     */
    public function load_user_assets()
    {
        $styles = Enqueue_Scripts::get_user_styles();
        $scripts = Enqueue_Scripts::get_user_scripts();

        $this->wp_enqueue_media_handler($styles,$scripts);
    }

    /**
     * Enqueues scripts and styles to admin pages from assets folder.
     * @since 1.0.0
     */
    public function load_admin_assets(){
        $styles = Enqueue_Scripts::get_admin_styles();
        $scripts = Enqueue_Scripts::get_admin_scripts();
        $specific_pages = self::get_specific_pages();
    
        if(!empty($specific_pages)){
            $wp_screen = get_current_screen();

            foreach($specific_pages as $filename => $pages){
                if(array_search($wp_screen->id,$pages) === false){
                    unset($scripts[$filename]);
                }
            }
        }
        $this->wp_enqueue_media_handler($styles,$scripts);
    }

    /**
     * just wrapper for wp_enqueue_style and wp_enqueue_script
     *
     * @since 1.0.0
     *
     * @param array $styles
     * @param array $scripts
     */
    public function wp_enqueue_media_handler($styles=array(), $scripts=array()){
        if (!empty($styles)) {
            foreach ($styles as $style) {
                if(wp_script_is($style,'enqueued')){
                    continue;
                }

                wp_enqueue_style($style['filename'], $style['src'], $style['deps'], $style['ver'], $style['in_footer']);
            }
        }

        if (!empty($scripts)) {
            foreach ($scripts as $script) {
                if(wp_script_is($script,'enqueued')){
                    continue;
                }

	            wp_enqueue_script($script['filename'], $script['src'], $script['deps'], $script['ver'], $script['in_footer']);
            }
        }
    }

    /**
     * Enqueue script or style by filename with its extension for USER pages.
     * if src not provided default file structure pattern("assets/[file_type]/[file_name].[file_type]") will be used.
     * uses same parameters as  wp_enqueue_script or wp_enqueue_style.
     *
     * @see wp_enqueue_script
     * @see wp_enqueue_style
     *
     * @since 1.0.0
     *
     * @param $filename (Required)
     * @param string $src
     * @param array $deps
     * @param bool $ver
     * @param bool $in_footer
     * @return Enqueue_Scripts
     */
    public static function enqueue($filename, $src = '', $in_footer = false, $deps = array(), $ver = false)
    {
        return self::enqueue_handler(true,$filename, $src, $in_footer, $deps, $ver);
    }

    /**
     * Enqueue script or style by filename with its extension for ADMIN pages.
     * if src not provided default file structure pattern("assets/[file_type]/[file_name].[file_type]") will be used.
     * uses same parameters as  wp_enqueue_script or wp_enqueue_style.
     *
     * @see wp_enqueue_script
     * @see wp_enqueue_style
     *
     * @since 1.0.0
     *
     * @param $filename (Required)
     * @param string $src
     * @param array $deps
     * @param bool $ver
     * @param bool $in_footer
     * @return Enqueue_Scripts
     */
    public static function admin($filename, $src = '', $in_footer = false, $deps = array(), $ver = false){
        self::set_admin_state($filename);
        return self::enqueue_handler(false,$filename, $src, $in_footer, $deps, $ver);
    }

    /**
     * Enqueue script or style by filename with its extension for USER or ADMIN pages.
     * uses same parameters as  wp_enqueue_script or wp_enqueue_style.
     *
     * @since 1.0.0
     *
     * @param bool $for_frontend
     * @param $filename
     * @param string $src
     * @param bool $in_footer
     * @param array $deps
     * @param bool $ver
     * @return Enqueue_Scripts | null
     */
    private static function enqueue_handler($for_frontend=true, $filename, $src = '', $in_footer = false, $deps = array(), $ver = false){
        try {
            $flag = empty($src)
	            ? Enqueue_Scripts::automated_file_directory($filename)
                : get_stylesheet_directory() .'/'. $src .'/'. $filename;

            if (file_exists($flag) || filter_var($src, FILTER_VALIDATE_URL) === true) {
                $tmp = explode('.', $filename);
                $filetype = end($tmp);

                if(filter_var($src, FILTER_VALIDATE_URL) === false){
                    $src = empty($src)
                        ? self::automated_file_uri($filename)
                        : get_stylesheet_directory_uri() .'/'. $src .'/'. $filename;
                }

                if ($filetype == 'js') {
                    if($for_frontend) {
                        self::frontend_scripts_push(array(
                            'filename' => $filename,
                            'src' => $src,
                            'in_footer' => $in_footer,
                            'deps' => $deps,
                            'ver' => $ver
                        ));
                    }else{
                        self::admin_scripts_push(array(
                            'filename' => $filename,
                            'src' => $src,
                            'in_footer' => $in_footer,
                            'deps' => $deps,
                            'ver' => $ver
                        ));
                    }
                } else if ($filetype == 'css') {
                    if($for_frontend) {
                        self::frontend_styles_push(array(
                            'filename' => $filename,
                            'src' => $src,
                            'in_footer' => $in_footer,
                            'deps' => $deps,
                            'ver' => $ver
                        ));
                    }else{
                        self::admin_styles_push(array(
                            'filename' => $filename,
                            'src' => $src,
                            'in_footer' => $in_footer,
                            'deps' => $deps,
                            'ver' => $ver
                        ));
                    }
                }

                return Enqueue_Scripts::get_instance();
            } else {
                $message = $filename . '  was not loaded properly.';
                $message .= "\n file wasn't found in " . $src; 
                throw new Exception($message);
            }
        } catch (Exception $ex) {
            Enqueue_Scripts::execption_message($ex);
        }

        return null;
    }


    /**
     * Enqueue js from cdn for admin pages
     * 
     * @since 1.0.0
     *
     * @param string $handle
     * @param string $src
     * @param boolean $in_footer
     * @param array $deps
     * @param boolean $ver
     * @return void
     */
    public static function admin_js_cdn($handle, $src = '', $in_footer = false, $deps = array(), $ver = false){
        self::admin_scripts_push(array(
            'filename' => $handle,
            'src' => $src,
            'in_footer' => $in_footer,
            'deps' => $deps,
            'ver' => $ver
        ));
    }

    /**
     * Enqueue style from cdn for admin pages
     * 
     * @since 1.0.0
     *
     * @param string $handle
     * @param string $src
     * @param boolean $in_footer
     * @param array $deps
     * @param boolean $ver
     * @return void
     */
    public static function admin_css_cdn($handle, $src = '', $in_footer = false, $deps = array(), $ver = false){
        self::admin_styles_push(array(
            'filename' => $handle,
            'src' => $src,
            'in_footer' => $in_footer,
            'deps' => $deps,
            'ver' => $ver
        ));
    }

    /**
     * Enqueue js from cdn for frontend
     * 
     * @since 1.0.0
     *
     * @param string $handle
     * @param string $src
     * @param boolean $in_footer
     * @param array $deps
     * @param boolean $ver
     * @return void
     */
    public static function frontend_js_cdn($handle, $src = '', $in_footer = false, $deps = array(), $ver = false){
        self::frontend_scripts_push(array(
            'filename' => $handle,
            'src' => $src,
            'in_footer' => $in_footer,
            'deps' => $deps,
            'ver' => $ver
        ));
    }

       /**
     * Enqueue css from cdn for frontend
     * 
     * @since 1.0.0
     *
     * @param string $handle
     * @param string $src
     * @param boolean $in_footer
     * @param array $deps
     * @param boolean $ver
     * @return void
     */
    public static function frontend_css_cdn($handle, $src = '', $in_footer = false, $deps = array(), $ver = false){
        self::frontend_styles_push(array(
            'filename' => $handle,
            'src' => $src,
            'in_footer' => $in_footer,
            'deps' => $deps,
            'ver' => $ver
        ));
    }

    /**
     * loads enqueued script for only specific page by wp screen id
     *
     * @since 1.0.0
     *
     * @param array $wp_screen_ids
     * @return Enqueue_Scripts
     */
    public static function admin_specific($wp_screen_ids = array()){
        $filename = self::get_admin_last_script_state();
        $specifics = self::get_specific_pages();

        if(!isset($specifics[$filename])){
            $result = $wp_screen_ids;
        }else{
            $result = array_unique(array_merge($specifics[$filename],$wp_screen_ids), SORT_REGULAR);
        }

        self::push_specific_pages($filename,$result);
        
        return Enqueue_Scripts::get_instance();
    }

    /**
     * @since 1.0.0
     * @return array
     */
    public static function get_specific_pages(){
        return self::$specific_pages;
    }

    /**
     * @since 1.0.0
     * @param $filename
     * @param array $value
     */
    public static function push_specific_pages($filename,$value=array()){
        self::$specific_pages[$filename] = $value;
    }

    /**
     * @since 1.0.0
     * @param $args
     */
    private static function frontend_scripts_push($args)
    {
        self::$user_scripts[] = $args;
    }

    /**
     * @since 1.0.0
     * @param $args
     */
    private static function frontend_styles_push($args)
    {
        self::$user_styles[] = $args;
    }

    /**
     * gets enqueued scripts
     *
     * @since 1.0.0
     *
     * @return array
     */
    public static function get_user_scripts()
    {
        return self::$user_scripts;
    }

    /**
     * gets enqueued styles
     *
     * @since 1.0.0
     *
     * @return array
     */
    public static function get_user_styles()
    {
        return self::$user_styles;
    }

    /**
     * @since 1.0.0
     *
     * @param array $args
     */
    private static function admin_scripts_push($args=array())
    {
        self::$admin_scripts[$args['filename']] = $args;
    }

    /**
     * @since 1.0.0
     * @param array $args
     */
    private static function admin_styles_push($args=array())
    {
        self::$admin_styles[$args['filename']] = $args;
    }

    /**
     * @since 1.0.0
     * @return array
     */
    private static function get_admin_scripts(){
        return self::$admin_scripts;
    }

    /**
     * @since 1.0.0
     * @return array
     */
    private static  function  get_admin_styles(){
        return self::$admin_styles;
    }

    /**
     * @since 1.0.0
     * @param $state
     */
    private static function set_admin_state($state){
        self::$admin_last_script_state = $state;
    }

    /**
     * @since 1.0.0
     * @return string
     */
    private static function get_admin_last_script_state(){
        return self::$admin_last_script_state;
    }

    /**
     * gets folder direcotry relative to $dir based on filename.
     * filename also must have file exsention.
     * used for automated loading and to get folder where
     * file should live.
     *
     * @since 1.0.0
     *
     * @param $filename
     * @return string
     */
    public static function automated_file_directory($filename)
    {
        $tmp = explode('.', $filename);
        $filetype = end($tmp);

        return get_template_directory() .self::$dir .'/' .$filetype .'/' .$filename;

    }

    /**
     * @since 1.0.0
     * @param $filename
     * @return string
     */
    public static function automated_file_uri($filename){
        $tmp = explode('.', $filename);
        $filetype = end($tmp);

        return get_template_directory_uri() . self::$dir .'/' .$filetype .'/' .$filename;
    }

    /**
     * sets the assets directory
     *
     * @since 1.0.0
     *
     * @param string $directory
     */
    public static function set_assets_dir($directory)
    {
        self::$dir = $directory;
    }

    /**
     * returns single or multiplay full file path with given pattern. Useful when you need load hashed script or style
     *
     * @since 1.0.0
     *
     * @param string $pattern
     * @return string | array | bool;
     */
    public static function  uri($pattern)
    {
        try {
            $path = get_template_directory(); 

            $result = glob($path . '/' . $pattern);

            if (count($result) > 0) {
                foreach ($result as $key => $value) {
                    $result[$key] = get_template_directory_uri() . str_replace(get_template_directory(), '', $value);
                }
            }

            if (count($result) === 1) {
                return $result[0];
            }

            if(count($result) == 0){
                throw  new Exception($pattern .' - No file found with given pattern');
            }

            return $result;
        }catch (Exception $ex){
            Enqueue_Scripts::execption_message($ex);
        }
        return false;
    }

    /**
     * @since 1.0.0
     * @return string
     */
    public static function get_css_uri(){
        return self::$asset_uri .'/css';
    }

    /**
     * @since 1.0.0
     * @return string
     */
    public static function get_js_uri(){
        return self::$asset_uri .'/js';
    }

    /**
     * @since 1.0.0
     * @param $ex
     */
    private static function execption_message($ex){
        if( defined('WP_DEBUG') && true === WP_DEBUG){
            echo '<pre style="color:red;font-size:1.5em;">' . $ex->getMessage() . '</pre>';
            echo '<h3> Stack Trace </h3>';
            echo '<pre>';
            echo $ex->getTraceAsString();
            echo '</pre>';
            wp_die();
        }else{
            wp_die('Something Went Terribly Wrong :(');
        }
    }
}
