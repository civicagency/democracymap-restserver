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

// the form is being processed to send the mail now

    // check all input variables
    $cid = $this->ctf_clean_input($_POST['fsc_CID']);
    if(empty($cid)) {
       $this->fsc_error = 1;
       $fsc_error_contact = ($fsc_opt['error_contact_select'] != '') ? $fsc_opt['error_contact_select'] : _('Selecting a contact is required.');
    }
    else if (!isset($contacts[$cid]['CONTACT'])) {
        $this->fsc_error = 1;
        $fsc_error_contact = _('Requested Contact not found.');
    }
    if (empty($ctf_contacts)) {
       $this->fsc_error = 1;
    }
    $mail_to    = ( isset($contacts[$cid]['EMAIL']) )   ? $this->ctf_clean_input($contacts[$cid]['EMAIL'])  : '';
    $to_contact = ( isset($contacts[$cid]['CONTACT']) ) ? $this->ctf_clean_input($contacts[$cid]['CONTACT']): '';

    if ($fsc_opt['name_type'] != 'not_available') {
        switch ($fsc_opt['name_format']) {
          case 'name':
             if (isset($_POST['fsc_name']))
               $name = $this->ctf_name_case($this->ctf_clean_input($_POST['fsc_name']));
          break;
          case 'first_last':
             if (isset($_POST['fsc_f_name']))
               $f_name = $this->ctf_name_case($this->ctf_clean_input($_POST['fsc_f_name']));
             if (isset($_POST['fsc_l_name']))
               $l_name = $this->ctf_name_case($this->ctf_clean_input($_POST['fsc_l_name']));
          break;
          case 'first_middle_i_last':
             if (isset($_POST['fsc_f_name']))
               $f_name = $this->ctf_name_case($this->ctf_clean_input($_POST['fsc_f_name']));
             if (isset($_POST['fsc_mi_name']))
               $mi_name = $this->ctf_name_case($this->ctf_clean_input($_POST['fsc_mi_name']));
             if (isset($_POST['fsc_l_name']))
               $l_name = $this->ctf_name_case($this->ctf_clean_input($_POST['fsc_l_name']));
          break;
          case 'first_middle_last':
             if (isset($_POST['fsc_f_name']))
               $f_name = $this->ctf_name_case($this->ctf_clean_input($_POST['fsc_f_name']));
             if (isset($_POST['fsc_m_name']))
               $m_name = $this->ctf_name_case($this->ctf_clean_input($_POST['fsc_m_name']));
             if (isset($_POST['fsc_l_name']))
               $l_name = $this->ctf_name_case($this->ctf_clean_input($_POST['fsc_l_name']));
         break;
      }
    }
    if ($fsc_opt['email_type'] != 'not_available') {
       if (isset($_POST['fsc_email']))
         $email = strtolower($this->ctf_clean_input($_POST['fsc_email']));
       if ($ctf_enable_double_email == 'true') {
         if (isset($_POST['fsc_email2']))
          $email2 = strtolower($this->ctf_clean_input($_POST['fsc_email2']));
       }
    }

    if ($fsc_opt['subject_type'] != 'not_available') {
        if(isset($_POST['fsc_subject'])) {
            // posted subject text input
            $subject = $this->ctf_name_case($this->ctf_clean_input($_POST['fsc_subject']));
        }else{
            // posted subject select input
            $sid = $this->ctf_clean_input($_POST['fsc_subject_ID']);
            if(empty($sid) && $fsc_opt['subject_type'] == 'required') {
               $this->fsc_error = 1;
               $fsc_error_subject = ($fsc_opt['error_subject'] != '') ? $fsc_opt['error_subject'] : _('Selecting a subject is required.');
            }
            else if (empty($subjects) || !isset($subjects[$sid])) {
               $this->fsc_error = 1;
               $fsc_error_subject = _('Requested subject not found.');
            } else {
               $subject = $this->ctf_clean_input($subjects[$sid]);
            }
       }
    }

    if ($fsc_opt['message_type'] != 'not_available') {
       if (isset($_POST['fsc_message'])) {
         if ($fsc_opt['preserve_space_enable'] == 'true')
           $message = $this->ctf_clean_input($_POST['fsc_message'],1);
         else
           $message = $this->ctf_clean_input($_POST['fsc_message']);
       }
    }
    if ( $this->is_captcha_enabled() )
        $captcha_code = $this->ctf_clean_input($_POST['fsc_captcha_code']);

    // check posted input for email injection attempts
    // fights common spammer tactics
    // look for newline injections
    $this->ctf_forbidifnewlines($name);
    $this->ctf_forbidifnewlines($email);
    if ($ctf_enable_double_email == 'true')
        $this->ctf_forbidifnewlines($email2);

    $this->ctf_forbidifnewlines($subject);

    // look for lots of other injections
    $forbidden = 0;
    $forbidden = $this->ctf_spamcheckpost();
    if ($forbidden)
       die("$forbidden");

   // check for banned ip
   if( $ctf_enable_ip_bans && in_array($_SERVER['REMOTE_ADDR'], $ctf_banned_ips) )
      die(_('Your IP is Banned'));

   // CAPS Decapitator
   if ($fsc_opt['name_case_enable'] == 'true' && !preg_match("/[a-z]/", $message))
      $message = $this->ctf_name_case($message);

    switch ($fsc_opt['name_format']) {
       case 'name':
        if($name == '' && $fsc_opt['name_type'] == 'required') {
          $this->fsc_error = 1;
          $fsc_error_name =  ($fsc_opt['error_name'] != '') ? $fsc_opt['error_name'] : _('Your name is required.');
        }
      break;
      default:
        if(empty($f_name) && $fsc_opt['name_type'] == 'required') {
          $this->fsc_error = 1;
          $fsc_error_f_name =  ($fsc_opt['error_name'] != '') ? $fsc_opt['error_name'] : _('Your name is required.');
        }
        if(empty($l_name) && $fsc_opt['name_type'] == 'required') {
          $this->fsc_error = 1;
          $fsc_error_l_name =  ($fsc_opt['error_name'] != '') ? $fsc_opt['error_name'] : _('Your name is required.');
        }
    }

   if(!empty($f_name)) $name .= $f_name;
   if(!empty($mi_name))$name .= ' '.$mi_name;
   if(!empty($m_name)) $name .= ' '.$m_name;
   if(!empty($l_name)) $name .= ' '.$l_name;

   if($fsc_opt['email_type'] == 'required') {
     if (!$this->ctf_validate_email($email)) {
         $this->fsc_error = 1;
         $fsc_error_email = ($fsc_opt['error_email'] != '') ? $fsc_opt['error_email'] : _('A proper e-mail address is required.');
     }
     if ($ctf_enable_double_email == 'true' && !$this->ctf_validate_email($email2)) {
         $this->fsc_error = 1;
         $fsc_error_email2 = ($fsc_opt['error_email'] != '') ? $fsc_opt['error_email'] : _('A proper e-mail address is required.');
     }
     if ($ctf_enable_double_email == 'true' && ($email != $email2)) {
         $this->fsc_error = 1;
         $fsc_error_double_email = ($fsc_opt['error_email2'] != '') ? $fsc_opt['error_email2'] : _('The two e-mail addresses did not match, please enter again.');
     }
   }

// check attachment directory
$attach_dir_error = 0;
if ($have_attach){
	$attach_dir = $this->site_path . '/attachments/';
	if ( !is_dir($attach_dir) ) {
        $this->fsc_error = 1;
		$attach_dir_error = sprintf( _( 'This contact form has file attachment fields, but the temporary folder for the files (%s) does not exist or is not writable. Create the folder or change its permission manually.' ), $attach_dir );
	} else if(!is_writable($attach_dir)) {
        $this->fsc_error = 1;
		$attach_dir_error = sprintf( _( 'This contact form has file attachment fields, but the temporary folder for the files (%s) is not writable. Fix the permissions.' ), $attach_dir );
    } else {
       // delete files over 3 minutes old in the attachment directory
       $this->clean_temp_dir($attach_dir, 3);
	}
}

   // optional extra fields
      for ($i = 1; $i <= $fsc_opt['max_fields']; $i++) {
        if ($fsc_opt['ex_field'.$i.'_label'] != '' && $fsc_opt['ex_field'.$i.'_type'] != 'fieldset-close') {
          if ($fsc_opt['ex_field'.$i.'_type'] == 'fieldset') {

          }else if ($fsc_opt['ex_field'.$i.'_type'] == 'date') {

$cal_date_array = array(
'mm/dd/yyyy' => $this->ctf_output_string(_('mm/dd/yyyy')),
'dd/mm/yyyy' => $this->ctf_output_string(_('dd/mm/yyyy')),
'mm-dd-yyyy' => $this->ctf_output_string(_('mm-dd-yyyy')),
'dd-mm-yyyy' => $this->ctf_output_string(_('dd-mm-yyyy')),
'mm.dd.yyyy' => $this->ctf_output_string(_('mm.dd.yyyy')),
'dd.mm.yyyy' => $this->ctf_output_string(_('dd.mm.yyyy')),
'yyyy/mm/dd' => $this->ctf_output_string(_('yyyy/mm/dd')),
'yyyy-mm-dd' => $this->ctf_output_string(_('yyyy-mm-dd')),
'yyyy.mm.dd' => $this->ctf_output_string(_('yyyy.mm.dd')),
);
               // required validate
               ${'ex_field'.$i} = ( !isset($_POST["fsc_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["fsc_ex_field$i"]);
               if( (${'ex_field'.$i} == '' || ${'ex_field'.$i} == $cal_date_array[$fsc_opt['date_format']]) && $fsc_opt['ex_field'.$i.'_req'] == 'true') {
                  $this->fsc_error = 1;
                  ${'fsc_error_ex_field'.$i} = ($fsc_opt['error_field'] != '') ? $fsc_opt['error_field'] : _('This field is required.');
               }
               // max_len validate
               if( ${'ex_field'.$i} != '' && $fsc_opt['ex_field'.$i.'_max_len'] != '' && strlen(${'ex_field'.$i}) > $fsc_opt['ex_field'.$i.'_max_len']) {
                  $this->fsc_error = 1;
                  ${'fsc_error_ex_field'.$i} = sprintf( _('Maximum of %d characters exceeded.'), $fsc_opt['ex_field'.$i.'_max_len'] );
               }
               // regex validate
               if( ${'ex_field'.$i} != '' && $fsc_opt['ex_field'.$i.'_regex'] != '' && !preg_match($fsc_opt['ex_field'.$i.'_regex'],${'ex_field'.$i}) ) {
                  $this->fsc_error = 1;
                  ${'fsc_error_ex_field'.$i} = ($fsc_opt['ex_field'.$i.'_regex_error'] != '') ? $fsc_opt['ex_field'.$i.'_regex_error'] : _('Invalid input.');
               }

          }else if ($fsc_opt['ex_field'.$i.'_type'] == 'hidden') {
               ${'ex_field'.$i} = ( !isset($_POST["fsc_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["fsc_ex_field$i"]);
          }else if ($fsc_opt['ex_field'.$i.'_type'] == 'time') {
               ${'ex_field'.$i.'h'}  = $this->ctf_clean_input($_POST["fsc_ex_field".$i."h"]);
               ${'ex_field'.$i.'m'}  = $this->ctf_clean_input($_POST["fsc_ex_field".$i."m"]);
               if ($fsc_opt['time_format'] == '12')
                  ${'ex_field'.$i.'ap'} = $this->ctf_clean_input($_POST["fsc_ex_field".$i."ap"]);
          }else if ($fsc_opt['ex_field'.$i.'_type'] == 'attachment') {
              // need to test if a file was selected for attach.
              $ex_field_file['name'] = '';
              if(isset($_FILES["fsc_ex_field$i"]))
                  $ex_field_file = $_FILES["fsc_ex_field$i"];
              if ($ex_field_file['name'] == '' && $fsc_opt['ex_field'.$i.'_req'] == 'true') {
                   $this->fsc_error = 1;
                   ${'fsc_error_ex_field'.$i} = ($fsc_opt['error_field'] != '') ? $fsc_opt['error_field'] : _('This field is required.');
              }
              if($ex_field_file['name'] != ''){  // may not be required
                 // validate the attachment now
                 $ex_field_file_check = $this->validate_attach( $this->site_path . '/attachments/', $ex_field_file, "ex_field$i"  );
                 if (!$ex_field_file_check['valid']) {
                     $this->fsc_error = 1;
                     ${'fsc_error_ex_field'.$i} = $ex_field_file_check['error'];
                 } else {
                    ${'ex_field'.$i} = $ex_field_file_check['file_name'];  // needed for email message
                 }
              }
              unset($ex_field_file);
          }else if ($fsc_opt['ex_field'.$i.'_type'] == 'checkbox' || $fsc_opt['ex_field'.$i.'_type'] == 'checkbox-multiple') {
             // see if checkbox children
             $exf_opts_array = array();
             $exf_opts_label = '';
             $exf_array_test = trim($fsc_opt['ex_field'.$i.'_label'] );
             if(preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
                  list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
                  $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
                  $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
                  if ($exf_opts_label != '' && $value != '') {
                     if(!preg_match("/;/", $value)) {
                        $this->fsc_error = 1;
                        ${'fsc_error_ex_field'.$i} = _('Error: A checkbox field is not configured properly in settings.');
                     } else {
                        // multiple options
                         $exf_opts_array = explode(";",$value);
                     }
                     // required check (only 1 has to be checked to meet required)
                    $ex_cnt = 1;
                    $ex_reqd = 0;
                    foreach ($exf_opts_array as $k) {
                      if( ! empty($_POST["fsc_ex_field$i".'_'.$ex_cnt]) ){
                        ${'ex_field'.$i.'_'.$ex_cnt} = $this->ctf_clean_input($_POST["fsc_ex_field$i".'_'.$ex_cnt]);
                        $ex_reqd++;
                      }
                      $ex_cnt++;
                    }
                    if(!$ex_reqd && $fsc_opt['ex_field'.$i.'_req'] == 'true') {
                        $this->fsc_error = 1;
                        ${'fsc_error_ex_field'.$i} = ($fsc_opt['error_field'] != '') ? $fsc_opt['error_field'] : _('At least one item in this field is required.');
                     }
                }
             }else{
                ${'ex_field'.$i} = ( !isset($_POST["fsc_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["fsc_ex_field$i"]);
                if(${'ex_field'.$i} == '' && $fsc_opt['ex_field'.$i.'_req'] == 'true') {
                    $this->fsc_error = 1;
                    ${'fsc_error_ex_field'.$i} = ($fsc_opt['error_field'] != '') ? $fsc_opt['error_field'] : _('This field is required.');
                }
             }
           }else if ($fsc_opt['ex_field'.$i.'_type'] == 'select-multiple') {
             $exf_opts_array = array();
             $exf_opts_label = '';
             $exf_array_test = trim($fsc_opt['ex_field'.$i.'_label'] );
             if(preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
                  list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
                  $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
                  $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
                  if ($exf_opts_label != '' && $value != '') {
                     if(!preg_match("/;/", $value)) {
                        $this->fsc_error = 1;
                        ${'fsc_error_ex_field'.$i} = _('Error: A select-multiple field is not configured properly in settings.');
                     } else {
                        // multiple options
                         $exf_opts_array = explode(";",$value);
                     }
                     // required check (only 1 has to be checked to meet required)
                     $ex_reqd = 0;
                     ${'ex_field'.$i} = ( !isset($_POST["fsc_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["fsc_ex_field$i"]);
                     if (is_array(${'ex_field'.$i}) && !empty(${'ex_field'.$i}) ) {
                       // loop
                       foreach ($exf_opts_array as $k) {  // checkbox multi
                          if (in_array($k, ${'ex_field'.$i} ) ) {
                             $ex_reqd++;
                          }
                       }
                     }
                     if((!$ex_reqd || empty(${'ex_field'.$i})) && $fsc_opt['ex_field'.$i.'_req'] == 'true') {
                        $this->fsc_error = 1;
                        ${'fsc_error_ex_field'.$i} = ($fsc_opt['error_field'] != '') ? $fsc_opt['error_field'] : _('At least one item in this field is required.');
                     }
                }
             } else {
                  $this->fsc_error = 1;
                  ${'fsc_error_ex_field'.$i} = _('Error: A checkbox-multiple field is not configured properly in settings.');
             }
           }else if ($fsc_opt['ex_field'.$i.'_type'] == 'email') {
                  ${'ex_field'.$i} = ( !isset($_POST["fsc_ex_field$i"]) ) ? '' : strtolower($this->ctf_clean_input($_POST["fsc_ex_field$i"]));
                  // required validate
                  if(${'ex_field'.$i} == '' && $fsc_opt['ex_field'.$i.'_req'] == 'true') {
                    $this->fsc_error = 1;
                    ${'fsc_error_ex_field'.$i} = ($fsc_opt['error_field'] != '') ? $fsc_opt['error_field'] : _('This field is required.');
                  }
                  // max_len validate
                  if( ${'ex_field'.$i} != '' && $fsc_opt['ex_field'.$i.'_max_len'] != '' && strlen(${'ex_field'.$i}) > $fsc_opt['ex_field'.$i.'_max_len']) {
                    $this->fsc_error = 1;
                    ${'fsc_error_ex_field'.$i} = sprintf( _('Maximum of %d characters exceeded.'), $fsc_opt['ex_field'.$i.'_max_len'] );
                  }
                  // regex validate
                  if (${'ex_field'.$i} != '' && !$this->ctf_validate_email(${'ex_field'.$i})) {
                    $this->fsc_error = 1;
                    ${'fsc_error_ex_field'.$i} = _('Invalid e-mail address.');
                  }
           }else if ($fsc_opt['ex_field'.$i.'_type'] == 'url') {
                  ${'ex_field'.$i} = ( !isset($_POST["fsc_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["fsc_ex_field$i"]);
                  // required validate
                  if(${'ex_field'.$i} == '' && $fsc_opt['ex_field'.$i.'_req'] == 'true') {
                    $this->fsc_error = 1;
                    ${'fsc_error_ex_field'.$i} = ($fsc_opt['error_field'] != '') ? $fsc_opt['error_field'] : _('This field is required.');
                  }
                  // max_len validate
                  if( ${'ex_field'.$i} != '' && $fsc_opt['ex_field'.$i.'_max_len'] != '' && strlen(${'ex_field'.$i}) > $fsc_opt['ex_field'.$i.'_max_len']) {
                    $this->fsc_error = 1;
                    ${'fsc_error_ex_field'.$i} = sprintf( _('Maximum of %d characters exceeded.'), $fsc_opt['ex_field'.$i.'_max_len'] );
                  }
                  // regex validate
                  if (${'ex_field'.$i} != '' && !$this->ctf_validate_url(${'ex_field'.$i})) {
                    $this->fsc_error = 1;
                    ${'fsc_error_ex_field'.$i} = _('Invalid URL.');
                  }
           }else{
                // text, textarea, radio, select, password
                if ($fsc_opt['ex_field'.$i.'_type'] == 'textarea' && $fsc_opt['textarea_html_allow'] == 'true') {
                      ${'ex_field'.$i} = ( !isset($_POST["fsc_ex_field$i"]) ) ? '' : $_POST["fsc_ex_field$i"];
                }else{
                     ${'ex_field'.$i} = ( !isset($_POST["fsc_ex_field$i"]) ) ? '' : $this->ctf_clean_input($_POST["fsc_ex_field$i"]);
                }
                // required validate
                if(${'ex_field'.$i} == '' && $fsc_opt['ex_field'.$i.'_req'] == 'true') {
                  $this->fsc_error = 1;
                  ${'fsc_error_ex_field'.$i} = ($fsc_opt['error_field'] != '') ? $fsc_opt['error_field'] : _('This field is required.');
                }
                // max_len validate
                if( ${'ex_field'.$i} != '' && $fsc_opt['ex_field'.$i.'_max_len'] != '' && strlen(${'ex_field'.$i}) > $fsc_opt['ex_field'.$i.'_max_len']) {
                  $this->fsc_error = 1;
                  ${'fsc_error_ex_field'.$i} = sprintf( _('Maximum of %d characters exceeded.'), $fsc_opt['ex_field'.$i.'_max_len'] );
                }
                // regex validate
                if( ${'ex_field'.$i} != '' && $fsc_opt['ex_field'.$i.'_regex'] != '' && !preg_match($fsc_opt['ex_field'.$i.'_regex'],${'ex_field'.$i}) ) {
                  $this->fsc_error = 1;
                  ${'fsc_error_ex_field'.$i} = ($fsc_opt['ex_field'.$i.'_regex_error'] != '') ? $fsc_opt['ex_field'.$i.'_regex_error'] : _('Invalid input.');
                }
           }
        }  // end if label != ''
      } // end foreach

   if ($fsc_opt['subject_type'] == 'required' && $subject == '') {
       $this->fsc_error = 1;
       if (count($subjects) == 0) {
         $fsc_error_subject = ($fsc_opt['error_subject'] != '') ? $fsc_opt['error_subject'] : _('Subject text is required.');
       }
   }
   if($fsc_opt['message_type'] == 'required' &&  $message == '') {
       $this->fsc_error = 1;
       $fsc_error_message = ($fsc_opt['error_message'] != '') ? $fsc_opt['error_message'] : _('Message text is required.');
   }

  // begin captcha check if enabled
  // captcha is optional but recommended to prevent spam bots from spamming your contact form
  if ( $this->is_captcha_enabled() ) {

      //captcha without sessions
      if (empty($captcha_code) || $captcha_code == '') {
         $this->fsc_error = 1;
         $fsc_error_captcha = ($fsc_opt['error_captcha_blank'] != '') ? $fsc_opt['error_captcha_blank'] : _('Please complete the CAPTCHA.');
      }else if (!isset($_POST['si_code_ctf_'.$form_id_num]) || empty($_POST['si_code_ctf_'.$form_id_num])) {
         $this->fsc_error = 1;
         $fsc_error_captcha = _('Could not find CAPTCHA token.');
      }else{
         $prefix = 'xxxxxx';
         if ( isset($_POST['si_code_ctf_'.$form_id_num]) && is_string($_POST['si_code_ctf_'.$form_id_num]) && preg_match('/^[a-zA-Z0-9]{15,17}$/',$_POST['si_code_ctf_'.$form_id_num]) ){
           $prefix = $_POST['si_code_ctf_'.$form_id_num];
         }
         if ( is_readable( $this->captcha_path . '/temp/' . $prefix . '.php' ) ) {
			include $this->captcha_path . '/temp/' . $prefix . '.php';
			if ( 0 == strcasecmp( $captcha_code, $captcha_word ) ) {
              // captcha was matched
              @unlink ($this->captcha_path . '/temp/' . $prefix . '.php');

			} else {
              $this->fsc_error = 1;
              $fsc_error_captcha = ($fsc_opt['error_captcha_wrong'] != '') ? $fsc_opt['error_captcha_wrong'] : _('That CAPTCHA was incorrect.');
            }
	     } else {
           $this->fsc_error = 1;
           $fsc_error_captcha = _('Could not read CAPTCHA token file. Try again.');
	    }
	  }
 } // end if enable captcha

  if (!$this->fsc_error) {
     // ok to send the email, so prepare the email message
     $posted_data = array();
     // new lines should be (\n for UNIX, \r\n for Windows and \r for Mac)
     //$php_eol = ( strtoupper(substr(PHP_OS,0,3) == 'WIN') ) ? "\r\n" : "\n";
	 $php_eol = (!defined('PHP_EOL')) ? (($eol = strtolower(substr(PHP_OS, 0, 3))) == 'win') ? "\r\n" : (($eol == 'mac') ? "\r" : "\n") : PHP_EOL;
	 $php_eol = (!$php_eol) ? "\n" : $php_eol;

     if($subject != '') {
          $subj = $fsc_opt['email_subject'] ." $subject";
     }else{
          $subj = $fsc_opt['email_subject'];
     }
     $msg = $this->make_bold(_('To')).": $to_contact$php_eol$php_eol";
     if ($name != '' || $email != '')  {
        $msg .= $this->make_bold(_('From')).":$php_eol";
        switch ($fsc_opt['name_format']) {
          case 'name':
             if($name != '') {
              $msg .= "$name$php_eol";
              $posted_data['from_name'] = $name;
             }
          break;
          case 'first_last':
              $msg .= ($fsc_opt['title_fname'] != '') ? $fsc_opt['title_fname'] : _('First Name').':';
              $msg .= " $f_name$php_eol";
              $msg .= ($fsc_opt['title_lname'] != '') ? $fsc_opt['title_lname'] : _('Last Name').':';
              $msg .= " $l_name$php_eol";
              $posted_data['first_name'] = $f_name;
              $posted_data['last_name'] = $l_name;
          break;
          case 'first_middle_i_last':
              $msg .= ($fsc_opt['title_fname'] != '') ? $fsc_opt['title_fname'] : _('First Name').':';
              $msg .= " $f_name$php_eol";
              $posted_data['first_name'] = $f_name;
              if($mi_name != '') {
                 $msg .= ($fsc_opt['title_miname'] != '') ? $fsc_opt['title_miname'] : _('Middle Initial').':';
                 $msg .= " $mi_name$php_eol";
                 $posted_data['middle_initial'] = $mi_name;
              }
              $msg .= ($fsc_opt['title_lname'] != '') ? $fsc_opt['title_lname'] : _('Last Name').':';
              $msg .= " $l_name$php_eol";
              $posted_data['last_name'] = $l_name;
          break;
          case 'first_middle_last':
              $msg .= ($fsc_opt['title_fname'] != '') ? $fsc_opt['title_fname'] : _('First Name').':';
              $msg .= " $f_name$php_eol";
              $posted_data['first_name'] = $f_name;
              if($m_name != '') {
                 $msg .= ($fsc_opt['title_mname'] != '') ? $fsc_opt['title_mname'] : _('Middle Name').':';
                 $msg .= " $m_name$php_eol";
                 $posted_data['middle_name'] = $m_name;
              }
              $msg .= ($fsc_opt['title_lname'] != '') ? $fsc_opt['title_lname'] : _('Last Name').':';
              $msg .= " $l_name$php_eol";
              $posted_data['last_name'] = $l_name;
         break;
      }
      $msg .= "$email$php_eol$php_eol";
      $posted_data['from_email'] = $email;
   }

   if ($fsc_opt['ex_fields_after_msg'] == 'true' && $message != '') {
        $msg .= $this->make_bold(_('Message')).":$php_eol$message$php_eol$php_eol";
        $posted_data['message'] = $message;
   }

   // optional extra fields
   for ($i = 1; $i <= $fsc_opt['max_fields']; $i++) {
      if ( $fsc_opt['ex_field'.$i.'_label'] != '' && $fsc_opt['ex_field'.$i.'_type'] != 'fieldset-close') {
         if(preg_match('/^{inline}/',$fsc_opt['ex_field'.$i.'_label'])) {
            // remove the {inline} modifier tag from the label
            $fsc_opt['ex_field'.$i.'_label'] = str_replace('{inline}','',$fsc_opt['ex_field'.$i.'_label']);
         }
         if ($fsc_opt['ex_field'.$i.'_type'] == 'fieldset') {
             $msg .= $this->make_bold($fsc_opt['ex_field'.$i.'_label']).$php_eol;
         } else if ($fsc_opt['ex_field'.$i.'_type'] == 'hidden') {
             list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$fsc_opt['ex_field'.$i.'_label']); //string will be split by "," but "\," will be ignored
             $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
             $msg .= $this->make_bold($exf_opts_label)."$php_eol${'ex_field'.$i}".$php_eol.$php_eol;
             $posted_data["ex_field$i"] = ${'ex_field'.$i};
         } else if ($fsc_opt['ex_field'.$i.'_type'] == 'time') {
             if ($fsc_opt['time_format'] == '12')
               $concat_time = ${'ex_field'.$i.'h'}.':'.${'ex_field'.$i.'m'}.' '.${'ex_field'.$i.'ap'};
             else
               $concat_time = ${'ex_field'.$i.'h'}.':'.${'ex_field'.$i.'m'};
             $msg .= $this->make_bold($fsc_opt['ex_field'.$i.'_label']).$php_eol.$concat_time.$php_eol.$php_eol;
             $posted_data["ex_field$i"] = $concat_time;
         } else if ($fsc_opt['ex_field'.$i.'_type'] == 'attachment' && $fsc_opt['php_mailer_enable'] != 'php' && ${'ex_field'.$i} != '') {
             $msg .= $this->make_bold($fsc_opt['ex_field'.$i.'_label'])."$php_eol * "._('File is attached:')." ${'ex_field'.$i}".$php_eol.$php_eol;
             $posted_data["ex_field$i"] = _('File is attached:')." ${'ex_field'.$i}";
         } else if ($fsc_opt['ex_field'.$i.'_type'] == 'select' || $fsc_opt['ex_field'.$i.'_type'] == 'radio') {
             list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$fsc_opt['ex_field'.$i.'_label']); //string will be split by "," but "\," will be ignored
             $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
             $msg .= $this->make_bold($exf_opts_label)."$php_eol${'ex_field'.$i}".$php_eol.$php_eol;
             $posted_data["ex_field$i"] = ${'ex_field'.$i};
         } else if ($fsc_opt['ex_field'.$i.'_type'] == 'select-multiple') {
             $exf_opts_array = array();
             $exf_opts_label = '';
             $exf_array_test = trim($fsc_opt['ex_field'.$i.'_label'] );
             if(preg_match('#(?<!\\\)\,#', $exf_array_test) && preg_match("/;/", $exf_array_test) ) {
                list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
                $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
                $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
                if ($exf_opts_label != '' && $value != '') {
                    if(!preg_match("/;/", $value)) {
                       // error - a select-multiple field is not configured properly in settings.
                    } else {
                         // multiple options
                         $exf_opts_array = explode(";",$value);
                    }
                    $msg .= $this->make_bold($exf_opts_label).$php_eol;
                    $posted_data["ex_field$i"] = '';
                    if (is_array(${'ex_field'.$i}) && ${'ex_field'.$i} != '') {
                       // loop
                       $ex_cnt = 1;
                       foreach ($exf_opts_array as $k) {  // select-multiple
                          if (in_array($k, ${'ex_field'.$i} ) ) {
                             $msg .= ' * '.$k.$php_eol;
                             $posted_data["ex_field$i"] .= ' * '.$k;
                             $ex_cnt++;
                          }
                       }
                    }
                    $msg .= $php_eol;
                }
             }
         } else if ($fsc_opt['ex_field'.$i.'_type'] == 'checkbox' || $fsc_opt['ex_field'.$i.'_type'] == 'checkbox-multiple') {
             $exf_opts_array = array();
             $exf_opts_label = '';
             $exf_array_test = trim($fsc_opt['ex_field'.$i.'_label'] );
             if(preg_match('#(?<!\\\)\,#', $exf_array_test)  && preg_match("/;/", $exf_array_test) ) {
                list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
                $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
                $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
                if ($exf_opts_label != '' && $value != '') {
                    if(!preg_match("/;/", $value)) {
                       // error
                       //A checkbox field is not configured properly in settings.
                    } else {
                         // multiple options
                         $exf_opts_array = explode(";",$value);
                    }
                    $msg .= $this->make_bold($exf_opts_label).$php_eol;
                    $posted_data["ex_field$i"] = '';
                    // loop
                    $ex_cnt = 1;
                    foreach ($exf_opts_array as $k) {  // checkbox multi
                     if( isset(${'ex_field'.$i.'_'.$ex_cnt}) && ${'ex_field'.$i.'_'.$ex_cnt} == 'selected') {
                       $msg .= ' * '.$k.$php_eol;
                       $posted_data["ex_field$i"] .= ' * '.$k;
                     }
                     $ex_cnt++;
                    }
                    $msg .= $php_eol;
                }
             } else {  // checkbox single
                 if(${'ex_field'.$i} == 'selected') {
                   $fsc_opt['ex_field'.$i.'_label'] = trim(str_replace('\,',',',$fsc_opt['ex_field'.$i.'_label'])); // "\," changes to ","
                   $msg .= $this->make_bold($fsc_opt['ex_field'.$i.'_label'])."$php_eol * "._('selected').$php_eol.$php_eol;
                   $posted_data["ex_field$i"] = '* '._('selected');
                 }
             }
         } else {  // text, textarea, date, password, email, url
               if(${'ex_field'.$i} != ''){
                   if ($fsc_opt['ex_field'.$i.'_type'] == 'textarea' && $fsc_opt['textarea_html_allow'] == 'true') {
                        $msg .= $this->make_bold($fsc_opt['ex_field'.$i.'_label']).$php_eol.$this->ctf_stripslashes(${'ex_field'.$i}).$php_eol.$php_eol;
                        $posted_data["ex_field$i"] = ${'ex_field'.$i};
                   }else{
                        $msg .= $this->make_bold($fsc_opt['ex_field'.$i.'_label']).$php_eol.${'ex_field'.$i}.$php_eol.$php_eol;
                        $posted_data["ex_field$i"] = ${'ex_field'.$i};
                        if ($fsc_opt['ex_field'.$i.'_type'] == 'email' && $email == '' && $fsc_opt['email_type'] == 'not_available') {
                          // admin set the standard email field 'not_avaulable' then added an email extra field type.
                          // lets capture that as the 'from_email'.
                           $email = ${'ex_field'.$i};
                           $this->ctf_forbidifnewlines($email);
                           $posted_data['from_email'] = $email;
                       }
                   }
               }
         }
       }
    } // end for
    if ($fsc_opt['ex_fields_after_msg'] != 'true' && $message != '') {
        $msg .= $this->make_bold(_('Message')).":$php_eol$message$php_eol$php_eol";
        $posted_data['message'] = $message;
    }

   // subject can include posted data names feature:
   foreach ($posted_data as $key => $data) {
      if( in_array($key,array('message','full_message','akismet')) )  // disallow these
            continue;
      if( is_string($data) )
          $subj = str_replace('['.$key.']',$data,$subj);
   }
   $subj = preg_replace('/(\[ex_field)(\d+)(\])/','',$subj); // remove empty ex_field tags
   $posted_form_name = ( $fsc_opt['form_name'] != '' ) ? $fsc_opt['form_name'] : sprintf(_('Form: %d'),$form_id_num);
   $subj = str_replace('[form_label]',$posted_form_name,$subj);
   $posted_data['subject'] = $subj;

  // lookup country info for this ip
  // geoip lookup using Visitor Maps plugin
  $geo_loc = '';
  if(
    file_exists( './whos-online/include-whos-online-geoip.php') &&
    file_exists( './whos-online/GeoLiteCity.dat') ) {
   require_once( './whos-online/include-whos-online-geoip.php');
   $gi = geoip_open( './whos-online/GeoLiteCity.dat', GEOIP_STANDARD);
    $record = geoip_record_by_addr($gi, $_SERVER['REMOTE_ADDR']);
   geoip_close($gi);
   $li = array();
   $li['city_name']    = (isset($record->city)) ? $record->city : '';
   $li['state_name']   = (isset($record->country_code) && isset($record->region)) ? $GEOIP_REGION_NAME[$record->country_code][$record->region] : '';
   $li['state_code']   = (isset($record->region)) ? strtoupper($record->region) : '';
   $li['country_name'] = (isset($record->country_name)) ? $record->country_name : '--';
   $li['country_code'] = (isset($record->country_code)) ? strtoupper($record->country_code) : '--';
   $li['latitude']     = (isset($record->latitude)) ? $record->latitude : '0';
   $li['longitude']    = (isset($record->longitude)) ? $record->longitude : '0';
   if ($li['city_name'] != '') {
     if ($li['country_code'] == 'US') {
         $geo_loc = $li['city_name'];
         if ($li['state_code'] != '')
            $geo_loc = $li['city_name'] . ', ' . strtoupper($li['state_code']);
     } else {      // all non us countries
             $geo_loc = $li['city_name'] . ', ' . strtoupper($li['country_code']);
     }
   } else {
     $geo_loc = '~ ' . $li['country_name'];
   }
 }

    // add some info about sender to the email message
    $userdomain = '';
    $userdomain = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $user_info_string = '';
    $user_info_string .= _('Sent from (ip address)').': '.$_SERVER['REMOTE_ADDR']." ($userdomain)".$php_eol;
    if ( $geo_loc != '' ) {
      $user_info_string .= _('Location').': '.$geo_loc. $php_eol;
      $posted_data['sender_location'] = _('Location').': '.$geo_loc;
    }
    $user_info_string .= _('Date/Time').': '.date("F j, Y, g:i a T") . $php_eol;
    $user_info_string .= _('Coming from (referer)').': '.$this->form_action_url . $php_eol;
    $user_info_string .= _('Using (user agent)').': '.$this->ctf_clean_input($_SERVER['HTTP_USER_AGENT']) . $php_eol.$php_eol;
    if ($fsc_opt['sender_info_enable'] == 'true')
       $msg .= $user_info_string;

    $posted_data['date_time'] = date("F j, Y, g:i a T");

   // Check with Akismet, but only if Akismet is enabled, and has a api key. (spam prevention).
  if( $fsc_opt['akismet_disable'] == 'false' ) { // each form disable feature
   if( $fsc_gb['akismet_enable'] == 'true' && $fsc_gb['akismet_api_key'] != '' ){
       require_once $this->site_path . '/Akismet.class.php';
       $akismet = new Akismet( str_replace('/contact-files','',$fsc_site['site_url']), $fsc_gb['akismet_api_key'] );
       if( isset($name) ) $akismet->setCommentAuthor($name);
       // $akismet->setCommentAuthor('viagra-test-123'); // uncomment this to test spam detection
      // or  You can just put viagra-test-123 as the name when testing the form (no need to edit this php file to test it)
       if( isset($email) ) $akismet->setCommentAuthorEmail($email);
       $akismet->setCommentContent($msg);
       $akismet->setPermalink($this->form_action_url);
       $akismet->setCommentType('fscontactform');
       if($akismet->isCommentSpam()) {
         if( $fsc_opt['akismet_send_anyway'] == 'false' ) {
              $this->fsc_error = 1; // Akismet says it is spam.
              $fsc_error_message = ($fsc_opt['error_input'] != '') ? $fsc_opt['error_input'] : _('Invalid Input - Spam?');
         }else{
              // Akismet says it is spam. flag the subject as spam and send anyway.
              $subj = _('Akismet: Spam'). ' - ' . $subj;
              $msg = str_replace(_('Sent from (ip address)'),_('Akismet Spam Check: probably spam').$php_eol._('Sent from (ip address)'),$msg);
              $posted_data['akismet'] = _('probably spam');
         }
       } else {
            $msg = str_replace(_('Sent from (ip address)'),_('Akismet Spam Check: passed').$php_eol._('Sent from (ip address)'),$msg);
            $posted_data['akismet'] = _('passed');
       }
    } // end akismet
   }
   $posted_data['full_message'] = $msg;

   if ($fsc_opt['email_html'] == 'true') {
     $msg = str_replace(array("\r\n", "\r", "\n"), "<br>", $msg);
     $msg = '<html><body>' . $php_eol . $msg . '</body></html>'.$php_eol;
   }

     // wordwrap email message
    if ($ctf_wrap_message)
       $msg = wordwrap($msg, 70,$php_eol);

  $email_off = 0;
  if ($fsc_opt['redirect_enable'] == 'true' && $fsc_opt['redirect_query'] == 'true' && $fsc_opt['redirect_email_off'] == 'true')
    $email_off = 1;

  if ($fsc_opt['silent_send'] != 'off' &&  $fsc_opt['silent_email_off'] == 'true')
    $email_off = 1;

  if (!$this->fsc_error) {

   if (!$email_off) {

     // ok to send the email, so prepare the email message
    $header = '';// for php mail
    $ctf_email_on_this_domain = $fsc_opt['email_from']; // optional
    // prepare the email header
    $this->fsc_from_name  = ($name == '') ? _('Contact Form') : $name;
    $this->fsc_from_email = ($email == '') ? $fsc_site['admin_email'] : $email;

    if($email != '')
      $this->fsc_from_email = $email;
        if ($ctf_email_on_this_domain != '' ) {
         if(!preg_match("/,/", $ctf_email_on_this_domain)) {
           // just an email: user1@example.com
           $this->fsc_mail_sender = $ctf_email_on_this_domain;
           if($email == '' || $fsc_opt['email_from_enforced'] == 'true')
              $this->fsc_from_email = $ctf_email_on_this_domain;
         } else {
           // name and email: webmaster,user1@example.com
           list($key, $value) = explode(",",$ctf_email_on_this_domain);
           $key   = trim($key);
           $value = trim($value);
           $this->fsc_mail_sender = $value;
           if($name == '')
             $this->fsc_from_name = $key;
           if($email == '' || $fsc_opt['email_from_enforced'] == 'true')
             $this->fsc_from_email = $value;
         }
    }
    $header_php =  "From: $this->fsc_from_name <$this->fsc_from_email>\n"; // header for php mail only

    // process $mail_to user1@example.com,[cc]user2@example.com,[cc]user3@example.com,[bcc]user4@example.com,[bcc]user5@example.com
    // some are cc, some are bcc
    $mail_to_arr = explode( ',', $mail_to );
    $mail_to = trim($mail_to_arr[0]);
    unset($mail_to_arr[0]);
    $ctf_email_address_cc = '';
    if ($ctf_email_address_bcc != '')
            $ctf_email_address_bcc = $ctf_email_address_bcc. ',';
	foreach ( $mail_to_arr as $key => $this_mail_to ) {
	       if (preg_match("/\[bcc\]/i",$this_mail_to) )  {
                 $this_mail_to = str_replace('[bcc]','',$this_mail_to);
                 $ctf_email_address_bcc .= "$this_mail_to,";
           }else{
                 $this_mail_to = str_replace('[cc]','',$this_mail_to);
                 $ctf_email_address_cc .= "$this_mail_to,";
           }
    }
    if ($ctf_email_address_cc != '') {
            $ctf_email_address_cc = rtrim($ctf_email_address_cc, ',');
            $header .= "Cc: $ctf_email_address_cc\n"; // for php mail
    }
    if ($ctf_email_address_bcc != '') {
            $ctf_email_address_bcc = rtrim($ctf_email_address_bcc, ',');
            $header .= "Bcc: $ctf_email_address_bcc\n"; // for php mail
    }

    if ($fsc_opt['email_reply_to'] != '') { // custom reply_to
         $header .= "Reply-To: ".$fsc_opt['email_reply_to']."\n"; // for php mail and wp_mail
    }else if($email != '') {   // trying this: keep users reply to even when email_from_enforced
         $header .= "Reply-To: $email\n"; // for php mail
    }else {
         $header .= "Reply-To: $this->fsc_from_email\n"; // for php mail
    }

    if ($ctf_email_on_this_domain != '') {
      $header .= "X-Sender: $this->fsc_mail_sender\n";  // for php mail
      $header .= "Return-Path: $this->fsc_mail_sender\n";   // for php mail
    }

    if ($fsc_opt['email_html'] == 'true') {
            $header .= 'Content-type: text/html; charset='. $fsc_site['site_charset'] . $php_eol;
    } else {
            $header .= 'Content-type: text/plain; charset='. $fsc_site['site_charset'] . $php_eol;
    }

    @ini_set('sendmail_from', $this->fsc_from_email);

    // Check for safe mode
    $this->safe_mode = ((boolean)@ini_get('safe_mode') === false) ? 0 : 1;

    if ($fsc_opt['php_mailer_enable'] == 'php') {
      // sending with php mail
       $header_php .= $header;
      if ($ctf_email_on_this_domain != '' && !$this->safe_mode) {
          // Pass the Return-Path via sendmail's -f command.
          @mail($mail_to,$subj,$msg,$header_php, '-f '.$this->fsc_mail_sender);
      }else{
          // the fifth parameter is not allowed in safe mode
          @mail($mail_to,$subj,$msg,$header_php);
      }
    }else if ($fsc_opt['php_mailer_enable'] == 'phpmailer') {
           // sending with phpmailer
           require_once $this->site_path . '/phpmailer5/class.phpmailer.php';
           $phpmailer = new PHPMailer ();
           $phpmailer->From = $this->fsc_from_email;
           $phpmailer->FromName = $this->fsc_from_name;
           if ($fsc_opt['email_reply_to'] != '') { // custom reply_to
               $phpmailer->AddReplyTo($fsc_opt['email_reply_to']);
           }else if($email != '') {   // trying this: keep users reply to even when email_from_enforced
               $phpmailer->AddReplyTo($email);
           }else {
               $phpmailer->AddReplyTo($this->fsc_from_email);
           }
           $phpmailer_to = explode( ',', $mail_to );
	       foreach ( (array) $phpmailer_to as $phpmailer_thisto ) {
	    	  $phpmailer->AddAddress( trim( $phpmailer_thisto ) );
       	   }
           if ($ctf_email_address_cc != '') {
              $phpmailer_cc = explode( ',', $ctf_email_address_cc );
	          foreach ( (array) $phpmailer_cc as $phpmailer_thiscc ) {
	    	      $phpmailer->AddCc( trim( $phpmailer_thiscc ) );
       	      }
           }
           if ($ctf_email_address_bcc != '') {
              $phpmailer_bcc = explode( ',', $ctf_email_address_bcc );
	          foreach ( (array) $phpmailer_bcc as $phpmailer_thisbcc ) {
	    	      $phpmailer->AddBcc( trim( $phpmailer_thisbcc ) );
       	      }
           }
           $phpmailer->CharSet = $fsc_site['site_charset'];
           $phpmailer->Subject = $subj;
           $phpmailer->Body = $msg;
           if ( substr($fsc_site['language'], 0, 2) != '' ) {
             $phpmailer->SetLanguage(substr($fsc_site['language'], 0, 2), $this->site_path . '/phpmailer5/');
           }
           if ($fsc_opt['email_html'] == 'true')
             $phpmailer->IsHTML(true);

         if ($fsc_opt['smtp_enable'] == 'true') {
             $phpmailer->IsSMTP();  // Set to use SMTP
             $phpmailer->Host = $fsc_opt['smtp_host']; // smtp.gmail.com
             $phpmailer->SMTPSecure = $fsc_opt['smtp_encryption'];  // encryption: ssl or tls or ''
             $phpmailer->Port = $fsc_opt['smtp_port']; // 25 or 465, etc
             if ($fsc_opt['smtp_auth_enable'] == 'true') {
               $phpmailer->SMTPAuth = true; // if must have user : pass
               $phpmailer->Username = $fsc_opt['smtp_user'];
               $phpmailer->Password = $fsc_opt['smtp_pass'];
             }
         }else{
               $phpmailer->IsMail(); // Set to use PHP's mail()
         }

         // Set custom headers
         if ($ctf_email_on_this_domain != '') {
           // add Sender for Return-path
           $phpmailer->Sender = $this->fsc_mail_sender;
           $phpmailer->AddCustomHeader("X-Sender: $this->fsc_mail_sender");
         }
         if ( $this->uploaded_files ) {
		     foreach ( $this->uploaded_files as $path ) {
			     $phpmailer->AddAttachment($path);
		     }
      	}
        @$phpmailer->Send();
    }
   } // end if (!$email_off) {

   // autoresponder feature
   if ($fsc_opt['auto_respond_enable'] == 'true' && $email != '' && $fsc_opt['auto_respond_subject'] != '' && $fsc_opt['auto_respond_message'] != ''){
       $subj = $fsc_opt['auto_respond_subject'];
       $msg =  $fsc_opt['auto_respond_message'];

       // $posted_data is an array of the form name value pairs
       // autoresponder can include posted data, tags are set on form settings page
       foreach ($posted_data as $key => $data) {
          if( in_array($key,array('message','full_message','akismet')) )  // disallow these
            continue;
	       if( is_string($data) ) {
	         $subj = str_replace('['.$key.']',$data,$subj);
             $msg = str_replace('['.$key.']',$data,$msg);
           }
       }
       $subj = preg_replace('/(\[ex_field)(\d+)(\])/','',$subj); // remove empty ex_field tags
       $msg = preg_replace('/(\[ex_field)(\d+)(\])/','',$msg);   // remove empty ex_field tags
       $subj = str_replace('[form_label]',$posted_form_name,$subj);

       // wordwrap email message
       if ($ctf_wrap_message)
             $msg = wordwrap($msg, 70,$php_eol);

       $header = '';
       $header_php = '';
       $auto_respond_from_name  = $fsc_opt['auto_respond_from_name'];
	   $auto_respond_from_email = $fsc_opt['auto_respond_from_email'];
       $auto_respond_reply_to   = $fsc_opt['auto_respond_reply_to'];
       // prepare the email header

       $header_php =  "From: $auto_respond_from_name <". $auto_respond_from_email . ">\n";
       $this->fsc_from_name = $auto_respond_from_name;
       $this->fsc_from_email = $auto_respond_from_email;

       $header .= "Reply-To: $auto_respond_reply_to\n";   // for php mail
       $header .= "X-Sender: $this->fsc_from_email\n";  // for php mail
       $header .= "Return-Path: $this->fsc_from_email\n";  // for php mail
       if ($fsc_opt['auto_respond_html'] == 'true') {
               $header .= 'Content-type: text/html; charset='. $fsc_site['site_charset'] . $php_eol;
       } else {
               $header .= 'Content-type: text/plain; charset='. $fsc_site['site_charset'] . $php_eol;
       }

       @ini_set('sendmail_from' , $this->fsc_from_email);
       if ($fsc_opt['php_mailer_enable'] == 'php') {
            // autoresponder sending with php
            $header_php .= $header;
            if (!$this->safe_mode) {
              // Pass the Return-Path via sendmail's -f command.
              @mail($email,$subj,$msg,$header_php, '-f '.$this->fsc_from_email);
            } else {
              // the fifth parameter is not allowed in safe mode
              @mail($email,$subj,$msg,$header_php);
            }

       }else if ($fsc_opt['php_mailer_enable'] == 'phpmailer') {
           // autoresponder sending with phpmailer
           require_once $this->site_path . '/phpmailer5/class.phpmailer.php';
           $phpmailer = new PHPMailer ();
           $phpmailer->From = $this->fsc_from_email;
           $phpmailer->FromName = $this->fsc_from_name;
           $phpmailer->AddAddress($email);
           $phpmailer->AddReplyTo($auto_respond_reply_to);
           $phpmailer->CharSet = $fsc_site['site_charset'];
           $phpmailer->Subject = $subj;
           $phpmailer->Body = $msg;
           if ( substr($fsc_site['language'], 0, 2) != '' ) {
             $phpmailer->SetLanguage(substr($fsc_site['language'], 0, 2), $this->site_path . '/phpmailer5/');
           }
           if ($fsc_opt['auto_respond_html'] == 'true')
             $phpmailer->IsHTML(true);

         if ($fsc_opt['smtp_enable'] == 'true') {
             $phpmailer->IsSMTP();  // Set to use SMTP
             $phpmailer->Host = $fsc_opt['smtp_host']; // smtp.gmail.com
             $phpmailer->SMTPSecure = $fsc_opt['smtp_encryption'];  // encryption: ssl or tls or ''
             $phpmailer->Port = $fsc_opt['smtp_port']; // 25 or 465, etc
             if ($fsc_opt['smtp_auth_enable'] == 'true') {
               $phpmailer->SMTPAuth = true; // if must have user : pass
               $phpmailer->Username = $fsc_opt['smtp_user'];
               $phpmailer->Password = $fsc_opt['smtp_pass'];
             }
         }else{
               $phpmailer->IsMail(); // Set to use PHP's mail()
         }

         // Set custom headers
         if ($ctf_email_on_this_domain != '') {
           // add Sender for Return-path
           $phpmailer->Sender = $this->fsc_from_email;
           $phpmailer->AddCustomHeader("X-Sender: $this->fsc_from_email");
         }
         @$phpmailer->Send();
     }
  }

    $message_sent = 1;

    unset($_POST['fsc_action']);  //prevent form double posting
    unset($_POST['fsc_form_id']); //prevent form double posting

  if ($fsc_opt['silent_send'] == 'get' && $fsc_opt['silent_url'] != '' && function_exists('curl_init') ) {
     // build query string
     $query_string = $this->fsc_export_convert($posted_data,$fsc_opt['silent_rename'],$fsc_opt['silent_ignore'],$fsc_opt['silent_add'],'query');
     if(!preg_match("/\?/", $fsc_opt['silent_url']) )
        $ch = curl_init($fsc_opt['silent_url'].'?'.$query_string);
      else
        $ch = curl_init($fsc_opt['silent_url'].'&'.$query_string);
     curl_setopt($ch, CURLOPT_REFERER, $form_action_url);
     curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     $curl_result = curl_exec($ch);
     curl_close($ch);
     //echo $curl_result;
  }

  if ($fsc_opt['silent_send'] == 'post' && $fsc_opt['silent_url'] != '' && function_exists('curl_init') ) {
     // build post_array
     $post_array = $this->fsc_export_convert($posted_data,$fsc_opt['silent_rename'],$fsc_opt['silent_ignore'],$fsc_opt['silent_add'],'array');
     $ch = curl_init($fsc_opt['silent_url']);
     curl_setopt($ch, CURLOPT_POST, 1);
     curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array);
     curl_setopt($ch, CURLOPT_REFERER, $form_action_url);
     curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     $curl_result = curl_exec($ch);
     curl_close($ch);
     //echo $curl_result;
  }

    // hook for other plugins to use (just after mail sent)
    $fsctf_posted_data = (object) array('title' => sprintf(_('Form: %d'),$form_id_num), 'posted_data' => $posted_data, 'uploaded_files' => (array) $this->uploaded_files );


   } // end if ! error
  } // end if ! error

if ($have_attach){
  // unlink attachment temp files
  foreach ( (array) $this->uploaded_files as $path ) {
   @unlink( $path );
  }
}

?>