<?php

/**
 * =========================================
 * Plugin Name: CSF - Search Filter library
 * Description: A plugin for search filter to generate form and query the form, usedfull for deeveloper. 
 * Plugin URI: https://github.com/santoshtmp/csf-search-filter
 * Version: 1.0
 * Author: santoshtmp
 * =======================================
 */

namespace csf_search_filter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// class to handle Admin_setting
class CSF_Admin_setting
{

    public static $page_slug = 'search-filter-csf';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'this_plugin_settings_submenu']);
        add_action('admin_init', [$this, 'csf_settings_init']);
    }


    // Register the submenu page
    public function this_plugin_settings_submenu()
    {
        add_options_page(
            'CSF - Search Filter', // Page title
            'CSF - Search Filter', // Menu title
            'manage_options',     // Capability required to see the menu
            self::$page_slug, // Menu slug
            [$this, 'csf_setting_page_callback'] // Function to display the page content
        );
    }


    // Register and define the settings
    function csf_settings_init()
    {
        register_setting('search-filter-csf-setting', 'enable_csf_cache_meta');
        register_setting('search-filter-csf-setting', 'reset_csf_cache_meta');
        register_setting('search-filter-csf-setting', 'csf_set_search_fields');
        register_setting('search-filter-csf-setting', 'csf_cache_metadata_fields');


        $section_id = 'settigs_fields_section';
        // Register a new section in the "search-filter-csf" page
        add_settings_section(
            $section_id, // Section ID
            '', //'Post Title Required : General Setting', // Title of the section
            [$this, 'settings_section_callback'], // Callback function to render the section description
            self::$page_slug // Page slug
        );
        // Register a new field in the "settigs_fields_section" section
        add_settings_field(
            'csf_set_search_fields',
            'CSF Search Fields',
            [$this, 'csf_set_search_fields_callback'],
            self::$page_slug,
            $section_id
        );

        add_settings_field(
            'enable_csf_cache_meta',
            'Enable CSF cache metadata ',
            [$this, 'enable_csf_cache_meta_callback'],
            self::$page_slug,
            $section_id
        );

        add_settings_field(
            'csf_cache_metadata_fields',
            'CSF Cache Meta Fields',
            [$this, 'csf_cache_metadata_fields_callback'],
            self::$page_slug,
            $section_id
        );

        add_settings_field(
            'reset_csf_cache_meta',
            'Reset CSF cache metadata ',
            [$this, 'reset_csf_cache_meta_callback'],
            self::$page_slug,
            $section_id
        );
    }

    // Callback function to display the content of the submenu page
    public function csf_setting_page_callback()
    {
?>
        <div class="wrap">
            <h1>CSF - Search Filter</h1>
            <form method="post" action="options.php">
                <?php
                // Output security fields for the registered setting
                settings_fields('search-filter-csf-setting');
                // Output setting sections and their fields
                do_settings_sections('search-filter-csf');
                // Output save settings button
                submit_button();
                ?>
            </form>
        </div>
        <!-- Script to initialize Ace Editor -->
        <style>
            #csf_set_search_fields_editor,
            #csf_cache_metadata_fields_editor {
                width: 100%;
                height: 400px;
            }

            .ace_print-margin {
                left: 0px !important;
            }
        </style>
    <?php
        \csf_search_filter\CSF_Enqueue::csf_admin_setting_js();
    }

    // Callback function to render the section description
    function settings_section_callback()
    {
        echo '';
    }

    //
    function enable_csf_cache_meta_callback()
    {
        $default_value = 1;
        $value = (get_option('enable_csf_cache_meta')) ?: '';
        $checked = '';
        if ($default_value == $value) {
            $checked = 'Checked';
            $default_value = $value;
            \csf_search_filter\CSF_Data::set_enable_csf_cache_table();
        } else {
            \csf_search_filter\CSF_Data::clear_delete_csf_cache_table();
        }
    ?>
        <label for="enable_csf_cache_meta">
            <input type="checkbox" name="enable_csf_cache_meta" id="enable_csf_cache_meta" value="<?php echo esc_attr($default_value); ?>" <?php echo esc_attr($checked); ?>>
        </label>
        <!-- <p class="description">description.</p> -->
    <?php
    }

    // reset csf cache meta data
    function reset_csf_cache_meta_callback()
    {
        $default_value = 1;
        $value = (get_option('reset_csf_cache_meta')) ?: '';
        $checked = '';
        if ($default_value == $value) {
            $enable_csf_cache_meta = (get_option('enable_csf_cache_meta')) ?: '';
            if ($enable_csf_cache_meta == '1') {
                \csf_search_filter\CSF_Data::clear_delete_csf_cache_table();
                \csf_search_filter\CSF_Data::set_enable_csf_cache_table();
                echo "CSF Cache metadata is sucessfully reset  <br>";
                update_option('reset_csf_cache_meta', 0);
                $default_value = 0;
            } else {
                echo "First enable csf_cache_meta <br>";
                $checked = 'Checked';
            }
        } else {
        }
    ?>
        <label for="reset_csf_cache_meta">
            <input type="checkbox" name="reset_csf_cache_meta" id="reset_csf_cache_meta" value="<?php echo esc_attr($default_value); ?>" <?php echo esc_attr($checked); ?>>
        </label>
        <!-- <p class="description">description.</p> -->
    <?php
    }


    // 
    public function csf_set_search_fields_callback()
    {
        $value = (get_option('csf_set_search_fields')) ?: '';
        $close_icon = csf_path_url . 'assets/icon/close.svg';

    ?>
        <textarea id="csf_set_search_fields" name="csf_set_search_fields" style="display: none;"><?php echo esc_attr($value); ?></textarea>
        <div class="info" style="margin-bottom: 7px;">
            <button type="button" id="csf_set_search_fields_format">Format Code</button>
            <button type="button" class="btn btn-primary" data-action="csf_set_search_fields_default"> Set Default Value</button>
            <button type="button" class="help_btn" help-info-id="csf_search_fields_help_desc">
                Set Search Field Help
                <img src="<?php echo esc_attr($close_icon); ?>" alt="close-icon" class="help-close-icon" style="height: 14px; display: none;">
            </button>
            <button type="button" class="help_btn" help-info-id="csf_form_display_help_desc">
                Form Display Help
                <img src="<?php echo esc_attr($close_icon); ?>" alt="close-icon" class="help-close-icon" style="height: 14px; display: none;">
            </button>
            <button type="button" class="help_btn" help-info-id="csf_result_display_help_desc">
                CSF Result Display Help
                <img src="<?php echo esc_attr($close_icon); ?>" alt="close-icon" class="help-close-icon" style="height: 14px; display: none;">
            </button>
        </div>
        <div style="margin-bottom: 7px;">
            <div id="csf_search_fields_help_desc" class="help-info" style="display: none; ">
                <h4>CSF Search Fields JSON format fields settings are as defined:</h4>
                <pre>csf_search_filter = {
                        "unique_filter_name":{
                            "post_type":"post_type",
                            "posts_per_page":12,
                            "search_filter_title":"Text Title",
                            "fields":[
                                {
                                    "display_name": "Region",
                                    "filter_term_type": "metadata",
                                    "filter_term_key": "region_png_region_only",
                                    "metadata_reference": "taxonomy,png-region,slug",
                                    "search_field_type": "checkbox",
                                    "display_count":0
                                }
                            ],
                            "free_search": {
                                "meta_keys": [
                                    "meta_key"
                                ],
                                "post_taxonomies": [
                                    "taxonomy"
                                ]
                            },
                            "field_relation":"OR",
                            "result_template":""
                        }   
                    }</pre>
                <ol>
                    <li>
                        csf_search_filter['unique_filter_name'] = Unique filter name should be post type to query and filter by main wp query. :: REQUIRED
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['post_type'] = post type to filter :: REQUIRED
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['posts_per_page'] = post per page in post wq query result page
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['search_filter_title'] = Search filter title in the search form
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['fields'] = Each filter fields values has following options
                        <ol>
                            <li>display_name=>'Display name'</li>
                            <li>filter_term_type => 'taxonomy' or 'metadata'</li>
                            <li>
                                filter_term_key => 'taxonomy_key' or 'metadata_key'; [if single meta_key has multiple metavalue in case of repeater metavalue:: example metakey_{array}_metakey]
                            </li>
                            <li>
                                metadata_reference => 'taxonomy,taxonomy_key,slug' or 'post' or 'function-name-as-defined'; only apply to filter_term_key = 'metadata_key' Where ON 'taxonomy,taxonomy_key,slug' third parameter 'slug' define that wp query will perform meta query on given value .
                            </li>
                            <li>search_field_type => 'dropdown' or 'checkbox' or 'search_text'; default dropdown; there can only be one 'search_text' on each filter</li>
                            <li>placeholder => 'free text' ;only apply to search_field_type search_text</li>
                            <li>display_count => 1 or 0; default 1</li>
                        </ol>
                    </li>
                    <li>
                        csf_search_filter['unique_filter_name']['free_search'] = define the meta_key and taxonomy to accept free text search; free_search will only work with field_relation="OR"
                    </li>
                    <li>csf_search_filter['unique_filter_name']['field_relation'] = "OR / AND"; default "OR"; OPTIONAL
                    </li>
                    <li>csf_search_filter['unique_filter_name']['result_template'] => 'archive/filter/post_name.php';OPTIONAL :: define the template file path for the current active theme;</li>

                </ol>
            </div>
            <div id="csf_form_display_help_desc" class="help-info" style="display: none; ">
                <h4>CSF Form Display Setting</h4>
                <pre>
    $search_form = [];
    \csf_search_filter\CSF_Form::the_search_filter_form($search_form);

    OR 

    echo do_shortcode('[csf_searchfilter filter_name="unique_filter_name" data_url="" ]');

                    </pre>
                <ol>
                    <li> $search_form['filter_name'] = 'unique_filter_name'; default current_post_type</li>
                    <li> $search_form['form_class'] = 'form_id'; default 'search-filter-form' </li>
                    <li> $search_form['post_type'] 'post_type'; default current_post_type</li>
                    <li> $search_form['data_url'] = 'data_action_url'; default current_post_archive_url </li>
                </ol>
            </div>
            <div id="csf_result_display_help_desc" class="help-info" style="display: none; ">
                <h4>CSF Filter Result Display Setting</h4>
                <pre>
        echo do_shortcode('[csf_searchfilter filter_name="unique_filter_name" result_show="true"]');  
        OR  
        &lt;div id="csf-result-area-filter_name" &gt; -- loop content -- &lt;/div&gt; 
                </pre>
                <p>
                    Use shortcode <br> OR <br>
                    CSF Search Filter Result area must be wrap by the id = "csf-result-area-filter_name" inorder to display/replace the result by ajax. Here filter_name should be replace.
                    <br>
                    For the result template csf query result is stored in variable $csf_query.
                </p>
            </div>
        </div>
        <div id="csf_set_search_fields_editor"><?php echo esc_attr(($value) ? $value : ''); ?></div>
    <?php
    }

    public function csf_cache_metadata_fields_callback()
    {
        $value = (get_option('csf_cache_metadata_fields')) ?: '';
        $close_icon = csf_path_url . 'assets/icon/close.svg';
    ?>
        <textarea id="csf_cache_metadata_fields" name="csf_cache_metadata_fields" style="display: none;"><?php echo esc_attr($value); ?></textarea>
        <div class="info" style="margin-bottom: 7px;">
            <button type="button" id="csf_cache_metadata_fields_format">Format Code</button>
            <button type="button" class="btn btn-primary" id="csf_cache_metadata_fields_default"> Set Default Value</button>
            <button type="button" class="help_btn" help-info-id="csf_cache_metadata_fields_help_desc">
                Set Cache Field Help
                <img src="<?php echo esc_attr($close_icon); ?>" alt="close-icon" class="help-close-icon" style="height: 14px; display: none;">
            </button>
        </div>
        <div id="csf_cache_metadata_fields_help_desc" class="help-info" style="display: none;">
            <h4>CSF Cache Meta Fields JSON format fields settings are as defined:</h4>
            <p>
                <strong>OPTIONAL</strong> only defind csf_cache_metadata_fields if you want to enable the csf_cache_metadata data
                this field and post type will be saved in csf cache meta data
            </p>
            <pre>csf_cache_fields={
                'unique_post_type':[
                        {
                            "filter_meta_key": "country_only",
                            "metadata_reference": "country_2digit_code",
                            "form_field_name": "acf.field_669642be967c2"
                        }
                    ]
                }
            </pre>
            <ol>
                <li>csf_cache_fields['unique_post_type'] = post type</li>
                <li>
                    csf_cache_fields['unique_post_type']['filter_meta_key'] = 'metadata_key'
                </li>
                <li>
                    csf_cache_fields['unique_post_type']['metadata_reference'] = 'taxonomy,taxonomy_key,slug' or 'post' or 'other-as-defined',
                </li>
                <li>
                    csf_cache_fields['unique_post_type']['form_field_name'] = 'form_field_name'; form field name connected with dot(.) and replcae repeated loop with {array}
                </li>
            </ol>

        </div>
        <div id="csf_cache_metadata_fields_editor"><?php echo esc_attr($value); ?></div>
<?php
    }

    // 
}
