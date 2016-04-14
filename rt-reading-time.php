<?php
/**
 * Plugin Name: Reading Time WP
 * Plugin URI: http://jasonyingling.me/reading-time-wp/
 * Description: Add an estimated reading time to your posts.
 * Version: 1.0.7
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
			'wpm' => 300,
			'before_content' => 'true',
		);

		$rtReadingOptions = get_option('rt_reading_time_options');

		add_shortcode( 'rt_reading_time', array($this, 'rt_reading_time') );
		add_option('rt_reading_time_options', $defaultSettings);
		add_action('admin_menu', array($this, 'rt_reading_time_admin_actions'));

		if ($rtReadingOptions['before_content'] === 'true') {

			add_filter('the_content', array($this, 'rt_add_reading_time_before_content'));

			add_filter('get_the_excerpt', array($this, 'rt_add_reading_time_before_excerpt'));

		}
	}

	public function rt_calculate_reading_time($rtPostID, $rtOptions) {

		$rtContent = get_post_field('post_content', $rtPostID);
		$strippedContent = strip_shortcodes($rtContent);
		$stripTagsContent = strip_tags($strippedContent);
		$wordCount = str_word_count($stripTagsContent);
		$this->readingTime = ceil($wordCount / $rtOptions['wpm']);

		return $this->readingTime;

	}

	public function rt_reading_time($atts, $content = null) {

		extract (shortcode_atts(array(
			'label' => '',
			'postfix' => '',
		), $atts));

		$rtReadingOptions = get_option('rt_reading_time_options');

		$rtPost = get_the_ID();

		$this->rt_calculate_reading_time($rtPost, $rtReadingOptions);

		return "
		<span class='span-reading-time'>$label $this->readingTime $postfix</span>
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
		if (get_post_type() === 'post') {
			$rtReadingOptions = get_option('rt_reading_time_options');

			$originalContent = $content;
			$rtPost = get_the_ID();

			$this->rt_calculate_reading_time($rtPost, $rtReadingOptions);

			$label = $rtReadingOptions['label'];
			$postfix = $rtReadingOptions['postfix'];

			if(in_array('get_the_excerpt', $GLOBALS['wp_current_filter'])) {
				return $content;
			}

			$content = '<span class="rt-reading-time" style="display: block;">'.'<span class="rt-label">'.$label.'</span>'.'<span class="rt-time">'.$this->readingTime.'</span>'.'<span class="rt-label"> '.$postfix.'</span>'.'</span>';
			$content .= $originalContent;
		}
        return $content;
	}

	public function rt_add_reading_time_before_excerpt($content) {
		if (get_post_type() === 'post') {
			$rtReadingOptions = get_option('rt_reading_time_options');

			$originalContent = $content;
			$rtPost = get_the_ID();

			$this->rt_calculate_reading_time($rtPost, $rtReadingOptions);

			$label = $rtReadingOptions['label'];
			$postfix = $rtReadingOptions['postfix'];

			$content = '<span class="rt-reading-time" style="display: block;">'.'<span class="rt-label">'.$label.'</span>'.'<span class="rt-time">'.$this->readingTime.'</span>'.'<span class="rt-label"> '.$postfix.'</span>'.'</span>';
			$content .= $originalContent;
		}
		return $content;
	}

}

$readingTimeWP = new readingTimeWP();

?>
