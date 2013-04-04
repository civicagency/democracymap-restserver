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

// copy settings from one form to another

// copy settings requested
if ( isset($_POST['ctf_action'])
    && $_POST['ctf_action'] == _('Copy Settings')
    && isset($_POST['fsc_copy_what'])
    && isset($_POST['fsc_this_form'])
    && is_numeric($_POST['fsc_this_form'])
    && isset($_POST['fsc_destination_form']) ) {

        $copy_what = $_POST['fsc_copy_what'];
        $this_form = $_POST['fsc_this_form'];
        $destination_form = $_POST['fsc_destination_form'];

        // get the global options from the database
        $fsc_bk_gb = $this->get_option("fsc_form_gb");

        // get the options to copy from
        if($this_form == 1)
          $this_form_arr = $this->get_option("fsc_form");
        else
          $this_form_arr = $this->get_option("fsc_form$this_form");

          // add slashes on get options array
          foreach($this_form_arr as $key => $val) {
             $this_form_arr[$key] = addslashes($val);
          }

        $ok = 0;
        if ($destination_form == '1'){
            // form 1
            if ($copy_what == 'styles') {
                $destination_form_arr = $this->get_option("fsc_form");
                foreach($destination_form_arr as $key => $val) {
                   $destination_form_arr[$key] = addslashes($val);
                }
                $destination_form_arr = $this->fsc_copy_styles($this_form_arr,$destination_form_arr);
                $this->set_option("fsc_form", $destination_form_arr);
            } else {
                $this->set_option("fsc_form", $this_form_arr);
            }

            $ok = 1;
        }
        if ($destination_form == 'all'){
            // multi-forms > 1
            for ($i = 2; $i <= $fsc_bk_gb['max_forms']; $i++) {
               if ($copy_what == 'styles') {
                   $destination_form_arr = $this->get_option("fsc_form$i");
                   foreach($destination_form_arr as $key => $val) {
                      $destination_form_arr[$key] = addslashes($val);
                   }
                   $destination_form_arr = $this->fsc_copy_styles($this_form_arr,$destination_form_arr);
                   $this->set_option("fsc_form$i", $destination_form_arr);
               } else {
                   $this->set_option("fsc_form$i", $this_form_arr);
               }
            }
            $ok = 1;
         }else if (is_numeric($destination_form) && $destination_form > 1 ){
           // form x
            if ($copy_what == 'styles') {
                $destination_form_arr = $this->get_option("fsc_form$destination_form");
                foreach($destination_form_arr as $key => $val) {
                   $destination_form_arr[$key] = addslashes($val);
                }
                $destination_form_arr = $this->fsc_copy_styles($this_form_arr,$destination_form_arr);
                $this->set_option("fsc_form$destination_form", $destination_form_arr);
            } else {
                $this->set_option("fsc_form$destination_form", $this_form_arr);
            }
           $ok = 1;
         }

         if(!$ok){
            // bail out
            die(_('Requested form to copy settings from is not found.'));
         }

       // success
       if ($destination_form == 'all'){
          echo '<div id="message" class="updated"><p>'.sprintf(_('Form %d settings have been copied to all forms.'),$this_form).'</p></div>';
       }else{
          echo '<div id="message" class="updated"><p>'.sprintf(_('Form %d settings have been copied to form %d.'),$this_form,$destination_form).'</p></div>';
       }

} // end backup action

?>