<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class MSS_Login_Protection {
    public function init() {
        add_filter( 'authenticate', array( $this, 'check_lockout' ), 30, 3 );
        add_action( 'wp_login_failed', array( $this, 'log_failed' ) );
        add_action( 'wp_login', array( $this, 'clear_on_success' ), 10, 2 );
    }

    private function get_ip() {
        // REMOTE_ADDR is intentionally used; forwarded headers are untrusted unless a proxy is explicitly configured.
        if ( empty( $_SERVER['REMOTE_ADDR'] ) || ! is_string( $_SERVER['REMOTE_ADDR'] ) ) {
            return 'unknown';
        }

        $raw = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        return filter_var( $raw, FILTER_VALIDATE_IP ) ? $raw : 'unknown';
    }

    private function transient_key( $ip ) {
        return 'mss_failed_' . md5( $ip );
    }

    public function check_lockout( $user, $username, $password ) {
        if ( ! is_string( $username ) || '' === trim( $username ) ) {
            return $user;
        }

        $data = get_transient( $this->transient_key( $this->get_ip() ) );
        if ( ! is_array( $data ) || ! isset( $data['attempts'] ) ) {
            return $user;
        }

        $now = time();
        $locked_until = isset( $data['locked_until'] ) ? absint( $data['locked_until'] ) : 0;

        // Support transient records written by version 1.1.3 until they expire.
        if ( 0 === $locked_until && isset( $data['expires'] ) && absint( $data['attempts'] ) >= $this->max_attempts() ) {
            $locked_until = absint( $data['expires'] );
        }

        if ( $locked_until > $now ) {
            return new WP_Error(
                'mss_locked',
                sprintf(
                    /* translators: %d: number of minutes until login retry is allowed. */
                    __( 'Too many failed attempts. Try again in %d minutes.', 'maasters-security-shield' ),
                    (int) ceil( ( $locked_until - $now ) / 60 )
                )
            );
        }

        return $user;
    }

    private function max_attempts() {
        $options = MSS_Core::options();
        return max( 1, min( 20, absint( $options['max_attempts'] ) ) );
    }

    private function lockout_seconds() {
        $options = MSS_Core::options();
        return max( 1, min( 1440, absint( $options['lockout_minutes'] ) ) ) * MINUTE_IN_SECONDS;
    }

    public function log_failed( $username ) {
        $key  = $this->transient_key( $this->get_ip() );
        $data = get_transient( $key );
        $now  = time();

        if ( is_array( $data ) && isset( $data['attempts'], $data['locked_until'] ) && absint( $data['locked_until'] ) > $now ) {
            // Do not extend a lockout each time another blocked request arrives.
            return;
        }

        // Preserve a still-active legacy lockout created before version 1.1.4.
        if ( is_array( $data ) && ! isset( $data['locked_until'] ) && isset( $data['attempts'], $data['expires'] ) && absint( $data['attempts'] ) >= $this->max_attempts() && absint( $data['expires'] ) > $now ) {
            return;
        }

        $window_expires = is_array( $data ) && isset( $data['window_expires'] )
            ? absint( $data['window_expires'] )
            : 0;

        if ( ! is_array( $data ) || ! isset( $data['attempts'] ) || $window_expires <= $now ) {
            // Start a fresh fixed window once the previous attempt window has expired.
            $data = array(
                'attempts'       => 0,
                'window_expires' => $now + $this->lockout_seconds(),
                'locked_until'   => 0,
            );
        }

        $data['attempts'] = min( 1000, absint( $data['attempts'] ) + 1 );
        if ( $data['attempts'] >= $this->max_attempts() ) {
            // The full lockout duration starts when the attempt threshold is reached.
            $data['locked_until'] = $now + $this->lockout_seconds();
        }

        $expires = max( absint( $data['window_expires'] ), absint( $data['locked_until'] ) );
        set_transient( $key, $data, max( 1, $expires - $now ) );
    }

    public function clear_on_success( $user_login, $user ) {
        delete_transient( $this->transient_key( $this->get_ip() ) );
    }
}
