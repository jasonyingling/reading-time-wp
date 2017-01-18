<?php
	$rtReadingOptions = get_option('rt_reading_time_options');

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

        $updateOptions = array(
        	'label' => $readingTimeLabel,
        	'postfix' => $readingTimePostfix,
            'postfix_singular' => $readingTimePostfixSingular,
			'wpm' => $readingTimeWPM,
			'before_content' => $readingTimeCheck,
            'before_excerpt' => $readingTimeCheckExcerpt,
        );

        update_option('rt_reading_time_options', $updateOptions);

        ?>
        <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
        <?php
    } else {
        //Normal page display
        $readingTimeLabel = $rtReadingOptions['label'];
        $readingTimePostfix = $rtReadingOptions['postfix'];
        $readingTimePostfixSingular = $rtReadingOptions['postfix_singular'];
        $readingTimeWPM = $rtReadingOptions['wpm'];
        $readingTimeCheck = $rtReadingOptions['before_content'];
        $readingTimeCheckExcerpt = $rtReadingOptions['before_excerpt'];
    }
?>

<div class="wrap">
    <?php    echo "<h2>" . __( 'Reading Time WP Settings', 'speedreadout_trdom' ) . "</h2>"; ?>

    <form name="rt_reading_time_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    	<input type="hidden" name="rt_reading_time_hidden" value="Y">
        <?php    echo "<h4>" . __( 'Reading Time Settings', 'rt_reading_time_trdom' ) . "</h4>"; ?>

        <p><?php _e("Reading time label: "); ?><input type="text" name="rt_reading_time_label" value="<?php echo $readingTimeLabel; ?>" size="20"><?php _e(" This value appears before the reading time. Leave blank for none."); ?></p>

        <p><?php _e("Reading time postfix: "); ?><input type="text" name="rt_reading_time_postfix" value="<?php echo $readingTimePostfix; ?>" size="20"><?php _e(" This value appears after the reading time. Leave blank for none."); ?></p>
        <p><?php _e("Reading time postfix singular: "); ?><input type="text" name="rt_reading_time_postfix_singular" value="<?php echo $readingTimePostfixSingular; ?>" size="20"><?php _e(" This value appears after the reading time, when lecture time is 1 minute."); ?></p>

		<p><?php _e("Words per minute: "); ?><input type="text" name="rt_reading_time_wpm" value="<?php echo $readingTimeWPM; ?>" size="20"><?php _e(" (defaults to 300, the average reading speed for adults)"); ?></p>

		<p><?php _e("Insert Reading Time before content: "); ?><input type="checkbox" name="rt_reading_time_check" <?php if ($readingTimeCheck === 'true') { echo 'checked'; } ?> size="20"><?php _e(""); ?></p>
        <p><?php _e("Insert Reading Time before excerpt: "); ?><input type="checkbox" name="rt_reading_time_check_excerpt" <?php if ($readingTimeCheckExcerpt === 'true') { echo 'checked'; } ?> size="20"><?php _e(""); ?></p>

        <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Update Options', 'rt_reading_time_trdom' ) ?>" />
        </p>
    </form>

    <div class="rt-shortcode-hint">
	    <p>Shortcode: [rt_reading_time label="Reading Time:" postfix="minutes" postfix_singular="minute"]</p>
	    <p>Or simply use [rt_reading_time] to return the number with no labels.</p>
	    <p>Want to insert the reading time into your theme? Use do_shortcode('[rt_reading_rtime]')</p>
    </div>
</div>
