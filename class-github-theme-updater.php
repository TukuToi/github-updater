<?php
/**
 * The GitHub_Theme_Updater Class File
 *
 * This file registers the GitHub_Theme_Updater class.
 *
 * @link    https://site.tld
 * @since   1.0.0 Introduced on 2023-10-14 15:30
 * @package Themes
 * @author  Your Name <your-name@site.tld>
 */

declare( strict_types = 1 );

namespace MyTheme;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The GitHub_Theme_Updater class
 *
 * Hooks into the update themes transient to update the update details.
 * Hoooks into theme page admin screen to force check if update is available.
 *
 * @since      1.0.0 Introduced on 2023-10-14 14:14
 * @package    Themes
 * @author     Your Name <your-name@site.tld>
 */
class GitHub_Theme_Updater {

  /**
	 * The Theme slug
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:02
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @access private
	 * @var string $theme_slug The Theme name (FOLDER slug, for example my-theme).
	 */
  private $theme_slug;

  /**
	 * The Current Version
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:02
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @access private
	 * @var string $current_version The Theme version
	 */
  private $current_version;

  /**
	 * The GitHub Repo URL
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:02
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @access private
	 * @var string $github_repo_url The Theme's GitHub Repo URL
	 */
  private $github_repo_url;

  /**
	 * Initialise the class
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:06
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @param  string $theme_slug     The Theme slug.
	 * @param  string $current_version The Current Version.
	 * @param  string $github_repo_url The GitHub Repo URL.
	 */
  public function __construct( $theme_slug, $current_version, $github_repo_url ) {
  
    $this->theme_slug     = $theme_slug;
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
  	add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_for_updates' ) );
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
  
  	if ( $this->check_caps() ) {
  		delete_site_transient( 'update_themes' );
  	}
  }

  /**
	 * Check for updates
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:07
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @param array $links The existing plugin action links.
	 * @return array The plugin action links.
	 */
  public function check_for_updates( $transient ) {
  	if ( empty( $transient->checked ) ) {
  		return $transient;
  	}
  
  	$release = $this->get_github_release_info();
  	if ( isset( $release['assets'][0]['browser_download_url'] ) ) {
  		$package = $release['assets'][0]['browser_download_url'];
  	} else {
  		return $transient;
  	}
  
  	if ( is_array( $release )
  		&& isset( $release['tag_name'] )
  		&& version_compare( $release['tag_name'], $this->current_version, '>' )
  	) {
  		$transient->response[ $this->theme_slug ] = array(
  			'theme'       => $this->theme_slug,
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
	 * Check permission and location
	 *
	 * Make sure the force-check only is available to allowed users and locations.
	 *
	 * @since 1.0.0 Introduced on 2023-10-14 18:12
	 * @author Beda Schmid <beda@tukutoi.com>
	 * @return bool true if the user/location is allowed. False otherwise.
	 */
  private function check_caps() {
  
  	global $pagenow;
  
  	if ( 'themes.php' === $pagenow
  		&& current_user_can( 'update_themes' )
  	) {
  		return true;
  	}
  
  	return false;
  }
}
