<?php


/**
 * =========================================
 * Plugin Name: CSF - Search Filter library
 * Description: A plugin for search filter to generate form and query the form, usedfull for deeveloper. 
 * Version: 1.0
 * =======================================
 */

namespace csf_search_filter;

use stdClass;
use WP_Query;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class CSF_shortcode
{
    public function __construct()
    {
        add_shortcode('csf_searchfilter', [$this, 'display_shortcode_csf_searchfilter']);
    }


    public function display_shortcode_csf_searchfilter($atts)
    {
        // Define default attributes
        $atts = shortcode_atts(
            array(
                'filter_name' => "",
                'form_id' => "",
                'form_class' => "",
                'post_type' => "",
                'data_url' => "",
                'result_show' => "false",
            ),
            $atts
        );
        $filter_name = $atts['filter_name'];
        $form_id = $atts['form_id'];
        $form_class = $atts['form_class'];
        $post_type = $atts['post_type'];
        $data_url = $atts['data_url'];
        $result_show = $atts['result_show'];
        $search_form = [
            "filter_name" => $filter_name,
            'form_id' => $form_id,
            'form_class' => $form_class,
            'post_type' => $post_type,
            'data_url' => $data_url,
        ];
        // 
        if (!$filter_name) {
            return "csf_searchfilter filter_name is required";
        }
        // 
        if ($result_show === 'true') {
            $search_fields = \csf_search_filter\CSF_Fields::set_search_fields();
            $fields_settings = (isset($search_fields[$filter_name])) ? $search_fields[$filter_name] : '';
            $this->display_shortcode_csf_searchfilter_result($filter_name, $fields_settings);
            return '';
        }
        ob_start();
        \csf_search_filter\CSF_Form::the_search_filter_form($search_form);
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }



    // 
    public function display_shortcode_csf_searchfilter_result($filter_name, $fields_settings)
    {
        $result_area_id = "csf-result-area-" . str_replace([' '], '-', strtolower($filter_name));
        if ($filter_name == get_post_type()) {
            global $wp_query;
            if (! isset($wp_query)) {
                return false;
            }
            $csf_query = $wp_query;
        } else {
            $csf_query = new \csf_search_filter\CSF_Query();
            $query_args = new CSF_Args();
            $csf_query->csf_query($fields_settings, $query_args, true);            
            $csf_query = new WP_Query($query_args->getAll());
        }
        $template_path = isset($fields_settings['result_template']) ? $fields_settings['result_template'] : '';
        if (empty($template_path) || $template_path == '') {
            $template_path = csf_dir . 'includes/csf-result-loop-template.php';
        } else {
            $template_path = get_stylesheet_directory() . '/' . $template_path;
        }
        if ($template_path) {
            $template_path = ltrim($template_path, '/');
            echo '<div class="csf-search-result" data-region="csf-search-filter-result"> ';
            echo '<div id="' . $result_area_id . '"> ';
            if (file_exists($template_path)) {
                include $template_path;
            } else {
                // $load_template = get_template_part($template_path);
                echo "invalid search filter result template.";
            }
            echo "</div> ";
            echo "</div> ";
        }
        wp_reset_postdata();
    }

    // class end
}
