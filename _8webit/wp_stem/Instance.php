<?php
namespace _8webit\wp_stem;

trait Instance{
    /**
     * current class instance
     * @since 1.0.0
     */
    private static  $instance = null;

    /**
     * get instance of current class
     * @since 1.0.0
     * @return Instance
     */
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}