<?php
/**
 * Plugin Name: Jet Chat
 * Plugin URI:  http://www.cherryframework.com/plugins/
 * Description: Live Chat. Dependencies: Wapu Core to process own options, TM live chat.
 * Version:     1.0.0
 * Author:      JetImpex
 * Author URI:  http://cherryframework.com/
 * Text Domain: jet-chat
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 *
 * @package Jet_Chat
 * @author  Cherry Team
 * @version 1.0.0
 * @license GPL-3.0+
 * @copyright  2002-2016, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Jet_Chat` doesn't exists yet.
if ( ! class_exists( 'Jet_Chat' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class Jet_Chat {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of cherry framework core class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private $core = null;

		/**
		 * Holder for base plugin URL
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_url = null;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		private $version = '1.0.0';

		/**
		 * Holder for base plugin path
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_path = null;

		/**
		 * Transient key to store chat form
		 *
		 * @var string
		 */
		protected $transient = 'jet-chat';

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			// Internationalize the text strings used.
			add_action( 'init', array( $this, 'lang' ), 0 );

			// Load the admin files.
			add_action( 'init', array( $this, 'init' ), -999 );

			add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );

		}

		/**
		 * Returns plugin version
		 *
		 * @return string
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Manually init required modules.
		 *
		 * @return void
		 */
		public function init() {
			add_filter( 'wapu_core/general_setting', array( $this, 'register_settings' ) );
			add_action( 'wapu-core/settings-page/save', array( $this, 'reset_cache' ), 10, 2 );
			add_action( 'wp_footer', array( $this, 'print_chat_template' ), -1 );

			$this->chat_redirect();
		}

		/**
		 * Get chat URL callback
		 *
		 * @return [type] [description]
		 */
		public function chat_redirect() {

			if ( ! isset( $_GET['action'] ) || 'start-chat' !== $_GET['action'] ) {
				return;
			}

			$params   = array();
			$settings = get_option( 'wapu_core', array() );

			$params['email']    = isset( $_GET['mail'] ) ? filter_var( $_GET['mail'], FILTER_SANITIZE_EMAIL ) : '';
			$params['nick']     = isset( $_GET['name'] ) ? filter_var( $_GET['name'], FILTER_SANITIZE_STRING ) : '';
			$params['question'] = '';
			$params['room']     = isset( $settings['jet-chat-room'] ) ? $settings['jet-chat-room'] : '';
			$params['referer']  = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : home_url( '/' );

			ksort( $params );

			$url    = isset( $settings['jet-chat-url'] ) ? $settings['jet-chat-url'] : false;
			$secret = isset( $settings['jet-chat-secret'] ) ? $settings['jet-chat-secret'] : false;

			$params['key'] = md5( implode( '', $params ) . $secret );

			wp_redirect( add_query_arg( urlencode_deep( $params ), $url ) );
			die();
		}

		/**
		 * Reset form cache
		 *
		 * @param  array  $settings [description]
		 * @param  array  $ars      [description]
		 * @return [type]           [description]
		 */
		public function reset_cache( $settings = array(), $args = array() ) {

			if ( empty( $args['slug'] ) || 'wapu_core' !== $args['slug'] ) {
				return;
			}

			delete_transient( $this->transient );
		}

		/**
		 * Print chat template in footer
		 *
		 * @return void
		 */
		public function print_chat_template() {

			$settings = get_option( 'wapu_core', array() );
			$enbaled  = isset( $settings['jet-chat-enabled'] )
							? filter_var( $settings['jet-chat-enabled'], FILTER_VALIDATE_BOOLEAN )
							: false;

			if ( ! $enbaled ) {
				return;
			}

			$url    = isset( $settings['jet-chat-url'] ) ? $settings['jet-chat-url'] : false;
			$secret = isset( $settings['jet-chat-secret'] ) ? $settings['jet-chat-secret'] : false;

			if ( ! $url || ! $secret ) {
				return;
			}

			$cached = get_transient( $this->transient );
			$cached = false;

			if ( ! $cached ) {

				ob_start();

				$template       = $this->get_template( 'form.php' );
				$locales_select = '';
				$locales        = isset( $settings['jet-locales'] ) ? $settings['jet-locales'] : array();
				$default        = '';
				$active         = ' active-locale';

				foreach ( $locales as $locale ) {

					$img = '';

					if ( ! empty( $locale['icon'] ) ) {
						$img = sprintf(
							'<img src="%1$s" alt="%2$s" class="jet-locales__img">',
							wp_get_attachment_image_url( $locale['icon'], 'full' ),
							$locale['name']
						);
					}

					$item = sprintf(
						'<li class="jet-locales__item%4$s" data-value="%1$s" data-label="%3$s" data-flag=\'%5$s\'>%2$s%3$s</li>',
						$locale['code'],
						$img,
						$locale['name'],
						$active,
						json_encode( $img )
					);

					if ( ! $default ) {
						$default = sprintf(
							'<button class="jet-locales__trigger" data-locale="%1$s">%2$s%3$s</button>',
							$locale['code'],
							$img,
							$locale['name']
						);
					}

					$locales_select .= $item;
					$active = '';
				}


				if ( ! empty( $locales_select ) ) {
					$locales_select = sprintf(
						'<div class="jet-locales">%1$s<ul class="jet-locales__list">%2$s</ul></div>',
						$default,
						$locales_select
					);
				}

				include $template;

				$cached = ob_get_clean();

				set_transient( $this->transient, $cached, 3 * DAY_IN_SECONDS );

			}

			wp_enqueue_script( 'jet-chat' );
			echo $cached;

		}

		/**
		 * Register settings callback
		 *
		 * @return array
		 */
		public function register_settings( $settings = array() ) {

			$settings['tabs']['jet-chat'] = esc_html__( 'Chat', 'jet-chat' );

			$settings['controls'] = array_merge( $settings['controls'], array(
				'jet-chat-enabled' => array(
					'type'   => 'switcher',
					'id'     => 'jet-chat-enabled',
					'name'   => 'jet-chat-enabled',
					'value'  => '',
					'label'  => esc_html__( 'Chat Enbaled', 'jet-chat' ),
					'parent' => 'jet-chat',
				),
				'jet-chat-url' => array(
					'type'   => 'text',
					'id'     => 'jet-chat-url',
					'name'   => 'jet-chat-url',
					'value'  => '',
					'label'  => esc_html__( 'Chat URL', 'jet-chat' ),
					'parent' => 'jet-chat',
				),
				'jet-chat-secret' => array(
					'type'   => 'text',
					'id'     => 'jet-chat-secret',
					'name'   => 'jet-chat-secret',
					'value'  => '',
					'label'  => esc_html__( 'Chat Secret', 'jet-chat' ),
					'parent' => 'jet-chat',
				),
				'jet-chat-room' => array(
					'type'   => 'text',
					'id'     => 'jet-chat-room',
					'name'   => 'jet-chat-room',
					'value'  => '',
					'label'  => esc_html__( 'Chat Room', 'jet-chat' ),
					'parent' => 'jet-chat',
				),
				'jet-locales' => array(
					'parent'      => 'jet-chat',
					'type'        => 'repeater',
					'label'       => esc_html__( 'Chat Locales', 'jet-chat' ),
					'add_label'   => esc_html__( 'Add New Locale', 'jet-chat' ),
					'title_field' => 'name',
					'fields'      => array(
						'code' => array(
							'type'        => 'text',
							'id'          => 'code',
							'name'        => 'code',
							'placeholder' => 'Code',
							'label'       => 'Language Code',
						),
						'name' => array(
							'type'        => 'text',
							'id'          => 'name',
							'name'        => 'name',
							'placeholder' => 'Name',
							'label'       => 'Language Display name',
						),
						'icon' => array(
							'type'        => 'media',
							'id'          => 'icon',
							'name'        => 'icon',
							'label'       => 'Language Icon',
						),
					),
				),
			) );

			return $settings;

		}

		/**
		 * Returns path to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function plugin_path( $path = null ) {

			if ( ! $this->plugin_path ) {
				$this->plugin_path = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->plugin_path . $path;
		}
		/**
		 * Returns url to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			return $this->plugin_url . $path;
		}

		/**
		 * Loads the translation files.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function lang() {
			load_plugin_textdomain( 'jet-chat', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'jet-chat/template-path', 'jet-chat/' );
		}

		/**
		 * Returns path to template file.
		 *
		 * @return string|bool
		 */
		public function get_template( $name = null ) {

			$template = locate_template( $this->template_path() . $name );

			if ( ! $template ) {
				$template = $this->plugin_path( 'templates/' . $name );
			}

			if ( file_exists( $template ) ) {
				return $template;
			} else {
				return false;
			}
		}

		/**
		 * Enqueue public-facing stylesheets.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function register_assets() {

			wp_register_script(
				'jet-chat',
				$this->plugin_url( 'assets/js/jet-chat.js' ),
				array( 'jquery' ),
				$this->get_version(),
				true
			);

			wp_localize_script( 'jet-chat', 'jetChatSettings', array(
				'chaturl'  => esc_url( home_url( '/' ) ),
				'errMsg'   => array(
					'emptyName'   => esc_html__( 'Please enter your name', 'jet-chat' ),
					'emptyMail'   => esc_html__( 'Please enter your e-mail', 'jet-chat' ),
					'invalidMail' => esc_html__( 'Please enter valid e-mail', 'jet-chat' ),
				),
			) );
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}

if ( ! function_exists( 'jet_chat' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function jet_chat() {
		return Jet_Chat::get_instance();
	}
}

jet_chat();
