<?php 
namespace _8webit\wp_stem;

class Stem{
    public static function init(){
        Enqueue_Scripts::init();
        PostType::init();
        Save_Post_Hook::init();

        Enqueue_Scripts::admin(
            'admin.css',
            Enqueue_Scripts::get_css_uri()
        );
    }
}