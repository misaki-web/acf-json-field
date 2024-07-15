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
				'name'         => 'editor_mode',
				'label'        => __('Editor Mode', 'acf-json-field'),
				'type'         => 'select',
				'choices'      => [
					'table'    => 'table',
					'text' => 'text',
					'tree'  => 'tree',
				],
				'required'     => false,
				'default_value' => self::DEFAULT_EDITOR_MODE,
				'hint'         => __('Select the editor mode ("tree" by default)', 'acf-json-field'),
			]
		);

		acf_render_field_setting(
			$field,
			[
				'label' => __('Default Value', 'acf-json-field'),
				'instructions' => __('Default JSON value if no value is entered by the user', 'acf-json-field'),
				'type' => 'textarea',
				'name' => 'default_value',
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
		$textarea_value = isset($field['value']) ? esc_textarea($field['value']) : '';

		if ($textarea_value === '' && isset($field['default_value'])) {
			$textarea_value = esc_textarea($field['default_value']);
		}

		$editor_id = $textarea_id . '-editor';
		$editor_mode = isset($field['editor_mode']) ? esc_attr($field['editor_mode']) : self::DEFAULT_EDITOR_MODE;

		if ($textarea_id !== '' && $textarea_name !== '') {
			echo <<<HTML
				<textarea id="$textarea_id" class="acf-json-field-data" name="$textarea_name">$textarea_value</textarea>
				<div id="$editor_id" class="acf-json-field-editor" data-editor-mode="$editor_mode"></div>
			HTML;
		}
	}
}
