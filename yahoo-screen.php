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


class CFTP_Yahoo_Screen {

	protected static $_instance = null;

	public static function instance() {
		if ( !isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		wp_embed_register_handler( 'yahoo', '#(http|https)://(.+).screen.yahoo.com/(.+)#', array( $this, 'yahoo_embed_handler' ) );
		wp_embed_register_handler( 'yahooscreen', '#(http|https)://screen.yahoo.com/(.+).html#', array( $this, 'yahoo_embed_handler' ) );
	}

	function yahoo_embed_handler( $matches, $attr, $url, $rawattr ) {

		$transient = false;//get_transient( 'yahoo_screen_embed_'.$url );
		$embed = $transient;
		if ( $transient === false ) {
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
			// we have a transient return/assign the results
			set_transient( 'yahoo_screen_embed_'.$url, $embed, DAY_IN_SECONDS );
		}

		return apply_filters( 'embed_yahoo', $embed, $matches, $attr, $url, $rawattr );
	}
}

add_action( 'init', array( 'CFTP_Yahoo_Screen', 'instance' ) );
