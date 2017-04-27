<?php
/**
 * Plugin Name:       Gravity Forms Web Hook Add-On
 * Plugin URI:        https://www.gravityhelp.com/documentation/article/gform_after_submission/#2-send-entry-data-to-third-party
 * Description:       Submits Gravity Forms form data to a remote URI endpoint via POST (HTTP).
 * Version:           0.0.1-alpha
 * Author:            Jonathan Bell
 * Author URI:        mailto:jonathan.bell@gov.bc.ca
 * License:           WTFPL
 * License URI:       http://www.wtfpl.net/txt/copying/
 * Text Domain:       gravityformswebhook
 */

// Include our handler(s). 
// These functions will send the form data over HTTP POST to a remote URL.
require_once('includes/post-actions.php');

// Only register this plugin's functionality if Gravity Forms is installed 
// and the required hook exists. 
if (has_action('gform_after_submission')) {
  
  // Hook Gravity forms on form submit. 
  // https://www.gravityhelp.com/documentation/article/gform_after_submission/
  add_action('gform_after_submission', 'gfprwh_post_to_remote', 10, 2);

  // Admin menu and links for the plugin.
  add_action('admin_menu', 'gravity_forms_submenu_webhook', 11);

}

// Adds GUI stuff to the WP Admin backend. Place our plugin's options under Gravity Forms menu.
function gravity_forms_submenu_webhook() {
  add_submenu_page(
    'gf_edit_forms', // Third party plugin slug (Gravity Forms slug here). 
    'Gravity Forms Post Receive Web Hook', // <title> tag, among other things.
    'Web Hook', // Plugin title displayed to user. 
    'edit_pages', // Permissions level.
    'gfprwh', 
    'gfprwh_settings_page' // The function to be called to output the content for this (admin) page.
  );
}

// Admin page/form and handler. 
function gfprwh_settings_page() { 

  // Check user/admin permissions. 
  if (!current_user_can('edit_pages')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }

  $hidden_field_name = 'submit_flag_hidden';
  $pr_field_count = get_option('gfprwh_count') ? get_option('gfprwh_count') : 1;

  // See if the user has POSTed us some information from this form.
  // If they have, this hidden field will be set to 'Y'
  if (isset($_POST[$hidden_field_name]) && $_POST[$hidden_field_name] == 'Y') {

    // This is hackish, but I don't know Wordpress that well. So, sue me! 
    // No, wait... Please don't sue me.. 
    for ($i=1; $i <= $pr_field_count; $i++) {
      // Deletes existing values so that we can populate or re-populate with new ones just sent. 
      delete_option('gfprwh_formID__'.$i);
      delete_option('gfprwh_form_post_url__'.$i);
      // TODO: Convert the above options into a serialized array and store the values that way instead.
    }
    
    $pr_field_count = 0;

    // Loop through POST values sent. 
    foreach($_POST as $key => $value) {
      // Filter the POST values that we are interested in. 
      if (strpos($key, 'gfprwh_') !== false) {

        // TODO: Validate these values better:
        // Make sure that the URL sent is actually a URL.
        // Make sure that the formID is numeric and exists.
        if ('' != trim($value)) {
          // Update the options table with new values. 
          update_option($key, $value);
          // Increment our counter for each "row" in the form.
          if (strpos($key, 'gfprwh_formID__') !== false) {
            $pr_field_count++;
          }
        }

      }
    }

    update_option('gfprwh_count', $pr_field_count);
    echo '<div class="updated"><p><strong>Settings Saved.</strong></p></div>';
  
  } // if user sent POST data.

  // The settings form...
  echo '<h1>Gravity Forms Web Hooks</h1>';
  echo '<p>Add a Gravity Forms Form ID on the left and add a URL endpoint on the right. Whenever data is submitted to the form, the form data will be sent to the URL via HTTP_POST.</p><p>It\'s up to the recieving server to verify the data sent to it is legitimate (in order to aviod Cross-Site Request Forgery). One way to validate/check the authenticity of the POST data would be to check that the data came from this server\'s IP ('.$_SERVER['SERVER_ADDR'].').</p>';
  echo '<form name="gfprwh_ids" method="post" action="">';
  echo '<input type="hidden" name="'.$hidden_field_name.'" value="Y">';

  echo '<fieldset>';
  for($i=1; $i <= $pr_field_count + 1; $i++) {

    echo '<label for="gfprwh_formID__'.$i.'">Gravity Forms Form ID: </label> <input type="text" name="gfprwh_formID__'.$i.'" value="'.get_option('gfprwh_formID__'.$i).'" size="2"> ';

    echo '<label for="gfprwh_form_post_url__'.$i.'">POST URL: </label> <input style="width: 50%" type="text" name="gfprwh_form_post_url__'.$i.'" value="'.get_option('gfprwh_form_post_url__'.$i).'">';
    echo '<br><br>';

  }
  echo '</fieldset>';

  echo '<hr />';
  echo '<input type="submit" name="Submit" class="button-primary" value="Save Changes" />';
  echo '</form>';  

} // plugin_options_gfprwh

