<?php
/**
 * Plugin Name: UNICEF Tap Project Banner
 * Plugin URI: http://adamkristopher.com/unicef-tap-project-banner/
 * Description: Displays a simple donate banner in the header or footer of your website so your visitors can participate in the UNICEF Tap Project. Learn more at https://thewaterproject.org
 * Version: 0.1.0
 * Author: Adam Carter
 * Author URI: http://adamkristopher.com
 * License: GPLv2+
 * Text Domain: unicef-tap-project-banner
 */

class UTP_Banner_Plugin {

	/**
	 * Plugin version number
	 *
	 * @const string
	 */
	const VERSION = '0.1.0';

	/**
	 * Hold plugin instance
	 *
	 * @var string
	 */
	public static $instance;

	/**
	 * Class constructor
	 */
	private function __construct() {
		define( 'UTP_PLUGIN', plugin_basename( __FILE__ ) );
		define( 'UTP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'UTP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		define( 'UTP_HOMEPAGE_URL', 'http://uniceftapproject.org' );
		define( 'UTP_DONATE_URL', 'https://www.unicefusa.org/donate/donate-unicef-tap-project/16034' );

		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'wp_footer', array( __CLASS__, 'display_banner' ) );
	}

	/**
	 * Register admin menu
	 *
	 * @action admin_menu
	 */
	public static function admin_menu() {
		add_options_page(
			__( 'UNICEF Tap Project Banner', 'unicef-tap-project-banner' ),
			__( 'UNICEF Tap Project Banner', 'unicef-tap-project-banner' ),
			'manage_options',
			'unicef-tap-project-banner',
			array( __CLASS__, 'settings_page' )
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @action admin_enqueue_scripts
	 */
	public static function admin_enqueue_scripts() {
		if ( ! isset( get_current_screen()->id ) || 'settings_page_unicef-tap-project-banner' !== get_current_screen()->id ) {
			return;
		}

		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'farbtastic' );
	}

	/**
	 * Register settings and fields
	 *
	 * @action admin_init
	 */
	public static function admin_init() {
		// Register setting
		register_setting( 'utp-settings-group', 'utp-settings' );

		// Register sections
		add_settings_section(
			'settings_section_one',
			__( 'Banner Colors', 'unicef-tap-project-banner' ),
			array( __CLASS__, 'settings_section_one_callback' ),
			'unicef-tap-project-banner'
		);

		add_settings_section(
			'settings_section_two',
			__( 'Banner Placement', 'unicef-tap-project-banner' ),
			array( __CLASS__, 'settings_section_two_callback' ),
			'unicef-tap-project-banner'
		);

		// Register fields
		add_settings_field(
			'utp_background_color',
			__( 'Background Color', 'unicef-tap-project-banner' ),
			array( __CLASS__, 'background_color_callback' ),
			'unicef-tap-project-banner',
			'settings_section_one'
		);

		add_settings_field(
			'utp_headline_color',
			__( 'Headline Color', 'unicef-tap-project-banner' ),
			array( __CLASS__, 'headline_color_callback' ),
			'unicef-tap-project-banner',
			'settings_section_one'
		);

		add_settings_field(
			'utp_button_color',
			__( 'Button Color', 'unicef-tap-project-banner' ),
			array( __CLASS__, 'button_color_callback' ),
			'unicef-tap-project-banner',
			'settings_section_one'
		);

		add_settings_field(
			'utp_placement',
			__( 'Banner Placement', 'unicef-tap-project-banner' ),
			array( __CLASS__, 'placement_callback' ),
			'unicef-tap-project-banner',
			'settings_section_two'
		);
	}

	/**
	 * Setting section one callback
	 */
	public static function settings_section_one_callback() {
		echo 'Select colors to compliment your website, or leave blank to use the default styles.';
	}

	/**
	 * Setting section two callback
	 */
	public static function settings_section_two_callback() {
		echo 'Select where you would like the donate banner to be placed.';
	}

	/**
	 * Setting field callbacks
	 */
	public static function background_color_callback() {
		$settings         = get_option( 'utp-settings', array() );
		$background_color = ( isset( $settings['background_color'] ) && ! empty( $settings['background_color'] ) ) ? $settings['background_color'] : '#f5f5f5';
		?>
		<input type="text" name="utp-settings[background_color]" id="utp_background_color" class="utp-color-picker-input" value="<?php echo esc_attr( $background_color ); ?>" />
		<div class="utp-color-picker" rel="utp_background_color"></div>
		<?php
	}

	public static function headline_color_callback() {
		$settings       = get_option( 'utp-settings', array() );
		$headline_color = ( isset( $settings['headline_color'] ) && ! empty( $settings['headline_color'] ) ) ? $settings['headline_color'] : '#222';
		?>
		<input type="text" name="utp-settings[headline_color]" id="utp_headline_color" class="utp-color-picker-input" value="<?php echo esc_attr( $headline_color ); ?>" />
		<div class="utp-color-picker" rel="utp_headline_color"></div>
		<?php
	}

	public static function button_color_callback() {
		$settings     = get_option( 'utp-settings', array() );
		$button_color = ( isset( $settings['button_color'] ) && ! empty( $settings['button_color'] ) ) ? $settings['button_color'] : '#40aae1';
		?>
		<input type="text" name="utp-settings[button_color]" id="utp_button_color" class="utp-color-picker-input" value="<?php echo esc_attr( $button_color ); ?>" />
		<div class="utp-color-picker" rel="utp_button_color"></div>
		<?php
	}

	public static function placement_callback() {
		$settings  = get_option( 'utp-settings', array() );
		$placement = ( isset( $settings['placement'] ) && ! empty( $settings['placement'] ) ) ? $settings['placement'] : 'footer';
		?>
		<input type="radio" name="utp-settings[placement]" id="utp-settings[placement][header]" value="header" <?php checked( $placement, 'header' ); ?> />
		<label for="utp-settings[placement][header]"><?php _e( 'Header', 'unicef-tap-project-banner' ); ?></label>
		&nbsp;&nbsp;
		<input type="radio" name="utp-settings[placement]" id="utp-settings[placement][footer]" value="footer" <?php checked( $placement, 'footer' ); ?> />
		<label for="utp-settings[placement][footer]"><?php _e( 'Footer', 'unicef-tap-project-banner' ); ?></label>
		<?php
	}

	/**
	 * Render the settings page
	 */
	public static function settings_page() {
		?>
		<script type="text/javascript">
		//<![CDATA[
			jQuery( document ).ready( function() {
				jQuery( '.utp-color-picker-input' ).on( 'focus', function() {
					var $this = jQuery( this );

					$this.next( '.utp-color-picker' ).show();
				});
				jQuery( '.utp-color-picker-input' ).on( 'focusout', function() {
					var $this = jQuery( this );

					$this.next( '.utp-color-picker' ).hide();
				});
				jQuery( '.utp-color-picker' ).each( function() {
					var $this = jQuery( this ),
					    id    = $this.attr( 'rel' );

					$this.farbtastic( '#' + id );
				});
			});
		//]]>
		</script>
		<style type="text/css">
		.utp-color-picker { display: none; }
		</style>
		<div class="wrap">
			<h2><?php _e( 'UNICEF Tap Project Banner', 'unicef-tap-project-banner' ); ?></h2>
			<form action="options.php" method="POST">
				<?php settings_fields( 'utp-settings-group' ); ?>
				<?php do_settings_sections( 'unicef-tap-project-banner' ); ?>
				<?php submit_button(); ?>
			</form>
			<p><em><?php printf( __( 'By displaying this banner on your website you are agreeing to the <a href="%s" target="_blank">UNICEF linking guidelines</a>.', 'unicef-tap-project-banner' ), esc_url( 'http://www.unicef.org/about/legal_linking.html' ) ); ?></em></p>
			<p><em><?php _e( 'This plugin is in no way officially affiliated with UNICEF.', 'unicef-tap-project-banner' ); ?></em></p>
		</div>
		<?php
	}

	/**
	 * Display a donate banner on the page
	 *
	 * @action wp_footer
	 */
	public static function display_banner() {
		require_once UTP_PLUGIN_DIR . 'banner.php';
	}

	/**
	 * Return active instance of UTP_Banner_Plugin, create one if it doesn't exist
	 *
	 * @return UTP_Banner_Plugin
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			$class = __CLASS__;
			self::$instance = new $class;
		}

		return self::$instance;
	}

}

$GLOBALS['utp_banner_plugin'] = UTP_Banner_Plugin::get_instance();
