<?php

/*

Plugin Name: CSF - Search Filter
Description: CSF - Search Filter library plugin purpose to make search filter easier for admin and developer by providing form and query it.
Plugin URI: https://github.com/santoshtmp/csf-search-filter
Tags: CSF, search, form, query
Version: 1.0
Author: santoshtmp
Author URI: https://github.com/santoshtmp
Requires WP: 6.0
Tested up to: 6.5
Requires PHP: 8.0
Domain Path: languages
Text Domain: csf-search-filter
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!defined('csf_path_url')) {
    define('csf_path_url', plugin_dir_url(__FILE__));
}
if (!defined('csf_dir')) {
    define('csf_dir', plugin_dir_path(__FILE__));
}

// require csf classes
require_once csf_dir . 'classes/_include.php';


// Hook into the plugin action links filter
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'csf_filter_add_settings_link');
function csf_filter_add_settings_link($links)
{
    // Create the settings link
    $settings_link = '<a href="options-general.php?page=search-filter-csf">Settings</a>';
    // Add the link to the beginning of the existing links array
    array_unshift($links, $settings_link);
    return $links;
}

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