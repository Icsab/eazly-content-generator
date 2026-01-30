<?php
/**
 * Plugin Name: Eazly Content Generator
 * Description: Generate dummy content for WordPress posts and custom post types
 * Version: 1.0.0
 * Author: Oortserv
 * Text Domain: eazly-content-generator
 * Domain Path: /languages
 * License: GPLv2 or later
 *License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
// Define plugin constants
define('EAZLY_CONTENT_GENERATOR_VERSION', '1.0.0');
define('EAZLY_CONTENT_GENERATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EAZLY_CONTENT_GENERATOR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EAZLY_CONTENT_GENERATOR_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once EAZLY_CONTENT_GENERATOR_PLUGIN_DIR . 'includes/eazly-admin-framework/admin-framework.php';
use Eazly\Admin\Framework\Admin_Assets;

Admin_Assets::init(
    plugin_dir_path( __FILE__ ) . 'includes/eazly-admin-framework/',
    plugin_dir_url( __FILE__ ) . 'includes/eazly-admin-framework/'
);
require_once EAZLY_CONTENT_GENERATOR_PLUGIN_DIR . 'includes/lorem-ipsum-generator.php';
require_once EAZLY_CONTENT_GENERATOR_PLUGIN_DIR . 'includes/class-content-generator.php';



// Initialize plugin
new Eazly_Content_Generator();
