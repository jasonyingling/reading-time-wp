<?php
    $rtReadingOptions = get_option('rt_reading_time_options');

    $rtwp_post_type_args = array(
        'public' => true,
    );

    $rtwp_post_type_args = apply_filters('rtwp_post_type_args', $rtwp_post_type_args );

    $rtwp_post_types = get_post_types( $rtwp_post_type_args );

    if( isset($_POST['rt_reading_time_hidden']) && $_POST['rt_reading_time_hidden'] == 'Y' ) {
        //Form data sent
        $readingTimeLabel = $_POST['rt_reading_time_label'];
        $readingTimePostfix = $_POST['rt_reading_time_postfix'];
        $readingTimePostfixSingular = $_POST['rt_reading_time_postfix_singular'];
        $readingTimeWPM = $_POST['rt_reading_time_wpm'];
        if ($_POST['rt_reading_time_check']) {
	        $readingTimeCheck = 'true';
        } else {
	        $readingTimeCheck = 'false';
        }

        if ( isset($_POST['rt_reading_time_check_excerpt']) && $_POST['rt_reading_time_check_excerpt'] ) {
            $readingTimeCheckExcerpt = 'true';
        } else {
            $readingTimeCheckExcerpt = 'false';
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

        $updateOptions = array(
        	'label' => $readingTimeLabel,
        	'postfix' => $readingTimePostfix,
            'postfix_singular' => $readingTimePostfixSingular,
			'wpm' => $readingTimeWPM,
			'before_content' => $readingTimeCheck,
            'before_excerpt' => $readingTimeCheckExcerpt,
            'exclude_images' => $reading_time_exclude_images,
            'post_types' => $reading_time_post_types,
            'include_shortcodes' => $reading_time_shortcodes,
        );

        update_option('rt_reading_time_options', $updateOptions);

        ?>
        <div class="updated"><p><strong><?php _e('Options saved.', 'reading-time-wp' ); ?></strong></p></div>
        <?php
    } else {
        //Normal page display
        $readingTimeLabel = $rtReadingOptions['label'];
        $readingTimePostfix = $rtReadingOptions['postfix'];
        $readingTimePostfixSingular = $rtReadingOptions['postfix_singular'];
        $readingTimeWPM = $rtReadingOptions['wpm'];
        $readingTimeCheck = $rtReadingOptions['before_content'];
        $readingTimeCheckExcerpt = $rtReadingOptions['before_excerpt'];
        $reading_time_exclude_images = $rtReadingOptions['exclude_images'];
        if ( isset( $rtReadingOptions['post_types'] ) ) {
            $reading_time_post_types = $rtReadingOptions['post_types'];
        } else {
            // set defaults that have always been there for backwards compat until users set their own
            $reading_time_post_types = array();

            foreach ( $rtwp_post_types as $post_type_option ) {
                if ( $post_type_option === 'attachment' ) {
                    continue;
                }
                $reading_time_post_types[$post_type_option] = true;
            }
        }
        $reading_time_shortcodes = $rtReadingOptions['include_shortcodes'];
    }
?>

<div class="wrap">
    <?php echo "<h2>" . __( 'Reading Time WP Settings', 'reading-time-wp' ) . "</h2>"; ?>

    <form name="rt_reading_time_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    	<input type="hidden" name="rt_reading_time_hidden" value="Y">
        <?php echo "<h4>" . __( 'Reading Time Settings', 'reading-time-wp' ) . "</h4>"; ?>

        <p><?php _e("Reading time label: ", "reading-time-wp"); ?><input type="text" name="rt_reading_time_label" value="<?php echo $readingTimeLabel; ?>" size="20"><?php _e(" This value appears before the reading time. Leave blank for none.", "reading-time-wp"); ?></p>

        <p><?php _e("Reading time postfix: ", "reading-time-wp"); ?><input type="text" name="rt_reading_time_postfix" value="<?php echo $readingTimePostfix; ?>" size="20"><?php _e(" This value appears after the reading time. Leave blank for none.", "reading-time-wp"); ?></p>
        <p><?php _e("Reading time postfix singular: ", "reading-time-wp"); ?><input type="text" name="rt_reading_time_postfix_singular" value="<?php echo $readingTimePostfixSingular; ?>" size="20"><?php _e(" This value appears after the reading time, when lecture time is 1 minute.", "reading-time-wp"); ?></p>

		<p><?php _e("Words per minute: ", "reading-time-wp"); ?><input type="text" name="rt_reading_time_wpm" value="<?php echo $readingTimeWPM; ?>" size="20"><?php _e(" (defaults to 300, the average reading speed for adults)", "reading-time-wp"); ?></p>

		<p><?php _e("Insert Reading Time before content: ", "reading-time-wp"); ?><input type="checkbox" name="rt_reading_time_check" <?php if ($readingTimeCheck === 'true') { echo 'checked'; } ?> size="20"></p>
        <p><?php _e("Insert Reading Time before excerpt: ", "reading-time-wp"); ?><input type="checkbox" name="rt_reading_time_check_excerpt" <?php if ($readingTimeCheckExcerpt === 'true') { echo 'checked'; } ?> size="20"></p>
		<p><?php _e("Exclude images from the reading time: ", "reading-time-wp"); ?><input type="checkbox" name="rt_reading_time_images" <?php if ($reading_time_exclude_images === true) { echo 'checked'; } ?> size="20"></p>
        <p><?php _e("Include shortcodes in the reading time: ", "reading-time-wp"); ?><input type="checkbox" name="rt_reading_time_shortcodes" <?php if ($reading_time_shortcodes === true) { echo 'checked'; } ?> size="20"></p>

        <h3><?php _e("Select Post Types to Display Reading Time On", "reading-time-wp"); ?></h3>
        
        <?php

            foreach ( $rtwp_post_types as $rtwp_post_type ) { ?>
                <p><?php echo __('Display on ', 'reading-time-wp') . $rtwp_post_type . ': '; ?><input type="checkbox" name="rt_reading_time_post_types[<?php echo $rtwp_post_type; ?>]" <?php if ( isset($reading_time_post_types[$rtwp_post_type]) && $reading_time_post_types[$rtwp_post_type] === true) { echo 'checked'; } ?> size="20"></p>
            <?php }
        ?>

        <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Update Options', 'reading-time-wp' ) ?>" />
        </p>
    </form>

    <div class="rt-shortcode-hint">
	    <p><?php echo __( 'Shortcode: <code>[rt_reading_time label="Reading Time:" postfix="minutes" postfix_singular="minute"]</code>', 'reading-time-wp' ); ?></p>
	    <p><?php echo __( 'Or simply use <code>[rt_reading_time]</code> to return the number with no labels.', 'reading-time-wp' ); ?></p>
	    <p><?php echo __( 'Want to insert the reading time into your theme? Use <code>do_shortcode(\'[rt_reading_time]\')</code>.', "reading-time-wp" ); ?></p>
    </div>
</div>
