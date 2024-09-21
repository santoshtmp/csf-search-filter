<?php

/**
 * =========================================
 * Plugin Name: CSF - Search Filter library
 * Description: A plugin for search filter to generate form and query the form, usedfull for deeveloper. 
 * Version: 1.0
 * =======================================
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!defined('csf_dir')) {
    define('csf_dir', plugin_dir_path(__FILE__));
    // define('csf_dir', dirname(__FILE__));
}

// require csf classes
require_once csf_dir . 'classes/csf_admin_setting.php';
require_once csf_dir . 'classes/csf_data.php';
require_once csf_dir . 'classes/csf_fields.php';
require_once csf_dir . 'classes/csf_form.php';
require_once csf_dir . 'classes/csf_query.php';
require_once csf_dir . 'classes/enqueue_script.php';
require_once csf_dir . 'classes/shortcode.php';

// csf main class
class CSF
{
    public function __construct()
    {
        // execute the csf .
        $search_filter_query = new \csf_search_filter\CSF_Query();
        $csf_table = new \csf_search_filter\CSF_Admin_setting();
        $csf_shortcode = new \csf_search_filter\CSF_shortcode();
    }
}
$csf = new CSF();



// Hook into the plugin action links filter
// add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'csf_filter_add_settings_link');
// function csf_filter_add_settings_link($links)
// {
//     // Create the settings link
//     $settings_link = '<a href="options-general.php?page=search-filter-csf">Settings</a>';
//     // Add the link to the beginning of the existing links array
//     array_unshift($links, $settings_link);
//     return $links;
// }

// /* Plugin Meta Links */
// add_filter('plugin_row_meta', 'csf_plugin_row_meta', 10, 2);
// function csf_plugin_row_meta($links, $file)
// {
//     if ('csf-search-filter/csf-search-filter.php' == $file) {
//         $row_meta = [
//             'View details' => '<a rel="noopener" href="https://github.com/santoshtmp/csf-search-filter"  target="_blank">' . esc_html__('Visit plugin site ', 'csf-search-filter') . '</a>',
//         ];
//         $links = array_merge($links, $row_meta);
//         return $links;
//     }
//     return (array)$links;
// }