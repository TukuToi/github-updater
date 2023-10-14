<?php
/**
 * The GitHub_Plugin_Updater Class File
 *
 * This file registers the GitHub_Plugin_Updater class.
 *
 * @link    https://site.tld
 * @since   1.0.0 Introduced on 2023-10-14 15:30
 * @package Plugins
 * @author  Your Name <your-name@site.tld>
 */

declare( strict_types = 1 );

namespace MyPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The GitHub_Plugin_Updater class
 *
 * Hooks into the Active Plugin List to add a "check for updates" link
 * Hooks into the update plugins transient to update the update details if:
 * - update-core.php is visited (or forced)
 * - plugin list is visited
 * - "check for updates" link is clicked
 *
 * @since      1.0.0 Introduced on 2023-10-14 14:14
 * @package    Plugins
 * @author     Your Name <your-name@site.tld>
 */
class GitHub_Plugin_Updater {

	/**
	 * The Plugin basename
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:02
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @access private
	 * @var string $plugin_slug The Plugin basename (for example my-plugin/my-plugin.php).
	 */
	private $plugin_slug;

	/**
	 * The Current Version
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:02
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @access private
	 * @var string $current_version The Plugin version
	 */
	private $current_version;

	/**
	 * The GitHub Repo URL
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:02
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @access private
	 * @var string $github_repo_url The Plugin's GitHub Repo URL
	 */
	private $github_repo_url;

	/**
	 * Initialise the class
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:06
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @param  string $plugin_slug     The Plugin basename.
	 * @param  string $current_version The Current Version.
	 * @param  string $github_repo_url The GitHub Repo URL.
	 */
	public function __construct( $plugin_slug, $current_version, $github_repo_url ) {

		$this->plugin_slug     = $plugin_slug;
		$this->current_version = $current_version;
		$this->github_repo_url = $github_repo_url;
	}

	/**
	 * Add all hooks
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:05
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @return void
	 */
	public function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
		add_filter( 'plugin_action_links_' . $this->plugin_slug, array( $this, 'add_action_links' ) );
		add_action( 'admin_init', array( $this, 'force_github_update_check' ) );
	}

	/**
	 * Force Update Check
	 *
	 * If the URL parameter `force-github-update` is set, we force an Update check.
	 * This plugin uses the URL parameter only on the Plugin List > Check for Updates.
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:07
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @return void
	 */
	public function force_github_update_check() {

		global $pagenow;
		if ( $this->check_caps() ) {
			delete_site_transient( 'update_plugins' );
			wp_safe_redirect( admin_url( 'plugins.php' ) );
			exit;
		}
	}

	/**
	 * Add "Check for updates" to the plugin list item.
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:07
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @param array $links The existing plugin action links.
	 * @return array The plugin action links.
	 */
	public function add_action_links( $links ) {

		$nonce = wp_create_nonce('github-updater-check');
		$mylinks = array(
			'<a href="' . admin_url('plugins.php?force-github-update=true&_wpnonce=' . $nonce) . '">Check for updates</a>',
		);
		return array_merge( $mylinks, $links );
	}

	/**
	 * Check for updates.
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:07
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @param array $transient The Update Transient.
	 * @return array $transient The plugin Transient.
	 */
	public function check_for_updates( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// Fetch the release data from GitHub.
		$release = $this->get_github_release_info();
		if ( isset( $release['assets'][0]['browser_download_url'] ) ) {
			$package = $release['assets'][0]['browser_download_url'];
		} else {
			return $transient; // No assets found or unexpected API response, skip the update.
		}

		if ( is_array( $release )
			&& isset( $release['tag_name'] )
			&& version_compare( $release['tag_name'], $this->current_version, '>' )
		) {
			$transient->response[ $this->plugin_slug ] = (object) array(
				'slug'        => $this->plugin_slug,
				'new_version' => $release['tag_name'],
				'package'     => $package,
				'url'         => $this->github_repo_url,
			);
		}

		return $transient;
	}

	/**
	 * Get update info
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:07
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @return array|bool The update information array on success or false on failure.
	 */
	private function get_github_release_info() {
		$url = $this->github_repo_url . '/releases/latest';

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		return json_decode( $body, true );
	}

	/**
	 * Check permission and locaation
	 *
	 * Make sure the force-chec only is available to allowed users and locations.
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:12
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @return bool true if the user/location is allowed. False otherwise.
	 */
	private function check_caps() {

		global $pagenow;

		if ( 'plugins.php' === $pagenow
			&& current_user_can( 'update_plugins' )
			&& isset( $_GET['force-github-update'] )
			&& 'true' === $_GET['force-github-update']
			&& isset( $_GET['_wpnonce'] )
			&& wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'github-updater-check' )
		) {
			return true;
		}

		return false;
	}
}
