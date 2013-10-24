<?php 
/*
	Plugin Name: User Submitted Co-Authored-Posts
	Plugin URI: https://github.com/alpha1/User-Submitted-Co-Authored-Posts/
	Original Plugin URI: http://perishablepress.com/user-submitted-posts/
	Description: Enables your visitors to submit posts and images from anywhere on your site.
	Tags: submit, public, news, share, upload, images, posts, users
	Author: Michael Fitzpatrick-Ruth (Original Plugin Jeff Starr)
	Author URI: http://monzilla.biz/
	Donate link: http://m0n.co/donate
	Requires at least: 3.3
	Requires Co-Authors-Plus
	Tested up to: 3.5
	Version: 20131024
	Stable tag: trunk
	License: GPL v2
*/
if (!function_exists('add_action')) die('&Delta;');

// NO EDITING REQUIRED - PLEASE SET PREFERENCES IN THE WP ADMIN!

$usp_version = '20130720';
$usp_plugin  = __('User Submitted Posts', 'usp');
$usp_options = get_option('usp_options');
$usp_path    = plugin_basename(__FILE__); // '/user-submitted-posts/user-submitted-posts.php';
$usp_logo    = plugins_url() . '/user-submitted-posts/images/usp-logo.png';
$usp_homeurl = 'http://perishablepress.com/user-submitted-posts/';

$usp_post_meta_IsSubmission = 'is_submission';
$usp_post_meta_SubmitterIp  = 'user_submit_ip';
$usp_post_meta_Submitter    = 'user_submit_name';
$usp_post_meta_SubmitterUrl = 'user_submit_url';
$usp_post_meta_Image        = 'user_submit_image';



$uscap_plugin_slug = "user-submitted-co-authored-posts";
$uscap_plugin_version = "20131024";
$uscap_allow_pulse = true;

include_once('updater.php');
if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
$config = array(
'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
'proper_folder_name' => $uscap_plugin_slug, // this is the name of the folder your plugin lives in
'api_url' => 'https://github.com/alpha1/User-Submitted-Co-Authored-Posts', // the github API url of your github repo
'raw_url' => 'https://github.com/alpha1/User-Submitted-Co-Authored-Postsmaster', // the github raw url of your github repo
'github_url' => 'https://github.com/alpha1/User-Submitted-Co-Authored-Posts', // the github url of your github repo
'zip_url' => 'https://github.com/alpha1/User-Submitted-Co-Authored-Posts/zipball/master', // the zip url of the github repo
'sslverify' => true, // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
'requires' => '3.0', // which version of WordPress does your plugin require?
'tested' => '3.6.1', // which version of WordPress is your plugin tested up to?
'readme' => 'README.MD' // which file to use as the readme for the version number
);
new WP_GitHub_Updater($config);
}

add_filter( 'plugins_api', 'uscap_github_filter_plugin_info', 20, 3 ); 
function uscap_github_filter_plugin_info($res, $action, $args) {
	global $uscap_plugin_slug;
	if($args->slug == $uscap_plugin_slug){
		if($action == 'plugin_information' ){
			//if in details iframe on update core page short-curcuit it
			if ( did_action( 'install_plugins_pre_plugin-information' )){
				$changelog = wp_remote_get("https://github.com/alpha1/User-Submitted-Co-Authored-Posts/master/changelog.txt");
				if($changelog['response']['code'] == "200"){
					echo nl2br($changelog['body']);

				} else {
					echo $changelog['response']['code'];
				} 
				exit;
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}

register_activation_hook(__FILE__, 'uscap_pulse_beacon_activate');
register_deactivation_hook(__FILE__, 'uscap_pulse_beacon_deactivate');
function uscap_pulse_beacon_activate(){
uscapgenerator_pulse_beacon("activate");
}
function uscap_pulse_beacon_deactivate(){
uscap_generator_pulse_beacon("deactivate");
}
function uscap_pulse_beacon($action){
	global $uscap_plugin_version;
	global $uscap_plugin_slug;
	global $uscap_allow_pulse;
	global $wp_version;
	$domain = "pulse.alpha1beta.org";
	if($uscap_allow_pulse){ //if $uscap_allow_pulse is false, this will not to sent.
		$url = 'http://'. $domain .'/pulse-beacon/?action='. $action .'&plugin='.$uscap_plugin_slug .'&url='. site_url() .'&wp_version='. $wp_version .'&uscap_plugin_version='. $uscap_plugin_version;
		$response = wp_remote_get($url);
	}
}
//==========================================================================
//Above this line is maintenance and updating functions. These do not affect the plugin's functionality.
//==========================================================================

// include template functions
include ('library/template-tags.php');

// require minimum version of WordPress
add_action('admin_init', 'usp_require_wp_version');
function usp_require_wp_version() {
	global $wp_version, $usp_path, $usp_plugin;
	if (version_compare($wp_version, '3.3', '<')) {
		if (is_plugin_active($usp_path)) {
			deactivate_plugins($usp_path);
			$msg =  '<strong>' . $usp_plugin . '</strong> ' . __('requires WordPress 3.3 or higher, and has been deactivated!', 'usp') . '<br />';
			$msg .= __('Please return to the ', 'usp') . '<a href="' . admin_url() . '">' . __('WordPress Admin area', 'usp') . '</a> ' . __('to upgrade WordPress and try again.', 'usp');
			wp_die($msg);
		}
	}
}

// add new post status
add_filter ('post_stati', 'usp_addNewPostStatus');
function usp_addNewPostStatus($postStati) {
	$postStati['submitted'] = array(__('Submitted', 'usp'), __('User Submitted Posts', 'usp'), _n_noop('Submitted', 'Submitted'));
	return $postStati;
}

// add submitted status clause
add_action ('parse_query', 'usp_addSubmittedStatusClause');
function usp_addSubmittedStatusClause($wp_query) {
	global $pagenow, $usp_post_meta_IsSubmission;
	if (isset($_GET['user_submitted']) && $_GET['user_submitted'] == '1') {
		if (is_admin() && $pagenow == 'edit.php') {
			set_query_var('meta_key', $usp_post_meta_IsSubmission);
			set_query_var('meta_value', 1);
			set_query_var('post_status', 'pending');
		}
	}
}

// check for submitted post
add_action ('parse_request', 'usp_checkForPublicSubmission');
function usp_checkForPublicSubmission() {
	global $usp_options;
	if (isset($_POST['user-submitted-post']) && !empty($_POST['user-submitted-post'])) {

		if ($usp_options['usp_title'] == 'show') {
			$title = stripslashes($_POST['user-submitted-title']);
		} else {
			$title = 'User Submitted Post';
		}
		if (stripslashes($_POST['user-submitted-name']) && !empty($_POST['user-submitted-name'])) {
			$author_submit = stripslashes($_POST['user-submitted-name']);
			$author_info = get_user_by('login', $author_submit);

			if ($author_info) {
				$authorID = $author_info->id;
				$authorName = $author_submit;
				
			} else {
				$authorID = $usp_options['author'];
				$authorName = $author_submit;
			}
		} else {
			$authorID = $usp_options['author'];
			$authorName = get_the_author_meta('display_name', $authorID);
		}
		$authorUrl = stripslashes($_POST['user-submitted-url']);
		//added
		$authorEmail = stripslashes($_POST['user-submitted-email']);
		$authorBio = stripslashes($_POST['user-submitted-bio']);
		//end added
		$tags      = stripslashes($_POST['user-submitted-tags']);
		$captcha   = stripslashes($_POST['user-submitted-captcha']);
		$category  = intval($_POST['user-submitted-category']);
		$content   = stripslashes($_POST['user-submitted-content']);

		if (isset($_FILES['user-submitted-image'])) {
			$fileData = $_FILES['user-submitted-image'];
		} else {
			$fileData = '';
		}
		
		if(isset($_FILES['user-submitted-avatar'])) {
			$avatar_data = $_FILES['user-submitted-avatar'];
		} else {
			$avatar_data = '';
		}

		$publicSubmission = usp_createPublicSubmission($title, $authorEmail,$authorBio, $content, $authorName, $authorID, $authorUrl, $tags, $category, $fileData,$avatar_data);
		//$publicSubmission = usp_createPublicSubmission($title, $content, $authorName, $authorID, $authorUrl, $tags, $category, $fileData);

		if (false == ($publicSubmission)) {
			$errorMessage = empty($usp_options['error-message']) ? __('An error occurred. Please go back and try again.', 'usp') : $usp_options['error-message'];
			if(!empty($_POST['redirect-override'])) {
				$redirect = stripslashes($_POST['redirect-override']);
				$redirect = remove_query_arg('`', $redirect);
				$redirect = add_query_arg(array('submission-error'=>'1'), $redirect);
				wp_redirect($redirect);
				exit();
			} else {
				$redirect = stripslashes($_SERVER["REQUEST_URI"]);
				$redirect = remove_query_arg('success', $redirect);
				$redirect = add_query_arg(array('submission-error'=>'1'), $redirect);
				wp_redirect($redirect);
				exit();
			}
			// wp_die($errorMessage);
		} else {
			$redirect = empty($usp_options['redirect-url']) ? $_SERVER['REQUEST_URI'] : $usp_options['redirect-url'];
			if (!empty($_POST['redirect-override'])) {
				$redirect = stripslashes($_POST['redirect-override']);
			}
			$redirect = remove_query_arg('submission-error', $redirect);
			$redirect = add_query_arg(array('success'=>1), $redirect);
			wp_redirect($redirect);
			exit();
		}
	}
}

// set attachment as featured image
if (!current_theme_supports('post-thumbnails')) {
	add_theme_support('post-thumbnails');
	// set_post_thumbnail_size(130, 100, true); // width, height, hard crop
}
function usp_display_featured_image() {
	global $post, $usp_options;
	if (usp_is_public_submission($post->ID)) {
		if ((!has_post_thumbnail()) && ($usp_options['usp_featured_images'] == 1)) {
			$attachments = get_posts(array(
				'post_type' => 'attachment', 
				'post_mime_type'=>'image', 
				'posts_per_page' => 0, 
				'post_parent' => $post->ID, 
				'order'=>'ASC'
			));
			if ($attachments) {
				foreach ($attachments as $attachment) {
					set_post_thumbnail($post->ID, $attachment->ID);
					break;
				}
			}
		}
	}
}
add_action('wp', 'usp_display_featured_image');

// enqueue script and style
add_action ('init', 'usp_enqueueResources');
function usp_enqueueResources() {
	global $usp_options, $usp_version;
	$display_url = $usp_options['usp_display_url'];
	$current_url = trailingslashit('http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
	$current_url = remove_query_arg('submission-error', $current_url);
	$current_url = remove_query_arg('success', $current_url);
	if (!is_admin()) {
		// style
		if ($display_url !== '') {
			if ($display_url == $current_url) {
				if ($usp_options['usp_form_version'] == 'classic') {
					wp_enqueue_style ('usp_style', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) . '/resources/usp-classic.css', false, $usp_version, 'all');
				} elseif ($usp_options['usp_form_version'] == 'current') { 
					wp_enqueue_style ('usp_style', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) . '/resources/usp.css', false, $usp_version, 'all');
				} elseif ($usp_options['usp_form_version'] == 'disable') {}
			}
		} else {
			if ($usp_options['usp_form_version'] == 'classic') {
				wp_enqueue_style ('usp_style', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) . '/resources/usp-classic.css', false, $usp_version, 'all');
			} elseif ($usp_options['usp_form_version'] == 'current') { 
				wp_enqueue_style ('usp_style', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) . '/resources/usp.css', false, $usp_version, 'all');
			} elseif ($usp_options['usp_form_version'] == 'disable') {}
		}
		// script
		if ($display_url !== '') {
			if (($display_url == $current_url) && ($usp_options['usp_include_js'] == true)) {
				wp_enqueue_script ('usp_script', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) . '/resources/usp.php', array('jquery'), $usp_version);
			}
		} else {
			if ($usp_options['usp_include_js'] == true) {
				wp_enqueue_script ('usp_script', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) . '/resources/usp.php', array('jquery'), $usp_version);
			}
		}
	}
}

// add styles to admin Edit page
add_action('admin_print_styles', 'load_custom_admin_css');
function load_custom_admin_css() {
	global $usp_version, $pagenow;
	if (is_admin() && $pagenow == 'edit.php') {
		wp_enqueue_style('usp_style_admin', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) . '/resources/usp-admin.css', false, $usp_version, 'all');
	}
}

// add styles for WP rich text editor
function usp_editor_style($mce_css){
    $mce_css .= ', ' . plugins_url('resources/editor-style.css', __FILE__);
    return $mce_css;
}
add_filter('mce_css', 'usp_editor_style');

// shortcode
add_shortcode ('user-submitted-posts', 'usp_display_form');
function usp_display_form($atts=array(), $content=null) {
	global $usp_options;
	if ($atts === true) {
		$redirect = usp_currentPageURL();
	}
	if ($usp_options['usp_form_version'] == 'classic') {
		ob_start();
		include (WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/views/submission-form-classic.php');
		return ob_get_clean();
	} else {
		ob_start();
		include (WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/views/submission-form.php');
		return ob_get_clean();
	}
}

// template tag
function user_submitted_posts() {
	echo usp_display_form();
}

// add usp link
add_action ('restrict_manage_posts', 'usp_outputUserSubmissionLink');
function usp_outputUserSubmissionLink() {
	global $pagenow;
	if ($pagenow == 'edit.php') {
		echo '<a id="usp_admin_filter_posts" class="button" href="' . admin_url('edit.php?post_status=pending&amp;user_submitted=1') . '">' . __('User Submitted Posts', 'usp') . '</a>';
	}
}

// replace author
add_filter ('the_author', 'usp_replaceAuthor');
function usp_replaceAuthor($author) {
	global $post, $usp_options, $usp_post_meta_IsSubmission, $usp_post_meta_Submitter;

	$isSubmission     = get_post_meta($post->ID, $usp_post_meta_IsSubmission, true);
	$submissionAuthor = get_post_meta($post->ID, $usp_post_meta_Submitter, true);

	if ($isSubmission && !empty($submissionAuthor)) {
		return $submissionAuthor;
	} else {
		return $author;
	}
}

// create the form
function usp_createPublicSubmission($title,$authorEmail,$authorBio, $content, $authorName, $authorID, $authorUrl, $tags, $category, $fileData,$avatar_data) {
//function usp_createPublicSubmission($title, $content, $authorName, $authorID, $authorUrl, $tags, $category, $fileData) {
/* if(function_exists('get_coauthors')){
add_action( 'admin_init', array( $this, 'handle_create_guest_author_action' ) );
} */

	global $usp_options, $usp_post_meta_IsSubmission, $usp_post_meta_SubmitterIp, $usp_post_meta_Submitter, $usp_post_meta_SubmitterUrl, $usp_post_meta_Image;
	$authorName = strip_tags($authorName);
	$authorUrl  = strip_tags($authorUrl);
	if (isset($_SERVER['REMOTE_ADDR']))          $authorIp = stripslashes(trim($_SERVER['REMOTE_ADDR']));
	if (isset($_POST['user-submitted-captcha'])) $captcha  = stripslashes(trim($_POST['user-submitted-captcha']));
	if (isset($_POST['user-submitted-verify']))  $verify   = stripslashes(trim($_POST['user-submitted-verify']));

	if (!usp_validateTitle($title)) {
		return false;
	}
	if (!usp_validateTags($tags)) {
		return false;
	}
	if (!empty($verify)) {
		return false;
	}
	if ($usp_options['usp_captcha'] == 'show') {
		if (!usp_spam_question($captcha)) {
			return false;
		}
	}
	$postData = array();
	$postData['post_title']   = $title;
	$postData['post_content'] = $content;
	$postData['post_status']  = 'pending';
	$postData['post_author']  = $authorID;
	$numberApproved           = $usp_options['number-approved'];

	if ($numberApproved < 0) {} elseif ($numberApproved == 0) {
		$postData['post_status'] = 'publish';
	} else {
		$posts = get_posts(array('post_status'=>'publish', 'meta_key'=>$usp_post_meta_Submitter, 'meta_value'=>$authorName));
		$counter = 0;
		foreach ($posts as $post) {
			$submitterUrl = get_post_meta($post->ID, $usp_post_meta_SubmitterUrl, true);
			$submitterIp  = get_post_meta($post->ID, $usp_post_meta_SubmitterIp, true);
			if ($submitterUrl == $authorUrl && $submitterIp == $authorIp) {
				$counter++;
			}
		}
		if ($counter >= $numberApproved) {
			$postData['post_status'] = 'publish';
		}
	}
	
	if(class_exists('CoAuthors_Guest_Authors')){
			global $coauthors_plus;
			
		$new_coauthor = new CoAuthors_Guest_Authors;
		$fields = $new_coauthor->get_guest_author_fields();
		// Create the primary post object
		$postname =  'cap-'. strtolower(str_replace(' ', '-', $authorName));
		$new_post = array(
				'post_title'      => $authorName,
				'post_name'       =>$postname,
				'post_type'       => $new_coauthor->post_type
		);
	$NewCoAuthor = wp_insert_post( $new_post, true );
		if ( is_wp_error( $NewCoAuthor ) )
			return $NewCoAuthor;
	//co-author post id: $NewCoAuthor
	
	add_post_meta($NewCoAuthor, 'cap-display_name', $authorName, false);
	add_post_meta($NewCoAuthor, 'cap-first_name', '', false);
	add_post_meta($NewCoAuthor, 'cap-last_name','', false);
	add_post_meta($NewCoAuthor, 'cap-user_login', strtolower(str_replace(' ', '-', $authorName)), false);
	add_post_meta($NewCoAuthor, 'cap-user_email',$authorEmail, true); //TODO: Check for true or false, if false, this user already exists
	add_post_meta($NewCoAuthor, 'cap-website', $authorUrl, false);
	add_post_meta($NewCoAuthor, 'cap-aim', "", false); //fills nothing
	add_post_meta($NewCoAuthor, 'cap-yahooim', "", false);  //fills nothing
	add_post_meta($NewCoAuthor, 'cap-jabber', "", false); //fills nothing
	add_post_meta($NewCoAuthor, 'cap-description', $authorBio, false); 
	//Add Featured Image/headshot
	
	
	//$author_term = $coauthors_plus->update_author_term( $new_coauthor->get_guest_author_by( 'ID', $NewCoAuthor ) );
	$slug = 'cap-'. strtolower(str_replace(' ', '-', $authorName));
	$description = "$authorName $slug $authorEmail";
	$args = array(
				'slug' => $slug,
				'description' => $description
	);
	$debug_on = false;
	$debug = "";
	$term_id = wp_insert_term(strtolower(str_replace(' ', '-', $authorName)), 'author', $args );
	
	if($term_id){
	} else {
		$debug .= "Creating Term Failed";
	}
	
	$co_auth_terms =  wp_set_post_terms($NewCoAuthor, array($slug), 'author', false );
	if($co_auth_terms){
	} else {
		$debug  .= "Adding Term Failed";
	}
	
	if(wp_set_object_terms($co_auth_terms, $co_auth_terms, 'author',true)){
	} else {
		$debug .= "Adding Object terms failed";
	}
	
	
	}
	
	$newPost = wp_insert_post($postData);
	if(wp_set_post_terms( $newPost, array($slug), 'author', false )){
	} else {
		$debug  .= "Adding to the post failed";
	}
	
	if(empty($debug)){
		$coAuthorsWorked = true;
	} else {
		$coAuthorsWorked = false;
	}
	
	if ($newPost) {
		wp_set_post_tags($newPost, $tags);
		wp_set_post_categories($newPost, array($category));

		if ($usp_options['usp_email_alerts'] == true) {
			$to = $usp_options['usp_email_address'];
			if ($to !== '') {
				$subject = '['. get_bloginfo("name") .'] New user-submitted Post ';
				$message = "Hey, there is a new user-submitted post waiting for you. <br>\n Edit Post: ". site_url() ."/wp-admin/post.php?post=$newPost&action=edit<br>\n Edit Guest-Author:". site_url() ."/wp-admin/post.php?post=$NewCoAuthor&action=edit";
				
				if($debug_on){
				$message .= "<Br>\n Debug:". $debug;
				}
				wp_mail($to, $subject, $message);
			}
		}
		if (!function_exists('media_handle_upload')) {
			require_once (ABSPATH . '/wp-admin/includes/media.php');
			require_once (ABSPATH . '/wp-admin/includes/file.php');
			require_once (ABSPATH . '/wp-admin/includes/image.php');
		}
		
		
		$attachmentIds = array();
		$imageCounter = 0;

		
		if ($fileData !== '') {
			for ($i = 0; $i < count($fileData['name']); $i++) {
				$imageInfo = @getimagesize($fileData['tmp_name'][$i]);
				if (false === $imageInfo || !usp_imageIsRightSize($imageInfo[0], $imageInfo[1])) {
					continue;
				}
				$key = "public-submission-attachment-{$i}";
	
				$_FILES[$key] = array();
				$_FILES[$key]['name']     = $fileData['name'][$i];
				$_FILES[$key]['tmp_name'] = $fileData['tmp_name'][$i];
				$_FILES[$key]['type']     = $fileData['type'][$i];
				$_FILES[$key]['error']    = $fileData['error'][$i];
				$_FILES[$key]['size']     = $fileData['size'][$i];
	
				$attachmentId = media_handle_upload($key, $newPost);
		
				if (!is_wp_error($attachmentId) && wp_attachment_is_image($attachmentId)) {
					$attachmentIds[] = $attachmentId;
					//add_post_meta($newPost, $usp_post_meta_Image, wp_get_attachment_url($attachmentId));
					if($imageCounter == 0){
						set_post_thumbnail($newPost, $attachmentId);
					}
					$imageCounter++;

				} else {
					wp_delete_attachment($attachmentId);
				}
				if ($imageCounter == $usp_options['max-images']) {
					break;
				}
			}
		}
		
		//added
		
			$imageCounter = 0;
			if ($avatar_data !== '') {
			for ($i = 0; $i < count($avatar_data['name']); $i++) {
				$imageInfo = @getimagesize($avatar_data['tmp_name'][$i]);
				if (false === $imageInfo || !usp_imageIsRightSize($imageInfo[0], $imageInfo[1])) {
					continue;
				}
				$key = "public-submission-attachment-{$i}";
	
				$_FILES[$key] = array();
				$_FILES[$key]['name']     = $avatar_data['name'][$i];
				$_FILES[$key]['tmp_name'] = $avatar_data['tmp_name'][$i];
				$_FILES[$key]['type']     = $avatar_data['type'][$i];
				$_FILES[$key]['error']    = $avatar_data['error'][$i];
				$_FILES[$key]['size']     = $avatar_data['size'][$i];
	
				$attachmentId = media_handle_upload($key, $NewCoAuthor);
		
				if (!is_wp_error($attachmentId) && wp_attachment_is_image($attachmentId)) {
					$attachmentIds[] = $attachmentId;
					//add_post_meta($newPost, $usp_post_meta_Image, wp_get_attachment_url($attachmentId));
					if($imageCounter == 0){
						set_post_thumbnail($NewCoAuthor, $attachmentId);
					}
					$imageCounter++;
				} else {
					wp_delete_attachment($attachmentId);
				}
			}
		}
		//end added
		
		if (count($attachmentIds) < $usp_options['min-images']) {
			foreach ($attachmentIds as $idToDelete) {
				wp_delete_attachment($idToDelete);
			}
			wp_delete_post($newPost);
			return false;
		}
		
		
		if(!$coAuthorsWorked){
			//if the co-authors failed or doesn't exist, drop this info the custom fields. If it worked, we don't need this junk in the database
			update_post_meta($newPost, $usp_post_meta_IsSubmission, true);
			update_post_meta($newPost, $usp_post_meta_Submitter, htmlentities($authorName, ENT_QUOTES, 'UTF-8'));
			update_post_meta($newPost, $usp_post_meta_SubmitterUrl, htmlentities($authorUrl, ENT_QUOTES, 'UTF-8'));
			update_post_meta($newPost, $usp_post_meta_SubmitterEmail, htmlentities($authorEmail, ENT_QUOTES, 'UTF-8'));
			update_post_meta($newPost, $usp_post_meta_SubmitterBio, htmlentities($authorBio, ENT_QUOTES, 'UTF-8'));
			update_post_meta($newPost, $usp_post_meta_SubmitterIp, htmlentities($authorIp, ENT_QUOTES, 'UTF-8'));
		}
	}
	return $newPost;
}

// validate stuff
function usp_imageIsRightSize($width, $height) {
	global $usp_options;
	$widthFits = ($width <= intval($usp_options['max-image-width'])) && ($width >= $usp_options['min-image-width']);
	$heightFits = ($height <= $usp_options['max-image-height']) && ($height >= $usp_options['min-image-height']);
	return $widthFits && $heightFits;
}
function usp_validateTags($tags) {
	return true;
}
function usp_validateTitle($title) {
	return !empty($title);
}

// challenge question
function usp_spam_question($input) {
	global $usp_options;
	$response = $usp_options['usp_response'];
	$response = stripslashes(trim($response));
	if ($usp_options['usp_casing'] == true) {
		return (strtoupper($input) == strtoupper($response));
	} else {
		return ($input == $response);
	}
}

// current url
function usp_currentPageURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

// display settings link on plugin page
add_filter ('plugin_action_links', 'usp_plugin_action_links', 10, 2);
function usp_plugin_action_links($links, $file) {
	global $usp_path;
	if ($file == $usp_path) {
		$usp_links = '<a href="' . get_admin_url() . 'options-general.php?page=' . $usp_path . '">' . __('Settings', 'usp') .'</a>';
		array_unshift($links, $usp_links);
	}
	return $links;
}

// delete plugin settings
function usp_delete_plugin_options() {
	delete_option('usp_options');
}
if ($usp_options['default_options'] == 1) {
	register_uninstall_hook (__FILE__, 'usp_delete_plugin_options');
}

// define default settings
register_activation_hook (__FILE__, 'usp_add_defaults');
function usp_add_defaults() {
	$currentUser = wp_get_current_user();
	$admin_mail = get_bloginfo('admin_email');
	$tmp = get_option('usp_options');
	if(($tmp['default_options'] == '1') || (!is_array($tmp))) {
		$arr = array(
			'default_options' => 0,
			'author' => $currentUser->ID,
			'categories' => array(get_option('default_category')),
			'number-approved' => -1,
			'redirect-url' => '',
			'error-message' => __('There was an error. Please ensure that you have added a title, some content, and that you have uploaded only images.', 'usp'),
			'min-images' => 0,
			'max-images' => 1,
			'min-image-height' => 0,
			'min-image-width' => 0,
			'max-image-height' => 1500,
			'max-image-width' => 1500,
			'usp_name' => 'show',
			'usp_url' => 'show',
			'usp_title' => 'show',
			'usp_tags' => 'show',
			'usp_category' => 'show',
			'usp_images' => 'hide',
			'upload-message' => 'Please select your image(s) to upload.',
			'usp_form_width' => '300', // in pixels (not used anywhere)
			'usp_question' => '1 + 1 =',
			'usp_response' => '2',
			'usp_casing' => 0,
			'usp_captcha' => 'show',
			'usp_content' => 'show',
			'success-message' => 'Success! Thank you for your submission.',
			'usp_form_version' => 'current',
			'usp_email_alerts' => 1,
			'usp_email_address' => $admin_mail,
			'usp_use_author' => 0,
			'usp_use_url' => 0,
			'usp_use_cat' => 0,
			'usp_use_cat_id' => '',
			'usp_include_js' => 1,
			'usp_display_url' => '',
			'usp_form_content' => '',
			'usp_richtext_editor' => 0,
			'usp_featured_images' => 0,
		);
		update_option('usp_options', $arr);
	}	
}

// define style options
$usp_form_version = array(
	'classic' => array(
		'value' => 'classic',
		'label' => __('Classic form + styles', 'usp')
	),
	'current' => array(
		'value' => 'current',
		'label' => __('HTML5 form + styles', 'usp')
	),
	'disable' => array(
		'value' => 'disable',
		'label' => __('Disable stylesheet', 'usp')
	),
);

// whitelist settings
add_action ('admin_init', 'usp_init');
function usp_init() {
	register_setting('usp_plugin_options', 'usp_options', 'usp_validate_options');
}

// sanitize and validate input
function usp_validate_options($input) {
	global $usp_options, $usp_form_version;

	if (!isset($input['default_options'])) $input['default_options'] = null;
	$input['default_options'] = ($input['default_options'] == 1 ? 1 : 0);

	$input['categories']       = is_array($input['categories']) && !empty($input['categories']) ? array_unique($input['categories']) : array(get_option('default_category'));
	$input['number-approved']  = is_numeric($input['number-approved']) ? intval($input['number-approved']) : - 1;

	$input['min-images']       = is_numeric($input['min-images']) ? intval($input['min-images']) : $input['max-images'];
	$input['max-images']       = (is_numeric($input['max-images']) && ($usp_options['min-images'] <= abs($input['max-images']))) ? intval($input['max-images']) : $usp_options['max-images'];
	
	$input['min-image-height'] = is_numeric($input['min-image-height']) ? intval($input['min-image-height']) : $usp_options['min-image-height'];
	$input['min-image-width']  = is_numeric($input['min-image-width'])  ? intval($input['min-image-width'])  : $usp_options['min-image-width'];
	
	$input['max-image-height'] = (is_numeric($input['max-image-height']) && ($usp_options['min-image-height'] <= $input['max-image-height'])) ? intval($input['max-image-height']) : $usp_options['max-image-height'];
	$input['max-image-width']  = (is_numeric($input['max-image-width'])  && ($usp_options['min-image-width']  <= $input['max-image-width']))  ? intval($input['max-image-width'])  : $usp_options['max-image-width'];

	$input['author']            = wp_filter_nohtml_kses($input['author']);
	$input['usp_name']          = wp_filter_nohtml_kses($input['usp_name']);
	$input['usp_url']           = wp_filter_nohtml_kses($input['usp_url']);
	$input['usp_title']         = wp_filter_nohtml_kses($input['usp_title']);
	$input['usp_tags']          = wp_filter_nohtml_kses($input['usp_tags']);
	$input['usp_category']      = wp_filter_nohtml_kses($input['usp_category']);
	$input['usp_images']        = wp_filter_nohtml_kses($input['usp_images']);
	//$input['usp_form_width']    = wp_filter_nohtml_kses($input['usp_form_width']);
	$input['usp_question']      = wp_filter_nohtml_kses($input['usp_question']);
	//$input['usp_answer']        = wp_filter_nohtml_kses($input['usp_answer']);
	$input['usp_captcha']       = wp_filter_nohtml_kses($input['usp_captcha']);
	$input['usp_content']       = wp_filter_nohtml_kses($input['usp_content']);
	$input['usp_email_address'] = wp_filter_nohtml_kses($input['usp_email_address']);
	$input['usp_use_cat_id']    = wp_filter_nohtml_kses($input['usp_use_cat_id']);
	$input['usp_display_url']   = wp_filter_nohtml_kses($input['usp_display_url']);
	$input['redirect-url']      = wp_filter_nohtml_kses($input['redirect-url']);

	// dealing with kses
	global $allowedposttags;
	$allowed_atts = array('align'=>array(), 'class'=>array(), 'type'=>array(), 'id'=>array(), 'dir'=>array(), 'lang'=>array(), 'style'=>array(), 'xml:lang'=>array(), 'src'=>array(), 'alt'=>array());

	$allowedposttags['script'] = $allowed_atts;
	$allowedposttags['strong'] = $allowed_atts;
	$allowedposttags['small'] = $allowed_atts;
	$allowedposttags['span'] = $allowed_atts;
	$allowedposttags['abbr'] = $allowed_atts;
	$allowedposttags['code'] = $allowed_atts;
	$allowedposttags['div'] = $allowed_atts;
	$allowedposttags['img'] = $allowed_atts;
	$allowedposttags['h1'] = $allowed_atts;
	$allowedposttags['h2'] = $allowed_atts;
	$allowedposttags['h3'] = $allowed_atts;
	$allowedposttags['h4'] = $allowed_atts;
	$allowedposttags['h5'] = $allowed_atts;
	$allowedposttags['ol'] = $allowed_atts;
	$allowedposttags['ul'] = $allowed_atts;
	$allowedposttags['li'] = $allowed_atts;
	$allowedposttags['em'] = $allowed_atts;
	$allowedposttags['p'] = $allowed_atts;
	$allowedposttags['a'] = $allowed_atts;

	$input['usp_form_content'] = wp_kses_post($input['usp_form_content'], $allowedposttags);
	$input['error-message']    = wp_kses_post($input['error-message'], $allowedposttags);
	$input['upload-message']   = wp_kses_post($input['upload-message'], $allowedposttags);
	$input['success-message']  = wp_kses_post($input['success-message'], $allowedposttags);

	if (!isset($input['usp_casing'])) $input['usp_casing'] = null;
	$input['usp_casing'] = ($input['usp_casing'] == 1 ? 1 : 0);

	if (!isset($input['usp_form_version'])) $input['usp_form_version'] = null;
	if (!array_key_exists($input['usp_form_version'], $usp_form_version)) $input['usp_form_version'] = null;
	
	if (!isset($input['usp_email_alerts'])) $input['usp_email_alerts'] = null;
	$input['usp_email_alerts'] = ($input['usp_email_alerts'] == 1 ? 1 : 0);

	if (!isset($input['usp_use_author'])) $input['usp_use_author'] = null;
	$input['usp_use_author'] = ($input['usp_use_author'] == 1 ? 1 : 0);

	if (!isset($input['usp_use_url'])) $input['usp_use_url'] = null;
	$input['usp_use_url'] = ($input['usp_use_url'] == 1 ? 1 : 0);
	
	if (!isset($input['usp_use_cat'])) $input['usp_use_cat'] = null;
	$input['usp_use_cat'] = ($input['usp_use_cat'] == 1 ? 1 : 0);

	if (!isset($input['usp_include_js'])) $input['usp_include_js'] = null;
	$input['usp_include_js'] = ($input['usp_include_js'] == 1 ? 1 : 0);

	if (!isset($input['usp_richtext_editor'])) $input['usp_richtext_editor'] = null;
	$input['usp_richtext_editor'] = ($input['usp_richtext_editor'] == 1 ? 1 : 0);

	if (!isset($input['usp_featured_images'])) $input['usp_featured_images'] = null;
	$input['usp_featured_images'] = ($input['usp_featured_images'] == 1 ? 1 : 0);

	return $input;
}

// add the options page
add_action ('admin_menu', 'usp_add_options_page');
function usp_add_options_page() {
	global $usp_plugin;
	add_options_page($usp_plugin, $usp_plugin, 'manage_options', __FILE__, 'usp_render_form');
}

// create the options page
function usp_render_form() {
	global $usp_plugin, $usp_options, $usp_path, $usp_homeurl, $usp_version, $usp_logo, $usp_form_version; ?>

	<style type="text/css">
		.mm-panel-overview { padding-left: 200px; background: url(<?php echo $usp_logo; ?>) no-repeat 15px 0; }

		#mm-plugin-options h2 small { font-size: 60%; }
		#mm-plugin-options h3 { cursor: pointer; }
		#mm-plugin-options h4, 
		#mm-plugin-options p { margin: 15px; line-height: 18px; }
		#mm-plugin-options ul { margin: 15px 15px 25px 40px; }
		#mm-plugin-options li { margin: 10px 0; list-style-type: disc; }
		#mm-plugin-options abbr { cursor: help; border-bottom: 1px dotted #dfdfdf; }

		.mm-table-wrap { margin: 15px; }
		.mm-table-wrap td { padding: 5px 10px; vertical-align: middle; }
		.mm-table-wrap .mm-table {}
		.mm-table-wrap .widefat th { padding: 10px 15px; vertical-align: middle; }
		.mm-table-wrap .widefat td { padding: 10px; vertical-align: middle; }
		.mm-item-caption { margin: 3px 0 0 3px; font-size: 80%; color: #777; }
		.mm-radio-inputs { margin: 7px 0; }
		.mm-radio-inputs span { padding-left: 5px; }
		.mm-code { background-color: #fafae0; color: #333; font-size: 14px; }
		#mm-plugin-options .mm-plain-list li { list-style-type: none; }
		.mm-plain-list li span { padding-left: 5px; }

		#setting-error-settings_updated { margin: 10px 0; }
		#setting-error-settings_updated p { margin: 5px; }
		#mm-plugin-options .button-primary { margin: 0 0 15px 15px; }

		#mm-panel-toggle { margin: 5px 0; }
		#mm-credit-info { margin-top: -5px; }
		#mm-iframe-wrap { width: 100%; height: 250px; overflow: hidden; }
		#mm-iframe-wrap iframe { width: 100%; height: 100%; overflow: hidden; margin: 0; padding: 0; }
	</style>

	<div id="mm-plugin-options" class="wrap">
		<?php screen_icon(); ?>

		<h2><?php echo $usp_plugin; ?> <small><?php echo 'v' . $usp_version; ?></small></h2>
		<div id="mm-panel-toggle"><a href="<?php get_admin_url() . 'options-general.php?page=' . $usp_path; ?>"><?php _e('Toggle all panels', 'usp'); ?></a></div>

		<form method="post" action="options.php">
			<?php $usp_options = get_option('usp_options'); settings_fields('usp_plugin_options'); ?>

			<div class="metabox-holder">
				<div class="meta-box-sortables ui-sortable">
					<div id="mm-panel-overview" class="postbox">
						<h3><?php _e('Overview', 'usp'); ?></h3>
						<div class="toggle">
							<div class="mm-panel-overview">
								<p>
									<strong><?php echo $usp_plugin; ?></strong> <?php _e('(USP) enables your visitors to submit posts from anywhere on your site.', 'usp'); ?> 
									<?php _e('To implement, customize your options and then include the USP form via shortcode or template tag.', 'usp'); ?> 
									<?php _e('Use the shortcode to display the upload form on a post or page, or use the template tag to display the upload form anywhere in your theme template.', 'usp'); ?>
								</p>
								<ul>
									<li><?php _e('To configure your settings, visit the', 'usp'); ?> <a id="mm-panel-primary-link" href="#mm-panel-primary"><?php _e('Options panel', 'usp'); ?></a>.</li>
									<li><?php _e('For the shortcode and template tag, visit', 'usp'); ?> <a id="mm-panel-secondary-link" href="#mm-panel-secondary"><?php _e('Shortcode &amp; Template Tag', 'usp'); ?></a>.</li>
									<li><?php _e('For more information check the', 'usp'); ?> <a href="<?php echo plugins_url(); ?>/user-submitted-posts/readme.txt">readme.txt</a> 
										<?php _e('and', 'usp'); ?> <a href="<?php echo $usp_homeurl; ?>"><?php _e('USP Homepage', 'usp'); ?></a>.</li>
								</ul>
							</div>
						</div>
					</div>
					<div id="mm-panel-primary" class="postbox">
						<h3><?php _e('Options', 'usp'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<p><?php _e('Here you may configure options for USP. See the <code>readme.txt</code> for more information.', 'usp'); ?></p>
							<h4><?php _e('Show/hide the following form fields', 'usp'); ?></h4>
							<ul class="mm-plain-list">
								<li>
									<select name="usp_options[usp_name]">
										<option <?php if ($usp_options['usp_name'] == 'show') echo 'selected="selected"'; ?> value="show"><?php _e('Show', 'usp'); ?></option>
										<option <?php if ($usp_options['usp_name'] == 'hide') echo 'selected="selected"'; ?> value="hide"><?php _e('Hide', 'usp'); ?></option>
									</select> <span><?php _e('User Name', 'usp'); ?></span>
								</li>
								<!--Added-->
								<li>
									<select name="usp_options[usp_email]">
										<option <?php if ($usp_options['usp_email'] == 'show') echo 'selected="selected"'; ?> value="show"><?php _e('Show', 'usp'); ?></option>
										<option <?php if ($usp_options['usp_email'] == 'hide') echo 'selected="selected"'; ?> value="hide"><?php _e('Hide', 'usp'); ?></option>
									</select> <span><?php _e('Guest Author Email', 'usp'); ?></span>
								</li>
								<li>
									<select name="usp_options[usp_bio]">
										<option <?php if ($usp_options['usp_bio'] == 'show') echo 'selected="selected"'; ?> value="show"><?php _e('Show', 'usp'); ?></option>
										<option <?php if ($usp_options['usp_bio'] == 'hide') echo 'selected="selected"'; ?> value="hide"><?php _e('Hide', 'usp'); ?></option>
									</select> <span><?php _e('Guest Author Bio', 'usp'); ?></span>
								</li>
								<!--End Added-->
								<li>
									<select name="usp_options[usp_url]">
										<option <?php if ($usp_options['usp_url'] == 'show') echo 'selected="selected"'; ?> value="show"><?php _e('Show', 'usp'); ?></option>
										<option <?php if ($usp_options['usp_url'] == 'hide') echo 'selected="selected"'; ?> value="hide"><?php _e('Hide', 'usp'); ?></option>
									</select> <span><?php _e('Post URL', 'usp'); ?></span>
								</li>
								<li>
									<select name="usp_options[usp_title]">
										<option <?php if ($usp_options['usp_title'] == 'show') echo 'selected="selected"'; ?> value="show"><?php _e('Show', 'usp'); ?></option>
										<option <?php if ($usp_options['usp_title'] == 'hide') echo 'selected="selected"'; ?> value="hide"><?php _e('Hide', 'usp'); ?></option>
									</select> <span><?php _e('Post Title', 'usp'); ?></span>
								</li>
								<li>
									<select name="usp_options[usp_tags]">
										<option <?php if ($usp_options['usp_tags'] == 'show') echo 'selected="selected"'; ?> value="show"><?php _e('Show', 'usp'); ?></option>
										<option <?php if ($usp_options['usp_tags'] == 'hide') echo 'selected="selected"'; ?> value="hide"><?php _e('Hide', 'usp'); ?></option>
									</select> <span><?php _e('Post Tags', 'usp'); ?></span>
								</li>
								<li>
									<select name="usp_options[usp_category]">
										<option <?php if ($usp_options['usp_category'] == 'show') echo 'selected="selected"'; ?> value="show"><?php _e('Show', 'usp'); ?></option>
										<option <?php if ($usp_options['usp_category'] == 'hide') echo 'selected="selected"'; ?> value="hide"><?php _e('Hide', 'usp'); ?></option>
									</select> <span><?php _e('Post Category', 'usp'); ?></span>
								</li>
								<li>
									<select name="usp_options[usp_content]">
										<option <?php if ($usp_options['usp_content'] == 'show') echo 'selected="selected"'; ?> value="show"><?php _e('Show', 'usp'); ?></option>
										<option <?php if ($usp_options['usp_content'] == 'hide') echo 'selected="selected"'; ?> value="hide"><?php _e('Hide', 'usp'); ?></option>
									</select> <span><?php _e('Post Content', 'usp'); ?></span>
								</li>
								<li>
									<select name="usp_options[usp_images]">
										<option <?php if ($usp_options['usp_images'] == 'show') echo 'selected="selected"'; ?> value="show"><?php _e('Show', 'usp'); ?></option>
										<option <?php if ($usp_options['usp_images'] == 'hide') echo 'selected="selected"'; ?> value="hide"><?php _e('Hide', 'usp'); ?></option>
									</select> <span><?php _e('Post Images', 'usp'); ?></span>
								</li>
								<li>
									<select name="usp_options[usp_co_author_avatar]">
										<option <?php if ($usp_options['usp_co_author_avatar'] == 'show') echo 'selected="selected"'; ?> value="show"><?php _e('Show', 'usp'); ?></option>
										<option <?php if ($usp_options['usp_co_author_avatar'] == 'hide') echo 'selected="selected"'; ?> value="hide"><?php _e('Hide', 'usp'); ?></option>
									</select> <span><?php _e('Co-Authors-Plus Avatar', 'usp'); ?></span>
								</li>
								<li>
									<select name="usp_options[usp_captcha]">
										<option <?php if ($usp_options['usp_captcha'] == 'show') echo 'selected="selected"'; ?> value="show"><?php _e('Show', 'usp'); ?></option>
										<option <?php if ($usp_options['usp_captcha'] == 'hide') echo 'selected="selected"'; ?> value="hide"><?php _e('Hide', 'usp'); ?></option>
									</select> <span><?php _e('Challenge question (Captcha)', 'usp'); ?></span>
								</li>
							</ul>
							<h4><?php _e('Choose some general form options', 'usp'); ?></h4>
							<div class="mm-table-wrap">
								<table class="widefat mm-table">
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_form_version]"><?php _e('Form style', 'usp'); ?></label></th>
										<td>
											<?php if (!isset($checked)) $checked = '';
												foreach ($usp_form_version as $usp_form) {
													$radio_setting = $usp_options['usp_form_version'];
													if ('' != $radio_setting) {
														if ($usp_options['usp_form_version'] == $usp_form['value']) {
															$checked = "checked=\"checked\"";
														} else {
															$checked = '';
														}
													} ?>
													<div class="mm-radio-inputs">
														<input type="radio" name="usp_options[usp_form_version]" value="<?php esc_attr_e($usp_form['value']); ?>" <?php echo $checked; ?> /> 
														<?php echo $usp_form['label']; ?>
													</div>
											<?php } ?>
											<div class="mm-item-caption">
												<?php _e('HTML5 is recommended. If upgrading and the new form looks weird, choose the Classic version.', 'usp'); ?> 
												<?php _e('To disable the plugin&rsquo;s stylesheet, choose Disable. Note: complete list of CSS hooks for the submission form at', 'usp'); ?> 
												<a href="http://m0n.co/e" title="<?php _e('CSS Hooks for User Submitted Posts', 'usp'); ?>" target="_blank">http://m0n.co/e</a>
											</div>
										</td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_include_js]"><?php _e('Include JavaScript?', 'usp'); ?></label></th>
										<td><input type="checkbox" value="1" name="usp_options[usp_include_js]" <?php if (isset($usp_options['usp_include_js'])) { checked('1', $usp_options['usp_include_js']); } ?> />
										<span class="mm-item-caption"><?php _e('Check this box if you want to include the external JavaScript file. Note: if you&rsquo;re not allowing image uploads, leave this option unchecked.', 'usp'); ?></span></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_display_url]"><?php _e('Targeted Loading', 'usp'); ?></label></th>
										<td><input type="text" size="45" maxlength="200" name="usp_options[usp_display_url]" value="<?php echo esc_attr($usp_options['usp_display_url']); ?>" />
										<div class="mm-item-caption"><?php _e('When enabled, external CSS &amp; JavaScript files are loaded on every page. Here you may specify the URL of the USP form to load resources only on that page. Note: leave blank to load on all pages.', 'usp'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description"><?php _e('Categories', 'usp'); ?></label></th>
										<td>
											<?php $categories = get_categories(array('hide_empty'=> 0)); ?>
											<?php foreach($categories as $category) { ?>
											
											<div class="mm-radio-inputs">
												<label class="description">
													<input <?php checked(true, in_array($category->term_id, $usp_options['categories'])); ?> type="checkbox" name="usp_options[categories][]" value="<?php echo $category->term_id; ?>" /> 
													<span><?php echo htmlentities($category->name, ENT_QUOTES, 'UTF-8'); ?></span>
												</label>
											</div>
											
											<?php } ?>
											<div class="mm-item-caption"><?php _e('Select which categories may be assigned to submitted posts.', 'usp'); ?></div>
										</td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[author]"><?php _e('Assigned Author', 'usp'); ?></label></th>
										<td>
											<select id="usp_options[author]" name="usp_options[author]">
											<?php global $wpdb; $allAuthors = $wpdb->get_results("SELECT ID, display_name FROM {$wpdb->users}");
												foreach($allAuthors as $author) { ?>
													<option <?php selected($usp_options['author'], $author->ID); ?> value="<?php echo $author->ID; ?>">
														<?php echo $author->display_name; ?>
													</option>
												<?php } ?>
											</select>
											<div class="mm-item-caption"><?php _e('Specify the user that should be assigned as author for user-submitted posts.', 'usp'); ?></div>
										</td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[number-approved]"><?php _e('Auto Publish?', 'usp'); ?></label></th>
										<td>
											<select name="usp_options[number-approved]">
												<option <?php selected(-1, $usp_options['number-approved']); ?> value="-1"><?php _e('Always moderate', 'usp'); ?></option>
												<option <?php selected( 0, $usp_options['number-approved']); ?> value="0"><?php _e('Always publish immediately', 'usp'); ?></option>
												<?php foreach(range(1, 20) as $value) { ?>
												<option <?php selected($value, $usp_options['number-approved']); ?> value="<?php echo $value; ?>"><?php echo $value; ?></option>
												<?php } ?>
											</select>
											<div class="mm-item-caption"><?php _e('For submitted posts, you can always moderate (recommended), publish immediately, or publish after any number of approved posts.', 'usp'); ?></div>
										</td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_email_alerts]"><?php _e('Receive Email Alert', 'usp'); ?></label></th>
										<td><input type="checkbox" value="1" name="usp_options[usp_email_alerts]" <?php if (isset($usp_options['usp_email_alerts'])) { checked('1', $usp_options['usp_email_alerts']); } ?> />
										<span class="mm-item-caption"><?php _e('Check this box if you want to be notified via email for new post submissions.', 'usp'); ?></span></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_richtext_editor]"><?php _e('Enable Rich Text Editor', 'usp'); ?></label></th>
										<td><input type="checkbox" value="1" name="usp_options[usp_richtext_editor]" <?php if (isset($usp_options['usp_richtext_editor'])) { checked('1', $usp_options['usp_richtext_editor']); } ?> />
										<span class="mm-item-caption"><?php _e('Check this box if you want to enable WP rich text editing for submitted posts.', 'usp'); ?></span></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_featured_images]"><?php _e('Set Uploaded Image as Featured Image', 'usp'); ?></label></th>
										<td><input type="checkbox" value="1" name="usp_options[usp_featured_images]" <?php if (isset($usp_options['usp_featured_images'])) { checked('1', $usp_options['usp_featured_images']); } ?> />
										<span class="mm-item-caption"><?php _e('Check this box if you want to set submitted images as Featured Images (aka Post Thumbnails) for posts. 
											Note: your theme&rsquo;s single.php file must include the_post_thumbnail() to display Featured Images.', 'usp'); ?></span></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_email_address]"><?php _e('Email Address for Alerts', 'usp'); ?></label></th>
										<td><input type="text" size="45" maxlength="200" name="usp_options[usp_email_address]" value="<?php echo esc_attr($usp_options['usp_email_address']); ?>" />
										<div class="mm-item-caption"><?php _e('If you checked the box to receive email alerts, indicate here the address to which the emails should be sent. Multi Addresses can be seperated by commas.', 'usp'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[redirect-url]"><?php _e('Redirect URL', 'usp'); ?></label></th>
										<td><input type="text" size="45" maxlength="200" name="usp_options[redirect-url]" value="<?php echo esc_attr($usp_options['redirect-url']); ?>" />
										<div class="mm-item-caption"><?php _e('Specify a URL to redirect the user after post-submission. Note: leave blank to redirect back to current page.', 'usp'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[success-message]"><?php _e('Success Message', 'usp'); ?></label></th>
										<td><textarea class="textarea" rows="3" cols="50" name="usp_options[success-message]"><?php echo esc_attr($usp_options['success-message']); ?></textarea> 
										<div class="mm-item-caption"><?php _e('This is the success message that is displayed if post-submission is successful.', 'usp'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[error-message]"><?php _e('Error Message', 'usp'); ?></label></th>
										<td><textarea class="textarea" rows="3" cols="50" name="usp_options[error-message]"><?php echo esc_attr($usp_options['error-message']); ?></textarea> 
										<div class="mm-item-caption"><?php _e('This is the error message that is displayed if post-submission fails.', 'usp'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_form_content]"><?php _e('Custom Content', 'usp'); ?></label></th>
										<td><textarea class="textarea" rows="3" cols="50" name="usp_options[usp_form_content]"><?php echo esc_attr($usp_options['usp_form_content']); ?></textarea> 
										<div class="mm-item-caption"><?php _e('Here you may specify custom text/markup to be included before the submission form. Note: leave blank to disable.', 'usp'); ?></div></td>
									</tr>
								</table>
							</div>
							<h4><?php _e('Use registered user info', 'usp'); ?></h4>
							<div class="mm-table-wrap">
								<table class="widefat mm-table">
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_use_author]"><?php _e('Use registered username for author?', 'usp'); ?></label></th>
										<td><input type="checkbox" value="1" name="usp_options[usp_use_author]" <?php if (isset($usp_options['usp_use_author'])) { checked('1', $usp_options['usp_use_author']); } ?> />
										<span class="mm-item-caption"><?php _e('Check this box if you want to automatically use the registered username as the submitted-post author. Note: this really should only be used when requiring log-in for submissions.', 'usp'); ?></span></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_use_url]"><?php _e('Use registered URL for submitted URL?', 'usp'); ?></label></th>
										<td><input type="checkbox" value="1" name="usp_options[usp_use_url]" <?php if (isset($usp_options['usp_use_url'])) { checked('1', $usp_options['usp_use_url']); } ?> />
										<span class="mm-item-caption"><?php _e('Check this box if you want to automatically use the registered user&rsquo;s specified URL as the submitted-post URL. Note: this really should only be used when requiring log-in for submissions.', 'usp'); ?></span></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_use_cat]"><?php _e('Use a hidden field for submitted category?', 'usp'); ?></label></th>
										<td><input type="checkbox" value="1" name="usp_options[usp_use_cat]" <?php if (isset($usp_options['usp_use_cat'])) { checked('1', $usp_options['usp_use_cat']); } ?> />
										<span class="mm-item-caption"><?php _e('Check this box if you want to use a hidden category field for the submitted category. Note: this may be used to specify a default category for submitted posts when the category field is hidden.', 'usp'); ?></span></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_use_cat_id]"><?php _e('Category ID for hidden field', 'usp'); ?></label></th>
										<td><input type="text" size="45" maxlength="200" name="usp_options[usp_use_cat_id]" value="<?php echo esc_attr($usp_options['usp_use_cat_id']); ?>" />
										<div class="mm-item-caption"><?php _e('Specify a cateogry (ID) to use as the default category when using the &ldquo;hidden field&rdquo; option.', 'usp'); ?></div></td>
									</tr>
								</table>
							</div>
							<h4><?php _e('Challenge question (captcha)', 'usp'); ?></h4>
							<div class="mm-table-wrap">
								<table class="widefat mm-table">
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_question]"><?php _e('Challenge Question', 'usp'); ?></label></th>
										<td><input type="text" size="45" maxlength="200" name="usp_options[usp_question]" value="<?php echo esc_attr($usp_options['usp_question']); ?>" />
										<div class="mm-item-caption"><?php _e('To prevent spam, enter a question that users must answer before submitting the form.', 'usp'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_response]"><?php _e('Challenge Response', 'usp'); ?></label></th>
										<td><input type="text" size="45" maxlength="200" name="usp_options[usp_response]" value="<?php echo esc_attr($usp_options['usp_response']); ?>" />
										<div class="mm-item-caption"><?php _e('Enter the <em>only</em> correct answer to the challenge question.', 'usp'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[usp_casing]"><?php _e('Case-sensitivity', 'usp'); ?></label></th>
										<td><input type="checkbox" value="1" name="usp_options[usp_casing]" <?php if (isset($usp_options['usp_casing'])) { checked('1', $usp_options['usp_casing']); } ?> />
										<span class="mm-item-caption"><?php _e('Check this box if you want the challenge response to be case-sensitive.', 'usp'); ?></span></td>
									</tr>
								</table>
							</div>
							<h4><?php _e('Options for image uploads', 'usp'); ?></h4>
							<div class="mm-table-wrap">
								<table class="widefat mm-table">
									<tr>
										<th scope="row"><label class="description" for="usp_options[upload-message]"><?php _e('Upload Message', 'usp'); ?></label></th>
										<td><textarea class="textarea" rows="3" cols="50" name="usp_options[upload-message]"><?php echo esc_attr($usp_options['upload-message']); ?></textarea>
										<div class="mm-item-caption"><?php _e('This is the message that appears next to upload field. Useful to state your upload guidelines/rules/etc.', 'usp'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[min-images]"><?php _e('Minimum number of images', 'usp'); ?></label></th>
										<td>
											<select name="usp_options[min-images]">
												<?php foreach(range(0, 20) as $number) { ?>
												<option <?php selected($number, $usp_options['min-images']); ?> value="<?php echo $number; ?>">
													<?php echo $number; ?>
												</option>
												<?php } ?>
											</select>
											<div class="mm-item-caption"><?php _e('Specify the <em>minimum</em> number of images.', 'usp'); ?></div>
										</td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[max-images]"><?php _e('Maximum number of images', 'usp'); ?></label></th>
										<td>
											<select name="usp_options[max-images]">
												<option value="-1"><?php _e('No Limit', 'usp'); ?></option>
												<?php foreach(range(0, 20) as $number) { ?>
												<option <?php selected($number, $usp_options['max-images']); ?> value="<?php echo $number; ?>">
													<?php echo $number; ?>
												</option>
												<?php } ?>
											</select>
											<div class="mm-item-caption"><?php _e('Specify the <em>maximum</em> number of images.', 'usp'); ?></div>
										</td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[min-image-width]"><?php _e('Minimum image width', 'usp'); ?></label></th>
										<td><input type="text" size="5" maxlength="200" name="usp_options[min-image-width]" value="<?php echo esc_attr($usp_options['min-image-width']); ?>" />
										<div class="mm-item-caption"><?php _e('Specify a <em>minimum width</em> (in pixels) for uploaded images.', 'usp'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[min-image-height]"><?php _e('Minimum image height', 'usp'); ?></label></th>
										<td><input type="text" size="5" maxlength="200" name="usp_options[min-image-height]" value="<?php echo esc_attr($usp_options['min-image-height']); ?>" />
										<div class="mm-item-caption"><?php _e('Specify a <em>minimum height</em> (in pixels) for uploaded images.', 'usp'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[max-image-width]"><?php _e('Maximum image width', 'usp'); ?></label></th>
										<td><input type="text" size="5" maxlength="200" name="usp_options[max-image-width]" value="<?php echo esc_attr($usp_options['max-image-width']); ?>" />
										<div class="mm-item-caption"><?php _e('Specify a <em>maximum width</em> (in pixels) for uploaded images.', 'usp'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="usp_options[max-image-height]"><?php _e('Maximum image height', 'usp'); ?></label></th>
										<td><input type="text" size="5" maxlength="200" name="usp_options[max-image-height]" value="<?php echo esc_attr($usp_options['max-image-height']); ?>" />
										<div class="mm-item-caption"><?php _e('Specify a <em>maximum height</em> (in pixels) for uploaded images.', 'usp'); ?></div></td>
									</tr>
								</table>
							</div>
							<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'usp'); ?>" />
						</div>
					</div>
					<div id="mm-panel-secondary" class="postbox">
						<h3><?php _e('Shortcode &amp; Template Tag', 'usp'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">

							<h4><?php _e('Shortcode', 'usp'); ?></h4>
							<p><?php _e('Use this shortcode to display the USP Form on any post or page:', 'usp'); ?></p>
							<p><code class="mm-code">[user-submitted-posts]</code></p>

							<h4><?php _e('Template tag', 'usp'); ?></h4>
							<p><?php _e('Use this template tag to display the USP Form anywhere in your theme template:', 'usp'); ?></p>
							<p><code class="mm-code">&lt;?php if (function_exists('user_submitted_posts')) user_submitted_posts(); ?&gt;</code></p>
						</div>
					</div>
					<div id="mm-restore-settings" class="postbox">
						<h3><?php _e('Restore Default Options', 'usp'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<p>
								<input name="usp_options[default_options]" type="checkbox" value="1" id="mm_restore_defaults" <?php if (isset($usp_options['default_options'])) { checked('1', $usp_options['default_options']); } ?> /> 
								<label class="description" for="usp_options[default_options]"><?php _e('Restore default options upon plugin deactivation/reactivation.', 'usp'); ?></label>
							</p>
							<p>
								<small>
									<?php _e('<strong>Tip:</strong> leave this option unchecked to remember your settings. Or, to go ahead and restore all default options, check the box, save your settings, and then deactivate/reactivate the plugin.', 'usp'); ?>
								</small>
							</p>
							<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'usp'); ?>" />
						</div>
					</div>
					<div id="mm-panel-current" class="postbox">
						<h3><?php _e('Updates &amp; Info', 'usp'); ?></h3>
						<div class="toggle">
							<div id="mm-iframe-wrap">
								<iframe src="http://perishablepress.com/current/index-usp.html"></iframe>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="mm-credit-info">
				<a target="_blank" href="<?php echo $usp_homeurl; ?>" title="<?php echo $usp_plugin; ?> Homepage"><?php echo $usp_plugin; ?></a> by 
				<a target="_blank" href="http://twitter.com/perishable" title="Jeff Starr on Twitter">Jeff Starr</a> @ 
				<a target="_blank" href="http://monzilla.biz/" title="Obsessive Web Design &amp; Development">Monzilla Media</a>
			</div>
		</form>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			// toggle panels
			jQuery('.default-hidden').hide();
			jQuery('#mm-panel-toggle a').click(function(){
				jQuery('.toggle').slideToggle(300);
				return false;
			});
			jQuery('h3').click(function(){
				jQuery(this).next().slideToggle(300);
			});
			jQuery('#mm-panel-primary-link').click(function(){
				jQuery('.toggle').hide();
				jQuery('#mm-panel-primary .toggle').slideToggle(300);
				return true;
			});
			jQuery('#mm-panel-secondary-link').click(function(){
				jQuery('.toggle').hide();
				jQuery('#mm-panel-secondary .toggle').slideToggle(300);
				return true;
			});
			// prevent accidents
			if(!jQuery("#mm_restore_defaults").is(":checked")){
				jQuery('#mm_restore_defaults').click(function(event){
					var r = confirm("<?php _e('Are you sure you want to restore all default options? (this action cannot be undone)', 'usp'); ?>");
					if (r == true){  
						jQuery("#mm_restore_defaults").attr('checked', true);
					} else {
						jQuery("#mm_restore_defaults").attr('checked', false);
					}
				});
			}
		});
	</script>

<?php } ?>