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

/**
 * Include the plugin update checker.
 */

require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';

$update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/misaki-web/acf-json-field',
	__FILE__,
	'acf-json-field'
);
$update_checker->getVcsApi()->enableReleaseAssets();
