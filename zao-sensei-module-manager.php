<?php

/*
 * Plugin Name: Zao Sensei Module Manager
 * Version: 1.0.0
 * Plugin URI: https://zao.is/
 * Description: Add collapsible modules to your Sensei courses
 * Author: Zao
 * Author URI: https://zao.is/
 * Requires at least: 3.5
 * Tested up to: 4.4
 * @package WordPress
 * @author Zao
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Functions used by plugins
 */
if ( ! class_exists( 'WooThemes_Sensei_Dependencies' ) ) {
	require_once 'woo-includes/class-woothemes-sensei-dependencies.php';
}
/**
 * Sensei Detection
 */
if ( ! function_exists( 'is_sensei_active' ) ) {
	function is_sensei_active() {
		return WooThemes_Sensei_Dependencies::sensei_active_check();
	}
}
/**
 * Include plugin class
 */
if ( is_sensei_active() ) {
	require_once( 'classes/class-zao-sensei-module-manager.php' );

	global $zao_sensei_module_manager;
	$zao_sensei_module_manager = new Zao_Sensei_Module_Manager( __FILE__ );

	require_once( 'classes/class-zao-sensei-module-manager-settings.php' );

	global $zao_sensei_module_manager_settings;
	$zao_sensei_module_manager_settings = new Zao_Sensei_Module_Manager_Settings( __FILE__ );
}
function zao_sensei_module_manager_load_scripts() {
	wp_enqueue_style( 'font-awesome-css', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' , array(), '1.0.0' );
}

add_action( 'wp_enqueue_scripts', 'zao_sensei_module_manager_load_scripts' );
