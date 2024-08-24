<?php

/**
 * =========================================
 * Plugin Name: CSF - Search Filter library
 * Description: A plugin for search filter to generate form and query the form, usedfull for deeveloper. 
 * Plugin URI: https://github.com/santoshtmp
 * Version: 1.0
 * Author: santoshtmp
 * =======================================
 */

namespace csf_search_filter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
class Helper
{

    //
    // public static function load_search_add_field($post_type, $index_field = 0, $settings = [])
    // {
    //     if (!$post_type) {
    //         return '';
    //     }
    //     if (empty($settings)) {
    //         $default_field = [
    //             'search_field_data' => 'taxonomy',
    //             'search_field_taxonomy' => '',
    //             'search_field_metadata' => '',
    //             'search_field_type' => 'dropdown',
    //             'display_count' => 1,
    //         ];
    //         $settings = $default_field;
    //     }
    //     ob_start();
    //     include plugin_dir_path(dirname(__FILE__)) . 'includes/admin-search-add-fields.php';
    //     $output = ob_get_contents();
    //     ob_end_clean();
    //     return $output;
    // }
}
