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

// outputs a contact form settings backup file

        $backup_type = $_POST['fsc_backup_type'];
        $fsc_site = $this->get_option("fsc_site");
        // set timezone php5 style
        date_default_timezone_set($fsc_site['timezone']);
        // get the global options from the database
        $fsc_bk_gb = $this->get_option("fsc_form_gb");
        $fsc_bk_gb['backup_type'] = $backup_type;
        $eol = "\r\n";

        // format the data to be stored in contact-form-backup.txt
        $string .= "**SERIALIZED DATA, DO NOT HAND EDIT!**$eol";
        $string .= "Backup of forms and settings for 'Fast Secure Contact Form' PHP Script $fsc_version $eol";
        $string .= 'Form ID included in this backup: '.$backup_type.$eol;
        $string .= "Web site: ".$fsc_site['site_url'].$eol;
        $string .= "Web site name: ".$fsc_site['site_name'].$eol;
        $string .= "Backup date: ".date("F j, Y, g:i a T") ."$eol*/$eol";
        $string .= "@@@@SPLIT@@@@$eol";
        $backup_array = array();
        $backup_array[0] = $fsc_bk_gb;

        $ok = 0;
        if ($backup_type == 'all' || $backup_type == '1'){
            // form 1
            $fsc_bk_opt = $this->get_option('fsc_form');
            // strip slashes on get options array
            //foreach($fsc_bk_opt as $key => $val) {
                //$fsc_bk_opt[$key] = $this->ctf_stripslashes($val);
            //}
            $backup_array[1] = $fsc_bk_opt;
            $ok = 1;
        }
        if ($backup_type == 'all'){
            // multi-forms > 1
            for ($i = 2; $i <= $fsc_bk_gb['max_forms']; $i++) {
              // get the form options from the database
              $fsc_bk_opt = $this->get_option("fsc_form$i");
              // strip slashes on get options array
              //foreach($fsc_bk_opt as $key => $val) {
                 // $fsc_bk_opt[$key] = $this->ctf_stripslashes($val);
              //}
              $backup_array[$i] = $fsc_bk_opt;
            }
            $ok = 1;
         }else if (is_numeric($backup_type)
           && $backup_type > 1
           && $fsc_bk_opt = $this->get_option('fsc_form'.$backup_type)){
           // form x
           // strip slashes on get options array
           //foreach($fsc_bk_opt as $key => $val) {
               // $fsc_bk_opt[$key] = $this->ctf_stripslashes($val);
           //}
           $backup_array[1] = $fsc_bk_opt;
           $ok = 1;
         }

         if(!$ok){
            // bail out
            die(_('Requested form to backup is not found.'));
         }
         $string .= serialize($backup_array);

         $filename = 'fsc-backup-'.$backup_type.'.txt';

        // force download dialog to web browser
        ob_end_clean();
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' .(string)(strlen($string)) );
        flush();
        echo $string;
        exit;

?>