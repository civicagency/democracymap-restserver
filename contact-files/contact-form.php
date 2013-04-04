<?php
/*
Fast Secure Contact Form - PHP Script
Author: Mike Challis
http://www.FastSecureContactForm.com/
*/
//error_reporting(E_ALL); // Report all errors and warnings (very strict, use for testing only)
//ini_set('display_errors', 1); // turn error reporting on

$fsc_version = '3.1';

//do not allow direct access
if ( strpos(strtolower($_SERVER['SCRIPT_NAME']),strtolower(basename(__FILE__))) ) {
 header('HTTP/1.0 403 Forbidden');
 exit('Forbidden');
}

/*  Copyright (C) 2008-2012 Mike Challis  (http://www.fastsecurecontactform.com/contact)

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// requires PHP 5.1

if (!class_exists('FSCForm')) {

 class FSCForm {
     private $fsc_error;
     private $uploaded_files;
     private $fsc_opt;
     private $fsc_gb;
     private $fsc_opt_defaults;
     private $fsc_gb_defaults;
     private $fsc_site;
     private $site_path;
     private $form_action_url;
     private $captcha_url;
     private $captcha_path;
     private $fsc_login_error;
     private $ctf_notes_style;
     public  $fsc_version;

   // password protection feature to check if admin logged in or not
   // also sets the cookie when logged in
private function process_login() {

   // time out login after NN minutes of inactivity. Set to 0 to not timeout
   $login_timeout_minutes = 60;

   // This setting is only useful when $login_timeout_minutes is not zero
   // 1 - reset timeout time from last activity, 0 - timeout time from login
   $login_timeout_check_activity = 1;

  // timeout in seconds
  //$timeout = ($login_timeout_minutes == 0 ? 0 : time() + $login_timeout_minutes * 60);
  // was having issues with expire times and cookies not setting on IE browsers when server is in a different timezone
  // timeout 0 means the cookie will stay active (logged in) until "end of session" (when you close the browser).
  $timeout = 0; // end of session

  $fsc_site = $this->fsc_site;

  $scripturlparts = explode('/', $_SERVER['PHP_SELF']);
  $scriptfilename = $scripturlparts[count($scripturlparts)-1];
  $cookie_path = preg_replace("/$scriptfilename$/i", '', $_SERVER['PHP_SELF']);

   if ( !preg_match("/hashed_/", $fsc_site['admin_pwd']) ) {
       // md5 and save
       $unhashed_admin_pwd = $fsc_site['admin_pwd'];
       $fsc_site['admin_pwd'] = 'hashed_'. md5($fsc_site['admin_pwd']);
       $this->set_option("fsc_site", $fsc_site);

       if (isset($_COOKIE['fsc_verify'])) {
          // was i logged in the old way?
          // check if cookie actually passes user login test
          $found = false;
          $LOGIN_INFO = array( $fsc_site['admin_usr'] => $unhashed_admin_pwd, );
          foreach($LOGIN_INFO as $key => $val) {
             $lp = $key .'%'. $val;
             if ($_COOKIE['fsc_verify'] == md5($lp)) {
               $found = true;
               break;
             }
          }
          if ($found) {
             $admin_pwd_c = str_replace('hashed_', '',$fsc_site['admin_pwd']);
             setcookie("fsc_verify", md5($fsc_site['admin_usr'].'%'.$admin_pwd_c), $timeout, $cookie_path);
             header('Location: '.$fsc_site['site_url'].'/admin/index.php');
             exit;
          }
       }
   } else {
       $fsc_site['admin_pwd'] = str_replace('hashed_', '',$fsc_site['admin_pwd']);
   }
   $LOGIN_INFO = array( $fsc_site['admin_usr'] => $fsc_site['admin_pwd'], );

  // if logout, clear login cooke
  if(isset($_GET['logout'])) {
    setcookie("fsc_verify", '', $timeout, $cookie_path);
    return false;
  }

  // user provided password
  if (isset($_POST['access_password'])) {

    $user = isset($_POST['access_user']) ? $_POST['access_user'] : '';
    $pass = md5($_POST['access_password']);

    if (  !array_key_exists($user, $LOGIN_INFO) || $LOGIN_INFO[$user] != $pass  ) {
      $this->fsc_login_error = _('Invalid Login');
      return false;
    } else {
      // set cookie if password was validated
      setcookie("fsc_verify", md5($user.'%'.$pass), $timeout, $cookie_path);

      // clear login post vars
      unset($_POST['access_user']);
      unset($_POST['access_password']);
      unset($_POST['Submit']);
      return true;
    }
  } else {
    // check if password cookie is set
    if (!isset($_COOKIE['fsc_verify']))
      return false;

    // check if cookie actually passes user login test
    $found = false;
    foreach($LOGIN_INFO as $key => $val) {
       $lp = $key .'%'. $val;
       if ($_COOKIE['fsc_verify'] == md5($lp)) {
         $found = true;
        // prolong timeout
        if ($login_timeout_check_activity) {
          setcookie("fsc_verify", md5($lp), $timeout, $cookie_path);
        }
        break;
      }
    }
    if ($found)
       return true;
    else
       return false;
  }
  return false;
} // end function process_login

public function admin_do() {

  $this->site_path = '..';

  $form_num = '';

  // multi-form
  $form_num = $this->get_form_num();

  if($form_num == '') {
        $form_id = 1;
  }else{
        $form_id = $form_num;
  }

  // get options for this form
  $this->init_options($form_num);

  $fsc_site = $this->fsc_site;
  $fsc_opt = $this->fsc_opt;
  $fsc_opt_defaults = $this->fsc_opt_defaults;
  $fsc_gb = $this->fsc_gb;
  $fsc_gb_defaults = $this->fsc_gb_defaults;
/*  print_r($fsc_site);
  print_r($fsc_opt);
  print_r($fsc_gb);
  exit;*/

  $this->set_language($fsc_site['language'], '..');
  require 'admin/contact-form-admin.php';

}

public function lost_pw_do() {

  $this->site_path = '..';

  $form_num = '';

  // multi-form
  $form_num = $this->get_form_num();

  if($form_num == '') {
        $form_id = 1;
  }else{
        $form_id = $form_num;
  }

  // get options for this form
  $this->init_options($form_num);

  $fsc_site = $this->fsc_site;
  $fsc_opt = $this->fsc_opt;
  $fsc_opt_defaults = $this->fsc_opt_defaults;
  $fsc_gb = $this->fsc_gb;
  $fsc_gb_defaults = $this->fsc_gb_defaults;
/*  print_r($fsc_site);
  print_r($fsc_opt);
  print_r($fsc_gb);
  exit;*/

  $this->set_language($fsc_site['language'], '..');
  require 'admin/contact-form-lost-pw.php';

}

public function install_do($fsc_gb_settings) {

  $this->site_path = $fsc_gb_settings['site_path'];

  // get global options
  if ( !$fsc_site = $this->get_option("fsc_site") ) {
        $this->set_option("fsc_site", $fsc_gb_settings);

        $fsc_site = $this->get_option("fsc_site");

        $scripturlparts = explode('/', $_SERVER['PHP_SELF']);
        $scriptfilename = $scripturlparts[count($scripturlparts)-1];
        $cookie_path = preg_replace("/install\/$scriptfilename$/i", 'admin/', $_SERVER['PHP_SELF']);
        // set login credentials
        $admin_pwd_c = str_replace('hashed_', '',$fsc_site['admin_pwd']);
        //setcookie("fsc_verify", md5($fsc_site['admin_usr'].'%'.$admin_pwd_c),  time() + 3600, $cookie_path);
        setcookie("fsc_verify", md5($fsc_site['admin_usr'].'%'.$admin_pwd_c), 0, $cookie_path);
        echo _('Install completed successfully. Go to <a href="../admin/index.php">admin page</a>.');
        exit;
  } else {
       // install has already been done
       echo _('Nothing changed because this install has already been done before.');
       echo '<br />';
       echo _('You can change settings on the admin page. Go to <a href="../admin/index.php">admin page</a>.');
  }

  // get options for form 1
  $form_num = '';
  $this->init_options($form_num);

  $fsc_site = $this->fsc_site;
  $fsc_opt = $this->fsc_opt;
  $fsc_opt_defaults = $this->fsc_opt_defaults;
  $fsc_gb = $this->fsc_gb;
  $fsc_gb_defaults = $this->fsc_gb_defaults;

}

private function init_options($form_num) {

  global $fsc_version;
  $this->fsc_version = $fsc_version;

  if ($this->site_path == '')
      $this->site_path = '.';

  if ( !$fsc_site = $this->get_option("fsc_site") ) {

      // install has not been done
       $string =  '<p>'._('Fast Secure Contact Form - PHP has not been completely installed.');
       $string .= '<br />';
       $string .= _('The basic configuration file is missing or not readable.');
       $string .= '<br />';
       $string .= _('The administrator will have to visit the installer page, see instructions.').'</p><!-- '.$this->fsc_version.' -->';
       $string .= _('Go to <a href="../install/index.php">install page</a>.');
       echo $string;
       exit;
  }

  $fsc_gb_defaults = array(
         'donated' => 'false',
         'max_forms' => '4',
         'max_fields' => '4',
         'akismet_enable' => 'false',
         'akismet_api_key' => '',
  );

  $fsc_opt_defaults = array(
         'form_name' => '',
         'welcome' => _('<p>Comments or questions are welcome.</p>'),
         'email_to' => _('Webmaster').','.$fsc_site['admin_email'],
         'php_mailer_enable' => 'phpmailer',
         'email_from' => '',
         'email_from_enforced' => 'false',
         'email_bcc' => '',
         'email_reply_to' => '',
         'email_subject' => $fsc_site['site_name'] . ' ' ._('Contact:'),
         'email_subject_list' => '',
         'name_format' => 'name',
         'name_type' => 'required',
         'email_type' => 'required',
         'subject_type' => 'required',
         'message_type' => 'required',
         'preserve_space_enable' => 'false',
         'max_fields' => $fsc_gb_defaults['max_fields'],
         'double_email' => 'false',
         'name_case_enable' => 'false',
         'sender_info_enable' => 'true',
         'domain_protect' => 'true',
         'email_check_dns' => 'false',
         'email_html' => 'false',
         'smtp_enable' => 'false',
         'smtp_host' => 'smtp.gmail.com',
         'smtp_encryption' => 'ssl',
         'smtp_port' => 465,
         'smtp_auth_enable' => 'true',
         'smtp_user' => 'you@gmail.com',
         'smtp_pass' => 'YourPassword',
         'akismet_disable' => 'false',
         'akismet_send_anyway' => 'true',
         'captcha_enable' => 'true',
         'captcha_small' => 'false',
         'captcha_difficulty' => 'medium',
         'captcha_no_trans' => 'false',
         'enable_audio' => 'true',
         'enable_audio_flash' => 'false',
         'redirect_enable' => 'true',
         'redirect_seconds' => '3',
         'redirect_url' => str_replace('contact-files','',$fsc_site['site_url']),
         'redirect_query' => 'false',
         'redirect_ignore' => '',
         'redirect_rename' => '',
         'redirect_add' => '',
         'redirect_email_off' => 'false',
         'silent_send' => 'off',
         'silent_url' => '',
         'silent_ignore' => '',
         'silent_rename' => '',
         'silent_add' => '',
         'silent_email_off' => 'false',
         'ex_fields_after_msg' => 'false',
         'date_format' => 'mm/dd/yyyy',
         'cal_start_day' => '0',
         'time_format' => '12',
         'attach_types' =>  'doc,pdf,txt,gif,jpg,jpeg,png',
         'attach_size' =>   '1mb',
         'textarea_html_allow' => 'false',
         'enable_areyousure' => 'false',
         'auto_respond_enable' => 'false',
         'auto_respond_html' => 'false', 
         'auto_respond_from_name' => $fsc_site['site_name'],
         'auto_respond_from_email' => $fsc_site['admin_email'],
         'auto_respond_reply_to' => $fsc_site['admin_email'],
         'auto_respond_subject' => '',
         'auto_respond_message' => '',
         'req_field_indicator_enable' => 'true',
         'req_field_label_enable' => 'true',
         'req_field_indicator' => ' *',
         'border_enable' => 'false',
         'form_style' => 'width:375px;',
         'border_style' => 'border:1px solid black; padding:10px;',
         'required_style' => 'text-align:left;',
         'notes_style' => 'text-align:left;',
         'title_style' => 'text-align:left; padding-top:5px;',
         'field_style' => 'text-align:left; margin:0;',
         'field_div_style' => 'text-align:left;',
         'error_style' => 'text-align:left; color:red;',
         'select_style' => 'text-align:left;',
         'captcha_div_style_sm' => 'width: 175px; height: 50px; padding-top:2px;',
         'captcha_div_style_m' => 'width: 250px; height: 65px; padding-top:2px;',
         'captcha_input_style' => 'text-align:left; margin:0; width:50px;',
         'submit_div_style' => 'text-align:left; padding-top:8px;',
         'button_style' => 'cursor:pointer; margin:0;',
         'reset_style' => 'cursor:pointer; margin:0;',
         'powered_by_style' => 'font-size:x-small; font-weight:normal; padding-top:5px;',
         'field_size' => '40',
         'captcha_field_size' => '6',
         'text_cols' => '30',
         'text_rows' => '10',
         'aria_required' => 'false',
         'title_border' => '',
         'title_dept' => '',
         'title_select' => '',
         'title_name' => '',
         'title_fname' => '',
         'title_mname' => '',
         'title_miname' => '',
         'title_lname' => '',
         'title_email' => '',
         'title_email2' => '',
         'title_email2_help' => '',
         'title_subj' => '',
         'title_mess' => '',
         'title_capt' => '',
         'title_submit' => '',
         'title_reset' => '',
         'title_areyousure' => '',
         'text_message_sent' => '',
         'tooltip_required' => '',
         'tooltip_captcha' => '',
         'tooltip_audio' => '',
         'tooltip_refresh' => '',
         'tooltip_filetypes' => '',
         'tooltip_filesize' => '',
         'enable_reset' => 'false',
         'enable_credit_link' => 'true',
         'error_contact_select' => '',
         'error_name'           => '',
         'error_email'          => '',
         'error_email2'         => '',
         'error_field'          => '',
         'error_subject'        => '',
         'error_message'        => '',
         'error_input'          => '',
         'error_captcha_blank'  => '',
         'error_captcha_wrong'  => '',
         'error_correct'        => '',
  );

   // optional extra fields
  $fsc_max_fields = $fsc_gb_defaults['max_fields'];
  if ($fsc_opt = $this->get_option("fsc_form$form_num")) { // when not in admin
     if (isset($fsc_opt['max_fields'])) // use previous setting if it is set
     $fsc_max_fields = $fsc_opt['max_fields'];
  }
  for ($i = 1; $i <= $fsc_max_fields; $i++) { // initialize new
        $fsc_opt_defaults['ex_field'.$i.'_default'] = '0';
        $fsc_opt_defaults['ex_field'.$i.'_default_text'] = '';
        $fsc_opt_defaults['ex_field'.$i.'_req'] = 'false';
        $fsc_opt_defaults['ex_field'.$i.'_label'] = '';
        $fsc_opt_defaults['ex_field'.$i.'_type'] = 'text';
        $fsc_opt_defaults['ex_field'.$i.'_max_len'] = '';
        $fsc_opt_defaults['ex_field'.$i.'_label_css'] = '';
        $fsc_opt_defaults['ex_field'.$i.'_input_css'] = '';
        $fsc_opt_defaults['ex_field'.$i.'_attributes'] = '';
        $fsc_opt_defaults['ex_field'.$i.'_regex'] = '';
        $fsc_opt_defaults['ex_field'.$i.'_regex_error'] = '';
        $fsc_opt_defaults['ex_field'.$i.'_notes'] = '';
        $fsc_opt_defaults['ex_field'.$i.'_notes_after'] = '';
  }

  // install the global option defaults
  if(!$this->get_option("fsc_form_gb")) {
      $this->set_option('fsc_form_gb',  $fsc_gb_defaults );
  }

  // install the option defaults
  if(!$this->get_option("fsc_form")) {
      $this->set_option('fsc_form',  $fsc_opt_defaults );
  }

  // multi-form
  $fsc_max_forms = ( isset($_POST['fsc_max_forms']) && is_numeric($_POST['fsc_max_forms']) ) ? $_POST['fsc_max_forms'] : $fsc_gb_defaults['max_forms'];
  for ($i = 2; $i <= $fsc_max_forms; $i++) {
    if(!$this->get_option("fsc_form$i")) {
        $this->set_option("fsc_form$i", $fsc_opt_defaults );
    }
  }

  // get the options from the database
  $fsc_gb = $this->get_option("fsc_form_gb");

  // array merge incase this version has added new options
  $fsc_gb = array_merge($fsc_gb_defaults, $fsc_gb);

  $this->set_option("fsc_form_gb", $fsc_gb);

  // get the options from the database
  $fsc_gb = $this->get_option("fsc_form_gb");

  // get the options from the database
  $fsc_opt = $this->get_option("fsc_form$form_num");

  if (!isset($fsc_opt['max_fields'])) {  // updated from version < 3.0.3
          $fsc_opt['max_fields'] = $fsc_gb['max_fields'];
          $this->set_option("fsc_form$form_num", $fsc_opt);
  }

  // array merge incase this version has added new options
  $fsc_opt = array_merge($fsc_opt_defaults, $fsc_opt);

  // strip slashes on get options array
  foreach($fsc_opt as $key => $val) {
           //$fsc_opt[$key] = $this->ctf_stripslashes($val);
  }

  if ($fsc_opt['php_mailer_enable'] == 'geekmail')
      $fsc_opt['php_mailer_enable'] == 'phpmailer';

  if ($fsc_opt['title_style'] == '' && $fsc_opt['field_style'] == '') {
     // if styles seem to be blank, reset styles
     $fsc_opt = $this->fsc_copy_styles($fsc_option_defaults,$fsc_opt);
  }
  // set timezone php5 style
  date_default_timezone_set($fsc_site['timezone']);
  $this->fsc_site = $fsc_site;
  $this->fsc_opt = $fsc_opt;
  $this->fsc_opt_defaults = $fsc_opt_defaults;
  $this->fsc_gb = $fsc_gb;
  $this->fsc_gb_defaults = $fsc_gb_defaults;

  $this->site_path = $fsc_site['site_path'];
  $this->captcha_url  = $fsc_site['site_url']. '/captcha';
  $this->captcha_path = $fsc_site['site_path']. '/captcha';
  // set the type of request (SSL or not)
  if ( $this->ctf_is_ssl() ) {
		$this->captcha_url = preg_replace('|http://|', 'https://', $this->captcha_url);
  }

} // end function init_options

// used when resetting or copying style settings
public function fsc_copy_styles($this_form_arr,$destination_form_arr) {

     $style_copy_arr = array(
     'border_enable','form_style','border_style','required_style','notes_style',
     'title_style','field_style','field_div_style','error_style','select_style',
     'captcha_div_style_sm','captcha_div_style_m','captcha_input_style','submit_div_style','button_style', 'reset_style',
     'powered_by_style','field_size','captcha_field_size','text_cols','text_rows');
     foreach($style_copy_arr as $style_copy) {
           $destination_form_arr[$style_copy] = $this_form_arr[$style_copy];
     }
     return $destination_form_arr;
}

public function set_language($locale = 'en_US', $contact_form_path = '.') {

  // gettext language support
  // https://launchpad.net/php-gettext/

  $contact_form_path = rtrim($contact_form_path, '/'); // no trailing slash
  $fsc_site = $this->fsc_site;

  $encoding = $fsc_site['site_charset'];

  // gather array of available locales based on folder names in contact-files/languages/ en_US, it_IT
  foreach ( scandir($contact_form_path . '/languages') as $lang ) {
       if( strpos($lang,'.')==false && $lang!='.' && $lang!='..'){
			   $supported_locales[] = $lang;
	   }
  }

  $this_locale = $locale;
  // allow URL overide ?lang=it_IT or ?lang=it
  if ( isset($_GET['lang']) ) {
       $lc_getlang = strtolower($_GET['lang']);
      foreach ($supported_locales as $lang) {
        if ( strlen($lc_getlang) == 2 && $lc_getlang == substr($lang, 0, 2)  ) {
             $this_locale = $lang;
             break;
        } else if ($lc_getlang == strtolower($lang)){
             $this_locale = $lang;
             break;
        }
     }
  }
  if ($this_locale == 'en_US')
    return;

  require_once $contact_form_path . '/gettext/gettext.inc';

  // Set the text domain
  $domain = 'fsc-form-'.$this_locale;

  // gettext functions
  T_setlocale(LC_MESSAGES, $this_locale);
  bindtextdomain($domain, $contact_form_path . '/languages');
  // bind_textdomain_codeset is supported only in PHP 4.2.0+
  if (function_exists('bind_textdomain_codeset'))
      bind_textdomain_codeset($domain, $encoding);
  textdomain($domain);

} // end function set_language

/**
 * Fix $_SERVER variables for various setups.
 *
 * @access private
 * @since 2.9.8.4
 */
function fsc_fix_server_vars() {
	global $PHP_SELF;

	$default_server_values = array(
		'SERVER_SOFTWARE' => '',
		'REQUEST_URI' => '',
	);

	$_SERVER = array_merge( $default_server_values, $_SERVER );

	// Fix for IIS when running with PHP ISAPI
	if ( empty( $_SERVER['REQUEST_URI'] ) || ( php_sapi_name() != 'cgi-fcgi' && preg_match( '/^Microsoft-IIS\//', $_SERVER['SERVER_SOFTWARE'] ) ) ) {

		// IIS Mod-Rewrite
		if ( isset( $_SERVER['HTTP_X_ORIGINAL_URL'] ) ) {
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
		}
		// IIS Isapi_Rewrite
		else if ( isset( $_SERVER['HTTP_X_REWRITE_URL'] ) ) {
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
		} else {
			// Use ORIG_PATH_INFO if there is no PATH_INFO
			if ( !isset( $_SERVER['PATH_INFO'] ) && isset( $_SERVER['ORIG_PATH_INFO'] ) )
				$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];

			// Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
			if ( isset( $_SERVER['PATH_INFO'] ) ) {
				if ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
					$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
				else
					$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
			}

			// Append the query string if it exists and isn't null
			if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
	}

	// Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
	if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && ( strpos( $_SERVER['SCRIPT_FILENAME'], 'php.cgi' ) == strlen( $_SERVER['SCRIPT_FILENAME'] ) - 7 ) )
		$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];

	// Fix for Dreamhost and other PHP as CGI hosts
	if ( strpos( $_SERVER['SCRIPT_NAME'], 'php.cgi' ) !== false )
		unset( $_SERVER['PATH_INFO'] );

	// Fix empty PHP_SELF
	$PHP_SELF = $_SERVER['PHP_SELF'];
	if ( empty( $PHP_SELF ) )
		$_SERVER['PHP_SELF'] = $PHP_SELF = preg_replace( '/(\?.*)?$/', '', $_SERVER["REQUEST_URI"] );
}

// this function prints the contact form
// and does all the decision making to send the email or not
public function form_do($form, $contact_form_path = '.', $contact_form_language_override = '', $contact_form_url = '') {

  $this->site_path = rtrim($contact_form_path, '/'); // no trailing slash

  $this->fsc_fix_server_vars();  // Fix for IIS $_SERVER vars

  // set the type of request (SSL or not)
  if ( $this->ctf_is_ssl() ) {
    define('FSC_HTTPS', 'on');
    $this->form_action_url = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  } else {
    define('FSC_HTTPS', 'off');
    $this->form_action_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  }
  if ( $contact_form_url != '' ) {  // you can set the form action URL manually in the page code.
    $this->form_action_url = $contact_form_url;
  }

  $fsc_gb_mf = $this->get_option("fsc_form_gb");

  $form_num = '';
  $form_id_num = 1;
  if ( isset($form) && is_numeric($form) && $form <= $fsc_gb_mf['max_forms'] ) {
     $form_num = (int)$form;
     $form_id_num = (int)$form;
     if ($form_num == 1)
        $form_num = '';
  }

  // get options for this form
  $fsc_gb = $this->init_options($form_num);

  $fsc_site = $this->fsc_site;
  $fsc_opt = $this->fsc_opt;
  $fsc_opt_defaults = $this->fsc_opt_defaults;
  $fsc_gb = $this->fsc_gb;
  $fsc_gb_defaults = $this->fsc_gb_defaults;

  $this_language = $fsc_site['language'];
  // check if language was manually set to override the site settings language
  // default to the site settings language if the requested language is not installed
  if ( isset($contact_form_language_override) && $contact_form_language_override != '' ) {
    // gather array of available locales based on folder names in contact-files/languages/ en_US, it_IT
    foreach ( scandir($contact_form_path . '/languages') as $lang ) {
       if( strpos($lang,'.')==false && $lang!='.' && $lang!='..'){
			   $supported_locales[] = $lang;
	   }
    }
    if ( in_array($contact_form_language_override, $supported_locales) )
        $this_language = $contact_form_language_override;
  }
  $this->set_language($this_language, $contact_form_path );


  // a couple language options need to be translated now.
  $this->update_lang();

// Email address(s) to receive Bcc (Blind Carbon Copy) messages
$ctf_email_address_bcc = $fsc_opt['email_bcc']; // optional

// optional subject list
$subjects = array ();
$subjects_test = explode("\n",trim($fsc_opt['email_subject_list']));
if(!empty($subjects_test) ) {
  $ct = 1;
  foreach($subjects_test as $v) {
       $v = trim($v);
       if ($v != '') {
          $subjects["$ct"] = $v;
          $ct++;
       }
  }
}

// E-mail Contacts
// the drop down list array will be made automatically by this code
// checks for properly configured E-mail To: addresses in options.
$ctf_contacts = array ();
$ctf_contacts_test = trim($fsc_opt['email_to']);
if(!preg_match("/,/", $ctf_contacts_test) ) {
    if($this->ctf_validate_email($ctf_contacts_test)) {
        // user1@example.com
       $ctf_contacts[] = array('CONTACT' => _('Webmaster'),  'EMAIL' => $ctf_contacts_test );
    }
} else {
  $ctf_ct_arr = explode("\n",$ctf_contacts_test);
  if (is_array($ctf_ct_arr) ) {
    foreach($ctf_ct_arr as $line) {
       // echo '|'.$line.'|' ;
       list($key, $value) = preg_split('#(?<!\\\)\,#',$line); //string will be split by "," but "\," will be ignored
       $key   = trim(str_replace('\,',',',$key)); // "\," changes to ","
       $value = trim($value);
       if ($key != '' && $value != '') {
          if(!preg_match("/;/", $value)) {
               // just one email here
               // Webmaster,user1@example.com
               $value = str_replace('[cc]','',$value);
               $value = str_replace('[bcc]','',$value);
               if ($this->ctf_validate_email($value)) {
                  $ctf_contacts[] = array('CONTACT' => $this->ctf_output_string($key),  'EMAIL' => $value);
               }
          } else {
               // multiple emails here
               // Webmaster,user1@example.com;user2@example.com;user3@example.com;[cc]user4@example.com;[bcc]user5@example.com
               $multi_cc_arr = explode(";",$value);
               $multi_cc_string = '';
               foreach($multi_cc_arr as $multi_cc) {
                  $multi_cc_t = str_replace('[cc]','',$multi_cc);
                  $multi_cc_t = str_replace('[bcc]','',$multi_cc_t);
                  if ($this->ctf_validate_email($multi_cc_t)) {
                     $multi_cc_string .= "$multi_cc,";
                   }
               }
               if ($multi_cc_string != '') { // multi cc emails
                  $ctf_contacts[] = array('CONTACT' => $this->ctf_output_string($key),  'EMAIL' => rtrim($multi_cc_string, ','));
               }
         }
      }

   } // end foreach
  } // end if (is_array($ctf_ct_arr) ) {
} // end else

//print_r($ctf_contacts);

// Site Name / Title
$ctf_sitename = $fsc_site['site_name'];

// Site Domain without the http://www like this: $domain = '642weather.com';
// Can be a single domain:      $ctf_domain = '642weather.com';
// Can be an array of domains:  $ctf_domain = array('642weather.com','someothersite.com');
// get site domain
$uri = parse_url($fsc_site['site_url']);
$this->ctf_domain = preg_replace("/^www\./i",'',$uri['host']);

// Make sure the form was posted from your host name only.
// This is a security feature to prevent spammers from posting from files hosted on other domain names
// "Input Forbidden" message will result if host does not match
$this->ctf_domain_protect = $fsc_opt['domain_protect'];

// Double E-mail entry is optional
// enabling this requires user to enter their email two times on the contact form.
$ctf_enable_double_email = $fsc_opt['double_email'];

// You can ban known IP addresses
// SET  $ctf_enable_ip_bans = 1;  ON,  $ctf_enable_ip_bans = 0; for OFF.
$ctf_enable_ip_bans = 0;

// Add IP addresses to ban here:  (be sure to SET  $ctf_enable_ip_bans = 1; to use this feature
$ctf_banned_ips = array(
'22.22.22.22', // example (add, change, or remove as needed)
'33.33.33.33', // example (add, change, or remove as needed)
);

// Wordwrap E-Mail message text so lines are no longer than 70 characters.
// SET  $ctf_wrap_message = 1;  ON,  $ctf_wrap_message = 0; for OFF.
$ctf_wrap_message = 1;

// add numbered keys starting with 1 to the $contacts array
$cont = array();
$ct = 1;
foreach ($ctf_contacts as $v)  {
    $cont["$ct"] = $v;
    $ct++;
}
$contacts = $cont;
unset($cont);

// initialize vars
$string = '';
$this->fsc_error = 0;
$fsc_error_print = '';
$message_sent = 0;
$mail_to    = '';
$to_contact = '';
$name       = $this->fsc_get_var($form_id_num,'name');
$f_name     = $this->fsc_get_var($form_id_num,'f_name');
$m_name     = $this->fsc_get_var($form_id_num,'m_name');
$mi_name    = $this->fsc_get_var($form_id_num,'mi_name');
$l_name     = $this->fsc_get_var($form_id_num,'l_name');
$email      = $this->fsc_get_var($form_id_num,'email');
$email2     = $this->fsc_get_var($form_id_num,'email');
$subject    = $this->fsc_get_var($form_id_num,'subject');
$message    = $this->fsc_get_var($form_id_num,'message');
$captcha_code  = '';

// optional extra fields
// capture query string vars
$have_attach = '';
for ($i = 1; $i <= $fsc_opt['max_fields']; $i++) {
   if ($fsc_opt['ex_field'.$i.'_label'] != '') {
      ${'ex_field'.$i} = '';
      ${'fsc_error_ex_field'.$i} = '';
      if ($fsc_opt['ex_field'.$i.'_type'] == 'time') {
         ${'ex_field'.$i.'h'} = $this->fsc_get_var($form_id_num,'ex_field'.$i.'h');
         ${'ex_field'.$i.'m'} = $this->fsc_get_var($form_id_num,'ex_field'.$i.'m');
         ${'ex_field'.$i.'ap'} = $this->fsc_get_var($form_id_num,'ex_field'.$i.'ap');
      }
      if( in_array($fsc_opt['ex_field'.$i.'_type'],array('hidden','text','email','url','textarea','date','password')) ) {
         ${'ex_field'.$i} = $this->fsc_get_var($form_id_num,'ex_field'.$i);
      }
      if ($fsc_opt['ex_field'.$i.'_type'] == 'radio' || $fsc_opt['ex_field'.$i.'_type'] == 'select') {
         $exf_opts_array = $this->fsc_get_exf_opts_array($fsc_opt['ex_field'.$i.'_label']);
         $check_ex_field = $this->fsc_get_var($form_id_num,'ex_field'.$i);
         if($check_ex_field != '' && is_numeric($check_ex_field) && $check_ex_field > 0 ) {
           if( isset($exf_opts_array[$check_ex_field-1]) )
               ${'ex_field'.$i} = $exf_opts_array[$check_ex_field-1];
         }
      }
      if ($fsc_opt['ex_field'.$i.'_type'] == 'select-multiple') {
         $exf_opts_array = $this->fsc_get_exf_opts_array($fsc_opt['ex_field'.$i.'_label']);
         $ex_cnt = 1;
         foreach ($exf_opts_array as $k) {
             if( $this->fsc_get_var($form_id_num,'ex_field'.$i.'_'.$ex_cnt) == 1 ){
                 ${'ex_field'.$i.'_'.$ex_cnt} = 'selected';
             }
             $ex_cnt++;
         }
      }
      if ($fsc_opt['ex_field'.$i.'_type'] == 'checkbox' || $fsc_opt['ex_field'.$i.'_type'] == 'checkbox-multiple') {
         $exf_array_test = trim($fsc_opt['ex_field'.$i.'_label'] );
         if(preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
            $exf_opts_array = $this->fsc_get_exf_opts_array($fsc_opt['ex_field'.$i.'_label']);
            $ex_cnt = 1;
            foreach ($exf_opts_array as $k) {
                if( $this->fsc_get_var($form_id_num,'ex_field'.$i.'_'.$ex_cnt) == 1 ){
                     ${'ex_field'.$i.'_'.$ex_cnt} = 'selected';
                }
                $ex_cnt++;
            }
         }else{
              if($this->fsc_get_var($form_id_num,'ex_field'.$i) == 1)
              ${'ex_field'.$i} = 'selected';
         }
      }
      if ($fsc_opt['ex_field'.$i.'_type'] == 'attachment')
         $have_attach = 'enctype="multipart/form-data" '; // for <form post
   }
}
$req_field_ind = ( $fsc_opt['req_field_indicator_enable'] == 'true' ) ? '<span class="required">'.$fsc_opt['req_field_indicator'].'</span>' : '';
$fsc_error_captcha = '';
$fsc_error_contact = '';
$fsc_error_name    = '';
$fsc_error_f_name  = '';
$fsc_error_m_name  = '';
$fsc_error_mi_name = '';
$fsc_error_l_name  = '';
$fsc_error_email   = '';
$fsc_error_email2  = '';
$fsc_error_double_email = '';
$fsc_error_subject = '';
$fsc_error_message = '';

// process form now
if (isset($_POST['fsc_action']) && ($_POST['fsc_action'] == 'send')
   && isset($_POST['fsc_form_id']) && ($_POST['fsc_form_id'] == $form_id_num)
) {

  // include the code to process the form and send the mail
  include $this->site_path . '/contact-form-process.php';

} // end if posted fsc_action = send



if($message_sent) {

        // Redirect to Home Page after message is sent
        $ctf_redirect_enable = $fsc_opt['redirect_enable'];
        // Used for the delay timer once the message has been sent
        $ctf_redirect_timeout = $fsc_opt['redirect_seconds']; // time in seconds to wait before loading another Web page
        // Web page to send the user to after the time has expired
        $ctf_redirect_url = $fsc_opt['redirect_url'];


// The $thank_you is what gets printed after the form is sent.
$ctf_thank_you = '
<p>
';
if ($fsc_opt['text_message_sent'] != '') {
        $ctf_thank_you .= $fsc_opt['text_message_sent'];
} else {
        $ctf_thank_you .= _('Your message has been sent, thank you.');
}
$ctf_thank_you .= '
</p>
';

if ($ctf_redirect_enable == 'true') {
     if ($ctf_redirect_url == '#')   // if you put # for the redirect URL it will redirect to the same page the form is on regardless of the page.
        $ctf_redirect_url = $this->form_action_url;

    // redirect query string code
   if ($fsc_opt['redirect_query'] == 'true') {
      // build query string
      $query_string = $this->fsc_export_convert($posted_data,$fsc_opt['redirect_rename'],$fsc_opt['redirect_ignore'],$fsc_opt['redirect_add'],'query');
      if(!preg_match("/\?/", $ctf_redirect_url) )
        $ctf_redirect_url .= '?'.$query_string;
      else
       $ctf_redirect_url .= '&'.$query_string;
   } // end if(redirect query


 $ctf_thank_you .= <<<EOT

<script type="text/javascript" language="javascript">
//<![CDATA[
var ctf_redirect_seconds=$ctf_redirect_timeout;
var ctf_redirect_time;
function ctf_redirect() {
  document.title='Redirecting in ' + ctf_redirect_seconds + ' seconds';
  ctf_redirect_seconds=ctf_redirect_seconds-1;
  ctf_redirect_time=setTimeout("ctf_redirect()",1000);
  if (ctf_redirect_seconds==-1) {
    clearTimeout(ctf_redirect_time);
    document.title='Redirecting ...';
    self.location='$ctf_redirect_url';
  }
}
function ctf_addOnloadEvent(fnc){
  if ( typeof window.addEventListener != "undefined" )
    window.addEventListener( "load", fnc, false );
  else if ( typeof window.attachEvent != "undefined" ) {
    window.attachEvent( "onload", fnc );
  }
  else {
    if ( window.onload != null ) {
      var oldOnload = window.onload;
      window.onload = function ( e ) {
        oldOnload( e );
        window[fnc]();
      };
    }
    else
      window.onload = fnc;
  }
}
ctf_addOnloadEvent(ctf_redirect);
//]]>
</script>
EOT;

$ctf_thank_you .= '
<img src="'. $fsc_site['site_url'] . '/ctf-loading.gif" alt="'.$this->ctf_output_string(_('Redirecting')).'" />&nbsp;&nbsp;
'._('Redirecting').' ... ';


// do not remove the above EOT line

}

      // thank you message is printed here
      $string .= $ctf_thank_you;
}else{

     $ctf_welcome_intro = "\n". $fsc_opt['welcome'];

     // welcome intro is printed here
     $string .= $ctf_welcome_intro;

     // include the code to display the form
     include $this->site_path . '/contact-form-display.php';

} // end if ( message sent

 return $string;
} // end function form_do

private function fsc_export_convert($posted_data,$rename,$ignore,$add,$return = 'array') {
    $query_string = '';
    $posted_data_export = array();
    //rename field names array
    $rename_fields = array();
    $rename_fields_test = explode("\n",$rename);
    if ( !empty($rename_fields_test) ) {
      foreach($rename_fields_test as $line) {
         if(preg_match("/=/", $line) ) {
            list($key, $value) = explode("=",$line);
            $key   = trim($key);
            $value = trim($value);
            if ($key != '' && $value != '')
              $rename_fields[$key] = $value;
         }
      }
    }
    // add fields
    $add_fields_test = explode("\n",$add);
    if ( !empty($add_fields_test) ) {
      foreach($add_fields_test as $line) {
         if(preg_match("/=/", $line) ) {
            list($key, $value) = explode("=",$line);
            $key   = trim($key);
            $value = trim($value);
            if ($key != '' && $value != '') {
              if($return == 'array')
		        $posted_data_export[$key] = $value;
              else
                $query_string .= $key . '=' . urlencode( stripslashes($value) ) . '&';
            }
         }
      }
    }
    //ignore field names array
    $ignore_fields = array();
    $ignore_fields = array_map('trim', explode("\n", $ignore));
    // $posted_data is an array of the form name value pairs
    foreach ($posted_data as $key => $value) {
	  if( is_string($value) ) {
         if ( in_array($key, $ignore_fields) )
            continue;
         $key = ( isset($rename_fields[$key]) ) ? $rename_fields[$key] : $key;
         if($return == 'array')
		    $posted_data_export[$key] = $value;
         else
            $query_string .= $key . '=' . urlencode( stripslashes($value) ) . '&';
      }
    }
    if($return == 'array')
      return $posted_data_export;
    else
      return $query_string;
} // end function fsc_export_convert

private function update_lang() {
   $fsc_site = $this->fsc_site;
   $fsc_opt = $this->fsc_opt;

   // a few language options need to be re-translated now.
   // had to do this becuse the options were actually needed to be set before the language translator was initialized

  // update translation for these options (for when switched from English to another lang)
  if ($fsc_opt['welcome'] == '<p>Comments or questions are welcome.</p>' ) {
     $fsc_opt['welcome'] = _('<p>Comments or questions are welcome.</p>');
     $fsc_opt_defaults['welcome'] = $fsc_opt['welcome'];
  }

  if ($fsc_opt['email_to'] == 'Webmaster,'.$fsc_site['admin_email']) {
       $fsc_opt['email_to'] = _('Webmaster').','.$fsc_site['admin_email'];
       $fsc_opt_defaults['email_to'] = $fsc_opt['email_to'];
  }

  if ($fsc_opt['email_subject'] == $fsc_site['site_name'] . ' ' .'Contact:') {
      $fsc_opt['email_subject'] =  $fsc_site['site_name'] . ' ' ._('Contact:');
      $fsc_opt_defaults['email_subject'] = $fsc_opt['email_subject'];
  }

} // end function update_lang

private function fsc_get_var($form_id_num,$name) {
   $value = (isset( $_GET["$form_id_num$name"])) ? $this->ctf_clean_input($_GET["$form_id_num$name"]) : '';
   return $value;
}

private function fsc_get_exf_opts_array($label) {
  $exf_opts_array = array();
  $exf_opts_label = '';
  $exf_array_test = trim($label);
  if(!preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
                // Error: A radio field is not configured properly in settings
  } else {
      list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
      $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
      $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
      if ($exf_opts_label != '' && $value != '') {
          if(!preg_match("/;/", $value)) {
             //Error: A radio field is not configured properly in settings.
          } else {
             // multiple options
             $exf_opts_array = explode(";",$value);
          }
      }
  } // end else
  return $exf_opts_array;
} //end function

// needed for making temp directories for attachments and captcha session files
private function init_temp_dir($dir) {

    // trailing slash it
    $dir = rtrim($dir, '/');
    $dir = $dir . '/';

    // make the temp directory
	$this->fsc_mkdir_p( $dir );
	@chmod( $dir, 0733 );
	$htaccess_file = $dir . '.htaccess';
	if ( !file_exists( $htaccess_file ) ) {
	   if ( $handle = @fopen( $htaccess_file, 'w' ) ) {
		   fwrite( $handle, "Deny from all\n" );
		   fclose( $handle );
	   }
    }
    $php_file = $dir . 'index.php';
	if ( !file_exists( $php_file ) ) {
       	if ( $handle = @fopen( $php_file, 'w' ) ) {
		   fwrite( $handle, '<?php //do not delete ?>' );
		   fclose( $handle );
     	}
	}
} // end function init_temp_dir

// needed for emptying temp directories for attachments and captcha session files
private function clean_temp_dir($dir, $minutes = 30) {
    // deletes all files over xx minutes old in a temp directory

    // trailing slash it
    $dir = rtrim($dir, '/');
    $dir = $dir . '/';

  	if ( ! is_dir( $dir ) || ! is_readable( $dir ) || ! is_writable( $dir ) )
		return false;

    $count = 0;
    $list = array();
	if ( $handle = @opendir( $dir ) ) {
		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( $file == '.' || $file == '..' || $file == '.htaccess' || $file == 'index.php')
				continue;

			$stat = @stat( $dir . $file );
			if ( ( $stat['mtime'] + $minutes * 60 ) < time() ) {
			    @unlink( $dir . $file );
				$count += 1;
			} else {
               $list[$stat['mtime']] = $file;
            }
		}
		closedir( $handle );
        // purge xx amount of files based on age to limit a DOS flood attempt. Oldest ones first, limit 500
        if( isset($list) && count($list) > 499) {
          ksort($list);
          $ct = 1;
          foreach ($list as $k => $v) {
            if ($ct > 499) @unlink( $dir . $v );
            $ct += 1;
          }
       }
	}
	return $count;
}

// used for file attachment feature
private function validate_attach( $attach_dir, $file, $ex_field ) {
    $fsc_opt = $this->fsc_opt;

    $result['valid'] = true;

    if ($fsc_opt['php_mailer_enable'] == 'php') {
        $result['valid'] = false;
		$result['error'] = _('Attachments not supported.');
		return $result;
    }

	if ( ($file['error'] && UPLOAD_ERR_NO_FILE != $file['error']) || !is_uploaded_file( $file['tmp_name'] ) ) {
		$result['valid'] = false;
		$result['error'] = _('Attachment upload failed.');
		return $result;
	}

	if ( empty( $file['tmp_name'] ) ) {
		$result['valid'] = false;
		$result['error'] = _('This field is required.');
		return $result;
	}

    // check file types
    $file_type_pattern = $fsc_opt['attach_types'];
	if ( $file_type_pattern == '' )
		$file_type_pattern = 'doc,pdf,txt,gif,jpg,jpeg,png';
    $file_type_pattern = str_replace(',','|',$fsc_opt['attach_types']);
    $file_type_pattern = str_replace(' ','',$file_type_pattern);
	$file_type_pattern = trim( $file_type_pattern, '|' );
	$file_type_pattern = '(' . $file_type_pattern . ')';
	$file_type_pattern = '/\.' . $file_type_pattern . '$/i';

	if ( ! preg_match( $file_type_pattern, $file['name'] ) ) {
		$result['valid'] = false;
		$result['error'] = _('Attachment file type not allowed.');
		return $result;
	}

    // check size
    $allowed_size = 1048576; // 1mb default
	if ( preg_match( '/^([[0-9.]+)([kKmM]?[bB])?$/', $fsc_opt['attach_size'], $matches ) ) {
	     $allowed_size = (int) $matches[1];
		 $kbmb = strtolower( $matches[2] );
		 if ( 'kb' == $kbmb ) {
		     $allowed_size *= 1024;
		 } elseif ( 'mb' == $kbmb ) {
		     $allowed_size *= 1024 * 1024;
		 }
	}
	if ( $file['size'] > $allowed_size ) {
		$result['valid'] = false;
		$result['error'] = _('Attachment file size is too large.');
		return $result;
	}

	$filename = $file['name'];

	// safer file names for scripts.
	if ( preg_match( '/\.(php|pl|py|rb|cgi)\d?$/', $filename ) )
		$filename .= '.txt';

 	//$attach_dir = './attachments/';

	$filename = $this->unique_filename( $attach_dir, $filename );

	$new_file = $attach_dir . $filename;

	if ( false === @move_uploaded_file( $file['tmp_name'], $new_file ) ) {
		$result['valid'] = false;
		$result['error'] = _('Attachment upload failed while moving file.');
		return $result;
	}

	// uploaded only readable for the owner process
	@chmod( $new_file, 0400 );

	$this->uploaded_files[$ex_field] = $new_file;

    $result['file_name'] = $filename; // needed for email message

	return $result;
}

/**
 * Get a filename that is sanitized and unique for the given directory.
 *
 * If the filename is not unique, then a number will be added to the filename
 * before the extension, and will continue adding numbers until the filename is
 * unique.
 *
 * The callback must accept two parameters, the first one is the directory and
 * the second is the filename.
 *
 * @param string $dir
 * @param string $filename
 * @return string New filename, if given wasn't unique.
 */
function unique_filename( $dir, $filename ) {
	// sanitize the file name before we begin processing
	$filename = $this->sanitize_file_name($filename);
	// separate the filename into a name and extension
	$info = pathinfo($filename);
	$ext = !empty($info['extension']) ? '.' . $info['extension'] : '';
	$name = basename($filename, $ext);
	// edge case: if file is named '.ext', treat as an empty name
	if ( $name === $ext )
		$name = '';
	// Increment the file number until we have a unique file to save in $dir.
	$number = '';
	// change '.ext' to lower case
	if ( $ext && strtolower($ext) != $ext ) {
	    $ext2 = strtolower($ext);
		$filename2 = preg_replace( '|' . preg_quote($ext) . '$|', $ext2, $filename );

		// check for both lower and upper case extension or image sub-sizes may be overwritten
		while ( file_exists($dir . "/$filename") || file_exists($dir . "/$filename2") ) {
				$new_number = $number + 1;
				$filename = str_replace( "$number$ext", "$new_number$ext", $filename );
				$filename2 = str_replace( "$number$ext2", "$new_number$ext2", $filename2 );
				$number = $new_number;
		}
		return $filename2;
	}
	while ( file_exists( $dir . "/$filename" ) ) {
	    if ( '' == "$number$ext" )
		   $filename = $filename . ++$number . $ext;
		else
		   $filename = str_replace( "$number$ext", ++$number . $ext, $filename );
	}
	return $filename;
}

/**
 * Sanitizes a filename replacing whitespace with dashes
 *
 * Removes special characters that are illegal in filenames on certain
 * operating systems and special characters requiring special escaping
 * to manipulate at the command line. Replaces spaces and consecutive
 * dashes with a single dash. Trim period, dash and underscore from beginning
 * and end of filename.
 *
 * @param string $filename The filename to be sanitized
 * @return string The sanitized filename
 */
function sanitize_file_name( $filename ) {
	$filename_raw = $filename;
	$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", chr(0));
	$filename = str_replace($special_chars, '', $filename);
	$filename = preg_replace('/[\s-]+/', '-', $filename);
	$filename = trim($filename, '.-_');

	// Split the filename into a base and extension[s]
	$parts = explode('.', $filename);

	// Return if only one extension
	if ( count($parts) <= 2 )
		return $filename;

	// Process multiple extensions
	$filename = array_shift($parts);
	$extension = array_pop($parts);
	$mimes = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'gif' => 'image/gif',
		'png' => 'image/png',
		'bmp' => 'image/bmp',
		'tif|tiff' => 'image/tiff',
		'ico' => 'image/x-icon',
		'asf|asx|wax|wmv|wmx' => 'video/asf',
		'avi' => 'video/avi',
		'divx' => 'video/divx',
		'flv' => 'video/x-flv',
		'mov|qt' => 'video/quicktime',
		'mpeg|mpg|mpe' => 'video/mpeg',
		'txt|asc|c|cc|h' => 'text/plain',
		'csv' => 'text/csv',
		'tsv' => 'text/tab-separated-values',
		'rtx' => 'text/richtext',
		'css' => 'text/css',
		'htm|html' => 'text/html',
		'mp3|m4a|m4b' => 'audio/mpeg',
		'mp4|m4v' => 'video/mp4',
		'ra|ram' => 'audio/x-realaudio',
		'wav' => 'audio/wav',
		'ogg|oga' => 'audio/ogg',
		'ogv' => 'video/ogg',
		'mid|midi' => 'audio/midi',
		'wma' => 'audio/wma',
		'mka' => 'audio/x-matroska',
		'mkv' => 'video/x-matroska',
		'rtf' => 'application/rtf',
		'js' => 'application/javascript',
		'pdf' => 'application/pdf',
		'doc|docx' => 'application/msword',
		'pot|pps|ppt|pptx|ppam|pptm|sldm|ppsm|potm' => 'application/vnd.ms-powerpoint',
		'wri' => 'application/vnd.ms-write',
		'xla|xls|xlsx|xlt|xlw|xlam|xlsb|xlsm|xltm' => 'application/vnd.ms-excel',
		'mdb' => 'application/vnd.ms-access',
		'mpp' => 'application/vnd.ms-project',
		'docm|dotm' => 'application/vnd.ms-word',
		'pptx|sldx|ppsx|potx' => 'application/vnd.openxmlformats-officedocument.presentationml',
		'xlsx|xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml',
		'docx|dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml',
		'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
		'swf' => 'application/x-shockwave-flash',
		'class' => 'application/java',
		'tar' => 'application/x-tar',
		'zip' => 'application/zip',
		'gz|gzip' => 'application/x-gzip',
		'exe' => 'application/x-msdownload',
		// openoffice formats
		'odt' => 'application/vnd.oasis.opendocument.text',
		'odp' => 'application/vnd.oasis.opendocument.presentation',
		'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		'odg' => 'application/vnd.oasis.opendocument.graphics',
		'odc' => 'application/vnd.oasis.opendocument.chart',
		'odb' => 'application/vnd.oasis.opendocument.database',
		'odf' => 'application/vnd.oasis.opendocument.formula',
		// wordperfect formats
		'wp|wpd' => 'application/wordperfect',
		);

	// Loop over any intermediate extensions.  Munge them with a trailing underscore if they are a 2 - 5 character
	// long alpha string not in the extension whitelist.
	foreach ( (array) $parts as $part) {
		$filename .= '.' . $part;

		if ( preg_match("/^[a-zA-Z]{2,5}\d?$/", $part) ) {
			$allowed = false;
			foreach ( $mimes as $ext_preg => $mime_match ) {
				$ext_preg = '!(^' . $ext_preg . ')$!i';
				if ( preg_match( $ext_preg, $part ) ) {
					$allowed = true;
					break;
				}
			}
			if ( !$allowed )
				$filename .= '_';
		}
	}
	$filename .= '.' . $extension;

	return $filename;
}

// makes bold html email labels
private function make_bold($label) {
   $fsc_opt = $this->fsc_opt;

   if ($fsc_opt['email_html'] == 'true')
        return '<b>'.$label.'</b>';
   else
        return $label;

}


// checks if captcha is enabled based on the options
private function is_captcha_enabled() {
   $fsc_opt = $this->fsc_opt;

   if ($fsc_opt['captcha_enable'] !== 'true') {
        return false; // captcha setting is disabled for si contact
   }
   return true;
} // end function is_captcha_enabled

private function check_captcha_requires() {

  $ok = 'ok';
  // Test for some required things, print error message if not OK.
  if ( !extension_loaded('gd') || !function_exists('gd_info') ) {
      $this->captcha_requires_error .= '<p '.$this->ctf_error_style.'>'._('ERROR: GD image support not detected in PHP!').'</p>';
      $this->captcha_requires_error .= '<p>'._('Contact your web host to enable GD image support for PHP.').'</p>';
      $ok = 'no';
  }
  if ( !function_exists('imagepng') ) {
      $this->captcha_requires_error .= '<p '.$this->ctf_error_style.'>'._('ERROR: imagepng function not detected in PHP!').'</p>';
      $this->captcha_requires_error .= '<p>'._('Contact your web host to enable imagepng support for PHP.').'</p>';
      $ok = 'no';
  }
  if ( !file_exists($this->captcha_path.'/securimage.php') ) {
       $this->captcha_requires_error .= '<p '.$this->ctf_error_style.'>'._('ERROR: captcha_library not found.').'</p>';
       $ok = 'no';
  }
  if ($ok == 'no')  return false;
  return true;
}

// this function adds the captcha to the contact form
private function get_captcha_html($fsc_error_captcha,$form_id_num) {

  $fsc_opt = $this->fsc_opt;
  $fsc_gb = $this->fsc_gb;
  $this->init_temp_dir($this->captcha_path . '/temp');

  $req_field_ind = ( $fsc_opt['req_field_indicator_enable'] == 'true' ) ? '<span class="required">'.$fsc_opt['req_field_indicator'].'</span>' : '';

  $string = '';

// Test for some required things, print error message right here if not OK.
if ($this->check_captcha_requires()) {

  $fsc_opt['captcha_image_style'] = 'border-style:none; margin:0; padding:0px; padding-right:5px; float:left;';
  $fsc_opt['audio_image_style'] = 'border-style:none; margin:0; padding:0px; vertical-align:top;';
  $fsc_opt['reload_image_style'] = 'border-style:none; margin:0; padding:0px; vertical-align:bottom;';

// the captch html
$string = '
<div '.$this->ctf_title_style.'> </div>
 <div ';
$this->ctf_captcha_div_style_sm = $this->fsc_convert_css($fsc_opt['captcha_div_style_sm']);
$this->ctf_captcha_div_style_m = $this->fsc_convert_css($fsc_opt['captcha_div_style_m']);

// url for no session captcha image
$securimage_show_url = $this->captcha_url .'/securimage_show.php?';
$securimage_size = 'width="175" height="60"';
if($fsc_opt['captcha_small'] == 'true') {
  $securimage_show_url .= 'ctf_sm_captcha=1&amp;';
  $securimage_size = 'width="132" height="45"';
}

$parseUrl = parse_url($this->captcha_url);
$securimage_url = $parseUrl['path'];

if($fsc_opt['captcha_difficulty'] == 'low') $securimage_show_url .= 'difficulty=1&amp;';
if($fsc_opt['captcha_difficulty'] == 'high') $securimage_show_url .= 'difficulty=2&amp;';
if($fsc_opt['captcha_no_trans'] == 'true') $securimage_show_url .= 'no_trans=1&amp;';

// clean out old captcha no session temp files
$this->clean_temp_dir($this->captcha_path . '/temp');
// pick new prefix token
$prefix_length = 16;
$prefix_characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
$prefix = '';
$prefix_count = strlen($prefix_characters);
while ($prefix_length--) {
        $prefix .= $prefix_characters[mt_rand(0, $prefix_count-1)];
}
$securimage_show_rf_url = $securimage_show_url . 'prefix=';
$securimage_show_url .= 'prefix='.$prefix;

$string .= ($fsc_opt['captcha_small'] == 'true') ? $this->ctf_captcha_div_style_sm : $this->ctf_captcha_div_style_m;
$string .= '>
    <img class="ctf-captcha" id="si_image_ctf'.$form_id_num.'" ';
    $string .= ($fsc_opt['captcha_image_style'] != '') ? 'style="' . $this->ctf_output_string( $fsc_opt['captcha_image_style'] ).'"' : '';
    $string .= ' src="'.$securimage_show_url.'" '.$securimage_size.' alt="';
    $string .= ($fsc_opt['tooltip_captcha'] != '') ? $this->ctf_output_string( $fsc_opt['tooltip_captcha'] ) : $this->ctf_output_string(_('CAPTCHA Image'));
    $string .='" title="';
    $string .= ($fsc_opt['tooltip_captcha'] != '') ? $this->ctf_output_string( $fsc_opt['tooltip_captcha'] ) : $this->ctf_output_string(_('CAPTCHA Image'));
    $string .= '" />'."\n";

    $string .= '    <input id="si_code_ctf_'.$form_id_num.'" type="hidden" name="si_code_ctf_'.$form_id_num.'" value="'.$prefix.'" />'."\n";

    $ctf_audio_type = 'noaudio';
    //Audio feature is disabled by Mike Challis until further notice because a proof of concept code CAPTCHA solving exploit was released - Security Advisory - SOS-11-007.
    $fsc_opt['enable_audio'] = 'false';

    if($fsc_opt['enable_audio'] == 'true') {
        $ctf_audio_type = 'wav';
       if($fsc_opt['enable_audio_flash'] == 'true') {
          $ctf_audio_type = 'flash';
          $securimage_play_url = $securimage_url.'/securimage_play.swf?prefix='.$prefix;
          $securimage_play_url2 = $securimage_url.'/securimage_play.php?prefix='.$prefix;

          $string .= '<div id="si_flash_ctf'.$form_id_num.'">
        <object type="application/x-shockwave-flash"
                data="'.$securimage_play_url.'&amp;bgColor1=#8E9CB6&amp;bgColor2=#fff&amp;iconColor=#000&amp;roundedCorner=5&amp;audio='.$securimage_play_url2.'"
                id="SecurImage_as3_'.$form_id_num.'" width="19" height="19">
			    <param name="allowScriptAccess" value="sameDomain" />
			    <param name="allowFullScreen" value="false" />
			    <param name="movie" value="'.$securimage_play_url.'&amp;bgColor1=#8E9CB6&amp;bgColor2=#fff&amp;iconColor=#000&amp;roundedCorner=5&amp;audio='.$securimage_play_url2.'" />
			    <param name="quality" value="high" />
			    <param name="bgcolor" value="#ffffff" />
		</object></div>
        ';
      }else{
         $securimage_play_url = $this->captcha_url.'/securimage_play.php?prefix='.$prefix;
         $string .= '    <div id="si_audio_ctf'.$form_id_num.'">'."\n";
         $string .= '      <a id="si_aud_ctf'.$form_id_num.'" href="'.$securimage_play_url.'" rel="nofollow" title="';
         $string .= ($fsc_opt['tooltip_audio'] != '') ? $this->ctf_output_string( $fsc_opt['tooltip_audio'] ) : $this->ctf_output_string(_('CAPTCHA Audio'));
         $string .= '">
      <img src="'.$this->captcha_url.'/images/audio_icon.png" width="22" height="20" alt="';
         $string .= ($fsc_opt['tooltip_audio'] != '') ? $this->ctf_output_string( $fsc_opt['tooltip_audio'] ) : $this->ctf_output_string(_('CAPTCHA Audio'));
         $string .= '" ';
         $string .= ($fsc_opt['audio_image_style'] != '') ? 'style="' . $this->ctf_output_string( $fsc_opt['audio_image_style'] ).'"' : '';
         $string .= ' onclick="this.blur();" /></a>
     </div>'."\n";
     }
   }
         $string .= '    <div id="si_refresh_ctf'.$form_id_num.'">'."\n";
         $string .= '      <a href="#" rel="nofollow" title="';
         $string .= ($fsc_opt['tooltip_refresh'] != '') ? $this->ctf_output_string( $fsc_opt['tooltip_refresh'] ) : $this->ctf_output_string(_('Refresh Image'));
         $string .= '" onclick="fsc_captcha_refresh(\''.$form_id_num.'\',\''.$ctf_audio_type.'\',\''.$securimage_url.'\',\''.$securimage_show_rf_url.'\'); return false;">'."\n";
         $string .= '      <img src="'.$this->captcha_url.'/images/refresh.png" width="22" height="20" alt="';
         $string .= ($fsc_opt['tooltip_refresh'] != '') ? $this->ctf_output_string( $fsc_opt['tooltip_refresh'] ) : $this->ctf_output_string(_('Refresh Image'));
         $string .=  '" ';
         $string .= ($fsc_opt['reload_image_style'] != '') ? 'style="' . $this->ctf_output_string( $fsc_opt['reload_image_style'] ).'"' : '';
         $string .=  ' onclick="this.blur();" /></a>
   </div>
 </div>

        <div '.$this->ctf_title_style.'>
                <label for="fsc_captcha_code'.$form_id_num.'">';
     $string .= ($fsc_opt['title_capt'] != '') ? $fsc_opt['title_capt'] : _('CAPTCHA Code').':';
     $string .= $req_field_ind.'</label>
        </div>
        <div '.$this->fsc_convert_css($fsc_opt['field_div_style']).'>'.$this->echo_if_error($fsc_error_captcha).'
                <input '.$this->fsc_convert_css($fsc_opt['captcha_input_style']).' type="text" value="" id="fsc_captcha_code'.$form_id_num.'" name="fsc_captcha_code" '.$this->ctf_aria_required.' size="'.$this->absint($fsc_opt['captcha_field_size']).'" />
        </div>
';
} else {
      $string .= $this->captcha_requires_error;
}
  return $string;
} // end function get_captcha_html

// return bool True if SSL, false if not used.
private function ctf_is_ssl(){
    if ( isset($_SERVER['HTTPS']) ) {
         if ( 'on' == strtolower($_SERVER['HTTPS']) )
	          return true;
	     if ( '1' == $_SERVER['HTTPS'] )
	          return true;
	} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
	     return true;
	} elseif ( 'on' == strtolower(getenv('HTTPS')) ) {
         return true;
    }
	return false;
} // end function ctf_is_ssl

// shows contact form errors
private function echo_if_error($this_error){
  if ($this->fsc_error) {
    if (!empty($this_error)) {
         return '
         <div '.$this->ctf_error_style.'>'. $this_error . '</div>'."\n";
    }
  }
} // end function echo_if_error

// functions for protecting and validating form input vars
private function ctf_clean_input($string, $preserve_space = 0) {
    if (is_string($string)) {
       if($preserve_space)
          return $this->ctf_sanitize_string(strip_tags($this->ctf_stripslashes($string)),$preserve_space);
       return trim($this->ctf_sanitize_string(strip_tags($this->ctf_stripslashes($string))));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = $this->ctf_clean_input($value,$preserve_space);
      }
      return $string;
    } else {
      return $string;
    }
} // end function ctf_clean_input

// functions for protecting and validating form vars
private function ctf_sanitize_string($string, $preserve_space = 0) {
    if(!$preserve_space)
      $string = preg_replace("/ +/", ' ', trim($string));

    return preg_replace("/[<>]/", '_', $string);
} // end function ctf_sanitize_string

// functions for protecting and validating form vars
private function ctf_stripslashes($string) {
        if (get_magic_quotes_gpc()) {
                return stripslashes($string);
        } else {
               return $string;
        }
} // end function ctf_stripslashes

// functions for protecting output against XSS. encode  < > & " ' (less than, greater than, ampersand, double quote, single quote).
private function ctf_output_string($string) {
    $string = str_replace('&', '&amp;', $string);
    $string = str_replace('"', '&quot;', $string);
    $string = str_replace("'", '&#39;', $string);
    $string = str_replace('<', '&lt;', $string);
    $string = str_replace('>', '&gt;', $string);
    return $string;
} // end function ctf_output_string

// A function knowing about name case (i.e. caps on McDonald etc)
// $name = name_case($name);
private function ctf_name_case($name) {
   $fsc_opt = $this->fsc_opt;

   if ($fsc_opt['name_case_enable'] !== 'true') {
        return $name; // name_case setting is disabled for si contact
   }
   if ($name == '') return '';
   $break = 0;
   $newname = strtoupper($name[0]);
   for ($i=1; $i < strlen($name); $i++) {
       $subed = substr($name, $i, 1);
       if (((ord($subed) > 64) && (ord($subed) < 123)) ||
           ((ord($subed) > 48) && (ord($subed) < 58))) {
           $word_check = substr($name, $i - 2, 2);
           if (!strcasecmp($word_check, 'Mc') || !strcasecmp($word_check, "O'")) {
               $newname .= strtoupper($subed);
           }else if ($break){
               $newname .= strtoupper($subed);
           }else{
               $newname .= strtolower($subed);
           }
             $break = 0;
       }else{
             // not a letter - a boundary
             $newname .= $subed;
             $break = 1;
       }
   }
   return $newname;
} // end function ctf_name_case

// checks proper url syntax (not perfect, none of these are, but this is the best I can find)
//   tutorialchip.com/php/preg_match-examples-7-useful-code-snippets/
private function ctf_validate_url($url) {

    $regex = "((https?|ftp)\:\/\/)?"; // Scheme
	$regex .= "([a-zA-Z0-9+!*(),;?&=\$_.-]+(\:[a-zA-Z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
    $regex .= "([a-zA-Z0-9-.]*)\.([a-zA-Z]{2,6})"; // Host or IP
    $regex .= "(\:[0-9]{2,5})?"; // Port
    $regex .= "(\/#\!)?"; // Path hash bang  (twitter) (mike challis added)
    $regex .= "(\/([a-zA-Z0-9+\$_-]\.?)+)*\/?"; // Path
    $regex .= "(\?[a-zA-Z+&\$_.-][a-zA-Z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
    $regex .= "(#[a-zA-Z_.-][a-zA-Z0-9+\$_.-]*)?"; // Anchor

	return preg_match("/^$regex$/", $url);

} // end function ctf_validate_url

// checks proper email syntax (not perfect, none of these are, but this is the best I can find)
private function ctf_validate_email($email) {
   $fsc_opt = $this->fsc_opt;

   //check for all the non-printable codes in the standard ASCII set,
   //including null bytes and newlines, and return false immediately if any are found.
   if (preg_match("/[\\000-\\037]/",$email)) {
      return false;
   }
   // regular expression used to perform the email syntax check
   // http://fightingforalostcause.net/misc/2006/compare-email-regex.php
   //$pattern = "/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|asia|cat|jobs|tel|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$/i";
   //$pattern = "/^([_a-zA-Z0-9-]+)(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+)(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})$/i";
   $pattern = "/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD";
   if(!preg_match($pattern, $email)){
      return false;
   }
   // Make sure the domain exists with a DNS check (if enabled in options)
   // MX records are not mandatory for email delivery, this is why this function also checks A and CNAME records.
   // if the checkdnsrr function does not exist (skip this extra check, the syntax check will have to do)
   // checkdnsrr available in Linux: PHP 4.3.0 and higher & Windows: PHP 5.3.0 and higher
   if ($fsc_opt['email_check_dns'] == 'true') {
      if( function_exists('checkdnsrr') ) {
         list($user,$domain) = explode('@',$email);
         if(!checkdnsrr($domain.'.', 'MX') &&
            !checkdnsrr($domain.'.', 'A') &&
            !checkdnsrr($domain.'.', 'CNAME')) {
            // domain not found in DNS
            return false;
         }
      }
   }
   return true;
} // end function ctf_validate_email

// helps spam protect email input
// finds new lines injection attempts
private function ctf_forbidifnewlines($input) {
   if (
       stristr($input, "\r")  !== false ||
       stristr($input, "\n")  !== false ||
       stristr($input, "%0a") !== false ||
       stristr($input, "%0d") !== false) {
         //die(_('Contact Form has Invalid Input'));
         $this->fsc_error = 1;

   }
} // end function ctf_forbidifnewlines

// helps spam protect email input
// blocks contact form posted from other domains
private function ctf_spamcheckpost() {

 if(!isset($_SERVER['HTTP_USER_AGENT'])){
     return _('Invalid User Agent');
 }

 // Make sure the form was indeed POST'ed:
 //  (requires your html form to use: fsc_action="post")
 if(!$_SERVER['REQUEST_METHOD'] == "POST"){
    return _('Invalid POST');
 }

  // Make sure the form was posted from an approved host name.
 if ($this->ctf_domain_protect == 'true') {
     $print_authHosts = '';
   // Host names from where the form is authorized to be posted from:
   if (is_array($this->ctf_domain)) {
      $this->ctf_domain = array_map(strtolower, $this->ctf_domain);
      $authHosts = $this->ctf_domain;
      foreach ($this->ctf_domain as $each_domain) {
         $print_authHosts .= ' '.$each_domain;
      }
   } else {
      $this->ctf_domain =  strtolower($this->ctf_domain);
      $authHosts = array("$this->ctf_domain");
      $print_authHosts = $this->ctf_domain;
   }

   // Where have we been posted from?
   if( isset($_SERVER['HTTP_REFERER']) and trim($_SERVER['HTTP_REFERER']) != '' ) {
      $fromArray = parse_url(strtolower($_SERVER['HTTP_REFERER']));
      // Test to see if the $fromArray used www to get here.
      $wwwUsed = preg_match("/^www\./i",$fromArray['host']);
      if(!in_array((!$wwwUsed ? $fromArray['host'] : preg_replace("/^www\./i",'',$fromArray['host'])), $authHosts ) ){
         return sprintf( _('Invalid HTTP_REFERER domain. See FAQ. The domain name posted from does not match the allowed domain names of this form: %s'), $print_authHosts );
      }
   }
 } // end if domain protect

 // check posted input for email injection attempts
 // Check for these common exploits
 // if you edit any of these do not break the syntax of the regex
 $input_expl = "/(content-type|mime-version|content-transfer-encoding|to:|bcc:|cc:|document.cookie|document.write|onmouse|onkey|onclick|onload)/i";
 // Loop through each POST'ed value and test if it contains one of the exploits fromn $input_expl:
 foreach($_POST as $k => $v){
   if (is_string($v)){
     $v = strtolower($v);
     $v = str_replace('donkey','',$v); // fixes invalid input with "donkey" in string
     $v = str_replace('monkey','',$v); // fixes invalid input with "monkey" in string
     if( preg_match($input_expl, $v) ){
       return _('Illegal characters in POST. Possible email injection attempt');
     }
   }
 }

 return 0;
} // end function ctf_spamcheckpost

// get the current contact form number (multi-forms)
private function get_form_num() {
    // get options
    $fsc_gb_mf = $this->get_option('fsc_form_gb');

    $form_num = '';
    if ( isset($_GET['ctf_form_num']) && is_numeric($_GET['ctf_form_num']) && $_GET['ctf_form_num'] > 1 && $_GET['ctf_form_num'] <= $fsc_gb_mf['max_forms'] ) {
       $form_num = (int)$_GET['ctf_form_num'];
    }
    return $form_num;
} // end function get_form_num

// restores settings from a contact form settings backup file
private function restore_options_from_backup($bk_form_num) {
     $fsc_opt = $this->fsc_opt;
     $fsc_opt_defaults = $this->fsc_opt_defaults;
     $fsc_gb = $this->fsc_gb;
     $fsc_gb_defaults = $this->fsc_gb_defaults;

   require $this->site_path . '/admin/contact-form-restore.php';  

} // end function restore_options_from_backup

// outputs a contact form settings backup file
private function generate_backup_download() {
   global $fsc_version;
   $fsc_site = $this->fsc_site;
   $fsc_opt = $this->fsc_opt;
   $fsc_opt_defaults = $this->fsc_opt_defaults;
   $fsc_gb = $this->fsc_gb;
   $fsc_gb_defaults = $this->fsc_gb_defaults;

  require $this->site_path . '/admin/contact-form-backup.php';

} // end function generate_backup_download

private function esc_url( $url ) {

	if ( '' == $url )
		return $url;
    $url = strip_tags($url);
	$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
	$strip = array('%0d', '%0a', '%0D', '%0A');
	$url = $this->deep_replace($strip, $url);

   	return $url;

}

private function deep_replace( $search, $subject ) {
      $found = true;
      $subject = (string) $subject;
      while ( $found ) {
          $found = false;
          foreach ( (array) $search as $val ) {
              while ( strpos( $subject, $val ) !== false ) {
                  $found = true;
                  $subject = str_replace( $val, '', $subject );
              }
          }
      }

      return $subject;
}

private function fsc_update_check() {
   // checks and alerts on admin page if there is a new program version, caches results for 1 hour
   $fsc_new_version = $this->fsc_version;
   $url = 'http://www.fastsecurecontactform.com/wp-content/plugins/download-fsc-php/version.txt';
   $cacheName = $this->site_path . '/settings/version-check.txt';
   $status = '';
   $refetchSeconds = 3600; // 3600 (1 hour)
   if (file_exists($cacheName) and filemtime($cacheName) + $refetchSeconds > time()) {  //600=10 min
      $age = time() - filectime($cacheName);
      $nextFetch = $refetchSeconds - $age;
      //$status = "\n<!-- using cached version check from $cacheName - age=$age secs. Next fetch in $nextFetch secs. -->\n";
      $status = "\n<!-- using cached version check - age=$age secs. Next fetch in $nextFetch secs. -->\n";
      $fsc_new_version = file_get_contents($cacheName);
      if ( $fsc_new_version == '' )
           return "<!-- version check returned blank -->\n".$status;
      if ( version_compare($fsc_new_version, $this->fsc_version, '<') )
           return "<!-- probably just updated -->\n".$status;
   } else {
      $status = "\n<!-- getting new version check file from $url -->\n";
      // Check for allow_url_fopen
      $allow_url_fopen = ((boolean)@ini_get('allow_url_fopen') === false) ? 0 : 1;
      if($allow_url_fopen){
           $fsc_new_version = trim(file_get_contents($url));
      } else if(function_exists('curl_init')) {  // try curl
           $c = curl_init();
           curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
           curl_setopt($c, CURLOPT_URL, $url);
           $fsc_new_version = trim(curl_exec($c));
           curl_close($c);
      } else {
           return ', '._('version update check failed.').$status;
      }
      // Write the contents to the file
      file_put_contents($cacheName, $fsc_new_version);
      //$status .= "<!-- cache saved to $cacheName -->\n";
      $status .= "<!-- cache saved -->\n";
   }
   if ( version_compare($fsc_new_version, $this->fsc_version, '>') )
      return ', <a style="color:green;" href="http://www.fastsecurecontactform.com/download-php-script">'.sprintf(_('a new version %s is available.'),$fsc_new_version).'</a>'.$status;
   else
      return "<!-- no new update -->\n".$status;

} // end function fsc_update_check


private function fsc_form_from_email() {
 return $this->fsc_from_email;
}

private function fsc_form_from_name() {
 return $this->fsc_from_name;
}


private function ctf_notes($notes) {
           return   '
        <div '.$this->ctf_notes_style.'>
         '.$notes.'
        </div>
        ';
}

private function fsc_convert_css($string) {

    if( preg_match("/^style=\"(.*)\"$/i", $string) ){
      return $string;
    }
    if( preg_match("/^class=\"(.*)\"$/i", $string) ){
      return $string;
    }
    return 'style="'.$string.'"';

} // end function fsc_convert_css

private function set_option($name, $array, $preserve_slashes = 0){

   $name = preg_replace( '/[^A-Za-z0-9_]+/', '', $name );
   if ( empty($name) )
       return false;

    if ( !is_array($array) || empty($array) )
       return false;

    // strip slashes on settings array
   if (!$preserve_slashes) {
      foreach($array as $k => $v) {
        $array[$k] = $this->ctf_stripslashes($v);
      }
   }

    $eol = "\r\n";
    $string = '<?php'."\n";
    // format the data to be stored
    $string .= '//do not allow direct access'."\n";
    $string .= 'if ( strpos(strtolower($_SERVER[\'SCRIPT_NAME\']),strtolower(basename(__FILE__))) ) {'."\n".'  header(\'HTTP/1.0 403 Forbidden\');'."\n".'  exit(\'Forbidden\');'."\n".'}'."\n";
    $string .= "//**AUTO-GENERATED DATA, DO NOT HAND EDIT!**$eol";
    $string .= "//Settings for 'Fast Secure Contact Form' PHP Script$eol";
    $string .= '$array = '. var_export($array, true).';';
    $string .= "\n".'?>';
    // save file now.
    $file = $this->site_path . '/settings/' . $name . '.php';
    if ( !file_put_contents($file, $string, LOCK_EX) ){
       return false;
    }
    return true;
}

private function get_option($name){
   //echo $this->site_path; exit;
   $name = preg_replace( '/[^A-Za-z0-9_]+/', '', $name );
   if ( empty($name) )
       return false;

   $file = $this->site_path . '/settings/' . $name . '.php';
   //$file = '../settings/' . $name . '.php';
   clearstatcache();
   if(file_exists($file)) {
      include $file;
      if ( !isset($array) || !is_array($array)  )
          return false;
   } else {
       return false;
   }
   return $array;
}

private function delete_option($name){

   $name = preg_replace( '/[^A-Za-z0-9_]+/', '', $name );
   if ( empty($name) )
       return false;

   $file = $this->site_path . '/settings/' . $name . '.php';
   if(file_exists($file)) {
      unlink($file);
      return true;
   }
   return false;
}

/**
 * Converts value to nonnegative integer.
 *
 * @param mixed $maybeint Data you wish to have convered to an nonnegative integer
 * @return int an nonnegative integer
 */
private function absint( $maybeint ) {
	return abs( intval( $maybeint ) );
}

/**
 * Recursive directory creation based on full path.
 *
 * Will attempt to set permissions on folders.
 *
 * @param string $target Full path to attempt to create.
 * @return bool Whether the path was created. True if path already exists.
 */
private function fsc_mkdir_p( $target ) {
	// from php.net/mkdir user contributed notes
	$target = str_replace( '//', '/', $target );

	// safe mode fails with a trailing slash under certain PHP versions.
	$target = rtrim($target, '/');
	if ( empty($target) )
		$target = '/';

	if ( file_exists( $target ) )
		return @is_dir( $target );

	// Don't clutter up display.
	if ( @mkdir( $target ) ) {
		$stat = @stat( dirname( $target ) );
		$dir_perms = $stat['mode'] & 0007777;  // Get the permission bits.
		@chmod( $target, $dir_perms );
		return true;
	} elseif ( is_dir( dirname( $target ) ) ) {
			return false;
	}

	// If the above failed, attempt to create the parent node, then try again.
	if ( ( $target != '/' ) && ( $this->fsc_mkdir_p( dirname( $target ) ) ) )
		return $this->fsc_mkdir_p( $target );

	return false;
} // end function fsc_mkdir_p

function fsc_code_usage($form_id) {
 // prints code usage instructions on admin page
 $fsc_site = $this->fsc_site;
?>
   <h2><?php echo _('Usage'); ?></h2>

<?php echo _('To display a form on your web page: Just add a few lines of PHP code.'); echo ' '; ?>
<a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_code_tip');"><?php echo _('Show PHP code'); ?></a>
<div style="text-align:left; display:none" id="fsc_code_tip">
<?php echo _('Edit the HTML of a PHP page on your web site and add this code:');
echo '<br />';
echo '<br />';
echo _('Put this code in the HTML head section:');
echo '<br />&lt;html&gt;<br />&lt;head&gt;<br />';
echo '<span style="color:green">&lt;script type="text/javascript" src="'.$fsc_site['site_url'].'/contact-form.js"&gt;&lt;/script&gt;</span>';
echo '<br />&lt;/head&gt;<br />';
echo '<br />';
echo _('Put this code in the HTML body section anywhere you want your form to show:');
echo '<br />&lt;html&gt;<br />&lt;head&gt;<br />&lt;/head&gt;<br />&lt;body&gt;<br />';
echo '<span style="color:green"><pre>&lt;?php
$contact_form = '.$form_id.'; // '._('set desired form number.').'
$contact_form_path = \''.$fsc_site['site_path'].'/\'; // '.sprintf(_('set path to %s with slash on end.'),'/contact-files/').'
require $contact_form_path . \'contact-form-run.php\';
?&gt;</pre></span>&lt;/body&gt;<br />&lt;/html&gt;<br />';
echo '<br />';
echo _('Notes: The code is highlighted in the color green. The &lt;?php ?&gt; tags may not be needed if you are putting the code in a part of your page that already has an open PHP tag.');
echo ' ';
echo _('You must set the path correctly or you will get a PHP error: "No such file or directory".');
echo ' ';
echo _('You can add more than one form on a page, just repeat the complete PHP code block using a different form number.');
?>
</div>

<br />
<br />

<?php

} // end function fsc_code_usage

} // end of class
} // end of if class


?>