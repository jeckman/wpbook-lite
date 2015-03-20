<?php
/*
Plugin Name: WPBook Lite
Plugin URI: http://wpbook.net/
Description: Plugin to cross post Wordpress Blog posts to Facebook. 
Author: John Eckman
Author URI: http://johneckman.com
Version: 1.6.5
Stable tag: 1.6.5

*/
  
/*
Note: This plugin draws inspiration (and sometimes code) from: 
   Steve Atty's Wordbooker (http://wordpress.org/extend/plugins/wordbooker/ )
   the WP admin interface was inspired by (read: copied from) Ozh's YOURLS: WordPress to Twitter 
   (http://wordpress.org/extend/plugins/yourls-wordpress-to-twitter/)
*/

/*  
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (version_compare(PHP_VERSION, '5.0.0', '<')) {
  wp_die("Sorry, but you can't run this plugin, it requires PHP 5 or higher.");
} else { 
  include(WP_PLUGIN_DIR .'/wpbook-lite/includes/wpbook_lite_cron.php');
}
  
$_SERVER['REQUEST_URI'] = ( isset($_SERVER['REQUEST_URI']) ? 
  $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'] 
  . (( isset($_SERVER['QUERY_STRING']) ? '?' 
  . $_SERVER['QUERY_STRING'] : '')));

// activation, install, uninstall need work  
function wpbook_lite_activate() {
  wpbook_lite_activation_check();
  $dummy=wp_clear_scheduled_hook('wpbook_lite_cron_job');
	$dummy=wp_schedule_event(time(), 'hourly', 'wpbook_lite_cron_job');
}

function wpbook_lite_deactivate() {
  wp_clear_scheduled_hook('wpbook_lite_cron_job');
}

function wpbook_lite_is_authorized() {
  global $user_level;
	if (function_exists("current_user_can")) {
		return current_user_can('activate_plugins');
	} else {
		return $user_level > 5;
	}
}

function wpbook_lite_getAdminOptions() {
	$wpbookLiteOptions = get_option('wpbookLiteAdminOptions');
	if (!empty($wpbookLiteOptions)) {
		foreach ($wpbookLiteOptions as $key => $option)
			$wpbookLiteAdminOptions[$key] = $option;
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") { 
		    $wpbookLiteAdminOptions['proto'] = "https"; 
		} else {
		    $wpbookLiteAdminOptions['proto'] = "http";
		}  
	} else {
		$wpbookLiteAdminOptions = ''; 
	}
	return $wpbookLiteAdminOptions;
}
  
function setAdminOptions($wpbook_installation,
							$fb_api_key, 
							$fb_secret, 
							$fb_admin_target,
							$fb_page_target,
							$show_advanced_options,
							$stream_publish,
							$stream_publish_pages,
							$show_errors,
							$import_comments,
							$approve_imported_comments,
							$num_days_import,
							$imported_comments_email,
							$infinite_session_key,
							$wpbook_enable_debug,
							$wpbook_use_global_gravatar,
							$wpbook_as_note,
							$wpbook_as_link,
							$wpbook_target_group,
							$wpbook_disable_sslverify) {
  $wpbookLiteAdminOptions = array('wpbook_installation' => $wpbook_installation,
                              'fb_api_key' => $fb_api_key,
                              'fb_secret'  => $fb_secret,
                              'fb_admin_target' => $fb_admin_target,
                              'fb_page_target' => $fb_page_target,
                              'show_advanced_options' => $show_advanced_options,
                              'stream_publish' => $stream_publish,
                              'stream_publish_pages' => $stream_publish_pages,
                              'show_errors' => $show_errors,
                              'import_comments' => $import_comments,
                              'approve_imported_comments' => $approve_imported_comments,
                              'num_days_import' => $num_days_import,
                              'imported_comments_email' => $imported_comments_email,
                              'infinite_session_key' => $infinite_session_key,
                              'wpbook_enable_debug' => $wpbook_enable_debug,
                              'wpbook_use_global_gravatar' => $wpbook_use_global_gravatar,
                              'wpbook_as_note' => $wpbook_as_note,
							  'wpbook_as_link' => $wpbook_as_link,
                              'wpbook_target_group' => $wpbook_target_group,
                              'wpbook_disable_sslverify' => $wpbook_disable_sslverify, 
                              );
  update_option('wpbookLiteAdminOptions', $wpbookLiteAdminOptions);
}
  
add_action('admin_menu', 'wpbook_lite_options_page');						   
function wpbook_lite_options_page() {
	if (function_exists('add_options_page')) {
		$wpbook_lite_plugin_page = add_options_page('WPBook Lite', 'WPBook Lite','manage_options', basename(__FILE__), 'wpbook_lite_subpanel');
	   add_action( 'load-'. $wpbook_lite_plugin_page, 'wpbook_lite_admin_head' );
	}
} 

//function to add css and java to the header of the admin page 
function wpbook_lite_admin_head() {
	wp_enqueue_script('wpbook_lite_admin_javascript.js',  WP_PLUGIN_URL .'/wpbook-lite/admin_includes/wpbook_admin_javascript.js');
	wp_enqueue_style('wpbook_lite_admin_styles.css',  WP_PLUGIN_URL .'/wpbook-lite/admin_includes/wpbook_admin_styles.css');
 }
  
function wpbook_lite_subpanel() {
  if (wpbook_lite_is_authorized()) {
    global $current_user;
    get_currentuserinfo(); 
    $wpbookLiteAdminOptions = wpbook_lite_getAdminOptions();
    if ( ! empty( $_POST ) && check_admin_referer( 'update_settings', 'wpbook_lite_admin_nonce') 
      	&& isset($_POST['fb_api_key']) && isset($_POST['fb_secret']) && isset($_POST['fb_admin_target']) ) { 
      $fb_api_key = preg_replace("#[^0-9]#", "",$_POST['fb_api_key']);
      $fb_secret = $_POST['fb_secret'];
      $fb_admin_target = preg_replace("#[^0-9]#", "",$_POST['fb_admin_target']);
      if(isset($_POST['fb_page_target'])) {
      	$fb_page_target = preg_replace("#[^0-9]#", "",$_POST['fb_page_target']);
      } else {
      	$fb_page_target = '';
      }
      if(isset($_POST['show_advanced_options'])) {
      	$show_advanced_options = $_POST['show_advanced_options'];
      } else {
      	$show_advanced_options = ''; 
      }
      if(isset($_POST['stream_publish'])) {
      	$stream_publish = $_POST['stream_publish'];  
      } else {
      	$stream_publish = ''; 
      }
      if(isset($_POST['stream_publish_pages'])) {
      	$stream_publish_pages = $_POST['stream_publish_pages'];
      } else {
      	$stream_publish_pages = ''; 
      }
      if(isset($_POST['show_errors'])) {
      	$show_errors = $_POST['show_errors']; 
      } else {
      	$show_errors = ''; 
      }
      if(isset($_POST['import_comments'])) {
      	$import_comments = $_POST['import_comments'];
      } else {
      	$import_comments = ''; 
      }
      if(isset($_POST['approve_imported_comments'])) {
      	$approve_imported_comments = $_POST['approve_imported_comments'];
      } else {
      	$approve_imported_comments = ''; 
      }
      if(isset($_POST['approve_imported_comments'])) {
      	$num_days_import = preg_replace("#[^0-9]#", "",$_POST['num_days_import']);  
      } else {
      	$num_days_import = ''; 
      }
      if(isset($_POST['imported_comments_email'])) {
      	$imported_comments_email = $_POST['imported_comments_email']; 
      } else {
      	$imported_comments_email = ''; 
      } 
      if(isset($_POST['infinite_session_key'])) {
      	$infinite_session_key = $_POST['infinite_session_key']; 
      } else {
      	$infinite_session_key = ''; 
      } 
      if(isset($_POST['wpbook_enable_debug'])) {
      	$wpbook_enable_debug = $_POST['wpbook_enable_debug'];
      } else {
      	$wpbook_enable_debug = ''; 
      }
	  $wpbook_as_note = 'post'; // default to post type of POST unless otherwise set
			if(isset($_POST['post_as']) && $_POST['post_as']=='note')
				$wpbook_as_note = 'note';
			if(isset($_POST['post_as']) && $_POST['post_as']=='link')
				$wpbook_as_note = 'link';      
	  $wpbook_as_link = 'post';
		if(isset($_POST['page_post_as']) && $_POST['page_post_as'] == 'link')
			$wpbook_as_link = 'link';
				
	  if(isset($_POST['wpbook_target_group'])) {
	  	$wpbook_target_group = $_POST['wpbook_target_group'];
	  } else {
	  	$wpbook_target_group = ''; 
	  }
      if(isset($_POST['wpbook_disable_sslverify'])) {
      	$wpbook_disable_sslverify = $_POST['wpbook_disable_sslverify'];
      } else {
      	$wpbook_disable_sslverify = ''; 
      }
      if(isset($_POST['wpbook_use_global_gravatar'])) {
      	$wpbook_use_global_gravatar = $_POST['wpbook_use_global_gravatar'];
      } else {
      	$wpbook_use_global_gravatar = '';
      }
      setAdminOptions(1, $fb_api_key, $fb_secret,$fb_admin_target,$fb_page_target,
                    $show_advanced_options,$stream_publish,
                    $stream_publish_pages,$show_errors,
                    $import_comments,$approve_imported_comments,$num_days_import,
                    $imported_comments_email,$infinite_session_key,
                    $wpbook_enable_debug,
                    $wpbook_use_global_gravatar,$wpbook_as_note,$wpbook_as_link,
                    $wpbook_target_group,$wpbook_disable_sslverify);
      $flash = "Your settings have been saved. ";
    } elseif ((isset($wpbookLiteAdminOptions['fb_api_key'])) && ($wpbookLiteAdminOptions['fb_api_key'] != "") && (isset($wpbookLiteAdminOptions['fb_secret'])) && ($wpbookLiteAdminOptions['fb_secret'] != "") && ($wpbookLiteAdminOptions['fb_admin_target'] != "")){
      $flash = "";
    } elseif (! empty( $_POST ) && ! check_admin_referer( 'update_settings', 'wpbook_lite_admin_nonce')) {
      $flash = "Admin nonce failed";
    } else {
      $flash = "Please complete all necessary fields";
    } // end of if posting
  } else {
    $flash = "You don't have enough access rights.";
  } // end of first if wpbook_lite_is_authorized() 
  
    if (wpbook_lite_is_authorized()) {
      $wpbookLiteAdminOptions = wpbook_lite_getAdminOptions();
      //set the "smart" defaults on install this only works once the page has been refeshed
      if ((isset($wpbookLiteAdminOptions['wpbook_installation'])) && ($wpbookLiteAdminOptions['wpbook_installation'] != 1)) {  
        setAdminOptions(1,null,null,null,null,false,
                        false,false,false,false,false,7,"facebook@openparenthesis.org",
						null,false,false,false,null,null,false);
      }
      if ($flash != '') echo '<div id="message"class="updated fade">'
        . '<p>' . $flash . '</p></div>'; 
      if(!class_exists('Facebook')) {  
		include_once(WP_PLUGIN_DIR . '/wpbook-lite/includes/client/facebook.php');  
	  }
	  ?>
      <div class="wrap">
      
      <div class="icon32" id="icon-plugins"><br/></div>
      <h2>WPBook Lite Setup</h2>
      <div id="wpbook_admin_header">
		<div class="wpbook_logo">
			<a href="http://wpbook.net/"><img src="<?php echo WP_PLUGIN_URL; ?>/wpbook-lite/admin_includes/images/wpbook_logo.png"></a>
		</div>
		<div class="wpbook_top_text">
    <p>This plugin cross-posts your blog posts to the wall of your profile, a fan page, an application profile page,
  or a group page, and enables you to add a tab for your fan page. It also imports
  comments made against wall posts which originated in WordPress.</p>
<p><a href="http://wpbook.net/docs/install/" target="_blank">Detailed instructions</a></p>
  </div>
	</div>
	<!-- START Required Options --> 
	<h3>Required Settings</h3>
	<div>
	 <p>To use this plugin, you must <a href="http://www.facebook.com/developers/createapp.php">create
   a Facebook application</a>. After you've obtained the necessary info, fill in
   both your application's App_ID and Secret keys as well as your Facebook profile ID.</p>
	<?php 
      echo '<form action="'. $_SERVER["REQUEST_URI"] .'" method="post">'; 
      echo '<p>Facebook App ID: <input type="text" name="fb_api_key" value="';
	  if(isset($wpbookLiteAdminOptions['fb_api_key'])) {
	  	echo htmlentities($wpbookLiteAdminOptions['fb_api_key']);
	  }
	  echo '" size="35" />';
      if((isset($wpbookLiteAdminOptions['fb_api_key'])) && (!empty($wpbookLiteAdminOptions['fb_api_key']))) {
		echo ' <a href="http://www.facebook.com/apps/application.php?id=' . $wpbookLiteAdminOptions['fb_api_key'] . '" target="_new"> Visit this app profile</a>';
      }
      echo '</p>';
      echo '<p>Facebook App Secret: ';
      echo '<input type="text" name="fb_secret" value="';
      if(isset($wpbookLiteAdminOptions['fb_secret'])) {
      	echo htmlentities($wpbookLiteAdminOptions['fb_secret']);
      }
      echo '" size="35" /></p>';
      echo '<p>YOUR Facebook Profile ID: <input type="text" name="fb_admin_target" value="';
      if(isset($wpbookLiteAdminOptions['fb_admin_target'])) {
      	echo preg_replace("#[^0-9]#","",htmlentities($wpbookLiteAdminOptions['fb_admin_target']));
      }
      echo '" size="15" />';
      if(isset($wpbookLiteAdminOptions['fb_admin_target']) && !empty($wpbookLiteAdminOptions['fb_admin_target'])) {
        echo ' <a href="http://www.facebook.com/profile.php?id=' . $wpbookLiteAdminOptions['fb_admin_target'] . '" target="_new">visit this profile</a>';
      }
      
      echo '</p><p>Note: if you don\'t know your ID you can get it by visiting <a href="http://graph.facebook.com/USERNAME" target="_blank">http://graph.facebook.com/YOUR_USERNAME</a> <br/>Replacing the "YOUR_USERNAME" 
      with your Facbook login. (More info on <a href="http://socialmediaseo.net/2010/02/20/how-to-find-facebook-id/">finding your Page ID</a>).'; 
      echo '</p>'; 
  ?></div> <!-- END Required Options --> 		
  <?php 
    if( (!empty($wpbookLiteAdminOptions['fb_admin_target'])) && 
	    (!empty($wpbookLiteAdminOptions['fb_secret'])) && 
		(!empty($wpbookLiteAdminOptions['fb_api_key']))
	  ) {
?>
  <h3>Grant Permissions</h3>
	<div>
		<p>You have to grant permissions now, so that the Facebook application you've just created can access
		your personal profile and post on your behalf.</p>
		
		<P><a href="https://www.facebook.com/dialog/oauth?client_id=<?php echo htmlentities($wpbookLiteAdminOptions['fb_api_key']); ?>&redirect_uri=<?php echo home_url(); ?>/%3Fwpbook=oauth&scope=read_stream,publish_actions,manage_pages,user_groups">Generate Access Token</a></p>
	 
		<p>That will trigger a multiple step process which will generate an access token and store it - you should see your blog home 
		page load with a message at the top declaring success or offering an error. Revisit this page and you should see the access code stored below: </p>
		<?php 
			echo "<strong>Stored access token = " . get_option('wpbook_lite_user_access_token') . '</strong><br/><br/>';
		?>
		<p><strong>Note:</strong> If you already have a stored access_token, you should not need to generate a new one, but you can if need be - it won't hurt anything. 
		This stored access_token is required EVEN IF YOU DO NOT PLAN TO POST TO YOUR PERSONAL PROFILE WALL</p>
		
<?php		  
	if((!empty($wpbookLiteAdminOptions['fb_page_target'])) && ($wpbookLiteAdminOptions['stream_publish_pages'] == "true")) {
	?>
	<h4>Permissions for pages</h4>
	<?php 
    echo "<p>You've indicated you wish to publish to this page: ". $wpbookLiteAdminOptions['fb_page_target'] ."</p>";
    echo "<!-- start hiding for session warnings";
	  $api_key = $wpbookLiteAdminOptions['fb_api_key'];	
	  $secret  = $wpbookLiteAdminOptions['fb_secret'];
	  $access_token = get_option('wpbook_lite_user_access_token','');
	  $wpbook_lite_fb_error = false; 
	  try {
		$facebook = new Facebook(array(
                              'appId'  => $api_key,
                              'secret' => $secret,						  
                              )
							);
      } catch (FacebookApiException $e) {
		echo '--> <p>Unable to access Facebook API - app id or secret may be wrong.</p> <!-- ';
		$wpbook_lite_fb_error = true;
	  }
	  if($wpbook_lite_fb_error == false) {
		try {
			$facebook->setAccessToken($access_token);
		} catch (FacebookAPIException $e) {
			echo '--> <p>Could not set access token. ' . $e->getMessage() .'Error code: '. $e->getCode()  .'</p> <!-- ';
		}
		try {
			$uid = $facebook->getUser();
		} catch (FacebookAPIException $e) {
			echo '--> <p>Could not get userid from Facebook. ' . $e->getMessage() .'Error code: '. $e->getCode()    .'</p><!-- ';
		}
		try {
			$fb_response = $facebook->api('/'. $uid .'/accounts'); 
		} catch (FacebookAPIException $e) {
			echo '--> <p>Could not get accounts list from Facebook. ' . $e->getMessage() .'Error code: '. $e->getCode()    .'</p><!-- ';
			$fb_response = false; 
		}	
		echo " end hiding -->";	
		if($fb_response) {
			foreach($fb_response['data'] as $page) {
				if ($page['id'] == $wpbookLiteAdminOptions['fb_page_target']) {
					$my_wp_page_name = $page['name'];
					if($page['access_token']) {
						update_option('wpbook_lite_page_access_token',$page['access_token']);
						echo '<p>An access token corresponding to the page titled '. $my_wp_page_name .' has been stored.</p>';
						echo '<p>It is ' . get_option('wpbook_lite_page_access_token','') . '</p>';
					} else {
						echo '<p><strong>ERROR: No access token corresponding to this page was ';
						echo 'found or stored.</strong> This likely means that either: ';
						echo '<ul><li>The PageID is entered incorrectly in WPBook settings, or</li>';
						echo '<li>The Facebook profile currently logged in has not granted the ';
						echo '"manage_pages" permission appropriately, or</li>';
						echo '<li>The Facebook profile logged in (and for which an access token ';
						echo 'has been stored) is not eligible to grant manage_pages for the page ';
						echo 'in question (not an admin).</li></ul></p>';
					}
				}
			}
		} // end if fb_response
	  }	// end if wpbook_lite_fb_error is false	
	} // end if fb_page_target is set
?>
<h4>Note on permissions for Groups</h4>
		<p>If you are going to post to a group, you will need to have a user access token stored (see above), and
		that user must have permission to post on the group's wall.</p>
	</div>
<?php 
	} // end if for required fields present
?>  
	<!-- START Stream Options --> 
	<h3>Stream/Wall Options</h3>
	<div>
	<?php 
      if(empty($wpbookLiteAdminOptions['fb_secret']) || empty($wpbookLiteAdminOptions['fb_api_key']) || empty($wpbookLiteAdminOptions['fb_admin_target'])) {  
        echo '<p><strong>Once your Facebook application is established by filling out the required information, return to edit streaming options.</strong></p>';
      } 
      else {  
        echo '<p>These settings all impact how WPBook publishes to Facebook walls, and depend on appropriate permissions being set in Facebook.</p>';
        echo '<p><strong>Stream Profile/Page Options</strong><br/>
        <p><input type="checkbox" name="stream_publish" value="true" ';
        if( htmlentities($wpbookLiteAdminOptions['stream_publish']) == "true") {
          echo("checked");
        }
        echo ' id="set_1"> Publish new posts to <a href="http://www.facebook.com/profile.php?id=' . $wpbookLiteAdminOptions['fb_admin_target'] .'" target="_new">YOUR Facebook Wall</a></p> ';
               
				
        echo '<p><input type="checkbox" name="stream_publish_pages" value="true" ';
        if( htmlentities($wpbookLiteAdminOptions['stream_publish_pages']) == "true") {
          echo("checked");
        }
        echo ' id="set_2" > Publish new posts to the wall of this page/group: ';
echo '<p class="wpbook_hidden wpbook_option_set_2 sub_options">Page ID: <input type="text" name="fb_page_target" value="';
      echo preg_replace("#[^0-9]#","",htmlentities($wpbookLiteAdminOptions['fb_page_target'])) .'" size="15" /> ';
      echo ' (Information on <a href="http://socialmediaseo.net/2010/02/20/how-to-find-facebook-id/">finding your Page ID</a>)</p>';
      
	  				

	  
	  
      echo '<p class="wpbook_hidden wpbook_option_set_2 sub_options">Group ID: <input type="text" name="wpbook_target_group" value="';
        echo preg_replace("#[^0-9]#","",htmlentities($wpbookLiteAdminOptions['wpbook_target_group'])) .'" size="15" /> ';
        echo ' (Generally your GroupID should be in your url, like: http://www.facebook.com/group.php?gid=149948248362737 - the gid is the group ID). </p>';
      echo '<p class="sub_options"><strong>Profile Post Type Options</strong> (Applied when posting to an individual profile)<br/>';
				echo '<div style="padding-left: 10px">';
				echo '<input type="radio" value="post" name="post_as" ';
				if(($wpbookLiteAdminOptions['wpbook_as_note'] == 'post') || ($wpbookLiteAdminOptions['wpbook_as_note'] == '')) 
					echo 'checked';	
				echo " > Publish as Posts. (Default: if you don't know the difference, don't change this.)</p>";

				echo '<p>';
				echo '<input type="radio" value="note" name="post_as" ';
				if(($wpbookLiteAdminOptions['wpbook_as_note'] == 'note') || ($wpbookLiteAdminOptions['wpbook_as_note'] == 'true')) 
					echo 'checked';
				echo ' > Publish as Notes</p>';
		
				echo '<p>';
				echo '<input type="radio" value="link" name="post_as" ';
				if($wpbookLiteAdminOptions['wpbook_as_note'] == 'link') 
					echo 'checked';
				echo ' > Publish as Links. (Note: This assumes appropriate Facebook open graph metadata is provided by your blog.)</p>';
				echo '</p></div>'; // end post type options		
	  
	  echo '<p class="sub_options"><strong>Page/Group Post Type Options</strong> (Applied when posting to a Page or Group).<br/>';
				echo '<div style="padding-left: 10px">';
				echo '<input type="radio" value="post" name="page_post_as" ';
				if(($wpbookLiteAdminOptions['wpbook_as_link'] == 'post') || ($wpbookLiteAdminOptions['wpbook_as_note'] == '')) 
					echo 'checked';	
				echo " > Publish as Posts. (Default: if you don't know the difference, don't change this.)</p>";

				echo '<p>';
				echo '<input type="radio" value="link" name="page_post_as" ';
				if($wpbookLiteAdminOptions['wpbook_as_link'] == 'link') 
					echo 'checked';
				echo ' > Publish as Links. (Note: This assumes appropriate Facebook open graph metadata is provided by your blog.)</p>';
				echo '</p></div>'; // end post type options		
	  
	  echo '<p><strong>Stream Debug Options</strong><br/><input type="checkbox" name="wpbook_enable_debug" value="true" ';
      if( htmlentities($wpbookLiteAdminOptions['wpbook_enable_debug']) == "true") {
        echo("checked");
      }
      echo ' id="wpbook_enable_debug" > Enable WPBook to create a debug file';
      echo '<br/><input type="checkbox" name="show_errors" value="true" ';
      if( htmlentities($wpbookLiteAdminOptions['show_errors']) == "true") {
        echo("checked");
      }
      echo ' id="show_errors" > Show errors posting to Facebook Stream </p>';
        
        echo '<p><input type="checkbox" name="wpbook_disable_sslverify" value="true" ';
        if( htmlentities($wpbookLiteAdminOptions['wpbook_disable_sslverify']) == "true") {
          echo("checked");
        }
        echo ' id="wpbook_disable_sslverify" > Disable Facebook SSL verification</p>';
  

      echo '<p><strong>Stream Comment Options</strong><br/>
      <input type="checkbox" name="import_comments" value="1" ';
      if( htmlentities($wpbookLiteAdminOptions['import_comments']) == "1") {
        echo("checked");
      }
      echo ' id="set_3" > Import comments from Facebook Walls <br/>';
      echo '<p class="wpbook_hidden wpbook_option_set_3 sub_options"><input type="checkbox" name="approve_imported_comments" value="1" ';
      if( htmlentities($wpbookLiteAdminOptions['approve_imported_comments']) == "1") {
        echo("checked");
      }
      echo ' id="approve_imported_comments" > Automatically approve imported Wall comments</p>';

      echo '<p class="wpbook_hidden wpbook_option_set_3 sub_options">For how many days should WPBook look for comments on Facebook Walls?: ';
      echo '&nbsp;<input type="text" name="num_days_import" value="';
      echo htmlentities($wpbookLiteAdminOptions['num_days_import']) .'" size="2" /></p>';      

      echo '<p class="wpbook_hidden wpbook_option_set_3 sub_options">What email address should WPBook associate with imported comments? ';
      echo '&nbsp;<input type="text" name="imported_comments_email" value="';
      echo htmlentities($wpbookLiteAdminOptions['imported_comments_email']) .'" size="40" /></p>';      
      //gravatar outside Facebook
        echo'<p class="wpbook_option_set_5"> <input type="checkbox" name="wpbook_use_global_gravatar" value="true"';
      if( htmlentities($wpbookLiteAdminOptions['wpbook_use_global_gravatar']) == "true") {
        echo("checked");
      }
      echo '> Show Facebook avatars on imported comments';
      echo '</p>';
	  echo '</div>'; 
      } //end hide all settings if required fields aren't filled in
      ?>
	
<?php 
	  wp_nonce_field( 'update_settings', 'wpbook_lite_admin_nonce' );
	  echo '<p><input type="submit" value="Save" class="button-primary"';
      echo ' name="wpbook_save_button" /></form></p>';
      echo'<div id="help">';
      echo '<h2>Need Help?</h2>';
      echo '<p>If you need help setting up this application first read the <a href="http://wpbook.net/docs/install" target="_blank"> install instructions</a>.';
      echo 'Support can also be found on <a href="http://wordpress.org/extend/plugins/wpbook-lite/" target="_blank">the plugin page</a> </p><h3>Thanks for using WPBook Lite!</h3>';
      echo'</div>';
  } else {
    echo '<div class="wrap"><p>Sorry, you are not allowed to access ';
    echo 'this page.</p></div>';
  }
}
  
/*
 * This function handles streaming publish to Facebook. 
 * It includes publish_to_facebook.php
 */
function wpbook_lite_publish_to_facebook($post_ID) {
  if (!version_compare(PHP_VERSION, '5.0.0', '<')) {
    include_once(WP_PLUGIN_DIR .'/wpbook-lite/includes/publish_to_facebook.php');
  } else {
    wp_die("Sorry, but you can't run this plugin, it requires PHP 5 or higher.");
  }
  wpbook_lite_safe_publish_to_facebook($post_ID);
} // end of function wpbook_publish_to_facebook
  
/*
 * Use postmeta to enable users to turn off streaming on case-by-case basis
 * Based on how Alex King's Twitter Tools handles the same case for pushing
 * posts to twitter
 */
function wpbook_lite_meta_box() {
	global $post;
	$wpbook_lite_publish = get_post_meta($post->ID, 'wpbook_lite_fb_publish', true);
	$wpbook_lite_message = get_post_meta($post->ID, 'wpbook_lite_message', true); 

	if ($wpbook_lite_publish == '') {
		$wpbook_lite_publish = 'yes';
	}
	echo '<p>'.__('Publish this post to Facebook Wall?', 'wpbook_lite').'<br/>';
	echo '<input type="radio" name="wpbook_lite_fb_publish" id="wpbook_lite_fb_publish_yes" value="yes" ';
	checked('yes', $wpbook_lite_publish, true);
	echo ' /> <label for="wpbook_lite_fb_publish_yes">'.__('Yes', 'wpbook_lite').'</label> &nbsp;&nbsp;';
	echo '<input type="radio" name="wpbook_lite_fb_publish" id="wpbook_lite_fb_publish_no" value="no" ';
	checked('no', $wpbook_lite_publish, true);
	echo ' /> <label for="wpbook_lite_fb_publish_no">'.__('No', 'wpbook_lite').'</label>';
	echo '</p>';
	echo '<p>'.__('Message for Facebook post: (plain text)','wpbook_lite').'<br/>';
	echo '<p><textarea cols="60" rows="4" style="width:95%" name="wpbook_lite_message" id="wpbook_lite_message">';
	echo $wpbook_lite_message;
	echo '</textarea></p>';
	do_action('wpbook_lite_store_post_options');
}
  
function wpbook_lite_add_meta_box() {
	global $wp_version;
	if (version_compare($wp_version, '2.7', '>=')) {
		add_meta_box('wpbook_lite_post_form','WPBook-Lite', 'wpbook_lite_meta_box', 'post', 'side');
	} else {
		add_meta_box('wpbook_lite_post_form','WPBook-Lite', 'wpbook_lite_meta_box', 'post', 'normal');
	}
}
  
function wpbook_lite_store_post_options($post_id, $post = false) {  
	$wpbookLiteAdminOptions = wpbook_lite_getAdminOptions();
	$post = get_post($post_id);
	$stored_meta = get_post_meta($post->ID, 'wpbook_lite_fb_publish',TRUE);
	$posted_meta = $_POST['wpbook_lite_fb_publish'];
	$wpbook_lite_message = $_POST['wpbook_lite_message']; 
	$save = false;
	/* if there is $posted_meta, that takes priority over stored */
	if (!empty($posted_meta)) { 
		$posted_meta == 'yes' ? $meta = 'yes' : $meta = 'no';
		$save = true;
	}
	/* if no posted meta, check stored meta */ 
	else if (empty($stored_meta)) {
		/* if no stored meta, but streaming publishing is on, default to yes */
		if (($wpbookLiteAdminOptions['stream_publish']) || ($wpbookLiteAdminOptions['stream_publish_pages'])) {
			$meta = 'yes';
		} else {
		$meta = 'no';
		}
		$save = true;
		/* if there is stored meta, and user didn't touch it, don't save */ 
	} else {
		$save = false;
	}
    
	if ($save) {
		update_post_meta($post_id, 'wpbook_lite_fb_publish', $meta); 
	}
	update_post_meta($post_id, 'wpbook_lite_message', $wpbook_lite_message); 
	// checking to see if the meta message variable exists before setting it
	if(isset($wpbook_lite_message) && ($wpbook_lite_message != '')) {
		update_post_meta($post_id, 'wpbook_lite_message', $wpbook_lite_message);
	}
	if ((isset($wpbook_lite_message)) && ($wpbook_lite_message == '')) {
		delete_post_meta($post_id, 'wpbook_lite_message');  
	}
}
add_action('draft_post', 'wpbook_lite_store_post_options', 1, 2);
add_action('publish_post', 'wpbook_lite_store_post_options', 1, 2);
add_action('save_post', 'wpbook_lite_store_post_options', 1, 2);

// show facebook avatar as gravatar even outside facebook
function wpbook_lite_get_global_facebook_avatar($avatar, $comment, $size="50") {
  $wpbookLiteOptions = get_option('wpbookLiteAdminOptions');
  if (!empty($wpbookLiteOptions)) {
    foreach ($wpbookLiteOptions as $key => $option)
      $wpbookLiteAdminOptions[$key] = $option;
  }
  if(isset($wpbookLiteAdminOptions['wpbook_use_global_gravatar'])
  	&& ($wpbookLiteAdminOptions['wpbook_use_global_gravatar'] =="true")
	&& (is_object($comment))
	&& (isset($comment->comment_author_email))
	&& ($comment->comment_author_email == $wpbookLiteAdminOptions['imported_comments_email'])) {
    $author_url = get_comment_author_url();
    $email = get_comment_author_email();    
	$size="50";
    $parse_author_url = (parse_url($author_url));
    if(preg_match("@^(?:https://)?(?:www\.)?facebook@i",trim($author_url))){
      $parse_author_url_q = $parse_author_url['query'];
      if(preg_match('/id[=]([0-9]*)/', $parse_author_url_q, $match)){
        $fb_id = "/".$match[1];
      }
      else { 
        /* have to account here for multiple options
         * http://facebook.com/profile.php?id=12234
         * http://www.facebook.com/pages/PageName/1234
         * Application pages, groups?
         */
        if(strrpos($parse_author_url['path'],'pages')) {
          $fb_id_array = explode('/',$parse_author_url['path']);
          $size = count($fb_id_array) -1;
          $fb_id = "/" . $fb_id_array[$size]; 
        } else {
          $fb_id = $parse_author_url['path'];
        }
      }
      $grav_url= "https://graph.facebook.com".$fb_id."/picture?type=square";
    }
    else {
      $grav_url = "http://www.gravatar.com/avatar/" . md5(strtolower($email))."?s=".$size;
    }
    $grav_img = "<img src='".$grav_url."'/>";
    return $grav_img; 
  }
  return $avatar;
}
  
/**
  * Thanks Otto - http://lists.automattic.com/pipermail/wp-hackers/2009-July/026759.html
  */
function wpbook_lite_activation_check(){
  global $wp_version;
  if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    deactivate_plugins(basename(__FILE__)); // Deactivate ourself
    wp_die("Sorry, but you can't run this plugin, it requires PHP 5 or higher.");
  }
  if (version_compare($wp_version, '2.9', '<')) {
    wp_die("This plugin requires WordPress 2.9 or greater.");
  }
}


// based on sample code here:
// http://willnorris.com/2009/06/wordpress-plugin-pet-peeve-2-direct-calls-to-plugin-files  
// thanks will  
function wpbook_parse_request($wp) {
  if (array_key_exists('wpbook', $wp->query_vars)){
    if($wp->query_vars['wpbook'] == 'oauth') {  // first process code handler for OAuth
      // process the request - in our case this is a comment being posted
      nocache_headers();
	  
	 if(isset($_REQUEST["code"])) {  // this is the first step
		$wpbookLiteOptions = get_option('wpbookLiteAdminOptions');
		if (!empty($wpbookLiteOptions)) {
			foreach ($wpbookLiteOptions as $key => $option)
				$wpbookLiteAdminOptions[$key] = $option;
			}
		$wpbookLiteAdminOptions['fb_api_key'] = $wpbookLiteOptions['fb_api_key'];
		$wpbookLiteAdminOptions['fb_secret'] = $wpbookLiteOptions['fb_secret'];
	  	  
		$token_url = 'https://graph.facebook.com/oauth/access_token?client_id='
		. htmlentities($wpbookLiteAdminOptions['fb_api_key']) . '&redirect_uri='
		. home_url() .'/%3Fwpbook=oauth&client_secret=' . htmlentities($wpbookLiteAdminOptions['fb_secret']) 
		. '&code=' . $_REQUEST["code"];
		// using wp_remote_request should support multiple capabilities
		$response = wp_remote_request($token_url,array('sslverify'=>false));
		if( is_wp_error($response)) {
			echo "WP Error occured in trying to get token:\n";
			echo "Token url was " . $token_url . "\n";
			echo "WP Error is " . $response->get_error_message(); 
			die(); 
		}
		if(strpos($response['body'],'access_token=') !== false) {
			if(strpos($response['body'],'&expires') !== false) {
				$my_at = substr($response['body'],strpos($response['body'],'access_token=')+13,strpos($response['body'],'&expires')-13);
			} else {
				$my_at = substr($response['body'],strpos($response['body'],'access_token=')+13);
			}
			update_option('wpbook_lite_user_access_token',$my_at);
			echo "Succeeded in saving Access Token\n";
			echo '<a href="'. get_bloginfo('home') .'">Return to your blog</a>\n';
		} else {
			echo "Failed in creating access token\n"; 
			echo "Response was: \n";
			if (is_array($response)) {
				echo print_r($response,true);
			} else {
				echo $response; 
			}
			echo '<a href="'. get_bloginfo('home') .'">Return to your blog</a>';
		}
		
		/* now let's exchange the short term one for a 2 month one */ 
		$token_url = 'https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token&client_id='
		. htmlentities($wpbookLiteAdminOptions['fb_api_key']) . '&redirect_uri='
		. home_url() .'/%3Fwpbook=oauth&client_secret=' . htmlentities($wpbookLiteAdminOptions['fb_secret']) 
		. '&fb_exchange_token=' . $my_at;
		// using wp_remote_request should support multiple capabilities
		$response = wp_remote_request($token_url,array('sslverify'=>false));
		if( is_wp_error($response)) {
			echo "WP Error occured in trying to exchange token:\n";
			echo "Token url was " . $token_url . "\n";
			echo "WP Error is " . $response->get_error_message(); 
			die(); 
		}
		if(strpos($response['body'],'access_token=') !== false) {
			if(strpos($response['body'],'&expires') !== false) {
				$my_at = substr($response['body'],strpos($response['body'],'access_token=')+13,strpos($response['body'],'&expires')-13);
			} else {
				$my_at = substr($response['body'],strpos($response['body'],'access_token=')+13);
			}
			update_option('wpbook_lite_user_access_token',$my_at);
			echo "Succeeded in saving new extended  Access Token\n";
			echo '<a href="'. get_bloginfo('home') .'">Return to your blog</a>';
		} else {
			echo "Failed in creating access token\n"; 
			echo "Response was: \n";
			if (is_array($response)) {
				echo print_r($response,true);
			} else {
				echo $response; 
			}
			echo '<a href="'. get_bloginfo('home') .'">Return to your blog</a>';
		}
		
		
		
		
		
		
		die(); 
	 }
    }
  }
}
 
function wpbook_query_vars($vars) {
    $vars[] = 'wpbook';
    return $vars;
}

// display admin notice if token is invalid
function wpbook_token_notice() {
?>
  <div class='error fade'>
    <p>Your Facebook Access Token for WPBook Lite has expired. Please
	   <a href="<?php echo admin_url('options-general.php?page=wpbook-lite.php'); ?>">visit the settings page for WPBook Lite</a> and
	   grant a new access token. Until you do so, cross-posting to Facebook and import of 
	   comments will fail.</p>
  </div>
<?php
}


// thanks http://wpengineer.com/35/wordpress-plugin-deinstall-data-automatically/ 
 /**
 * Check for uninstall hook
 */
if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, 'wpbooklite_deinstall');

/**
 * Delete options in database
 */
function wpbooklite_deinstall() {
	delete_option('wpbookLiteAdminOptions');
	delete_option('wpbook_lite_user_access_token');
	delete_option('wpbook_lite_page_access_token');
	$wpbook_lite_allposts = get_posts('numberposts=-1&post_type=post&post_status=any');
	foreach( $wpbook_lite_allposts as $wpbook_lite_postinfo) {
		delete_post_meta($wpbook_lite_postinfo->ID, 'wpbook_lite_fb_publish');
		delete_post_meta($wpbook_lite_postinfo->ID, 'wpbook_lite_message');
	}
	
	
}

//admin notice for expired token
$my_access_token = get_option('wpbook_lite_user_access_token','');
if ($my_access_token == 'invalid') {
	add_action('admin_notices', 'wpbook_token_notice');
}

//add gravatar/facebook avatar support outside facebook
add_filter('get_avatar','wpbook_lite_get_global_facebook_avatar', 1, 3 ); 

add_action('admin_menu', 'wpbook_lite_options_page');
add_action('admin_menu', 'wpbook_lite_add_meta_box');
add_action('wp', 'wpbook_parse_request');
add_filter('query_vars', 'wpbook_query_vars');

// these capture new posts, not edits of previous posts	
add_action('future_to_publish','wpbook_lite_publish_to_facebook');	
add_action('new_to_publish','wpbook_lite_publish_to_facebook');
add_action('draft_to_publish','wpbook_lite_publish_to_facebook');  
add_action('pending_to_publish','wpbook_lite_publish_to_facebook');
// for windows live writer and other XML-RPC clients
add_action('auto-draft_to_publish','wpbook_lite_publish_to_facebook');

  
// cron job task  
add_action('wpbook_lite_cron_job', 'wpbook_lite_import_comments');

register_activation_hook(__FILE__, 'wpbook_lite_activate');
register_deactivation_hook(__FILE__, 'wpbook_lite_deactivate');

?>
