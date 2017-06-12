<?php
//security first
if ( ! defined( 'ABSPATH' ) ) exit;

class Zao_Sensei_Module_Manager_Settings {
	public function __construct(){
		if ( is_admin() ) {
			add_filter( 'sensei_settings_tabs', array( $this, 'register_settings_tab' ) );
			add_filter( 'sensei_settings_fields', array( $this, 'register_settings_fields' ) );
		}
	}// end __construct

	/**
	 * sensei get_setting value wrapper
	 *
	 * @return string $settings value
	 */
	public function get_setting( $setting_token ) {
		global $woothemes_sensei;

		// get all settings from sensei
		$settings = $woothemes_sensei->settings->get_settings();

		if ( empty( $settings )  || ! isset( $settings[ $setting_token ] ) ) {
			return '';
		}

		return $settings[ $setting_token ];
	}

	/**
	 * Attaches the the module collapse settings to the sensei admin settings tabs
	 *
	 * @param array $sensei_settings_tabs;
	 * @return array  $sensei_settings_tabs
	 */
	public function register_settings_tab( $sensei_settings_tabs ) {

		$smc_tab  = array(
			'name' 			=> __( 'Module Collapse', 'zao-sensei-module-manager' ),
			'description'	=> __( 'Optional settings for the Module Collapse extension', 'zao-sensei-module-manager' )
		);

		$sensei_settings_tabs['zao-sensei-module-manager-settings'] = $smc_tab;

		return $sensei_settings_tabs;
	}// end register_settings_tab


	/**
	 * Includes the module collapse settings fields
	 *
	 * @param array $sensei_settings_fields;
	 * @return array  $sensei_settings_fields
	 */
	public function register_settings_fields( $sensei_settings_fields ) {

		$sensei_settings_fields['sensei_module_title'] = array(
			'name' => __( 'Module title link', 'zao-sensei-module-manager' ),
			'description' => __( 'Check to remove the link in the module title so that it is a plain title with lessons listed below.', 'woothemes-sensei' ),
			'type' => 'checkbox',
			'default' => false,
			'section' => 'zao-sensei-module-manager-settings',
		);

		$sensei_settings_fields['sensei_module_video'] = array(
			'name' => __( 'Lesson video', 'zao-sensei-module-manager' ),
			'description' => __( 'Check to add video icon when a lesson includes a video in expanded lesson list.', 'woothemes-sensei' ),
			'type' => 'checkbox',
			'default' => true,
			'section' => 'zao-sensei-module-manager-settings',
		);

		$sensei_settings_fields['sensei_module_lesson_time'] = array(
			'name' => __( 'Lesson time', 'zao-sensei-module-manager' ),
			'description' => __( 'Check to add lesson time when a lesson includes a video time in expanded lesson list.', 'woothemes-sensei' ),
			'type' => 'checkbox',
			'default' => true,
			'section' => 'zao-sensei-module-manager-settings',
		);

		$sensei_settings_fields['sensei_module_notes'] = array(
			'name' => __( 'Lesson notes', 'zao-sensei-module-manager' ),
			'description' => __( 'Check to add notes icon when a lesson includes a video in expanded lesson list (requires Sensei Media Attachments plugin).', 'woothemes-sensei' ),
			'type' => 'checkbox',
			'default' => true,
			'section' => 'zao-sensei-module-manager-settings',
		);

		$sensei_settings_fields['sensei_module_quiz'] = array(
			'name' => __( 'Lesson quiz', 'zao-sensei-module-manager' ),
			'description' => __( 'Check to add quiz icon when a lesson includes a video in expanded lesson list.', 'woothemes-sensei' ),
			'type' => 'checkbox',
			'default' => true,
			'section' => 'zao-sensei-module-manager-settings',
		);

		return $sensei_settings_fields;

	}// end register_settings_fields
}// end Zao_Sensei_Module_Manager_Settings
