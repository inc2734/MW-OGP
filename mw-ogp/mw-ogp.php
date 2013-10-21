<?php
/**
 * Plugin Name: MW OGP
 * Plugin URI: http://2inc.org
 * Description: MW OGP added OGP tags.
 * Version: 0.5.9
 * Author: Takashi Kitajima
 * Author URI: http://2inc.org
 * Created : March 19, 2012
 * Modified: October 19, 2013
 * Text Domain: mw-ogp
 * Domain Path: /languages/
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
$mw_ogp = new mw_ogp();
class mw_ogp {

	const NAME = 'mw-ogp';
	const DOMAIN = 'mw-ogp';
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
		// 有効化した時の処理
		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );
		// アンインストールした時の処理
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );

		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * activation
	 */
	public static function activation() {
		/*
		$options = get_option( self::NAME );
		$options = array_merge( self::$options, (array)$options );
		update_option( self::NAME, $options );
		*/
	}

	/**
	 * uninstall
	 */
	public static function uninstall() {
		delete_option( self::NAME );
	}

	/**
	 * init
	 */
	public function init() {
		load_plugin_textdomain( self::DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );
		add_filter( 'wp_head', array( $this, 'print_head' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_head', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		$options = get_option( self::NAME );
		if ( !empty( $options ) ) {
			$this->options = $options;
		} else {
			update_option( self::NAME, $this->options );
		}
		add_theme_support( 'post-thumbnails' );
		add_image_size( self::NAME . 'ogp_image', 1200, 627, true );
	}

	/**
	 * add_admin_menu
	 */
	public function add_admin_menu() {
		if ( !is_admin() )
			return;
		include_once( plugin_dir_path( __FILE__ ) . 'system/admin.php' );
		$this->admin_page = new mw_ogp_admin_page();
		$this->admin_page->setName( self::NAME );
		add_action( 'admin_print_styles', array( $this, 'admin_style' ) );
		add_action( 'admin_print_scripts', array( $this, 'admin_scripts' ) );
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
		$image = home_url() . $this->options['image'];
		if ( is_singular() && !is_front_page() ) {
			$type = 'article';
			$url = get_permalink();
			if ( $_image = $this->catch_that_image() ) {
				$image = $_image;
			}
		} else {
			$type = ( empty( $this->options['type'] ) ) ? 'blog' : $this->options['type'];
			if ( is_singular() ) {
				$url = get_permalink();
			} else {
				$url  = 'http';
				$url .= ( empty( $_SERVER['HTTPS'] ) ) ? '' : 's';
				$url .= '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
				if ( !empty( $_SERVER['QUERY_STRING'] ) ) {
					$url = $url . '?' . $_SERVER['QUERY_STRING'];
				}
			}
		}
		$title = trim( wp_title( '', false, '' ) );

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
	 * ogp_image > thumbnail > first image
	 */
	public function catch_that_image() {
		global $post;
		$first_img = '';
		$_image_id = get_post_meta( $post->ID, self::NAME, true );
		if ( !empty( $_image_id['ogp_image_id'] ) ) {
			$image_id = $_image_id['ogp_image_id'];
		} elseif ( function_exists( 'get_post_thumbnail_id' ) ) {
			$image_id = get_post_thumbnail_id();
		}
		if ( !empty( $image_id ) )
			$image_url = wp_get_attachment_image_src( $image_id, self::NAME . 'ogp_image', false );

		if ( !empty( $image_url[0] ) ) {
			$first_img = $image_url[0];
		} else {
			if ( preg_match( '/<img.+?src=[\'"]([^\'"]+?)[\'"].*?>/msi', $post->post_content, $matches ) )
				$first_img = do_shortcode( $matches[1] );
		}
		if ( !empty( $first_img ) && preg_match( '/^\/.+$/', $first_img ) )
			$first_img = home_url() . $first_img;
		return $first_img;
	}

	/**
	 * get_description
	 */
	public function get_description( $strnum = 200 ) {
		global $post;
		$description = get_bloginfo( 'description' );
		if ( is_singular() ) {
			add_filter( 'wp_trim_excerpt', array( $this, 'wp_trim_excerpt'), 9999 );
			$description = get_the_excerpt();
			remove_filter( 'wp_trim_excerpt', array( $this, 'wp_trim_excerpt' ), 9999 );
		}
		return $description;
	}
	public function wp_trim_excerpt( $excerpt ) {
		global $post;
		return $post->post_excerpt;
	}

	/**
	 * add_meta_box
	 */
	public function add_meta_box() {
		global $post;
		if ( !is_admin() )
			return;
		$post_types = get_post_types();
		foreach ( $post_types as $post_type ) {
			if ( current_theme_supports( 'post-thumbnails' ) && post_type_supports( $post_type, 'thumbnail' ) ) {
				add_meta_box(
					self::NAME . '_add_ogp_image_metabox',
					__( 'OGP Image', self::DOMAIN ),
					array( $this, 'add_ogp_image' ),
					$post_type,
					'side',
					'low'
				);
			}
		}
	}

	/**
	 * add_ogp_image
	 * og:image 用の画像をアップロード
	 */
	public function add_ogp_image() {
		global $post;
		$post_meta = get_post_meta( $post->ID, self::NAME, true );
		$ogp_image_id = '';
		if ( !empty( $post_meta['ogp_image_id'] ) ) {
			$ogp_image_id = $post_meta['ogp_image_id'];
		}
		$add_button_class = 'mwogp-image-hide';
		$delete_button_class = 'mwogp-image-hide';
		if ( !empty( $post_meta['ogp_image_id'] ) ) {
			$delete_button_class = 'mwogp-image-show';
		} else {
			$add_button_class = 'mwogp-image-show';
		}
		?>
		<a id="mwogp-media" href="javascript:void( 0 )" class="<?php echo esc_attr( $add_button_class ); ?>"><?php _e( 'Set OGP Image', self::DOMAIN ); ?></a>
		<div id="mwogp-images">
			<?php
			if ( !empty( $ogp_image_id ) ) {
				$ogp_image = wp_get_attachment_image( $ogp_image_id, self::NAME . 'ogp_image' );
				echo $ogp_image;
			}
			?>
		</div>
		<a id="mwogp-delete" href="javascript:void( 0 )" class="<?php echo esc_attr( $delete_button_class ); ?>">
			<?php _e( 'Delete OGP Image', self::DOMAIN ); ?>
		</a>
		<input type="hidden" id="mwogp-hidden" name="<?php echo self::NAME; ?>[ogp_image_id]" value="<?php echo esc_attr( $ogp_image_id ); ?>" />
		<p class="howto">
			<?php _e( 'Recommended image size of 1200 x 627', self::DOMAIN ); ?>
		</p>
		<?php
	}

	/**
	 * admin_style
	 */
	public function admin_style() {
		$url = plugin_dir_url( __FILE__ );
		wp_register_style( self::DOMAIN . '-admin', $url . 'css/admin.css' );
		wp_enqueue_style( self::DOMAIN . '-admin' );
	}

	/**
	 * admin_scripts
	 */
	public function admin_scripts() {
		wp_enqueue_media();
		wp_enqueue_script(
			self::DOMAIN . '-admin',
			plugins_url( 'js/media-uploader.js', __FILE__ ),
			array( 'jquery' ),
			false,
			true
		);
		wp_localize_script( self::DOMAIN . '-admin', 'mwogp', array(
			'title' => __( 'Set OGP Image', self::DOMAIN ),
		) );
	}

	/**
	 * save_post
	 * @param	$post_ID
	 */
	public function save_post( $post_ID ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_ID;
		//if ( !current_user_can( 'manage_options', $post_ID ) )
			//return $post_ID;
		if ( !isset( $_POST[self::NAME] ) )
			return $post_ID;

		$accepts = array(
			'ogp_image_id',
		);
		$data = array();
		foreach ( $accepts as $accept ) {
			if ( isset( $_POST[self::NAME][$accept] ) )
				$data[] = $_POST[self::NAME][$accept];
		}
		$old_data = get_post_meta( $post_ID, self::NAME, true );
		update_post_meta( $post_ID, self::NAME, $data, $old_data );
	}
}

