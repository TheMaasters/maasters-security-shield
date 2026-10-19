<?php
/**
 * Plugin Name: Maasters Security Shield
 * Description: Practical WordPress hardening with configurable security headers, CSP report-only mode, login rate limiting, XML-RPC controls, and privacy-aware settings.
 * Version: 1.1.5
 * Author: The Maasters Web Design Studio
 * Author URI: https://themaasters.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: maasters-security-shield
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'MSS_VERSION', '1.1.5' );
define( 'MSS_PATH', plugin_dir_path( __FILE__ ) );
define( 'MSS_URL', plugin_dir_url( __FILE__ ) );
define( 'MSS_BASENAME', plugin_basename( __FILE__ ) );

require_once MSS_PATH . 'includes/class-mss-core.php';
require_once MSS_PATH . 'includes/class-mss-headers.php';
require_once MSS_PATH . 'includes/class-mss-login-protection.php';
require_once MSS_PATH . 'includes/class-mss-hardening.php';
require_once MSS_PATH . 'includes/class-mss-admin.php';

function mss_run() {
    $core = new MSS_Core();
    $core->init();
}
add_action( 'plugins_loaded', 'mss_run' );
register_activation_hook( __FILE__, array( 'MSS_Core', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MSS_Core', 'deactivate' ) );
