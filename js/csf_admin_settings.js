/**
 * JS to handle csf admin setting
 */
var csf_fields = ace.edit("csf_set_search_fields_editor"); // div id to convert into editor
csf_fields.session.setMode("ace/mode/json"); // Set mode for JSON syntax highlighting
csf_fields.setTheme("ace/theme/monokai");  // Set a theme


// handle submit 
let submit_btn = document.getElementById('submit');
submit_btn.addEventListener('click', function (e) {
    // csf_fields_input
    const csf_fields_input = csf_fields.getValue();
    let csf_fields_input_isValidJSON = false;
    if (csf_fields_input) {
        if (isValidJSON(csf_fields_input)) {
            csf_fields_input_isValidJSON = true;
        } else {
            e.stopPropagation();
            e.preventDefault();
        }
    }
    if (csf_fields_input_isValidJSON || (csf_fields_input == '')) {
        document.getElementById('csf_set_search_fields').value = csf_fields_input;
    }
});

// ceck is json is valid
const isValidJSON = str => {
    try {
        JSON.parse(str);
        return true;
    } catch (e) {
        return false;
    }
};

// formatJSON
function formatJSON(editor) {
    try {
        // Get editor value and parse JSON
        var content = editor.getValue();
        var json = JSON.parse(content);

        // Format JSON with indentation
        var formatted = JSON.stringify(json, null, 4);

        // Set formatted JSON back to editor
        editor.setValue(formatted, 1); // 1 moves cursor to the end of the text
    } catch (e) {
        alert("Invalid JSON!");
    }
}

// 
document.getElementById('csf_set_search_fields_format').addEventListener('click', function () {
    formatJSON(csf_fields);
});


// 
document.querySelector('[data-action="csf_set_search_fields_default"]').addEventListener('click', function () {
    let default_val_fields = csf_obj.default_search_fields;
    if (default_val_fields) {
        csf_fields.setValue(default_val_fields)
    }
});

// 
let help_btn = document.querySelectorAll('.help_btn');
help_btn.forEach((btn) => {
    btn.addEventListener('click', function () {
        let help_info_id = btn.getAttribute('help-info-id');
        if (help_info_id) {
            let field_help_desc = document.getElementById(help_info_id);
            let close_icon = document.querySelector('[help-info-id="' + help_info_id + '"] .help-close-icon');
            if (field_help_desc.style.display == '') {
                field_help_desc.style.display = 'none';
                close_icon.style.display = 'none';
            } else {
                field_help_desc.style.display = '';
                close_icon.style.display = '';
            }
        }
    });
});
