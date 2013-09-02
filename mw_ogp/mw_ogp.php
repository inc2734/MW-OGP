<?php
/**
 * Plugin Name: MW OGP
 * Plugin URI: http://2inc.org
 * Description: The plugin add OGP tags.
 * Version: 0.5.6
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created: March 19, 2012
 * Modified: August 29, 2013
 * License: GPL2
 *
 * Copyright 2013 Takashi Kitajima (email : inc@2inc.org)
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
class mw_ogp {

	const NAME = 'mw_ogp';
	protected $options = array(
		'app_id' => '',
		'type' => 'blog',
		'image' => '',
		'locale' => 'ja_JP'
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( self::NAME, 'activation' ) );
		register_uninstall_hook( __FILE__, array( self::NAME, 'uninstall' ) );
		include_once( plugin_dir_path( __FILE__ ) . 'system/admin.php' );
		$this->admin_page = new mw_ogp_admin_page();
		$this->admin_page->setName( self::NAME );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_filter( 'wp_head', array( $this, 'print_head' ) );
		$options = get_option( self::NAME );
		if ( !empty( $options ) ) {
			$this->options = $options;
		}
	}

	/**
	 * activation
	 */
	public static function activation() {
		$options = get_option( self::NAME );
		$options = array_merge( self::$options, (array)$options );
		update_option( self::NAME, $options );
	}

	/**
	 * uninstall
	 */
	public static function uninstall() {
		delete_option( self::NAME );
	}

	/**
	 * add_admin_menu
	 */
	public function add_admin_menu() {
		add_options_page( 'MW OGP', 'MW OGP', 'activate_plugins', __FILE__,  array( $this, 'admin_page' ) );
	}

	/**
	 * admin_page
	 */
	public function admin_page() {
		$this->admin_page->view();
	}

	/**
	 * Added tags in head
	 */
	public function print_head() {
		$image = home_url().$this->options['image'];
		if ( is_singular() && !is_front_page() ) {
			$type = 'article';
			$url = get_permalink();
			if ( $_image = $this->catch_that_image() ) {
				$image = $_image;
			}
		} else {
			$type = ( empty( $this->options['type'] ) ) ? 'blog' : $this->options['type'];
			//$url = home_url();
			$url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			if ( !empty( $_SERVER['QUERY_STRING'] ) ) {
				$url = $url.'?'.$_SERVER['QUERY_STRING'];
			}
			$url = preg_replace( '/([^:])\/+/', '$1/', $url );
			$url = trailingslashit( $url );
		}
		$title = trim( wp_title( '', false, '' ) );
		if ( empty( $title ) )
			$title = get_bloginfo( 'name' );

		$options = array(
			'app_id' => $this->options['app_id'],
			'type' => $type,
			'url' => $url,
			'title' => $title,
			'image' => $image,
			'site_name' => get_bloginfo( 'name' ),
			'description' => $this->get_description(),
			'locale' => $this->options['locale']
		);

		echo sprintf( '
			<meta property="fb:app_id" content="%s" />
			<meta property="og:type" content="%s" />
			<meta property="og:site_name" content="%s" />
			<meta property="og:image" content="%s" />
			<meta property="og:title" content="%s" />
			<meta property="og:url" content="%s" />
			<meta property="og:description" content="%s" />
			<meta property="og:locale" content="%s" />
			',
			esc_attr( apply_filters( 'mw_ogp_app_id', $options['app_id'] ) ),
			esc_attr( apply_filters( 'mw_ogp_type', $options['type'] ) ),
			esc_attr( apply_filters( 'mw_ogp_site_name', $options['site_name'] ) ),
			esc_attr( apply_filters( 'mw_ogp_image', $options['image'] ) ),
			esc_attr( apply_filters( 'mw_ogp_title', $options['title'] ) ),
			esc_attr( apply_filters( 'mw_ogp_url', $options['url'] ) ),
			esc_attr( apply_filters( 'mw_ogp_description', $options['description'] ) ),
			esc_attr( apply_filters( 'mw_ogp_locale', strtolower( $options['locale'] ) ) )
		);
	}

	/**
	 * catch_that_image
	 */
	public function catch_that_image() {
		global $post;
		$first_img = '';
		if ( function_exists( 'get_post_thumbnail_id' ) ) {
			$image_id = get_post_thumbnail_id();
			if ( !empty( $image_id ) ) {
				$image_url = wp_get_attachment_image_src( $image_id, 'midium', true );
			}
		}

		if ( !empty( $image_url[0] ) ) {
			$first_img = $image_url[0];
		} else {
			if ( preg_match( '/<img.+?src=[\'"]([^\'"]+?)[\'"].*?>/msi', $post->post_content, $matches ) ) {
				$first_img = do_shortcode( $matches[1] );
			}
		}
		if ( ! empty( $first_img ) && preg_match( '/^\/.+$/', $first_img ) ) {
			$first_img = home_url().$first_img;
		}
		return $first_img;
	}

	/**
	 * get_description
	 */
	public function get_description( $strnum = 200 ) {
		global $post;
		$description = get_bloginfo( 'description' );
		if ( is_singular() ) {
			if ( !empty( $post->post_excerpt ) ) {
				$description = $post->post_excerpt;
			} elseif ( !empty( $post->post_content ) ) {
				$description = $post->post_content;
			}
		}
		// $description = do_shortcode( $description );
		$description = strip_shortcodes( $description );
		$description = str_replace( ']]>', ']]&gt;', $description );
		$description = strip_tags( $description );
		$description = esc_html( $description );
		$description = str_replace( array( "\r\n","\r","\n" ), '', $description );
		$description = mb_strimwidth( $description, 0, $strnum, "Å...", 'utf8' );
		return $description;
	}
}
$mw_ogp = new mw_ogp();
