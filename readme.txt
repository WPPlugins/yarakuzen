=== YarakuZen ===
Contributors: yaraku, takayukister
Tags: translation, multilingual, localization, language
Requires at least: 4.4
Tested up to: 4.6
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The YarakuZen plugin helps you to translate your site using both machine translation and translation by professionals.

== Description ==

YarakuZen is an online service that helps translators manage their translation. You can use machine translation through it, and you can also send orders to professional translators. Plus, YarakuZen provides smart and powerful translation memory that should be a great help when you translate.

This WordPress plugin is officially supported by Yaraku, the operating company of YarakuZen.

= Getting Started with YarakuZen =

First, [set up an account](http://app.yarakuzen.com/wordpress) for a YarakuZen.com API key. Then copy the key into the WordPress admin menu ('Settings' > 'YarakuZen').

You'll find the YarakuZen box added to the editor screen for posts and pages. You can send your text to YarakuZen.com through it.

= Available Languages =

Currently the following languages are supported for the translation source and target.

* English
* Japanese
* Chinese (Simplified)
* Chinese (Traditional)
* Korean
* French
* German
* Spanish
* Italian
* Swedish
* Portuguese
* Indonesian
* Vietnamese
* Thai
* Malay
* Filipino
* Hindi

== Installation ==

1. Upload the entire `yarakuzen` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Changelog ==

= 1.1.1 =

* Confirmed compatibility with WordPress 4.6.

= 1.1 =

* Require WordPress 4.4 or higher.
* Add a HTTP header "User-Agent: YarakuZen WordPress Client" to each request to the YarakuZen API.
* Add a link on a Post editor page to the corresponding translation editor page on YarakuZen.
* Allow to send a callback request for update from YarakuZen.
* Removed Japanese translation in favor of translating on translate.wordpress.org.
