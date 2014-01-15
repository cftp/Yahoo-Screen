=== Yahoo Screen Embed ===
Contributors: TJNowell, codeforthepeople
Tags: video, embed, oembed, yahoo
Requires at least: 3.7
Tested up to: 3.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily embed Yahoo Screen videos by pasting the URL into your WordPress post.

== Description ==

This plugin adds [OEmbed-style embedding](http://codex.wordpress.org/Embeds) of [Yahoo Screen](http://screen.yahoo.com/) videos. 

Paste the URL of a video’s Yahoo Screen page on a blank line by itself, save, and an embed will appear in its place on the front end. No need for any HTML code: it just works.

(The technical bit: Yahoo Screen allows embeds by appending `?format=embed` on the end of the video page URL, this plugin handles that for you, retrieving the embed code and showing it to your site visitors.)

== Installation ==
1. Upload to the "/wp-content/plugins/" directory.
1. Activate the plugin through the "Plugins" menu in WordPress.
1. Place a Yahoo video url in post content and save

== Frequently Asked Questions ==
= Does this handle https? =
Yes, it handles both http and https urls.

= I activated the plugin, but I can’t see a settings screen? =
Don’t worry, there isn’t one. It just works.

= I pasted a link but no video embed appears? =
You must paste the URL on its own line, and it must not be a link. The editor will sometimes auto-link the URL, and if this happens, you must unlink the URL using the editor toolbar.

Please note: Yahoo Screen’s help pages state that ‘it may not be possible to share all videos due to licensing agreements with content partners.’

= Can I use the embed shortcode? =
Yes, [embed] shortcodes will also work.

= Does this work for country-specific sites like fr.screen.yahoo.com? =
Yes it does.

== Changelog ==
= 1.0 =
* Initial release.

== Upgrade Notice ==
= 1.0 =
* Initial release.

