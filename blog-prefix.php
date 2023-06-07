<?php
/**
 * Plugin Name: Content-Statistics
 * Plugin URI: https://github.com/DevZonayed/content-statistics-plugin-for-wp
 * Description: This is a plugin to add post statistics to single post page.
 * Version: 1.1.0
 * Author: Zonayed Ahamad
 * Text Domain: cspdomain
 * Domain Path:/languages/
 * @package Content Statistics
 */


class WordCountAndTimePlugin {
	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_page' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
		add_filter( 'the_content', array( $this, 'ifWrap' ) );
		add_action( "init", array( $this, 'languages' ) );
	}

	function languages() {
		load_plugin_textdomain( 'cspdomain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	function ifWrap( $content ) {
		if ( ( is_main_query() and is_single() ) and ( get_option( 'csp_word_count', '1' ) or get_option( 'csp_character_count', '1' ) or get_option( 'csp_read_time', '1' ) ) ) {
			return $this->createHTML( $content );
		}
		;
		return $content;
	}

	function createHTML( $content ) {
		$title = esc_html( get_option( 'csp_headline', 'Post Statistics' ) );
		$content_payload = "<h3>$title</h3><p>";
		$total_word = str_word_count( strip_tags( $content ) );
		$total_later = strlen( strip_tags( $content ) );
		$read_time = round( ( $total_word / 225 ) );
		// Conditions
		if ( get_option( 'csp_word_count', '1' ) ) {
			$content_payload .= "<strong>" . esc_html__( "This post has", 'cspdomain' ) . " " . $total_word . " " . esc_html__( "words", 'cspdomain' ) . "</strong><br>";
		}
		if ( get_option( 'csp_character_count', '1' ) ) {
			$content_payload .= "<strong>" . esc_html__( "This post has", 'cspdomain' ) . " " . $total_later . " " . esc_html__( "characters", 'cspdomain' ) . "</strong><br>";
		}
		if ( get_option( 'csp_read_time', '1' ) ) {
			$content_payload .= "<strong>" . esc_html__( "This post shall take about", 'cspdomain' ) . " " . $read_time . " " . esc_html__( "minite(s) to read it", 'cspdomain' ) . ".</strong><br>";
		}

		$content_payload .= "</p>";
		if ( get_option( 'csp_location', '1' ) == "1" ) {
			return $content . $content_payload;
		} elseif ( get_option( 'csp_location' ) == "0" ) {
			return $content_payload . $content;
		}


	}


	function settings() {
		function add_html_section( $label, $key, $default_value = null, $html_method, $section_title = null, $instance, $htmlarg = array(), $customSanitize = 'sanitize_text_field' ) {
			add_settings_section( $section_title, null, null, 'word-count-settings-page' );
			add_settings_field( $key, $label, array( $instance, $html_method ), 'word-count-settings-page', $section_title, $htmlarg );
			register_setting( 'wordcountplugin', $key, array( 'sanitize_callback' => $customSanitize, 'default' => $default_value ) );
		}

		// Locations
		add_html_section( "Display Location", 'csp_location', '0', 'location_HTML', null, $this, null, array( $this, 'display_location_valigation' ) );
		add_html_section( "Headline Text", 'csp_headline', 'Post Statistic', 'headLiNeHtml', null, $this );
		add_html_section( "Word Count", 'csp_word_count', '1', 'csp_checkBox', null, $this, array( 'name' => 'csp_word_count' ) );
		add_html_section( "Character count", 'csp_character_count', '1', 'csp_checkBox', null, $this, array( 'name' => 'csp_character_count' ) );
		add_html_section( "Read Time", 'csp_read_time', '1', 'csp_checkBox', null, $this, array( 'name' => 'csp_read_time' ) );


	}

	function display_location_valigation( $input ) {
		if ( $input != '0' and $input != '1' ) {
			add_settings_error( 'csp_location', 'csp_location_error', 'Display location must be either begaining or ending' );
			return get_option( 'csp_location' );
		}
		;
		return $input;
	}


	function location_HTML() {
		?>
		<select name="csp_location">
			<option value="0" <?php selected( get_option( 'csp_location' ), '0' ) ?>>Beginning of post</option>
			<option value="1" <?php selected( get_option( 'csp_location' ), '1' ) ?>>End of post</option>
		</select>
		<?php
	}
	function headLiNeHtml() {
		?>
		<input type="text" name="csp_headline" value="<?php echo esc_attr( get_option( 'csp_headline' ) ) ?>" />
		<?php
	}
	function csp_checkBox( $args ) {
		?>
		<input type="checkbox" name="<?php echo $args["name"] ?>" value="1" <?php checked( get_option( $args["name"] ), '1' ) ?>>
		<?php
	}


	function admin_page() {
		add_options_page( 'Word count settings', esc_html__( 'Word Count', 'cspdomain' ), 'manage_options', 'word-count-settings-page', array( $this, 'admin_page_html' ) );
	}

	function admin_page_html() {
		?>

		<div class="wrap">
			<h1>Word Count Settings</h1>
			<form action="options.php" method="POST">
				<?php
				settings_fields( 'wordcountplugin' );
				do_settings_sections( 'word-count-settings-page' );
				submit_button();
				?>
			</form>
		</div>


		<?php
	}

}
;


$wordCountAndTimePlugin = new WordCountAndTimePlugin();