=== Zoorum Comments ===
Contributors: zoorum
Donate link: http://example.com/
Tags: comments, forum
Requires at least: 3.0.1
Tested up to: 3.6
Stable tag: 0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Zoorum Comments connects to your Zoorum forum. Each Wordpress article that is commented is also reflected as a new topic on your Zoorum forum.

== Description ==

The Zoorum Comments plugin is a comment function which connects to your Zoorum forum. Each Wordpress article that is commented is also reflected as a new topic on your Zoorum forum.
Zoorum is a new kind of forum platform, it's free and can be started instantly on http://www.zoorum.com.


== Installation ==

1. Create a forum at zoorum.com
2. Generate an API-key from your forums settings at zoorum.com
3. Unzip or otherwise install the plugin in your Wordpress plugin directory
4. Activate the plugin through the 'Plugins' menu in Wordpress
5. Enter the API-key and forum-url in Zoorum Comments settings through the Settings->Zoorum Comments menu
6. Try to remove any widgets displaying old deprecated comments.
7. Customize!
7.1 Add Zoorum widget to any registered widget areas/sidebars in Appearence->Widgets
7.2 Create a link to the registered forum in Appearence->Menus by adding a Custom Link to your forums URL.


== Frequently Asked Questions ==

= The thread was not created! =

Try enabling 'Display error messages' in Zoorum Comments settings. Which might help you pinpoint the error.

= Why is the number of comments wrong? =

The nr of comments will be updated when a user views the post in question or fetches it through ajax. Which means the 
static numbers of comments will not change until a proper refresh of the page is done.

= The meta-box contains an empty comment rss feed! =

Since the comments are replaced with comments from your Zoorum forum the rss feed will be more or less useless, try 
to disable it if you can.

= What is the plugin prepending my posts with? =

The plugin prepends the Zoorum topic titles with the category for the given post. if no category is set, the plugin
will use 'uncategorized' or whatever the standard fallback category is. If there are multiple categories, the plugin 
will choose the prepend like this: 1. the first parent found where both child and parent is categories for post, 2. 
the first category in alphabetic order. If the comments are done on non-post-object the plugin will select a fallback 
prepend.


== Screenshots ==

Soon...


== Changelog ==
= 0.9 =
* Added better support for Twenty Thirteen
= 0.8 =
* Internationalization, including Swedish translation.
* Refactorization to ensure future ease of development and code standards.
= 0.7 =
* Basic widget added.
* Prepends threadstarts with category name or if many, either the first or the highest parent
= 0.6.1 =
* Fixed jerky jquery effects.
= 0.6 =
* Tags from wordpress to zoorum implemented and even fancier javascript action
= 0.5.1 =
* Fancy javascript action
= 0.5 =
* Added ajax for comments
= 0.4 =
* First draft


== Upgrade Notice ==

= 0.9 =
* Added better support for Twenty Thirteen

= 0.8 =
* Internationalization!

= 0.7 =
* Widget!
* Category prepend in threadstarts

= 0.6 =
* Tags are supported!

= 0.5 =
Upgrade to get ajax for the comments

= 0.4 =
First draft


`<?php code(); // goes in backticks ?>`