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

class CSF_Enqueue
{
    // 
    public static function csf_search_js($form_id = 'csf-filter-form', $result_area_id = 'csf-result-area')
    {
        $js_file_path = csf_path_url . 'assets/js/csf-search-filter.js';
        wp_enqueue_script(
            'csf-filter',
            $js_file_path,
            array('jquery'),
            filemtime(get_stylesheet_directory($js_file_path)),
            array(
                'in_footer' => true,
                'strategy' => 'defer'
            )
        );
        wp_localize_script('csf-filter', 'csf_obj', [
            'form_id' => $form_id,
            'result_area_id' => $result_area_id
        ]);
    }

    public static function csf_admin_setting_js()
    {
        $js_file_path = csf_path_url . 'assets/js/csf_admin_settings.js';

        wp_enqueue_script(
            'csf_admin_settings',
            $js_file_path,
            array('jquery'),
            filemtime(get_stylesheet_directory($js_file_path)),
            array(
                'in_footer' => true,
                'strategy' => 'defer'
            )
        );


        $default_search_fields = \csf_search_filter\CSF_Fields::set_search_fields(true);
        $default_cache_metadata_fields = \csf_search_filter\CSF_Fields::set_csf_cache_metadata_fields(true);
        wp_localize_script('csf_admin_settings', 'csf_obj', array(
            'default_search_fields' => ($default_search_fields) ? json_encode($default_search_fields) : '',
            'default_cache_metadata_fields' => ($default_cache_metadata_fields) ? json_encode($default_cache_metadata_fields) : '',
        ));
    }

    // public function admin_csf_search_filter_scripts()
    // {
    //     // check post title
    //     global $pagenow,  $post_type;
    //     $js_file_path = csf_path_url . 'assets/js/form-settings-metabox.js';
    //     $seach_filter_post_type = \csf_search_filter\Search_Filter_Post_Type::get_seach_filter_post_type();
    //     if ($post_type === $seach_filter_post_type and ($pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'edit.php')) {
    //         wp_enqueue_script(
    //             'search-filter-form-settings-metabox',
    //             $js_file_path,
    //             array('jquery'),
    //             filemtime(get_stylesheet_directory($js_file_path)),
    //             array(
    //                 'in_footer' => true,
    //                 'strategy' => 'defer'
    //             )
    //         );
    //         // Pass the AJAX URL and nonce to the script
    //         wp_localize_script('search-filter-form-settings-metabox', 'csf_obj', array(
    //             'ajaxUrl' => admin_url('admin-ajax.php'),
    //             'nonce' => wp_create_nonce('csf_add_data_field_wrapper')
    //         ));
    //     }
    // }
    // add_action('admin_enqueue_scripts', 'admin_csf_search_filter_scripts');
}