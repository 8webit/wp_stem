<?php

namespace _8webit\wp_stem;

class Google_Fonts{
    public static function link($option){
        $base_url = 'https://fonts.googleapis.com/css?family=';
        
        $link = $base_url. str_replace(' ','+',$option['family']);
        $link .= ':'. $option['variant'];
        $link .= '&amp;subset='. $option['subset'];

        return $link;
    }

    public static function links($fonts){
        $base_url = 'https://fonts.googleapis.com/css?family=';
        $query = '';

        for($i=0; $i < count($fonts); $i++){
            if( empty($fonts[$i])
                || empty($fonts[$i]['family'])
                || empty($fonts[$i]['variant'])
                || empty($fonts[$i]['subset'])
                || empty($fonts[$i]['size']) ){
                    continue;
            }
                
            $variants = '';
            $query .= str_replace(' ', '+', $fonts[$i]['family']);

            $variant_keys = array_keys(array_column($fonts, 'family'), $fonts[$i]['family']);

            if(count($variant_keys) > 1){
                $itersected_fonts =  array_intersect_key($fonts, array_flip($variant_keys));

                $end = end($variant_keys);
                foreach($itersected_fonts as $key => $itersected_font){
                    $variant = (string)Google_fonts::sanitize_variant($itersected_font['variant']);

                    if(strpos($variants,$variant) === false){
                        $variants .= $variant;
                        $variants .= $key !== $end ? ',' : '';
                    }
                }

                $variants = rtrim($variants, ',');
            }else{
                $variants = Google_fonts::sanitize_variant($fonts[$i]['variant']);
            }

            $fonts = array_diff_key($fonts, array_flip($variant_keys));
            
            $query .= ':'.$variants;
            $query .= count($fonts) > 1 && count($fonts) - 1 !== $i ? '|' : '';
        }
        
        $query = rtrim($query, '|');

        return $base_url . $query;
    }

    public static function style($option){
        if(empty($option)){
            return;
        }
        
        if(!empty($option['variant'])){        
            switch($option['variant']){
                case 'regular':
                    $option['variant'] = 400;
                    break;
                case '100italic':
                case '200italic':
                case '300italic':
                case '400italic':
                case '500italic':
                case '600italic':
                case '700italic':
                case '800italic':
                case '900italic':
                    $option['variant'] = filter_var($option['variant'], FILTER_SANITIZE_NUMBER_INT);
                    echo 'font-style: italic;';
                    break;
            }
            echo 'font-weight:' .$option['variant'] .';';
        }

        if(!empty($option['family'])){
            echo "font-family:'" .$option['family']. "', serif;";
        }
        if(!empty($option['size'])){
            echo 'font-size:' .$option['size']. 'px;';
        }

    }

    public static function register_setting($option_group, $option_name){
        register_setting($option_group, $option_name.'_family');
        register_setting($option_group, $option_name.'_variant');
        register_setting($option_group, $option_name.'_subset');
        register_setting($option_group, $option_name.'_size');
    }
    
    public static function get_option($option_name, $default_value=false){
        $result = array();
        
        if($default_value){
            $result['family'] = get_option($option_name.'_family', $default_value['family']);
            $result['variant'] = get_option($option_name.'_variant', $default_value['variant']);
            $result['subset'] = get_option($option_name.'_subset', $default_value['subset']);
            $result['size'] = get_option($option_name.'_size', $default_value['size']);
        }{
            $result['family'] = get_option($option_name.'_family');
            $result['variant'] = get_option($option_name.'_variant');
            $result['subset'] = get_option($option_name.'_subset');
            $result['size'] = get_option($option_name.'_size');
        }
    
        return $result;
    }


    private static function sanitize_variant($variant){
        $result = '';

        switch($variant){
            case 'regular':
                $result = 400;
                break;
            case '100italic':
            case '200italic':
            case '300italic':
            case '400italic':
            case '500italic':
            case '600italic':
            case '700italic':
            case '800italic':
            case '900italic':
                $result = filter_var($variant, FILTER_SANITIZE_NUMBER_INT).'i';
                break;
            default:
                $result = $variant;
                break;
        }

        return $result;
    }
}