<?php

/**
 * =========================================
 * Plugin Name: CSF - Search Filter 
 * Description: A plugin for Search filter to generate form and query the form, usedfull for deeveloper. 
 * Plugin URI: https://github.com/santoshtmp/csf-search-filter
 * Version: 1.0
 * Author: santoshtmp
 * =======================================
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
require_once csf_dir . 'classes/csf_admin_setting.php';
require_once csf_dir . 'classes/csf_data.php';
require_once csf_dir . 'classes/csf_fields.php';
require_once csf_dir . 'classes/csf_form.php';
require_once csf_dir . 'classes/csf_query.php';
require_once csf_dir . 'classes/enqueue_script.php';
require_once csf_dir . 'classes/shortcode.php';

// execute the csf .
$search_filter_query = new \csf_search_filter\CSF_Query();
$csf_table = new \csf_search_filter\CSF_Admin_setting();
$csf_table = new \csf_search_filter\CSF_Data();
$csf_shortcode = new \csf_search_filter\CSF_shortcode();


