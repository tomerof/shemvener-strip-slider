<?php
/**
 * Plugin Name: Shemvener Strip Slider
 * Description: A standalone plugin to show an infinite slider of deceased persons via an Elementor widget.
 * Version: 1.0.1
 * Author: Tomer
 * Text Domain: shemvener-strip-slider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class ShemvenerStripSliderPlugin {

	private static $_instance = null;

	public string $plugin_slug;
	public string $cache_key;
	public bool $cache_allowed;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );

		add_filter( 'plugins_api', array( $this, 'info' ), 20, 3 );
		add_filter( 'site_transient_update_plugins', array( $this, 'update' ) );
		add_action( 'upgrader_process_complete', array( $this, 'purge' ), 10, 2 );

		$this->plugin_slug = plugin_basename( __DIR__ );
		$this->cache_key = 'shemvener-strip-slider-update';
		$this->cache_allowed = false;
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

	public function info( $res, $action, $args ) {
		if( 'plugin_information' !== $action ) {
			return false;
		}

		if( $this->plugin_slug !== $args->slug ) {
			return false;
		}

		$remote = $this->request();

		if( ! $remote ) {
			return false;
		}

		$res = new \stdClass();

		$res->name = $remote->name ?? '';
		$res->slug = $remote->slug ?? '';
		$res->version = $remote->version ?? '';
		$res->tested = $remote->tested ?? '';
		$res->requires = $remote->requires ?? '';
		$res->author = $remote->author ?? '';
		$res->author_profile = $remote->author_profile ?? '';
		$res->download_link = $remote->download_url ?? '';
		$res->trunk = $remote->download_url ?? '';
		$res->requires_php = $remote->requires_php ?? '';
		$res->last_updated = $remote->last_updated ?? '';

		$res->sections = array(
			'description' => $remote->sections->description ?? '',
			'installation' => $remote->sections->installation ?? '',
			'changelog' => $remote->sections->changelog ?? ''
		);

		return $res;
	}

	public function update( $transient ) {
		if ( empty($transient->checked ) ) {
			return $transient;
		}

		$remote = $this->request();
		if(is_admin()){
			$plugin_data = get_plugin_data( __FILE__ );
	
			if(
				$remote
				&& version_compare( $plugin_data['Version'], $remote->version, '<' )
				&& version_compare( $remote->requires, get_bloginfo( 'version' ), '<' )
				&& version_compare( $remote->requires_php, PHP_VERSION, '<' )
			) {
				$res = new \stdClass();
				$res->slug = $this->plugin_slug;
				$res->plugin = plugin_basename( __FILE__ );
				$res->new_version = $remote->version;
				$res->tested = $remote->tested;
				$res->package = $remote->download_url;
	
				$transient->response[ $res->plugin ] = $res;
			}
		}

		return $transient;
	}

	public function purge($updater, $options){
		if (
			$this->cache_allowed
			&& 'update' === $options['action']
			&& 'plugin' === $options[ 'type' ]
		) {
			delete_transient( $this->cache_key );
		}
	}

	public function request(){
		$remote = get_transient( $this->cache_key );

		if( false === $remote || ! $this->cache_allowed ) {
			$remote = wp_remote_get(
				"https://netdesign.media/plugins-updates/{$this->plugin_slug}/info.json",
				array(
					'timeout' => 10,
					'headers' => array(
						'Accept' => 'application/json'
					)
				)
			);

			if(
				is_wp_error( $remote )
				|| 200 !== wp_remote_retrieve_response_code( $remote )
				|| empty( wp_remote_retrieve_body( $remote ) )
			) {
				return false;
			}

			set_transient( $this->cache_key, $remote, 60 );
		}

		return json_decode( wp_remote_retrieve_body( $remote ) );
	}
}

ShemvenerStripSliderPlugin::instance();
