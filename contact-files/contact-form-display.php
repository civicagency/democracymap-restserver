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

// the form is being displayed now
 $this->ctf_notes_style = $this->fsc_convert_css($fsc_opt['notes_style']); 
 $this->ctf_form_style = $this->fsc_convert_css($fsc_opt['form_style']);
 $this->ctf_border_style = $this->fsc_convert_css($fsc_opt['border_style']);
 $this->ctf_select_style = $this->fsc_convert_css($fsc_opt['select_style']);
 $this->ctf_title_style = $this->fsc_convert_css($fsc_opt['title_style']);
 $this->ctf_field_style = $this->fsc_convert_css($fsc_opt['field_style']);
 $this->ctf_field_div_style = $this->fsc_convert_css($fsc_opt['field_div_style']);
 $this->ctf_error_style = $this->fsc_convert_css($fsc_opt['error_style']);
 $this->ctf_required_style = $this->fsc_convert_css($fsc_opt['required_style']);

 $ctf_field_size = $this->absint($fsc_opt['field_size']);

 $this->ctf_aria_required = ($fsc_opt['aria_required'] == 'true') ? ' aria-required="true" ' : '';

if ($this->fsc_error)
  $this->ctf_form_style = str_replace('display: none;','',$this->ctf_form_style);

$string .= '
<!-- Fast Secure Contact Form PHP plugin '.$this->fsc_version.' - begin - FastSecureContactForm.com -->
<div id="FSCForm'.$form_id_num.'" '.$this->ctf_form_style.'>';

if ($fsc_opt['border_enable'] == 'true') {
  $string .= '
    <form '.$have_attach.'action="'.$this->esc_url($this->form_action_url).'#FSCForm'.$form_id_num.'" id="fsc_form'.$form_id_num.'" method="post">
    <fieldset '.$this->ctf_border_style.'>
        <legend>';
     $string .= ($fsc_opt['title_border'] != '') ? $fsc_opt['title_border'] : _('Contact Form');
     $string .= '</legend>';
} else { 

 $string .= '
<form '.$have_attach.'action="'.$this->esc_url($this->form_action_url).'#FSCForm'.$form_id_num.'" id="fsc_form'.$form_id_num.'" method="post">
';
}

// check attachment directory
$attach_dir_error = 0;
if ($have_attach){
	$attach_dir = $this->site_path . '/attachments/';
    $this->init_temp_dir($attach_dir);
    if ($fsc_opt['php_mailer_enable'] == 'php'){
       $this->fsc_error = 1;
	   $attach_dir_error = _( 'This contact form has file attachment fields. Attachments are only supported when the Send E-Mail function is set to phpmailer. You can find this setting on the contact form settings page.' );
    }
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

// print any input errors
if ($this->fsc_error) {
    $string .= '<div '.$this->ctf_required_style.'>
    <div '.$this->ctf_error_style.'>'."\n";
    $string .= ($fsc_opt['error_correct'] != '') ? $fsc_opt['error_correct'] : _('Please make corrections below and try again.');
    $string .= '
    </div>
</div>'."\n";
    if($have_attach && $attach_dir_error) {
      $string .= '<div '.$this->ctf_required_style.'>
      <div '.$this->ctf_error_style.'>'."\n";
      $string .= $attach_dir_error;
      $string .= '
      </div>
</div>'."\n";
    }
}
if (empty($ctf_contacts)) {
   $string .= '<div '.$this->ctf_required_style.'>
   <div '.$this->ctf_error_style.'>'._('ERROR: Misconfigured E-mail address in options.').'
   </div>
</div>'."\n";
}

if ($fsc_opt['req_field_label_enable'] == 'true' && $fsc_opt['req_field_indicator_enable'] == 'true' ) {
   $string .=  '<div '.$this->ctf_required_style.'>'."\n";
   $string .= ($fsc_opt['tooltip_required'] != '') ? '<span class="required">'.$fsc_opt['req_field_indicator'].'</span>' .$fsc_opt['tooltip_required'] : '<span class="required">'.$fsc_opt['req_field_indicator'].'</span>' . _('(denotes required field)');
   $string .= '</div>
';
}

if (count($contacts) > 1) {

     $string .= '
        <div '.$this->ctf_title_style.'>
                <label for="fsc_CID'.$form_id_num.'">';
     $string .= ($fsc_opt['title_dept'] != '') ? $fsc_opt['title_dept'] : _('Department to Contact').':';
     $string .= $req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>
               '.$this->echo_if_error($fsc_error_contact).' 
                <select '.$this->ctf_select_style.' id="fsc_CID'.$form_id_num.'" name="fsc_CID" '.$this->ctf_aria_required.'>
';

    $string .= '                <option value="">';
    $string .= ($fsc_opt['title_select'] != '') ? $this->ctf_output_string($fsc_opt['title_select']) : $this->ctf_output_string( _('Select'));
    $string .= '</option>'."\n";

    if ( !isset($cid) && isset($_GET[$form_id_num .'mailto_id']) ) {
        $cid = (int)$this->fsc_get_var($form_id_num,'mailto_id');
    }else if ( !isset($cid) && isset($_GET['fsc_CID']) ){
        $cid = (int)$_GET['fsc_CID']; // legacy code
    }

     $selected = '';

      foreach ($contacts as $k => $v)  {
          if (!empty($cid) && $cid == $k) {
                    $selected = ' selected="selected"';
          }
          $string .= '                <option value="' . $this->ctf_output_string($k) . '"' . $selected . '>' . $this->ctf_output_string($v['CONTACT']) . '</option>' . "\n";
          $selected = '';
      }

      $string .= '                </select>
      </div>
';
}
else {

     $string .= '
        <div>
                <input type="hidden" name="fsc_CID" value="1" />
        </div>
';

}

if($fsc_opt['name_type'] != 'not_available' ) {

     $f_name_string = '
        <div '.$this->ctf_title_style.'>
                <label for="fsc_f_name'.$form_id_num.'">';
     $f_name_string .= ($fsc_opt['title_fname'] != '') ? $fsc_opt['title_fname'] : _('First Name').':';
     if($fsc_opt['name_type'] == 'required' )
           $f_name_string .= $req_field_ind;
     $f_name_string .= '</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error($fsc_error_f_name).'
                <input '.$this->ctf_field_style.' type="text" id="fsc_f_name'.$form_id_num.'" name="fsc_f_name" value="' . $this->ctf_output_string($f_name) .'" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>';

     $l_name_string = '
        <div '.$this->ctf_title_style.'>
                <label for="fsc_l_name'.$form_id_num.'">';
     $l_name_string .= ($fsc_opt['title_lname'] != '') ? $fsc_opt['title_lname'] : _('Last Name').':';;
     if($fsc_opt['name_type'] == 'required' )
           $l_name_string .= $req_field_ind;
     $l_name_string .= '</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error($fsc_error_l_name).'
                <input '.$this->ctf_field_style.' type="text" id="fsc_l_name'.$form_id_num.'" name="fsc_l_name" value="' . $this->ctf_output_string($l_name) .'" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>
';


    switch ($fsc_opt['name_format']) {
       case 'name':

$string .= '
        <div '.$this->ctf_title_style.'>
                <label for="fsc_name'.$form_id_num.'">';
     $string .= ($fsc_opt['title_name'] != '') ? $fsc_opt['title_name'] : _('Name').':';
     if($fsc_opt['name_type'] == 'required' )
           $string .= $req_field_ind;
     $string .= '</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error($fsc_error_name).'
                <input '.$this->ctf_field_style.' type="text" id="fsc_name'.$form_id_num.'" name="fsc_name" value="' . $this->ctf_output_string($name) .'" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>
';

      break;
      case 'first_last':

     $string .= $f_name_string;
     $string .= $l_name_string;

      break;
      case 'first_middle_i_last':

     $string .= $f_name_string;

$string .= '
        <div '.$this->ctf_title_style.'>
                <label for="fsc_mi_name'.$form_id_num.'">';
     $string .= ($fsc_opt['title_miname'] != '') ? $fsc_opt['title_miname'] : _('Middle Initial').':';
     $string .= '</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error($fsc_error_mi_name).'
                <input '.$this->ctf_field_style.' type="text" id="fsc_mi_name'.$form_id_num.'" name="fsc_mi_name" value="' . $this->ctf_output_string($mi_name) .'" '.$this->ctf_aria_required.' size="2" />
        </div>';

     $string .= $l_name_string;

      break;
      case 'first_middle_last':

     $string .= $f_name_string;

$string .= '
        <div '.$this->ctf_title_style.'>
                <label for="fsc_m_name'.$form_id_num.'">';
     $string .= ($fsc_opt['title_mname'] != '') ? $fsc_opt['title_mname'] : _('Middle Name').':';
     $string .= '</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error($fsc_error_m_name).'
                <input '.$this->ctf_field_style.' type="text" id="fsc_m_name'.$form_id_num.'" name="fsc_m_name" value="' . $this->ctf_output_string($m_name) .'" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>';

     $string .= $l_name_string;

      break;
    }
}
if($fsc_opt['email_type'] != 'not_available' ) {
 if ($ctf_enable_double_email == 'true') {
   $string .= '
        <div '.$this->ctf_title_style.'>
                <label for="fsc_email'.$form_id_num.'">';
     $string .= ($fsc_opt['title_email'] != '') ? $fsc_opt['title_email'] : _('E-Mail Address').':';
     if($fsc_opt['email_type'] == 'required' )
           $string .= $req_field_ind;
     $string .= '</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error($fsc_error_email).'
         '.$this->echo_if_error($fsc_error_double_email).'
                <input '.$this->ctf_field_style.' type="email" id="fsc_email'.$form_id_num.'" name="fsc_email" value="' . $this->ctf_output_string($email) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>
        <div '.$this->ctf_title_style.'>
                <label for="fsc_email2_'.$form_id_num.'">';
     $string .= ($fsc_opt['title_email2'] != '') ? $fsc_opt['title_email2'] : _('E-Mail Address again').':';
     $string .= $req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error($fsc_error_email2).'
                <span style="font-size:x-small; font-weight:normal;">';
     $string .= ($fsc_opt['title_email2_help'] != '') ? $fsc_opt['title_email2_help'] : _('Please enter your E-mail Address a second time.');
     $string .= '</span><br />
                <input '.$this->ctf_field_style.' type="email" id="fsc_email2_'.$form_id_num.'" name="fsc_email2" value="' . $this->ctf_output_string($email2) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>
 ';

  } else {
    $string .= '
        <div '.$this->ctf_title_style.'>
                <label for="fsc_email'.$form_id_num.'">';
     $string .= ($fsc_opt['title_email'] != '') ? $fsc_opt['title_email'] : _('E-Mail Address').':';
     if($fsc_opt['email_type'] == 'required' )
           $string .= $req_field_ind;
     $string .= '</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error($fsc_error_email).'
                <input '.$this->ctf_field_style.' type="email" id="fsc_email'.$form_id_num.'" name="fsc_email" value="' . $this->ctf_output_string($email) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />
        </div>
';
  }
}

if ($fsc_opt['ex_fields_after_msg'] != 'true') {
     // are there any optional extra fields/
     for ($i = 1; $i <= $fsc_opt['max_fields']; $i++) {
        if ($fsc_opt['ex_field'.$i.'_label'] != '') {
           // include the code to display extra fields
           require $this->site_path . '/contact-form-ex-fields.php';
           break;
        }
      }
}

if($fsc_opt['subject_type'] != 'not_available' ) {
   if (count($subjects) > 0) {

       $string .= '
        <div '.$this->ctf_title_style.'>
                <label for="fsc_subject_ID'.$form_id_num.'">';
     $string .= ($fsc_opt['title_subj'] != '') ? $fsc_opt['title_subj'] : _('Subject').':';
     if($fsc_opt['subject_type'] == 'required' )
           $string .= $req_field_ind;
     $string .= '</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error($fsc_error_subject).'
               <select '.$this->ctf_select_style.' id="fsc_subject_ID'.$form_id_num.'" name="fsc_subject_ID" '.$this->ctf_aria_required.'>
';

    $string .= '               <option value="">';
    $string .= ($fsc_opt['title_select'] != '') ? $this->ctf_output_string($fsc_opt['title_select']) : $this->ctf_output_string( _('Select'));
    $string .= '</option>'."\n";

    if ( !isset($sid) && isset($_GET[$form_id_num .'subject_id']) ) {
        $sid = (int)$this->fsc_get_var($form_id_num,'subject_id');
    }else if ( !isset($sid) && isset($_GET['fsc_SID']) ){
        $sid = (int)$_GET['fsc_SID']; // legacy code
    }

     $selected = '';

      foreach ($subjects as $k => $v)  {
          if (!empty($sid) && $sid == $k) {
                    $selected = ' selected="selected"';
          }
          $string .= '               <option value="' . $this->ctf_output_string($k) . '"' . $selected . '>' . $this->ctf_output_string($v) . '</option>' . "\n";
          $selected = '';
      }

      $string .= '               </select>';

       } else {
            // text entry subject
              if ( $subject != '' ) {
                $subject = substr($subject,0,75); // shorten to 75 chars or less
              }
            $string .= '
        <div '.$this->ctf_title_style.'>
                <label for="fsc_subject'.$form_id_num.'">';
     $string .= ($fsc_opt['title_subj'] != '') ? $fsc_opt['title_subj'] : _('Subject').':';
     if($fsc_opt['subject_type'] == 'required' )
           $string .= $req_field_ind;
     $string .= '</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error($fsc_error_subject).'
                 <input '.$this->ctf_field_style.' type="text" id="fsc_subject'.$form_id_num.'" name="fsc_subject" value="' . $this->ctf_output_string($subject) . '" '.$this->ctf_aria_required.' size="'.$ctf_field_size.'" />';
       }

        $string .= '
        </div>
';
}

if($fsc_opt['message_type'] != 'not_available' ) {
$string .= '
        <div '.$this->ctf_title_style.'>
                <label for="fsc_message'.$form_id_num.'">';
     $string .= ($fsc_opt['title_mess'] != '') ? $fsc_opt['title_mess'] : _('Message').':';
     if($fsc_opt['message_type'] == 'required' )
           $string .= $req_field_ind;
     $string .= '</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error($fsc_error_message).'
                <textarea '.$this->ctf_field_style.' id="fsc_message'.$form_id_num.'" name="fsc_message" '.$this->ctf_aria_required.' cols="'.$this->absint($fsc_opt['text_cols']).'" rows="'.$this->absint($fsc_opt['text_rows']).'">' . $this->ctf_output_string($message) . '</textarea>
        </div>
';
}

if ($fsc_opt['ex_fields_after_msg'] == 'true') {
     // are there any optional extra fields/
     for ($i = 1; $i <= $fsc_opt['max_fields']; $i++) {
        if ($fsc_opt['ex_field'.$i.'_label'] != '') {
           // include the code to display extra fields
           require $this->site_path . '/contact-form-ex-fields.php';
           break;
        }
      }
}

 $this->ctf_submit_div_style = $this->fsc_convert_css($fsc_opt['submit_div_style']);
 $this->ctf_submit_style = $this->fsc_convert_css($fsc_opt['button_style']);
 $this->ctf_reset_style = $this->fsc_convert_css($fsc_opt['reset_style']);
// captcha is optional but recommended to prevent spam bots from spamming your contact form

if ( $this->is_captcha_enabled() ) {
  $string .= $this->get_captcha_html($fsc_error_captcha,$form_id_num)."\n";
}

$string .= '
<div '.$this->ctf_submit_div_style.'>
  <input type="hidden" name="fsc_action" value="send" />
  <input type="hidden" name="fsc_form_id" value="'.$form_id_num.'" />
  <input type="submit" id="fsc-submit-'.$form_id_num.'" '.$this->ctf_submit_style.' value="';
     $string .= ($fsc_opt['title_submit'] != '') ? $this->ctf_output_string( $fsc_opt['title_submit'] ) : $this->ctf_output_string( _('Submit'));
     $string .= '" ';
   if($fsc_opt['enable_areyousure'] == 'true') {
     $string .= ' onclick="return confirm(\'';
     $string .= ($fsc_opt['title_areyousure'] != '') ? $this->ctf_output_string(addslashes( $fsc_opt['title_areyousure'] )) : $this->ctf_output_string( addslashes(_('Are you sure?')));
     $string .= '\')" ';
    }
     $string .= '/> ';
   if($fsc_opt['enable_reset'] == 'true') {
     $string .= '<input type="reset" id="fsc-reset-'.$form_id_num.'" '.$this->ctf_reset_style.' value="';
     $string .= ($fsc_opt['title_reset'] != '') ? $this->ctf_output_string( $fsc_opt['title_reset'] ) : $this->ctf_output_string( _('Reset'));
     $string .= '" onclick="return confirm(\'';
     $string .= addslashes(_('Do you really want to reset the form?'));
     $string .= '\')"  />'."\n";
    }
$string .= '</div>
';
if ($fsc_opt['border_enable'] == 'true') {
  $string .= '
    </fieldset>
  ';
}
$string .= '
</form>
</div>
';
if ($fsc_opt['enable_credit_link'] == 'true') {
  $this->ctf_powered_by_style = $this->fsc_convert_css($fsc_opt['powered_by_style']);
$string .= '
<p '.$this->ctf_powered_by_style.'>'._('Powered by'). ' <a href="http://www.FastSecureContactForm.com">'._('Fast Secure Contact Form - PHP'). '</a></p>
';
}
$string .= '<!-- Fast Secure Contact Form PHP plugin '.$this->fsc_version.' - end - FastSecureContactForm.com -->
';
?>