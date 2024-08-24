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

// Here we define or set the search filter fields/settings
class CSF_Fields
{

    // 
    public static function set_search_fields($default = false)
    {
        if (!$default) {
            $csf_set_search_fields = (get_option('csf_set_search_fields')) ?: '';
            if ($csf_set_search_fields) {
                return json_decode($csf_set_search_fields, true);
            }
        }
        return self::set_search_fields_default();
    }

    // 
    public static function set_csf_cache_metadata_fields($default = false)
    {
        if (!$default) {
            $csf_cache_metadata_fields = (get_option('csf_cache_metadata_fields')) ?: '';
            if ($csf_cache_metadata_fields) {
                return json_decode($csf_cache_metadata_fields, true);
            }
        }
        return self::set_csf_cache_metadata_fields_default();
    }

    /**
     * Each filter fields values has following options $fields['unique_filter_name']['fields']
     * display_name=>'Display name'
     * filter_term_type => 'taxonomy' or 'metadata'
     * filter_term_key => 'taxonomy_key' or 'metadata_key'; [if single meta_key has multiple metavalue in case of repeater metavalue:: example metakey_{array}_metakey]
     * metadata_reference => 'taxonomy,taxonomy_key,slug' or 'post' or 'other-as-defined'; only apply to filter_term_key metadata_key Where 'taxonomy,taxonomy_key,slug' third parameter 'slug' define that wp query will perform meta query on given value .
     * search_field_type => 'dropdown' or 'checkbox' or 'search_text'; there can only be one 'search_text' on each filter
     * placeholder => 'free text' ;only apply to search_field_type search_text
     * display_count => 1 or 0; default 1
     */
    /**
     * In case of repeater metavalue
     * Multiple metavalue for same metakey filte is not developed. 
     */
    //search_fields
    public static function set_search_fields_default()
    {
        $fields = [];

        // For resource post type search filter
        $resource_filter = [];
        $resource_filter_name = 'resource'; //filter name should be post type to query and filter by main wp query 
        $resource_filter['post_type']  = $resource_filter_name; // post type to filter
        $resource_filter['posts_per_page'] = 12; // post per page in post wq query result page
        $resource_filter['search_filter_title'] = 'Filters'; // Search filter title in the search form
        $resource_filter['fields']  = [
            ['display_name' => '', 'search_field_type' => 'search_text', 'placeholder' => 'Search with title or keywords.'],
            ['display_name' => 'Sector', 'filter_term_type' => 'taxonomy', 'filter_term_key' => 'resource-sector', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Commodity Type', 'filter_term_type' => 'taxonomy', 'filter_term_key' => 'commodity-type', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Country', 'filter_term_type' => 'metadata', 'filter_term_key' => 'country_only', 'metadata_reference' => 'country_2digit_code', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Region', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_png_region_only', 'metadata_reference' => 'taxonomy,png-region,slug', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Province', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_png_region_province_only', 'metadata_reference' => 'taxonomy,png-region,slug', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Topic', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,topic', 'filter_term_key' => 'topic_sub_topic_list_{array}_topic_only', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Project', 'filter_term_type' => 'metadata', 'metadata_reference' => 'post', 'filter_term_key' => 'project_name', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Resource Type', 'filter_term_type' => 'taxonomy', 'filter_term_key' => 'resource-type', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Organisation', 'filter_term_type' => 'taxonomy', 'filter_term_key' => 'organisation', 'search_field_type' => 'checkbox',],
            ['display_name' => 'Publisher', 'filter_term_type' => 'taxonomy', 'filter_term_key' => 'publisher', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Year', 'filter_term_type' => 'metadata', 'filter_term_key' => 'publication_year_only', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Format', 'filter_term_type' => 'taxonomy', 'filter_term_key' => 'format', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Right', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,resource-rights', 'filter_term_key' => 'rights_license_type_rights_only', 'search_field_type' => 'checkbox'],
        ];
        $resource_filter['free_search']['meta_keys'] = ['description', 'publication_year_only'];
        $resource_filter['free_search']['post_taxonomies'] = ['keywords-tag', 'resource-type', 'commodity-type', 'resource-sector', 'format'];

        // For project post type search filter
        $project_filter = [];
        $project_filter_name = 'project'; //filter name should be post type to query and filter by main wp query 
        $project_filter['post_type']  = $project_filter_name; // post type to filter
        $project_filter['posts_per_page'] = 12; // post per page in post archive wq query result page
        $project_filter['search_filter_title'] = 'Filters'; // Search filter title in the search form
        $project_filter['fields']  = [
            ['search_field_type' => 'search_text', 'placeholder' => 'Search with title or keywords.'],
            ['display_name' => 'Sector', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,resource-sector', 'filter_term_key' => 'sector', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Commodity Type', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,commodity-type', 'filter_term_key' => 'commodity_type', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Status', 'filter_term_type' => 'taxonomy', 'filter_term_key' => 'project-status', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Owner', 'filter_term_type' => 'taxonomy', 'filter_term_key' => 'owner-operator', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Region', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_province_png_region_only', 'metadata_reference' => 'taxonomy,png-region,slug', 'search_field_type' => 'checkbox'],
            ['display_name' => 'Province', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_province_png_region_province_only', 'metadata_reference' => 'taxonomy,png-region,slug', 'search_field_type' => 'checkbox'],
        ];

        // For map block search filter
        $map_block_filter = [];
        $map_block_filter_name = 'map_filter'; //filter name should be post type to query and filter by main wp query 
        $map_block_filter['post_type']  = 'project'; // post type to filter
        $map_block_filter['search_filter_title'] = 'Filter By'; // Search filter title in the search form
        $map_block_filter['fields']  = [
            ['display_name' => 'Sector', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,resource-sector', 'filter_term_key' => 'sector', 'search_field_type' => 'dropdown'],
            ['display_name' => 'Commodity Type', 'filter_term_type' => 'metadata', 'metadata_reference' => 'taxonomy,commodity-type', 'filter_term_key' => 'commodity_type', 'search_field_type' => 'dropdown'],
            ['display_name' => 'Status', 'filter_term_type' => 'taxonomy', 'filter_term_key' => 'project-status'],
            ['display_name' => 'Region', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_province_png_region_only', 'metadata_reference' => 'taxonomy,png-region', 'search_field_type' => 'dropdown'],
            ['display_name' => 'Province', 'filter_term_type' => 'metadata', 'filter_term_key' => 'region_province_png_region_province_only', 'metadata_reference' => 'taxonomy,png-region', 'search_field_type' => 'dropdown'],
        ];

        // return field settings
        $fields[$resource_filter_name]  = $resource_filter;
        $fields[$map_block_filter_name]  = $map_block_filter;
        $fields[$project_filter_name]  = $project_filter;

        return $fields;
    }


    /**
     * OPTIONAL
     * only defind set_csf_cache_metadata_fields if you want to set the csf_cache_metadata data
     * this field and post type will be saved in csf cache meta data
     * $fields[$post_type]=[
     *      [
     *          'filter_meta_key' => 'metadata_key', 
     *          'metadata_reference' => 'taxonomy,taxonomy_key,slug' or 'post' or 'other-as-defined',
     *          'form_field_name'=>'form_field_name'; form field name connected with dot(.) and replcae repeated loop with {array}
     *      ],
     * ]
     */
    public static function set_csf_cache_metadata_fields_default()
    {
        $fields = [];
        // For post type resource
        $resource_post_type = 'resource';
        $fields[$resource_post_type] = [
            [
                'filter_meta_key' => 'country_only',
                'metadata_reference' => 'country_2digit_code',
                'form_field_name' => 'acf.field_669642be967c2'
            ],
            [
                'filter_meta_key' => 'region_png_region_only',
                'metadata_reference' => 'taxonomy,png-region',
                'form_field_name' => 'acf.field_66acc85fa24df.field_66acc8ebebda9'
            ],
            [
                'filter_meta_key' => 'region_png_region_province_only',
                'metadata_reference' => 'taxonomy,png-region',
                'form_field_name' => 'acf.field_66acc85fa24df.field_66acc96fb2d29'
            ],
            [
                'filter_meta_key' => 'topic_sub_topic_list_{array}_topic_only',
                'metadata_reference' => 'taxonomy,topic',
                'form_field_name' => 'acf.field_66bb1ba7d5ecf.{array}.field_66964cecb080f'
            ],
            [
                'filter_meta_key' => 'project_name',
                'metadata_reference' => 'post',
                'form_field_name' => 'acf.field_66964ad4e1cc0.{array}'
            ],
            [
                'filter_meta_key' => 'publication_year_only',
                'metadata_reference' => '',
                'form_field_name' => 'acf.field_66963bc7fb891'
            ],
            [
                'filter_meta_key' => 'rights_license_type_rights_only',
                'metadata_reference' => 'taxonomy,resource-rights',
                'form_field_name' => 'acf.field_66b59b577d4af.field_66964d15b0811'
            ],
        ];
        // For post type resource
        $project_post_type = 'project';
        $fields[$project_post_type] = [
            [
                'filter_meta_key' => 'sector',
                'metadata_reference' => 'taxonomy,resource-sector',
                'form_field_name' => 'acf.field_66b5c49915748'
            ],
            [
                'filter_meta_key' => 'commodity_type',
                'metadata_reference' => 'taxonomy,commodity-type',
                'form_field_name' => 'acf.field_66b5c4bb15749'
            ],
            [
                'filter_meta_key' => 'region_province_png_region_only',
                'metadata_reference' => 'taxonomy,png-region',
                'form_field_name' => 'acf.field_66bf178e4592a.field_66bf17b84592b'
            ],
            [
                'filter_meta_key' => 'region_province_png_region_province_only',
                'metadata_reference' => 'taxonomy,png-region',
                'form_field_name' => 'acf.field_66bf178e4592a.field_66bf17e74592c'
            ]
        ];
        // return at last
        // return [];
        return $fields;
    }
}
