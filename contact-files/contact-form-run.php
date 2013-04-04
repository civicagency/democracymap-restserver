<?php
/*
Fast Secure Contact Form - PHP Script
Author: Mike Challis
http://www.FastSecureContactForm.com/
*/
//do not allow direct access
if ( strpos(strtolower($_SERVER['SCRIPT_NAME']),strtolower(basename(__FILE__))) ) {
 header('HTTP/1.0 403 Forbidden');
 exit('Forbidden');
}

// fixes no gettext support error: Fatal error: Call to undefined function _()
if (!function_exists('_')) {
    function _($string) {
          return $string;
    }
}

// requires PHP 5.1 or higher
$phpversion = substr(PHP_VERSION, 0, 6);
if($phpversion >= 5.1) {

  if (!isset($contact_form_language_override))
        $contact_form_language_override = '';
  if (!isset($contact_form_url))
        $contact_form_url = '';

  require $contact_form_path . 'contact-form.php';
  if (class_exists('FSCForm') && !isset($fsc_form) ) {
    $fsc_form = new FSCForm();
  }
  if (isset($fsc_form)) {
     echo $fsc_form->form_do($contact_form, $contact_form_path, $contact_form_language_override, $contact_form_url);
  }

}else{
	echo '<p><span style="color:red;">'._('Fast Secure Contact Form requires PHP version 5.1 or higher').'</span><br />'.
    _('Please upgrade PHP in order to proceed').'<p>';

}
unset($contact_form);
unset($contact_form_language_override);
unset($contact_form_url);
unset($fsc_form);
?>