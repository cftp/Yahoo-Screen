<?php
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
	public function __construct() {
		wp_embed_register_handler( 'yahoo', '#http://(.+).screen.yahoo.com/(.+)#', array( $this, 'yahoo_embed_handler' ) );
	}

	function yahoo_embed_handler( $matches, $attr, $url, $rawattr ) {

		$embed = sprintf(
			'<figure class="o-container yahoo_screen">
				<iframe src="%1$s?format=embed" frameborder="0" scrolling="no" width="650" height="450" marginwidth="0" marginheight="0" allowfullscreen></iframe>
			</figure>',
			esc_attr( $url )
		);

		return apply_filters( 'embed_yahoo', $embed, $matches, $attr, $url, $rawattr );
	}
}

new CFTP_Yahoo_Screen();