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
        global $wp;
        $form_class = (isset($search_form['form_class'])) ? $search_form['form_class'] : "search-filter-form";
        $post_type =  (isset($search_form['post_type'])) ? $search_form['post_type'] : '';
        $data_url = (isset($search_form['data_url'])) ? $search_form['data_url'] : home_url(add_query_arg(array(), $wp->request));
        $data_url = ($data_url) ? $data_url : home_url($post_type);
        $filter_name = (isset($search_form['filter_name'])) ? $search_form['filter_name'] : '';
        $all_post_ids =  (isset($search_form['all_post_ids'])) ? $search_form['all_post_ids'] : [];
        // 
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
        self::$form_ids[] = $form_id; ?>
        <div class="search-form-wrapper">
            <div>
                <h2 class='search-filter-title font-semibold text-sm m-4 mt-0'>
                    <?php echo esc_attr($search_filter_title); ?>
                </h2>
            </div>
            <form id="<?php echo esc_attr($form_id); ?>" action="" method="GET" class='<?php echo esc_attr($form_class); ?>'
                data-url=<?php echo esc_attr($data_url); ?> role="search"
                result-area-id="<?php echo esc_attr($result_area_id) ?>">
                <?php
                // setup csf_nonce
                // wp_nonce_field('csf_nonce', '_csf_nonce', true, true);
                // check and display each search filter field
                $fields = $fields_settings['fields'];
                $has_search_text_get_value =  $has_drop_down_get_value =  $has_checkbox_get_value = '';
                foreach ($fields as $key => $field) {
                    // search_field_type
                    $search_field_type = (isset($field['search_field_type'])) ? $field['search_field_type'] : 'dropdown';
                    if ($search_field_type === 'search_text') {
                        $has_search_text_get_value = self::search_text_field($field);
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
                            $filter_items = \csf_search_filter\CSF_Data::get_csf_metadata($post_type, $filter_term_key, $metadata_reference, '', $all_post_ids);
                        }
                    }

                    // check search_field_type
                    if ($search_field_type == 'dropdown') {
                        $drop_down_get_value = \csf_search_filter\CSF_Form::dropdown_field($filter_title, $field_name, $filter_items, $show_count);
                        if (!$has_drop_down_get_value) {
                            $has_drop_down_get_value = $drop_down_get_value;
                        }
                    }
                    if ($search_field_type == 'checkbox') {
                        $checkbox_get_value =  \csf_search_filter\CSF_Form::checkbox_field($filter_title, $field_name, $filter_items, $show_count);
                        if (!$has_checkbox_get_value) {
                            $has_checkbox_get_value = $checkbox_get_value;
                        }
                    }
                    // 
                }
                // 
                $fields_actions = isset($fields_settings['fields_actions']) ? $fields_settings['fields_actions'] : '';
                if ($fields_actions) {

                    $auto_submit = isset($fields_actions['auto_submit']) ? $fields_actions['auto_submit'] : true;
                    if ($auto_submit) {
                        \csf_search_filter\CSF_Enqueue::csf_search_js(self::$form_ids, $filter_name);
                    }
                    // 
                    $submit_btn_show = isset($fields_actions['submit_btn_show']) ? $fields_actions['submit_btn_show'] : false;
                    $reset_btn_show = isset($fields_actions['reset_btn_show']) ? $fields_actions['reset_btn_show'] : false;
                    $submit_display_name = isset($fields_actions['submit_display_name']) ? $fields_actions['submit_display_name'] : 'Search';
                    $reset_display_name = isset($fields_actions['reset_display_name']) ? $fields_actions['reset_display_name'] : 'Reset';

                    if ($submit_btn_show == 'true' || $reset_btn_show == 'true') {
                        echo '<div class="filter-action-btns">';
                        if ($submit_btn_show == 'true') {
                ?>
                            <div class="filter-submit-btn ">
                                <button type="submit"><?php echo $submit_display_name; ?></button>
                            </div>
                        <?php
                        }
                        if ($reset_btn_show == 'true') {
                            $reset_display = 'display: none;';
                            if ($has_search_text_get_value ||  $has_drop_down_get_value ||  $has_checkbox_get_value) {
                                $reset_display = '';
                            }
                        ?>
                            <div class="filter-reset-btn ">
                                <button type="reset" style="<?php echo $reset_display; ?>"><?php echo $reset_display_name; ?></button>
                            </div>
                <?php
                        }
                        echo '</div>';
                    }
                }
                ?>


            </form>
            <!-- Put a close icon for use in the map filters in mobile view -->
            <div class='close-icon' style='display: none;'>
                <?php echo useSVG('close-icon'); ?>
            </div>
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
        $search = isset($_GET['search']) ? $_GET['search'] : '';  ?>
        <div class="filter-block search-box" filter-action="free-search">
            <span class='search-icon'>
                <?php
                if (function_exists('useSVG')) {
                    echo useSVG('search-icon');
                }
                ?>
            </span>
            <input type="text" id="search_input" class='search-input' name="search" value="<?php echo esc_attr($search); ?>"
                placeholder="<?php echo esc_attr($field['placeholder']); ?>">
        </div>
    <?php
        return $search;
    }

    // form dropdown_field fild
    public static function dropdown_field($filter_title, $name, $filter_items, $show_count = 0)
    {
        $select_dropdown = isset($_GET[$name]) ? $_GET[$name] : '';
    ?>
        <div class="filter-block sort-wrapper <?php echo ($select_dropdown) ? 'active' : ''; ?>">
            <select name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>"
                data-type="<?php echo esc_attr($name); ?>">
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
                    $unique_filter_item_name = $name . '-' . $value['slug']

                ?>
                    <option value="<?php echo (isset($value['slug'])) ? $value['slug'] : ''; ?>"
                        item-type="<?php echo esc_attr($unique_filter_item_name); ?>"
                        option-id="<?php echo (isset($value['term_id'])) ? $value['term_id'] : ''; ?>"
                        parent-id="<?php echo (isset($value['parent'])) ? $value['parent'] : ''; ?>"
                        <?php echo  $search_drop_select; ?>>
                        <span class="label-name">
                            <?php echo (isset($value['name'])) ? ucfirst($value['name']) : ''; ?>
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
        return $select_dropdown;
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
                    if ($field_name == 'csf_year') {
                        array_multisort(array_column($filter_items, 'name'), SORT_DESC, $filter_items);
                    } else {
                        array_multisort(array_column($filter_items, 'name'), SORT_ASC, $filter_items);
                    }
                    foreach ($filter_items as $each_key => $value) {
                        $value = (array) $value;
                        $checkbox_checked = '';
                        if (is_array($select_checkbox) && in_array($value['slug'], $select_checkbox)) {
                            $checkbox_checked = 'checked';
                        }
                        $unique_filter_item_name = $field_name . '-' . $value['slug']
                    ?>
                        <div class="filter-item" item-type="<?php echo esc_attr($unique_filter_item_name); ?>">
                            <div class="checkbox-input-wrapper ">
                                <div class='filter-input-wrapper custom-checkbox'>
                                    <input type="checkbox" name="<?php echo esc_attr($field_name) . '[]'; ?>"
                                        id="<?php echo esc_attr($unique_filter_item_name); ?>" value="<?php echo $value['slug']; ?>"
                                        <?php echo  $checkbox_checked; ?>>
                                    <span class='checkmark'></span>
                                </div>
                                <label class='filter-item-label flex gap-x-1.5 items-center'
                                    for="<?php echo esc_attr($unique_filter_item_name); ?>">
                                    <div>
                                        <span class="label-name"><?php echo ucfirst($value['name']); ?></span>
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
        return $select_checkbox;
    }

    // SF_Form class end
}
