<?php
/**
 * Main Plugin Class
 *
 * @package Reading_Time_WP
 * @since 3.0.0
 */

namespace RTWP;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class that orchestrates all components.
 *
 * @since 3.0.0
 */
class Plugin {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '3.0.0';

	/**
	 * Plugin instance.
	 *
	 * @var Plugin
	 */
	private static $instance = null;

	/**
	 * Calculator instance.
	 *
	 * @var Calculator
	 */
	public $calculator;

	/**
	 * Admin instance.
	 *
	 * @var Admin
	 */
	public $admin;

	/**
	 * Shortcode instance.
	 *
	 * @var Shortcode
	 */
	public $shortcode;

	/**
	 * Content filter instance.
	 *
	 * @var Content_Filter
	 */
	public $content_filter;

	/**
	 * Blocks manager instance.
	 *
	 * @var Blocks\Blocks_Manager
	 */
	public $blocks_manager;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	public $options;

	/**
	 * Get plugin instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->load_textdomain();
		$this->init_components();
		$this->setup_hooks();
	}

	/**
	 * Load required dependencies.
	 */
	private function load_dependencies() {
		require_once __DIR__ . '/class-calculator.php';
		require_once __DIR__ . '/class-admin.php';
		require_once __DIR__ . '/class-shortcode.php';
		require_once __DIR__ . '/class-content-filter.php';
		require_once __DIR__ . '/blocks/class-blocks-manager.php';
	}

	/**
	 * Load plugin textdomain for translations.
	 */
	private function load_textdomain() {
		load_plugin_textdomain(
			'reading-time-wp',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/../languages/'
		);
	}

	/**
	 * Initialize plugin components.
	 */
	private function init_components() {
		// Load options.
		$this->options = $this->get_options();

		// Initialize calculator.
		$this->calculator = new Calculator( $this->options );

		// Initialize admin.
		$this->admin = new Admin( $this->options );

		// Initialize shortcode handler.
		$this->shortcode = new Shortcode( $this->calculator, $this->options );

		// Initialize content filter.
		$this->content_filter = new Content_Filter( $this->calculator, $this->options );

		// Initialize blocks manager.
		$this->blocks_manager = new Blocks\Blocks_Manager( $this->calculator, $this->options );
	}

	/**
	 * Setup WordPress hooks.
	 */
	private function setup_hooks() {
		// Plugin loaded action.
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );

		// Init action.
		add_action( 'init', array( $this, 'on_init' ) );
	}

	/**
	 * Fired when plugins are loaded.
	 */
	public function on_plugins_loaded() {
		/**
		 * Fires after the plugin is fully loaded.
		 *
		 * @since 3.0.0
		 *
		 * @param Plugin $plugin Plugin instance.
		 */
		do_action( 'rtwp_plugin_loaded', $this );
	}

	/**
	 * Fired on WordPress init.
	 */
	public function on_init() {
		// Reload options in case they were updated.
		$this->options = $this->get_options();

		// Update component options.
		$this->calculator->set_options( $this->options );
		$this->admin->set_options( $this->options );
		$this->shortcode->set_options( $this->options );
		$this->content_filter->set_options( $this->options );
		$this->blocks_manager->set_options( $this->options );
	}

	/**
	 * Get plugin options with defaults.
	 *
	 * @return array Plugin options.
	 */
	public function get_options() {
		// Build default post types array.
		$default_post_types = array();
		$post_type_args     = apply_filters( 'rtwp_post_type_args', array( 'public' => true ) );
		$post_types         = get_post_types( $post_type_args );

		foreach ( $post_types as $post_type ) {
			// Skip attachments.
			if ( 'attachment' === $post_type ) {
				continue;
			}
			$default_post_types[ $post_type ] = true;
		}

		$defaults = array(
			'label'              => __( 'Reading Time:', 'reading-time-wp' ) . ' ',
			'postfix'            => __( 'minutes', 'reading-time-wp' ),
			'postfix_singular'   => __( 'minute', 'reading-time-wp' ),
			'wpm'                => 300,
			'before_content'     => true,
			'before_excerpt'     => true,
			'exclude_images'     => false,
			'include_shortcodes' => false,
			'post_types'         => $default_post_types,
		);

		$options = get_option( 'rt_reading_time_options', array() );

		return wp_parse_args( $options, $defaults );
	}

	/**
	 * Get plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return self::VERSION;
	}

	/**
	 * Get plugin directory path.
	 *
	 * @return string
	 */
	public function get_plugin_path() {
		return dirname( __DIR__ );
	}

	/**
	 * Get plugin directory URL.
	 *
	 * @return string
	 */
	public function get_plugin_url() {
		return plugin_dir_url( __DIR__ );
	}
}
