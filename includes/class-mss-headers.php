<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class MSS_Headers {
    public function init() {
        add_action( 'send_headers', array( $this, 'send_security_headers' ) );
    }

    public function send_security_headers() {
        $options = MSS_Core::options();
        if ( headers_sent() ) {
            return;
        }

        if ( ! empty( $options['headers_enabled'] ) ) {
            header( 'X-Frame-Options: SAMEORIGIN' );
            header( 'X-Content-Type-Options: nosniff' );
            header( 'Referrer-Policy: strict-origin-when-cross-origin' );
            header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
        }

        // HSTS and CSP are independent options; disabling baseline headers must not silently disable them.
        if ( ! empty( $options['hsts_enabled'] ) && is_ssl() ) {
            $max_age = max( 0, absint( $options['hsts_max_age'] ) );
            $value   = 'max-age=' . $max_age;
            if ( ! empty( $options['hsts_subdomains'] ) ) {
                $value .= '; includeSubDomains';
            }
            if ( ! empty( $options['hsts_preload'] ) ) {
                $value .= '; preload';
            }
            header( 'Strict-Transport-Security: ' . $value );
        }

        if ( in_array( $options['csp_mode'], array( 'report-only', 'enforce' ), true ) ) {
            $policy = $this->sanitize_policy( $options['csp_policy'] );
            if ( '' !== $policy ) {
                $header_name = ( 'report-only' === $options['csp_mode'] )
                    ? 'Content-Security-Policy-Report-Only'
                    : 'Content-Security-Policy';
                header( $header_name . ': ' . $policy );
            }
        }
    }

    private function sanitize_policy( $policy ) {
        if ( ! is_string( $policy ) ) {
            return '';
        }

        // Replace header control characters, including line breaks, before using a stored policy.
        $policy = preg_replace( '/[\x00-\x1F\x7F]+/', ' ', $policy );
        if ( ! is_string( $policy ) || strlen( $policy ) > 2000 ) {
            return '';
        }

        return trim( $policy );
    }
}
