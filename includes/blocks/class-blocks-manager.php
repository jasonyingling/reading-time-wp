<?php
/**
 * Blocks Manager Class
 *
 * @package Reading_Time_WP
 * @since 3.0.0
 */

namespace RTWP\Blocks;

use RTWP\Calculator;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages block editor integration.
 *
 * @since 3.0.0
 */
class Blocks_Manager {

	/**
	 * Calculator instance.
	 *
	 * @var Calculator
	 */
	private $calculator;

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Registered blocks.
	 *
	 * @var array
	 */
	private $blocks = array();

	/**
	 * Constructor.
	 *
	 * @param Calculator $calculator Calculator instance.
	 * @param array      $options Plugin options.
	 */
	public function __construct( $calculator, $options = array() ) {
		$this->calculator = $calculator;
		$this->options    = $options;

		$this->load_dependencies();
		$this->setup_hooks();
	}

	/**
	 * Load block dependencies.
	 */
	private function load_dependencies() {
		require_once __DIR__ . '/class-reading-time-block.php';
	}

	/**
	 * Set options.
	 *
	 * @param array $options Plugin options.
	 */
	public function set_options( $options ) {
		$this->options = $options;
	}

	/**
	 * Setup WordPress hooks.
	 */
	private function setup_hooks() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Register blocks.
	 *
	 * @since 3.0.0
	 */
	public function register_blocks() {
		// Only proceed if block editor exists.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Register reading time block.
		$reading_time_block = new Reading_Time_Block( $this->calculator, $this->options );
		$reading_time_block->register();

		$this->blocks['reading-time'] = $reading_time_block;
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @since 3.0.0
	 */
	public function enqueue_block_editor_assets() {
		// This will be used for future block editor scripts.
		// For now, we rely on server-side rendering.
	}

	/**
	 * Get registered blocks.
	 *
	 * @return array
	 */
	public function get_blocks() {
		return $this->blocks;
	}
}
