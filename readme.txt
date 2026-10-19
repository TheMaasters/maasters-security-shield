=== Maasters Security Shield ===
Contributors: themaasters
Tags: security, hardening, security headers, login protection, xml-rpc
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Practical WordPress hardening with configurable security headers, CSP modes, login rate limiting, XML-RPC controls, and version hiding.

== Description ==
Maasters Security Shield provides configurable baseline hardening controls for WordPress sites. It is not a replacement for updates, backups, strong authentication, or a professional security review.

== Features ==
* Baseline security headers: X-Frame-Options, X-Content-Type-Options, Referrer-Policy, and Permissions-Policy.
* Optional HSTS on HTTPS responses, with configurable max-age and opt-in subdomain/preload directives.
* Content Security Policy in Disabled, Report Only, or Enforce mode. Report Only is recommended for initial testing.
* Failed-login rate limiting with configurable attempt count and lockout duration.
* Option to disable XML-RPC authentication and remove the X-Pingback header.
* Option to hide WordPress generator/version output.
* Option to redirect public author-enumeration query requests.
* Capability-protected administration and sanitized settings.

== Screenshots ==
1. Maasters Security Shield settings page showing the available WordPress security and hardening options.

== Important Notes ==
* Back up your site and test on staging before enabling HSTS subdomains/preload or enforcing CSP.
* CSP defaults are only a starting point; adapt directives for your theme, plugins, fonts, images, scripts, and third-party integrations.
* Login attempts are grouped by the REMOTE_ADDR value. Visitors behind the same public IP can share a lockout. Forwarded proxy headers are intentionally not trusted automatically.
* To disable the theme/plugin file editor, add `define( 'DISALLOW_FILE_EDIT', true );` to wp-config.php above the stop-editing line. This plugin does not modify wp-config.php.
* Failed-login protection uses the visitor IP address to group attempts and stores attempt counters under an IP-derived transient key in WordPress's database or object cache. It does not send this information to an external service.
* Security headers are emitted only when headers have not already been sent. Other server layers or plugins may also set or override headers.

== Installation ==
1. Upload the `maasters-security-shield` folder to `/wp-content/plugins/` or install the ZIP through Plugins > Add New > Upload Plugin.
2. Activate the plugin.
3. Open **Maasters Shield** in the WordPress admin sidebar to configure protections.
4. Test changes on a staging site before enabling strict policies.

== Frequently Asked Questions ==

= Will this plugin make my site completely secure? =
No. It adds configurable hardening controls but cannot replace timely updates, strong authentication, reliable backups, least-privilege access, HTTPS, or a security review.

= Should I enable Content Security Policy enforcement immediately? =
No. Start with Report Only, test your pages and integrations, adjust the policy, and only consider Enforce mode after confirming legitimate resources are not blocked.

= When should I enable HSTS, includeSubDomains, or preload? =
Enable HSTS only when your site is consistently served over HTTPS. Include subdomains only when every affected subdomain supports HTTPS. The preload directive does not itself submit your domain to browser preload lists.

= Why can login protection affect multiple visitors? =
Failed attempts are counted by the connecting IP address in REMOTE_ADDR. Visitors sharing a public IP address may share the same rate limit. Proxy headers are not trusted automatically because they can be spoofed unless your server is configured to validate them.

= Does the plugin send data to a third party? =
No. It does not send telemetry or login-failure data to an external service. Login protection stores local attempt counters using an IP-derived transient key.

= How can I disable the theme and plugin file editor? =
Add `define( 'DISALLOW_FILE_EDIT', true );` to wp-config.php above the stop-editing line. Maasters Security Shield does not edit wp-config.php.

== Upgrade Notice ==

= 1.1.5 =
Fixes a Plugin Check internationalization warning in the login-protection message.

= 1.1.4 =
Fixes the contributor username in the readme, improves failed-login lockout timing and behavior, keeps HSTS/CSP independent from baseline headers, and limits admin CSS loading to the plugin page.

== Changelog ==

= 1.1.5 =
* Added the required translators comment for the login-lockout message placeholder.
* Updated the readme contributor username to `arvindindcs`.

= 1.1.4 =
* Corrected the WordPress.org contributor username to `arvindindcs`.
* Fixed login-lockout checks so existing authentication errors cannot bypass the rate limit.
* Starts the configured lockout duration when the attempt threshold is reached and does not extend lockouts on each blocked retry.
* Keeps HSTS and CSP behavior independent from the baseline header toggle.
* Loads the admin stylesheet only on the Maasters Shield settings page.
* Added FAQ and upgrade notice sections; improved privacy and policy documentation.

= 1.1.3 =
* Replaced the sidebar icon with a dedicated 20 x 20 PNG asset.

= 1.1.2 =
* Updated the sidebar icon handling and admin stylesheet rules.

= 1.1.1 =
* Moved the plugin to a branded top-level admin menu.
* Added the brand logo to the dashboard header.
* Updated author-enumeration handling to use WordPress query vars.

= 1.1.0 =
* Rebuilt the settings screen with functional security controls.
* Added login protection settings and CSP modes.
* Added configurable HSTS max-age with opt-in subdomain/preload directives.
* Removed the obsolete X-XSS-Protection header and duplicate files.
* Added settings validation and IP validation.

= 1.0.1 =
* Initial security hardening and settings foundation.
