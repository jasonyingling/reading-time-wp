<?php
/**
 * Shortcode Class
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
 * Handles reading time shortcode.
 *
 * @since 3.0.0
 */
class Shortcode {

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
		add_shortcode( 'rt_reading_time', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render the [rt_reading_time] shortcode.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param string $content Shortcode content (unused).
	 * @return string Shortcode output.
	 */
	public function render_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts(
			array(
				'label'            => '',
				'postfix'          => '',
				'postfix_singular' => '',
				'post_id'          => '',
			),
			$atts,
			'rt_reading_time'
		);

		// If post_id attribute was specified and exists, use that; otherwise use current post ID.
		$post_id = $atts['post_id'] && get_post_status( $atts['post_id'] ) ? $atts['post_id'] : get_the_ID();

		// Calculate reading time.
		$reading_time = $this->calculator->calculate_reading_time( $post_id, $this->options );

		// Determine postfix.
		$postfix_singular = ! empty( $atts['postfix_singular'] ) ? $atts['postfix_singular'] : $this->options['postfix_singular'];
		$postfix          = ! empty( $atts['postfix'] ) ? $atts['postfix'] : $this->options['postfix'];
		$calculated_postfix = $this->calculator->get_postfix( $reading_time, $postfix_singular, $postfix );

		// Determine label.
		$label = ! empty( $atts['label'] ) ? $atts['label'] : $this->options['label'];

		// Generate output.
		$output = $this->calculator->generate_output( $label, $reading_time, $calculated_postfix, '' );

		return $output;
	}
}
