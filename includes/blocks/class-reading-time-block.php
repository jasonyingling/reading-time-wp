<?php
/**
 * Reading Time Block Class
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
 * Reading Time block implementation.
 *
 * @since 3.0.0
 */
class Reading_Time_Block {

	/**
	 * Block name.
	 *
	 * @var string
	 */
	const BLOCK_NAME = 'reading-time-wp/reading-time';

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
	}

	/**
	 * Register the block.
	 *
	 * @since 3.0.0
	 */
	public function register() {
		// Only proceed if block editor exists.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Register block with server-side rendering.
		register_block_type(
			self::BLOCK_NAME,
			array(
				'attributes'      => $this->get_attributes(),
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Get block attributes.
	 *
	 * @return array Block attributes.
	 */
	private function get_attributes() {
		return array(
			'label'            => array(
				'type'    => 'string',
				'default' => $this->options['label'],
			),
			'postfix'          => array(
				'type'    => 'string',
				'default' => $this->options['postfix'],
			),
			'postfixSingular'  => array(
				'type'    => 'string',
				'default' => $this->options['postfix_singular'],
			),
			'textAlign'        => array(
				'type' => 'string',
			),
			'fontSize'         => array(
				'type' => 'string',
			),
			'customFontSize'   => array(
				'type' => 'number',
			),
			'textColor'        => array(
				'type' => 'string',
			),
			'customTextColor'  => array(
				'type' => 'string',
			),
			'backgroundColor'  => array(
				'type' => 'string',
			),
			'customBackgroundColor' => array(
				'type' => 'string',
			),
		);
	}

	/**
	 * Render the block.
	 *
	 * @since 3.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	public function render( $attributes ) {
		// Get current post ID.
		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return '';
		}

		// Calculate reading time.
		$reading_time = $this->calculator->calculate_reading_time( $post_id, $this->options );

		// Get label and postfix from attributes or options.
		$label            = ! empty( $attributes['label'] ) ? $attributes['label'] : $this->options['label'];
		$postfix          = ! empty( $attributes['postfix'] ) ? $attributes['postfix'] : $this->options['postfix'];
		$postfix_singular = ! empty( $attributes['postfixSingular'] ) ? $attributes['postfixSingular'] : $this->options['postfix_singular'];

		$calculated_postfix = $this->calculator->get_postfix( $reading_time, $postfix_singular, $postfix );

		// Build wrapper classes and styles.
		$wrapper_classes = array( 'wp-block-reading-time-wp-reading-time' );
		$wrapper_styles  = array();

		// Text alignment.
		if ( ! empty( $attributes['textAlign'] ) ) {
			$wrapper_classes[] = 'has-text-align-' . $attributes['textAlign'];
		}

		// Font size.
		if ( ! empty( $attributes['fontSize'] ) ) {
			$wrapper_classes[] = 'has-' . $attributes['fontSize'] . '-font-size';
		} elseif ( ! empty( $attributes['customFontSize'] ) ) {
			$wrapper_styles[] = 'font-size:' . $attributes['customFontSize'] . 'px';
		}

		// Text color.
		if ( ! empty( $attributes['textColor'] ) ) {
			$wrapper_classes[] = 'has-' . $attributes['textColor'] . '-color';
			$wrapper_classes[] = 'has-text-color';
		} elseif ( ! empty( $attributes['customTextColor'] ) ) {
			$wrapper_styles[]  = 'color:' . $attributes['customTextColor'];
			$wrapper_classes[] = 'has-text-color';
		}

		// Background color.
		if ( ! empty( $attributes['backgroundColor'] ) ) {
			$wrapper_classes[] = 'has-' . $attributes['backgroundColor'] . '-background-color';
			$wrapper_classes[] = 'has-background';
		} elseif ( ! empty( $attributes['customBackgroundColor'] ) ) {
			$wrapper_styles[]  = 'background-color:' . $attributes['customBackgroundColor'];
			$wrapper_classes[] = 'has-background';
		}

		$wrapper_attributes = sprintf(
			'class="%s"%s',
			esc_attr( implode( ' ', $wrapper_classes ) ),
			! empty( $wrapper_styles ) ? ' style="' . esc_attr( implode( ';', $wrapper_styles ) ) . '"' : ''
		);

		// Generate output.
		$output = sprintf(
			'<div %s><span class="rt-label rt-prefix">%s</span> <span class="rt-time">%s</span> <span class="rt-label rt-postfix">%s</span></div>',
			$wrapper_attributes,
			wp_kses_post( $label ),
			esc_html( $reading_time ),
			wp_kses_post( $calculated_postfix )
		);

		return $output;
	}
}
