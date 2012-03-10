=== WPBook Lite ===
Contributors: johneckman
Tags: facebook, platform, application, blog, mirror
Stable tag: 1.3
Tested up to: 3.3.1
Requires at least: 2.9

Based on wpbook ( http://wordpress.org/extend/plugins/wbook/ ), 
WPBook Lite enables cross-posting of blog items to facebook, 
and import of comments from Facebook back to WordPress.  

Unlike WPBook, WPBook Lite does NOT provide a view of your blog as a Facebook application,
which means (also unlike WPBook) it does not require that your blog be visible via https
connections. Setup of WPBook Lite should also be considerably simpler than WPBook. 

== Description ==

WPBook enables cross-posting of blog posts from WordPress into Facebook. 

Comments made against notifications posted to the wall (via WPBook Lite) or timeline
will be imported as comments inside WordPress. 

If your blog is available via HTTPS, you may wish to consider the full WPBook
instead - both are free. 

This plugin requires PHP 5. 

== Installation ==

1. Copy the entire wpbook-lite directory into your wordpress plugins folder,
   /wp-content/plugins/

   You should have a directory structure like this:
   /wp-content/plugins/wpbook-lite/wpbook_lite.php
   /wp-content/plugins/wpbook-lite/client/

2. Set up a New Application at http://www.facebook.com/developers/, obtaining
   a secret and API key.  
   
   Set the application type to "website"
      
3. Login to Wordpress Admin and activate the plugin

4. Using the WPBook menu, (Dashboard->Settings->WPBook) fill 
   in the appropriate information including Facebook application secret 
   and app ID, as well as your application canvas url. 

== Frequently Asked Questions ==


== Changelog ==

* Added hook to remove all post_meta wpbook lite creates on uninstall

= 1.3 = 
* New feature: added "message" to post-meta box; used it text is entered into
  it instead of the excerpt for the post. 
* Bugfix: duplicate post meta (custom fields) for each save of post
* Bugfix: Publishing to facebook regardless of setting meta-box to no
* Added permission check and grant for user_groups, required to publish to 
  non-public groups

= 1.2.6 = 
* Added 'auto-draft-to-publish' post transition state, to better support posts
  created by XML-RPC clients, including Windows Live Writer
* Cleaned up debugging output and simplified logic for try/catch blocks 
  around the calls to the Facebook API throughout the Facebook publish process. 
* Added check for WP_error object returned to wp_remote_request when fetching
  access token. 
* Update support link on settings page to point to the forum for wpbook-lite not
  the existing forum for wp-book
  
= 1.2.5 =
* Fixed bug in access_token capture, exposued more debug info to WPBook Lite
  settings page. 

= 1.2.4 =
* Update mechanism for capturing access_token to work on sites where fopen
  is not allowed, using wp_remote_request. 

= 1.2.3 = 
 * Restrict global filter on gravatars to only comments

= 1.2.2 =
* Wrap try/catch blocks around Facebook permission calls in admin page -
  should more robustly handle bad data entered in profile ID, app ID, secret. 

= 1.2.1 =
* Bugfix: Delete data from db when uninstalled

= 1.2 = 
* Add option to publish as link, note, or post

= 1.1 =
* Cleanup - have to check for FB_API_KEY and SECRET before making permissions calls. 

= 1.0 =
* Fork from WPBook full version
* Upgrade to Facebook SDK version 3.x

== To Do ==
* 