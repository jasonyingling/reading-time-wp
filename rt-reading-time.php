<?php
/**
 * Plugin Name: Reading Time WP
 * Plugin URI: http://jasonyingling.me/reading-time-wp/
 * Description: Add an estimated reading time to your posts.
 * Version: 1.1.0
 * Author: Jason Yingling
 * Author URI: http://jasonyingling.me
 * License: GPL2
 */

 /*  Copyright 2016  Jason Yingling  (email : yingling017@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class readingTimeWP {

	// Add label option using add_option if it does not already exist
	public $readingTime;

	public function __construct() {
		$defaultSettings = array(
			'label' => 'Reading Time: ',
			'postfix' => 'minutes',
			'postfix_singular' => 'minute',
			'wpm' => 300,
			'before_content' => 'true',
			'before_excerpt' => 'true',
			'exclude_images' => false
		);

		$rtReadingOptions = get_option('rt_reading_time_options');

		add_shortcode( 'rt_reading_time', array($this, 'rt_reading_time') );
		add_option('rt_reading_time_options', $defaultSettings);
		add_action('admin_menu', array($this, 'rt_reading_time_admin_actions'));

		if ( isset($rtReadingOptions['before_content']) && $rtReadingOptions['before_content'] === 'true' ) {
			add_filter('the_content', array($this, 'rt_add_reading_time_before_content'));
		}

		if( isset($rtReadingOptions['before_excerpt']) && $rtReadingOptions['before_excerpt'] === 'true' ) {
			add_filter('get_the_excerpt', array($this, 'rt_add_reading_time_before_excerpt'));
		}

	}

	public function rt_calculate_reading_time($rtPostID, $rtOptions) {

		$rtContent = get_post_field('post_content', $rtPostID);
		$number_of_images = substr_count(strtolower($rtContent), '<img ');
		$strippedContent = strip_shortcodes($rtContent);
		$stripTagsContent = strip_tags($strippedContent);
		$wordCount = str_word_count($stripTagsContent);

		if ( isset($rtOptions['exclude_images'] ) && $rtOptions['exclude_images'] ) {
			// Don't calculate images if they've been set to be excluded
		} else {
			// Calculate additional time added to post by images
			$additional_words_for_images = $this->rt_calculate_images( $number_of_images, $rtOptions['wpm'] );
			$wordCount += $additional_words_for_images;
		}

		$this->readingTime = ceil($wordCount / $rtOptions['wpm']);

		// If the reading time is 0 then return it as < 1 instead of 0.
		if ( $this->readingTime < 1 ) {
			$this->readingTime = __('< 1', 'reading-time-wp');
		}

		return $this->readingTime;

	}

	/**
	 * Adds additional reading time for images
	 *
	 * Calculate additional reading time added by images in posts. Based on calculations by Medium. https://blog.medium.com/read-time-and-you-bc2048ab620c
	 *
	 * @since 1.1.0
	 *
	 * @param int $total_images number of images in post
	 * @param array $wpm words per minute
	 * @return int Additional time added to the reading time by images
	 */
	public function rt_calculate_images( $total_images, $wpm ) {
		$additional_time = 0;
		// For the first image add 12 seconds, second image add 11, ..., for image 10+ add 3 seconds
		for ( $i = 1; $i <= $total_images; $i++ ) {
			if ( $i >= 10 ) {
				$additional_time += 3 * (int) $wpm / 60;
			} else {
				$additional_time += (12 - ($i - 1) ) * (int) $wpm / 60;
			}
		}

		return $additional_time;
	}

	public function rt_reading_time($atts, $content = null) {

		extract (shortcode_atts(array(
			'label' => '',
			'postfix' => '',
			'postfix_singular' => '',
		), $atts));

		$rtReadingOptions = get_option('rt_reading_time_options');

		$rtPost = get_the_ID();

		$this->rt_calculate_reading_time($rtPost, $rtReadingOptions);

		if($this->readingTime > 1) {
			$calculatedPostfix = $postfix;
		} else {
			$calculatedPostfix = $postfix_singular;
		}

		return "
		<span class='span-reading-time'>$label $this->readingTime $calculatedPostfix</span>
		";
	}

	// Functions to create Reading Time admin pages
	public function rt_reading_time_admin() {
	    include('rt-reading-time-admin.php');
	}

	public function rt_reading_time_admin_actions() {
		add_options_page("Reading Time WP Settings", "Reading Time WP", "manage_options", "rt-reading-time-settings", array($this, "rt_reading_time_admin"));
	}

    // Calculate reading time by running it through the_content
	public function rt_add_reading_time_before_content($content) {
		$rtReadingOptions = get_option('rt_reading_time_options');

		$originalContent = $content;
		$rtPost = get_the_ID();

		$this->rt_calculate_reading_time($rtPost, $rtReadingOptions);

		$label = $rtReadingOptions['label'];
		$postfix = $rtReadingOptions['postfix'];
		$postfix_singular = $rtReadingOptions['postfix_singular'];

		if(in_array('get_the_excerpt', $GLOBALS['wp_current_filter'])) {
			return $content;
		}

		if($this->readingTime > 1) {
			$calculatedPostfix = $postfix;
		} else {
			$calculatedPostfix = $postfix_singular;
		}

		$content = '<span class="rt-reading-time" style="display: block;">'.'<span class="rt-label">'.$label.'</span>'.'<span class="rt-time">'.$this->readingTime.'</span>'.'<span class="rt-label"> '.$calculatedPostfix.'</span>'.'</span>';
		$content .= $originalContent;
		return $content;
	}

	public function rt_add_reading_time_before_excerpt($content) {
		$rtReadingOptions = get_option('rt_reading_time_options');

		$originalContent = $content;
		$rtPost = get_the_ID();

		$this->rt_calculate_reading_time($rtPost, $rtReadingOptions);

		$label = $rtReadingOptions['label'];
		$postfix = $rtReadingOptions['postfix'];
		$postfix_singular = $rtReadingOptions['postfix_singular'];

		if($this->readingTime > 1) {
			$calculatedPostfix = $postfix;
		} else {
			$calculatedPostfix = $postfix_singular;
		}


		$content = '<span class="rt-reading-time" style="display: block;">'.'<span class="rt-label">'.$label.'</span>'.'<span class="rt-time">'.$this->readingTime.'</span>'.'<span class="rt-label"> '.$calculatedPostfix.'</span>'.'</span>';
		$content .= $originalContent;
		return $content;
	}

}

$readingTimeWP = new readingTimeWP();

?>
