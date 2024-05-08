<?php
/**
 * Plugin Name: Give - ConvertKit
 * Plugin URI: https://givewp.com/addons/convertkit/
 * Description: Easily integrate ConvertKit opt-ins within your Give donation forms.
 * Version: 2.0.0
 * Requires PHP: 7.2
 * Requires at least: 6.3
 * Author: GiveWP
 * Author URI: https://givewp.com
 * Text Domain: give-convertkit
 */

//Define constants.
if ( ! defined( 'GIVE_CONVERTKIT_VERSION' ) ) {
	define( 'GIVE_CONVERTKIT_VERSION', '2.0.0' );
}

if ( ! defined( 'GIVE_CONVERTKIT_MIN_GIVE_VERSION' ) ) {
	define( 'GIVE_CONVERTKIT_MIN_GIVE_VERSION', '3.11.0' );
}

if ( ! defined( 'GIVE_CONVERTKIT_FILE' ) ) {
	define( 'GIVE_CONVERTKIT_FILE', __FILE__ );
}

if ( ! defined( 'GIVE_CONVERTKIT_PATH' ) ) {
	define( 'GIVE_CONVERTKIT_PATH', dirname( GIVE_CONVERTKIT_FILE ) );
}

if ( ! defined( 'GIVE_CONVERTKIT_URL' ) ) {
	define( 'GIVE_CONVERTKIT_URL', plugin_dir_url( GIVE_CONVERTKIT_FILE ) );
}

if ( ! defined( 'GIVE_CONVERTKIT_DIR' ) ) {
	define( 'GIVE_CONVERTKIT_DIR', plugin_dir_path( GIVE_CONVERTKIT_FILE ) );
}

if ( ! defined( 'GIVE_CONVERTKIT_BASENAME' ) ) {
	define( 'GIVE_CONVERTKIT_BASENAME', plugin_basename( GIVE_CONVERTKIT_FILE ) );
}

if ( ! class_exists( 'Give_ConvertKit' ) ) {

	/**
	 * Class Give_ConvertKit
	 *
	 * @since 1.0.3
	 */
	class Give_ConvertKit {
		/**
		 * @since 1.0.3
		 *
		 * @var Give_ConvertKit The reference the singleton instance of this class.
		 */
		private static $instance;

		/**
		 * Notices (array)
		 *
		 * @since 1.0.3
		 *
		 * @var array
		 */
		public $notices = array();

		/**
		 * $id (string)
		 *
		 * @since 1.0.3
		 *
		 * @var array
		 */
		public $id = 'convertkit';

		/**
		 * $label (string)
		 *
		 * @since 1.0.3
		 *
		 * @var array
		 */
		public $label = 'ConvertKit';

        /**
         * @since 2.0.0
         * @var array
         */
        private $serviceProviders = [
            \GiveConvertKit\ConvertKitAPI\ServiceProvider::class,
            \GiveConvertKit\FormExtension\ServiceProvider::class,
        ];

		/**
		 * Returns the singleton instance of this class.
		 *
		 * @since 1.0.3
		 * @return Give_ConvertKit The singleton instance.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
				self::$instance->setup();
			}

			return self::$instance;
		}

		/**
		 * Setup Give ConvertKit.
		 *
         * @since 2.0.0 register service providers
		 * @since 1.0.3
		 * @access private
		 */
		public function setup() {

            // Load service providers.
            add_action('before_give_init', [$this, 'registerServiceProviders']);

			// Give init hook.
			add_action( 'give_init', array( $this, 'init' ), 10 );
			add_action( 'admin_init', array( $this, 'check_environment' ), 999 );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
		}

        /**
         * Register service providers
         *
         * @since 2.0.0
         */
        public function registerServiceProviders()
        {
            if ( ! $this->get_environment_warning() ) {
                return;
            }

            foreach ($this->serviceProviders as $className) {
                give()->registerServiceProvider($className);
            }
        }

		/**
		 * Init the plugin after plugins_loaded so environment variables are set.
		 *
		 * @since 1.0.3
		 */
		public function init() {
			if ( ! $this->get_environment_warning() ) {
				return;
			}

			include( GIVE_CONVERTKIT_PATH . '/includes/give-convertkit-activation.php' );


			include( GIVE_CONVERTKIT_PATH . '/includes/class-give-convertkit.php' );

			new Give_ConvertKit_Settings( $this->id, $this->label );

			$this->licensing();
			$this->textdomain();
			$this->give_convertkit_activation_banner();

			// Scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 100 );
		}

		/**
		 * Check plugin environment.
		 *
		 * @since 1.0.3
		 * @access public
		 *
		 * @return bool
		 */
		public function check_environment() {
			// Flag to check whether plugin file is loaded or not.
			$is_working = true;

			// Load plugin helper functions.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}

			/* Check to see if Give is activated, if it isn't deactivate and show a banner. */
			// Check for if give plugin activate or not.
			$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

			if ( empty( $is_give_active ) ) {
				// Show admin notice.
				$this->add_admin_notice( 'prompt_give_activate', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for Give - ConvertKit to activate.', 'give-convertkit' ), 'https://givewp.com' ) );
				$is_working = false;
			}

			return $is_working;
		}

		/**
		 * Check plugin for Give environment.
		 *
		 * @since 1.0.3
		 * @access public
		 *
		 * @return bool
		 */
		public function get_environment_warning() {
			// Flag to check whether plugin file is loaded or not.
			$is_working = true;

			// Verify dependency cases.
			if (
				defined( 'GIVE_VERSION' )
				&& version_compare( GIVE_VERSION, GIVE_CONVERTKIT_MIN_GIVE_VERSION, '<' )
			) {

				/* Min. Give. plugin version. */
				// Show admin notice.
				$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> core version %s for the Give - ConvertKit add-on to activate.', 'give-convertkit' ), 'https://givewp.com', GIVE_CONVERTKIT_MIN_GIVE_VERSION ) );

				$is_working = false;
			}

			return $is_working;
		}


		/**
		 * Load the plugin's textdomain
		 *
		 * @since 1.0.3
		 */
		public function textdomain() {

			// Set filter for language directory.
			$lang_dir = GIVE_CONVERTKIT_DIR . '/languages/';
			$lang_dir = apply_filters( 'give_convertkit_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter.
			$locale = apply_filters( 'plugin_locale', get_locale(), 'give-convertkit' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'give-convertkit', $locale );

			// Setup paths to current locale file.
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/give-convertkit/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/give-convertkit/ folder.
				load_textdomain( 'give-convertkit', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/give-convertkit/languages/ folder.
				load_textdomain( 'give-convertkit', $mofile_local );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'give-convertkit', false, $lang_dir );
			}

		}

		/**
		 * Load Admin Scripts
		 *
		 * Enqueues the required admin scripts.
		 *
		 * @since 1.0.3
		 * @global       $post
		 *
		 * @param string $hook Page hook
		 *
		 * @return void
		 */
		public function admin_scripts( $hook ) {

			global $post_type;

			// Directories of assets.
			$js_dir  = GIVE_CONVERTKIT_URL . 'assets/js/';
			$css_dir = GIVE_CONVERTKIT_URL . 'assets/css/';

			wp_register_script( 'give_' . $this->id . '_admin_ajax_js', $js_dir . 'admin-ajax.js', array( 'jquery' ) );
			wp_register_style( 'give_' . $this->id . '_admin_css', $css_dir . 'admin-forms.css', GIVE_CONVERTKIT_VERSION );
			wp_register_script( 'give_' . $this->id . '_admin_forms_scripts', $js_dir . 'admin-forms.js', array( 'jquery' ), GIVE_CONVERTKIT_VERSION, false );

			// Forms CPT Script.
			if ( $post_type === 'give_forms' ) {

				// CSS.
				wp_enqueue_style( 'give_' . $this->id . '_admin_css' );

				// JS.
				wp_enqueue_script( 'give_' . $this->id . '_admin_forms_scripts' );
				wp_enqueue_script( 'give_' . $this->id . '_admin_ajax_js' );
			}

			// Admin settings.
			if ( $hook == 'give_forms_page_give-settings' ) {

				// JS/CSS.
				wp_enqueue_script( 'give_' . $this->id . '_admin_ajax_js' );
				wp_enqueue_style( 'give_' . $this->id . '_admin_css' );

			}

		}

		/**
		 * Allow this class and other classes to add notices.
		 *
		 * @since 1.0.3
		 *
		 * @param $slug
		 * @param $class
		 * @param $message
		 */
		public function add_admin_notice( $slug, $class, $message ) {
			$this->notices[ $slug ] = array(
				'class'   => $class,
				'message' => $message,
			);
		}

		/**
		 * Display admin notices.
		 *
		 * @since 1.0.3
		 */
		public function admin_notices() {

			$allowed_tags = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'span'   => array(
					'class' => array(),
				),
				'strong' => array(),
			);

			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
				echo wp_kses( $notice['message'], $allowed_tags );
				echo '</p></div>';
			}

		}

		/**
		 * Implement Give Licensing for Give ConvertKit Add On.
		 *
		 * @since 1.0.3
		 * @access private
		 */
		public function licensing() {
			if ( class_exists( 'Give_License' ) ) {
				new Give_License( GIVE_CONVERTKIT_FILE, 'ConvertKit', GIVE_CONVERTKIT_VERSION, 'WordImpress' );
			}
		}

		/**
		 * Show activation banner for this add-on.
		 *
		 * @since 1.0.3
		 *
		 * @return bool
		 */
		public function give_convertkit_activation_banner() {

			// Check for activation banner inclusion.
			if (
				! class_exists( 'Give_Addon_Activation_Banner' )
				&& file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' )
			) {
				include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
			}

			// Initialize activation welcome banner.
			if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {

				//Only runs on admin
				$args = array(
					'file'              => __FILE__,
					'name'              => esc_html__( 'ConvertKit', 'give-convertkit' ),
					'version'           => GIVE_CONVERTKIT_VERSION,
					'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=addons&section=convertkit-settings' ),
					'documentation_url' => 'http://docs.givewp.com/addon-convertkit',
					'support_url'       => 'https://givewp.com/support/',
					'testing'           => false
				);

				new Give_Addon_Activation_Banner( $args );
			}

			return true;
		}
	}

	/**
	 * Returns class object instance.
	 *
	 * @since 1.0.3
	 *
	 * @return Give_ConvertKit bool|object
	 */
	function Give_ConvertKit() {
		return Give_ConvertKit::get_instance();
	}

	Give_ConvertKit();

    require_once GIVE_CONVERTKIT_DIR . 'vendor/autoload.php';
}
