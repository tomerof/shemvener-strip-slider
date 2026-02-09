<?php
/**
 * Plugin Name: Shemvener Strip Slider
 * Description: A standalone plugin to show an infinite slider of deceased persons via an Elementor widget.
 * Version: 1.0.0
 * Author: Tomer
 * Text Domain: shemvener-strip-slider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class ShemvenerStripSliderPlugin {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	public function init() {
		// Check if Elementor is installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function register_widgets( $widgets_manager ) {
		require_once __DIR__ . '/widgets/ShemvenerStripSliderWidget.php';
		$widgets_manager->register( new \ShemvenerStripSliderWidget() );
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'shemvener-strip-slider', plugin_dir_url( __FILE__ ) . 'assets/css/strip-slider.css', [], '1.0.0' );
		wp_enqueue_script( 'shemvener-strip-slider', plugin_dir_url( __FILE__ ) . 'assets/js/strip-slider.js', [ 'jquery', 'elementor-frontend-modules' ], '1.0.0', true );
	}
}

ShemvenerStripSliderPlugin::instance();
