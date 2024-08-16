<?php
// 
$index_field = ($index_field) ? $index_field : 0;
$post_type = ($post_type) ? $post_type : 'page';
$settings = ($settings) ? $settings : [];
// define field name
$name_search_field_data = 'fields[' . $index_field . '][search_field_data]';
$name_search_field_taxonomy = 'fields[' . $index_field . '][search_field_taxonomy]';
$name_search_field_metadata = 'fields[' . $index_field . '][search_field_metadata]';
$name_search_field_type = 'fields[' . $index_field . '][search_field_type]';
$name_display_count = 'fields[' . $index_field . '][display_count]';
$name_display_name = 'fields[' . $index_field . '][display_name]';
// get tax and meta data
$taxonomies = get_object_taxonomies($post_type, 'object');
$all_meta_data = custom_search_filter\Helper::get_post_all_meta_data($post_type);
// field values
$taxonomy_selected = ($settings['search_field_data'] === 'taxonomy') ? 'selected' : '';
$metadata_selected = ($settings['search_field_data'] === 'metadata') ? 'selected' : '';
$search_field_type_dropdown = ($settings['search_field_type'] === 'dropdown') ? 'selected' : '';
$search_field_type_checkbox = ($settings['search_field_type'] === 'checkbox') ? 'selected' : '';
$display_count_checked = (isset($settings['display_count'])) ? 'checked' : '';
$display_name_value = (isset($settings['display_name'])) ? $settings['display_name'] : '';
// 
?>
<div class="data-field-wrapper" style="margin: 0 0 14px; display:flex; flex-wrap:wrap; gap:4px;">

    <!-- <div class="snum">
        <?php echo $index_field; ?>
    </div> -->

    <div class="display_name">
        <input class="" name="<?php echo $name_display_name; ?>" type="text" size="21" value="<?php echo $display_name_value; ?>" placeholder="Display Name" required >
    </div>

    <div class="search_field_data_type">
        <select name="<?php echo $name_search_field_data; ?>" data-field="search_field_data">
            <!-- <option value="">Field Data Type</option> -->
            <option value="taxonomy" <?php echo $taxonomy_selected; ?>>
                Taxonomy
            </option>
            <option value="metadata" <?php echo $metadata_selected; ?>>
                Meta data
            </option>
        </select>
    </div>

    <div class="search_field_taxonomy">
        <select name="<?php echo $name_search_field_taxonomy; ?>" data-field="search_field_taxonomy" <?php echo ($taxonomy_selected) ? ' required ' : ' style="display: none;" '; ?>>
            <option value="">Select Taxonomy Term</option>
            <?php
            foreach ($taxonomies  as $key => $taxo) {
                $selected = "";
                if ($settings['search_field_taxonomy'] === $taxo->name) {
                    $selected = "selected";
                }
            ?>
                <option value="<?php echo _e($taxo->name); ?>" <?php echo $selected; ?>>
                    <?php echo _e($taxo->label); ?>
                </option>
            <?php
            }
            ?>
        </select>
    </div>

    <div class="search_field_metadata" <?php echo ($metadata_selected) ? '' : ' style="display: none;" '; ?>>
        <select name="<?php echo $name_search_field_metadata; ?>" data-field="search_field_metadata" <?php echo ($metadata_selected) ? ' required ' : ''; ?>>
            <option value="">Select Meta Data</option>
            <?php
            foreach ($all_meta_data  as $key => $meta_data) {
                $selected = "";
                if ($settings['search_field_metadata'] === $meta_data) {
                    $selected = "selected";
                }
            ?>
                <option value="<?php echo _e($meta_data); ?>" <?php echo $selected; ?>>
                    <?php echo _e($meta_data); ?>
                </option>
            <?php
            }
            ?>
        </select>
    </div>

    <div class="search_field_type">
        <select name="<?php echo $name_search_field_type; ?>" data-field="search_field_type" <?php ?>>
            <!-- <option value="">Field Type</option> -->
            <option value="dropdown" <?php echo $search_field_type_dropdown; ?>>Dropdown</option>
            <option value="checkbox" <?php echo $search_field_type_checkbox; ?>>Checkbox</option>
        </select>
    </div>

    <div class="display_count">
        <label>
            <?php _e("Display count?"); ?>
            <input class="checkbox display_count" type="checkbox" name="<?php echo $name_display_count; ?>" data-field="display_count" <?php echo $display_count_checked; ?>>
        </label>
    </div>

    <button type="button" data-action="remove-field">Remove Field</button>
</div>