<?php

/*
Plugin Name: Custom Search Filter
Description: Custom Search Filter(CSF) plugin purpose to make post type filter easier by providing form and condition with shortcode to display in archive page.
Plugin URI: https://github.com/santoshtmp/custom-search-filter
Version: 1.0.0
Author: santoshtmp
Author URI: https://github.com/santoshtmp
Requires WP: 6.5
Requires PHP: 7.4
Domain Path: languages
Text Domain: custom-search-filter
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!defined('csf_path')) {
    define('csf_path', plugin_dir_url(__FILE__));
}
if (!defined('csf_dir')) {
    define('csf_dir', plugin_dir_path(__FILE__));
}

require_once plugin_dir_path(__FILE__) . 'classes/helper.php';
require_once plugin_dir_path(__FILE__) . 'classes/search_filter_post_type.php';
require_once plugin_dir_path(__FILE__) . 'classes/search_filter_query.php';
require_once plugin_dir_path(__FILE__) . 'classes/shortcode_customsearchfilter.php';
require_once plugin_dir_path(__FILE__) . 'lib/ajax-api.php';
require_once plugin_dir_path(__FILE__) . 'lib/enqueue_scripts.php';
