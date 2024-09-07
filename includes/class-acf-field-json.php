<?php

namespace AcfJsonField;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * acf_field_json class.
 */
class acf_field_json extends \acf_field {
	private const DEFAULT_EDITOR_MODE = 'tree';

	/**
	 * Constructor.
	 */
	public function __construct() {
		/**
		 * Field type reference used in PHP code.
		 *
		 * No spaces. Underscores allowed.
		 */
		$this->name = 'json';

		/**
		 * Field type label.
		 *
		 * For public-facing UI. May contain spaces.
		 */
		$this->label = __('JSON', 'acf-json-field');

		/**
		 * The category the field appears within in the field type picker.
		 */
		$this->category = 'content'; // basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME

		parent::__construct();
	}

	/**
	 * Settings to display when users configure a field of this type.
	 *
	 * These settings appear on the ACF “Edit Field Group” admin page when
	 * setting up the field.
	 *
	 * @param array $field
	 * @return void
	 */
	public function render_field_settings($field) {
		acf_render_field_setting(
			$field,
			[
				'name' => 'editor_mode',
				'label' => __('Editor Mode', 'acf-json-field'),
				'type' => 'select',
				'choices' => [
					'none' => 'none',
					'table' => 'table',
					'text' => 'text',
					'tree' => 'tree',
				],
				'required' => false,
				'default_value' => self::DEFAULT_EDITOR_MODE,
				'instructions' => sprintf(__('Select the editor mode ("%s" by default)', 'acf-json-field'), self::DEFAULT_EDITOR_MODE),
			]
		);
		
		acf_render_field_setting(
			$field,
			[
				'name' => 'editor_height',
				'label' => __('Editor Height', 'acf-json-field'),
				'type' => 'number',
				'step' => 1,
				'min' => 1,
				'required' => false,
				'default_value' => '',
				'instructions' => __('Editor height (px) different from the default height defined in the stylesheet. If the editor is disabled, the height will apply to the textarea.', 'acf-json-field'),
			]
		);
		
		acf_render_field_setting(
			$field,
			[
				'name' => 'default_value',
				'label' => __('Default Value', 'acf-json-field'),
				'type' => 'textarea',
				'required' => false,
				'instructions' => __('Default JSON value if no value is entered by the user', 'acf-json-field'),
			]
		);
	}

	/**
	 * HTML content to show when the field is edited.
	 *
	 * @param array $field The field settings and values.
	 * @return void
	 */
	public function render_field($field) {
		$textarea_id = isset($field['id']) ? esc_attr($field['id']) : '';
		$textarea_name = isset($field['name']) ? esc_attr($field['name']) : '';
		$field_value = isset($field['value']) ? $field['value'] : '';
		
		if (is_string($field_value)) {
			# Decode the JSON string to ensure it can be re-encoded with consistent formatting.
			$field_value = JsonUtils::decode($field_value);
		}
		
		$field_value = JsonUtils::encode($field_value);
		$textarea_value = esc_textarea($field_value);

		if ($textarea_value === '' && isset($field['default_value'])) {
			$textarea_value = esc_textarea($field['default_value']);
		}

		$editor_id = $textarea_id . '-editor';
		$editor_mode = isset($field['editor_mode']) ? esc_attr($field['editor_mode']) : self::DEFAULT_EDITOR_MODE;
		$style_attr = '';
		$style = [];
		
		if (!empty($field['editor_height'])) {
			$style['height'] = esc_attr($field['editor_height']) . 'px';
		}
		
		if (!empty($style)) {
			foreach ($style as $k => $v) {
				$style_attr .= "$k: $v;";
			}
			
			$style_attr = 'style="' . $style_attr . '"';
		}

		if ($textarea_id !== '' && $textarea_name !== '') {
			echo <<<HTML
				<textarea id="$textarea_id" class="acf-json-field-data" name="$textarea_name" data-editor-mode="$editor_mode" {$style_attr}>$textarea_value</textarea>
				<div id="$editor_id" class="acf-json-field-editor" data-editor-mode="$editor_mode" {$style_attr}></div>
			HTML;
		}
	}
}
