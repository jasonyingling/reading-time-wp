<?php
/**
 * Calculator Class
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
 * Handles reading time calculations.
 *
 * @since 3.0.0
 */
class Calculator {

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Last calculated reading time.
	 *
	 * @var int|string
	 */
	private $reading_time;

	/**
	 * Constructor.
	 *
	 * @param array $options Plugin options.
	 */
	public function __construct( $options = array() ) {
		$this->options = $options;
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
	 * Calculate reading time for a post.
	 *
	 * @since 3.0.0
	 *
	 * @param int   $post_id Post ID.
	 * @param array $options Optional. Override options for this calculation.
	 * @return int|string Reading time in minutes or "< 1".
	 */
	public function calculate_reading_time( $post_id, $options = array() ) {
		// Merge with instance options.
		$options = wp_parse_args( $options, $this->options );

		// Get post content.
		$content = get_post_field( 'post_content', $post_id );

		// Count images.
		$number_of_images = substr_count( strtolower( $content ), '<img ' );

		// Strip shortcodes unless configured to include them.
		if ( ! isset( $options['include_shortcodes'] ) || ! $options['include_shortcodes'] ) {
			$content = strip_shortcodes( $content );
		}

		// Strip HTML tags.
		$content = wp_strip_all_tags( $content );

		// Count words.
		$word_count = count( preg_split( '/\s+/', $content ) );

		// Add time for images if not excluded.
		if ( isset( $options['exclude_images'] ) && ! $options['exclude_images'] ) {
			$wpm                         = isset( $options['wpm'] ) ? $options['wpm'] : 300;
			$additional_words_for_images = $this->calculate_image_time( $number_of_images, $wpm );
			$word_count                 += $additional_words_for_images;
		}

		/**
		 * Filter the word count before calculating reading time.
		 *
		 * @since 1.2.0
		 *
		 * @param int $word_count Word count.
		 */
		$word_count = apply_filters( 'rtwp_filter_wordcount', $word_count );

		// Calculate reading time.
		$wpm                = isset( $options['wpm'] ) ? $options['wpm'] : 300;
		$this->reading_time = $word_count / $wpm;

		// If the reading time is less than 1, return "< 1".
		if ( 1 > $this->reading_time ) {
			$this->reading_time = __( '< 1', 'reading-time-wp' );
		} else {
			$this->reading_time = ceil( $this->reading_time );
		}

		return $this->reading_time;
	}

	/**
	 * Calculate additional reading time for images.
	 *
	 * Based on calculations by Medium.
	 * @link https://blog.medium.com/read-time-and-you-bc2048ab620c
	 *
	 * @since 3.0.0
	 *
	 * @param int $total_images Number of images in post.
	 * @param int $wpm Words per minute.
	 * @return int Additional time added by images (in word equivalents).
	 */
	public function calculate_image_time( $total_images, $wpm ) {
		$additional_time = 0;

		// For the first image add 12 seconds, second image add 11, ..., for image 10+ add 3 seconds.
		for ( $i = 1; $i <= $total_images; $i++ ) {
			if ( $i >= 10 ) {
				$additional_time += 3 * (int) $wpm / 60;
			} else {
				$additional_time += ( 12 - ( $i - 1 ) ) * (int) $wpm / 60;
			}
		}

		return $additional_time;
	}

	/**
	 * Get the appropriate postfix for reading time.
	 *
	 * @since 3.0.0
	 *
	 * @param string|int $time Reading time value.
	 * @param string     $singular Singular postfix.
	 * @param string     $multiple Plural postfix.
	 * @return string Postfix text.
	 */
	public function get_postfix( $time, $singular, $multiple ) {
		if ( (int) $time > 1 ) {
			$postfix = $multiple;
		} else {
			$postfix = $singular;
		}

		/**
		 * Filter the postfix text.
		 *
		 * @since 2.0.5
		 *
		 * @param string     $postfix Calculated postfix.
		 * @param string|int $time Reading time value.
		 * @param string     $singular Singular postfix.
		 * @param string     $multiple Plural postfix.
		 */
		$postfix = apply_filters( 'rt_edit_postfix', $postfix, $time, $singular, $multiple );

		return $postfix;
	}

	/**
	 * Generate reading time output HTML.
	 *
	 * @since 3.0.0
	 *
	 * @param string     $label Label text.
	 * @param string|int $reading_time Reading time value.
	 * @param string     $postfix Postfix text.
	 * @param string     $style Optional. Inline style attribute.
	 * @return string HTML output.
	 */
	public function generate_output( $label, $reading_time, $postfix, $style = 'display: block;' ) {
		$output = sprintf(
			'<span class="span-reading-time rt-reading-time" style="%s"><span class="rt-label rt-prefix">%s</span> <span class="rt-time">%s</span> <span class="rt-label rt-postfix">%s</span></span>',
			esc_attr( $style ),
			wp_kses_post( $label ),
			esc_html( $reading_time ),
			wp_kses_post( $postfix )
		);

		/**
		 * Filter the reading time output HTML.
		 *
		 * @since 2.0.0
		 *
		 * @param string     $output HTML output.
		 * @param string     $label Label text.
		 * @param string|int $reading_time Reading time value.
		 * @param string     $postfix Postfix text.
		 */
		$output = apply_filters( 'rtwp_filter_reading_time_output', $output, $label, $reading_time, $postfix );

		return $output;
	}

	/**
	 * Get last calculated reading time.
	 *
	 * @since 3.0.0
	 *
	 * @return int|string Reading time.
	 */
	public function get_reading_time() {
		return $this->reading_time;
	}
}
