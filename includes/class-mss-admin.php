<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class MSS_Admin {
    public function init() {
        add_action( 'admin_menu', array( $this, 'menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }
    public function menu() {
        add_menu_page( __( 'Maasters Security Shield', 'maasters-security-shield' ), __( 'Maasters Shield', 'maasters-security-shield' ), 'manage_options', 'maasters-security-shield', array( $this, 'render' ), MSS_URL . 'assets/admin-menu-icon.png', 58 );
    }
    public function assets( $hook ) {
        if ( 'toplevel_page_maasters-security-shield' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'mss-admin', MSS_URL . 'assets/css/admin.css', array(), MSS_VERSION );
    }
    public function register_settings() {
        register_setting( 'mss_settings_group', 'mss_settings', array( 'sanitize_callback' => array( $this, 'sanitize' ) ) );
    }
    public function sanitize( $input ) {
        $input = is_array( $input ) ? $input : array();
        $defaults = MSS_Core::defaults();
        $out = array();
        foreach ( array( 'headers_enabled', 'hsts_enabled', 'hsts_subdomains', 'hsts_preload', 'login_protection', 'xmlrpc_disabled', 'hide_version', 'disable_author_enum' ) as $key ) {
            $value        = isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ? (string) $input[ $key ] : '';
            $out[ $key ] = ( '1' === $value ) ? 1 : 0;
        }

        $hsts_max_age = isset( $input['hsts_max_age'] ) && is_scalar( $input['hsts_max_age'] ) ? absint( $input['hsts_max_age'] ) : $defaults['hsts_max_age'];
        $max_attempts = isset( $input['max_attempts'] ) && is_scalar( $input['max_attempts'] ) ? absint( $input['max_attempts'] ) : $defaults['max_attempts'];
        $lockout_minutes = isset( $input['lockout_minutes'] ) && is_scalar( $input['lockout_minutes'] ) ? absint( $input['lockout_minutes'] ) : $defaults['lockout_minutes'];

        $out['hsts_max_age'] = max( 300, min( 63072000, $hsts_max_age ) );
        $out['max_attempts'] = max( 1, min( 20, $max_attempts ) );
        $out['lockout_minutes'] = max( 1, min( 1440, $lockout_minutes ) );
        $mode = isset( $input['csp_mode'] ) && is_string( $input['csp_mode'] ) ? sanitize_key( $input['csp_mode'] ) : 'disabled';
        $out['csp_mode'] = in_array( $mode, array( 'disabled', 'report-only', 'enforce' ), true ) ? $mode : 'disabled';
        $policy = isset( $input['csp_policy'] ) && is_string( $input['csp_policy'] ) ? wp_unslash( $input['csp_policy'] ) : $defaults['csp_policy'];
        $policy = preg_replace( '/[\r\n]+/', ' ', $policy );
        $out['csp_policy'] = ( strlen( $policy ) <= 2000 && false === strpos( $policy, "\0" ) ) ? sanitize_text_field( $policy ) : $defaults['csp_policy'];
        return $out;
    }
    private function toggle( $key, $label, $options ) {
        printf( '<label class="mss-toggle"><input type="checkbox" name="mss_settings[%1$s]" value="1" %2$s> <span>%3$s</span></label>', esc_attr( $key ), checked( ! empty( $options[ $key ] ), true, false ), esc_html( $label ) );
    }
    private function status( $enabled ) { echo $enabled ? '<span class="mss-status is-on">' . esc_html__( 'Enabled', 'maasters-security-shield' ) . '</span>' : '<span class="mss-status">' . esc_html__( 'Disabled', 'maasters-security-shield' ) . '</span>'; }
    public function render() {
        if ( ! current_user_can( 'manage_options' ) ) { return; }
        $o = MSS_Core::options();
        $checks = array(
            __( 'Security headers', 'maasters-security-shield' ) => ! empty( $o['headers_enabled'] ),
            __( 'Login rate limiting', 'maasters-security-shield' ) => ! empty( $o['login_protection'] ),
            __( 'XML-RPC disabled', 'maasters-security-shield' ) => ! empty( $o['xmlrpc_disabled'] ),
            __( 'WordPress version hidden', 'maasters-security-shield' ) => ! empty( $o['hide_version'] ),
            __( 'Author enumeration protection', 'maasters-security-shield' ) => ! empty( $o['disable_author_enum'] ),
            __( 'HSTS (HTTPS only)', 'maasters-security-shield' ) => ! empty( $o['hsts_enabled'] ),
        );
        $active = count( array_filter( $checks ) );
        ?>
        <div class="wrap mss-wrap">
            <header class="mss-header"><div class="mss-brand"><img class="mss-brand-logo" src="<?php echo esc_url( MSS_URL . 'assets/icon-transparent-256.png' ); ?>" alt="" width="58" height="58"><div><p class="mss-eyebrow"><?php esc_html_e( 'THE MAASTERS · WORDPRESS SECURITY', 'maasters-security-shield' ); ?></p><h1><?php esc_html_e( 'Security Shield', 'maasters-security-shield' ); ?> <span class="mss-version">v<?php echo esc_html( MSS_VERSION ); ?></span></h1><p><?php esc_html_e( 'Configure practical protections for your WordPress site.', 'maasters-security-shield' ); ?></p></div></div><div class="mss-score"><strong><?php echo esc_html( $active ); ?><small>/<?php echo esc_html( count( $checks ) ); ?></small></strong><span><?php esc_html_e( 'Protections enabled', 'maasters-security-shield' ); ?></span></div></header>
            <div class="mss-notice"><strong><?php esc_html_e( 'Before enabling strict policies', 'maasters-security-shield' ); ?></strong><p><?php esc_html_e( 'Back up your site and test changes on staging. HSTS and enforced CSP can affect visitors or integrations if configured incorrectly.', 'maasters-security-shield' ); ?></p></div>
            <form method="post" action="options.php" class="mss-form">
                <?php settings_fields( 'mss_settings_group' ); ?>
                <section class="mss-card"><h2><?php esc_html_e( 'Protection overview', 'maasters-security-shield' ); ?></h2><div class="mss-check-grid"><?php foreach ( $checks as $label => $enabled ) : ?><div class="mss-check-item"><span class="mss-dot <?php echo $enabled ? 'is-on' : ''; ?>"></span><span><?php echo esc_html( $label ); ?></span><?php $this->status( $enabled ); ?></div><?php endforeach; ?></div></section>
                <section class="mss-card"><h2><?php esc_html_e( 'Security headers', 'maasters-security-shield' ); ?></h2><p class="description"><?php esc_html_e( 'Baseline headers are sent on WordPress responses when enabled.', 'maasters-security-shield' ); ?></p><?php $this->toggle( 'headers_enabled', __( 'Enable baseline security headers', 'maasters-security-shield' ), $o ); ?><hr><h3><?php esc_html_e( 'HTTP Strict Transport Security (HSTS)', 'maasters-security-shield' ); ?></h3><?php $this->toggle( 'hsts_enabled', __( 'Enable HSTS on HTTPS requests', 'maasters-security-shield' ), $o ); ?><div class="mss-fields"><label><?php esc_html_e( 'Max age (seconds)', 'maasters-security-shield' ); ?><input type="number" min="300" max="63072000" name="mss_settings[hsts_max_age]" value="<?php echo esc_attr( $o['hsts_max_age'] ); ?>"></label><?php $this->toggle( 'hsts_subdomains', __( 'Include all subdomains', 'maasters-security-shield' ), $o ); ?><?php $this->toggle( 'hsts_preload', __( 'Add preload directive (only if eligible)', 'maasters-security-shield' ), $o ); ?></div><p class="mss-warning"><?php esc_html_e( 'Enable includeSubDomains or preload only when every affected hostname is permanently HTTPS-ready. The preload directive alone does not submit your domain to browser preload lists.', 'maasters-security-shield' ); ?></p></section>
                <section class="mss-card"><h2><?php esc_html_e( 'Content Security Policy (CSP)', 'maasters-security-shield' ); ?></h2><p class="description"><?php esc_html_e( 'Start with Report Only and test all themes and plugins before enforcement.', 'maasters-security-shield' ); ?></p><label class="mss-field-label" for="mss-csp-mode"><?php esc_html_e( 'CSP mode', 'maasters-security-shield' ); ?></label><select id="mss-csp-mode" name="mss_settings[csp_mode]"><option value="disabled" <?php selected( $o['csp_mode'], 'disabled' ); ?>><?php esc_html_e( 'Disabled', 'maasters-security-shield' ); ?></option><option value="report-only" <?php selected( $o['csp_mode'], 'report-only' ); ?>><?php esc_html_e( 'Report Only (recommended first)', 'maasters-security-shield' ); ?></option><option value="enforce" <?php selected( $o['csp_mode'], 'enforce' ); ?>><?php esc_html_e( 'Enforce (may block resources)', 'maasters-security-shield' ); ?></option></select><label class="mss-field-label" for="mss-csp-policy"><?php esc_html_e( 'CSP policy', 'maasters-security-shield' ); ?></label><textarea id="mss-csp-policy" name="mss_settings[csp_policy]" rows="3" maxlength="2000" class="large-text code"><?php echo esc_textarea( $o['csp_policy'] ); ?></textarea><p class="description"><?php esc_html_e( 'Default policy is a starting point only. Adjust it for your site’s scripts, styles, fonts, images, APIs, and embeds.', 'maasters-security-shield' ); ?></p></section>
                <section class="mss-card"><h2><?php esc_html_e( 'Login protection', 'maasters-security-shield' ); ?></h2><?php $this->toggle( 'login_protection', __( 'Enable failed-login rate limiting', 'maasters-security-shield' ), $o ); ?><div class="mss-fields"><label><?php esc_html_e( 'Maximum failed attempts', 'maasters-security-shield' ); ?><input type="number" min="1" max="20" name="mss_settings[max_attempts]" value="<?php echo esc_attr( $o['max_attempts'] ); ?>"></label><label><?php esc_html_e( 'Lockout window (minutes)', 'maasters-security-shield' ); ?><input type="number" min="1" max="1440" name="mss_settings[lockout_minutes]" value="<?php echo esc_attr( $o['lockout_minutes'] ); ?>"></label></div><p class="description"><?php esc_html_e( 'Attempts are grouped by REMOTE_ADDR. Forwarded proxy headers are not trusted. Users behind the same public IP may share a lockout.', 'maasters-security-shield' ); ?></p></section>
                <section class="mss-card"><h2><?php esc_html_e( 'WordPress hardening', 'maasters-security-shield' ); ?></h2><?php $this->toggle( 'xmlrpc_disabled', __( 'Disable XML-RPC authentication and remove pingback header', 'maasters-security-shield' ), $o ); ?><?php $this->toggle( 'hide_version', __( 'Hide WordPress generator/version output', 'maasters-security-shield' ), $o ); ?><?php $this->toggle( 'disable_author_enum', __( 'Redirect public ?author= enumeration requests', 'maasters-security-shield' ), $o ); ?><hr><p class="mss-warning"><strong><?php esc_html_e( 'File editor hardening', 'maasters-security-shield' ); ?></strong> <?php esc_html_e( 'For reliable file-editor disabling, add define( “DISALLOW_FILE_EDIT”, true ); to wp-config.php above the “That’s all, stop editing!” line. This plugin does not modify wp-config.php.', 'maasters-security-shield' ); ?></p></section>
                <section class="mss-card mss-privacy"><h2><?php esc_html_e( 'Privacy & compatibility', 'maasters-security-shield' ); ?></h2><p><?php esc_html_e( 'This plugin does not send data to an external service. Login failure tracking temporarily stores the visitor IP address locally in WordPress transients. Check your privacy policy and applicable legal requirements.', 'maasters-security-shield' ); ?></p><p><?php esc_html_e( 'Security protections reduce some risks but do not replace updates, backups, strong passwords, least-privilege access, HTTPS, or a full security review.', 'maasters-security-shield' ); ?></p></section>
                <?php submit_button( __( 'Save security settings', 'maasters-security-shield' ), 'primary mss-save' ); ?>
            </form>
        </div>
        <?php
    }
}
