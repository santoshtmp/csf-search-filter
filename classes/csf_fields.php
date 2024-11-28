<?php

/**
 * =========================================
 * Plugin Name: CSF - Search Filter library
 * Description: A plugin for search filter to generate form and query the form, usedfull for deeveloper. 
 * Version: 1.0
 * =======================================
 */

namespace csf_search_filter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Here we define or set the search filter fields/settings
class CSF_Fields
{

    // 
    public static function set_search_fields()
    {
        $csf_set_search_fields = (get_option('csf_set_search_fields')) ?: '';
        if ($csf_set_search_fields) {
            return json_decode($csf_set_search_fields, true);
        }

        return self::set_search_fields_default();
    }

    /**
     * Each filter fields values has following options $fields['unique_filter_name']['fields']
     * display_name=>'Display name'
     * filter_term_type => 'taxonomy' or 'metadata'
     * filter_term_key => 'taxonomy_key' or 'metadata_key'; [if single meta_key has multiple metavalue in case of repeater metavalue:: example metakey_{array}_metakey]
     * metadata_reference => 'taxonomy,taxonomy_key,slug' or 'post' or 'function-name-as-defined'; only apply to filter_term_key metadata_key Where 'taxonomy,taxonomy_key,slug' third parameter 'slug' define that wp query will perform meta query on given value .
     * search_field_type => 'dropdown' or 'checkbox' or 'search_text'; there can only be one 'search_text' on each filter
     * placeholder => 'free text' ;only apply to search_field_type search_text
     * display_count => 1 or 0; default 1
     */
    //search_fields
    public static function set_search_fields_default()
    {
        // initially define the fields
        $fields = [];

        // Tools archive page Filter
        $tool_filter = [];
        $tool_filter_name = 'application-tool'; //filter name should be post type to query and filter by main wp query 
        $tool_filter['post_type']  = $tool_filter_name; // post type to filter
        // $tool_filter['taxonomies']  = 'tools-country'; // seperate the multiple taxonomy by (,) comma
        $tool_filter['is_main_query'] = true;
        $tool_filter['posts_per_page'] = 24; // post per page in post wq query result page
        $tool_filter['search_filter_title'] = ''; // Search filter title in the search form
        $tool_filter['field_relation'] = "AND";
        // $tool_filter['result_template'] = "";
        $tool_filter['fields_actions']  = [
            'auto_submit' => true,
            'submit_btn_show' => false,
            'submit_display_name' => 'Search',
            'reset_btn_show' => true,
            'reset_display_name' => 'Reset'
        ];
        $tool_filter['fields']  = [
            ['display_name' => '', 'search_field_type' => 'search_text', 'placeholder' => 'Search by keyword'],
            ['display_name' => 'Service Area', 'filter_term_type' => 'metadata', 'filter_term_key' => 'related_service_area','metadata_reference' => 'post', 'search_field_type' => 'checkbox'],
            // ['display_name' => 'Year', 'filter_term_type' => 'metadata', 'filter_term_key' => 'publication_year_only', 'search_field_type' => 'checkbox'],
            ['display_name' => ' Geographical Region', 'filter_term_type' => 'metadata', 'filter_term_key' => 'tools_country', 'metadata_reference' => 'taxonomy,tools-country', 'search_field_type' => 'checkbox',],
        ];
        // $tool_filter['free_search']['meta_keys'] = ['description', 'publication_year_only']; //applied only in "OR" relation
        // $tool_filter['free_search']['post_taxonomies'] = ['tools-country'];


        // return field settings
        $fields[$tool_filter_name]  = $tool_filter;


        return $fields;
    }
}
