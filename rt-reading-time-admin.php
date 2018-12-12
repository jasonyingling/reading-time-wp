<?php
/**
 * Functions for building out the Reading Time settings page.
 *
 * @package Reading_Time_WP
 */

$rt_reading_time_options = get_option( 'rt_reading_time_options' );

$rtwp_post_type_args = array(
	'public' => true,
);

$rtwp_post_type_args = apply_filters( 'rtwp_post_type_args', $rtwp_post_type_args );

$rtwp_post_types = get_post_types( $rtwp_post_type_args );

if ( isset( $_POST['rt_reading_time_hidden'] ) && 'Y' == $_POST['rt_reading_time_hidden'] ) {
	// Check the nonce for the Reading Time.
	check_admin_referer( 'reading_time_settings' );
	// Form data sent.
	$reading_time_label            = $_POST['rt_reading_time_label'];
	$reading_time_postfix          = $_POST['rt_reading_time_postfix'];
	$reading_time_postfix_singular = $_POST['rt_reading_time_postfix_singular'];
	$reading_time_wpm              = $_POST['rt_reading_time_wpm'];

	if ( $_POST['rt_reading_time_check'] ) {
		$reading_time_check = 'true';
	} else {
		$reading_time_check = 'false';
	}

	if ( isset( $_POST['rt_reading_time_check_excerpt'] ) && $_POST['rt_reading_time_check_excerpt'] ) {
		$reading_time_check_excerpt = 'true';
	} else {
		$reading_time_check_excerpt = 'false';
	}

	if ( isset( $_POST['rt_reading_time_images'] ) && $_POST['rt_reading_time_images'] ) {
		$reading_time_exclude_images = true;
	} else {
		$reading_time_exclude_images = false;
	}

	if ( isset( $_POST['rt_reading_time_post_types'] ) ) {
		foreach ( $_POST['rt_reading_time_post_types'] as $key => $value ) {
			if ( $value ) {
				$reading_time_post_types[$key] = true;
			}
		}
	}

	if ( isset( $_POST['rt_reading_time_shortcodes'] ) && $_POST['rt_reading_time_shortcodes'] ) {
		$reading_time_shortcodes = true;
	} else {
		$reading_time_shortcodes = false;
	}

	$update_options = array(
		'label'              => $reading_time_label,
		'postfix'            => $reading_time_postfix,
		'postfix_singular'   => $reading_time_postfix_singular,
		'wpm'                => $reading_time_wpm,
		'before_content'     => $reading_time_check,
		'before_excerpt'     => $reading_time_check_excerpt,
		'exclude_images'     => $reading_time_exclude_images,
		'post_types'         => $reading_time_post_types,
		'include_shortcodes' => $reading_time_shortcodes,
	);

	update_option( 'rt_reading_time_options', $update_options );

	?>
	<div class="updated"><p><strong><?php echo esc_html( __( 'Options saved.', 'reading-time-wp' ) ); ?></strong></p></div>
	<?php
} else {
	// Normal page display.
	$reading_time_label            = $rt_reading_time_options['label'];
	$reading_time_postfix          = $rt_reading_time_options['postfix'];
	$reading_time_postfix_singular = $rt_reading_time_options['postfix_singular'];
	$reading_time_wpm              = $rt_reading_time_options['wpm'];
	$reading_time_check            = $rt_reading_time_options['before_content'];
	$reading_time_check_excerpt    = $rt_reading_time_options['before_excerpt'];
	$reading_time_exclude_images   = $rt_reading_time_options['exclude_images'];

	if ( isset( $rt_reading_time_options['post_types'] ) ) {
		$reading_time_post_types = $rt_reading_time_options['post_types'];
	} else {
		// set defaults that have always been there for backwards compat until users set their own.
		$reading_time_post_types = array();

		foreach ( $rtwp_post_types as $post_type_option ) {
			if ( 'attachment' === $post_type_option ) {
				continue;
			}
			$reading_time_post_types[ $post_type_option ] = true;
		}
	}
	if ( isset( $rt_reading_time_options['include_shortcodes'] ) ) {
		$reading_time_shortcodes = $rt_reading_time_options['include_shortcodes'];
	} else {
		$reading_time_shortcodes = false;
	}
}
?>

<div class="wrap">
	<?php echo '<h2>' . esc_html__( 'Reading Time WP Settings', 'reading-time-wp' ) . '</h2>'; ?>

	<form name="rt_reading_time_form" method="POST">
		<input type="hidden" name="rt_reading_time_hidden" value="Y">
		<?php wp_nonce_field( 'reading_time_settings' ); ?>
		<?php echo '<h4>' . esc_html__( 'Reading Time Settings', 'reading-time-wp' ) . '</h4>'; ?>

		<p><?php echo esc_html_e( 'Reading time label: ', 'reading-time-wp' ); ?><input type="text" name="rt_reading_time_label" value="<?php echo esc_attr( $reading_time_label ); ?>" size="20"><?php esc_html_e( ' This value appears before the reading time. Leave blank for none.', 'reading-time-wp' ); ?></p>

		<p><?php esc_html_e( 'Reading time postfix: ', 'reading-time-wp' ); ?><input type="text" name="rt_reading_time_postfix" value="<?php echo esc_attr( $reading_time_postfix ); ?>" size="20"><?php esc_html_e( ' This value appears after the reading time. Leave blank for none.', 'reading-time-wp' ); ?></p>
		<p><?php esc_html_e( 'Reading time postfix singular: ', 'reading-time-wp' ); ?><input type="text" name="rt_reading_time_postfix_singular" value="<?php echo esc_attr( $reading_time_postfix_singular ); ?>" size="20"><?php esc_html_e( ' This value appears after the reading time, when lecture time is 1 minute.', 'reading-time-wp' ); ?></p>

		<p><?php esc_html_e( 'Words per minute: ', 'reading-time-wp' ); ?><input type="text" name="rt_reading_time_wpm" value="<?php echo esc_attr( $reading_time_wpm ); ?>" size="20"><?php esc_html_e( ' (defaults to 300, the average reading speed for adults)', 'reading-time-wp' ); ?></p>

		<p><?php esc_html_e( 'Insert Reading Time before content: ', 'reading-time-wp' ); ?><input type="checkbox" name="rt_reading_time_check" <?php if ( 'true' === $reading_time_check ) { echo 'checked'; } ?> size="20"></p>
		<p><?php esc_html_e( 'Insert Reading Time before excerpt: ', 'reading-time-wp' ); ?><input type="checkbox" name="rt_reading_time_check_excerpt" <?php if ( 'true' === $reading_time_check_excerpt ) { echo 'checked'; } ?> size="20"></p>
		<p><?php esc_html_e( 'Exclude images from the reading time: ', 'reading-time-wp' ); ?><input type="checkbox" name="rt_reading_time_images" <?php if ( true === $reading_time_exclude_images ) { echo 'checked'; } ?> size="20"></p>
		<p><?php esc_html_e( 'Include shortcodes in the reading time: ', 'reading-time-wp' ); ?><input type="checkbox" name="rt_reading_time_shortcodes" <?php if ( true === $reading_time_shortcodes ) { echo 'checked'; } ?> size="20"></p>

		<h3><?php esc_html_e( 'Select Post Types to Display Reading Time On', 'reading-time-wp' ); ?></h3>

		<?php foreach ( $rtwp_post_types as $rtwp_post_type ) : ?>
			<p><?php echo esc_html( 'Display on ', 'reading-time-wp' ) . esc_html( $rtwp_post_type ) . ': '; ?><input type="checkbox" name="rt_reading_time_post_types[<?php echo esc_attr( $rtwp_post_type ); ?>]" <?php if ( isset( $reading_time_post_types[ $rtwp_post_type ] ) && $reading_time_post_types[ $rtwp_post_type ] === true) { echo 'checked'; } ?> size="20"></p>
		<?php endforeach; ?>

		<p class="submit">
		<input type="submit" name="Submit" value="<?php esc_html_e( 'Update Options', 'reading-time-wp' ); ?>" />
		</p>
	</form>

	<div class="rt-shortcode-hint">
		<p><?php echo wp_kses_post( __( 'Shortcode: <code>[rt_reading_time label="Reading Time:" postfix="minutes" postfix_singular="minute"]</code>', 'reading-time-wp' ) ); ?></p>
		<p><?php echo wp_kses_post( __( 'Or simply use <code>[rt_reading_time]</code> to return the number with no labels.', 'reading-time-wp' ) ); ?></p>
		<p><?php echo wp_kses_post( __( 'Want to insert the reading time into your theme? Use <code>do_shortcode(\'[rt_reading_time]\')</code>.', 'reading-time-wp' ) ); ?></p>
	</div>
</div>
