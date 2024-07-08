<?php

namespace AcfJsonField;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Registers the ACF field type.
 */
add_action('init', function () {
	if (!function_exists('acf_register_field_type')) {
		return;
	}

	require_once __DIR__ . '/class-acf-field-json.php';
	require_once __DIR__ . '/class-json-utils.php';

	acf_register_field_type(__NAMESPACE__ . '\\acf_field_json');
});
