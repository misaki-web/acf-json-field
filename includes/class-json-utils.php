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
	 * Adds HTML tokens to a JSON-encoded string for syntax highlighting.
	 *
	 * @param string $json_encoded The JSON-encoded string.
	 * @return string The JSON string with HTML tokens for syntax highlighting.
	 */
	private static function add_html_tokens($json_encoded) {
		$token_pattern = '/("(?:\\\\u[a-fA-F0-9]{4}|\\\\[^u]|[^\\\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(\.\d*)?([eE][+-]?\d+)?)/';

		return preg_replace_callback($token_pattern, function ($matches) {
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
	}

	/**
	 * Retrieves the appropriate ID based on the input.
	 * @return int|string The determined ID.
	 */
	private static function get_id($id) {
		if ($id === 'user') {
			$id = 'user_' . get_current_user_id();
		} else if (empty($id) || $id === 'null' || $id === 'false') {
			$id = get_the_ID();
		}

		return $id;
	}

	/**
	 * Encodes PHP data to a JSON string with formatting options.
	 *
	 * @param mixed $php_data The PHP data to be encoded.
	 * @return string The JSON-encoded string.
	 */
	public static function encode($php_data) {
		$json_encoded = json_encode($php_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$json_encoded = preg_replace_callback('/^( {4,})/m', function ($matches) {
			return str_replace('    ', "\t", $matches[1]);
		}, $json_encoded);

		return $json_encoded;
	}

	/**
	 * Generates HTML pre code from PHP value.
	 *
	 * @param mixed $php_data The PHP data to be encoded and highlighted.
	 * @param string $additional_classes Optional additional classes for the <pre> tag.
	 * @return string The HTML string with syntax highlighting.
	 */
	public static function generate_pre($php_data, $additional_classes = '') {
		$json_encoded = self::encode($php_data);
		$json_highlighted = self::add_html_tokens($json_encoded);
		$classes = 'acf-json-field-output ' . $additional_classes;

		return '<pre class="' . esc_attr(trim($classes)) . '">' . $json_highlighted . '</pre>';
	}

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
	 *                                  - get_json_field('field_name_or_key', false)
	 *                                  - get_json_field('field_name_or_key', null)
	 *                                  - get_json_field('field_name_or_key', 125)
	 *                                  - get_json_field('field_name_or_key', 'user')
	 *                                  - get_json_field('field_name_or_key', 'user_24')
	 * @param string $format The format in which to return the data.
	 *                       Accepts 'php' for the appropriate PHP type, or 'html' for an HTML formatted string.
	 *                       Defaults to 'php'.
	 * @return mixed The JSON data formatted as specified.
	 *               Returns the appropriate PHP type if `$format` is 'php', or an HTML string if `$format` is 'html'.
	 */
	public static function get_json_field($field, $id = null, $format = 'php') {
		$id = self::get_id($id);
		$data = '';
		$raw_data = get_field($field, $id);
		$json_decoded = json_decode($raw_data, true);

		if ($format === 'html') {
			$data = self::generate_pre($json_decoded);
		} else if ($format === 'php') {
			$data = $json_decoded;
		}

		return $data;
	}

	/**
	 * Sets the JSON data to a specified ACF field.
	 *
	 * @param string $field The key or name of the ACF field where the JSON data will be stored.
	 * @param mixed $data The data to be stored.
	 * @param int|string|false|null $id The ID of the post or user (see `get_json_field` for details).
	 * @param bool $is_json_encoded True if the data passed is already JSON encoded, false otherwise.
	 * @param bool $add_slashes True if slashes should be added to the data to escape it properly, false otherwise.
	 * @return bool True if the data was successfully updated, false otherwise.
	 */
	public static function set_json_field($field, $data, $id = null, $is_json_encoded = false, $add_slashes = false) {
		$id = self::get_id($id);
		$json_encoded = $is_json_encoded ? $data : self::encode($data);

		if ($add_slashes) {
			$json_encoded = wp_slash($json_encoded);
		}

		if (str_starts_with($id, 'user_')) {
			$ret = update_user_meta(substr($id, 5), $field, $json_encoded);
		} else {
			$ret = update_post_meta($id, $field, $json_encoded);
		}

		$success = !empty($ret);

		return $success;
	}
}
