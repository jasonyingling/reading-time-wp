<?php
/**
 * Content Filter Class
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
 * Handles automatic reading time insertion in content and excerpts.
 *
 * @since 3.0.0
 */
class Content_Filter {

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
	 * Constructor.
	 *
	 * @param Calculator $calculator Calculator instance.
	 * @param array      $options Plugin options.
	 */
	public function __construct( $calculator, $options = array() ) {
		$this->calculator = $calculator;
		$this->options    = $options;

		$this->setup_hooks();
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
		// Check if before_content is enabled.
		if ( isset( $this->options['before_content'] ) && $this->options['before_content'] ) {
			add_filter( 'the_content', array( $this, 'add_before_content' ) );
		}

		// Check if before_excerpt is enabled.
		if ( isset( $this->options['before_excerpt'] ) && $this->options['before_excerpt'] ) {
			add_filter( 'get_the_excerpt', array( $this, 'add_before_excerpt' ), 1000 );
		}
	}

	/**
	 * Add reading time before post content.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content Original post content.
	 * @return string Content with reading time prepended.
	 */
	public function add_before_content( $content ) {
		// Reload options to ensure we have the latest.
		$options = get_option( 'rt_reading_time_options', $this->options );

		// Get current post type.
		$current_post_type = get_post_type();

		// Ensure post_types is set.
		if ( ! isset( $options['post_types'] ) ) {
			$options['post_types'] = array();
		}

		// If current post type isn't enabled, return original content.
		if ( ! isset( $options['post_types'][ $current_post_type ] ) || ! $options['post_types'][ $current_post_type ] ) {
			return $content;
		}

		// Don't display on excerpts (prevents double display).
		if ( in_array( 'get_the_excerpt', $GLOBALS['wp_current_filter'], true ) ) {
			return $content;
		}

		$original_content = $content;
		$post_id          = get_the_ID();

		// Calculate reading time.
		$reading_time = $this->calculator->calculate_reading_time( $post_id, $options );

		// Get label and postfix.
		$label            = $options['label'];
		$postfix          = $options['postfix'];
		$postfix_singular = $options['postfix_singular'];

		$calculated_postfix = $this->calculator->get_postfix( $reading_time, $postfix_singular, $postfix );

		// Generate output and prepend to content.
		$reading_time_output = $this->calculator->generate_output( $label, $reading_time, $calculated_postfix, 'display: block;' );

		return $reading_time_output . $original_content;
	}

	/**
	 * Add reading time before excerpt.
	 *
	 * @since 3.0.0
	 *
	 * @param string $content Original excerpt content.
	 * @return string Excerpt with reading time prepended.
	 */
	public function add_before_excerpt( $content ) {
		// Reload options to ensure we have the latest.
		$options = get_option( 'rt_reading_time_options', $this->options );

		// Get current post type.
		$current_post_type = get_post_type();

		// Ensure post_types is set.
		if ( ! isset( $options['post_types'] ) ) {
			$options['post_types'] = array();
		}

		// If current post type isn't enabled, return original content.
		if ( ! isset( $options['post_types'][ $current_post_type ] ) || ! $options['post_types'][ $current_post_type ] ) {
			return $content;
		}

		$original_content = $content;
		$post_id          = get_the_ID();

		// Calculate reading time.
		$reading_time = $this->calculator->calculate_reading_time( $post_id, $options );

		// Get label and postfix.
		$label            = $options['label'];
		$postfix          = $options['postfix'];
		$postfix_singular = $options['postfix_singular'];

		$calculated_postfix = $this->calculator->get_postfix( $reading_time, $postfix_singular, $postfix );

		// Generate output and prepend to content.
		$reading_time_output = $this->calculator->generate_output( $label, $reading_time, $calculated_postfix, 'display: block;' );

		return $reading_time_output . $original_content;
	}
}
