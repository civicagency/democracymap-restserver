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

     // form file upload
     if(isset($_FILES['fsc_backup_file']) && !empty( $_FILES['fsc_backup_file'] ))
       $file = $_FILES['fsc_backup_file'];
     else
       return '<div id="message" class="error"><strong>'._('Restore failed: Backup file is required').'</strong></div>';

	 if ( ($file['error'] && UPLOAD_ERR_NO_FILE != $file['error']) || !is_uploaded_file( $file['tmp_name'] ) )
        return '<div id="message" class="error"><strong>'._('Restore failed: Backup file upload failed').'</strong></div>';

	 if ( empty( $file['tmp_name'] ) )
        return '<div id="message" class="error"><strong>'._('Restore failed: Backup file is required').'</strong></div>';

    // check file type
	$file_type_pattern = '/\.txt$/i';
	if ( ! preg_match( $file_type_pattern, $file['name'] ) )
        return '<div id="message" class="error"><strong>'._('Restore failed: Backup file type not allowed').'</strong></div>';

    // check size
    $allowed_size = 1048576; // 1mb default
	if ( $file['size'] > $allowed_size )
        return '<div id="message" class="error"><strong>'._('Restore failed: Backup file size is too large').'</strong></div>';

    // get the uploaded file that contains all the data
    $ctf_backup_data = file_get_contents($file['tmp_name']);
    $ctf_backup_data_split = explode("@@@@SPLIT@@@@\r\n", $ctf_backup_data);
    $ctf_backup_array = unserialize($ctf_backup_data_split[1]);

    if ( !isset($ctf_backup_array) || !is_array($ctf_backup_array) || !isset($ctf_backup_array[0]['backup_type']) )
         return '<div id="message" class="error"><strong>'._('Restore failed: Backup file contains invalid data').'</strong></div>';

   // print_r($ctf_backup_array);
   // exit;

         $ctf_backup_type = $ctf_backup_array[0]['backup_type'];
         unset($ctf_backup_array[0]['backup_type']);

         // is the uploaded file of the "all" type?
         if ( $ctf_backup_type != 'all' && $bk_form_num == 'all'  )
              return '<div id="message" class="error"><strong>'._('Restore failed: Selected All to restore, but backup file is a single form').'</strong></div>';

         // restore all ?
         if($ctf_backup_type == 'all' && $bk_form_num == 'all' ) {
            // all

            // is the uploaded file of the "all" type?
            if ( !isset($ctf_backup_array[2]) || !is_array($ctf_backup_array[2])  )
              return '<div id="message" class="error"><strong>'._('Restore failed: Selected All to restore, but backup file is a single form').'</strong></div>';

            $my_max_forms = $fsc_gb['max_forms'];
            // if current max_forms or max_fields are more, go with higher value
            if($fsc_gb['max_forms'] > $ctf_backup_array[0]['max_forms']) {
                $my_max_forms = $ctf_backup_array[0]['max_forms'];
                $ctf_backup_array[0]['max_forms'] = $fsc_gb['max_forms'];
            } else {
                $my_max_forms = $ctf_backup_array[0]['max_forms'];
            }
            if($fsc_gb['max_fields'] > $ctf_backup_array[0]['max_fields'])
                $ctf_backup_array[0]['max_fields'] = $fsc_gb['max_fields'];
            $this->set_option("fsc_form_gb", $ctf_backup_array[0], 1);

            // extra field labels might have \, (make sure it does not get removed)
            //foreach($ctf_backup_array[1] as $key => $val) {
                //$ctf_backup_array[1][$key] = str_replace('\,','\\\,',$val);
            //}
            $this->set_option("fsc_form", $ctf_backup_array[1], 1);
            // multi-forms > 1
            for ($i = 2; $i <= $my_max_forms; $i++) {
              // extra field labels might have \, (make sure it does not get removed)
             // foreach($ctf_backup_array[$i] as $key => $val) {
                 // $ctf_backup_array[$i][$key] = str_replace('\,','\\\,',$val);
             // }
              if(!$this->get_option("fsc_form$i")) {
                    $this->set_option("fsc_form$i", $ctf_backup_array[$i], 1);
              }else{
                   $this->set_option("fsc_form$i", $ctf_backup_array[$i], 1);
              }
            }
           //error_reporting(0); // suppress errors because a different version backup may have uninitialized vars
           // success
           return '<div id="message" class="updated"><strong>'._('All form settings have been restored from the backup file').'</strong></div>';

         } // end restoring all

         // restore single?
         if(is_numeric($bk_form_num)){
            // single
            if( ($bk_form_num == 1 && !$this->get_option("fsc_form")) || ($bk_form_num > 1 && !$this->get_option("fsc_form$bk_form_num")))
               return '<div id="message" class="error"><strong>'._('Restore failed: Form to restore to does not exist').'</strong></div>';

            // update the globals
            if($fsc_gb['max_fields'] < $ctf_backup_array[0]['max_fields']) {
                $fsc_gb['max_fields'] = $ctf_backup_array[0]['max_fields'];
                $this->set_option("fsc_form_gb", $fsc_gb, 1);
            }

            // is the uploaded file of the "single" type?
            if ( !isset($ctf_backup_array[2]) || !is_array($ctf_backup_array[2])  ) {
               //single

               // extra field labels might have \, (make sure it does not get removed)
              // foreach($ctf_backup_array[1] as $key => $val) {
              //     $ctf_backup_array[1][$key] = str_replace('\,','\\\,',$val);
              // }
               if ($bk_form_num == 1)
                  $this->set_option("fsc_form", $ctf_backup_array[1], 1);

               if ($bk_form_num > 1)
                   $this->set_option("fsc_form$bk_form_num", $ctf_backup_array[1], 1);

               // is the uploaded file of the "all" type?
            } else {
               // "all" backup file, but wants to restore only one form, match the form #
               // extra field labels might have \, (make sure it does not get removed)
               //foreach($ctf_backup_array[$bk_form_num] as $key => $val) {
               //    $ctf_backup_array[$bk_form_num][$key] = str_replace('\,','\\\,',$val);
               //}
               if ($bk_form_num == 1)
                  $this->set_option("fsc_form", $ctf_backup_array[1], 1);

               if ($bk_form_num > 1)
                  $this->set_option("fsc_form$bk_form_num", $ctf_backup_array[$bk_form_num], 1);
             }
              error_reporting(0); // suppress errors because a different version backup may have uninitialized vars
              // success
              return '<div id="message" class="updated"><strong>'.sprintf(_('Form %d settings have been restored from the backup file'),$bk_form_num).'</strong></div>';

         } // end restoring single

?>