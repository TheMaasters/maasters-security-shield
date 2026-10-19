# Maasters Security Shield

**Practical Security Hardening for WordPress**

Maasters Security Shield is a WordPress security hardening plugin developed by **The Maasters Web Design Studio**. It brings essential WordPress security controls into one easy-to-use admin interface, helping website owners configure security headers, login rate limiting, Content Security Policy (CSP), HSTS, and other hardening measures.

The plugin is designed to make common security configurations easier to manage without replacing essential WordPress maintenance, backups, or professional security monitoring.

## Features

### Security Headers
Configure common browser security headers to help reduce certain browser-based risks:

- `X-Frame-Options`
- `X-Content-Type-Options`
- `Referrer-Policy`
- `Permissions-Policy`

### HTTP Strict Transport Security (HSTS)
- Enable HSTS on HTTPS responses.
- Configure the `max-age` value.
- Optionally include subdomains and the preload directive.
- Use caution before enabling HSTS on production websites.

### Content Security Policy (CSP)
Choose from three CSP modes:

- **Disabled** — Do not send the plugin's CSP policy.
- **Report Only** — Test a policy without enforcing its restrictions.
- **Enforce** — Apply the configured policy to browser resource loading.

Report Only mode is recommended when testing a new policy. Existing themes, plugins, fonts, scripts, images, and third-party integrations may require additional CSP directives.

### Failed-Login Rate Limiting
- Configure the permitted failed-login attempts.
- Set the lockout duration.
- Limit repeated login attempts using the connecting IP address.
- Store attempt counters locally through WordPress transients.

Visitors sharing the same public IP address may share a rate limit.

### XML-RPC Controls
- Optionally disable XML-RPC authentication.
- Remove the `X-Pingback` response header.

Review your site's integrations before changing XML-RPC settings.

### WordPress Hardening
- Optionally hide WordPress generator/version information.
- Optionally redirect public author-enumeration query requests.
- Sanitize plugin settings.
- Restrict administration to users with the required capabilities.
- Provide a dedicated **Maasters Shield** menu in the WordPress admin area.

## Requirements

- WordPress 5.8 or later
- PHP 7.4 or later
- An administrator account with the required capabilities to configure the plugin
- HTTPS for HSTS functionality

These are the plugin's declared minimum requirements. Always test compatibility with your current WordPress, PHP, theme, and plugin versions before production deployment.

## Installation

### Install from a ZIP file

1. Download the plugin ZIP from the project's GitHub Releases, if a release is available.
2. Open your WordPress admin dashboard.
3. Navigate to **Plugins → Add New Plugin → Upload Plugin**.
4. Select the plugin ZIP and click **Install Now**.
5. Activate **Maasters Security Shield**.
6. Open **Maasters Shield** from the WordPress admin sidebar.
7. Review the settings and test changes on a staging website before enabling strict policies.

### Install manually

1. Download or clone this repository.
2. Place the `maasters-security-shield` plugin directory inside `wp-content/plugins/`.
3. Open **Plugins** in your WordPress dashboard.
4. Activate Maasters Security Shield.

Use a packaged release when available so that you install the intended release version rather than an unfinished development branch.

## Configuration Guide

### Recommended setup

1. Review the available security header settings.
2. Test CSP using Report Only mode before enforcing it.
3. Enable HSTS only after confirming that HTTPS works consistently across the affected domain and subdomains.
4. Configure login rate limiting according to your site's traffic and access requirements.
5. Disable XML-RPC authentication only if your site does not depend on it.
6. Test login, forms, REST API integrations, caching, and other essential functionality after changing settings.

**Important:** Incorrect CSP or HSTS configuration can break site functionality or make affected domains inaccessible in some circumstances. Keep a backup and test on staging before applying strict policies.

## Privacy

Maasters Security Shield is designed to keep its login-failure counters local to the WordPress installation.

- Login attempts are grouped using the connecting IP address.
- IP-derived transient keys are used to store attempt counters.
- The plugin does not intentionally send login-failure data or telemetry to an external service.

Your hosting provider, server logs, caching layer, or other installed plugins may independently process visitor IP addresses.

## Limitations

Maasters Security Shield provides configurable hardening controls. It does **not** guarantee that a website is secure and does not replace:

- WordPress core, theme, and plugin updates
- Strong passwords and multi-factor authentication
- Reliable backups and tested recovery procedures
- Least-privilege user permissions
- HTTPS and secure hosting configuration
- Vulnerability monitoring, malware scanning, or incident response

The plugin's security headers and CSP policies may interact with other plugins or server-level configurations. Verify the final response headers and application behavior in your own environment.

## Development and Contributions

Contributions, bug reports, and suggestions are welcome.

When reporting an issue, include:

- WordPress and PHP versions
- Maasters Security Shield version
- Steps to reproduce the issue
- Relevant error messages or sanitized logs
- Any related theme, plugin, caching, or server configuration

Do not publish passwords, API keys, session tokens, personal information, or sensitive production logs in an issue.

Please test changes before submitting a pull request and avoid including unrelated changes.

## Changelog

### 1.1.5
- Updated the WordPress.org readme and contributor metadata.
- Added the translators comment required for a translatable login-protection message.
- Prepared the package for WordPress.org submission.

### 1.1.4
- Improved failed-login lockout behavior.
- Kept HSTS and CSP behavior independent of the baseline security-header toggle.
- Limited admin stylesheet loading to the plugin's settings page.
- Improved documentation and configuration guidance.

### 1.1.3
- Added a dedicated 20 × 20 PNG asset for the WordPress admin menu icon.

### 1.1.2
- Updated admin menu icon handling and styling.

### 1.1.1
- Added a branded top-level admin menu.
- Added branding to the settings interface.
- Improved author-enumeration handling.

### 1.1.0
- Reworked the settings interface and security controls.
- Added configurable login protection and CSP modes.
- Added configurable HSTS options.
- Removed duplicate files and the obsolete `X-XSS-Protection` header.
- Improved settings validation.

## License

Maasters Security Shield is licensed under **GPL-2.0-or-later**.

See the [GNU General Public License v2.0](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).

## Maintained By

**The Maasters Web Design Studio**

Website: https://themaasters.com

Building practical, reliable, and user-friendly digital products for WordPress and the web.
