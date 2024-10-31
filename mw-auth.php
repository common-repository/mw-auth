<?php
/**
 * Plugin Name: MW Auth
 * Plugin URI: http://2inc.org
 * Description: This plugin allows only users to authenticate to WordPress.
 * Version: 1.2
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Text Domain: mw-auth
 * Domain Path: /languages/
 * Created: March 12, 2012
 * Modified: May 31, 2014
 * License: GPL2
 *
 * Copyright 2014 Takashi Kitajima (email : inc@2inc.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
$mw_auth = new mw_auth();
class mw_auth {

	const NAME = 'mw-auth';
	const DOMAIN = 'mw-auth';

	/**
	 * __construct
	 */
	public function __construct() {
		load_plugin_textdomain( self::DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );
		add_action( 'template_redirect', array( $this, 'logged_in' ) );
		add_filter( 'robots_txt', array( $this, 'disallow_robots_txt' ) );
		add_action( 'admin_menu', array( $this, 'redirect' ) );
		add_filter( 'admin_bar_menu' , array( $this, 'remove_admin_bar_menu' ), 9999 );
		add_action( 'wp_before_admin_bar_render', array( $this, 'add_admin_bar_menu' ) );
	}

	/**
	 * logged_in
	 * ログインしていないときはログイン画面にリダイレクト
	 */
	public function logged_in() {
		if ( !is_user_logged_in() )
			auth_redirect();
	}

	/**
	 * disallow_robots_txt
	 * robots.txt を "Disallow: /" に
	 * @param $output
	 * @return $output
	 */
	public function disallow_robots_txt( $output ) {
		$public = get_option( 'blog_public' );
		if ( '0' == $public )
			return $output;
		$output  = "User-agent: *\n";
		$output .= "Disallow: /\n";
		return $output;
	}

	/**
	 * redirect
	 * 購読者の場合は管理画面に入らせない（トップページにリダイレクト）
	 */
	public function redirect() {
		if ( is_admin() && !current_user_can( 'delete_posts' ) ) {
			wp_redirect( home_url() );
			exit;
		}
	}

	/**
	 * remove_admin_bar_menu
	 * 購読者の場合は管理バーのメニューを表示させない
	 * @param $wp_admin_bar
	 */
	public function remove_admin_bar_menu( $wp_admin_bar ) {
		if ( !current_user_can( 'delete_posts' ) ) {
			$wp_admin_bar->remove_menu( 'my-account' );
			$wp_admin_bar->remove_menu( 'site-name' );
		}
	}

	/**
	 * add_admin_bar_menu
	 * 管理バーにログアウトを追加
	 */
	public function add_admin_bar_menu() {
		if ( !current_user_can( 'delete_posts' ) ) {
			global $wp_admin_bar;
			$wp_admin_bar->add_menu( array(
				'id' => 'mw-logout',
				'title' => __( 'Logout', self::DOMAIN ),
				'href' => wp_logout_url(),
			) );
		}
	}
}

