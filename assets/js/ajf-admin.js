import { JSONEditor, toTextContent } from './vanilla-jsoneditor-standalone.js';

function ajf_decode_html_entities(str) {
	const textarea = document.createElement('textarea');
	textarea.innerHTML = str;

	return textarea.value;
}

function ajf_str_to_json_data(str) {
	const json_data = {
		error: '',
		json: {}
	};

	if (!str.length) {
		json_data.success = true;
	} else {
		try {
			json_data.json = JSON.parse(str);
			json_data.success = true;
		} catch (e) {
			json_data.json = {};
			json_data.error = e;
		}
	}

	return json_data;
}

jQuery(document).ready(function ($) {
	$('.acf-json-field-data').each(function () {
		const $textarea = $(this);
		const textarea_id = $textarea.attr('id');
		const $editor = $('#' + textarea_id + '-editor');
		
		if ($editor.length) {
			const textarea_value = ajf_decode_html_entities($textarea.val().trim());
			const json_data = ajf_str_to_json_data(textarea_value);
			const content = {
				text: undefined,
				json: json_data.json
			};

			if (json_data.error) {
				const error_msg = `ACF JSON Field: Invalid JSON format in \`textarea#${textarea_id}\`.`;
				console.error(error_msg, json_data.error, 'Parsed value: ' + textarea_value);
				wp.data.dispatch('core/notices').createNotice('error', error_msg, { isDismissible: true });
			}
			
			const json_editor = new JSONEditor({
				target: $editor[0],
				props: {
					content,
					mode: $editor.attr('data-editor-mode'),
					askToFormat: false,
					onChange: (updated_content) => {
						const updated_content_with_text = toTextContent(updated_content);
						$textarea.val(updated_content_with_text.text);
					}
				}
			});
		}
	});
});
