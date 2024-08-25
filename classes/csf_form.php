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

/**
 * class to handle search_filter_form 
 */
class CSF_Form
{
    public static $form_ids = [];
    /**
     * search filter form output
     * @param array $search_form[]
     *              $search_form['form_class'] = "search-filter-form";
     *              $search_form['post_type'] = 'post_type';
     *              $search_form['data_url'] = 'action_url ;
     *              $search_form['filter_name'] = 'post_type;' default=post_type or as defined in \SF_Fields\set_search_fields $resource_filter_name var
     */
    public static function the_search_filter_form($search_form = [])
    {
        $form_class = (isset($search_form['form_class'])) ? $search_form['form_class'] : "search-filter-form";
        $post_type =  (isset($search_form['post_type'])) ? $search_form['post_type'] : '';
        $data_url = (isset($search_form['data_url'])) ? $search_form['data_url'] : home_url($post_type);
        $data_url = ($data_url) ? $data_url : home_url($post_type);
        $filter_name = (isset($search_form['filter_name'])) ? $search_form['filter_name'] : '';
        if (!$filter_name) {
            echo "filter name is required";
            return;
        }
        if (!$post_type) {
            echo "post type is required";
            return;
        }

        $search_fields = \csf_search_filter\CSF_Fields::set_search_fields();
        $fields_settings = (isset($search_fields[$filter_name])) ? $search_fields[$filter_name] : '';
        if (! $fields_settings) {
            echo "search filter fields/settings are not set/defind";
            return;
        }
        $search_filter_title = (isset($fields_settings['search_filter_title'])) ? $fields_settings['search_filter_title'] : 'Filters';
        $form_id = 'csf-filter-form-' . str_replace([' '], '-', strtolower($filter_name));
        $result_area_id = "csf-result-area-" . str_replace([' '], '-', strtolower($filter_name));
        self::$form_ids[] = $form_id;
        \csf_search_filter\CSF_Enqueue::csf_search_js(self::$form_ids); ?>
        <div class="search-form-wrapper">
            <h2 class='search-filter-title font-semibold text-sm m-4 mt-0'>
                <?php echo esc_attr($search_filter_title); ?>
            </h2>
            <form id="<?php echo esc_attr($form_id); ?>" action="" method="GET" class='<?php echo esc_attr($form_class); ?>' data-url=<?php echo esc_attr($data_url); ?> role="search" result-area-id="<?php echo esc_attr($result_area_id) ?>">
                <?php
                // setup csf_nonce
                // wp_nonce_field('csf_nonce', '_csf_nonce', true, true);
                // check and display each search filter field
                $fields = $fields_settings['fields'];
                foreach ($fields as $key => $field) {
                    // search_field_type
                    $search_field_type = (isset($field['search_field_type'])) ? $field['search_field_type'] : 'dropdown';
                    if ($search_field_type === 'search_text') {
                        self::search_text_field($field);
                        continue;
                    }
                    // filter_term_type
                    $filter_term_type = (isset($field['filter_term_type'])) ? $field['filter_term_type'] : '';
                    if (! $filter_term_type) {
                        echo "search filter field term_type is not set/defind";
                        continue;
                    }
                    $filter_title = (isset($field['display_name'])) ? $field['display_name'] : '';
                    if (!$filter_title) {
                        echo "filter display name is not set or defined.";
                        break;
                        // if there is no name then go to next field
                    }
                    $field_name = self::get_search_field_name($filter_title);
                    $show_count = isset($field['display_count']) ? $field['display_count'] : 1;
                    $filter_items = (isset($field['filter_items'])) ? $field['filter_items'] : '';
                    if (!$filter_items || !is_array($filter_items)) {
                        $filter_items = [];
                        // check filter_term_type for taxonomy and metadara
                        $filter_term_key = (isset($field['filter_term_key'])) ? $field['filter_term_key'] : '';
                        if (! $filter_term_key) {
                            echo "search filter field term_key is not set/defind";
                            continue;
                        }
                        if ($filter_term_type === 'taxonomy') {
                            $filter_items = get_terms(['taxonomy' => $filter_term_key, 'hide_empty' => true]);
                        }
                        if ($filter_term_type === 'metadata') {
                            $metadata_reference = (isset($field['metadata_reference'])) ? $field['metadata_reference'] : '';
                            $filter_items = \csf_search_filter\CSF_Data::get_csf_metadata($post_type, $filter_term_key, $metadata_reference);
                        }
                    }

                    // check search_field_type
                    if ($search_field_type == 'dropdown') {
                        \csf_search_filter\CSF_Form::dropdown_field($filter_title, $field_name, $filter_items, $show_count);
                    }
                    if ($search_field_type == 'checkbox') {
                        \csf_search_filter\CSF_Form::checkbox_field($filter_title, $field_name, $filter_items, $show_count);
                    }
                }
                ?>
            </form>

        </div>
    <?php
    }

    // get input field name
    public static function get_search_field_name($display_name)
    {
        $field_name = "csf_" . str_replace([' ', '-'], '_', strtolower($display_name));
        return  $field_name;
    }

    // form free search text area
    public static function search_text_field($field)
    {
        $search = isset($_GET['search']) ? $_GET['search'] : '';

    ?>
        <div class="filter-block search-box" filter-action="free-search">
            <span class='search-icon'>
                <?php
                if (function_exists('useSVG')) {
                    echo useSVG('search-icon');
                }
                ?>
            </span>
            <input type="text" id="search_input" class='search-input' name="search" value="<?php echo esc_attr($search); ?>" placeholder="<?php echo esc_attr($field['placeholder']); ?>">
        </div>
    <?php
    }

    // form dropdown_field fild
    public static function dropdown_field($filter_title, $name, $filter_items, $show_count = 0)
    {
        $select_dropdown = isset($_GET[$name]) ? $_GET[$name] : '';
        wp_enqueue_style('select2');
        wp_enqueue_script('select2'); ?>
        <div class="filter-block sort-wrapper <?php echo ($select_dropdown) ? 'active' : ''; ?>">
            <select name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" data-type="<?php echo esc_attr($name); ?>">
                <option value="">
                    <?php echo esc_attr($filter_title); ?>
                </option>
                <?php
                foreach ($filter_items as $each_key => $value) {
                    $value = (array) $value;
                    $search_drop_select = '';
                    if (isset($value['slug'])) {
                        $search_drop_select = ($select_dropdown == $value['slug']) ? 'selected' : '';
                    }
                ?>
                    <option value="<?php echo (isset($value['slug'])) ? $value['slug'] : ''; ?>" option-id="<?php echo (isset($value['term_id'])) ? $value['term_id'] : ''; ?>" parent-id="<?php echo (isset($value['parent'])) ? $value['parent'] : ''; ?>" <?php echo  $search_drop_select; ?>>
                        <span class="label-name">
                            <?php echo (isset($value['name'])) ? $value['name'] : ''; ?>
                        </span>
                        <?php
                        if ($show_count) {
                        ?>
                            <span class="label-count">
                                <?php echo (isset($value['count'])) ? '(' . $value['count'] . ')' : ''; ?>
                            </span>
                        <?php
                        }
                        ?>
                    </option>
                <?php
                }
                ?>
            </select>
        </div>
    <?php
    }

    // form checkbox_field field
    public static function checkbox_field($filter_title, $field_name, $filter_items, $show_count = 0)
    {
        $active_filter = isset($_GET['active_filter']) ? $_GET['active_filter'] : '';
        $select_checkbox = isset($_GET[$field_name]) ? $_GET[$field_name] : [];
        $active_filter = ($active_filter == $field_name) ? 'active' : '';
        $filter_active_class = ($select_checkbox) ? 'active' : $active_filter; ?>
        <div class="filter-block accordion <?php echo esc_attr($filter_active_class); ?>">
            <div class="filter-title accordion__title-container <?php echo esc_attr($filter_active_class); ?>">
                <div class='title flex gap-x-1.5 items-center'>
                    <?php echo esc_attr($filter_title); ?>
                </div>
            </div>
            <div class="filter-item-list accordion__body">
                <div class="item-list-wrapper">
                    <?php
                    foreach ($filter_items as $each_key => $value) {
                        $value = (array) $value;
                        $checkbox_checked = '';
                        if (is_array($select_checkbox) && in_array($value['slug'], $select_checkbox)) {
                            $checkbox_checked = 'checked';
                        }
                    ?>
                        <div class="filter-item">
                            <div class="checkbox-input-wrapper ">
                                <div class='filter-input-wrapper custom-checkbox'>
                                    <input type="checkbox" name="<?php echo esc_attr($field_name) . '[]'; ?>" id="<?php echo esc_attr($field_name) . '-' . $value['slug']; ?>" value="<?php echo $value['slug']; ?>" <?php echo  $checkbox_checked; ?>>
                                    <span class='checkmark'></span>
                                </div>
                                <label class='filter-item-label flex gap-x-1.5 items-center' for="<?php echo esc_attr($field_name) . '-' . $value['slug']; ?>">
                                    <div>
                                        <span class="label-name"><?php echo $value['name']; ?></span>
                                        <?php
                                        if ($show_count) {
                                        ?>
                                            <span class="label-count">(<?php echo $value['count']; ?>)</span>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </label>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
<?php
    }

    // SF_Form class end
}
