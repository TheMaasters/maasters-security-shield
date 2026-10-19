<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class MSS_Core {
    public static function defaults() {
        return array(
            'headers_enabled' => 1,
            'hsts_enabled' => 0,
            'hsts_max_age' => 31536000,
            'hsts_subdomains' => 0,
            'hsts_preload' => 0,
            'csp_mode' => 'disabled',
            'csp_policy' => "default-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'",
            'login_protection' => 1,
            'max_attempts' => 5,
            'lockout_minutes' => 15,
            'xmlrpc_disabled' => 1,
            'hide_version' => 1,
            'disable_author_enum' => 1,
        );
    }
    public static function options() {
        $saved = get_option( 'mss_settings', array() );
        return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
    }
    public static function activate() {
        if ( false === get_option( 'mss_settings', false ) ) {
            add_option( 'mss_settings', self::defaults() );
        } else {
            update_option( 'mss_settings', wp_parse_args( get_option( 'mss_settings', array() ), self::defaults() ) );
        }
    }
    public static function deactivate() {
        // Settings are intentionally retained on deactivation.
    }
    public function init() {
        $options = self::options();
        ( new MSS_Headers() )->init();
        ( new MSS_Hardening() )->init();
        if ( ! empty( $options['login_protection'] ) ) {
            ( new MSS_Login_Protection() )->init();
        }
        if ( is_admin() ) {
            ( new MSS_Admin() )->init();
        }
    }
}
