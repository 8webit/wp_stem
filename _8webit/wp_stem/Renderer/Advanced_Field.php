<?php
namespace _8webit\wp_stem\Renderer;

use _8webit\wp_stem\Enqueue_Scripts;

/**
 * Responsible for rendering normal fields,
 * wp_media, color picker and font picker fields
 *
 * @since 1.0.0
 *
 * @package _8webit\stem
 */
class Advanced_Field extends Field
{
    protected function __construct() {}

    /**
     * renders single,group cloneable or normal field.
     * automatically enqueues js for cloneable fields for
     * front end handling when editing from admin pages.
     *
     * @since 1.0.0
     *
     * @param array $args single field to render
     * @return string HTML
     */
    public static function render($args=array()){
        $is_single_cloneable = isset($args['cloneable']);
        $is_group_cloneable = isset($args['options']) && isset($args['fields']);

        $html = '<div class="meta-box-field">';

        if($is_single_cloneable) {
            $options = $args['options'];

            unset($args['options']);
            unset($args['cloneable']);

            $html .= Cloneable::single($args,$options);
        } else if($is_group_cloneable){
            $html .= Cloneable::group($args);
        } else{
            Enqueue_Scripts::admin(
                'autocomplete.js',
                Enqueue_Scripts::get_js_uri(),
                true,
                array('jquery-ui-autocomplete')
            );

            $args['value'] = is_array($args['value']) && isset($args['value'][0]) ? $args['value'][0] : $args['value'];
            $html .= self::render_field($args);
        }

        $html .= '</div>'; // end of .metabox-field

        return $html;
    }

    /**
     * generates any single field html for post meta
     *
     * @since 1.0.0
     *
     * @param array $field
     * @return string HTML
     */
    public static function render_field($field)
    {
        $html = '';

        $html .= '<div class="field-group">';
        switch($field['type']){
            case 'wp_media':
                $html .= self::wp_media( $field['name'], $field['value'], $field['label'] );
                break;
            case 'color':
                $html .= self::spectrum( $field['name'], $field['value'], $field['id'], $field['label'] );
                break;
	        case 'font':
	        	$html .= self::font($field);
	        	break;
            default:
                var_dump($field);
                $html .= Field::render_field($field);
                break;
        }
        $html .= '</div>';

        return $html;
    }

    /**
     *  Renders wp_media file uploader field.
     *
     * @since 1.0.0
     *
     * @param $name
     * @param string $value
     * @param string $field_label
     * @param string $upload_label
     * @param string $remove_label
     * @param bool $show_image
     * @return string
     */
	public static function wp_media( $name, $value = '', $field_label = '', $upload_label = 'Upload Media', $remove_label = 'Remove Selected Media', $show_image = true ) {
        Enqueue_Scripts::admin(
            'wp_media.js',
            Enqueue_Scripts::get_js_uri(),
            array('jquery','media-upload','thickbox')
        );

        $html = '';

		$attachmend_id = ! empty( $value ) ? $value
			: get_post_meta( get_the_ID(), $name, true );

		$attachment_src = get_attached_file( $attachmend_id );
		$filename       = basename( $attachment_src );
		$img_src        = wp_get_attachment_image_src( $attachmend_id, 'full' );

		$show_image_class = $show_image ? ' media_show_image' : '';

		$hide_upload_link = ! empty( $attachment_src ) ? 'hidden' : '';
		$hide_remove_link = $hide_upload_link == 'hidden' ? '' : 'hidden';

		$html .= ! empty( $field_label ) ? Field::label( $name, $field_label ) : '';

		$html .= '<div class="field-wrapper stem-media-uploader">';
		$html .= '<div class="media-data' . $show_image_class . '">'; // container of uploaded image

		if ( $hide_upload_link == 'hidden' ) {
			if ( $img_src && $show_image ) {
				$html .= '<img src="' . $img_src[0] . '" alt="" />';
			}
			$html .= '<p>' . $filename . '</p>';
		}

		$html .= '</div>';// end of stem-media-uploader"
		$html .= '<div class="hide-if-no-js">';
		$html .= '<a class="button stem-upload-wp-media ' . $hide_upload_link . '"  href="javascript:void(0)" >';
		$html .= $upload_label;
		$html .= '</a>';// end of stem-upload-wp-media
		$html .= '<a class="button stem-remove-wp-media ' . $hide_remove_link . '" href="javascript:void(0)" >';
		$html .= $remove_label;
		$html .= '</a>'; // end of stem-remove-wp-media
		$html .= '</div>'; // end of button wrappers
		$html .= '<input id="' . $name . '" name="' . $name . '" class="stem-wp-media" type="hidden" value="' . esc_attr( $attachmend_id ) . '" />';
		$html .= '</div>'; // end of container


		return $html;
    }
    /**
     * renders a color picker.
     *
     * @since 1.0.0
     *
     * @param string $name
     * @param string $value
     * @param string string $id
     * @param string $label
     * @return string   HTML
 */
	public static function spectrum( $name, $value, $id = '', $label = '' ) {
        //JS
        Enqueue_Scripts::admin(
            'init_spectrum.js',
            Enqueue_Scripts::get_js_uri()
        );

        // Dependencies
        Enqueue_Scripts::admin_css_cdn(
            'spectrum.css',
            'https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.css'
        );
        Enqueue_Scripts::admin_js_cdn(
            'spectrum.js',
            'https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js'
        );

		$html = Field::render_field(
		    array(
			'id'    => !empty($id) ? $id : $name .'_id',
			'class' => 'spectrum',
			'name'  => $name,
			'value' => $value,
			'label' => $label
            )
        );

		return $html;
	}

    /**
     * renders font field
     *
     * @since 1.0.0
     *
     * @param array $field
     * @return string
     */
	public static function font($field = array()){
        Enqueue_Scripts::admin(
            'google_fonts.js',
            Enqueue_Scripts::get_js_uri(),
            true,
            array('jquery','jquery-ui-autocomplete')
        );

        $label = $field['label'];

		$button = '<button class="button wp_stem-font-picker" >';
		$button .= 'Select Google Font...';
		$button .= '</button>';

		$field['id'] = isset($field['id']) ? $field['id'] : $field['name'] . '_id';
			
		unset($field['label']);
		
		$family = Field::input(array(
			'class'       => 'wp_stem-google-fonts-family',
			'type'        => 'text',
			'placeholder' => 'Font Family',
			'value'       => isset($field['value']['family']) ? $field['value']['family'] : '',
			'name'        => $field['name'] .'_family',
			'autocomplete' => 'off'
			),false);

		$variants = Field::select(array(
			'class'       => 'wp_stem-google-fonts-variants',
			'value'       => isset($field['value']['variant']) ? $field['value']['variant'] : '',
			'name'        => $field['name'] .'_variant'
		));

		$subsets = Field::select(array(
			'class'       => 'wp_stem-google-fonts-subsets',
			'value'       => isset($field['value']['subset']) ? $field['value']['subset'] : '',
			'name'        => $field['name'] . '_subset'
		));

		$font_size = Field::input(array(
			'class'       => 'wp_stem-google-fonts-size',
			'type'        => 'number',
			'value'       => isset($field['value']['size']) ? $field['value']['size'] : 16,
			'name'        => $field['name'] .'_size'
		),false);

		$result = $button;
		$result .= '<div class="wp_stem-font-picker-content">';
		$result .= $family;
		$result .= $variants;
		$result .= $subsets;
		$result .= $font_size;
		$result .= '</div>';

		$html  = Field::label($field['id'],$label);
		$html .= Field::wrapper($result);

		return $html;
	}
}
