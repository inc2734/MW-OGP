<?php
/** 
 * Plugin Name: MW OGP
 * Plugin URI: http://2inc.org
 * Description: Added FB Scripts, div#fb-root, OGP tags.
 * Version: 0.3
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * License: GPL2
 *
 * Copyright 2012 Takashi Kitajima (email : inc@2inc.org)
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

include( 'admin_page.php' );

register_activation_hook( __FILE__, array( 'mw_ogp', 'activation' ) );
register_uninstall_hook( __FILE__, array( 'mw_ogp', 'uninstall' ) );

$mw_ogp = new mw_ogp();
$mw_ogp->add_action();
$mw_ogp->add_filter();

class mw_ogp {

	public static $options = array(
		'app_id' => '',
		'type' => 'blog',
		'image' => '',
		'locale' => 'ja_JP'
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		$options = get_option( 'mw_ogp' );
		if ( !empty( $options ) ) {
			$this->options = $options;
		}
	}

	/**
	 * activation
	 */
	public static function activation() {
		add_option( 'mw_ogp', self::$options );
	}

	/**
	 * uninstall
	 */
	public static function uninstall() {
		delete_option( 'mw_ogp' );
	}

	/**
	 * do action!
	 */
	public function add_action() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * do filter!
	 */
	public function add_filter() {
		add_filter( 'wp_head', array( $this, 'print_head' ) );
		add_filter( 'wp_footer', array( $this, 'print_footer' ) );
	}

	/**
	 * add_admin_menu
	 */
	public function add_admin_menu() {
		add_options_page( 'MW OGP', 'MW OGP', 8, __FILE__,  array( $this, 'admin_page' ) );
	}

	/**
	 * admin_page
	 */
	public function admin_page() {
		$admin_page = new mw_ogp_admin_page();
		$admin_page->view();
	}

	/**
	 * Added tags in head
	 */
	public function print_head() {
		$image = $this->options['image'];
		if ( is_singular() && !is_front_page() ) {
			$type = 'article';
			$url = get_permalink();
			$title = get_the_title();
			if ( $_image = $this->catch_that_image() ) {
				$image = $_image;
			}
		} else {
			$type = ( empty( $this->options['type'] ) ) ? 'blog' : $this->options['type'];
			$url = home_url();
			$title = get_bloginfo( 'name' );
		}

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
		', esc_attr( $options['app_id'] ), esc_attr( $options['type'] ), esc_attr( $options['site_name'] ), esc_attr( $options['image'] ) ,esc_attr( $options['title'] ), esc_attr( $options['url'] ), esc_attr( $options['description'] ), esc_attr( $options['locale'] ) );
	}

	/**
	 * Added tags in footer
	 */
	public function print_footer() {
		echo sprintf( '
			<div id="fb-root"></div>
			<script type="text/javascript">
			window.fbAsyncInit = function() {
				FB.init({
					appId  : "%s",
					status : true,
					cookie : true,
					xfbml  : true,
					oauth  : true
				});
			};
			(function() {
				var e = document.createElement("script");
				e.src = document.location.protocol + "//connect.facebook.net/%s/all.js";
				e.async = true;
				document.getElementById("fb-root").appendChild(e);
			}());
			</script>
		', esc_html( $this->options['app_id'] ), esc_html( $this->options['locale'] ) );
	}

	/**
	 * catch_that_image 
	 */
	public function catch_that_image() {
		global $post;
		$first_img = '';
	
		$image_id = get_post_thumbnail_id();
		if ( $image_id ) {
			$image_url = wp_get_attachment_image_src( $image_id, 'thumbnail', true );
		}
		
		if ( !empty( $image_url[0] ) ) {
			$first_img = $image_url[0];
		} else {
			$output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/msi', $post->post_content, $matches );
			$first_img = $matches[1][0];
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
		if ( is_singular() && !is_front_page() ) {
			if ( !empty( $post->post_excerpt ) ) {
				$description = $post->post_excerpt;
			} elseif ( !empty( $post->post_content ) ) {
				$description = $post->post_content;
			}
		} else {
			$description = get_bloginfo( 'description' );
		}
		$description = strip_tags( $description );
		$description = esc_html( $description );
		$description = str_replace( array( "\r\n","\r","\n" ), '', $description );
		$description = mb_strimwidth( $description, 0, $strnum, "…", 'utf8' );
		return $description;
	}
}
?>