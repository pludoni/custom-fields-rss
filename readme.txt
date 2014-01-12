=== Custom Fields RSS ===
Contributors: webdeveloper.info
Donate link: http://www.webentwickler.de/hire-a-webdeveloper
Tags: rss, custom fields, extend rss
Requires at least: 3.0
Tested up to: 3.8
Stable tag: 1.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extends your RSS feed by the custom fields you have set in a post.


== Description ==

This plugin helps you making arbitrary custom fields show up in the RSS feed of your blog.

Your custom fields will appear as XML elements then,
whereas a custom field's name will make the text node's name or tag name in the RSS-XML
and the value of that custom field goes in between its opening and the closing tag.

e.g.

If you add a custom field to your post
named 'region'
filled with a text value 'Germany (Cologne)'
this will autmatically add an extra output to your standard RSS-XML like

    <region>Germany (Cologne)</region>

within the node of the appropriate item or post it has been added to.


== Installation ==

1. Upload `custom-fields-rss.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= What if there are multiple custom fields of the same name? =

Every entry will be added to the RSS XML as a new element.

= What if I use spaces or irregular characters for my fields' names ? =

Spaces in custom fields' names are not allowed.
The Plugin will make no changes to the characters given.

== Screenshots ==

1. Add a custom field to your post.
Choose an arbitrary name, don't use special characters and no spaces.

2. Your custom fields will appear in the post's RSS-XML.

== Changelog ==

= 1.0 =
* Basic usage.

== Upgrade Notice ==

There is no upgrade notice yet.

== Arbitrary section ==