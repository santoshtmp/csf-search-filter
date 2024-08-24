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
                'id' => 0,
                'result_show' => "false",
            ),
            $atts
        );
        $search_filter_id = (int)$atts['id'];
        $result_show = $atts['result_show'];
        if (!$search_filter_id) {
            return "csf_searchfilter id is required";
        }
        if (!get_post($search_filter_id)) {
            return "csf_searchfilter id is invalid";
        }

        $settings = [];
        $result_area_id = '';
        if ($result_show === 'true') {
            $this->display_shortcode_csf_searchfilter_result($result_area_id, $settings);
            return '';
        }

        // Generate the form shortcode output
        ob_start();
?>
        <div class="form">
            loading under development
        </div>
<?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }



    // 
    public function display_shortcode_csf_searchfilter_result($result_area_id, $settings)
    {
        $template_path = isset($settings['search_filter_result_template']) ? $settings['search_filter_result_template'] : '';
        if (empty($template_path) || $template_path == '') {
            $template_path = csf_dir . 'includes/csf-result-template.php';
        } else {
            $template_path = get_template_directory() . '/' . $template_path;
        }
        if ($template_path) {
            $template_path = ltrim($template_path, '/');
            echo '<div class="csf-search-result" data-region="csf-search-filter-result"> ';
            echo '<div id="' . $result_area_id . '"> ';
            if (file_exists($template_path)) {
                include $template_path;
            } else {
                $load_template = get_template_part($template_path);
                if ($load_template === false) {
                    echo "invalid search filter result template.";
                }
            }
            echo "</div> ";
            echo "</div> ";
        }
    }

    // class end
}
