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

  // the admin settings page

$passed_login = false;
$passed_login = $this->process_login();


// backup requested
if ($passed_login && isset($_POST['ctf_action'] )
    && $_POST['ctf_action'] == _('Backup Settings')
    && isset($_POST['fsc_backup_type'])
    && (is_numeric($_POST['fsc_backup_type']) || $_POST['fsc_backup_type'] == 'all') ) {

     $this->generate_backup_download();

} // end backup action

if ( $fsc_site = $this->get_option("fsc_site") ) {
      $encoding = $fsc_site['site_charset'];
}else{
     $encoding = 'UTF-8';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $encoding; ?>" />
	<title><?php echo _('Fast Secure Contact Form - PHP'); ?> - <?php echo _('Admin'); ?></title>
    <meta name="robots" content="noindex" />
	<link rel="stylesheet" type="text/css" href="style.css" media="screen" />
	<script type="text/javascript" src="../common.js"></script>
    <script type="text/javascript" src="../contact-form.js"></script>
</head>
<body>
<div id="container">
    <div id="header">
        <h1><?php echo _('Fast Secure Contact Form - PHP'); ?> - <?php echo _('Admin'); ?></h1>
        <ul id="nav_main">
            <li><a href="index.php" title="<?php echo _('Admin'); ?>" class="current"><?php echo _('Admin')?></a></li>

    <?php
    if ( $passed_login ) {
    ?>
      <li><a href="index.php?show_form=1" title="<?php echo _('Preview Form'); ?>"<?php if ( isset($_GET['show_form']) ){?>class="current"<?php }?>><?php echo _('Preview Form'); ?></a></li>
      <li><a href="index.php?site_settings=1" title="<?php echo _('Site Settings'); ?>"<?php if ( isset($_GET['site_settings']) ){?>class="current"<?php }?>><?php echo _('Site Settings'); ?></a></li>
      <li><a href="index.php?logout=1" title="<?php echo _('Logout'); ?>"><?php echo _('Logout'); ?></a></li>
    <?php
     }
    ?>
        </ul>
    <?php
     $fsc_update = $this->fsc_update_check();
     echo '<div style="text-align:right;">Version: '.$this->fsc_version.$fsc_update.'</div>';
    ?>
    </div>
    <div id="main">
<div id="content">
<?php

// show login form if not logged in
if (!$passed_login ) {
 echo _('This is where you login to configure your contact forms.');
?>
<br />
<br />
<div class="form-tab"><?php echo _('Login:');?></div>
<div class="clear"></div>
<fieldset>
<p>
<?php
    echo '<form method="post" action="index.php">'."\n";
    echo '<label for="access_user">'._('User').':</label> <input class="text-effect" type="text" id="access_user" name="access_user" size="10" />'."\n";
    echo '<label for="access_password">'._('Password').':</label> <input class="text-effect" type="password" id="access_password" name="access_password" size="10" />'."\n";
    echo  ' <input type="submit" name="Submit" value="'._('Login').'" />';
    if ( isset($this->fsc_login_error) ) echo '<br /><br /><span class="error">'. $this->fsc_login_error .'</span><br />'."\n";
  echo '</form>
  </p>

  <p>
  <a href="lost-pw.php">'._('I lost my password').'</a>
  </p>

 </fieldset>'."\n";

}

  // a couple language options need to be translated now.
  $this->update_lang();


   // action copy settings
	if ($passed_login &&  isset($_POST['ctf_action'])
    && $_POST['ctf_action'] == _('Copy Settings')
    && isset($_POST['fsc_copy_what'])
    && isset($_POST['fsc_this_form'])
    && is_numeric($_POST['fsc_this_form'])
    && isset($_POST['fsc_destination_form']) ) {

     include $this->site_path . '/admin/contact-form-settings-copy.php';

     // refresh settings
     $this->init_options($form_num);
     $fsc_opt = $this->fsc_opt;
     $fsc_opt_defaults = $this->fsc_opt_defaults;
     $fsc_gb = $this->fsc_gb;
     $fsc_gb_defaults = $this->fsc_gb_defaults;

  } // end action copy settings

    // action backup restore
  if ($passed_login && isset($_POST['ctf_action'])
    && $_POST['ctf_action'] == _('Restore Settings')
    && isset($_POST['fsc_backup_type'])) {

     echo $this->restore_options_from_backup($_POST['fsc_backup_type']);

     // initialize the restored backup
     $this->init_options($form_num);
     $fsc_opt = $this->fsc_opt;
     $fsc_opt_defaults = $this->fsc_opt_defaults;
     $fsc_gb = $this->fsc_gb;
     $fsc_gb_defaults = $this->fsc_gb_defaults;

  } // end action backup restore

  // Send a test mail if necessary
  if ($passed_login && isset($_POST['ctf_action']) && $_POST['ctf_action'] == _('Send Test') && isset($_POST['fsc_to'])) {

     include $this->site_path . '/admin/contact-form-do-test-mail.php';

  } // end Send a test mail if necessary


 // preview forms, viewable to logged in admin only
 if ($passed_login && isset($_GET['show_form']) && is_numeric($_GET['show_form']) && !isset($_POST['ctf_action'])) {

  // Set language
  //$language = 'en_US';
  $fsc_gb_mf = $this->get_option("fsc_form_gb");

  $form = $_GET['show_form'];
  $form_num = '';
  $form_id = 1;
  if ( isset($form) && is_numeric($form) && $form <= $fsc_gb_mf['max_forms'] ) {
     $form_num = (int)$form;
     $form_id = (int)$form;
     if ($form_num == 1)
        $form_num = '';
  }

   // show form number links
    // initialize the settings
     $this->init_options($form_num);
     $fsc_site = $this->fsc_site;
     $fsc_opt = $this->fsc_opt;
     $fsc_opt_defaults = $this->fsc_opt_defaults;
     $fsc_gb = $this->fsc_gb;
     $fsc_gb_defaults = $this->fsc_gb_defaults;

   $this->fsc_code_usage($form_id);

   ?>

  <h2><?php echo _('Preview'); ?></h2>

   <div class="form-tab"><?php echo _('Preview Multi-Forms:').' '. sprintf(_('(form %d)'),$form_id);?></div>
   <div class="clear"></div>
   <fieldset>

   <h3><?php
  // multi-form selector
  for ($i = 1; $i <= $fsc_gb['max_forms']; $i++) {
     if($i == 1) {
         if ($form_id == 1) {
             echo '<b>'.sprintf(_('Form: %d'),1).'</b>';
             echo ' <small><a href="index.php">('. _('edit'). ')</a></small>';
        } else {
             echo '<a href="index.php?show_form='.$i.'">'. sprintf(_('Form: %d'),1). '</a>';
        }
     } else {
        if ($form_id == $i) {
             echo ' | <b>'.sprintf(_('Form: %d'),$i).'</b>';
             echo ' <small><a href="index.php?ctf_form_num='.$i.'">('. _('edit'). ')</a></small>';
       } else {
             echo ' | <a href="index.php?show_form='.$i.'&amp;ctf_form_num='.$i.'">'. sprintf(_('Form: %d'),$i). '</a>';
       }
     }
  }
  ?>
  </h3>

  <br />

  <?php
   echo $this->form_do($form, $fsc_site['site_path']);

   echo '
  </fieldset>
  ';

 }// end preview forms


 if ($passed_login && isset($_GET['site_settings'])) {

   if ( isset($_POST['ctf_action']) && $_POST['ctf_action'] == 'update' ) {

        $admin_pwd_upd = $fsc_site['admin_pwd'];
        if ($_POST['admin_pwd'] != '') {
            $admin_pwd_upd = 'hashed_'. md5(trim($_POST['admin_pwd']));
             // reset cookie
             $scripturlparts = explode('/', $_SERVER['PHP_SELF']);
             $scriptfilename = $scripturlparts[count($scripturlparts)-1];
             $cookie_path = preg_replace("/$scriptfilename$/i", '', $_SERVER['PHP_SELF']);
             $admin_pwd_c = str_replace('hashed_', '',$admin_pwd_upd);
             //setcookie("fsc_verify", md5($fsc_site['admin_usr'].'%'.$admin_pwd_c),  time() + 3600, $cookie_path);
             setcookie("fsc_verify", md5($fsc_site['admin_usr'].'%'.$admin_pwd_c), 0, $cookie_path);
        }

        $fsc_site_update = array(
        'site_name' =>   trim($_POST['site_name']),
        'site_url' =>    trim($_POST['site_url']),
        'site_path' =>   trim($_POST['site_path']),
        'admin_name' =>  trim($_POST['admin_name']),
        'admin_email' => trim($_POST['admin_email']),
        'admin_usr' =>   trim($_POST['admin_usr']),
        'admin_pwd' =>   $admin_pwd_upd,
        'site_charset' => trim($_POST['site_charset']),
        'language' =>    trim($_POST['language']),
        'timezone' =>    trim($_POST['timezone']),
        'pwd_reset_key' => '',
        );

       // functions for protecting output against XSS. encode  < > & " ' (less than, greater than, ampersand, double quote, single quote).
       foreach($fsc_site_update as $key => $val) {
           $fsc_site_update[$key] = str_replace('&lt;','<',$val);
           $fsc_site_update[$key] = str_replace('&gt;','>',$val);
           $fsc_site_update[$key] = str_replace('&#39;',"'",$val);
           $fsc_site_update[$key] = str_replace('&quot;','"',$val);
           $fsc_site_update[$key] = str_replace('&amp;','&',$val);
       }

       // update site settings
       $this->set_option("fsc_site", $fsc_site_update);

      ?>
      <div class="updated"><strong><?php echo _('Options saved'); ?></strong></div>
      <?php

   } // end if posted update

   // show redo site settings page
   $fsc_site = $this->get_option("fsc_site");

   // strip slashes on get options array
   //foreach($fsc_site as $key => $val) {
          // $fsc_site[$key] = $this->ctf_stripslashes($val);
   //}

 ?>
<h2><?php echo _('Update Site Settings');?></h2>
<p><?php echo _('Edit the site settings and click the button below to update');?> Fast Secure Contact Form - PHP</p>

<form id="site_settings" action="" method="post" onsubmit="return checkForm(this);">
<div class="form-tab"><?php echo _("Path Settings");?></div>
<div class="clear"></div>
<fieldset>
<p>
    <label for="site_url"><?php echo _("Full URL to");?>: /contact-files</label>&nbsp;(<?php echo _("with http://");?>)<br />
    <input type="text" size="50" name="site_url" id="site_url" value="<?php echo $this->ctf_output_string($fsc_site['site_url']);?>" lang="false" onblur="validateText(this);" class="text-long" />&nbsp;<?php echo _("please check this carefully");?>,&nbsp;<?php echo _("no slash on end");?>
</p>
<p>
    <label for="site_path"><?php echo _("File Path to");?>: /contact-files</label><br />
    <input type="text" size="50" name="site_path" id="site_path" value="<?php echo $this->ctf_output_string($fsc_site['site_path']);?>" lang="false" onblur="validateText(this);" class="text-long" />&nbsp;<?php echo _("please check this carefully");?>,&nbsp;<?php echo _("no slash on end");?>
</p>
</fieldset>

<div class="form-tab"><?php echo _("Basic Configuration");?></div>
<div class="clear"></div>
<fieldset>
<p>
	<label for="site_name"><?php echo _("Site Name");?>:</label><br />
	<input type="text" size="50" name="site_name" id="site_name" value="<?php echo $this->ctf_output_string($fsc_site['site_name']);?>" lang="false" onblur="validateText(this);" class="text-long" />&nbsp;<?php echo _("the name of this web site");?>
</p>
<p>
	<label for="admin_name"><?php echo _("Admin Name");?>:</label><br />
    <input type="text" size="50" name="admin_name" id="admin_name" value="<?php echo $this->ctf_output_string($fsc_site['admin_name']);?>" lang="false" onblur="validateText(this);" class="text-long" />&nbsp;<?php echo _("for notifications, and set as recipient name in the emails sent from the site");?>.
</p>
<p>
	<label for="admin_email"><?php echo _("Email address");?>:</label><br />
    <?php
    if ( !$this->ctf_validate_email($fsc_site['admin_email']) ) {
      echo '<span style="color:red;">'. _('ERROR: Misconfigured E-mail address.').'</span><br />'."\n";
    }
    ?>
	<input type="text" size="50" name="admin_email" id="admin_email" value="<?php echo $this->ctf_output_string($fsc_site['admin_email']);?>" lang="false" onblur="validateEmail(this);" class="text-long" />&nbsp;<?php echo _("for notifications, and set as recipient email in the emails sent from the site");?>.
</p>
<p>
	<label for="admin_usr"><?php echo _("Admin Login User");?>:</label><br />
    <input type="text" name="admin_usr" id="admin_usr" value="<?php echo $this->ctf_output_string($fsc_site['admin_usr']);?>" lang="false" onblur="validateText(this);" class="text-long" />&nbsp;<?php echo _("needed to login to change settings");?>
</p>
<p>
	<label for="admin_pwd"><?php echo _("Admin Login Password");?>:</label><br />
	<input type="text" name="admin_pwd" id="admin_pwd" value="" class="text-long" />&nbsp;<span style="color:red;"><?php echo _("leave blank unless you want to change it.");?></span> <?php echo _("write this down and remember!");?>
</p>
<p>
	<label for="site_charset"><?php echo _("Site Character Encoding");?>:</label><br />
    <select name="site_charset" id="site_charset">
    <?php
        foreach (array ('UTF-8','ISO-8859-1','ISO-8859-2') as $site_charset) {
            if ($fsc_site['site_charset'] == $site_charset) {
                   echo '<option value="'.$site_charset.'" selected="selected">'.$site_charset.'</option>';
            } else {
                   echo '<option value="'.$site_charset.'">'.$site_charset.'</option>';
            }
        }
    ?>
	</select>&nbsp;<?php echo _("The character encoding of your site (UTF-8 is recommended)");?>
    <a href="http://www.w3.org/International/O-HTTP-charset" target="_blank">Setting the HTTP charset parameter.</a>
</p>
<p>
	<label for="language"><?php echo _("Site language");?>:</label><br />
   <select name="language" id="language">
	    <?php
	    $languages = scandir('../languages');
        if(!in_array('en_US',$languages) ) {
           $languages[] = 'en_US';
           sort($languages);
        }
	    foreach ($languages as $lang) {
            if( strpos($lang,'.')==false && $lang!='.' && $lang!='..' && !preg_match("/^_/",$lang)){
               if ($fsc_site['language'] == $lang) {
                   echo "<option value=\"$lang\" selected=\"selected\">$lang</option>";
               } else {
                   echo "<option value=\"$lang\">$lang</option>";
               }
	     	}
	    }
	    ?>
	</select>&nbsp;<?php echo _("you can add more languages in");?> /contact-files/languages
<?php
if(!function_exists('mb_detect_encoding') ) {
		echo '<br /><span style="color:red;">'._('Warning: Your PHP web server is lacking support for the function: mb_detect_encoding.').' '.
    _('You can ignore this warning if you are only going to use the en_US language.').' '.
    sprintf( _('In order to use languages other than en_US, you will have to add the <a href="%s">mbstring extension</a> to PHP.'),'http://www.php.net/manual/en/mbstring.installation.php' ).'</span><br />';
}
?>
</p>
<p>
	<label for="timezone"><?php echo _("Time Zone");?>:</label><br />
    <select name="timezone" id="timezone">
	<?php
/*	$timezone_identifiers = DateTimeZone::listIdentifiers();
	foreach( $timezone_identifiers as $value ){
		if ( preg_match( '/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific|Australia)\//', $value ) ){
	    	$ex=explode("/",$value);//obtain continent,city
	    	if ($continent!=$ex[0]){
	    		if ($continent!="") echo '</optgroup>';
	    		echo '<optgroup label="'.$ex[0].'">';
	    	}
	    	$city=$ex[1];
	    	$continent=$ex[0];
            if ($fsc_site['timezone'] == $value) {
                   echo '<option value="'.$value.'" selected="selected">'.$city.'</option>';
            } else {
                   echo '<option value="'.$value.'">'.$city.'</option>';
            }
	    }
	}*/

    $timezones = array (
  0 => 'America/Adak',
  1 => 'America/Anchorage',
  2 => 'America/Anguilla',
  3 => 'America/Antigua',
  4 => 'America/Araguaina',
  5 => 'America/Argentina/Buenos_Aires',
  6 => 'America/Argentina/Catamarca',
  7 => 'America/Argentina/ComodRivadavia',
  8 => 'America/Argentina/Cordoba',
  9 => 'America/Argentina/Jujuy',
  10 => 'America/Argentina/La_Rioja',
  11 => 'America/Argentina/Mendoza',
  12 => 'America/Argentina/Rio_Gallegos',
  13 => 'America/Argentina/San_Juan',
  14 => 'America/Argentina/San_Luis',
  15 => 'America/Argentina/Tucuman',
  16 => 'America/Argentina/Ushuaia',
  17 => 'America/Aruba',
  18 => 'America/Asuncion',
  19 => 'America/Atikokan',
  20 => 'America/Atka',
  21 => 'America/Bahia',
  22 => 'America/Barbados',
  23 => 'America/Belem',
  24 => 'America/Belize',
  25 => 'America/Blanc-Sablon',
  26 => 'America/Boa_Vista',
  27 => 'America/Bogota',
  28 => 'America/Boise',
  29 => 'America/Buenos_Aires',
  30 => 'America/Cambridge_Bay',
  31 => 'America/Campo_Grande',
  32 => 'America/Cancun',
  33 => 'America/Caracas',
  34 => 'America/Catamarca',
  35 => 'America/Cayenne',
  36 => 'America/Cayman',
  37 => 'America/Chicago',
  38 => 'America/Chihuahua',
  39 => 'America/Coral_Harbour',
  40 => 'America/Cordoba',
  41 => 'America/Costa_Rica',
  42 => 'America/Cuiaba',
  43 => 'America/Curacao',
  44 => 'America/Danmarkshavn',
  45 => 'America/Dawson',
  46 => 'America/Dawson_Creek',
  47 => 'America/Denver',
  48 => 'America/Detroit',
  49 => 'America/Dominica',
  50 => 'America/Edmonton',
  51 => 'America/Eirunepe',
  52 => 'America/El_Salvador',
  53 => 'America/Ensenada',
  54 => 'America/Fort_Wayne',
  55 => 'America/Fortaleza',
  56 => 'America/Glace_Bay',
  57 => 'America/Godthab',
  58 => 'America/Goose_Bay',
  59 => 'America/Grand_Turk',
  60 => 'America/Grenada',
  61 => 'America/Guadeloupe',
  62 => 'America/Guatemala',
  63 => 'America/Guayaquil',
  64 => 'America/Guyana',
  65 => 'America/Halifax',
  66 => 'America/Havana',
  67 => 'America/Hermosillo',
  68 => 'America/Indiana/Indianapolis',
  69 => 'America/Indiana/Knox',
  70 => 'America/Indiana/Marengo',
  71 => 'America/Indiana/Petersburg',
  72 => 'America/Indiana/Tell_City',
  73 => 'America/Indiana/Vevay',
  74 => 'America/Indiana/Vincennes',
  75 => 'America/Indiana/Winamac',
  76 => 'America/Indianapolis',
  77 => 'America/Inuvik',
  78 => 'America/Iqaluit',
  79 => 'America/Jamaica',
  80 => 'America/Jujuy',
  81 => 'America/Juneau',
  82 => 'America/Kentucky/Louisville',
  83 => 'America/Kentucky/Monticello',
  84 => 'America/Knox_IN',
  85 => 'America/La_Paz',
  86 => 'America/Lima',
  87 => 'America/Los_Angeles',
  88 => 'America/Louisville',
  89 => 'America/Maceio',
  90 => 'America/Managua',
  91 => 'America/Manaus',
  92 => 'America/Marigot',
  93 => 'America/Martinique',
  94 => 'America/Mazatlan',
  95 => 'America/Mendoza',
  96 => 'America/Menominee',
  97 => 'America/Merida',
  98 => 'America/Mexico_City',
  99 => 'America/Miquelon',
  100 => 'America/Moncton',
  101 => 'America/Monterrey',
  102 => 'America/Montevideo',
  103 => 'America/Montreal',
  104 => 'America/Montserrat',
  105 => 'America/Nassau',
  106 => 'America/New_York',
  107 => 'America/Nipigon',
  108 => 'America/Nome',
  109 => 'America/Noronha',
  110 => 'America/North_Dakota/Center',
  111 => 'America/North_Dakota/New_Salem',
  112 => 'America/Panama',
  113 => 'America/Pangnirtung',
  114 => 'America/Paramaribo',
  115 => 'America/Phoenix',
  116 => 'America/Port-au-Prince',
  117 => 'America/Port_of_Spain',
  118 => 'America/Porto_Acre',
  119 => 'America/Porto_Velho',
  120 => 'America/Puerto_Rico',
  121 => 'America/Rainy_River',
  122 => 'America/Rankin_Inlet',
  123 => 'America/Recife',
  124 => 'America/Regina',
  125 => 'America/Resolute',
  126 => 'America/Rio_Branco',
  127 => 'America/Rosario',
  128 => 'America/Santiago',
  129 => 'America/Santo_Domingo',
  130 => 'America/Sao_Paulo',
  131 => 'America/Scoresbysund',
  132 => 'America/Shiprock',
  133 => 'America/St_Barthelemy',
  134 => 'America/St_Johns',
  135 => 'America/St_Kitts',
  136 => 'America/St_Lucia',
  137 => 'America/St_Thomas',
  138 => 'America/St_Vincent',
  139 => 'America/Swift_Current',
  140 => 'America/Tegucigalpa',
  141 => 'America/Thule',
  142 => 'America/Thunder_Bay',
  143 => 'America/Tijuana',
  144 => 'America/Toronto',
  145 => 'America/Tortola',
  146 => 'America/Vancouver',
  147 => 'America/Virgin',
  148 => 'America/Whitehorse',
  149 => 'America/Winnipeg',
  150 => 'America/Yakutat',
  151 => 'America/Yellowknife',
  152 => 'Arctic/Longyearbyen',
  153 => 'Asia/Aden',
  154 => 'Asia/Almaty',
  155 => 'Asia/Amman',
  156 => 'Asia/Anadyr',
  157 => 'Asia/Aqtau',
  158 => 'Asia/Aqtobe',
  159 => 'Asia/Ashgabat',
  160 => 'Asia/Ashkhabad',
  161 => 'Asia/Baghdad',
  162 => 'Asia/Bahrain',
  163 => 'Asia/Baku',
  164 => 'Asia/Bangkok',
  165 => 'Asia/Beirut',
  166 => 'Asia/Bishkek',
  167 => 'Asia/Brunei',
  168 => 'Asia/Calcutta',
  169 => 'Asia/Choibalsan',
  170 => 'Asia/Chongqing',
  171 => 'Asia/Chungking',
  172 => 'Asia/Colombo',
  173 => 'Asia/Dacca',
  174 => 'Asia/Damascus',
  175 => 'Asia/Dhaka',
  176 => 'Asia/Dili',
  177 => 'Asia/Dubai',
  178 => 'Asia/Dushanbe',
  179 => 'Asia/Gaza',
  180 => 'Asia/Harbin',
  181 => 'Asia/Ho_Chi_Minh',
  182 => 'Asia/Hong_Kong',
  183 => 'Asia/Hovd',
  184 => 'Asia/Irkutsk',
  185 => 'Asia/Istanbul',
  186 => 'Asia/Jakarta',
  187 => 'Asia/Jayapura',
  188 => 'Asia/Jerusalem',
  189 => 'Asia/Kabul',
  190 => 'Asia/Kamchatka',
  191 => 'Asia/Karachi',
  192 => 'Asia/Kashgar',
  193 => 'Asia/Katmandu',
  194 => 'Asia/Kolkata',
  195 => 'Asia/Krasnoyarsk',
  196 => 'Asia/Kuala_Lumpur',
  197 => 'Asia/Kuching',
  198 => 'Asia/Kuwait',
  199 => 'Asia/Macao',
  200 => 'Asia/Macau',
  201 => 'Asia/Magadan',
  202 => 'Asia/Makassar',
  203 => 'Asia/Manila',
  204 => 'Asia/Muscat',
  205 => 'Asia/Nicosia',
  206 => 'Asia/Novosibirsk',
  207 => 'Asia/Omsk',
  208 => 'Asia/Oral',
  209 => 'Asia/Phnom_Penh',
  210 => 'Asia/Pontianak',
  211 => 'Asia/Pyongyang',
  212 => 'Asia/Qatar',
  213 => 'Asia/Qyzylorda',
  214 => 'Asia/Rangoon',
  215 => 'Asia/Riyadh',
  216 => 'Asia/Saigon',
  217 => 'Asia/Sakhalin',
  218 => 'Asia/Samarkand',
  219 => 'Asia/Seoul',
  220 => 'Asia/Shanghai',
  221 => 'Asia/Singapore',
  222 => 'Asia/Taipei',
  223 => 'Asia/Tashkent',
  224 => 'Asia/Tbilisi',
  225 => 'Asia/Tehran',
  226 => 'Asia/Tel_Aviv',
  227 => 'Asia/Thimbu',
  228 => 'Asia/Thimphu',
  229 => 'Asia/Tokyo',
  230 => 'Asia/Ujung_Pandang',
  231 => 'Asia/Ulaanbaatar',
  232 => 'Asia/Ulan_Bator',
  233 => 'Asia/Urumqi',
  234 => 'Asia/Vientiane',
  235 => 'Asia/Vladivostok',
  236 => 'Asia/Yakutsk',
  237 => 'Asia/Yekaterinburg',
  238 => 'Asia/Yerevan',
  239 => 'Atlantic/Azores',
  240 => 'Atlantic/Bermuda',
  241 => 'Atlantic/Canary',
  242 => 'Atlantic/Cape_Verde',
  243 => 'Atlantic/Faeroe',
  244 => 'Atlantic/Faroe',
  245 => 'Atlantic/Jan_Mayen',
  246 => 'Atlantic/Madeira',
  247 => 'Atlantic/Reykjavik',
  248 => 'Atlantic/South_Georgia',
  249 => 'Atlantic/St_Helena',
  250 => 'Atlantic/Stanley',
  251 => 'Australia/ACT',
  252 => 'Australia/Adelaide',
  253 => 'Australia/Brisbane',
  254 => 'Australia/Broken_Hill',
  255 => 'Australia/Canberra',
  256 => 'Australia/Currie',
  257 => 'Australia/Darwin',
  258 => 'Australia/Eucla',
  259 => 'Australia/Hobart',
  260 => 'Australia/LHI',
  261 => 'Australia/Lindeman',
  262 => 'Australia/Lord_Howe',
  263 => 'Australia/Melbourne',
  264 => 'Australia/North',
  265 => 'Australia/NSW',
  266 => 'Australia/Perth',
  267 => 'Australia/Queensland',
  268 => 'Australia/South',
  269 => 'Australia/Sydney',
  270 => 'Australia/Tasmania',
  271 => 'Australia/Victoria',
  272 => 'Australia/West',
  273 => 'Australia/Yancowinna',
  274 => 'Europe/Amsterdam',
  275 => 'Europe/Andorra',
  276 => 'Europe/Athens',
  277 => 'Europe/Belfast',
  278 => 'Europe/Belgrade',
  279 => 'Europe/Berlin',
  280 => 'Europe/Bratislava',
  281 => 'Europe/Brussels',
  282 => 'Europe/Bucharest',
  283 => 'Europe/Budapest',
  284 => 'Europe/Chisinau',
  285 => 'Europe/Copenhagen',
  286 => 'Europe/Dublin',
  287 => 'Europe/Gibraltar',
  288 => 'Europe/Guernsey',
  289 => 'Europe/Helsinki',
  290 => 'Europe/Isle_of_Man',
  291 => 'Europe/Istanbul',
  292 => 'Europe/Jersey',
  293 => 'Europe/Kaliningrad',
  294 => 'Europe/Kiev',
  295 => 'Europe/Lisbon',
  296 => 'Europe/Ljubljana',
  297 => 'Europe/London',
  298 => 'Europe/Luxembourg',
  299 => 'Europe/Madrid',
  300 => 'Europe/Malta',
  301 => 'Europe/Mariehamn',
  302 => 'Europe/Minsk',
  303 => 'Europe/Monaco',
  304 => 'Europe/Moscow',
  305 => 'Europe/Nicosia',
  306 => 'Europe/Oslo',
  307 => 'Europe/Paris',
  308 => 'Europe/Podgorica',
  309 => 'Europe/Prague',
  310 => 'Europe/Riga',
  311 => 'Europe/Rome',
  312 => 'Europe/Samara',
  313 => 'Europe/San_Marino',
  314 => 'Europe/Sarajevo',
  315 => 'Europe/Simferopol',
  316 => 'Europe/Skopje',
  317 => 'Europe/Sofia',
  318 => 'Europe/Stockholm',
  319 => 'Europe/Tallinn',
  320 => 'Europe/Tirane',
  321 => 'Europe/Tiraspol',
  322 => 'Europe/Uzhgorod',
  323 => 'Europe/Vaduz',
  324 => 'Europe/Vatican',
  325 => 'Europe/Vienna',
  326 => 'Europe/Vilnius',
  327 => 'Europe/Volgograd',
  328 => 'Europe/Warsaw',
  329 => 'Europe/Zagreb',
  330 => 'Europe/Zaporozhye',
  331 => 'Europe/Zurich',
  332 => 'Indian/Antananarivo',
  333 => 'Indian/Chagos',
  334 => 'Indian/Christmas',
  335 => 'Indian/Cocos',
  336 => 'Indian/Comoro',
  337 => 'Indian/Kerguelen',
  338 => 'Indian/Mahe',
  339 => 'Indian/Maldives',
  340 => 'Indian/Mauritius',
  341 => 'Indian/Mayotte',
  342 => 'Indian/Reunion',
  343 => 'Pacific/Apia',
  344 => 'Pacific/Auckland',
  345 => 'Pacific/Chatham',
  346 => 'Pacific/Easter',
  347 => 'Pacific/Efate',
  348 => 'Pacific/Enderbury',
  349 => 'Pacific/Fakaofo',
  350 => 'Pacific/Fiji',
  351 => 'Pacific/Funafuti',
  352 => 'Pacific/Galapagos',
  353 => 'Pacific/Gambier',
  354 => 'Pacific/Guadalcanal',
  355 => 'Pacific/Guam',
  356 => 'Pacific/Honolulu',
  357 => 'Pacific/Johnston',
  358 => 'Pacific/Kiritimati',
  359 => 'Pacific/Kosrae',
  360 => 'Pacific/Kwajalein',
  361 => 'Pacific/Majuro',
  362 => 'Pacific/Marquesas',
  363 => 'Pacific/Midway',
  364 => 'Pacific/Nauru',
  365 => 'Pacific/Niue',
  366 => 'Pacific/Norfolk',
  367 => 'Pacific/Noumea',
  368 => 'Pacific/Pago_Pago',
  369 => 'Pacific/Palau',
  370 => 'Pacific/Pitcairn',
  371 => 'Pacific/Ponape',
  372 => 'Pacific/Port_Moresby',
  373 => 'Pacific/Rarotonga',
  374 => 'Pacific/Saipan',
  375 => 'Pacific/Samoa',
  376 => 'Pacific/Tahiti',
  377 => 'Pacific/Tarawa',
  378 => 'Pacific/Tongatapu',
  379 => 'Pacific/Truk',
  380 => 'Pacific/Wake',
  381 => 'Pacific/Wallis',
  382 => 'Pacific/Yap',

);

	foreach( $timezones as $value ){
	   if ($fsc_site['timezone'] == $value) {
                echo '<option value="'.$value.'" selected="selected">'.$value.'</option>';
       } else {
                echo '<option value="'.$value.'">'.$value.'</option>';
       }
	}
	?>
	</select>

</p>
</fieldset>
<p>
<input type="hidden" name="ctf_action" value="update" />
<input type="submit" name="submit" id="submit" value="<?php echo _('Update');?>" class="button-submit" />
</p>
</form>
<?php

 }// end show site settings



  if ($passed_login && !isset($_GET['site_settings']) && isset($_POST['submit']) && !isset($_POST['ctf_action'])) {

   // post changes to the options array
   $fsc_gb_update = array(
         'donated' =>          (isset( $_POST['fsc_donated'] ) ) ? 'true' : 'false',
         'max_forms' =>    ( is_numeric(trim($_POST['fsc_max_forms'])) && trim($_POST['fsc_max_forms']) < 100 ) ? $this->absint(trim($_POST['fsc_max_forms'])) : $fsc_gb['max_forms'],
         'max_fields' =>   $fsc_gb['max_fields'],
         'akismet_enable'         =>    (isset( $_POST['fsc_akismet_enable'] ) ) ? 'true' : 'false',
         'akismet_api_key' =>             trim($_POST['fsc_akismet_api_key']),  // can be empty
         );


   $fsc_opt_update = array(
         'form_name' =>           trim($_POST['fsc_form_name']),  // can be empty
         'welcome' =>             trim($_POST['fsc_welcome']),  // can be empty
         'email_to' =>          ( trim($_POST['fsc_email_to']) != '' ) ? trim($_POST['fsc_email_to']) : $fsc_opt_defaults['email_to'], // use default if empty
         'php_mailer_enable' =>        $_POST['fsc_php_mailer_enable'],
         'email_from' =>          trim($_POST['fsc_email_from']),
         'email_from_enforced' => (isset( $_POST['fsc_email_from_enforced'] ) ) ? 'true' : 'false',
         'email_bcc' =>           trim($_POST['fsc_email_bcc']),
         'email_reply_to' =>      trim($_POST['fsc_email_reply_to']),
         'email_subject' =>     ( trim($_POST['fsc_email_subject']) != '' ) ? trim($_POST['fsc_email_subject']) : '',
         'email_subject_list' =>  trim($_POST['fsc_email_subject_list']),
         'name_format' =>           $_POST['fsc_name_format'],
         'name_type' =>             $_POST['fsc_name_type'],
         'email_type' =>            $_POST['fsc_email_type'],
         'subject_type' =>          $_POST['fsc_subject_type'],
         'message_type' =>          $_POST['fsc_message_type'],
         'preserve_space_enable' => (isset( $_POST['fsc_preserve_space_enable'] ) ) ? 'true' : 'false',
         'max_fields' =>   ( is_numeric(trim($_POST['fsc_max_fields'])) && trim($_POST['fsc_max_fields']) < 200 ) ? $this->absint(trim($_POST['fsc_max_fields'])) : $fsc_gb['max_fields'],
         'double_email' =>     (isset( $_POST['fsc_double_email'] ) ) ? 'true' : 'false', // true or false
         'name_case_enable' => (isset( $_POST['fsc_name_case_enable'] ) ) ? 'true' : 'false',
         'sender_info_enable' =>   (isset( $_POST['fsc_sender_info_enable'] ) ) ? 'true' : 'false',
         'domain_protect' =>   (isset( $_POST['fsc_domain_protect'] ) ) ? 'true' : 'false',
         'email_check_dns' =>  (isset( $_POST['fsc_email_check_dns'] ) ) ? 'true' : 'false',
         'email_html' =>       (isset( $_POST['fsc_email_html'] ) ) ? 'true' : 'false',
         'smtp_enable' =>      (isset( $_POST['fsc_smtp_enable'] ) ) ? 'true' : 'false',
         'smtp_host' =>          trim(strtolower($_POST['fsc_smtp_host'])),
         'smtp_encryption' =>    trim(strtolower($_POST['fsc_smtp_encryption'])),
         'smtp_port' => $this->absint(trim($_POST['fsc_smtp_port'])),
         'smtp_auth_enable' => (isset( $_POST['fsc_smtp_auth_enable'] ) ) ? 'true' : 'false',
         'smtp_user' =>           trim($_POST['fsc_smtp_user']),
         'smtp_pass' =>           trim($_POST['fsc_smtp_pass']),
         'akismet_disable' =>  (isset( $_POST['fsc_akismet_disable'] ) ) ? 'true' : 'false',
         'akismet_send_anyway' =>  $_POST['fsc_akismet_send_anyway'],
         'captcha_enable' =>   (isset( $_POST['fsc_captcha_enable'] ) ) ? 'true' : 'false',
         'captcha_difficulty' =>  $_POST['fsc_captcha_difficulty'],
         'captcha_small' =>     (isset( $_POST['fsc_captcha_small'] ) ) ? 'true' : 'false',
         'captcha_no_trans' =>    (isset( $_POST['fsc_captcha_no_trans'] ) ) ? 'true' : 'false',
         'enable_audio' =>        (isset( $_POST['fsc_enable_audio'] ) ) ? 'true' : 'false',
         'enable_audio_flash' => (isset( $_POST['fsc_enable_audio_flash'] ) ) ? 'true' : 'false',
         'redirect_enable' =>  (isset( $_POST['fsc_redirect_enable'] ) ) ? 'true' : 'false',
         'redirect_seconds' => ( is_numeric(trim($_POST['fsc_redirect_seconds'])) && trim($_POST['fsc_redirect_seconds']) < 61 ) ? $this->absint(trim($_POST['fsc_redirect_seconds'])) : $fsc_opt_defaults['redirect_seconds'],
         'redirect_url' =>        trim($_POST['fsc_redirect_url']),
         'redirect_query' =>  (isset( $_POST['fsc_redirect_query'] ) ) ? 'true' : 'false',
         'redirect_ignore' =>        trim($_POST['fsc_redirect_ignore']),
         'redirect_rename' =>        trim($_POST['fsc_redirect_rename']),
         'redirect_add' =>           trim($_POST['fsc_redirect_add']),
         'redirect_email_off' =>  (isset( $_POST['fsc_redirect_email_off'] ) ) ? 'true' : 'false',
         'silent_send' =>             $_POST['fsc_silent_send'],
         'silent_url' =>         trim($_POST['fsc_silent_url']),
         'silent_ignore' =>      trim($_POST['fsc_silent_ignore']),
         'silent_rename' =>      trim($_POST['fsc_silent_rename']),
         'silent_add' =>          trim($_POST['fsc_silent_add']),
         'silent_email_off' =>  (isset( $_POST['fsc_silent_email_off'] ) ) ? 'true' : 'false',
         'border_enable' =>    (isset( $_POST['fsc_border_enable'] ) ) ? 'true' : 'false',
         'ex_fields_after_msg' => (isset( $_POST['fsc_ex_fields_after_msg'] ) ) ? 'true' : 'false',
         'date_format' =>               $_POST['fsc_date_format'],
         'cal_start_day' =>     ( preg_match('/^[0-6]?$/',$_POST['fsc_cal_start_day']) ) ? trim($_POST['fsc_cal_start_day']) : $fsc_opt_defaults['cal_start_day'],
         'time_format' =>               $_POST['fsc_time_format'],
         'attach_types' =>      trim(str_replace('.','',$_POST['fsc_attach_types'])),
         'attach_size' =>       ( preg_match('/^([[0-9.]+)([kKmM]?[bB])?$/',$_POST['fsc_attach_size']) ) ? trim($_POST['fsc_attach_size']) : $fsc_opt_defaults['attach_size'],
         'textarea_html_allow' =>    (isset( $_POST['fsc_textarea_html_allow'] ) ) ? 'true' : 'false',
         'enable_areyousure' =>    (isset( $_POST['fsc_enable_areyousure'] ) ) ? 'true' : 'false',
         'auto_respond_enable' =>    (isset( $_POST['fsc_auto_respond_enable'] ) ) ? 'true' : 'false',
         'auto_respond_html' =>      (isset( $_POST['fsc_auto_respond_html'] ) ) ? 'true' : 'false',
         'auto_respond_from_name' => ( trim($_POST['fsc_auto_respond_from_name']) != '' ) ? trim($_POST['fsc_auto_respond_from_name']) : $fsc_opt_defaults['auto_respond_from_name'], // use default if empty
         'auto_respond_from_email' =>  ( trim($_POST['fsc_auto_respond_from_email']) != '' && $this->ctf_validate_email($_POST['fsc_auto_respond_from_email'])) ? trim($_POST['fsc_auto_respond_from_email']) : $fsc_opt_defaults['auto_respond_from_email'], // use default if empty
         'auto_respond_reply_to' =>  ( trim($_POST['fsc_auto_respond_reply_to']) != '' && $this->ctf_validate_email($_POST['fsc_auto_respond_reply_to'])) ? trim($_POST['fsc_auto_respond_reply_to']) : $fsc_opt_defaults['auto_respond_reply_to'], // use default if empty
         'auto_respond_message' => trim($_POST['fsc_auto_respond_message']),  // can be empty
         'auto_respond_subject' => trim($_POST['fsc_auto_respond_subject']),  // can be empty
         'req_field_indicator' =>       $_POST['fsc_req_field_indicator'],
         'req_field_label_enable' =>    (isset( $_POST['fsc_req_field_label_enable'] ) ) ? 'true' : 'false',
         'req_field_indicator_enable' =>    (isset( $_POST['fsc_req_field_indicator_enable'] ) ) ? 'true' : 'false',
         'form_style' =>          ( trim($_POST['fsc_form_style']) != '' ) ? trim($_POST['fsc_form_style']) : $fsc_opt_defaults['form_style'],
         'border_style' =>          ( trim($_POST['fsc_border_style']) != '' ) ? trim($_POST['fsc_border_style']) : $fsc_opt_defaults['border_style'],
         'required_style' =>      ( trim($_POST['fsc_required_style']) != '' ) ? trim($_POST['fsc_required_style']) : $fsc_opt_defaults['required_style'],
         'notes_style' =>         ( trim($_POST['fsc_notes_style']) != '' ) ? trim($_POST['fsc_notes_style']) : $fsc_opt_defaults['notes_style'],
         'title_style' =>         ( trim($_POST['fsc_title_style']) != '' ) ? trim($_POST['fsc_title_style']) : $fsc_opt_defaults['title_style'],
         'select_style' =>        ( trim($_POST['fsc_select_style']) != '' ) ? trim($_POST['fsc_select_style']) : $fsc_opt_defaults['select_style'],
         'field_style' =>         ( trim($_POST['fsc_field_style']) != '' ) ? trim($_POST['fsc_field_style']) : $fsc_opt_defaults['field_style'],
         'field_div_style' =>     ( trim($_POST['fsc_field_div_style']) != '' ) ? trim($_POST['fsc_field_div_style']) : $fsc_opt_defaults['field_div_style'],
         'error_style' =>         ( trim($_POST['fsc_error_style']) != '' ) ? trim($_POST['fsc_error_style']) : $fsc_opt_defaults['error_style'],
         'captcha_div_style_sm' =>   ( trim($_POST['fsc_captcha_div_style_sm']) != '' ) ? trim($_POST['fsc_captcha_div_style_sm']) : $fsc_opt_defaults['captcha_div_style_sm'],
         'captcha_div_style_m' =>   ( trim($_POST['fsc_captcha_div_style_m']) != '' ) ? trim($_POST['fsc_captcha_div_style_m']) : $fsc_opt_defaults['captcha_div_style_m'],
         'captcha_input_style' =>   ( trim($_POST['fsc_captcha_input_style']) != '' ) ? trim($_POST['fsc_captcha_input_style']) : $fsc_option_defaults['captcha_input_style'],
         'submit_div_style' =>        ( trim($_POST['fsc_submit_div_style']) != '' ) ? trim($_POST['fsc_submit_div_style']) : $fsc_opt_defaults['submit_div_style'],
         'button_style' =>        ( trim($_POST['fsc_button_style']) != '' ) ? trim($_POST['fsc_button_style']) : $fsc_opt_defaults['button_style'],
         'reset_style' =>        ( trim($_POST['fsc_reset_style']) != '' ) ? trim($_POST['fsc_reset_style']) : $fsc_opt_defaults['reset_style'],
         'powered_by_style' =>    ( trim($_POST['fsc_powered_by_style']) != '' ) ? trim($_POST['fsc_powered_by_style']) : $fsc_opt_defaults['powered_by_style'],
         'field_size' => ( is_numeric(trim($_POST['fsc_field_size'])) && trim($_POST['fsc_field_size']) > 14 ) ? $this->absint(trim($_POST['fsc_field_size'])) : $fsc_opt_defaults['field_size'], // use default if empty
         'captcha_field_size' => ( is_numeric(trim($_POST['fsc_captcha_field_size'])) && trim($_POST['fsc_captcha_field_size']) > 4 ) ? $this->absint(trim($_POST['fsc_captcha_field_size'])) : $fsc_opt_defaults['captcha_field_size'],
         'text_cols' =>    $this->absint(trim($_POST['fsc_text_cols'])),
         'text_rows' =>    $this->absint(trim($_POST['fsc_text_rows'])),
         'aria_required' =>    (isset( $_POST['fsc_aria_required'] ) ) ? 'true' : 'false',
         'title_border' =>        trim($_POST['fsc_title_border']),
         'title_dept' =>          trim($_POST['fsc_title_dept']),
         'title_select' =>        trim($_POST['fsc_title_select']),
         'title_name' =>          trim($_POST['fsc_title_name']),
         'title_fname' =>         trim($_POST['fsc_title_fname']),
         'title_lname' =>         trim($_POST['fsc_title_lname']),
         'title_mname' =>         trim($_POST['fsc_title_mname']),
         'title_miname' =>        trim($_POST['fsc_title_miname']),
         'title_email' =>         trim($_POST['fsc_title_email']),
         'title_email2' =>        trim($_POST['fsc_title_email2']),
         'title_email2_help' =>   trim($_POST['fsc_title_email2_help']),
         'title_subj' =>          trim($_POST['fsc_title_subj']),
         'title_mess' =>          trim($_POST['fsc_title_mess']),
         'title_capt' =>          trim($_POST['fsc_title_capt']),
         'title_submit' =>        trim($_POST['fsc_title_submit']),
         'title_reset' =>         trim($_POST['fsc_title_reset']),
         'title_areyousure' =>    trim($_POST['fsc_title_areyousure']),
         'text_message_sent' =>   trim($_POST['fsc_text_message_sent']),
         'tooltip_required' =>    $_POST['fsc_tooltip_required'],
         'tooltip_captcha' =>     trim($_POST['fsc_tooltip_captcha']),
         'tooltip_audio' =>       trim($_POST['fsc_tooltip_audio']),
         'tooltip_refresh' =>     trim($_POST['fsc_tooltip_refresh']),
         'tooltip_filetypes' =>   trim($_POST['fsc_tooltip_filetypes']),
         'tooltip_filesize' =>    trim($_POST['fsc_tooltip_filesize']),
         'enable_reset' => (isset( $_POST['fsc_enable_reset'] ) ) ? 'true' : 'false',
         'enable_credit_link' => (isset( $_POST['fsc_enable_credit_link'] ) ) ? 'true' : 'false',
         'error_contact_select' => trim($_POST['fsc_error_contact_select']),
         'error_name'           => trim($_POST['fsc_error_name']),
         'error_email'          => trim($_POST['fsc_error_email']),
         'error_email2'         => trim($_POST['fsc_error_email2']),
         'error_field'          => trim($_POST['fsc_error_field']),
         'error_subject'        => trim($_POST['fsc_error_subject']),
         'error_message'        => trim($_POST['fsc_error_message']),
         'error_input'          => trim($_POST['fsc_error_input']),
         'error_captcha_blank'  => trim($_POST['fsc_error_captcha_blank']),
         'error_captcha_wrong'  => trim($_POST['fsc_error_captcha_wrong']),
         'error_correct'        => trim($_POST['fsc_error_correct']),
  );

    // optional extra fields
    for ($i = 1; $i <= $fsc_opt_update['max_fields']; $i++) {
        $fsc_opt_update['ex_field'.$i.'_label'] = (isset($_POST['fsc_ex_field'.$i.'_label'])) ? trim($_POST['fsc_ex_field'.$i.'_label']) : '';
        $fsc_opt_update['ex_field'.$i.'_type'] = (isset($_POST['fsc_ex_field'.$i.'_type'])) ? trim($_POST['fsc_ex_field'.$i.'_type']) : 'text';
        $fsc_opt_update['ex_field'.$i.'_default'] = ( isset($_POST['fsc_ex_field'.$i.'_default']) && is_numeric(trim($_POST['fsc_ex_field'.$i.'_default'])) && trim($_POST['fsc_ex_field'.$i.'_default']) >= 0 ) ? $this->absint(trim($_POST['fsc_ex_field'.$i.'_default'])) : '0'; // use default if empty
        $fsc_opt_update['ex_field'.$i.'_default_text'] = (isset($_POST['fsc_ex_field'.$i.'_default_text'])) ? trim($_POST['fsc_ex_field'.$i.'_default_text']) : '';
        $fsc_opt_update['ex_field'.$i.'_max_len'] = ( isset($_POST['fsc_ex_field'.$i.'_max_len']) && is_numeric(trim($_POST['fsc_ex_field'.$i.'_max_len'])) && trim($_POST['fsc_ex_field'.$i.'_max_len']) > 0 ) ? $this->absint(trim($_POST['fsc_ex_field'.$i.'_max_len'])) : '';
        $fsc_opt_update['ex_field'.$i.'_label_css'] = (isset($_POST['fsc_ex_field'.$i.'_label_css'])) ? trim($_POST['fsc_ex_field'.$i.'_label_css']) : '';
        $fsc_opt_update['ex_field'.$i.'_input_css'] = (isset($_POST['fsc_ex_field'.$i.'_input_css'])) ? trim($_POST['fsc_ex_field'.$i.'_input_css']) : '';
        $fsc_opt_update['ex_field'.$i.'_attributes'] = (isset($_POST['fsc_ex_field'.$i.'_attributes'])) ? trim($_POST['fsc_ex_field'.$i.'_attributes']) : '';
        $fsc_opt_update['ex_field'.$i.'_regex'] = (isset($_POST['fsc_ex_field'.$i.'_regex'])) ? trim($_POST['fsc_ex_field'.$i.'_regex']) : '';
        $fsc_opt_update['ex_field'.$i.'_regex_error'] = (isset($_POST['fsc_ex_field'.$i.'_regex_error'])) ? trim($_POST['fsc_ex_field'.$i.'_regex_error']) : '';
        $fsc_opt_update['ex_field'.$i.'_req'] = (isset( $_POST['fsc_ex_field'.$i.'_req'] ) ) ? 'true' : 'false';
        $fsc_opt_update['ex_field'.$i.'_notes'] = (isset($_POST['fsc_ex_field'.$i.'_notes'])) ? trim($_POST['fsc_ex_field'.$i.'_notes']) : '';
        $fsc_opt_update['ex_field'.$i.'_notes_after'] = (isset($_POST['fsc_ex_field'.$i.'_notes_after'])) ? trim($_POST['fsc_ex_field'.$i.'_notes_after']) : '';
        if ($fsc_opt_update['ex_field'.$i.'_label'] != '' && !in_array($fsc_opt_update['ex_field'.$i.'_type'], array('checkbox','checkbox-multiple','radio','select','select-multiple'))) {
                $fsc_opt_update['ex_field'.$i.'_default'] = '0';
        }
        if ($fsc_opt_update['ex_field'.$i.'_label'] == '' && $fsc_opt_update['ex_field'.$i.'_type'] != 'fieldset-close') {
          $fsc_opt_update['ex_field'.$i.'_type'] = 'text';
          $fsc_opt_update['ex_field'.$i.'_default'] = '0';
          $fsc_opt_update['ex_field'.$i.'_default_text'] = '';
          $fsc_opt_update['ex_field'.$i.'_max_len'] = '';
          $fsc_opt_update['ex_field'.$i.'_label_css'] = '';
          $fsc_opt_update['ex_field'.$i.'_input_css'] = '';
          $fsc_opt_update['ex_field'.$i.'_attributes'] = '';
          $fsc_opt_update['ex_field'.$i.'_regex'] = '';
          $fsc_opt_update['ex_field'.$i.'_regex_error'] = '';
          $fsc_opt_update['ex_field'.$i.'_req'] = 'false';
          $fsc_opt_update['ex_field'.$i.'_notes'] = '';
          $fsc_opt_update['ex_field'.$i.'_notes_after'] = '';
        }
    }

    if (isset($_POST['fsc_reset_styles'])) {
         // reset styles feature
         $fsc_opt_update = $this->fsc_copy_styles($fsc_opt_defaults,$fsc_opt_update);
    }

    if (isset($_POST['fsc_reset_styles_left'])) {
        $style_resets_arr = array(
         'border_enable' => 'false',
         'form_style' => 'width:550px;',
         'border_style' => 'border:1px solid black; padding:10px;',
         'required_style' => 'padding-left:146px; text-align:left; ',
         'notes_style' => 'padding-left:146px; text-align:left; clear:left;',
         'title_style' => 'width:138px; text-align:right; float:left; clear:left; padding-top:8px; padding-right:10px;',
         'field_style' => 'text-align:left; float:left; padding:2px; margin:0;',
         'field_div_style' => 'text-align:left; float:left; padding-top:10px;',
         'error_style' => 'text-align:left; color:red;',
         'select_style' => 'text-align:left;',
         'captcha_div_style_sm' => 'float:left; width:162px; height:50px; padding-top:5px;',
         'captcha_div_style_m' => 'float:left; width:362px; height:65px; padding-top:5px;',
         'captcha_input_style' => 'text-align:left; float:left; padding:2px; margin:0; width:50px;',
         'submit_div_style' => 'padding-left:146px; text-align:left; float:left; clear:left; padding-top:8px;',
         'button_style' => 'cursor:pointer; margin:0;',
         'powered_by_style' => 'padding-left:146px; float:left; clear:left; font-size:x-small; font-weight:normal; padding-top:5px;',
         'field_size' => '39',
         'captcha_field_size' => '6',
         'text_cols' => '30',
         'text_rows' => '10',
         );

         // reset left styles feature
         foreach($style_resets_arr as $key => $val) {
           $fsc_opt_update[$key] = $val;
         }
    }

    // unencode < > & " ' (less than, greater than, ampersand, double quote, single quote).
    foreach($fsc_opt_update as $key => $val) {
           $fsc_opt_update[$key] = str_replace('&lt;','<',$val);
           $fsc_opt_update[$key] = str_replace('&gt;','>',$val);
           $fsc_opt_update[$key] = str_replace('&#39;',"'",$val);
           $fsc_opt_update[$key] = str_replace('&quot;','"',$val);
           $fsc_opt_update[$key] = str_replace('&amp;','&',$val);
    }

    //echo $this->site_path; exit;
    //print_r($fsc_opt_update);
    // save updated options to the database
    $this->set_option("fsc_form$form_num", $fsc_opt_update);

    // get the options from the database
    $fsc_opt = $this->get_option("fsc_form$form_num");
    //print_r($fsc_opt);
    // save updated global options to the database
    $this->set_option("fsc_form_gb", $fsc_gb_update);

    $redirect_to_form_1 = 0;
    if ( $fsc_gb_update['max_forms'] != $fsc_gb['max_forms'] ) {
       if ($fsc_gb_update['max_forms'] < $fsc_gb['max_forms']) {
         // delete all multi-forms higher than set number
         for ($i = $fsc_gb_update['max_forms'] + 1; $i <= 100; $i++) {
            $this->delete_option("fsc_form$i");
         }
       }
      // max_forms settings has changed, need to redirect to form 1 later on
      $redirect_to_form_1 = 1;
    }

    // get the global options from the database
    $fsc_gb = $this->get_option("fsc_form_gb");

    // strip slashes on get options array
    //foreach($fsc_opt as $key => $val) {
          // $fsc_opt[$key] = $this->ctf_stripslashes($val);
    //}

    if ($redirect_to_form_1) {
       // max_forms settings has changed, need to redirect to form 1
       $ctf_redirect_url = 'index.php';
       $ctf_redirect_timeout = 1;
 echo <<<EOT

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

echo '
<div class="updated"><strong>
<img src="'.$fsc_site['site_url']. '/ctf-loading.gif" alt="'.$this->ctf_output_string(_('Redirecting to Form 1')).'" />&nbsp;&nbsp;
'._('Redirecting to Form 1').' ...
</strong></div>
';

    }
  } // end if (isset($_POST['submit']))

if ( $passed_login && !isset($_GET['site_settings']) && !isset($_GET['show_form']) && !isset($_POST['fsc_action'])) {
  // update translation for this setting (when switched from English to something else)
  if ($fsc_opt['welcome'] == '<p>Comments or questions are welcome.</p>') {
       $fsc_opt['welcome'] = _('<p>Comments or questions are welcome.</p>');
  }

?>
<?php if ( !empty($_POST )  && !isset($_POST['ctf_action'])) : ?>
<div class="updated"><strong><?php echo _('Options saved'); ?></strong></div>
<?php endif; ?>


<h2><?php echo _('Fast Secure Contact Form - PHP'); ?> <?php echo _('Options'); ?></h2>

<?php

//echo 'post var count: ' . count($_POST);

$av_fld_arr  = array(); // used to show available field tags this form
$av_fld_subj_arr  = array(); // used to show available field tags for this form  subject

if ($fsc_opt['name_type'] != 'not_available') {
   switch ($fsc_opt['name_format']) {
      case 'name':
         $av_fld_arr[] = 'from_name';
      break;
      case 'first_last':
         $av_fld_arr[] = 'first_name';
         $av_fld_arr[] = 'last_name';
      break;
      case 'first_middle_i_last':
         $av_fld_arr[] = 'first_name';
         $av_fld_arr[] = 'middle_initial';
         $av_fld_arr[] = 'last_name';
      break;
      case 'first_middle_last':
         $av_fld_arr[] = 'first_name';
         $av_fld_arr[] = 'middle_name';
         $av_fld_arr[] = 'last_name';
      break;
   }
}
// email
$autoresp_ok = 1; // used in autoresp settings below
if ($fsc_opt['email_type'] != 'not_available') {
        $av_fld_arr[] = 'from_email';
}else{
   $autoresp_ok = 0;
}
        // optional extra fields
for ($i = 1; $i <= $fsc_opt['max_fields']; $i++) {
    if ( $fsc_opt['ex_field'.$i.'_label'] != '' && $fsc_opt['ex_field'.$i.'_type'] != 'fieldset-close') {
      if ($fsc_opt['ex_field'.$i.'_type'] == 'fieldset') {
      } else if ($fsc_opt['ex_field'.$i.'_type'] == 'attachment' && $fsc_opt['php_mailer_enable'] != 'php') {
            $av_fld_arr[] = "ex_field$i";
      } else {  // text, textarea, date, password, email, url, hidden, time, select, select-multiple, radio, checkbox, checkbox-multiple
            $av_fld_arr[] = "ex_field$i";
            if ($fsc_opt['ex_field'.$i.'_type'] == 'email')
              $autoresp_ok = 1;
      }
    }
} // end for
//if ($fsc_opt['email_type'] != 'not_available')
   $av_fld_subj_arr = $av_fld_arr;
//if ($fsc_opt['subject_type'] != 'not_available')
   $av_fld_arr[] = 'subject';
if ($fsc_opt['message_type'] != 'not_available')
   $av_fld_arr[] = 'message';
   $av_fld_arr[] = 'full_message';
if (function_exists('akismet_verify_key'))
   $av_fld_arr[] = 'akismet';

$av_fld_arr[] = 'date_time';
$av_fld_subj_arr[] = 'form_label';
?>

<p>
<a href="http://www.fastsecurecontactform.com/faq-php-version" target="_blank"><?php echo _('FAQ'); ?></a> |
<a href="http://www.fastsecurecontactform.com/support" target="_blank"><?php echo _('Support'); ?></a> |
<a href="http://www.fastsecurecontactform.com/changelog-php" target="_blank"><?php echo _('Changelog'); ?></a> |
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LV2DK8MC8QV6J" target="_blank"><?php echo _('Donate'); ?></a> |
<a href="http://www.642weather.com/weather/scripts.php" target="_blank"><?php echo _('Free PHP Scripts'); ?></a> |
<a href="http://www.fastsecurecontactform.com/contact" target="_blank"><?php echo _('Contact'); ?> Mike Challis</a>
</p>

<?php
if ($fsc_gb['donated'] != 'true') {
?>
<h2><?php echo _('Donate'); ?></h2>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post">

<table style="background-color:#FEFFAF; border:none; margin: -5px 0; vertical-align:middle;" width="500">
        <tr>
        <td>
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="LV2DK8MC8QV6J" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" style="border:none;" name="submit" alt="Paypal Donate" />
<img alt="" style="border:none;" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</td>
<td><?php
echo _('Please Donate to keep this program FREE'); echo '<br />';
echo _('If you find this program useful to you, please consider making a small donation to help contribute to my time invested and to further development. Thanks for your kind support!'); ?> - <a style="cursor:pointer;" title="<?php echo _('More from Mike Challis'); ?>" onclick="toggleVisibility('fsc_mike_challis_tip');"><?php echo _('More from Mike Challis'); ?></a></td>
</tr></table>
</form>
<br />

<div style="text-align:left; display:none" id="fsc_mike_challis_tip">
<img src="contact-form.jpg" width="250" height="185" alt="Mike Challis" /><br />
<?php echo _('Mike Challis says: "Hello, I have spent hundreds of hours coding this program just for you. If you are satisfied with my programs and support please consider making a small donation. If you are not able to, that is OK.'); ?>
<?php echo ' '; echo _('Most people donate $3, $5, $10, $20, or more. Though no amount is too small. Donations can be made with your PayPal account, or securely using any of the major credit cards."'); ?>
<br />
<a style="cursor:pointer;" title="Close" onclick="toggleVisibility('fsc_mike_challis_tip');"><?php echo _('Close this message'); ?></a>
</div>

<?php
}
?>

<form name="formoptions" action="index.php?ctf_form_num=<?php echo $form_num; ?>" method="post">
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="form_type" value="upload_options" />
   <p>
    <input name="fsc_donated" id="fsc_donated" type="checkbox" <?php if( $fsc_gb['donated'] == 'true' ) echo 'checked="checked"'; ?> />
    <label for="fsc_donated"><?php echo _('I have donated to help contribute for the development of this Contact Form.'); ?></label>
   </p>

<?php $this->fsc_code_usage($form_id); ?>

<h2><?php echo _('Options'); ?></h2>

<div class="form-tab"><?php echo _('Multi-Forms:').' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>
<fieldset>

<h3><?php
  // multi-form selector
  for ($i = 1; $i <= $fsc_gb['max_forms']; $i++) {
     if($i == 1) {
         if ($form_id == 1) {
             echo '<b>'.sprintf(_('Form: %d'),1).'</b>';
             echo ' <small><a href="index.php?show_form=1">('. _('view'). ')</a></small>';
        } else {
             echo '<a href="index.php">'. sprintf(_('Form: %d'),1). '</a>';
        }
     } else {
        if ($form_id == $i) {
             echo ' | <b>' . sprintf(_('Form: %d'),$i).'</b>';
             echo ' <small><a href="index.php?show_form='.$i.'">('. _('view'). ')</a></small>';
       } else {
             echo ' | <a href="index.php?ctf_form_num='.$i.'">'. sprintf(_('Form: %d'),$i). '</a>';
       }
     }
  }
  ?>
  </h3>

  <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_multi_tip');"><?php echo _('Multi-forms help'); ?></a>
  <div style="text-align:left; display:none" id="fsc_multi_tip">
  <?php echo _('This multi-form feature allows you to have many different forms on your site. Each form has unique settings and shortcode. Select the form you want to edit using the links above, then edit the settings below for the form you selected. Be sure to use the correct shortcode to call the form.') ?>
  </div>

<br />
  <label for="fsc_max_forms"><?php echo _('Number of available Multi-forms'); ?>:</label>
  <input name="fsc_max_forms" id="fsc_max_forms" class="text-effect" type="text" onclick="return alert('<?php echo _('Caution: Lowering this setting deletes forms.'); ?>')" value="<?php echo $this->absint($fsc_gb['max_forms']);  ?>" size="3" />
  <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_multi_num_tip');"><?php echo _('help'); ?></a>
  <div style="text-align:left; display:none" id="fsc_multi_num_tip">
  <?php echo _('Use this setting to increase or decrease the number of available forms. The most forms you can add is 99. Caution: lowering this number will delete forms of a higher number than the number you set.') ?>
  </div>

<br />

<label for="fsc_form_name"><?php echo sprintf(_('Form %d label'),$form_id) ?>:</label><input name="fsc_form_name" id="fsc_form_name" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['form_name']);  ?>" size="55" />
<a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_form_name_tip');"><?php echo _('help'); ?></a>
<div style="text-align:left; display:none" id="fsc_form_name_tip">
<?php echo _('Enter a label for your form. This is not used anywhere else, it just helps you keep track of what you are using it for.'); ?>
</div>

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('Form:').' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>
<fieldset>

  <label for="fsc_welcome"><?php echo _('Welcome introduction'); ?>:</label><br />
  <textarea rows="6" cols="70" name="fsc_welcome" id="fsc_welcome"><?php echo $this->ctf_output_string($fsc_opt['welcome']); ?></textarea>
  <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_welcome_tip');"><?php echo _('help'); ?></a>
  <div style="text-align:left; display:none" id="fsc_welcome_tip">
  <?php echo _('This is printed before the contact form. HTML is allowed.') ?>
  </div>

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('E-mail:').' '. sprintf(_('(form %d)'),$form_id); ?></div>
<div class="clear"></div>

<fieldset>

<?php
// checks for properly configured E-mail To: addresses in options.
$ctf_contacts = array ();
$ctf_contacts_test = trim($fsc_opt['email_to']);
$ctf_contacts_error = 0;
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
                  $ctf_contacts[] = array('CONTACT' => $key,  'EMAIL' => $value);
               } else {
                  $ctf_contacts_error = 1;
               }
          } else {
               // multiple emails here (additional ones will be Cc:)
               // Webmaster,user1@example.com;user2@example.com;user3@example.com;[cc]user4@example.com;[bcc]user5@example.com
               $multi_cc_arr = explode(";",$value);
               $multi_cc_string = '';
               foreach($multi_cc_arr as $multi_cc) {
               $multi_cc_t = str_replace('[cc]','',$multi_cc);
               $multi_cc_t = str_replace('[bcc]','',$multi_cc_t);
                  if ($this->ctf_validate_email($multi_cc_t)) {
                     $multi_cc_string .= "$multi_cc,";
                  } else {
                     $ctf_contacts_error = 1;
                  }
               }
               if ($multi_cc_string != '') {  // multi cc emails
                  $ctf_contacts[] = array('CONTACT' => $key,  'EMAIL' => rtrim($multi_cc_string, ','));
               }
         }
      }
   } // end foreach
  } // end if (is_array($ctf_ct_arr) ) {
} // end else

//print_r($ctf_contacts);

?>
        <label for="fsc_email_to"><?php echo _('E-mail To'); ?>:</label>
<?php
if (empty($ctf_contacts) || $ctf_contacts_error ) {
      echo '<div class="error">';
       echo _('ERROR: Misconfigured "E-mail To" address.');
       echo "</div>\n";
}

if ( !function_exists('mail') ) {
   echo '<div class="error">'. _('Warning: Your web host has the mail() function disabled. PHP cannot send email. This program will not work.').'</div>'."\n";
}
?>
<br />
        <textarea rows="6" cols="70" name="fsc_email_to" id="fsc_email_to"><?php echo $this->ctf_output_string($fsc_opt['email_to']);  ?></textarea>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_email_to_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_email_to_tip">
        <?php echo _('E-mail address the messages are sent to (your email). Add as many contacts as you need, the drop down list on the contact form will be made automatically. Each contact has a name and an email address separated by a comma. Separate each contact by pressing enter. If you need to add more than one contact, follow this example:'); ?><br />
        <?php echo _('If you need to use a comma in the name, escape it with a back slash, like this: \,'); ?><br />
        Webmaster,user1@example.com<br />
        Sales,user2@example.com<br /><br />

        <?php echo _('You can have multiple emails per contact using [cc]Carbon Copy. Separate each email with a semicolon. Follow this example:'); ?><br />
        Sales,user3@example.com;user4@example.com;user5@example.com<br /><br />

        <?php echo _('You can specify [cc]Carbon Copy or [bcc]Blind Carbon Copy by using tags. Separate each email with a semicolon. Follow this example:'); ?><br />
        Sales,user3@example.com;[cc]user1@example.com;[cc]user2@example.com;[bcc]user3@example.com;[bcc]user4@example.com
        </div>
<br />
  <?php
   // Check for safe mode
    $safe_mode_is_on = ((boolean)@ini_get('safe_mode') === false) ? 0 : 1;
    if($safe_mode_is_on){
      echo '<div class="error">'. _('Warning: Your web host has PHP safe_mode turned on.').' ';
      echo _('PHP safe_mode can cause problems like sending mail failures and file permission errors.').' ';
      echo _('Contact your web host for support.')."</div>\n";
    }

if ( $fsc_opt['email_from'] != '' ) {
    $from_fail = 0;
    if(!preg_match("/,/", $fsc_opt['email_from'])) {
        // just one email here
        // user1@example.com
        if (!$this->ctf_validate_email($fsc_opt['email_from'])) {
           $from_fail = 1;
        }
    } else {
        // name and email here
        // webmaster,user1@example.com
        list($key, $value) = explode(",",$fsc_opt['email_from']);
        $key   = trim($key);
        $value = trim($value);
        if (!$this->ctf_validate_email($value)) {
           $from_fail = 1;
        }
   }

   if ($from_fail)  {
       echo '<div class="error">';
       echo _('ERROR: Misconfigured "E-mail From" address.');
       echo "</div>\n";
   } else {
       $uri = parse_url($fsc_site['site_url']);
       $blogdomain = preg_replace("/^www\./i",'',$uri['host']);
       list($email_from_user,$email_from_domain) = explode('@',$fsc_opt['email_from']);
       if ( $blogdomain != $email_from_domain) {
       echo '<div class="updated">';
       echo sprintf(_('Warning: "E-mail From" is not set to an address from the same domain name as your web site (%s). This can sometimes cause mail not to send, or send but be delivered to a Spam folder. Be sure to test that your form is sending email and that you are receiving it, if not, fix this setting.'), $blogdomain);
       echo "</div>\n";
       }
   }
}
?>

        <label for="fsc_email_from"><?php echo _('E-mail From (optional)'); ?>:</label>
        <input name="fsc_email_from" id="fsc_email_from"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['email_from']);  ?>" size="50" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_email_from_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_email_from_tip">
        <?php echo _('E-mail address the messages are sent from. Some web hosts do not allow PHP to send email unless the envelope sender email address is on the same web domain as your web site. And they require it to be a real address on that domain, or mail will NOT SEND! (They do this to help prevent spam.) If your contact form does not send any email, then set this to a real email address on the SAME domain as your web site, then test the form.'); ?>
        <?php echo _('If your form still does not send any email, also check the setting below: "Enable when web host requires "Mail From" strictly tied to domain email account". In some cases, this will resolve the problem. This setting is also recommended for gmail users to prevent email from going to spam folder.'); ?>
        <br />
        <?php echo _('Enter just an email: user1@example.com'); ?><br />
        <?php echo _('Or enter name and email: webmaster,user1@example.com '); ?>
        </div>
<br />

        <?php
       if( $fsc_opt['email_from_enforced'] == 'true' && $fsc_opt['email_from'] == '') {
         echo '<div class="updated">';
         echo _('Warning: Enabling this setting requires the "E-mail From" setting above to also be set.');
         echo "</div>\n";
       }
       ?>
        <input name="fsc_email_from_enforced" id="fsc_email_from_enforced" type="checkbox" <?php if( $fsc_opt['email_from_enforced'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_email_from_enforced"><?php echo _('Enable when web host requires "Mail From" strictly tied to domain email account.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_email_from_enforced_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_email_from_enforced_tip">
        <?php echo _('If your form does not send any email, then set the "E-mail From" setting above to an address on the same web domain as your web site. If email still does not send, also check this setting. (ie: some users report this is required by yahoo small business web hosting, maybe others)') ?>
        </div>
        <br />

        <label for="fsc_email_reply_to"><?php echo _('Custom Reply To (optional)'); ?>:</label>
        <input name="fsc_email_reply_to" id="fsc_email_reply_to" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['email_reply_to']);  ?>" size="50" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_email_reply_to_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_email_reply_to_tip">
        <?php echo _('Leave this setting blank for most forms because the "reply to" is set automatically. Only use this setting if you are using the form for a mailing list and you do NOT want the reply going to the form user.'); ?>
        <?php echo _('Defines the email address that is automatically inserted into the "To:" field when a user replies to an email message.'); ?>
        <br />
        <?php echo _('Enter just an email: user1@example.com'); ?><br />
        </div>
        <br />

        <?php
       if( $fsc_opt['smtp_enable'] == 'true' && $fsc_opt['php_mailer_enable'] == 'php') {
         echo '<div class="updated">';
         echo _('Warning: Send E-mail function: phpmailer is required when you have Mail With SMTP enabled.');
         echo "</div>\n";
       }
       ?>

      <label for="fsc_php_mailer_enable"><?php echo _('Send E-mail function:'); ?></label>
      <select id="fsc_php_mailer_enable" name="fsc_php_mailer_enable">
<?php

$selected = '';
foreach (array(
'phpmailer' => $this->ctf_output_string(_('phpmailer')),
'php' => $this->ctf_output_string(_('PHP'))
) as $k => $v) {
 if ($fsc_opt['php_mailer_enable'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_php_mailer_enable_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_php_mailer_enable_tip">
        <?php echo _('If your form does not send any email, first try setting the "E-mail From" setting above because some web hosts do not allow PHP to send email unless the "From:" email address is on the same web domain.'); ?>
        <?php echo _('Note: attachments are only supported when using the "phpmailer" mail function.'); ?>
       </div>
<br />

        <input name="fsc_email_html" id="fsc_email_html" type="checkbox" <?php if( $fsc_opt['email_html'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_email_html"><?php echo _('Enable to receive email as HTML instead of plain text.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_email_html_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_email_html_tip">
        <?php echo _('Enable if you want the email message sent as HTML format. HTML format is desired if you want to avoid a 70 character line wordwrap when you copy and paste the email message. Normally the email is sent in plain text wordwrapped 70 characters per line to comply with most email programs.') ?>
        </div>
        <br />

<?php
if ( $fsc_opt['email_bcc'] != ''){
    $bcc_fail = 0;
    if(!preg_match("/,/", $fsc_opt['email_bcc'])) {
         // just one email here
         // user1@example.com
         if (!$this->ctf_validate_email($fsc_opt['email_bcc'])) {
             $bcc_fail = 1;
         }
    } else {
         // multiple emails here
         // user1@example.com,user2@example.com
         $bcc_arr = explode(",",$fsc_opt['email_bcc']);
         foreach($bcc_arr as $b_cc) {
             if (!$this->ctf_validate_email($b_cc)) {
                $bcc_fail = 1;
                break;
             }
         }
   }
   if ($bcc_fail)  {
      echo '<div class="error">';
      echo _('ERROR: Misconfigured "Bcc E-mail" address.');
      echo "</div>\n";
   }
}
?>
        <label for="fsc_email_bcc"><?php echo _('E-mail Bcc (optional)'); ?>:</label>
        <input name="fsc_email_bcc" id="fsc_email_bcc"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['email_bcc']);  ?>" size="50" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_email_bcc_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_email_bcc_tip">
        <?php echo _('E-mail address(s) to receive Bcc (Blind Carbon Copy) messages. You can send to multiple or single, both methods are acceptable:'); ?>
        <br />
        user1@example.com<br />
        user1@example.com,user2@example.com
        </div>
<br />

        <label for="fsc_email_subject"><?php echo _('E-mail Subject Prefix') ?>:</label><input name="fsc_email_subject" id="fsc_email_subject"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['email_subject']);  ?>" size="55" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_email_subject_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_email_subject_tip">
        <?php echo _('This will become a prefix of the subject for the E-mail you receive.'); ?>
        <?php echo _('Listed below is an optional list of field tags for fields you can add to the subject.') ?><br />
        <?php echo _('Example: to include the name of the form sender, include this tag in the E-mail Subject Prefix:'); ?> [from_name]<br />
		<?php echo _('Available field tags:'); ?>
		<span style="margin: 2px 0" dir="ltr"><br />
        <?php
       // show available fields
       foreach ($av_fld_subj_arr as $i)
         echo "[$i]<br />";
        ?>
        </span>
        </div>
<br />

        <label for="fsc_email_subject_list"><?php echo _('Optional E-mail Subject List'); ?>:</label><br />
        <textarea rows="6" cols="70" name="fsc_email_subject_list" id="fsc_email_subject_list"><?php echo $this->ctf_output_string($fsc_opt['email_subject_list']);  ?></textarea>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_email_subject_list_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_email_subject_list_tip">
        <?php echo _('Optional E-mail subject drop down list. Add as many subject options as you need, the drop down list on the contact form will be made automatically. Separate each subject option by pressing enter. Follow this example:'); ?><br />
        Newsletter Signup<br />
        Question<br />
        Comment
        </div>
<br />

        <input name="fsc_double_email" id="fsc_double_email" type="checkbox" <?php if( $fsc_opt['double_email'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_double_email"><?php echo _('Enable double E-mail entry required on contact form.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_double_email_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_double_email_tip">
        <?php echo _('Requires users to enter email address in two fields to help reduce mistakes.') ?>
        </div>
<br />

        <input name="fsc_name_case_enable" id="fsc_name_case_enable" type="checkbox" <?php if( $fsc_opt['name_case_enable'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_name_case_enable"><?php echo _('Enable upper case alphabet correction.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_name_case_enable_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_name_case_enable_tip">
        <?php echo _('Automatically corrects form input using a function knowing about alphabet case (example: correct caps on McDonald, or correct USING ALL CAPS).'); ?>
        <?php echo _('Enable on English language only because it can cause accent character problems if enabled on other languages.'); ?>
        </div>
<br />

        <input name="fsc_sender_info_enable" id="fsc_sender_info_enable" type="checkbox" <?php if( $fsc_opt['sender_info_enable'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_sender_info_enable"><?php echo _('Enable sender information in E-mail footer.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_sender_info_enable_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_sender_info_enable_tip">
        <?php echo _('You will receive in the E-mail, detailed information about the sender. Such as IP Address, date, time, and which web browser they used.'); ?>
        </div>
<br />
        <input name="fsc_domain_protect" id="fsc_domain_protect" type="checkbox" <?php if( $fsc_opt['domain_protect'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_domain_protect"><?php echo _('Enable Form Post security by requiring domain name match for'); ?>
        <?php
        $uri = parse_url($fsc_site['site_url']);
        $sitedomain = preg_replace("/^www\./i",'',$uri['host']);
        echo " $sitedomain ";
        ?><?php echo _('(recommended).'); ?>
        </label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_domain_protect_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_domain_protect_tip">
        <?php echo _('Prevents automated spam bots posting from off-site forms.') ?>
        </div>
<br />

        <input name="fsc_email_check_dns" id="fsc_email_check_dns" type="checkbox" <?php if( $fsc_opt['email_check_dns'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_email_check_dns"><?php echo _('Enable checking DNS records for the domain name when checking for a valid E-mail address.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_email_check_dns_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_email_check_dns_tip">
        <?php echo _('Improves email address validation by checking that the domain of the email address actually has a valid DNS record.') ?>
        </div>

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('Mail With SMTP(optional):').' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>

<fieldset>

        <?php
       if( $fsc_opt['smtp_enable'] == 'true' && ($fsc_opt['smtp_host'] == '' || $fsc_opt['smtp_port'] == '')) {
         echo '<div class="error">';
         echo _('Warning: SMTP Host and SMTP Port are required when you have SMTP enabled.');
         echo "</div>\n";
       }
       ?>

        <input name="fsc_smtp_enable" id="fsc_smtp_enable" type="checkbox" <?php if( $fsc_opt['smtp_enable'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_smtp_enable"><?php echo _('Enable SMTP for mail sending.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_smtp_enable_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_smtp_enable_tip">
        <?php echo _('This setting reconfigures the mail() function to use SMTP instead. Enable if you have to use SMTP protocol to send email. Most users do not have to enable this setting, but sometimes it becomes necessary.') ?>
        </div>
        <br />

        <label for="fsc_smtp_host"><?php echo _('SMTP Host'); ?>:</label><input name="fsc_smtp_host" id="fsc_smtp_host"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['smtp_host']);  ?>" size="50" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_smtp_host_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_smtp_host_tip">
        <?php echo _('The SMTP server host address. Check with your provider\'s recommendations to be sure this is correct.'); ?>
        <?php echo _('Example: smtp.gmail.com'); ?>
        </div>
        <br />

        <label for="fsc_smtp_port"><?php echo _('SMTP Port'); ?>:</label><input name="fsc_smtp_port" id="fsc_smtp_port"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['smtp_port']);  ?>" size="3" />

        <label for="fsc_smtp_encryption"><?php echo _('SMTP Encryption (optional)'); ?>:</label><input name="fsc_smtp_encryption" id="fsc_smtp_encryption"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['smtp_encryption']);  ?>" size="3" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_smtp_encryption_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_smtp_encryption_tip">
        <?php echo _('Some hosts require port 25, 465, 587 or others. Some hosts require SMTP encryption set to SSL, TLS, or left blank for none. Check with your provider\'s recommendations to be sure this is correct.'); ?>
        </div>
        <br />

        <input name="fsc_smtp_auth_enable" id="fsc_smtp_auth_enable" type="checkbox" <?php if( $fsc_opt['smtp_auth_enable'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_smtp_auth_enable"><?php echo _('Enable when SMTP Authentication is required (optional).'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_smtp_auth_enable_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_smtp_auth_enable_tip">
        <?php echo _('Enable when the SMTP server requires SMTP Authentication. When the SMTP server requires SMTP Authentication, you must also enter the correct Username and Password below. Check with your provider\'s recommendations to be sure this is correct.') ?>
        </div>
        <br />

        <?php
       if( $fsc_opt['smtp_enable'] == 'true' && $fsc_opt['smtp_auth_enable'] == 'true' && ($fsc_opt['smtp_user'] == '' || $fsc_opt['smtp_pass'] == '') ) {
         echo '<div class="error">';
         echo _('Warning: SMTP Username and SMTP Password are required when you have SMTP Authentication enabled.');
         echo "</div>\n";
       }
       ?>

        <label for="fsc_smtp_user"><?php echo _('SMTP Username'); ?>:</label><input name="fsc_smtp_user" id="fsc_smtp_user"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['smtp_user']);  ?>" size="50" />
        <br />

        <label for="fsc_smtp_pass"><?php echo _('SMTP Password'); ?>:</label><input name="fsc_smtp_pass" id="fsc_smtp_pass"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['smtp_pass']);  ?>" size="50" />

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('Autoresponder:').' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>

<fieldset>

        <input name="fsc_auto_respond_enable" id="fsc_auto_respond_enable" type="checkbox" <?php if( $fsc_opt['auto_respond_enable'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_auto_respond_enable"><?php echo _('Enable autoresponder E-mail message.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_auto_respond_enable_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_auto_respond_enable_tip">
        <?php echo _('Enable when you want the form to automatically answer with an autoresponder E-mail message.'); ?>
        </div>
<br />

      <?php
       if( $fsc_opt['auto_respond_enable'] == 'true' && ($fsc_opt['auto_respond_from_name'] == '' || $fsc_opt['auto_respond_from_email'] == '' || $fsc_opt['auto_respond_reply_to'] == '' || $fsc_opt['auto_respond_subject'] == '' || $fsc_opt['auto_respond_message'] == '') ) {
         echo '<div class="error">';
         echo _('Warning: Enabling this setting requires all the autoresponder fields below to also be set.');
         echo "</div>\n";
       }
       if( !$autoresp_ok && $fsc_opt['auto_respond_enable'] == 'true' && $fsc_opt['auto_respond_from_name'] != '' && $fsc_opt['auto_respond_from_email'] != '' && $fsc_opt['auto_respond_reply_to'] != '' && $fsc_opt['auto_respond_subject'] != '' && $fsc_opt['auto_respond_message'] != '' ) {
         echo '<div class="error">';
         echo _('Warning: No email address field is set, you will not be able to reply to emails and the autoresponder will not work.');
         echo "</div>\n";
       }
       ?>
        <label for="fsc_auto_respond_from_name"><?php echo _('Autoresponder E-mail "From" name'); ?>:</label><input name="fsc_auto_respond_from_name" id="fsc_auto_respond_from_name"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['auto_respond_from_name']);  ?>" size="60" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_auto_respond_from_name_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_auto_respond_from_name_tip">
        <?php echo _('This sets the name in the "from" field when the autoresponder sends E-mail.'); ?>
        </div>
<br />

        <label for="fsc_auto_respond_from_email"><?php echo _('Autoresponder E-mail "From" address'); ?>:</label><input name="fsc_auto_respond_from_email" id="fsc_auto_respond_from_email"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['auto_respond_from_email']);  ?>" size="60" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_auto_respond_from_email_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_auto_respond_from_email_tip">
        <?php echo _('This sets the "from" E-mail address when the autoresponder sends email. If your autoresponder does not send any email, then set this setting to a real email address on the same web domain as your web site. (Same applies to the "Email-From" setting on this page)'); ?>
        </div>
<br />

        <label for="fsc_auto_respond_reply_to"><?php echo _('Autoresponder E-mail "Reply To" address'); ?>:</label><input name="fsc_auto_respond_reply_to" id="fsc_auto_respond_reply_to"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['auto_respond_reply_to']);  ?>" size="60" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_auto_respond_reply_to_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_auto_respond_reply_to_tip">
        <?php echo _('This sets the "reply to" E-mail address when the autoresponder sends E-mail.'); ?>
        </div>
<br />

        <label for="fsc_auto_respond_subject"><?php echo _('Autoresponder E-mail subject'); ?>:</label><input name="fsc_auto_respond_subject" id="fsc_auto_respond_subject"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['auto_respond_subject']);  ?>" size="60" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_auto_respond_subject_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_auto_respond_subject_tip">
        <?php echo _('Type your autoresponder E-mail subject here, then enable it with the setting above.'); ?>
        <?php echo _('Listed below is an optional list of field tags for fields you can add to the subject.') ?><br />
        <?php echo _('Example: to include the name of the form sender, include this tag in the Autoresponder E-mail subject:'); ?> [from_name]<br />
		<?php echo _('Available field tags:'); ?>
		<span style="margin: 2px 0" dir="ltr"><br />
        <?php
       // show available fields
       foreach ($av_fld_subj_arr as $i)
         echo "[$i]<br />";
        ?>
        </span>
        </div>
<br />

        <label for="fsc_auto_respond_message"><?php echo _('Autoresponder E-mail message'); ?>:</label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_auto_respond_message_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_auto_respond_message_tip">
        <?php echo _('Type your autoresponder E-mail message here, then enable it with the setting above.'); ?>
        <?php echo _('Listed below is an optional list of field tags for fields you can add to the autoresponder email message.') ?><br />
        <?php echo _('Example: to include the name of the form sender, include this tag in the Autoresponder E-mail message:'); ?> [from_name]<br />
		<?php echo _('Available field tags:'); ?>
		<span style="margin: 2px 0" dir="ltr"><br />
        <?php
       // show available fields
       foreach ($av_fld_arr as $i) {
         if( in_array($i,array('message','full_message','akismet')) )  // exclude these
            continue;
         echo "[$i]<br />";
       }
        ?>
        </span>
        <?php echo _('Note: If you add any extra fields, they will show up in this list of available tags.'); ?>
        <?php echo _('Note: The message fields are intentionally disabled to help prevent spammers from using this form to relay spam.'); ?>
        <?php echo _('Try to limit this feature to just using the name field to personalize the message. Do not try to use it to send a copy of what was posted.'); ?>

        </div><br />
        <textarea rows="5" cols="70" name="fsc_auto_respond_message" id="fsc_auto_respond_message"><?php echo $this->ctf_output_string($fsc_opt['auto_respond_message']);  ?></textarea>
<br />

        <input name="fsc_auto_respond_html" id="fsc_auto_respond_html" type="checkbox" <?php if( $fsc_opt['auto_respond_html'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_auto_respond_html"><?php echo _('Enable using HTML in autoresponder E-mail message.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('auto_respond_html_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="auto_respond_html_tip">
        <?php echo _('Enable when you want to use HTML in the autoresponder E-mail message.'); echo ' ';?>
        <?php echo _('Then you can use an HTML message. example:'); ?><br />
&lt;html&gt;&lt;body&gt;<br />
&lt;h1&gt;<?php echo _('Hello World!'); ?>&lt;/h1&gt;<br />
&lt;/body&gt;&lt;/html&gt;
        </div>

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>


<div class="form-tab"><?php echo _('Akismet:') .' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>
<fieldset>

   <?php
    if (isset($_POST['akismet_check'])){
      if ($fsc_gb['akismet_enable'] == 'true') {
         if ($fsc_gb['akismet_api_key'] != '') {
              require $fsc_site['site_path'] . '/Akismet.class.php';
              $akismet = new Akismet( str_replace('/contact-files','',$fsc_site['site_url']), $fsc_gb['akismet_api_key'] );
              if($akismet->isKeyValid()) {
                    // api key is okay
                    ?><div class="updated"><strong><?php echo _('Akismet is enabled and the key is valid. This form will be checked with Akismet to help prevent spam'); ?></strong></div><?php
              } else {
                   // api key is invalid
                   ?><div class="error"><strong><?php echo _('Akismet plugin is enabled but key failed to verify'); ?></strong></div><?php
              }
         }else{
               ?><div class="error"><strong><?php echo _('Akismet plugin is enabled but key needs to be activated'); ?></strong></div><?php
         }
     }else{
         ?><div class="error"><strong><?php echo _('Akismet is deactivated'); ?></strong></div><?php
     }
   }
    ?>

     <strong><?php echo _('Akismet Spam Prevention:'); ?></strong>

    <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('akismet_tip');"><?php echo _('help'); ?></a>
    <div style="text-align:left; display:none" id="akismet_tip">
    <?php echo _('Akismet is a spam prevention plugin. When Akismet is installed and active, this form will be checked with Akismet to help prevent spam.') ?>
    </div>
<br />

    <input name="fsc_akismet_enable" id="fsc_akismet_enable" type="checkbox" <?php if ( $fsc_gb['akismet_enable'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fsc_akismet_enable"><?php echo _('Enable Akismet.'); ?></label>
<br />

    <label for="fsc_akismet_api_key"><?php echo _('Akismet API Key') ?>:</label><input name="fsc_akismet_api_key" id="fsc_akismet_api_key" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_gb['akismet_api_key']);  ?>" size="30" />
    <?php echo ' '; echo sprintf(_('If you do not have an Akismet API key yet, you can get one at %s'),'<a href="http://akismet.com" target="_new">Akismet.com</a>'); ?>.
<br />

  <input name="akismet_check" id="akismet_check" type="checkbox" value="1" />
  <label for="akismet_check"><?php echo _('Check this and click "Update Options" to determine if Akismet key is active.'); ?></label>

<br />
  <label for="fsc_akismet_send_anyway"><?php echo _('What should happen if Akismet determines the message is spam?'); ?></label>
   <select id="fsc_akismet_send_anyway" name="fsc_akismet_send_anyway">
<?php
$akismet_send_anyway_array = array(
'false' => $this->ctf_output_string(_('Block spam messages')),
'true' => $this->ctf_output_string(_('Tag as spam and send anyway')),
);
$selected = '';
foreach ($akismet_send_anyway_array as $k => $v) {
 if ($fsc_opt['akismet_send_anyway'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>
<a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_akismet_send_anyway_tip');"><?php echo _('help'); ?></a>
    <div style="text-align:left; display:none" id="fsc_akismet_send_anyway_tip">
    <?php echo _('If you select "block spam messages". If Akismet determines the message is spam: An error will display "Invalid Input - Spam?" and the form will not send.'); ?>
    <?php echo ' '; echo _('If you select "tag as spam and send anyway". If Akismet determines the message is spam: The message will send and the subject will begin with "Akismet: Spam". This way you can have Akismet on and be sure not to miss a message.'); ?>
    </div>
<br />
  <input name="fsc_akismet_disable" id="fsc_akismet_disable" type="checkbox" <?php if( $fsc_opt['akismet_disable'] == 'true' ) echo 'checked="checked"'; ?> />
  <label for="fsc_akismet_disable"><?php echo _('Turn off Akismet for this form.'); ?></label>
  <?php if( $fsc_opt['akismet_disable'] == 'true' ) { ?>
   <br /><br /><span class="updated"><?php echo _('Akismet is turned off for this form'); ?></span>
  <?php } ?>

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('CAPTCHA:').' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>

<fieldset>

        <input name="fsc_captcha_enable" id="fsc_captcha_enable" type="checkbox" <?php if ( $fsc_opt['captcha_enable'] == 'true' ) echo ' checked="checked" '; ?> />
        <label for="fsc_captcha_enable"><?php echo _('Enable CAPTCHA (recommended).'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_captcha_enable_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_captcha_enable_tip">
        <?php echo _('Prevents automated spam bots by requiring that the user pass a CAPTCHA test before posting. You can disable CAPTCHA if you prefer, because the form also uses Akismet to prevent spam when Akismet plugin is installed with the key activated.') ?>
        </div>
<br />

        <label for="fsc_captcha_difficulty"><?php echo _('CAPTCHA difficulty level:'); ?></label>
      <select id="fsc_captcha_difficulty" name="fsc_captcha_difficulty">
<?php
$captcha_difficulty_array = array(
'low' => $this->ctf_output_string(_('Low')),
'medium' => $this->ctf_output_string(_('Medium')),
'high' => $this->ctf_output_string(_('High')),
);
$selected = '';
foreach ($captcha_difficulty_array as $k => $v) {
 if ($fsc_opt['captcha_difficulty'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_captcha_difficulty_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_captcha_difficulty_tip">
        <?php echo _('Changes level of distorion of the CAPTCHA image text.') ?>
        </div>
<br />

        <input name="fsc_captcha_small" id="fsc_captcha_small" type="checkbox" <?php if ( $fsc_opt['captcha_small'] == 'true' ) echo ' checked="checked" '; ?> />
        <label for="fsc_captcha_small"><?php echo _('Enable smaller size CAPTCHA image.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_captcha_small_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_captcha_small_tip">
        <?php echo _('Makes the CAPTCHA image smaller.') ?>
        </div>
<br />

        <input name="fsc_captcha_no_trans" id="fsc_captcha_no_trans" type="checkbox" <?php if ( $fsc_opt['captcha_no_trans'] == 'true' ) echo ' checked="checked" '; ?> />
        <label for="fsc_captcha_no_trans"><?php echo _('Disable CAPTCHA transparent text (only if captcha text is missing on the image, try this fix).'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_captcha_no_trans_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_captcha_no_trans_tip">
        <?php echo _('Sometimes fixes missing test on the CAPTCHA image. If this does not fix missing text, your PHP server is not compatible with the CAPTCHA functions. You can disable CAPTCHA or have your web server fixed.') ?>
        </div>
<br />

        <a href="<?php echo $this->captcha_url . '/test/index.php'; ?>" target="_new"><?php echo _('Test if your PHP installation will support the CAPTCHA'); ?></a>

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('Form:').' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>

<fieldset>

<strong><?php echo _('Standard Fields:'); ?></strong><br />
       <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_stand_fields_tip');">
       <?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_stand_fields_tip">
       <?php echo _('The standard fields can be set to be required or not, or even be disabled.'); ?>
      </div>
 <br />

   <label for="fsc_name_type"><?php echo _('Name field:'); ?></label>
   <select id="fsc_name_type" name="fsc_name_type">
<?php
$name_type_array = array(
'not_available' => $this->ctf_output_string(_('Not Available')),
'not_required' => $this->ctf_output_string(_('Not Required')),
'required' => $this->ctf_output_string(_('Required')),
);
$selected = '';
foreach ($name_type_array as $k => $v) {
 if ($fsc_opt['name_type'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>

      <label for="fsc_name_format"><?php echo _('Name field format:'); ?></label>
      <select id="fsc_name_format" name="fsc_name_format">
<?php
$name_format_array = array(
'name' => $this->ctf_output_string(_('Name')),
'first_last' => $this->ctf_output_string(_('First Name, Last Name')),
'first_middle_i_last' => $this->ctf_output_string(_('First Name, Middle Initial, Last Name')),
'first_middle_last' => $this->ctf_output_string(_('First Name, Middle Name, Last Name')),
);
$selected = '';
foreach ($name_format_array as $k => $v) {
 if ($fsc_opt['name_format'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>
       <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_name_format_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_name_format_tip">
       <?php echo _('Select how the name field is formatted on the form.'); ?>
       </div>
<br />

      <label for="fsc_email_type"><?php echo _('E-mail field:'); ?></label>
      <select id="fsc_email_type" name="fsc_email_type">
<?php
$selected = '';
foreach ($name_type_array as $k => $v) {
 if ($fsc_opt['email_type'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>
<br />

      <label for="fsc_subject_type"><?php echo _('Subject field:'); ?></label>
      <select id="fsc_subject_type" name="fsc_subject_type">
<?php
$selected = '';
foreach ($name_type_array as $k => $v) {
 if ($fsc_opt['subject_type'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>

<br />

      <label for="fsc_message_type"><?php echo _('Message field:'); ?></label>
      <select id="fsc_message_type" name="fsc_message_type">
<?php
$selected = '';
foreach ($name_type_array as $k => $v) {
 if ($fsc_opt['message_type'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>

      <input name="fsc_preserve_space_enable" id="fsc_preserve_space_enable" type="checkbox" <?php if( $fsc_opt['preserve_space_enable'] == 'true' ) echo 'checked="checked"'; ?> />
      <label for="fsc_preserve_space_enable"><?php echo _('Preserve Message field spaces.'); ?></label>
      <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_preserve_space_enable_tip');"><?php echo _('help'); ?></a>
      <div style="text-align:left; display:none" id="fsc_preserve_space_enable_tip">
      <?php echo _('Normally the Message field will have all extra white space removed. Enabling this setting will allow all the Message field white space to be preserved.'); ?>
      </div>

<br />
<br />

<strong><?php echo _('Extra Fields:'); ?></strong><br /><br />
       <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_extra_fields_tip');"><h3><?php echo _('Click here to see instructions for extra fields.'); ?></a></h3>
       <div style="text-align:left; display:none" id="fsc_extra_fields_tip">
       <br />
<strong><?php echo _('Instructions for how to use Extra Fields:'); ?></strong>
       <blockquote>
      <?php echo _('You can use extra contact form fields for phone number, company name, etc. To enable an extra field, just enter a label. Then check if you want the field to be required or not. To disable, empty the label.'); ?>
<br /><strong><?php echo _('Text and Textarea fields:'); ?></strong><br />
       <?php echo _('The text field is for single line text entry. The textarea field is for multiple line text entry.'); ?>
<br /><strong><?php echo _('Checkbox, Checkbox-multiple, Radio, Select, and Select-multiple extra fields:'); ?></strong><br />
       <?php echo _('To enable a checkbox field with a single option, just enter a label. Then check if you want the field to be required or not.'); ?><br />
       <?php echo _('To enable fields with multiple options like checkbox-multiple, radio, select, or select-multiple field types; first enter the label and a comma, then include the options separating each one with a semicolon like this example: Color:,Red;Green;Blue.'); ?>
       <?php echo _('If you need to use a comma besides the one needed to separate the label, escape it with a back slash, like this: \,'); ?>
       <?php echo _('You can also use fields that allow multiple options to be checked at once, such as checkbox-multiple and select-multiple like in this example: Pizza Toppings:,olives;mushrooms;cheese;ham;tomatoes. Now multiple options can be checked for the "Pizza Toppings" label.'); ?>
       <?php echo _('By default radio and checkboxes are displayed vertical. Here is how to make them display horizontal: add the tag {inline} before the label, like this: {inline}Pizza Toppings:,olives;mushrooms;cheese;ham;tomatoes.'); ?>
<br /><strong><?php echo _('Attachment:'); ?></strong><br />
       <?php echo _('The attachment is used to allow users to attach a file upload from the form. You can add multiple attachments. The attachment is sent with your email. Attachments are deleted from the server after the email is sent.'); ?>
<br /><strong><?php echo _('Date field:'); ?></strong><br />
       <?php echo _('The date is used to allow a date field with a calendar pop-up. The date field ensures that a date entry is in a standard format every time.'); ?>
<br /><strong><?php echo _('Time field:'); ?></strong><br />
       <?php echo _('The time is used to allow a time entry field with hours, minutes, and AM/PM. The time field ensures that a time entry is in a standard format.'); ?>
<br /><strong><?php echo _('Hidden field:'); ?></strong><br />
       <?php echo _('The hidden field is used if you need to pass a hidden value from the form to the email message. The hidden field does not show on the page. You must set the label and the value. First enter the label, a comma, then the value. Like in this example: Language,English'); ?>
<br /><strong><?php echo _('Email field:'); ?></strong><br />
       <?php echo _('The email field is used to allow an email address entry field. The email field ensures that a email entry is in a valid email format.'); ?>
<br /><strong><?php echo _('URL field:'); ?></strong><br />
       <?php echo _('The URL field is used to allow a URL entry field. The URL field ensures that a URL entry is in a valid URL format.'); ?>
<br /><strong><?php echo _('Password field:'); ?></strong><br />
       <?php echo _('The password field is used for a text field where what is entered shows up as dots on the screen. The email you receive will have the entered value fully visible.'); ?>
<br /><strong><?php echo _('Fieldset:'); ?></strong><br />
       <?php echo _('The fieldset(box-open) is used to draw a box around related form elements. The fieldset label is used for a (legend) title of the group.'); ?>
       <br />
       <?php echo _('The fieldset(box-close) is used to close a box around related form elements. A label is not required for this type. If you do not close a fieldset box, it will close automatically when you add another fieldset box.'); ?>
 <br /><strong><?php echo _('Optional HTML before field:'); ?></strong><br />
       <?php echo _('Use the Optional HTML before field to print some HTML before an extra field on the form. This is for the form display only, not E-mail. HTML is allowed.'); ?>

<br /><br />
<strong><?php echo _('Optional modifiers:'); ?></strong><br />

<br /><strong><?php echo _('Default text:'); ?></strong><br />
       <?php echo _('Use to pre-fill a value for a text field. Can be used for text or textarea field types.'); ?>
<br /><strong><?php echo _('Default option:'); ?></strong><br />
       <?php echo _('To make "green" the default selection for a red, green, blue select field: set "Default option" 2. Can be used for checkbox, radio, or select field types.'); ?>
<br /><strong><?php echo _('Max length:'); ?></strong><br />
       <?php echo _('Use to limit the number of allowed characters for a text field. The limit will be checked when the form is posted. Can be used for text, textarea, and password field types.'); ?>
<br /><strong><?php echo _('Required field:'); ?></strong><br />
       <?php echo _('Check this setting if you want the field to be required when the form is posted. Can be used for any extra field type.'); ?>
<br /><strong><?php echo _('Attributes:'); ?></strong><br />
       <?php echo _('Use to insert input field attributes. Example: To make a text field readonly, set to: readonly="readonly" Can be used for any extra field type.'); ?>
<br /><strong><?php echo _('Validation regex:'); ?></strong><br />
       <?php echo _('Use to validate if form input is in a specific format. Example: If you want numbers in a text field type but do not allow text, use this regex: /^\d+$/ Can be used for text, textarea, date and password field types.'); ?>
<br /><strong><?php echo _('Regex fail message:'); ?></strong><br />
       <?php echo _('Use to customize a message to alert the user when the form fails to validate a regex after post. Example: Please only enter numbers. For use with validation regex only.'); ?>
<br /><strong><?php echo _('Label CSS/Input CSS :'); ?></strong><br />
       <?php echo _('Use to style individual form fields with CSS. CSS class names or style code are both acceptable. Note: If you do not need to style fields individually, you should use the CSS DIV settings instead.'); ?>
<br /><strong><?php echo _('HTML before/after field:'); ?></strong><br />
       <?php echo _('Use the HTML before/after field to print some HTML before or after an extra field on the form. This is for the form display only, not E-mail. HTML is allowed.'); ?>


       </blockquote>
</div>

<br />


      <?php
$field_type_array = array(
'text' => $this->ctf_output_string(_('text')),
'textarea' => $this->ctf_output_string(_('textarea')),
'checkbox' => $this->ctf_output_string(_('checkbox')),
'checkbox-multiple' => $this->ctf_output_string(_('checkbox-multiple')),
'radio' => $this->ctf_output_string(_('radio')),
'select' => $this->ctf_output_string(_('select')),
'select-multiple' => $this->ctf_output_string(_('select-multiple')),
'attachment' => $this->ctf_output_string(_('attachment')),
'date' => $this->ctf_output_string(_('date')),
'time' => $this->ctf_output_string(_('time')),
'email' => $this->ctf_output_string(_('email')),
'url' => $this->ctf_output_string(_('url')),
'hidden' => $this->ctf_output_string(_('hidden')),
'password' => $this->ctf_output_string(_('password')),
'fieldset' => $this->ctf_output_string(_('fieldset(box-open)')),
'fieldset-close' => $this->ctf_output_string(_('fieldset(box-close)')),
);
      // optional extra fields
      for ($i = 1; $i <= $fsc_opt['max_fields']; $i++) {
      ?>
      <fieldset style="padding:4px; margin:4px;">
         <legend style="padding:4px;"><b><?php echo sprintf( _('Extra field %d'),$i);?></b></legend>

       <label for="<?php echo 'fsc_ex_field'.$i.'_label' ?>"><?php echo _('Label:'); ?></label>
       <input name="<?php echo 'fsc_ex_field'.$i.'_label' ?>" id="<?php echo 'fsc_ex_field'.$i.'_label' ?>"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['ex_field'.$i.'_label']);  ?>" size="95" />

       <label for="<?php echo 'fsc_ex_field'.$i.'_type' ?>"><?php echo _('Field type:'); ?></label>
       <select id="<?php echo 'fsc_ex_field'.$i.'_type' ?>" name="<?php echo 'fsc_ex_field'.$i.'_type' ?>">
<?php
$selected = '';
foreach ($field_type_array as $k => $v) {
 if ($fsc_opt['ex_field'.$i.'_type'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select><br />

       <?php echo _('Optional modifiers'); ?>:
       <label for="<?php echo 'fsc_ex_field'.$i.'_default_text' ?>"><?php echo _('Default text'); ?>:</label>
       <input name="<?php echo 'fsc_ex_field'.$i.'_default_text' ?>" id="<?php echo 'fsc_ex_field'.$i.'_default_text' ?>" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['ex_field'.$i.'_default_text']);  ?>" size="45" />

       <label for="<?php echo 'fsc_ex_field'.$i.'_default' ?>"><?php printf(_('Default option:'),$i); ?></label>
       <input name="<?php echo 'fsc_ex_field'.$i.'_default' ?>" id="<?php echo 'fsc_ex_field'.$i.'_default' ?>" class="text-effect" type="text" value="<?php echo $this->ctf_output_string(isset($fsc_opt['ex_field'.$i.'_default']) ? $fsc_opt['ex_field'.$i.'_default'] : 0);  ?>" size="2" />

       <label for="<?php echo 'fsc_ex_field'.$i.'_max_len' ?>"><?php echo _('Max length'); ?>:</label>
       <input name="<?php echo 'fsc_ex_field'.$i.'_max_len' ?>" id="<?php echo 'fsc_ex_field'.$i.'_max_len' ?>" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['ex_field'.$i.'_max_len']);  ?>" size="2" />

       <input name="<?php echo 'fsc_ex_field'.$i.'_req' ?>" id="<?php echo 'fsc_ex_field'.$i.'_req' ?>" type="checkbox" <?php if( $fsc_opt['ex_field'.$i.'_req'] == 'true' ) echo 'checked="checked"'; ?> />
       <label for="<?php echo 'fsc_ex_field'.$i.'_req' ?>"><?php echo _('Required field'); ?></label><br />

       <label for="<?php echo 'fsc_ex_field'.$i.'_attributes' ?>"><?php echo _('Attributes'); ?>:</label>
       <input name="<?php echo 'fsc_ex_field'.$i.'_attributes' ?>" id="<?php echo 'fsc_ex_field'.$i.'_attributes' ?>" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['ex_field'.$i.'_attributes']);  ?>" size="20" />

       <label for="<?php echo 'fsc_ex_field'.$i.'_regex' ?>"><?php echo _('Validation regex'); ?>:</label>
       <input name="<?php echo 'fsc_ex_field'.$i.'_regex' ?>" id="<?php echo 'fsc_ex_field'.$i.'_regex' ?>" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['ex_field'.$i.'_regex']);  ?>" size="20" />

       <label for="<?php echo 'fsc_ex_field'.$i.'_regex_error' ?>"><?php echo _('Regex fail message'); ?>:</label>
       <input name="<?php echo 'fsc_ex_field'.$i.'_regex_error' ?>" id="<?php echo 'fsc_ex_field'.$i.'_regex_error' ?>" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['ex_field'.$i.'_regex_error']);  ?>" size="35" /><br />

       <label for="<?php echo 'fsc_ex_field'.$i.'_label_css' ?>"><?php echo _('Label CSS'); ?>:</label>
       <input name="<?php echo 'fsc_ex_field'.$i.'_label_css' ?>" id="<?php echo 'fsc_ex_field'.$i.'_label_css' ?>" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['ex_field'.$i.'_label_css']);  ?>" size="53" />

       <label for="<?php echo 'fsc_ex_field'.$i.'_input_css' ?>"><?php echo _('Input CSS'); ?>:</label>
       <input name="<?php echo 'fsc_ex_field'.$i.'_input_css' ?>" id="<?php echo 'fsc_ex_field'.$i.'_input_css' ?>" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['ex_field'.$i.'_input_css']);  ?>" size="53" /><br />

       <label for="<?php echo 'fsc_ex_field'.$i.'_notes' ?>"><?php printf(_('HTML before form field %d:'),$i); ?></label>
       <input name="<?php echo 'fsc_ex_field'.$i.'_notes' ?>" id="<?php echo 'fsc_ex_field'.$i.'_notes' ?>" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['ex_field'.$i.'_notes']);  ?>" size="100" /><br />

       <label for="<?php echo 'fsc_ex_field'.$i.'_notes_after' ?>"><?php printf(_('HTML after form field %d:'),$i); ?></label>
       <input name="<?php echo 'fsc_ex_field'.$i.'_notes_after' ?>" id="<?php echo 'fsc_ex_field'.$i.'_notes_after' ?>" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['ex_field'.$i.'_notes_after']);  ?>" size="100" />

</fieldset>
      <?php
      } // end foreach
      ?>

<br />

 <label for="fsc_max_fields"><?php echo _('Number of available extra fields'); ?>:</label>
 <input name="fsc_max_fields" id="fsc_max_fields"  class="text-effect" type="text" onclick="return alert('<?php echo _('Caution: Increase the number of extra fields as needed, but make sure you do not change to a lower number than what is being used on this form.'); ?>')" value="<?php echo $this->absint($fsc_opt['max_fields']);  ?>" size="3" />
<a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_max_fields_tip');"><?php echo _('help'); ?></a>
 <div style="text-align:left; display:none" id="fsc_max_fields_tip">
   <?php echo _('Caution: Increase the number of extra fields as needed, but make sure you do not change to a lower number than what is being used on this form.'); ?>
 </div>

<br />
      <input name="fsc_ex_fields_after_msg" id="fsc_ex_fields_after_msg" type="checkbox" <?php if( $fsc_opt['ex_fields_after_msg'] == 'true' ) echo 'checked="checked"'; ?> />
      <label for="fsc_ex_fields_after_msg"><?php echo _('Move extra fields to after the Message field.'); ?></label>
      <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_ex_fields_after_msg_tip');"><?php echo _('help'); ?></a>
      <div style="text-align:left; display:none" id="fsc_ex_fields_after_msg_tip">
      <?php echo _('Normally the extra fields are inserted into the form between the E-mail address and the Subject fields. Enabling this setting will move the extra fields to after the Message field.'); ?>
      </div>
<br />

      <label for="fsc_date_format"><?php echo _('Date field - Date format:'); ?></label>
      <select id="fsc_date_format" name="fsc_date_format">
<?php
$selected = '';
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
foreach ($cal_date_array as $k => $v) {
 if ($fsc_opt['date_format'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>
       <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_date_format_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_date_format_tip">
       <?php echo _('Use to set the date format for the date field.'); ?>
       </div>
<br />

       <label for="fsc_cal_start_day"><?php echo _('Date field - Calendar Start Day of the Week'); ?>:</label><input name="fsc_cal_start_day" id="fsc_cal_start_day"  class="text-effect" type="text" value="<?php echo $this->absint($fsc_opt['cal_start_day']);  ?>" size="3" />
       <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_cal_start_day_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_cal_start_day_tip">
       <?php echo _('Use to set the day the week the date field calendar will start on: 0(Sun) to 6(Sat).'); ?>
       </div>
<br />

      <label for="fsc_time_format"><?php echo _('Time field - Time format:'); ?></label>
      <select id="fsc_time_format" name="fsc_time_format">
<?php
$selected = '';
$time_format_array = array(
'12' => $this->ctf_output_string(_('12 Hour')),
'24' => $this->ctf_output_string(_('24 Hour')),
);
foreach ($time_format_array as $k => $v) {
 if ($fsc_opt['time_format'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>
       <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_time_format_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_time_format_tip">
       <?php echo _('Use to set the time format for the time field.'); ?>
       </div>
<br />

        <label for="fsc_attach_types"><?php echo _('Attached files acceptable types'); ?>:</label><input name="fsc_attach_types" id="fsc_attach_types"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['attach_types']);  ?>" size="60" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_attach_types_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_attach_types_tip">
        <?php echo _('Set the acceptable file types for the file attachment feature. Any file type not on this list will be rejected.'); ?>
        <?php echo _('Separate each file type with a comma character. example:'); ?>
        doc,pdf,txt,gif,jpg,jpeg,png
        </div>
<br />

        <label for="fsc_attach_size"><?php echo _('Attached files maximum size allowed'); ?>:</label><input name="fsc_attach_size" id="fsc_attach_size"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['attach_size']);  ?>" size="30" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_attach_size_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_attach_size_tip">
        <?php echo _('Set the acceptable maximum file size for the file attachment feature.'); ?><br />
        <?php echo _('example: 1mb equals one Megabyte, 1kb equals one Kilobyte');
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));
        $upload_mb = min($max_upload, $max_post, $memory_limit);
        ?><br />
        <?php echo _('Note: Maximum size is limited to available server resources and various PHP settings. Very few servers will accept more than 2mb. Sizes under 1mb will usually have best results. examples:'); ?>
        500kb, 800kb, 1mb, 1.5mb, 2mb
        <?php echo _('Note: If you set the value higher than your server can handle, users will have problems uploading big files. The form can time out and may not even show an error.'); ?>
        <b><?php echo _('Your server will not allow uploading files larger than than:');  echo " $upload_mb"; ?>mb</b>
        </div>
<br />

        <input name="fsc_textarea_html_allow" id="fsc_textarea_html_allow" type="checkbox" <?php if( $fsc_opt['textarea_html_allow'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_textarea_html_allow"><?php echo _('Enable users to send HTML code in the textarea extra field types.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_textarea_html_allow_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_textarea_html_allow_tip">
        <?php echo _('Enable only if you want users to be able to send HTML code in the textarea extra field types. This is disabled by default for better security. HTML code is only needed for sharing embedded video links, PHP code samples, etc.'); ?>
        </div>
<br />

        <input name="fsc_enable_areyousure" id="fsc_enable_areyousure" type="checkbox" <?php if( $fsc_opt['enable_areyousure'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_enable_areyousure"><?php echo _('Enable an "Are you sure?" popup for the submit button.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_enable_areyousure_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_enable_areyousure_tip">
        <?php echo _('When a visitor clicks the form submit button, a popup message will ask "Are you sure?". This message can be changed in the "change field labels" settings below.'); ?>
        </div>
<br />

        <input name="fsc_enable_reset" id="fsc_enable_reset" type="checkbox" <?php if( $fsc_opt['enable_reset'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_enable_reset"><?php echo _('Enable a "Reset" button on the form.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_enable_reset_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_enable_reset_tip">
        <?php echo _('When a visitor clicks a reset button, the form entries are reset to the default values.'); ?>
        </div>
<br />

        <input name="fsc_enable_credit_link" id="fsc_enable_credit_link" type="checkbox" <?php if ( $fsc_opt['enable_credit_link'] == 'true' ) echo ' checked="checked" '; ?> />
        <label for="fsc_enable_credit_link"><?php echo _('Enable plugin credit link:') ?></label> <small><?php echo _('Powered by'). ' <a href="http://www.FastSecureContactForm.com/" target="_new">'. _('Fast Secure Contact Form - PHP'); ?></a></small>

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('Redirect:').' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>

<fieldset>

        <input name="fsc_redirect_enable" id="fsc_redirect_enable" type="checkbox" <?php if( $fsc_opt['redirect_enable'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_redirect_enable"><?php echo _('Enable redirect after the message sends'); ?>.</label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_redirect_enable_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_redirect_enable_tip">
        <?php echo _('If enabled: After a user sends a message, the web browser will display "message sent" for x seconds, then redirect to the redirect URL. This can be used to redirect to the blog home page, or a custom "Thank You" page.'); ?>
        </div>
<br />

        <label for="fsc_redirect_seconds"><?php echo _('Redirect delay in seconds'); ?>:</label>
        <input name="fsc_redirect_seconds" id="fsc_redirect_seconds"  class="text-effect" type="text" value="<?php echo $this->absint($fsc_opt['redirect_seconds']);  ?>" size="3" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_redirect_seconds_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_redirect_seconds_tip">
        <?php echo _('How many seconds the web browser will display "message sent" before redirecting to the redirect URL. Values of 0-60 are allowed.'); ?>
        </div>
<br />

        <label for="fsc_redirect_url"><?php echo _('Redirect URL'); ?>:</label><input name="fsc_redirect_url" id="fsc_redirect_url"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['redirect_url']);  ?>" size="50" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_redirect_url_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_redirect_url_tip">
        <?php echo _('The form will redirect to this URL after success. This can be used to redirect to the site home page, or a custom "Thank You" page.'); ?>
        <?php echo _('Use FULL URL including http:// for best results.'); ?>
        </div>
        <br />
      <?php
       if( $fsc_opt['redirect_query'] == 'true' &&  $fsc_opt['redirect_enable'] != 'true') {
         echo '<div class="error">';
         echo _('Warning: Enabling this setting requires the "Enable redirect" to also be set.');
         echo "</div>\n";
       }
       ?>
        <input name="fsc_redirect_query" id="fsc_redirect_query" type="checkbox" <?php if( $fsc_opt['redirect_query'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_redirect_query"><?php echo _('Enable posted data to be sent as a query string on the redirect URL.'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_redirect_query_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_redirect_query_tip">
        <?php echo _('If enabled: The posted data is sent to the redirect URL. This can be used to send the posted data via GET query string to a another form.'); ?>
        </div>
        <br />
        <a href="http://www.fastsecurecontactform.com/sending-data-by-query-string" target="_new"><?php echo _('FAQ: Posted data can be sent as a query string on the redirect URL'); ?></a>
        <br />

<table style="border:none; margin:20px; padding:20px;">
  <tr>
  <td valign="bottom">

        <label for="fsc_redirect_ignore"><?php echo _('Query string fields to ignore'); ?>:</label>
      <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_redirect_ignore_tip');"><?php echo _('help'); ?></a><br />
      <div style="text-align:left; display:none" id="fsc_redirect_ignore_tip">
        <?php echo _('Optional list of field names for fields you do not want included in the query string.') ?><br />
        <?php echo _('Start each entry on a new line.'); ?><br />
		<?php echo _('Available fields on this form:'); ?>
		<span style="margin: 2px 0" dir="ltr"><br />
        <?php
       // show available fields
       foreach ($av_fld_arr as $i)
         echo "$i<br />";
        ?>
        </span>
      </div>
      <textarea rows="4" cols="25" name="fsc_redirect_ignore" id="fsc_redirect_ignore"><?php echo $fsc_opt['redirect_ignore']; ?></textarea>
      <br />

 </td><td valign="bottom">

      <label for="fsc_redirect_rename"><?php echo _('Query string fields to rename'); ?>:</label>
      <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_redirect_rename_tip');"><?php echo _('help'); ?></a><br />
      <div style="text-align:left; display:none" id="fsc_redirect_rename_tip">
        <?php echo _('Optional list of field names for fields that need to be renamed for the query string.') ?><br />
        <?php echo _('Start each entry on a new line.'); ?><br />
        <?php echo _('Type the old field name separated by the equals character, then type the new name, like this: oldname=newname'); ?><br />
		<?php echo _('Examples:'); ?>
		<span style="margin: 2px 0" dir="ltr"><br />
        from_name=name<br />
		from_email=email</span><br />
        <?php echo _('Available fields on this form:'); ?>
		<span style="margin: 2px 0" dir="ltr"><br />
        <?php
       // show available fields
       foreach ($av_fld_arr as $i)
         echo "$i<br />";
        ?>
        </span>
      </div>
      <textarea rows="4" cols="25" name="fsc_redirect_rename" id="fsc_redirect_rename"><?php echo $fsc_opt['redirect_rename']; ?></textarea>
      <br />

  </td><td valign="bottom">

      <label for="fsc_redirect_add"><?php echo _('Query string key value pairs to add'); ?>:</label>
      <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_redirect_add_tip');"><?php echo _('help'); ?></a><br />
      <div style="text-align:left; display:none" id="fsc_redirect_add_tip">
        <?php echo _('Optional list of key value pairs that need to be added.') ?><br />
        <?php echo _('Sometimes the outgoing connection will require fields that were not posted on your form.') ?><br />
        <?php echo _('Start each entry on a new line.'); ?><br />
        <?php echo _('Type the key separated by the equals character, then type the value, like this key=value'); ?><br />
		<?php echo _('Examples:'); ?>
		<span style="margin: 2px 0" dir="ltr"><br />
        account=3629675<br />
		newsletter=join<br />
		action=signup</span><br />
      </div>
      <textarea rows="4" cols="25" name="fsc_redirect_add" id="fsc_redirect_rename"><?php echo $fsc_opt['redirect_add']; ?></textarea>
      <br />

 </td>
 </tr>
 </table>

      <?php
       if( $fsc_opt['redirect_email_off'] == 'true' && ($fsc_opt['redirect_enable'] != 'true' || $fsc_opt['redirect_query'] != 'true') ) {
         echo '<div class="error">';
         echo _('Warning: Enabling this setting requires the "Enable redirect" and "Enable posted data to be sent as a query string" to also be set.');
         echo "</div>\n";
       }
       ?>

       <?php
       if( $fsc_opt['redirect_email_off'] == 'true' && $fsc_opt['redirect_enable'] == 'true' && $fsc_opt['redirect_query'] == 'true' ) {
        ?><div class="updated"><strong><?php echo _('Warning: You have turned off email sending in the setting below. This is just a reminder in case that was a mistake. If that is what you intended, then ignore this message.'); ?></strong></div><?php
       }
       ?>
        <input name="fsc_redirect_email_off" id="fsc_redirect_email_off" type="checkbox" <?php if( $fsc_opt['redirect_email_off'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_redirect_email_off"><?php echo _('Disable email sending (use only when required while you have enabled query string on the redirect URL).'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_redirect_email_off_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_redirect_email_off_tip">
        <?php echo _('No email will be sent to you!! The posted data will ONLY be sent to the redirect URL. This can be used to send the posted data via GET query string to a another form. Note: the autoresponder will still send email if it is enabled.'); ?>
        </div>
        <br />

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('Silent Remote Sending:') .' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>
<fieldset>

   <?php echo _('Posted form data can be sent silently to a remote form using CURL and the method GET or POST.'); ?>
   <br />
       <?php
       if( $fsc_opt['silent_send'] != 'off' && !function_exists('curl_init') ) {
         echo '<div class="error">';
         echo _('Warning: Enabling this setting will not work because your PHP server does not have CURL support.');
         echo "</div>\n";
       }
       ?>
   <a href="http://www.fastsecurecontactform.com/send-form-data-elsewhere" target="_new"><?php echo _('FAQ: Send the posted form data to another site.'); ?></a>
   <br />
   <br />

      <label for="fsc_silent_send"><?php echo _('Silent Remote Sending:'); ?></label>
      <select id="fsc_silent_send" name="fsc_silent_send">
<?php
$silent_send_array = array(
'off' => $this->ctf_output_string(_('Off')),
'get' => $this->ctf_output_string(_('Enabled: Method GET')),
'post' => $this->ctf_output_string(_('Enabled: Method POST')),
);
$selected = '';
foreach ($silent_send_array as $k => $v) {
 if ($fsc_opt['silent_send'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>

        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_silent_send_tip');">

        <?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_silent_send_tip">
        <?php echo _('If enabled: After a user sends a message, the form can silently send the posted data to a third party remote URL. This can be used for a third party service such as a mailing list API.'); ?>
        <?php echo ' '; echo _('Select method GET or POST based on the remote API requirement.'); ?>
        </div>
        <br />

       <?php
       if( $fsc_opt['silent_send'] != 'off' &&  $fsc_opt['silent_url'] == '') {
         echo '<div class="error">';
         echo _('Warning: Enabling this setting requires the "Silent Remote URL" to also be set.');
         echo "</div>\n";
       }
       ?>

        <label for="fsc_silent_url"><?php echo _('Silent Remote URL'); ?>:</label><input name="fsc_silent_url" id="fsc_silent_url" type="text" value="<?php echo $fsc_opt['silent_url'];  ?>" size="50" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_silent_url_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_silent_url_tip">
        <?php echo _('The form will silently send the form data to this URL after success. This can be used for a third party service such as a mailing list API.'); ?>
        <?php echo _('Use FULL URL including http:// for best results.'); ?>
        </div>
        <br />

<table style="border:none;" cellspacing="20">
  <tr>
  <td valign="bottom">

        <label for="fsc_silent_ignore"><?php echo _('Silent send fields to ignore'); ?>:</label>
      <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_silent_ignore_tip');"><?php echo _('help'); ?></a><br />
      <div style="text-align:left; display:none" id="fsc_silent_ignore_tip">
        <?php echo _('Optional list of field names for fields you do not want included.') ?><br />
        <?php echo _('Start each entry on a new line.'); ?><br />
		<?php echo _('Available fields on this form:'); ?>
		<span style="margin: 2px 0" dir="ltr"><br />
        <?php
       // show available fields
       foreach ($av_fld_arr as $i)
         echo "$i<br />";
        ?>
        </span>
      </div>
      <textarea rows="4" cols="25" name="fsc_silent_ignore" id="fsc_silent_ignore"><?php echo $fsc_opt['silent_ignore']; ?></textarea>
      <br />

 </td><td valign="bottom">

      <label for="fsc_silent_rename"><?php echo _('Silent send fields to rename'); ?>:</label>
      <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_silent_rename_tip');"><?php echo _('help'); ?></a><br />
      <div style="text-align:left; display:none" id="fsc_silent_rename_tip">
        <?php echo _('Optional list of field names for fields that need to be renamed.') ?><br />
        <?php echo _('Start each entry on a new line.'); ?><br />
        <?php echo _('Type the old field name separated by the equals character, then type the new name, like this: oldname=newname'); ?><br />
		<?php echo _('Examples:'); ?>
		<span style="margin: 2px 0" dir="ltr"><br />
        from_name=name<br />
		from_email=email</span><br />
        <?php echo _('Available fields on this form:'); ?>
		<span style="margin: 2px 0" dir="ltr"><br />
        <?php
       // show available fields
       foreach ($av_fld_arr as $i)
         echo "$i<br />";
        ?>
        </span>
      </div>
      <textarea rows="4" cols="25" name="fsc_silent_rename" id="fsc_silent_rename"><?php echo $fsc_opt['silent_rename']; ?></textarea>
      <br />

  </td><td valign="bottom">

      <label for="fsc_silent_add"><?php echo _('Silent send key value pairs to add'); ?>:</label>
      <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_silent_add_tip');"><?php echo _('help'); ?></a><br />
      <div style="text-align:left; display:none" id="fsc_silent_add_tip">
        <?php echo _('Optional list of key value pairs that need to be added.') ?><br />
        <?php echo _('Sometimes the outgoing connection will require fields that were not posted on your form.') ?><br />
        <?php echo _('Start each entry on a new line.'); ?><br />
        <?php echo _('Type the key separated by the equals character, then type the value, like this: key=value'); ?><br />
		<?php echo _('Examples:'); ?>
		<span style="margin: 2px 0" dir="ltr"><br />
        account=3629675<br />
		newsletter=join<br />
		action=signup</span><br />
      </div>
      <textarea rows="4" cols="25" name="fsc_silent_add" id="fsc_silent_add"><?php echo $fsc_opt['silent_add']; ?></textarea>
      <br />

 </td>
 </tr>
 </table>

      <?php
       if( $fsc_opt['silent_email_off'] == 'true' && ($fsc_opt['silent_send'] == 'off' || $fsc_opt['silent_url'] == '') ) {
         echo '<div class="error">';
         echo _('Warning: Enabling this setting requires the "Silent Remote Send" and "Silent Remote URL" to also be set.');
         echo "</div>\n";
       }
       ?>

       <?php
       if( $fsc_opt['silent_email_off'] == 'true' && $fsc_opt['silent_send'] != 'off' ) {
        ?><div class="updated"><strong><?php echo _('Warning: You have turned off email sending in the setting below. This is just a reminder in case that was a mistake. If that is what you intended, then ignore this message.'); ?></strong></div><?php
       }
       ?>
        <input name="fsc_silent_email_off" id="fsc_silent_email_off" type="checkbox" <?php if( $fsc_opt['silent_email_off'] == 'true' ) echo 'checked="checked"'; ?> />
        <label for="fsc_silent_email_off"><?php echo _('Disable email sending (use only when required while you have enabled silent remote sending).'); ?></label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_silent_email_off_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_silent_email_off_tip">
        <?php echo _('No email will be sent to you!! The posted data will ONLY be sent to the silent remote URL. This can be used for a third party service such as a mailing list API. Note: the autoresponder will still send email if it is enabled.'); ?>
        </div>
        <br />

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('Style:').' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>

<fieldset>

        <input name="fsc_reset_styles" id="fsc_reset_styles" type="checkbox" />
        <label for="fsc_reset_styles"><strong><?php echo _('Reset the styles to labels on top (default).') ?></strong></label><br />

        <input name="fsc_reset_styles_left" id="fsc_reset_styles_left" type="checkbox" />
        <label for="fsc_reset_styles_left"><strong><?php echo _('Reset the styles to labels on left.') ?></strong></label><br />

        <input name="fsc_border_enable" id="fsc_border_enable" type="checkbox" <?php if ( $fsc_opt['border_enable'] == 'true' ) echo ' checked="checked" '; ?> />
        <label for="fsc_border_enable"><?php echo _('Enable border on contact form') ?>.</label>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_border_enable_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_border_enable_tip">
       <?php echo _('Enable to draw a fieldset box around all the form elements. The default label for the fieldset is "Contact Form:", but you can change it in the "Fields:" section below.'); ?>
       </div>
<br />
<br />
        <strong><?php echo _('Modifiable CSS Style Feature:'); ?></strong>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_css_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_css_tip">
        <?php echo _('Use to adjust the font colors or other styling of the contact form.'); ?><br />
        <?php echo _('You can use inline css, or add a class property to be used by your own stylsheet.'); ?><br />
        <?php echo _('Acceptable Examples:'); ?><br />
        text-align:left; color:#000000; background-color:#CCCCCC;<br />
        style="text-align:left; color:#000000; background-color:#CCCCCC;"<br />
        class="input"
        </div>
<br />

        <label for="fsc_form_style"><?php echo _('CSS style for form DIV on the contact form'); ?>:</label><input name="fsc_form_style" id="fsc_form_style"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['form_style']);  ?>" size="60" />
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_form_style_tip');"><?php echo _('help'); ?></a>
        <div style="text-align:left; display:none" id="fsc_form_style_tip">
        <?php echo _('Use to adjust the style of the contact form border (if border is enabled).'); ?>
        </div>
<br />
        <label for="fsc_border_style"><?php echo _('CSS style for border on the contact form'); ?>:</label><input name="fsc_border_style" id="fsc_border_style"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['border_style']);  ?>" size="60" /><br />
        <label for="fsc_required_style"><?php echo _('CSS style for required field text on the contact form'); ?>:</label><input name="fsc_required_style" id="fsc_required_style"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['required_style']);  ?>" size="60" /><br />
        <label for="fsc_notes_style"><?php echo _('CSS style for extra field HTML on the contact form'); ?>:</label><input name="fsc_notes_style" id="fsc_notes_style" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['notes_style']);  ?>" size="60" /><br />
        <label for="fsc_title_style"><?php echo _('CSS style for form input titles on the contact form'); ?>:</label><input name="fsc_title_style" id="fsc_title_style"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_style']);  ?>" size="60" /><br />
        <label for="fsc_field_style"><?php echo _('CSS style for form input fields on the contact form'); ?>:</label><input name="fsc_field_style" id="fsc_field_style"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['field_style']);  ?>" size="60" /><br />
        <label for="fsc_field_div_style"><?php echo _('CSS style for form input fields DIV on the contact form'); ?>:</label><input name="fsc_field_div_style" id="fsc_field_div_style"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['field_div_style']);  ?>" size="60" /><br />
        <label for="fsc_error_style"><?php echo _('CSS style for form input errors on the contact form'); ?>:</label><input name="fsc_error_style" id="fsc_error_style"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_style']);  ?>" size="60" /><br />
        <label for="fsc_select_style"><?php echo _('CSS style for contact drop down select on the contact form'); ?>:</label><input name="fsc_select_style" id="fsc_select_style"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['select_style']);  ?>" size="60" /><br />
        <label for="fsc_captcha_div_style_sm"><?php echo _('CSS style for Small CAPTCHA DIV on the contact form'); ?>:</label><input name="fsc_captcha_div_style_sm" id="fsc_captcha_div_style_sm" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['captcha_div_style_sm']);  ?>" size="60" /><br />
        <label for="fsc_captcha_div_style_m"><?php echo _('CSS style for CAPTCHA DIV on the contact form'); ?>:</label><input name="fsc_captcha_div_style_m" id="fsc_captcha_div_style_m" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['captcha_div_style_m']);  ?>" size="60" /><br />
        <label for="fsc_captcha_input_style"><?php echo _('CSS style for CAPTCHA input field on the contact form'); ?>:</label><input name="fsc_captcha_input_style" id="fsc_captcha_input_style"  class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['captcha_input_style']);  ?>" size="60" /><br />
        <label for="fsc_submit_div_style"><?php echo _('CSS style for Submit DIV on the contact form'); ?>:</label><input name="fsc_submit_div_style" id="fsc_submit_div_style" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['submit_div_style']);  ?>" size="60" /><br />
        <label for="fsc_button_style"><?php echo _('CSS style for Submit button on the contact form'); ?>:</label><input name="fsc_button_style" id="fsc_button_style" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['button_style']);  ?>" size="60" /><br />
        <label for="fsc_reset_style"><?php echo _('CSS style for Reset button on the contact form'); ?>:</label><input name="fsc_reset_style" id="fsc_reset_style" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['reset_style']);  ?>" size="60" /><br />
        <label for="fsc_powered_by_style"><?php echo _('CSS style for "Powered by" message on the contact form'); ?>:</label><input name="fsc_powered_by_style" id="fsc_powered_by_style" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['powered_by_style']);  ?>" size="60" />
<br />

       <label for="fsc_field_size"><?php echo _('Input Text Field Size'); ?>:</label><input name="fsc_field_size" id="fsc_field_size" class="text-effect" type="text" value="<?php echo $this->absint($fsc_opt['field_size']);  ?>" size="3" />
       <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_field_size_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_field_size_tip">
       <?php echo _('Use to adjust the size of the contact form text input fields.'); ?>
       </div>
<br />

       <label for="fsc_captcha_field_size"><?php echo _('Input CAPTCHA Field Size'); ?>:</label><input name="fsc_captcha_field_size" id="fsc_captcha_field_size" class="text-effect" type="text" value="<?php echo $this->absint($fsc_opt['captcha_field_size']);  ?>" size="3" />
       <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_captcha_field_size_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_captcha_field_size_tip">
       <?php echo _('Use to adjust the size of the contact form CAPTCHA input field.'); ?>
       </div>
<br />

       <label for="fsc_text_cols"><?php echo _('Input Textarea Field Cols'); ?>:</label><input name="fsc_text_cols" id="fsc_text_cols" class="text-effect" type="text" value="<?php echo $this->absint($fsc_opt['text_cols']);  ?>" size="3" />
       <label for="fsc_text_rows"><?php echo _('Rows'); ?>:</label><input name="fsc_text_rows" id="fsc_text_rows" class="text-effect" type="text" value="<?php echo $this->absint($fsc_opt['text_rows']);  ?>" size="3" />
       <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_text_rows_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_text_rows_tip">
       <?php echo _('Use to adjust the size of the contact form message textarea.'); ?>
       </div>
<br />

       <input name="fsc_aria_required" id="fsc_aria_required" type="checkbox" <?php if( $fsc_opt['aria_required'] == 'true' ) echo 'checked="checked"'; ?> />
       <label for="fsc_aria_required"><?php echo _('Enable aria-required tags for screen readers'); ?>.</label>
       <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_aria_required_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_aria_required_tip">
       <?php echo _('aria-required is a form input WAI ARIA tag. Screen readers use it to determine which fields are required. Enabling this is good for accessability, but will cause the HTML to fail the W3C Validation (there is no attribute "aria-required"). WAI ARIA attributes are soon to be accepted by the HTML validator, so you can safely ignore the validation error it will cause.'); ?>
       </div>
</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('Fields:').' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>

<fieldset>

   <strong><?php echo _('Change field labels:'); ?></strong>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_text_fields_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_text_fields_tip">
       <?php echo _('Some people wanted to change the labels for the contact form. These fields can be filled in to override the standard labels.'); ?>
       </div>
<br />

        <input name="fsc_req_field_label_enable" id="fsc_req_field_label_enable" type="checkbox" <?php if ( $fsc_opt['req_field_label_enable'] == 'true' ) echo ' checked="checked" '; ?> />
        <label for="fsc_req_field_label_enable"><?php echo _('Enable required field label on contact form:') ?></label> <?php echo ($fsc_opt['tooltip_required'] != '') ? $fsc_opt['req_field_indicator'] .$fsc_opt['tooltip_required'] : $fsc_opt['req_field_indicator'] . _('(denotes required field)'); ?><br />

        <input name="fsc_req_field_indicator_enable" id="fsc_req_field_indicator_enable" type="checkbox" <?php if ( $fsc_opt['req_field_indicator_enable'] == 'true' ) echo ' checked="checked" '; ?> />
        <label for="fsc_req_field_indicator_enable"><?php echo _('Enable required field indicators on contact form') ?>.</label><br />

        <label for="fsc_req_field_indicator"><?php echo _('Required field indicator:'); ?></label><input name="fsc_req_field_indicator" id="fsc_req_field_indicator" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['req_field_indicator']);  ?>" size="20" /><br />

        <label for="fsc_tooltip_required"><?php echo _('(denotes required field)'); ?></label><input name="fsc_tooltip_required" id="fsc_tooltip_required" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['tooltip_required']);  ?>" size="50" /><br />
        <label for="fsc_title_border"><?php echo _('Contact Form'); ?>:</label><input name="fsc_title_border" id="fsc_title_border" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_border']);  ?>" size="50" /><br />
        <label for="fsc_title_dept"><?php echo _('Department to Contact'); ?>:</label><input name="fsc_title_dept" id="fsc_title_dept" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_dept']);  ?>" size="50" /><br />
        <label for="fsc_title_select"><?php echo _('Select'); ?>:</label><input name="fsc_title_select" id="fsc_title_select" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_select']);  ?>" size="50" /><br />
        <label for="fsc_title_name"><?php echo _('Name'); ?>:</label><input name="fsc_title_name" id="fsc_title_name" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_name']);  ?>" size="50" /><br />
        <label for="fsc_title_fname"><?php echo _('First Name'); ?>:</label><input name="fsc_title_fname" id="fsc_title_fname" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_fname']);  ?>" size="50" /><br />
        <label for="fsc_title_lname"><?php echo _('Last Name'); ?>:</label><input name="fsc_title_lname" id="fsc_title_lname" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_lname']);  ?>" size="50" /><br />
        <label for="fsc_title_mname"><?php echo _('Middle Name'); ?>:</label><input name="fsc_title_mname" id="fsc_title_mname" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_mname']);  ?>" size="50" /><br />
        <label for="fsc_title_miname"><?php echo _('Middle Initial'); ?>:</label><input name="fsc_title_miname" id="fsc_title_miname" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_miname']);  ?>" size="50" /><br />
        <label for="fsc_title_email"><?php echo _('E-Mail Address'); ?>:</label><input name="fsc_title_email" id="fsc_title_email" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_email']);  ?>" size="50" /><br />
        <label for="fsc_title_email2"><?php echo _('E-Mail Address again'); ?>:</label><input name="fsc_title_email2" id="fsc_title_email2" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_email2']);  ?>" size="50" /><br />
        <label for="fsc_title_email2"><?php echo _('Please enter your E-mail Address a second time.'); ?></label><input name="fsc_title_email2_help" id="fsc_title_email2_help" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_email2_help']);  ?>" size="50" /><br />
        <label for="fsc_title_subj"><?php echo _('Subject'); ?>:</label><input name="fsc_title_subj" id="fsc_title_subj" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_subj']);  ?>" size="50" /><br />
        <label for="fsc_title_mess"><?php echo _('Message'); ?>:</label><input name="fsc_title_mess" id="fsc_title_mess" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_mess']);  ?>" size="50" /><br />
        <label for="fsc_title_capt"><?php echo _('CAPTCHA Code'); ?>:</label><input name="fsc_title_capt" id="fsc_title_capt" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_capt']);  ?>" size="50" /><br />
        <label for="fsc_title_submit"><?php echo _('Submit'); ?></label><input name="fsc_title_submit" id="fsc_title_submit" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_submit']);  ?>" size="50" /><br />
        <label for="fsc_title_reset"><?php echo _('Reset'); ?></label><input name="fsc_title_reset" id="fsc_title_reset" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_reset']);  ?>" size="50" /><br />
        <label for="fsc_title_areyousure"><?php echo _('Are you sure?'); ?></label><input name="fsc_title_areyousure" id="fsc_title_areyousure" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['title_areyousure']);  ?>" size="50" /><br />
        <label for="fsc_text_message_sent"><?php echo _('Your message has been sent, thank you.'); ?></label><input name="fsc_text_message_sent" id="fsc_text_message_sent" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['text_message_sent']);  ?>" size="50" /><br />

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('Tooltips:').' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>

<fieldset>
     <strong><?php echo _('Change tooltips labels:'); ?></strong>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_text_tools_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_text_tools_tip">
       <?php echo _('Some people wanted to change the labels for the contact form. These fields can be filled in to override the standard labels.'); ?>
       </div>
 <br />

        <label for="fsc_tooltip_captcha"><?php echo _('CAPTCHA Image'); ?></label><input name="fsc_tooltip_captcha" id="fsc_tooltip_captcha" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['tooltip_captcha']);  ?>" size="50" /><br />
        <label for="fsc_tooltip_audio"><?php echo _('CAPTCHA Audio'); ?></label><input name="fsc_tooltip_audio" id="fsc_tooltip_audio" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['tooltip_audio']);  ?>" size="50" /><br />
        <label for="fsc_tooltip_refresh"><?php echo _('Refresh Image'); ?></label><input name="fsc_tooltip_refresh" id="fsc_tooltip_refresh" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['tooltip_refresh']);  ?>" size="50" /><br />
        <label for="fsc_tooltip_filetypes"><?php echo _('Acceptable file types:'); ?></label><input name="fsc_tooltip_filetypes" id="fsc_tooltip_filetypes" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['tooltip_filetypes']);  ?>" size="50" /><br />
        <label for="fsc_tooltip_filesize"><?php echo _('Maximum file size:'); ?></label><input name="fsc_tooltip_filesize" id="fsc_tooltip_filesize" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['tooltip_filesize']);  ?>" size="50" />

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

<div class="form-tab"><?php echo _('Errors:').' '. sprintf(_('(form %d)'),$form_id);?></div>
<div class="clear"></div>

<fieldset>

    <strong><?php echo _('Change error labels:'); ?></strong>
        <a style="cursor:pointer;" title="<?php echo _('Click for Help!'); ?>" onclick="toggleVisibility('fsc_error_fields_tip');"><?php echo _('help'); ?></a>
       <div style="text-align:left; display:none" id="fsc_error_fields_tip">
       <?php echo _('Some people wanted to change the error messages for the contact form. These fields can be filled in to override the standard included error messages.'); ?>
       </div>
<br />
         <label for="fsc_error_contact_select"><?php echo _('Selecting a contact is required.'); ?></label><input name="fsc_error_contact_select" id="fsc_error_contact_select" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_contact_select']);  ?>" size="50" /><br />
         <label for="fsc_error_name"><?php echo _('Your name is required.'); ?></label><input name="fsc_error_name" id="fsc_error_name" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_name']);  ?>" size="50" /><br />
         <label for="fsc_error_email"><?php echo _('A proper e-mail address is required.'); ?></label><input name="fsc_error_email" id="fsc_error_email" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_email']);  ?>" size="50" /><br />
         <label for="fsc_error_email2"><?php echo _('The two e-mail addresses did not match, please enter again.'); ?></label><input name="fsc_error_email2" id="fsc_error_email2" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_email2']);  ?>" size="50" /><br />
         <label for="fsc_error_field"><?php echo _('This field is required.'); ?></label><input name="fsc_error_field" id="fsc_error_field" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_field']);  ?>" size="50" /><br />
         <label for="fsc_error_subject"><?php echo _('Subject text is required.'); ?></label><input name="fsc_error_subject" id="fsc_error_subject" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_subject']);  ?>" size="50" /><br />
         <label for="fsc_error_message"><?php echo _('Message text is required.'); ?></label><input name="fsc_error_message" id="fsc_error_message" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_message']);  ?>" size="50" /><br />
         <label for="fsc_error_input"><?php echo _('Contact Form has Invalid Input'); ?></label><input name="fsc_error_input" id="fsc_error_input" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_input']);  ?>" size="50" /><br />
         <label for="fsc_error_captcha_blank"><?php echo _('Please complete the CAPTCHA.'); ?></label><input name="fsc_error_captcha_blank" id="fsc_error_captcha_blank" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_captcha_blank']);  ?>" size="50" /><br />
         <label for="fsc_error_captcha_wrong"><?php echo _('That CAPTCHA was incorrect.'); ?></label><input name="fsc_error_captcha_wrong" id="fsc_error_captcha_wrong" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_captcha_wrong']);  ?>" size="50" /><br />
         <label for="fsc_error_correct"><?php echo _('Please make corrections below and try again.'); ?></label><input name="fsc_error_correct" id="fsc_error_correct" class="text-effect" type="text" value="<?php echo $this->ctf_output_string($fsc_opt['error_correct']);  ?>" size="50" />

</fieldset>

    <p class="submit">
      <input type="submit" name="submit" value="<?php echo $this->ctf_output_string( _('Update Options')); ?> &raquo;" />
    </p>

</form>

<form action="index.php?ctf_form_num=<?php echo $form_num ?>" method="post">
<?php
//wp_nonce_field('fs-contact-form-email_test');
?>
<fieldset class="options" style="border:1px solid black; padding:10px;">
<legend><?php echo _('Send a Test E-mail'); ?></legend>
<?php echo _('If you are not receiving email from your form, try this test because it can display troubleshooting information.'); ?><br />
<?php echo _('There are settings you can use to try to fix email delivery problems, see this FAQ for help:'); ?>
 <a href="http://www.fastsecurecontactform.com/email-does-not-send" target="_blank"><?php echo _('FAQ'); ?></a><br />
<?php echo _('Type an email address here and then click Send Test to generate a test email.'); ?>
<?php
if ( !function_exists('mail') ) {
   echo '<div class="error">'. _('Warning: Your web host has the mail() function disabled. PHP cannot send email. This program will not work.').'</div>'."\n";
}
?>
<br />
<label for="fsc_to"><?php echo _('To:'); ?></label>
<input class="text-effect" type="text" name="fsc_to" id="fsc_to" value="" size="40" class="code" />
<p style="padding:0px;" class="submit">
<input type="submit" name="ctf_action" value="<?php echo _('Send Test'); ?>" />
</p>
</fieldset>
</form>

<br />

<form id="ctf_copy_settings" action="index.php?ctf_form_num=<?php echo $form_num; ?>" method="post">
<fieldset class="options" style="border:1px solid black; padding:10px;">

<legend><?php echo _('Copy Settings'); ?></legend>
<?php echo _('This tool can copy your contact form settings from this form number to any of your other forms.'); ?><br />
<?php echo _('Use to copy just the style settings, or all the settings from this form.'); ?><br />
<?php echo _('It is a good idea to backup all forms with the backup tool before you use this copy tool. Changes are permanent!'); ?><br />

<label for="fsc_copy_what"><?php echo _('What to copy:'); ?></label>
<select id="fsc_copy_what" name="fsc_copy_what">
<?php
$copy_what_array = array(
'all' => $this->ctf_output_string(sprintf(_('Form %d - all settings'),$form_id)),
'styles' => $this->ctf_output_string(sprintf(_('Form %d - style settings'),$form_id)),
);

$selected = '';
foreach ($copy_what_array as $k => $v) {
 if (isset($_POST['fsc_copy_what']) && $_POST['fsc_copy_what'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>

<label for="fsc_destination_form"><?php echo sprintf(_('Select a form to copy form %d settings to:'),$form_id); ?></label>
<select id="fsc_destination_form" name="fsc_destination_form">
<?php
$backup_type_array = array(
'all' => $this->ctf_output_string(_('All Forms')),
);
$backup_type_array["1"] = $this->ctf_output_string(sprintf(_('Form: %d'),1));
// multi-forms > 1
for ($i = 2; $i <= $fsc_gb['max_forms']; $i++) {
$backup_type_array[$i] = $this->ctf_output_string(sprintf(_('Form: %d'),$i));
}
$selected = '';
foreach ($backup_type_array as $k => $v) {
 if (isset($_POST['fsc_destination_form']) && $_POST['fsc_destination_form'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>


<input type="hidden" name="fsc_this_form" id="fsc_this_form" value="<?php echo $form_id ?>"  />
<p style="padding:0px;" class="submit">
<input type="submit" name="ctf_action" onclick="return confirm('<?php echo _('Are you sure you want to permanently make this change?'); ?>')" value="<?php echo _('Copy Settings'); ?>" />
</p>

</fieldset>
</form>

<br />

<form id="ctf_backup_settings" action="index.php?ctf_form_num=<?php echo $form_num ?>" method="post">

<fieldset class="options" style="border:1px solid black; padding:10px;">

<legend><?php echo _('Backup Settings'); ?></legend>
<?php echo _('This tool can save a backup of your contact form settings.'); ?><br />
<?php echo _('Use to transfer one, or all, of your forms from one site to another. Or just make a backup to save.'); ?><br />
<label for="fsc_backup_type"><?php echo _('Select a form to backup:'); ?></label>

<select id="fsc_backup_type" name="fsc_backup_type">
<?php
$backup_type_array = array(
'all' => $this->ctf_output_string(_('All Forms')),
);
$backup_type_array["1"] = $this->ctf_output_string(sprintf(_('Form: %d'),1));
// multi-forms > 1
for ($i = 2; $i <= $fsc_gb['max_forms']; $i++) {
$backup_type_array[$i] = $this->ctf_output_string(sprintf(_('Form: %d'),$i));
}
$selected = '';
foreach ($backup_type_array as $k => $v) {
 if (isset($_POST['fsc_backup_type']) && $_POST['fsc_backup_type'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>

<p style="padding:0px;" class="submit">
<input type="submit" name="ctf_action" value="<?php echo _('Backup Settings'); ?>" />
</p>

</fieldset>
</form>

<br />

<form enctype="multipart/form-data" id="ctf_restore_settings" action="index.php?ctf_form_num=<?php echo $form_num ?>" method="post">

<fieldset class="options" style="border:1px solid black; padding:10px;">

<legend><?php echo _('Restore Settings'); ?></legend>
<?php echo _('This tool can restore a backup of your contact form settings. If you have previously made a backup, you can restore one or all your forms.'); ?><br />
<?php echo _('It is a good idea to backup all forms with the backup tool before you restore any. Changes are permanent!'); ?><br />
<label for="fsc_restore_backup_type"><?php echo _('Select a form to restore:'); ?></label>

<select id="fsc_restore_backup_type" name="fsc_backup_type">
<?php
$backup_type_array = array(
'all' => $this->ctf_output_string(_('All Forms')),
);
$backup_type_array["1"] = $this->ctf_output_string(sprintf(_('Form: %d'),1));
// multi-forms > 1
for ($i = 2; $i <= $fsc_gb['max_forms']; $i++) {
$backup_type_array[$i] = $this->ctf_output_string(sprintf(_('Form: %d'),$i));
}
$selected = '';
foreach ($backup_type_array as $k => $v) {
 if (isset($_POST['fsc_backup_type']) && $_POST['fsc_backup_type'] == "$k")  $selected = ' selected="selected"';
 echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>'."\n";
 $selected = '';
}
?>
</select>
<br />

<label for="fsc_backup_file"><?php echo _('Upload Backup File:'); ?></label>
<input style="text-align:left; margin:0;" type="file" id="fsc_backup_file" name="fsc_backup_file" value=""  size="20" />

<p style="padding:0px;" class="submit">
<input type="submit" name="ctf_action" onclick="return confirm('<?php echo _('Are you sure you want to permanently make this change?'); ?>')" value="<?php echo _('Restore Settings'); ?>" />
</p>

</fieldset>
</form>

<p><strong><?php echo _('PHP scripts by Mike Challis:') ?></strong></p>
<ul>
<li><a href="http://www.FastSecureContactForm.com/" target="_blank"><?php echo _('Fast Secure Contact Form'); ?></a></li>
<li><a href="http://wordpress.org/extend/plugins/si-captcha-for-wordpress/" target="_blank"><?php echo _('SI CAPTCHA Anti-Spam'); ?></a></li>
<li><a href="http://wordpress.org/extend/plugins/visitor-maps/" target="_blank"><?php echo _('Visitor Maps and Who\'s Online'); ?></a></li>
<li><a href="http://www.642weather.com/weather/scripts.php" target="_blank"><?php echo _('Free PHP Scripts'); ?></a></li>
</ul>

<?php
 } // if $passed_login
?>