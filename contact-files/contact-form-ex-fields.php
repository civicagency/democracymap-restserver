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

// display extra fields on the contact form

      $ex_fieldset = 0;
      $printed_tooltip_filetypes = 0;
      $ex_loop_cnt = 1;
      for ($i = 1; $i <= $fsc_opt['max_fields']; $i++) {
        if ($fsc_opt['ex_field'.$i.'_label'] != '' || $fsc_opt['ex_field'.$i.'_type'] == 'fieldset-close') {
           $ex_req_field_ind = ($fsc_opt['ex_field'.$i.'_req'] == 'true') ? $req_field_ind : '';
           $ex_req_field_aria = ($fsc_opt['ex_field'.$i.'_req'] == 'true') ? $this->ctf_aria_required : '';
           if(!$fsc_opt['ex_field'.$i.'_type'] ) $fsc_opt['ex_field'.$i.'_type'] = 'text';
           if(!$fsc_opt['ex_field'.$i.'_default'] ) $fsc_opt['ex_field'.$i.'_default'] = '0';
           if(!isset($fsc_opt['ex_field'.$i.'_default_text'] )) $fsc_opt['ex_field'.$i.'_default_text'] = '';
           if(!$fsc_opt['ex_field'.$i.'_max_len'] ) $fsc_opt['ex_field'.$i.'_max_len'] = '';
           if(!$fsc_opt['ex_field'.$i.'_label_css'] ) $fsc_opt['ex_field'.$i.'_label_css'] = '';
           if(!$fsc_opt['ex_field'.$i.'_input_css'] ) $fsc_opt['ex_field'.$i.'_input_css'] = '';
           if(!$fsc_opt['ex_field'.$i.'_attributes'] ) $fsc_opt['ex_field'.$i.'_attributes'] = '';
           if(!$fsc_opt['ex_field'.$i.'_regex'] ) $fsc_opt['ex_field'.$i.'_regex'] = '';
           if(!$fsc_opt['ex_field'.$i.'_regex_error'] ) $fsc_opt['ex_field'.$i.'_regex_error'] = '';
           if(!$fsc_opt['ex_field'.$i.'_notes'] ) $fsc_opt['ex_field'.$i.'_notes'] = '';
           if(!$fsc_opt['ex_field'.$i.'_notes_after'] ) $fsc_opt['ex_field'.$i.'_notes_after'] = '';

          switch ($fsc_opt['ex_field'.$i.'_type']) {
           case 'fieldset':
                if($ex_fieldset)
                   $string .=   "</fieldset>\n";
                if($fsc_opt['ex_field'.$i.'_notes'] != '') {
                  $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
                }
                $string .=   '<fieldset '.$this->ctf_border_style.'>
        <legend>' . $fsc_opt['ex_field'.$i.'_label'] ."</legend>\n";
                $ex_fieldset = 1;
           break;

           case 'fieldset-close':
                if($ex_fieldset)
                   $string .=   "</fieldset>\n";
                $ex_fieldset = 0;
           break;
           case 'hidden':
           $exf_opts_label = ''; $value = '';
           if(preg_match("/,/", $fsc_opt['ex_field'.$i.'_label']) )
             list($exf_opts_label, $value) = explode(",",$fsc_opt['ex_field'.$i.'_label']);
           $exf_opts_label = trim($exf_opts_label); $value = trim($value);
           if ($exf_opts_label == '' || $value == '') {
               // error
               $this->fsc_error = 1;
               $string .= $this->echo_if_error(_('Error: A hidden field is not configured properly in settings.'));
            }
            if (${'ex_field'.$i} != '') // guery string can overrride
                 $value = ${'ex_field'.$i};
            $string .= '
                <input type="hidden" id="fsc_ex_field'.$form_id_num.'_'.$i.'" name="fsc_ex_field'.$i.'" value="' . $this->ctf_output_string($value) . '" />
';
           break;
           case 'password':
        if($fsc_opt['ex_field'.$i.'_notes'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
        }
                 $string .= '
        <div ';
         $string .= ($fsc_opt['ex_field'.$i.'_label_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_label_css']) : $this->ctf_title_style;
         $string .= '>
                <label for="fsc_ex_field'.$form_id_num.'_'.$i.'">' . $fsc_opt['ex_field'.$i.'_label'] . $ex_req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error(${'fsc_error_ex_field'.$i}).'
                <input '.$this->ctf_field_style.' type="password" id="fsc_ex_field'.$form_id_num.'_'.$i.'" name="fsc_ex_field'.$i.'" value="' . $this->ctf_output_string(${'ex_field'.$i}) . '" '.$ex_req_field_aria.' ';
                if($fsc_opt['ex_field'.$i.'_max_len'] != '')
                  $string .=  ' maxlength="'.$fsc_opt['ex_field'.$i.'_max_len'].'" ';
                if(strpos($fsc_opt['ex_field'.$i.'_attributes'],'size')===false)
                   $string .= 'size="'.$ctf_field_size.'"';
                if($fsc_opt['ex_field'.$i.'_attributes'] != '')
                  $string .= ' '.$fsc_opt['ex_field'.$i.'_attributes'];
                $string .= ' />
        </div>
';
        if($fsc_opt['ex_field'.$i.'_notes_after'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes_after']);
        }
              break;
           case 'text':
           case 'email':
           case 'url':
        if($fsc_opt['ex_field'.$i.'_notes'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
        }
                 $string .= '
        <div ';
         $string .= ($fsc_opt['ex_field'.$i.'_label_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_label_css']) : $this->ctf_title_style;
         $string .= '>
                <label for="fsc_ex_field'.$form_id_num.'_'.$i.'">' . $fsc_opt['ex_field'.$i.'_label'] . $ex_req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error(${'fsc_error_ex_field'.$i}).'
                <input ';
         $string .= ($fsc_opt['ex_field'.$i.'_input_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_input_css']) : $this->ctf_field_style;
         $string .= ' type="'.$fsc_opt['ex_field'.$i.'_type'].'" id="fsc_ex_field'.$form_id_num.'_'.$i.'" name="fsc_ex_field'.$i.'" value="';
              if($fsc_opt['ex_field'.$i.'_default_text'] != '' && ${'ex_field'.$i} == '')
                  $string .=  $this->ctf_output_string($fsc_opt['ex_field'.$i.'_default_text']);
              else
                 $string .=  $this->ctf_output_string(${'ex_field'.$i});

                 $string .= '" '.$ex_req_field_aria.' ';
                if($fsc_opt['ex_field'.$i.'_max_len'] != '')
                  $string .= ' maxlength="'.$fsc_opt['ex_field'.$i.'_max_len'].'" ';
                if(strpos($fsc_opt['ex_field'.$i.'_attributes'],'size')===false)
                   $string .= 'size="'.$ctf_field_size.'"';
                if($fsc_opt['ex_field'.$i.'_attributes'] != '')
                  $string .= ' '.$fsc_opt['ex_field'.$i.'_attributes'];
                $string .= ' />
        </div>
';
        if($fsc_opt['ex_field'.$i.'_notes_after'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes_after']);
        }
              break;
           case 'textarea':
        if($fsc_opt['ex_field'.$i.'_notes'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
        }
                $string .= '
        <div ';
         $string .= ($fsc_opt['ex_field'.$i.'_label_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_label_css']) : $this->ctf_title_style;
         $string .= '>
                <label for="fsc_ex_field'.$form_id_num.'_'.$i.'">' . $fsc_opt['ex_field'.$i.'_label'] . $ex_req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error(${'fsc_error_ex_field'.$i}).' 
                <textarea ';
         $string .= ($fsc_opt['ex_field'.$i.'_input_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_input_css']) : $this->ctf_field_style;
         $string .= ' id="fsc_ex_field'.$form_id_num.'_'.$i.'" name="fsc_ex_field'.$i.'" '.$ex_req_field_aria;
         if(strpos($fsc_opt['ex_field'.$i.'_attributes'],'cols')===false)
            $string .= ' cols="'.$this->absint($fsc_opt['text_cols']).'"';
         if(strpos($fsc_opt['ex_field'.$i.'_attributes'],'rows')===false)
            $string .= ' rows="'.$this->absint($fsc_opt['text_rows']).'"';

                if($fsc_opt['ex_field'.$i.'_attributes'] != '')
                  $string .= ' '.$fsc_opt['ex_field'.$i.'_attributes'];
                $string .= '>';
              if($fsc_opt['ex_field'.$i.'_default_text'] != '' && ${'ex_field'.$i} == '')
                  $string .=  $this->ctf_output_string($fsc_opt['ex_field'.$i.'_default_text']);
              else
                $string .= ($fsc_opt['textarea_html_allow'] == 'true') ? $this->ctf_stripslashes(${'ex_field'.$i}) : $this->ctf_output_string(${'ex_field'.$i});

                $string .= '</textarea>
        </div>
';
        if($fsc_opt['ex_field'.$i.'_notes_after'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes_after']);
        }
              break;
           case 'select':

           // find the label and the options inside $fsc_opt['ex_field'.$i.'_label']
           // the drop down list array will be made automatically by this code
$exf_opts_array = array();
$exf_opts_label = '';
$exf_array_test = trim($fsc_opt['ex_field'.$i.'_label'] );
if(!preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
       // error
       $this->fsc_error = 1;
       $string .= $this->echo_if_error(_('Error: A select field is not configured properly in settings.'));
} else {
       list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
       $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
       $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
       if ($exf_opts_label != '' && $value != '') {
          if(!preg_match("/;/", $value)) {
               // error
               $this->fsc_error = 1;
               $string .= $this->echo_if_error(_('Error: A select field is not configured properly in settings.'));
          } else {
               // multiple options
               $exf_opts_array = explode(";",$value);
         }
      }
} // end else
        if($fsc_opt['ex_field'.$i.'_notes'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
        }
           $string .= '
        <div ';
         $string .= ($fsc_opt['ex_field'.$i.'_label_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_label_css']) : $this->ctf_title_style;
         $string .= '>
                <label for="fsc_ex_field'.$form_id_num.'_'.$i.'">' . $exf_opts_label . $ex_req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error(${'fsc_error_ex_field'.$i}).'
               <select ';
         $string .= ($fsc_opt['ex_field'.$i.'_input_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_input_css']) : $this->ctf_field_style;
         $string .= ' id="fsc_ex_field'.$form_id_num.'_'.$i.'" name="fsc_ex_field'.$i.'"';
                if($fsc_opt['ex_field'.$i.'_attributes'] != '')
                  $string .= ' '.$fsc_opt['ex_field'.$i.'_attributes'];
                $string .= '>
        ';

$exf_opts_ct = 1;
$selected = '';
foreach ($exf_opts_array as $k) {
 $k = trim($k);
 if (${'ex_field'.$i} != '') {
    if (${'ex_field'.$i} == "$k") {
      $selected = ' selected="selected"';
    }
 }else{
    if ($exf_opts_ct == $fsc_opt['ex_field'.$i.'_default']) {
      $selected = ' selected="selected"';
    }
 }
 if ($exf_opts_ct == 1 && preg_match('/^\[(.*)]$/',$k, $matches)) // "[Please select]" becomes "Please select"
  $string .= '          <option value=""'.$selected.'>'.$this->ctf_output_string($matches[1]).'</option>'."\n";
 else
  $string .= '          <option value="'.$this->ctf_output_string($k).'"'.$selected.'>'.$this->ctf_output_string($k).'</option>'."\n";

 $exf_opts_ct++;
 $selected = '';

}
$string .= '            </select>
        </div>
';
        if($fsc_opt['ex_field'.$i.'_notes_after'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes_after']);
        }
             break;
           case 'select-multiple':

           // find the label and the options inside $fsc_opt['ex_field'.$i.'_label']
           // the drop down list array will be made automatically by this code
$exf_opts_array = array();
$exf_opts_label = '';
$exf_array_test = trim($fsc_opt['ex_field'.$i.'_label'] );
if(!preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
       // error
       $this->fsc_error = 1;
       $string .= $this->echo_if_error(_('Error: A select-multiple field is not configured properly in settings.'));
} else {
       list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
       $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
       $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
       if ($exf_opts_label != '' && $value != '') {
          if(!preg_match("/;/", $value)) {
               echo $value;
               // error
               $this->fsc_error = 1;
               $string .= $this->echo_if_error(_('Error: A select-multiple field is not configured properly in settings.'));
          } else {
               // multiple options
               $exf_opts_array = explode(";",$value);
         }
      }
} // end else
        if($fsc_opt['ex_field'.$i.'_notes'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
        }
           $string .= '
        <div ';
         $string .= ($fsc_opt['ex_field'.$i.'_label_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_label_css']) : $this->ctf_title_style;
         $string .= '>
                <label for="fsc_ex_field'.$form_id_num.'_'.$i.'">' . $exf_opts_label . $ex_req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error(${'fsc_error_ex_field'.$i}).'
               <select ';
         $string .= ($fsc_opt['ex_field'.$i.'_input_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_input_css']) : $this->ctf_field_style;
         $string .= ' id="fsc_ex_field'.$form_id_num.'_'.$i.'" name="fsc_ex_field'.$i.'[]" multiple="multiple"';
                if($fsc_opt['ex_field'.$i.'_attributes'] != '')
                  $string .= ' '.$fsc_opt['ex_field'.$i.'_attributes'];
                $string .= '>
';

  $ex_get = 0;
  $ex_cnt = 1;
  // any thing already selected by GET method?
  foreach ($exf_opts_array as $k) {
      if(isset(${'ex_field'.$i.'_'.$ex_cnt}) && ${'ex_field'.$i.'_'.$ex_cnt} == 'selected' ){
        $ex_get =1;
        break;
      }
      $ex_cnt++;
  }

$exf_opts_ct = 1;
$selected = '';
foreach ($exf_opts_array as $k) {
 $k = trim($k);
 if (is_array(${'ex_field'.$i}) && ${'ex_field'.$i} != '') {
    if (in_array($k, ${'ex_field'.$i} ) ) {
      $selected = ' selected="selected"';
    }
 }
 // selected by default
 if (!isset($_POST['fsc_form_id']) && !$ex_get && $exf_opts_ct == $fsc_opt['ex_field'.$i.'_default']) {
      $selected = ' selected="selected"';
 }
 // selected by get
 if ( $ex_get && isset(${'ex_field'.$i.'_'.$exf_opts_ct}) && ${'ex_field'.$i.'_'.$exf_opts_ct} == 'selected' )
    $selected = ' selected="selected"';
 $string .= '               <option value="'.$this->ctf_output_string($k).'"'.$selected.'>'.$this->ctf_output_string($k).'</option>'."\n";
 $exf_opts_ct++;
 $selected = '';

}
$string .= '               </select>
        </div>
';
        if($fsc_opt['ex_field'.$i.'_notes_after'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes_after']);
        }
             break;
           case 'checkbox':
           case 'checkbox-multiple':

$exf_opts_array = array();
$exf_opts_label = '';
$exf_opts_inline = 0;
$exf_array_test = trim($fsc_opt['ex_field'.$i.'_label'] );
if ( ($fsc_opt['ex_field'.$i.'_type'] == 'checkbox' && preg_match('#(?<!\\\)\,#', $exf_array_test) ) ||
($fsc_opt['ex_field'.$i.'_type'] == 'checkbox-multiple' && !preg_match("/;/", $exf_array_test))  ) {
   $this->fsc_error = 1;
   $string .= $this->echo_if_error(_('Error: A checkbox field is not configured properly in settings.'));
}
if( preg_match('#(?<!\\\)\,#', $exf_array_test) && preg_match("/;/", $exf_array_test) ) {
       list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
       $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
       $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
       if ($exf_opts_label != '' && $value != '') {
          if(!preg_match("/;/", $value)) {
               // error
               $this->fsc_error = 1;
               $string .= $this->echo_if_error(_('Error: A checkbox field is not configured properly in settings.'));
          } else {
               // multiple options
               $exf_opts_array = explode(";",$value);
         }
      }

  // checkbox children
         if(preg_match('/^{inline}/',$exf_opts_label)) {
              $exf_opts_label = str_replace('{inline}','',$exf_opts_label);
              $exf_opts_inline = 1;
         }
        if($fsc_opt['ex_field'.$i.'_notes'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
        }
           $string .= '
        <div ';
         $string .= ($fsc_opt['ex_field'.$i.'_label_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_label_css']) : $this->ctf_title_style;
         $string .= '>
                 <label>' . $exf_opts_label . $ex_req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'. $this->echo_if_error(${'fsc_error_ex_field'.$i});
$string .=   "\n";

  $ex_get = 0;
  $ex_cnt = 1;
  // any thing already selected by GET method?
  foreach ($exf_opts_array as $k) {
      if(isset(${'ex_field'.$i.'_'.$ex_cnt}) && ${'ex_field'.$i.'_'.$ex_cnt} == 'selected' ){
        $ex_get =1;
        break;
      }
      $ex_cnt++;
  }

  $ex_cnt = 1;
  foreach ($exf_opts_array as $k) {
     $k = trim($k);
     if(!$exf_opts_inline && $ex_cnt > 1)
               $string .= "                <br />\n";
     $string .= '                <span style="white-space:nowrap;"><input type="checkbox" style="width:13px;" id="fsc_ex_field'.$form_id_num.'_'.$i.'_'.$ex_cnt.'" name="fsc_ex_field'.$i.'_'.$ex_cnt.'" value="selected"  ';

    if (!isset($_POST['fsc_form_id']) && !$ex_get && $ex_cnt == $fsc_opt['ex_field'.$i.'_default']) {
      $string .= ' checked="checked"';
    }

    if ( isset(${'ex_field'.$i.'_'.$ex_cnt}) && ${'ex_field'.$i.'_'.$ex_cnt} == 'selected' )
    $string .= ' checked="checked"';

                if($fsc_opt['ex_field'.$i.'_attributes'] != '')
                  $string .= ' '.$fsc_opt['ex_field'.$i.'_attributes'];
                 $string .= ' />
                <label style="display:inline;" for="fsc_ex_field'.$form_id_num.'_'.$i.'_'.$ex_cnt.'">' . $k .'</label></span>'."\n";
     $ex_cnt++;
  }

   $string .= '        </div>'."\n";

} else {

  // single
        if($fsc_opt['ex_field'.$i.'_notes'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
        }
               $string .= '
        <div ';
         $string .= ($fsc_opt['ex_field'.$i.'_label_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_label_css']) : $this->ctf_title_style;
         $string .= '>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error(${'fsc_error_ex_field'.$i}).'
               <input type="checkbox" style="width:13px;" id="fsc_ex_field'.$form_id_num.'_'.$i.'" name="fsc_ex_field'.$i.'" value="selected" ';
    if (${'ex_field'.$i} != '') {
      if (${'ex_field'.$i} == 'selected') {
         $string .= 'checked="checked"';
      }
    }else{
      if (!isset($_POST['fsc_action']) && $fsc_opt['ex_field'.$i.'_default'] == '1') {
         $string .= 'checked="checked"';
      }
    }
    $fsc_opt['ex_field'.$i.'_label'] = trim(str_replace('\,',',',$fsc_opt['ex_field'.$i.'_label'])); // "\," changes to ","

               if($fsc_opt['ex_field'.$i.'_attributes'] != '')
                  $string .= ' '.$fsc_opt['ex_field'.$i.'_attributes'];
                 $string .= ' />
                <label style="display:inline;" for="fsc_ex_field'.$form_id_num.'_'.$i.'">' . $fsc_opt['ex_field'.$i.'_label'] . $ex_req_field_ind.'</label>
        </div>
';

} // end else
        if($fsc_opt['ex_field'.$i.'_notes_after'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes_after']);
        }
             break;
           case 'radio':

           // find the label and the options inside $fsc_opt['ex_field'.$i.'_label']
           // the radio list array will be made automatically by this code
$exf_opts_array = array();
$exf_opts_label = '';
$exf_opts_inline = 0;
$exf_array_test = trim($fsc_opt['ex_field'.$i.'_label'] );
if(!preg_match('#(?<!\\\)\,#', $exf_array_test) ) {
       // error
       $this->fsc_error = 1;
       $string .= $this->echo_if_error(_('Error: A radio field is not configured properly in settings.'));
} else {
       list($exf_opts_label, $value) = preg_split('#(?<!\\\)\,#',$exf_array_test); //string will be split by "," but "\," will be ignored
       $exf_opts_label   = trim(str_replace('\,',',',$exf_opts_label)); // "\," changes to ","
       $value = trim(str_replace('\,',',',$value)); // "\," changes to ","
       if ($exf_opts_label != '' && $value != '') {
          if(!preg_match("/;/", $value)) {
               // error
               $this->fsc_error = 1;
               $string .= $this->echo_if_error(_('Error: A radio field is not configured properly in settings.'));
          } else {
               // multiple options
               $exf_opts_array = explode(";",$value);
         }
      }
} // end else
         if(preg_match('/^{inline}/',$exf_opts_label)) {
              $exf_opts_label = str_replace('{inline}','',$exf_opts_label);
              $exf_opts_inline = 1;
         }
        if($fsc_opt['ex_field'.$i.'_notes'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
        }
           $string .=   '
        <div ';
         $string .= ($fsc_opt['ex_field'.$i.'_label_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_label_css']) : $this->ctf_title_style;
         $string .= '>
                 <label>' . $exf_opts_label . $ex_req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'. $this->echo_if_error(${'fsc_error_ex_field'.$i});
$string .=   "\n";

$selected = '';
$ex_cnt = 1;
foreach ($exf_opts_array as $k) {
 $k = trim($k);
 if (${'ex_field'.$i} != '') {
    if (${'ex_field'.$i} == "$k") {
      $selected = ' checked="checked"';
    }
 }else{
    if ($ex_cnt == $fsc_opt['ex_field'.$i.'_default']) {
      $selected = ' checked="checked"';
    }
 }
      if(!$exf_opts_inline && $ex_cnt > 1)
               $string .= "           <br />\n";
 $string .= '           <span style="white-space:nowrap;"><input type="radio" style="width:13px;" id="fsc_ex_field'.$form_id_num.'_'.$i.'_'.$ex_cnt.'" name="fsc_ex_field'.$i.'" value="'.$this->ctf_output_string($k).'"'.$selected;
                if($fsc_opt['ex_field'.$i.'_attributes'] != '')
                  $string .= ' '.$fsc_opt['ex_field'.$i.'_attributes'];
                $string .= ' />
           <label style="display:inline;" for="fsc_ex_field'.$form_id_num.'_'.$i.'_'.$ex_cnt.'">' . $k .'</label></span>'."\n";
 $selected = '';
 $ex_cnt++;
}
$string .= '
        </div>
';
        if($fsc_opt['ex_field'.$i.'_notes_after'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes_after']);
        }
             break;
           case 'attachment':
     if ($fsc_opt['php_mailer_enable'] != 'php') {
        if($fsc_opt['ex_field'.$i.'_notes'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
        }
            $string .= '
        <div ';
         $string .= ($fsc_opt['ex_field'.$i.'_label_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_label_css']) : $this->ctf_title_style;
         $string .= '>
                <label for="fsc_ex_field'.$form_id_num.'_'.$i.'">' . $fsc_opt['ex_field'.$i.'_label'] . $ex_req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error(${'fsc_error_ex_field'.$i}).'
                <input '.$this->ctf_field_style.' type="file" id="fsc_ex_field'.$form_id_num.'_'.$i.'" name="fsc_ex_field'.$i.'" value="' . $this->ctf_output_string(${'ex_field'.$i}) . '" '.$ex_req_field_aria.' size="20" ';
                if($fsc_opt['ex_field'.$i.'_attributes'] != '')
                  $string .= ' '.$fsc_opt['ex_field'.$i.'_attributes'];
                $string .= ' />';
 if(!$printed_tooltip_filetypes || ($printed_tooltip_filetypes+1) != $ex_loop_cnt) {
    $string .=  '<br /><span style="font-size:x-small;">';
    $string .= ($fsc_opt['tooltip_filetypes'] != '') ? $fsc_opt['tooltip_filetypes'] : _('Acceptable file types:');
    $string .= ' '.$fsc_opt['attach_types'] . '.<br />';
    $string .= ($fsc_opt['tooltip_filesize'] != '') ? $fsc_opt['tooltip_filesize'] : _('Maximum file size:');
    $string .= ' '.$fsc_opt['attach_size'].'.</span>';
 }
 $printed_tooltip_filetypes = $ex_loop_cnt;
$string .= '        </div>
';
        if($fsc_opt['ex_field'.$i.'_notes_after'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes_after']);
        }
        }
          break;
          case 'date':
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
        if($fsc_opt['ex_field'.$i.'_notes'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
        }
                 $string .= '
        <div ';
         $string .= ($fsc_opt['ex_field'.$i.'_label_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_label_css']) : $this->ctf_title_style;
         $string .= '>
                <label for="fsc_ex_field'.$form_id_num.'_'.$i.'">' .$fsc_opt['ex_field'.$i.'_label'] . $ex_req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error(${'fsc_error_ex_field'.$i}).'
                <input ';
         $string .= ($fsc_opt['ex_field'.$i.'_input_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_input_css']) : $this->ctf_field_style;
         $string .= ' type="text" id="fsc_ex_field'.$form_id_num.'_'.$i.'" name="fsc_ex_field'.$i.'" value="';
                $string .=   ( isset(${'ex_field'.$i}) && ${'ex_field'.$i} != '') ? $this->ctf_output_string(${'ex_field'.$i}): $cal_date_array[$fsc_opt['date_format']];
                $string .=   '" '.$ex_req_field_aria.' size="15" ';
                if($fsc_opt['ex_field'.$i.'_attributes'] != '')
                  $string .= ' '.$fsc_opt['ex_field'.$i.'_attributes'];
                $string .= ' />
        </div>
';
        if($fsc_opt['ex_field'.$i.'_notes_after'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes_after']);
        }
             break;
             case 'time':
           // the time drop down list array will be made automatically by this code
$exf_opts_array = array();
        if($fsc_opt['ex_field'.$i.'_notes'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes']);
        }
           $string .=  '
        <div ';
         $string .= ($fsc_opt['ex_field'.$i.'_label_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_label_css']) : $this->ctf_title_style;
         $string .= '>
                <label for="fsc_ex_field'.$form_id_num.'_'.$i.'">' . $fsc_opt['ex_field'.$i.'_label'] . $ex_req_field_ind.'</label>
        </div>
        <div '.$this->ctf_field_div_style.'>'.$this->echo_if_error(${'fsc_error_ex_field'.$i}).'
               <select ';
         $string .= ($fsc_opt['ex_field'.$i.'_input_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_input_css']) : $this->ctf_field_style;
         $string .= ' id="fsc_ex_field'.$form_id_num.'_'.$i.'" name="fsc_ex_field'.$i.'h">
        ';

$selected = '';
// hours
$tf_hours = ($fsc_opt['time_format'] == '24') ? '23' : '12';
for ($ii = ($fsc_opt['time_format'] == '24') ? 0 : 1; $ii <= $tf_hours; $ii++) {
 $ii = sprintf("%02d",$ii);
 if (${'ex_field'.$i.'h'} != '') {
    if (${'ex_field'.$i.'h'} == "$ii") {
      $selected = ' selected="selected"';
    }
 }
 $string .= '           <option value="'.$this->ctf_output_string($ii).'"'.$selected.'>'.$this->ctf_output_string($ii).'</option>'."\n";
 $selected = '';

}
 $string .= '           </select>:<select ';
         $string .= ($fsc_opt['ex_field'.$i.'_input_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_input_css']) : $this->ctf_field_style;
         $string .= ' id="fsc_ex_field'.$form_id_num.'_'.$i.'m" name="fsc_ex_field'.$i.'m">
        ';
$selected = '';
// minutes
for ($ii = 00; $ii <= 59; $ii++) {
      $ii = sprintf("%02d",$ii);
 if (${'ex_field'.$i.'m'} != '') {
    if (${'ex_field'.$i.'m'} == "$ii") {
      $selected = ' selected="selected"';
    }
 }
 $string .= '           <option value="'.$this->ctf_output_string($ii).'"'.$selected.'>'.$this->ctf_output_string($ii).'</option>'."\n";
 $selected = '';

}
$string .= '           </select>';
if ($fsc_opt['time_format'] == '12'){
$string .= '<select ';
         $string .= ($fsc_opt['ex_field'.$i.'_input_css'] != '') ? $this->fsc_convert_css($fsc_opt['ex_field'.$i.'_input_css']) : $this->ctf_field_style;
         $string .= ' id="fsc_ex_field'.$form_id_num.'_'.$i.'ap" name="fsc_ex_field'.$i.'ap">
        ';

$selected = '';
// am/pm
foreach (array($this->ctf_output_string(_('AM')), $this->ctf_output_string(_('PM')) ) as $k) {
 if (${'ex_field'.$i.'ap'} != '') {
    if (${'ex_field'.$i.'ap'} == "$k") {
      $selected = ' selected="selected"';
    }
 }
 $string .= '           <option value="'.$this->ctf_output_string($k).'"'.$selected.'>'.$this->ctf_output_string($k).'</option>'."\n";
 $selected = '';

}
$string .= '           </select>';
}
$string .= '
       </div>
';
        if($fsc_opt['ex_field'.$i.'_notes_after'] != '') {
           $string .=  $this->ctf_notes($fsc_opt['ex_field'.$i.'_notes_after']);
        }
             break;

          }

        } // end if label
      $ex_loop_cnt++;
      } // end foreach

 // how many extra fields are date fields?
     $ex_date_found = array();
     for ($i = 1; $i <= $fsc_opt['max_fields']; $i++) {
        if ($fsc_opt['ex_field'.$i.'_label'] != '' && $fsc_opt['ex_field'.$i.'_type'] == 'date') {
          $ex_date_found[$i] = $i;
        }
     }
     if (isset($ex_date_found) && count($ex_date_found) > 0 ) {
     $string .=
/*<link rel="stylesheet" type="text/css" href="'.$fsc_site['site_url'].'/date/ctf_epoch_styles.css?'.time().'" />*/
'<script type="text/javascript">
    var ctf_css = document.createElement(\'link\');
	ctf_css.rel = \'stylesheet\';
	ctf_css.type = \'text/css\';
	ctf_css.href = \''.$fsc_site['site_url'].'/date/ctf_epoch_styles.css?'.time().'\';
	document.getElementsByTagName(\'head\')[0].appendChild(ctf_css);
	var ctf_daylist = new Array( \''._('Su').'\',\''._('Mo').'\',\''._('Tu').'\',\''._('We').'\',\''._('Th').'\',\''._('Fr').'\',\''._('Sa').'\',\''._('Su').'\',\''._('Mo').'\',\''._('Tu').'\',\''._('We').'\',\''._('Th').'\',\''._('Fr').'\',\''._('Sa').'\' );
	var ctf_months_sh = new Array( \''._('Jan').'\',\''._('Feb').'\',\''._('Mar').'\',\''._('Apr').'\',\''._('May').'\',\''._('Jun').'\',\''._('Jul').'\',\''._('Aug').'\',\''._('Sep').'\',\''._('Oct').'\',\''._('Nov').'\',\''._('Dec').'\' );
	var ctf_monthup_title = \''._('Go to the next month').'\';
	var ctf_monthdn_title = \''._('Go to the previous month').'\';
	var ctf_clearbtn_caption = \''._('Clear').'\';
	var ctf_clearbtn_title = \''._('Clears any dates selected on the calendar').'\';
	var ctf_maxrange_caption = \''._('This is the maximum range').'\';
    var ctf_cal_start_day = '.$fsc_opt['cal_start_day'].';
    var ctf_date_format = \'';
 if($fsc_opt['date_format'] == 'mm/dd/yyyy')
      $string .=   'm/d/Y';
 if($fsc_opt['date_format'] == 'dd/mm/yyyy')
      $string .=   'd/m/Y';
 if($fsc_opt['date_format'] == 'mm-dd-yyyy')
      $string .=   'm-d-Y';
 if($fsc_opt['date_format'] == 'dd-mm-yyyy')
      $string .=   'd-m-Y';
 if($fsc_opt['date_format'] == 'mm.dd.yyyy')
      $string .=   'm.d.Y';
 if($fsc_opt['date_format'] == 'dd.mm.yyyy')
      $string .=   'd.m.Y';
 if($fsc_opt['date_format'] == 'yyyy/mm/dd')
      $string .=   'Y/m/d';
 if($fsc_opt['date_format'] == 'yyyy-mm-dd')
      $string .=   'Y-m-d';
 if($fsc_opt['date_format'] == 'yyyy.mm.dd')
      $string .=   'Y.m.d';
 $string .= '\';
</script>
<script type="text/javascript" src="'.$fsc_site['site_url'].'/date/ctf_epoch_classes.js?'.time().'"></script>
<script type="text/javascript">
var ';
        $ex_date_var_string = '';
        foreach ($ex_date_found as $v) {
          $ex_date_var_string .= "dp_cal$form_id_num".'_'."$v,";
        }
        $ex_date_var_string = substr($ex_date_var_string,0,-1);
$string .= "$ex_date_var_string;\n";
$string .= 'window.onload = function () {
';
        foreach ($ex_date_found as $v) {
          $string .= "dp_cal$form_id_num".'_'."$v  = new Epoch('epoch_popup$form_id_num".'_'."$v','popup',document.getElementById('fsc_ex_field$form_id_num".'_'."$v'));\n";
        }
$string .=   "};\n</script>\n";

     }
     if($ex_fieldset)
        $string .=   "</fieldset>\n";
?>