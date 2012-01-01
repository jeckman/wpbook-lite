=== WPBook Lite ===
Contributors: johneckman
Tags: facebook, platform, application, blog, mirror
Stable tag: 1.1
Tested up to: 3.3
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

= 1.1 =
* Cleanup - have to check for FB_API_KEY and SECRET before making permissions calls. 

= 1.0 =
* Fork from WPBook full version
* Upgrade to Facebook SDK version 3.x

== To Do ==
* 