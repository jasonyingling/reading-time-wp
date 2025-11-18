# Reading Time WP Plugin

## Overview

Reading Time WP is a WordPress plugin that calculates and displays estimated reading time for posts and other content types. It uses a research-based algorithm (based on Medium's approach) that accounts for both word count and images to provide accurate reading time estimates.

**Version:** 2.0.17
**Author:** Jason Yingling
**License:** GPL2
**Text Domain:** reading-time-wp

## Core Functionality

### Reading Time Calculation

The plugin calculates reading time based on:
- **Word count** from post content
- **Image count** using Medium's algorithm
- **Configurable words per minute** (WPM, default: 300)

#### Image Time Algorithm
- First image: 12 seconds
- Each subsequent image: decreases by 1 second
- Images 10+: 3 seconds each

### Display Options

Reading time can be displayed via:
1. **Automatic insertion** - Before post content or excerpts
2. **Shortcode** - `[rt_reading_time]` for manual placement
3. **Programmatic** - Direct function calls in theme templates

## File Structure

```
/reading-time-wp/
├── rt-reading-time.php              # Main plugin file
├── rt-reading-time-admin.php        # Admin settings UI
├── readme.txt                        # WordPress.org readme
├── LICENSE                           # GPL2 license
├── /includes/                        # OOP classes directory
│   ├── class-plugin.php             # Main plugin class
│   ├── class-calculator.php         # Reading time calculator
│   ├── class-admin.php              # Admin settings
│   ├── class-shortcode.php          # Shortcode handler
│   ├── class-content-filter.php     # Content/excerpt filters
│   └── /blocks/                     # Block editor classes
│       ├── class-blocks-manager.php # Block registration
│       └── class-reading-time-block.php # Reading time block
├── /blocks/                          # Block editor assets
│   └── /reading-time/               # Reading time block
│       ├── block.json               # Block metadata
│       ├── index.js                 # Block JS (build)
│       ├── style.css                # Frontend styles
│       └── editor.css               # Editor styles
├── /src/                             # Source files for blocks
│   └── /blocks/
│       └── /reading-time/
│           └── index.js             # Block source
├── /assets/                          # Plugin assets
├── /languages/                       # Translation files
└── /tests/                          # Unit tests (future)
```

## Architecture

### OOP Structure (v3.0+)

The plugin follows an object-oriented architecture with these components:

#### Core Classes

1. **`RTWP\Plugin`** (`/includes/class-plugin.php`)
   - Main plugin orchestrator
   - Initializes all components
   - Handles plugin activation/deactivation
   - Manages dependency injection

2. **`RTWP\Calculator`** (`/includes/class-calculator.php`)
   - Core reading time calculation logic
   - Image time calculation
   - Word counting with customizable options
   - Extensible via filters

3. **`RTWP\Admin`** (`/includes/class-admin.php`)
   - Admin settings page
   - Options saving and validation
   - Settings UI rendering
   - Admin notices

4. **`RTWP\Shortcode`** (`/includes/class-shortcode.php`)
   - Shortcode registration and handling
   - Attribute parsing
   - Output generation

5. **`RTWP\Content_Filter`** (`/includes/class-content-filter.php`)
   - Automatic content insertion
   - Excerpt handling
   - Post type filtering

6. **`RTWP\Blocks\Blocks_Manager`** (`/includes/blocks/class-blocks-manager.php`)
   - Block registration
   - Asset enqueueing
   - Block category management

7. **`RTWP\Blocks\Reading_Time_Block`** (`/includes/blocks/class-reading-time-block.php`)
   - Reading time block implementation
   - Server-side rendering
   - Block attributes and styles

### Backwards Compatibility

The plugin maintains 100% backwards compatibility with previous versions:

- **Legacy functions** remain available as wrappers
- **Existing hooks and filters** continue to work
- **Settings** migrate automatically
- **Shortcode syntax** unchanged
- **No user action required** after update

Legacy global instance `$reading_time_wp` is maintained for theme compatibility.

## Public API

### Functions

#### `rt_reading_time( $atts, $content )`
Shortcode handler for `[rt_reading_time]`.

**Parameters:**
- `$atts` (array) - Shortcode attributes
  - `label` - Text before reading time
  - `postfix` - Text after reading time (plural)
  - `postfix_singular` - Text after reading time (singular)
  - `post_id` - Specific post ID (optional)
- `$content` (string) - Shortcode content (unused)

**Returns:** (string) Formatted reading time HTML

**Example:**
```php
[rt_reading_time label="Read in:" postfix="mins"]
```

#### `rt_calculate_reading_time( $post_id, $options )`
Calculate reading time for a post.

**Parameters:**
- `$post_id` (int) - Post ID
- `$options` (array) - Calculation options
  - `wpm` - Words per minute
  - `exclude_images` - Exclude image time
  - `include_shortcodes` - Include shortcode content

**Returns:** (int|string) Reading time in minutes or "< 1"

#### `rt_add_postfix( $time, $singular, $multiple )`
Add postfix text to reading time.

**Parameters:**
- `$time` (int|string) - Reading time value
- `$singular` (string) - Singular postfix (e.g., "minute")
- `$multiple` (string) - Plural postfix (e.g., "minutes")

**Returns:** (string) Postfix text

### Filters

#### `rtwp_post_type_args`
Filter post type query arguments.

**Parameters:**
- `$args` (array) - Default: `array('public' => true)`

**Example:**
```php
add_filter('rtwp_post_type_args', function($args) {
    $args['show_ui'] = true;
    return $args;
});
```

#### `rtwp_filter_wordcount`
Filter word count before calculating reading time.

**Parameters:**
- `$word_count` (int) - Calculated word count

**Example:**
```php
add_filter('rtwp_filter_wordcount', function($count) {
    // Add custom field content
    global $post;
    $extra_content = get_post_meta($post->ID, 'extra_content', true);
    $extra_words = str_word_count(strip_tags($extra_content));
    return $count + $extra_words;
});
```

#### `rt_edit_postfix`
Filter postfix text.

**Parameters:**
- `$postfix` (string) - Final postfix text
- `$time` (int|string) - Reading time value
- `$singular` (string) - Singular postfix
- `$multiple` (string) - Plural postfix

**Example:**
```php
add_filter('rt_edit_postfix', function($postfix, $time) {
    if ($time < 5) {
        return 'min (quick read!)';
    }
    return $postfix;
}, 10, 2);
```

#### `rtwp_filter_reading_time_output`
Filter complete HTML output.

**Parameters:**
- `$output` (string) - HTML output
- `$label` (string) - Label text
- `$reading_time` (int|string) - Reading time value
- `$postfix` (string) - Postfix text

**Example:**
```php
add_filter('rtwp_filter_reading_time_output', function($output, $label, $time, $postfix) {
    return sprintf(
        '<div class="custom-reading-time">%s%s %s</div>',
        $label,
        $time,
        $postfix
    );
}, 10, 4);
```

### Actions

#### `rtwp_plugin_loaded`
Fired after plugin is fully loaded.

**Example:**
```php
add_action('rtwp_plugin_loaded', function() {
    // Plugin is ready
});
```

## Settings

### Stored in: `rt_reading_time_options`

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `label` | string | "Reading Time: " | Text before reading time |
| `postfix` | string | "minutes" | Text after reading time (plural) |
| `postfix_singular` | string | "minute" | Text after reading time (singular) |
| `wpm` | int | 300 | Words per minute reading speed |
| `before_content` | bool | true | Auto-insert before content |
| `before_excerpt` | bool | true | Auto-insert before excerpt |
| `exclude_images` | bool | false | Exclude image time from calculation |
| `include_shortcodes` | bool | false | Include shortcode content in word count |
| `post_types` | array | ['post'] | Post types to display on |

### Accessing Settings

```php
$options = get_option('rt_reading_time_options');
$wpm = isset($options['wpm']) ? $options['wpm'] : 300;
```

## HTML Output Structure

```html
<span class="span-reading-time rt-reading-time" style="display: block;">
    <span class="rt-label rt-prefix">Reading Time: </span>
    <span class="rt-time">5</span>
    <span class="rt-label rt-postfix">minutes</span>
</span>
```

### CSS Classes

- `.span-reading-time` - Wrapper span
- `.rt-reading-time` - Reading time specific class
- `.rt-label` - Label text wrapper
- `.rt-prefix` - Label before time
- `.rt-time` - Reading time value
- `.rt-postfix` - Label after time

## Block Editor Support

### Reading Time Block

**Block Name:** `reading-time-wp/reading-time`

**Attributes:**
- `label` (string) - Label text
- `postfix` (string) - Postfix text (plural)
- `postfixSingular` (string) - Postfix text (singular)
- `textAlign` (string) - Text alignment
- `fontSize` (string) - Font size preset
- `customFontSize` (number) - Custom font size
- `textColor` (string) - Text color preset
- `customTextColor` (string) - Custom text color

**Usage:**
Insert the "Reading Time" block from the block inserter. Configure display options in the block settings panel.

**Server-Side Rendering:**
The block uses dynamic rendering to calculate reading time on the server, ensuring accuracy with the latest post content.

## Internationalization

**Text Domain:** `reading-time-wp`
**Domain Path:** `/languages/`

### Included Translations
- Dutch (nl_NL)

### Translation Functions Used
- `__()` - Translate string
- `esc_html__()` - Translate and escape
- `esc_html_e()` - Translate, escape, and echo

## Development

### Requirements
- PHP 7.0+
- WordPress 5.0+
- Node.js and npm (for block development)

### Building Blocks

```bash
# Install dependencies
npm install

# Build blocks for production
npm run build

# Development mode with watch
npm run start
```

### Coding Standards
- WordPress Coding Standards
- PSR-4 autoloading for classes
- Namespaced classes under `RTWP\`
- Proper sanitization and escaping
- Nonce verification for forms

### Security Practices
- Direct file access prevention
- Nonce verification on form submissions
- Input sanitization with `sanitize_text_field()`, `wp_kses()`
- Output escaping with `esc_html()`, `esc_attr()`
- Prepared statements for database queries (if any)

## Testing

### Manual Testing Checklist
1. Reading time displays correctly on posts
2. Settings save and apply properly
3. Shortcode works with various attributes
4. Block inserts and renders correctly
5. Post type filtering works
6. Image calculation is accurate
7. Filters and hooks work as expected

### Future: Automated Testing
- Unit tests with PHPUnit
- Integration tests for WordPress
- JavaScript tests for blocks

## Common Customization Examples

### Change Reading Time for Custom Post Type Only

```php
add_filter('rtwp_filter_wordcount', function($count) {
    if (get_post_type() === 'recipe') {
        // Recipes take longer to read
        return $count * 1.5;
    }
    return $count;
});
```

### Add Reading Time to REST API

```php
add_action('rest_api_init', function() {
    register_rest_field('post', 'reading_time', array(
        'get_callback' => function($post) {
            $options = get_option('rt_reading_time_options');
            return rt_calculate_reading_time($post['id'], $options);
        },
        'schema' => array(
            'description' => 'Reading time in minutes',
            'type' => 'string'
        )
    ));
});
```

### Custom Template Tag

```php
// In your theme's functions.php
function mytheme_reading_time() {
    if (function_exists('rt_calculate_reading_time')) {
        $options = get_option('rt_reading_time_options');
        $time = rt_calculate_reading_time(get_the_ID(), $options);
        echo '<div class="reading-time">' .
             esc_html($time) . ' min read</div>';
    }
}

// In your template file
mytheme_reading_time();
```

## Troubleshooting

### Reading Time Not Displaying

1. Check if post type is enabled in settings
2. Verify "Before Content" or "Before Excerpt" is enabled
3. Check if theme is using `the_content()` filter
4. Ensure plugin is activated

### Incorrect Reading Time

1. Verify WPM setting (default: 300)
2. Check if shortcodes should be included
3. Verify image calculation setting
4. Test with `rtwp_filter_wordcount` filter

### Block Not Available

1. Ensure WordPress 5.0+
2. Clear block editor cache
3. Check browser console for errors
4. Verify block build files exist

## Changelog

### 3.0.0 (Upcoming)
- Refactored to OOP architecture
- Added block editor support
- Improved extensibility
- 100% backwards compatible
- Added namespace support

### 2.0.17
- Style tag fix for backwards compatibility
- Added filter to reading time

### Earlier Versions
See readme.txt for complete changelog

## Support and Contributing

**Support:** WordPress.org support forums
**Repository:** GitHub
**Issues:** Report bugs on GitHub

## License

GPL2 - See LICENSE file for details

## Credits

**Author:** Jason Yingling
**Reading Time Algorithm:** Based on Medium's research

---

*This documentation is maintained for use by AI assistants and developers working with the Reading Time WP plugin.*
