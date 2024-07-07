<?php

namespace AcfJsonField;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Utility class for ACF JSON Field plugin.
 */
class JsonUtils {
	/**
	 * Retrieves and formats JSON data from a specified ACF field.
	 *
	 * @param string $field The key or name of the ACF field containing the JSON data.
	 * @param int|string|false|null $id The ID of the post or user:
	 *                                  - `false` or `null`: Uses the ID of the current post.
	 *                                  - `int`: Uses the provided value as the post ID.
	 *                                  - `user`: Uses the ID of the current user.
	 *                                  - `user_\d+`: Uses the specified user ID.
	 *                                  Examples:
	 *                                  - get_json_field('field_name_or_key')
	 *                                  - get_json_field('field_name_or_key', null)
	 *                                  - get_json_field('field_name_or_key', 'null')
	 *                                  - get_json_field('field_name_or_key', false)
	 *                                  - get_json_field('field_name_or_key', 'false')
	 *                                  - get_json_field('field_name_or_key', 125)
	 *                                  - get_json_field('field_name_or_key', '125')
	 *                                  - get_json_field('field_name_or_key', 'user')
	 *                                  - get_json_field('field_name_or_key', 'user_24')
	 * @param string $format The format in which to return the data.
	 *                       Accepts 'php' for the appropriate PHP type, or 'html' for an HTML formatted string.
	 *                       Defaults to 'php'.
	 * @return mixed The JSON data formatted as specified.
	 *               Returns the appropriate PHP type if `$format` is 'php', or an HTML string if `$format` is 'html'.
	 */
	public static function get_json_field($field, $id = null, $format = 'php') {
		if ($id === 'user') {
			$id = 'user_' . get_current_user_id();
		} else if ($id === '' || $id === null || $id === 'null' || $id === false || $id === 'false') {
			$id = get_the_ID();
		}

		$data = '';
		$raw_data = get_field($field, $id);
		$json_decoded = json_decode($raw_data, true);

		if ($format === 'html') {
			$json_encoded = json_encode($json_decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$json_encoded = preg_replace_callback('/^( {4,})/m', function ($matches) {
				return str_replace('    ', '  ', $matches[1]);
			}, $json_encoded);

			$token_pattern = '/("(?:\\\\u[a-fA-F0-9]{4}|\\\\[^u]|[^\\\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(\.\d*)?([eE][+-]?\d+)?)/';
			$json_encoded = preg_replace_callback($token_pattern, function ($matches) {
				$match = $matches[0];
				$token_type = 'number';

				if (preg_match('/^"/', $match)) {
					if (preg_match('/:$/', $match)) {
						$token_type = 'key';
					} else {
						$token_type = 'string';
					}
				} elseif (preg_match('/true|false/', $match)) {
					$token_type = 'boolean';
				} elseif (preg_match('/null/', $match)) {
					$token_type = 'null';
				}

				return '<span class="acf-json-field-' . $token_type . '">' . esc_html($match) . '</span>';
			}, $json_encoded);

			$data = '<pre class="acf-json-field-output">' . $json_encoded . '</pre>';
		} else if ($format === 'php') {
			$data = $json_decoded;
		}

		return $data;
	}
}
