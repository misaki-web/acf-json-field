<?php

/**
 * Plugin Name: ACF JSON Field
 * Description: A custom ACF field type for manipulating JSON data
 * Text Domain: acf-json-field
 * Author: Misaki F.
 * Version: 1.0.8
 */

namespace AcfJsonField;

if (!defined('ABSPATH')) {
	return;
}

################################################################################
# @title Constants
################################################################################

define('ACF_JSON_FIELD_VERSION', '1.0.9');

################################################################################
# @title Inclusions
################################################################################

require_once(__DIR__ . '/includes/init.php');
require_once(__DIR__ . '/includes/plugin-update-checker/plugin-update-checker.php');

################################################################################
# @title Assets
################################################################################

########################################
## @subtitle Backend
########################################

add_action('acf/input/admin_enqueue_scripts', function () {
	if (is_admin()) { # Voir <https://github.com/AdvancedCustomFields/acf/issues/412>
		$url_dir = plugin_dir_url(__FILE__);

		wp_enqueue_style('acf-json-field-admin-css', $url_dir . 'assets/css/ajf-admin.css', [], ACF_JSON_FIELD_VERSION);
		wp_enqueue_style('acf-json-field-css', $url_dir . 'assets/css/ajf.css', [], ACF_JSON_FIELD_VERSION);

		wp_enqueue_script('acf-json-field-admin-js', $url_dir . 'assets/js/ajf-admin.js', ['jquery', 'acf-input'], ACF_JSON_FIELD_VERSION, true);
		wp_register_script('acf-json-field-js', $url_dir . 'assets/js/ajf.js', ['jquery'], ACF_JSON_FIELD_VERSION);
		wp_enqueue_script('acf-json-field-js');
	}
});

add_filter('script_loader_tag', function ($tag, $handle, $src) {
	if ($handle === 'acf-json-field-admin-js') {
		$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
	}

	return $tag;
}, 10, 3);

########################################
## @subtitle Frontend
########################################

add_action('wp_enqueue_scripts', function () {
	$url_dir = plugin_dir_url(__FILE__);

	wp_enqueue_style('acf-json-field-css', $url_dir . 'assets/css/ajf.css', [], ACF_JSON_FIELD_VERSION);

	wp_register_script('acf-json-field-js', $url_dir . 'assets/js/ajf.js', ['jquery'], ACF_JSON_FIELD_VERSION);
	wp_enqueue_script('acf-json-field-js');
});

################################################################################
# @title Update checker
################################################################################

$update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/misaki-web/acf-json-field',
	__FILE__,
	'acf-json-field'
);
$update_checker->getVcsApi()->enableReleaseAssets();

################################################################################
# @title Shortcode
################################################################################

# Render the shortcode "acf_json_field".
add_shortcode('acf_json_field', function ($atts = []) {
	$default_atts = [
		'field' => '',
		'id' => null,
	];
	$atts = shortcode_atts($default_atts, $atts);

	return JsonUtils::get_json_field($atts['field'], $atts['id'], 'html');
});
