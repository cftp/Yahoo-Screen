<?php
/*
Plugin Name: CFTP Yahoo Screen
Description: Yahoo Screen/Video OEmbed support
Author: Tom J Nowell, Code For The People
Version: 1.0
Author URI: http://codeforthepeople.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


//add_action( 'init', array( 'CFTP_Yahoo_Screen', 'instance' ) );
CFTP_Yahoo_Screen::instance();
YahooScreenOembedProvider::init();

/**
 * Class CFTP_Yahoo_Screen
 */
class CFTP_Yahoo_Screen {

	/**
	 * The primary instance of this object lives here
	 *
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * WordPress doesn't provide a place for this object to live, so rather than pollute the global namespace, we'll
	 * use the singleton pattern to store it.
	 *
	 * @return CFTP_Yahoo_Screen|null
	 */
	public static function instance() {
		if ( !isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Adds the oembed providers
	 */
	public function __construct() {
		$this->add_providers();
	}

	public function add_providers() {
		wp_oembed_add_provider( '#https?://(.+).screen.yahoo.com/(.+)#i', site_url('/?oembed=true&oembedtype=yahooscreen&format={format}'), true );
		wp_oembed_add_provider( '#https?://screen.yahoo.com/(.+).html#i', site_url('/?oembed=true&oembedtype=yahooscreen&format={format}'), true );
	}

	/**
	 * Generates a Yahoo Screen embed code given a URL
	 *
	 * @param $url A valid Yahoo Screen video URL to be embedded
	 * @return string A Yahoo embed iframe
	 */
	public static function yahoo_embed_code( $url ) {
		$width = 640;
		$height = 360;
		$aspect = $width/$height;
		global $content_width;
		if ( isset( $content_width ) ) {
			$width = $content_width;
			$height = $content_width/$aspect;
		}

		$embed = sprintf(
			'<figure class="o-container yahoo_screen">
				<iframe src="%1$s?format=embed" frameborder="0" scrolling="no" width="%2$d" height="%3$d" marginwidth="0" marginheight="0" allowfullscreen></iframe>
			</figure>',
			esc_attr( $url ), $width, $height
		);
		return $embed;
	}
}


/**
 * oEmbed Provider, modified code from Matthias and Craig
 *
 * @author Matthias Pfefferle
 * @author Craig Andrews
 * @author Tom J Nowell
 */
class YahooScreenOembedProvider {

	/**
	 * Initialises the provider by adding the necessary hooks
	 */
	public static function init() {
		add_action( 'parse_query', array( 'YahooScreenOembedProvider', 'parse_query' ) );
		add_filter( 'query_vars', array( 'YahooScreenOembedProvider', 'query_vars' ) );
		add_filter( 'cftp_oembed_provider_data_yahoo_screen', array( 'YahooScreenOembedProvider', 'generate_default_content' ), 90, 3 );
		add_action( 'cftp_oembed_provider_render_yahoo_screen_json', array( 'YahooScreenOembedProvider', 'render_json' ), 99, 2 );
		add_action( 'cftp_oembed_provider_render_yahoo_screen_xml', array( 'YahooScreenOembedProvider', 'render_xml' ), 99 );
	}

	/**
	 * adds query vars
	 */
	public static function query_vars( $query_vars ) {
		foreach ( array( 'oembed', 'oembedtype', 'format', 'url', 'callback' ) as $qvar ) {
			if ( !array_key_exists( $qvar, $query_vars ) ) {
				$query_vars[] = $qvar;
			}
		}

		return $query_vars;
	}

	/**
	 * handles request
	 */
	public static function parse_query($wp) {
		if (!array_key_exists('oembed', $wp->query_vars) ||
			!array_key_exists('url', $wp->query_vars) ||
			!array_key_exists('oembedtype', $wp->query_vars)
		) {
			return;
		}

		// we're only handling yahoo screen here
		if ( $wp->query_vars['oembedtype'] != 'yahooscreen' ) {
			return;
		}

		$yahoo_url = $wp->query_vars['url'];

		// @TODO: perform a check on the regex if the URL matches to validate, if not, 404
		/*if(!$post) {
			header('Status: 404');
			wp_die("Not found");
		}*/

		// add support for alternate output formats
		$oembed_provider_formats = apply_filters( "oembed_provider_formats", array( 'json', 'xml' ) );

		// check output format
		$format = 'json';
		if ( array_key_exists( 'format', $wp->query_vars ) && in_array( strtolower( $wp->query_vars['format'] ), $oembed_provider_formats ) ) {
			$format = $wp->query_vars['format'];
		}

		// content filter
		$oembed_provider_data = apply_filters( 'cftp_oembed_provider_data_yahoo_screen', array(), $yahoo_url );

		do_action( 'cftp_oembed_provider_render_yahoo_screen', $format, $oembed_provider_data, $wp->query_vars);
		do_action( "cftp_oembed_provider_render_yahoo_screen_{$format}", $oembed_provider_data, $wp->query_vars);
	}

	/**
	 * adds default content
	 *
	 * @param array $oembed_provider_data
	 * @param $url
	 * @internal param string $post_type
	 * @internal param Object $post
	 *
	 * @return array OEmbed data to be formatted as a response
	 */
	public static function generate_default_content( $oembed_provider_data, $url ) {
		$count = 4;
		$image_url = '';
		$title = '';
		$video_height = 1024;
		$video_width = 576;
		$remote = wp_remote_get( $url );
		if ( !is_wp_error( $remote ) ) {
			// disable the printing of xml errors so we don't break the frontend
			libxml_use_internal_errors( true );
			$dom = new DOMDocument();
			$dom->loadHTML( $remote['body'] );
			libxml_clear_errors();
			$metaChildren = $dom->getElementsByTagName( 'meta' );
			// for each meta tag found
			for ( $i = 0; $i < $metaChildren->length; $i++ ) {
				$el = $metaChildren->item( $i );
				$name = $el->getAttribute( 'name' );
				if (!$name) {
					$name = $el->getAttribute('property');
				}
				if ( $name == 'og:image' ) {
					// we've found the twitter meta tag for the video player, stop looping
					$image_url = $el->getAttribute( 'content' );
					$count--;
				}
				if ( $name == 'og:title' ) {
					// we've found the twitter meta tag for the video player, stop looping
					$title = $el->getAttribute( 'content' );
					$count--;
				}
				if ( $name == 'og:video:height' ) {
					// we've found the twitter meta tag for the video player, stop looping
					$video_height = $el->getAttribute( 'content' );
					$count--;
				}
				if ( $name == 'og:video:width' ) {
					// we've found the twitter meta tag for the video player, stop looping
					$video_width = $el->getAttribute( 'content' );
					$count--;
				}
				if ( $count == 0 ) {
					break;
				}
			}
		}
		$oembed_provider_data['version'] = '1.0';
		$oembed_provider_data['provider_name'] = 'Yahoo Screen';
		$oembed_provider_data['provider_url'] = home_url();
		$oembed_provider_data['author_name'] = 'Yahoo Screen';
		$oembed_provider_data['author_url'] = 'http://screen.yahoo.com';
		$oembed_provider_data['title'] = $title;

		if ( !empty( $image_url ) ) {
			$oembed_provider_data['thumbnail_url'] = $image_url;
			$oembed_provider_data['thumbnail_width'] = $video_width;
			$oembed_provider_data['thumbnail_height'] = $video_height;
		}
		$oembed_provider_data['type'] = 'video';
		$oembed_provider_data['html'] = CFTP_Yahoo_Screen::yahoo_embed_code( $url );

		return $oembed_provider_data;
	}

	/**
	 * Render json output
	 *
	 * @param array $oembed_provider_data
	 * @param array $wp_query Query variables ( not a WP_Query object as you would think )
	 */
	public static function render_json($oembed_provider_data, $wp_query) {
		header( 'Content-Type: application/json; charset=' . get_bloginfo( 'charset' ), true );

		// render json output
		$json = json_encode( $oembed_provider_data );

		// add callback if available
		if (array_key_exists( 'callback', $wp_query ) ) {
			$json = $wp_query['callback'] . "($json);";
		}

		echo $json;
		exit;
	}

	/**
	 * Render xml output
	 *
	 * @param array $oembed_provider_data
	 */
	public static function render_xml( $oembed_provider_data ) {
		header('Content-Type: text/xml; charset=' . get_bloginfo('charset'), true);

		// render xml-output
		echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '" ?>';
		echo '<oembed>';
		foreach ( array_keys($oembed_provider_data) as $element ) {
			echo '<' . $element . '>' . esc_html($oembed_provider_data[$element]) . '</' . $element . '>';
		}
		echo '</oembed>';
		exit;
	}

}
