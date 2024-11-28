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
        $fields = [];

        // For resource post type search filter
        $resource_filter = [];
        $resource_filter_name = 'resource'; //filter name should be post type to query and filter by main wp query 
        $resource_filter['post_type']  = $resource_filter_name; // post type to filter
        // $resource_filter['taxonomies']  = 'resource-rights'; // seperate the multiple taxonomy by (,) comma
        $resource_filter['is_main_query'] = true; // It may overried my others plugin or functions
        $resource_filter['posts_per_page'] = 24; // post per page in post wq query result page
        $resource_filter['search_filter_title'] = 'Filters'; // Search filter title in the search form
        $resource_filter['fields']  = [
            ['display_name' => '', 'search_field_type' => 'search_text', 'placeholder' => 'Search by keyword'],
            ['display_name' => 'Sector', 'filter_term_type' => 'metadata', 'filter_term_key' => 'sector', 'metadata_reference' => 'taxonomy,resource-sector', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Commodity', 'filter_term_type' => 'metadata', 'filter_term_key' => 'commodity_type', 'metadata_reference' => 'taxonomy,commodity-type', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Country', 'filter_term_type' => 'metadata', 'filter_term_key' => 'country_only', 'metadata_reference' => 'get_plghub_cache_all_unsd_countries', 'search_field_type' => 'checkbox'],
            // ['display_name' => 'Region', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_png_region_only', 'metadata_reference' => 'taxonomy,png-region,slug', 'search_field_type' => 'checkbox'],
            //  search in both or more then one metadata is possible only in extera function search is developed; not in wp query.
            ['display_name' => 'Region', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_png_region_only|region_unsd_region_only', 'metadata_reference' => 'taxonomy,png-region,slug|get_plghub_cache_all_unsd_region', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Province', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_png_region_province_only', 'metadata_reference' => 'taxonomy,png-region,slug', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Topic', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,topic', 'filter_term_key' => 'topic_sub_topic_list_{array}_topic_only', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Project', 'filter_term_type' => 'metadata', 'metadata_reference' => 'post', 'filter_term_key' => 'project_name', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Resource type', 'filter_term_type' => 'metadata', 'filter_term_key' => 'resource_type', 'metadata_reference' => 'taxonomy,resource-type', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Organisation', 'filter_term_type' => 'metadata', 'filter_term_key' => 'organisation', 'metadata_reference' => 'taxonomy,organisation', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Publisher', 'filter_term_type' => 'metadata', 'filter_term_key' => 'publisher', 'metadata_reference' => 'taxonomy,publisher', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Year', 'filter_term_type' => 'metadata', 'filter_term_key' => 'publication_year_only', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Format', 'filter_term_type' => 'metadata', 'filter_term_key' => 'format', 'metadata_reference' => 'taxonomy,format', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Rights', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,resource-rights', 'filter_term_key' => 'rights_license_type_rights_only', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Access', 'filter_term_type' => 'metadata', 'filter_term_key' => 'access', 'search_field_type' => 'checkbox'],
        ];
        $resource_filter['free_search']['meta_keys'] = ['description', 'publication_year_only']; //applied only in "OR" relation
        // $resource_filter['free_search']['post_taxonomies'] = ['keywords-tag', 'resource-type', 'commodity-type', 'resource-sector', 'format'];
        $resource_filter['field_relation'] = "AND";
        // $resource_filter['result_template'] = "";
        $resource_filter['fields_actions']  = [
            'auto_submit' => true,
            'submit_btn_show' => false,
            'submit_display_name' => 'Search',
            'reset_btn_show' => true,
            'reset_display_name' => 'Reset'
        ];
        // For project post type search filter
        $project_filter = [];
        $project_filter_name = 'project'; //filter name should be post type to query and filter by main wp query 
        $project_filter['is_main_query'] = true;
        $project_filter['post_type']  = $project_filter_name; // post type to filter
        $project_filter['posts_per_page'] = 12; // post per page in post archive wq query result page
        $project_filter['search_filter_title'] = 'Filters'; // Search filter title in the search form
        $project_filter['fields']  = [
            ['search_field_type' => 'search_text', 'placeholder' => 'Search with title or keywords.'],
            ['display_name' => 'Sector', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,resource-sector', 'filter_term_key' => 'sector', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Commodity', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,commodity-type', 'filter_term_key' => 'commodity_type', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Status', 'filter_term_type' => 'metadata', 'filter_term_key' => 'status', 'metadata_reference' => 'taxonomy,project-status', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Owner', 'filter_term_type' => 'metadata', 'filter_term_key' => 'owner_operator', 'metadata_reference' => 'taxonomy,owner-operator', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Region', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_province_png_region_only', 'metadata_reference' => 'taxonomy,png-region,slug', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Province', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_province_png_region_province_only', 'metadata_reference' => 'taxonomy,png-region,slug', 'search_field_type' => 'checkbox'],
        ];
        $project_filter['fields_actions']  = [
            'auto_submit' => true,
            'submit_btn_show' => false,
            'submit_display_name' => 'Search',
            'reset_btn_show' => true,
            'reset_display_name' => 'Reset'
        ];
        $project_filter['field_relation'] = "AND";

        // For map block search filter
        $map_block_filter = [];
        $map_block_filter_name = 'map_filter'; //filter name should be post type to query and filter by main wp query 
        $map_block_filter['post_type']  = 'project'; // post type to filter
        $map_block_filter['is_main_query'] = false;
        $map_block_filter['search_filter_title'] = 'Filter by'; // Search filter title in the search form
        $map_block_filter['fields']  = [
            ['display_name' => 'Sector', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,resource-sector', 'filter_term_key' => 'sector', 'search_field_type' => 'checkbox', 'display_count' => 0],
            ['display_name' => 'Commodity', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,commodity-type', 'filter_term_key' => 'commodity_type', 'search_field_type' => 'checkbox', 'display_count' => 0],
            ['display_name' => 'Region', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_province_png_region_only', 'metadata_reference' => 'taxonomy,png-region', 'search_field_type' => 'checkbox', 'display_count' => 0],
            ['display_name' => 'Province', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_province_png_region_province_only', 'metadata_reference' => 'taxonomy,png-region', 'search_field_type' => 'checkbox', 'display_count' => 0],
            ['display_name' => 'Status', 'filter_term_type' => 'metadata', 'filter_term_key' => 'status', 'metadata_reference' => 'taxonomy,project-status', 'search_field_type' => 'checkbox',  'display_count' => 0],
        ];
        $map_block_filter['fields_actions']  = [
            'auto_submit' => false,
            'submit_btn_show' => false,
            'submit_display_name' => 'Search',
            'reset_btn_show' => true,
            'reset_display_name' => 'Reset'
        ];
        // 
        $multimedia_filter = [];
        $multimedia_filter_name = 'multimedia'; //filter name should be post type to query and filter by main wp query 
        $multimedia_filter['is_main_query'] = true;
        $multimedia_filter['post_type']  = $multimedia_filter_name; // post type to filter
        $multimedia_filter['taxonomies']  = 'multimedia-type,multimedia-related-to'; // seperate the multiple taxonomy by (,) comma
        $multimedia_filter['posts_per_page'] = -1; // post per page in post archive wq query result page

        // return field settings
        $fields[$resource_filter_name]  = $resource_filter;
        $fields[$map_block_filter_name]  = $map_block_filter;
        $fields[$project_filter_name]  = $project_filter;
        $fields[$multimedia_filter_name]  = $multimedia_filter;


        return $fields;
    }
}
