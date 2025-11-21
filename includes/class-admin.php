<?php
/**
 * Admin Class
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
 * Handles admin settings page.
 *
 * @since 3.0.0
 */
class Admin {

	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Allowed HTML tags for wp_kses.
	 *
	 * @var array
	 */
	private $kses_allowed;

	/**
	 * Constructor.
	 *
	 * @param array $options Plugin options.
	 */
	public function __construct( $options = array() ) {
		$this->options       = $options;
		$this->kses_allowed = array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'span'   => array(),
		);

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
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
	}

	/**
	 * Add settings page to admin menu.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Reading Time WP Settings', 'reading-time-wp' ),
			__( 'Reading Time WP', 'reading-time-wp' ),
			'manage_options',
			'rt-reading-time-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'reading-time-wp' ) );
		}

		// Get post types.
		$post_type_args = apply_filters(
			'rtwp_post_type_args',
			array( 'public' => true )
		);

		$post_types = get_post_types( $post_type_args, 'object' );

		// Handle form submission.
		if ( isset( $_POST['rt_reading_time_hidden'] ) &&
			check_admin_referer( 'reading_time_settings' ) &&
			'Y' === $_POST['rt_reading_time_hidden'] ) {

			$this->save_settings( $post_types );
			echo '<div class="updated"><p><strong>' . esc_html__( 'Options saved.', 'reading-time-wp' ) . '</strong></p></div>';
		}

		// Load current values.
		$this->options = get_option( 'rt_reading_time_options', $this->options );

		$label            = isset( $this->options['label'] ) ? esc_html( $this->options['label'] ) : '';
		$postfix          = isset( $this->options['postfix'] ) ? esc_html( $this->options['postfix'] ) : '';
		$postfix_singular = isset( $this->options['postfix_singular'] ) ? esc_html( $this->options['postfix_singular'] ) : '';
		$wpm              = isset( $this->options['wpm'] ) ? (float) $this->options['wpm'] : 300;
		$before_content   = isset( $this->options['before_content'] ) ? (bool) $this->options['before_content'] : false;
		$before_excerpt   = isset( $this->options['before_excerpt'] ) ? (bool) $this->options['before_excerpt'] : false;
		$exclude_images   = isset( $this->options['exclude_images'] ) ? (bool) $this->options['exclude_images'] : false;
		$include_shortcodes = isset( $this->options['include_shortcodes'] ) ? (bool) $this->options['include_shortcodes'] : false;

		// Handle post types with backwards compatibility.
		if ( isset( $this->options['post_types'] ) ) {
			$selected_post_types = $this->options['post_types'];
		} elseif ( ! isset( $this->options['post_types'] ) || null === $this->options['post_types'] ) {
			$selected_post_types = array();
		} else {
			// Set defaults for backwards compat.
			$selected_post_types = array();
			foreach ( $post_types as $post_type ) {
				if ( 'attachment' === $post_type->name ) {
					continue;
				}
				$selected_post_types[ $post_type->name ] = true;
			}
		}

		// Set variables for admin template (backwards compat).
		$reading_time_label            = $label;
		$reading_time_postfix          = $postfix;
		$reading_time_postfix_singular = $postfix_singular;
		$reading_time_wpm              = $wpm;
		$reading_time_check            = $before_content;
		$reading_time_check_excerpt    = $before_excerpt;
		$reading_time_exclude_images   = $exclude_images;
		$reading_time_shortcodes       = $include_shortcodes;
		$reading_time_post_types       = $selected_post_types;
		$rtwp_post_types               = $post_types;

		// Render the settings form.
		include dirname( __DIR__ ) . '/rt-reading-time-admin.php';
	}

	/**
	 * Save settings.
	 *
	 * @param array $post_types Available post types.
	 */
	private function save_settings( $post_types ) {
		$reading_time_label            = isset( $_POST['rt_reading_time_label'] ) ? wp_kses( wp_unslash( $_POST['rt_reading_time_label'] ), $this->kses_allowed ) : '';
		$reading_time_postfix          = isset( $_POST['rt_reading_time_postfix'] ) ? wp_kses( wp_unslash( $_POST['rt_reading_time_postfix'] ), $this->kses_allowed ) : '';
		$reading_time_postfix_singular = isset( $_POST['rt_reading_time_postfix_singular'] ) ? wp_kses( wp_unslash( $_POST['rt_reading_time_postfix_singular'] ), $this->kses_allowed ) : '';
		$reading_time_wpm              = isset( $_POST['rt_reading_time_wpm'] ) ? sanitize_text_field( wp_unslash( $_POST['rt_reading_time_wpm'] ) ) : '';
		$reading_time_check            = isset( $_POST['rt_reading_time_check'] ) ? true : false;
		$reading_time_check_excerpt    = isset( $_POST['rt_reading_time_check_excerpt'] ) ? true : false;
		$reading_time_exclude_images   = isset( $_POST['rt_reading_time_images'] ) ? true : false;
		$reading_time_shortcodes       = isset( $_POST['rt_reading_time_shortcodes'] ) ? true : false;

		$reading_time_post_types = array();
		if ( isset( $_POST['rt_reading_time_post_types'] ) ) {
			foreach ( $_POST['rt_reading_time_post_types'] as $key => $value ) {
				if ( $value ) {
					$reading_time_post_types[ sanitize_text_field( $key ) ] = true;
				}
			}
		}

		$update_options = array(
			'label'              => $reading_time_label,
			'postfix'            => $reading_time_postfix,
			'postfix_singular'   => $reading_time_postfix_singular,
			'wpm'                => (float) $reading_time_wpm,
			'before_content'     => $reading_time_check,
			'before_excerpt'     => $reading_time_check_excerpt,
			'exclude_images'     => $reading_time_exclude_images,
			'post_types'         => $reading_time_post_types,
			'include_shortcodes' => $reading_time_shortcodes,
		);

		update_option( 'rt_reading_time_options', $update_options );
		$this->options = $update_options;
	}

	/**
	 * Get allowed HTML for wp_kses.
	 *
	 * @return array
	 */
	public function get_kses_allowed() {
		return $this->kses_allowed;
	}
}
