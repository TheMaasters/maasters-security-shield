<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class MSS_Hardening {
    public function init() {
        $opt = MSS_Core::options();
        if ( ! empty( $opt['xmlrpc_disabled'] ) ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
            add_filter( 'wp_headers', array( $this, 'remove_pingback' ) );
        }
        if ( ! empty( $opt['hide_version'] ) ) {
            remove_action( 'wp_head', 'wp_generator' );
            add_filter( 'the_generator', '__return_empty_string' );
        }
        if ( ! empty( $opt['disable_author_enum'] ) ) {
            add_action( 'template_redirect', array( $this, 'block_author_enum' ) );
        }
    }
    public function remove_pingback( $headers ) {
        if ( is_array( $headers ) ) { unset( $headers['X-Pingback'] ); }
        return $headers;
    }
    public function block_author_enum() {
        if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { return; }
        // Query parameter detection only; this is not a form submission and requires no nonce.
        // WordPress parses public query vars before template_redirect; use its API instead of reading request globals.
        $author = get_query_var( 'author', '' );
        if ( is_scalar( $author ) ) {
            $author = sanitize_text_field( (string) $author );
            if ( '' !== $author ) {
                wp_safe_redirect( home_url( '/' ), 302 );
                exit;
            }
        }
    }
}
