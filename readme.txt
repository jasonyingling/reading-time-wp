=== Reading Time WP ===
Contributors: yingling017
Donate link: http://jasonyingling.me
Tags: reading time, estimated time, word count, time, posts, page, reading
Requires at least: 3.0.1
Tested up to: 4.7.1
Stable tag: 1.0.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Reading Time WP creates an estimated reading time of your posts that is inserted above the content or by using a shortcode.

== Description ==

WP Reading Time let's you easily add an estimated reading time to your WordPress posts. Activating the plugin will automatically add the reading time to the beginning of your post's content. This can be deactivated in the Reading Time settings which can be accessed from your Dashboard's Settings menu. You can also edit the label and postfix from this menu.

If you'd prefer more control over where you add your reading time you can use the the [rt_reading_time] shortcode to insert the time into a post. This shortcode also excepts values for label and postfix. These are optional. Ex. [rt_reading_time label="Reading Time:" postfix="minutes"].

== Installation ==

1. Upload the 'rt-reading-time-wp' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's it! Your reading time will now be inserted at the beginning of every post.
4. If you would like to edit settings or turn off reading time, select Reading Time from the WP Dashboard Settings menu

== Frequently Asked Questions ==

= How do I insert my reading time before posts. =

On initial installation your reading time should be showing where the_content is called in your template. If it is not, navigate to the Reading Time WP settings menu in the WP Dashboard Settings menu and make sure the "Insert Reading Time before content" option is checked.

= But I want to insert reading time wherever I want. How can I do that? =

Easy, turn off the "Insert Reading Time before content" option form the Reading Time settings within your WP Dashboard's settings. Then use the Reading Time WP shortcode [rt_reading_time label="Reading Time:" postfix="minutes"]. Best of all the label and postfix parameters are optional.

= That's good and all, but how do I insert it into my theme? =

Still easy, but you'll need to use WordPress' built in do_shortcode function. Simply place `<?php echo do_shortcode('[rt_reading_time label="Reading Time:" postfix="minutes"]'); ?>` into your theme wherever you please.

= I'll just go with it entering before the_content. How can I change what appears before and after the reading time? =

Just edit the Reading time label and Reading time postfix fields in the Reading Time WP Settings. The label appears before the time and the postfix after. Feel free to leave either blank to not use that field.


== Screenshots ==

1. An example of an estimated reading time entered before "the_content".
2. The options available in Reading Time WP.

== Changelog ==

= 1.0.8 =
* Added in singular postfix setting. Added in separate control to display reading time on excerpts.

= 1.0.7 =
* Switched to using span elements instead of divs for inserting before content and excerpt

= 1.0.6 =
* Updated the way the word count is calculated to be more accurate when using images and links

= 1.0.5 =
* Plugin tested for WordPress 4.1

= 1.0.4 =
* Minor fix to stable version tags, updating readme after fixes in 1.0.2 and 1.0.3

= 1.0.3 =
* Fixes issue with miscalculating the reading time when using <!--more--> tags and the_content. Also fixes issue with reading time appearing inline when using the_excerpt.

= 1.0.2 =
* Fixing bug with more tags in the_content

= 1.0.1 =
* Converting the plugin to a class based structure

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0 =
Initial release

= 1.0.1 =
This update converts the plugin into a class based structure for better expandability in the future.

= 1.0.2 =
Fixes issue with miscalculated reading time when using <!--more--> tags

= 1.0.3 =
Fixes issue with reading time appearing inline when using the_excerpt.

= 1.0.4 =
Updating stable version and readme files

= 1.0.5 =
Plugin tested for WordPress 4.1

= 1.0.6 =
Updated the way the word count is calculated to be more accurate when using images and links

= 1.0.7 =
Switched to using span elements instead of divs for inserting before content and excerpt

= 1.0.8 =
Added in singular postfix setting. Added in separate control to display reading time on excerpts.
