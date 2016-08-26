<?php
/**
 *
 * The main metabox functions can be found in this file.
 *
 * @link              http://jeffreydewit.com
 * @since             1.0.0
 * @package           image_metabox
 *
 * @wordpress-plugin
 * Plugin Name:       Image Metabox
 * Plugin URI:        http://jeffreydewit.com/image_metabox/
 * Description:       A custom metabox with an image field for use however you please. Added to pages by default.
 * Version:           1.0.0
 * Author:            Jeffrey de Wit
 * Author URI:        http://jeffreydewit.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jj-image-metabox
 * Domain Path:       /languages
 *
 *
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

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Main plugin class.
 *
 * This class defines all code for running the plugin.
 *
 * @since      1.0.0
 * @package    image_metabox
 * @author     Jeffrey de Wit <jeffrey.dewit@gmail.com>
 */
class JJ_Image_Metabox {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_version    The current version of the plugin.
	 */
	protected $plugin_version;

	/**
	 * An array of post type strings.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $post_types    The post types the metabox should load for.
	 */
	public $post_types;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	function __construct() {
		$this->plugin_name = 'image-metabox';
		$this->plugin_version = '1.0.0';
		$this->post_types = apply_filters( 'image_metabox_post_types', array( 'page' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_custom_metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_image_metabox' ) );
	}

	/**
	 * Enqueue the image metabox scripts.
	 *
	 * @since 1.0.0
	 * @param string $hook The page hook it applies to.
	 */
	public function enqueue_scripts( $hook ) {
		if ( $this->can_enqueue( $hook ) ) {
			wp_enqueue_media();
			wp_enqueue_script( $this->plugin_name . '-script', plugin_dir_url( __FILE__ ) . 'js/image-metabox-script.js', array( 'jquery', 'media-upload', 'media-views' ), $this->version, true );

			wp_localize_script( $this->plugin_name . '-script', 'JJImageMetaboxi18n', array(
				'frame_title'    => __( 'Select an Image', 'jj-image-metabox' ),
				'button_title'   => __( 'Set this image', 'jj-image-metabox' ),
				'clear_image'    => __( 'Clear Image', 'jj-image-metabox' ),
			) );
		}
	}

	/**
	 * Enqueue the image metabox stylesheet.
	 *
	 * @since 1.0.0
	 * @param string $hook The page hook it applies to.
	 */
	public function enqueue_styles( $hook ) {
		if ( $this->can_enqueue( $hook ) ) {
			wp_enqueue_style( $this->plugin_name . '-style', plugin_dir_url( __FILE__ ) . 'css/image-metabox-style.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register custom metaboxes
	 *
	 * @since    1.0.0
	 */
	public function add_custom_metaboxes() {
		foreach ( $this->post_types as $post_type ) {
			add_meta_box(
				'jj_image_metabox',
				__( 'Custom Image', 'jj-image-metabox' ),
				array( $this, 'image_metabox_function' ),
				$post_type,
				'side'
			);
		}
	}

	/**
	 * Custom Image metabox function.
	 *
	 * @since    1.0.0
	 */
	public function image_metabox_function() {
		global $post;

		$custom_image = get_post_meta( $post->ID, 'jj_img_metabox_img', true );

		if ( ! empty( $custom_image ) ) {
			$img_src = wp_get_attachment_image_src( $custom_image, 'thumbnail' );
		}

		wp_nonce_field( 'jj_image_metabox_save', 'jj_image_metabox_nonce' );

		require plugin_dir_path( __FILE__ ) . 'partials/image-metabox-fields.php';
	}

	/**
	 * Handles saving of the custom image field.
	 *
	 * @since    1.0.0
	 * @param integer $post_id    Post ID for the post that is being saved.
	 */
	public function save_image_metabox( $post_id ) {
		if ( ! $this->is_valid_post_type( 'page' ) ||
			 ! $this->user_can_save( $post_id, 'jj_image_metabox_nonce', 'jj_image_metabox_save' ) ) {
				 return;
		}

		if ( isset( $_POST['jj_img_metabox_img'] ) && ! empty( $_POST['jj_img_metabox_img'] ) ) {
			$sanitized = intval( $_POST['jj_img_metabox_img'] );

			update_post_meta( $post_id, 'jj_img_metabox_img', $sanitized );
		} else {
			if ( '' !== get_post_meta( $post_id, 'jj_img_metabox_img', true ) ) {
				delete_post_meta( $post_id, 'jj_img_metabox_img' );
			}
		}

	}

	/**
	 * Checks to see if we should enqueue our scripts or not.
	 *
	 * @since 1.0.0
	 * @param string $hook The page hook it applies to.
	 */
	private function can_enqueue( $hook ) {
		if ( 'post-new.php' === $hook || 'post.php' === $hook ) {
			global $post;

			return in_array( $post->post_type, $this->post_types, true );
		}
	}

	/**
	 * Validates post type we're trying to save
	 *
	 * @since    1.0.0
	 * @param string $post_type    The post type we're checking for.
	 */
	private function is_valid_post_type( $post_type ) {
		return ! empty( $_POST['post_type'] ) && $post_type === $_POST['post_type'];
	}

	/**
	 * Validates if the user can actually save this post
	 *
	 * @since    1.0.0
	 * @param integer $post_id    Post ID for the post that is being saved.
	 * @param string  $nonce_action    Nonce value for action.
	 * @param string  $nonce_id    Nonce value for id.
	 */
	private function user_can_save( $post_id, $nonce_action, $nonce_id ) {

		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ $nonce_action ] ) && wp_verify_nonce( $_POST[ $nonce_action ], $nonce_id ) );

	    // Return true if the user is able to save; otherwise, false.
	    return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;

	}
}

$jj_image_metabox = new JJ_Image_Metabox();
