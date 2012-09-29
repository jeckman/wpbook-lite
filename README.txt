=== WPBook Lite ===
Contributors: johneckman
Tags: facebook, platform, application, blog, mirror
Requires at least: 2.9
Stable tag: 1.5.3
Tested up to: 3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Based on WPBook ( http://wordpress.org/extend/plugins/wbook/ ), 
WPBook Lite enables cross-posting of blog items to Facebook, 
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
= 1.5.3 =
* Fixed typo in wpbook_lite_cron where it had degub instead of debug - thanks
  to jeff in wpbook_lite support forum for noting it
* Updating handling of wpbook_lite_message in post_meta

= 1.5.2 =
* Too quick on the fix in 1.5.1.  Fixed again with code from WPBook

= 1.5.1 = 
* Fixed undefined $response variable in wpbook_cron - threw warnings when 
  run, may have interfered with comment import. 

= 1.5 = 
* Fixed error introduced somewhere around 1.4.1 involving storing access tokens
  for long-lived tokens. Wasn't properly storing full length of token which 
  cause this to fail for some users. 
  
* Fixed bad define() statement for setting WPBOOKDEBUG to false if the file
  handler fp comes back not writeable. This resulted in warnings being thrown
  for users whose permissions did not allow writing to the file system. 

= 1.4.3 = 
* Fixed typo in wpbook_cron.php thanks to jeff@pyebrook.com 
  (http://wordpress.org/support/topic/plugin-wpbook-lite-minor-typo-in-wpbook-cron)
* Updated add_options_page() function call in wpbook-lite.php from same forum
  thread. 
* Fixed extraneous close div tags in admin interface
* Removed unused Jquery functions from admin interface javascript
* Deleted unused PNG files from admin interface
  
= 1.4.2 = 
* Eliminated check on page access_token as that really just comes from the user
  access token - hope this will solve issues with page publishing
  
= 1.4.1 =
* Simple typo bug in publishing to Facebook - EVERYONE MUST UPGRADE as 1.4
  will not work

= 1.4 = 
* Updated logic to accomodate Facebook's deprecation of "offline_access" tokens. 
  This will mean tokens (for newly created apps) will expire every 60 days and 
  require re-authentication. WPBook Lite checks for token validity every time it
  publishes and every time the cron task for comment import runs (once an hour). 

= 1.3.1 = 
* Added hook to remove all post_meta wpbook lite creates on uninstall
* Added sslverify false for getting token from Facebook to solve certificate
  errors
  
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