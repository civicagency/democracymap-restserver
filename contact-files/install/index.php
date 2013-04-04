<?php
/*
Fast and Secure Contact Form
Mike Challis
http://www.642weather.com/weather/scripts.php
*/
define('DEBUG', true);

if (!DEBUG){//do not display any error message
    error_reporting(0);
    @ini_set('display_errors','off');
}
else ini_set('display_errors','on');//displays error messages

// requires PHP 5.1 or higher
$phpversion = substr(PHP_VERSION, 0, 6);
if($phpversion >= 5.1) {
      //OK
}else{
	echo '<p><span style="color:red;">Fast Secure Contact Form requires PHP version 5.1 or higher</span><br />
    Warning: Your web host has not upgraded from PHP4 to PHP5.
    PHP4 was officially discontinued August 8, 2008 and is no longer considered safe.
    PHP5 is faster, has more features, and is and safer. You need PHP5. Please upgrade PHP in order to proceed.
    Contact your web host for support.<p>';
   echo ' <a href="../captcha/test/index.php">Requirements Test</a>';
exit;
}

install_fix_server_vars();  // Fix for IIS $_SERVER vars

$step="";
if (isset($_POST["step"])) $step=$_POST["step"];

$terms="";
if (isset($_POST["terms"])) $terms=$_POST["terms"];

$SITE_NAME="";
if (isset($_POST["SITE_NAME"])) $SITE_NAME= ctf_output_string($_POST["SITE_NAME"]);

$SITE_URL="";
if (isset($_POST["SITE_URL"])) $SITE_URL= $_POST["SITE_URL"];

$SITE_PATH="";
if (isset($_POST["SITE_PATH"])) $SITE_PATH=$_POST["SITE_PATH"];

$ADMIN_NAME="";
if (isset($_POST["ADMIN_NAME"])) $ADMIN_NAME= ctf_output_string($_POST["ADMIN_NAME"]);

$ADMIN_EMAIL="";
if (isset($_POST["ADMIN_EMAIL"])) $ADMIN_EMAIL=$_POST["ADMIN_EMAIL"];

$ADMIN_USR="";
if (isset($_POST["ADMIN_USR"])) $ADMIN_USR=$_POST["ADMIN_USR"];

$ADMIN_PWD="";
if (isset($_POST["ADMIN_PWD"])) $ADMIN_PWD=$_POST["ADMIN_PWD"];

$SITE_CHARSET="";
if (isset($_POST["SITE_CHARSET"])) $SITE_CHARSET=$_POST["SITE_CHARSET"];

$LANGUAGE="";
if (isset($_POST["LANGUAGE"])) $LANGUAGE=$_POST["LANGUAGE"];

$TIMEZONE="";
if (isset($_POST["TIMEZONE"])) $TIMEZONE=$_POST["TIMEZONE"];

  // gettext setup
  // https://launchpad.net/php-gettext/

  // gather array of available locales, en_US, it_IT
  foreach ( scandir('../languages') as $lang ) {
       if( strpos($lang,'.')==false && $lang!='.' && $lang!='..'){
			   $supported_locales[] = $lang;
	   }
  }

  $locale = 'en_US';
  $encoding = 'UTF-8';
  if (file_exists('../settings/fsc_site.php')){
     include '../settings/fsc_site.php';
     $locale = $array['language'];
     $encoding = $array['site_charset'];
  }

  // allow URL overide ?lang=it_IT
  //$this_locale = ( isset($_GET['lang']) && in_array($_GET['lang'], $supported_locales) ) ? $_GET['lang'] : $locale;

  $this_locale = ( isset($LANGUAGE) && in_array($LANGUAGE, $supported_locales) ) ? $LANGUAGE : $locale;

  if ($this_locale != 'en_US') { // do not need to load this if locale is en_US
    require_once '../gettext/gettext.inc';

    // Set the text domain as 'messages'
    $domain = 'fsc-form-'.$this_locale;

    // gettext setup
    T_setlocale(LC_MESSAGES, $this_locale);
    bindtextdomain($domain, '../languages');
    // bind_textdomain_codeset is supported only in PHP 4.2.0+
    if (function_exists('bind_textdomain_codeset'))
        bind_textdomain_codeset($domain, $encoding);
    textdomain($domain);

   }

// fixes no gettext support error: Fatal error: Call to undefined function _()
if (!function_exists('_')) {
    function _($string) {
          return $string;
    }
}


clearstatcache();
if (!file_exists('../settings/fsc_site.php')){
 if ($_POST && $step=="install"){//if there's post step=install

   $fsc_gb_settings = array(
   'site_name' =>   $SITE_NAME,
   'site_url' =>    $SITE_URL,
   'site_path' =>   $SITE_PATH,
   'admin_name' =>  $ADMIN_NAME,
   'admin_email' => $ADMIN_EMAIL,
   'admin_usr' =>   $ADMIN_USR,
   'admin_pwd' =>   $ADMIN_PWD,
   'site_charset' => $SITE_CHARSET,
   'language' =>    $LANGUAGE,
   'timezone' =>    $TIMEZONE,
   'pwd_reset_key' => '',
   );

   $fsc_gb_settings['admin_pwd'] = 'hashed_'. md5($fsc_gb_settings['admin_pwd']);
   //print_r($fsc_gb_settings);
   fsc_install_do($fsc_gb_settings);
 }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $encoding; ?>" />
	<title><?php echo _('Fast Secure Contact Form - PHP'); ?> - <?php echo _('Install'); ?></title>
	<meta name="generator" content="<?php echo _('Fast Secure Contact Form - PHP'); ?>" />
    <meta name="robots" content="noindex" /> 
	<link rel="stylesheet" type="text/css" href="style.css" media="screen" />
	<script type="text/javascript" src="../common.js"></script>
</head>
<body>
<div id="container">
    <div id="header">
        <h1><?php echo _('Fast Secure Contact Form - PHP'); ?> - <?php echo _('Install'); ?></h1>
        <ul id="nav_main">
            <li><a href="index.php" title="<?php echo _("Welcome")?>" <?php if ($step=="" || $terms==""){?>class="current"<?php }?>><?php echo _("Welcome")?></a></li>
            <li><a href="#" title="<?php echo _("Requirements")?>" <?php if ($step=="requirements" && $terms!=""){?>class="current"<?php }?>><?php echo _("Requirements")?></a></li>
            <li><a href="#" title="<?php echo _("Path Settings")?>" <?php if ($step=="path"){?>class="current"<?php }?>><?php echo _("Path Settings")?></a></li>
            <li><a href="#" title="<?php echo _("Basic Configuration")?>" <?php if ($step=="basic"){?>class="current"<?php }?>><?php echo _("Basic Configuration")?></a></li>
            <li><a href="#" title="<?php echo _("Review & Install")?>" <?php if ($step=="review" || $step=="install"){?>class="current"<?php }?>><?php echo _("Review & Install")?></a></li>
        </ul>
    </div>
    <div id="main">
<div id="content">
<?php
//echo '<pre>';
//print_r($_SERVER);
//echo '</pre>';
clearstatcache();
if (file_exists('../settings/fsc_site.php')){
      // install has already been done
       echo _('This install has already been performed.');
       echo '<br />';
       echo _('You can change settings on the <a href="../admin/index.php">admin page</a>.');
}else{//config file doesn't exists
if ($_POST && $step=="install"){//if there's post step=install
}else{//else post
if ($step=="" || $terms==""){?>
<h2><?php echo _("Welcome to the installation");?></h2>
<p>
<?php echo _("Welcome to the super easy and fast installation");?>.
<?php echo _("If you need any help please contact");?> <a href="http://www.fastsecurecontactform.com/support" target="_blank"><?php echo _("support");?></a>.
</p>
<p>
<?php if ($_POST && $terms==""){?><a href="#terms"><?php echo _("Please read the following license agreement and accept the terms to continue");?>:</a></strong>
<?php } else {?><?php echo _("Please read the following license agreement and accept the terms to continue");?>:<?php }?></p>
<iframe width="770" height="250" src="http://www.gnu.org/licenses/gpl.txt" frameborder="1"></iframe>
<form method="post" action="">
<input type="hidden" name="step" id="step" value="requirements" />
<p>
<a name="terms"></a>
<label for="terms"><input type="checkbox" id="terms" name="terms" value="1" /> <?php echo _("I accept the license terms");?>.</label>
</p>
<p>
	<label><?php echo _("Site Language");?>:</label>
	<select name="LANGUAGE" >
	    <?php
	    $languages = scandir("../languages");
        if(!in_array('en_US',$languages) ) {
           $languages[] = 'en_US';
           sort($languages);
        }
	    foreach ($languages as $lang) {
            if( strpos($lang,'.')==false && $lang!='.' && $lang!='..' && !preg_match("/^_/",$lang)){
			   echo "<option value=\"$lang\">$lang</option>\n";
	     	}
	    }
	    ?>
	</select>&nbsp;<?php echo _("you can add more languages in");?> /contact-files/languages
</p>
<p>
<input type="submit" name="action" id="action" value="<?php echo _("Continue");?> >>" class="button-submit" />
</p>
</form>
<?php } elseif ($step=="requirements"){?>
<h2><?php echo _('Requirements');?></h2>
<p><?php echo _('Please carefully review the requirements check list below');?>:</p>
<div class="form-tab"><?php echo _('Server software');?></div>
<div class="clear"></div>
<div class="install_info">
<a href="http://www.php.net">PHP</a> <?php echo _("version");?> 5.1 <?php echo _('or higher');?>:
<?php
$succeed=true;

//echo PHP_VERSION;
$phpversion = substr(PHP_VERSION, 0, 6);
if($phpversion >= 5.1) {
    echo '<span style="color:green;">'._('Yes').'</span>';
}else{
    $succeed=false;
	echo '<span style="color:red;">'._('No').'</span>, '._('Please upgrade PHP in order to proceed');
}


if(!function_exists('mb_detect_encoding') ) {
	echo '<p><span style="color:red;">'._('Warning: Your PHP web server is lacking support for the function: mb_detect_encoding.').' '.
    _('You can ignore this warning if you are only going to use the en_US language.').' '.
    sprintf( _('In order to use languages other than en_US, you will have to add the <a href="%s">mbstring extension</a> to PHP.'),'http://www.php.net/manual/en/mbstring.installation.php' ).'</span></p>';
    if ($this_locale != 'en_US')
      $succeed = false;
}
?>
</div>
<div class="form-tab"><?php echo _("Writable folders");?></div>
<div class="clear"></div>
<div class="install_info">
(<?php

function ok_write(){
    echo '<span style="color:green;">'._('OK - Writable').'</span>';
}
function file_not_found(){
    global $succede;
    $succeed=false;
    echo '<span style="color:red;">'._('File not found').'</span>';
}
function unwritable(){
    global $succede;
    $succeed=false;
    echo '<span style="color:red;">'._('Unwritable (check permissions)').'</span>';
}


echo _('mandatory to perform installation');?>) <br />
"/contact-files/"
			<?php if(is_writable('../../contact-files')) {
				ok_write();
			} elseif(!file_exists('../../contact-files')) {
				file_not_found(); }
			else  unwritable(); ?>
			<br />
"/contact-files/attachments/"
			<?php if(is_writable('../attachments')) {
				ok_write();
			} elseif(!file_exists('../attachments')) {
			  file_not_found(); }
			else  unwritable(); ?>
			<br />
"/contact-files/captcha/"
			<?php if(is_writable('../captcha')) {
				ok_write();
			} elseif(!file_exists('../captcha')) {
					file_not_found(); }
			else  unwritable(); ?>
			<br/>
"/contact-files/captcha/temp/"
			<?php if(is_writable('../captcha/temp')) {
			   ok_write();
			} elseif(!file_exists('../captcha/temp')) {
					file_not_found(); }
			else  unwritable(); ?>
			<br/>
"/contact-files/settings/"
			<?php if(is_writable('../settings')) {
			   ok_write();
			} elseif(!file_exists('../settings')) {
					file_not_found(); }
			else  unwritable();
		   ?>
</div>
<form method="post" action="">
<input type="hidden" name="terms" id="terms" value="1" />
<input type="hidden" name="LANGUAGE" id="LANGUAGE" value="<?php echo $LANGUAGE;?>" />
<?php if ($succeed){?>
<input type="hidden" name="step" id="step" value="path" />
<?php } else {?>
<p><a href="#"><?php echo _("Please correct the items described above then click the button below to run the requirements check again");?></a></p>
<input type="hidden" name="step" id="step" value="requirements" />
<?php }?>
<p><input type="submit" name="action" id="action" value="<?php echo _("Continue");?> >>" class="button-submit" /></p>
</form>
<?php } elseif ($step=="path"){?>
<?php
    // Try to guess installation path
    $suggest_path = substr(__FILE__,0,-18);
    $suggest_path = str_replace("\\","/",$suggest_path);

    // Try to guess installation URL
    $suggest_url = 'http://'.$_SERVER["SERVER_NAME"];
    if ($_SERVER["SERVER_PORT"] != "80") $suggest_url = $suggest_url.":".$_SERVER["SERVER_PORT"];
    if ($_SERVER["REQUEST_URI"]!="/install/"){//check if it's in a subfolder
        if(stristr($_SERVER["REQUEST_URI"], 'index.php')) $suggest_url .=substr($_SERVER["REQUEST_URI"],0,-18);//erase install
        else $suggest_url .=substr($_SERVER["REQUEST_URI"],0,-9);//erase install
    }
?>
<h2><?php echo _("Path Settings");?></h2>
<div class="form-tab"><?php echo _("Check your path settings");?></div>
<div class="clear"></div>
<form method="post" action="" onsubmit="return checkForm(this);">
<input type="hidden" name="LANGUAGE" id="LANGUAGE" value="<?php echo $LANGUAGE;?>" />
<fieldset>
<p>
    <label><?php echo _("Full URL to");?>: /contact-files</label>&nbsp;(<?php echo _("with http://");?>)
    <input  type="text" size="125" name="SITE_URL" value="<?php echo $suggest_url;?>" lang="false" onblur="validateText(this);" class="text-long" />&nbsp;<?php echo _("please check this carefully, no slash on end");?>
</p>
<p>
    <label><?php echo _("File Path to");?>: /contact-files</label>
    <input  type="text" size="125" name="SITE_PATH" value="<?php echo $suggest_path;?>" lang="false" onblur="validateText(this);" class="text-long" />&nbsp;<?php echo _("please check this carefully, no slash on end");?>
</p>
<p>
    <input type="hidden" name="terms" id="terms" value="1" />
    <input type="hidden" name="step" id="step" value="basic" />
	<input type="submit" name="action" id="action" value="<?php echo _("Continue");?> >>" class="button-submit" /></p>
</fieldset>
</form>
<?php } elseif ($step=="basic"){?>
<h2><?php echo _("Basic Configuration");?></h2>
<p><?php echo _("Basic Configuration");?>. <?php echo _("More settings available in the contact form admin");?>.</p>
<form method="post" action="" onsubmit="return checkForm(this);">
<input type="hidden" name="terms" id="terms" value="1" />
<input type="hidden" name="LANGUAGE" id="LANGUAGE" value="<?php echo $LANGUAGE;?>" />
<input type="hidden" name="SITE_URL" id="SITE_URL" value="<?php echo $SITE_URL;?>" />
<input type="hidden" name="SITE_PATH" id="SITE_PATH" value="<?php echo $SITE_PATH;?>" />

<input type="hidden" name="step" id="step" value="review" />
<fieldset>
<p>
	<label><?php echo _("Site Name");?>:</label>&nbsp;<?php echo _("the name of this web site");?>
	<input  type="text" name="SITE_NAME" value="My Site Name" lang="false" onblur="validateText(this);" class="text-long" />
</p>

<p>
	<label><?php echo _("Admin Name");?>:</label>
	<input type="text" name="ADMIN_NAME"  value="Your name" lang="false" onblur="validateText(this);" class="text-long" />&nbsp;<?php echo _("for notifications, and used as recipient name in the emails sent from the forms");?>.
</p>

<p>
	<label><?php echo _("Email address");?>:</label>
	<input type="text" name="ADMIN_EMAIL"  value="your@email.com" lang="false" onblur="validateEmail(this);" class="text-long" />&nbsp;<?php echo _("for notifications, and used as recipient email in the emails sent from the forms");?>.
</p>
<p>
	<label><?php echo _("Admin Login User");?>:</label>&nbsp;<?php echo _("needed to login and configure forms");?>
	<input type="text" name="ADMIN_USR" value="admin" lang="false" onblur="validateText(this);" class="text-long" />
</p>
<p>
	<label><?php echo _("Admin Login Password");?>:</label>&nbsp;<?php echo _("write this down and remember it!");?>
	<input type="text" name="ADMIN_PWD" value="" lang="false" onblur="validateText(this);" class="text-long" />
</p>
<p>
	<label><?php echo _("Site Character Encoding");?>:</label>
	<select name="SITE_CHARSET">
    <?php
        foreach (array ('UTF-8','ISO-8859-1','ISO-8859-2') as $site_charset) {
           echo '<option value="'.$site_charset.'">'.$site_charset.'</option>';
        }
    ?>
	</select>&nbsp;<?php echo _("The character encoding of your site, (UTF-8 is recommended)");?>.
    <a href="http://www.w3.org/International/O-HTTP-charset" target="_blank"><?php echo _("Setting the HTTP charset parameter.");?></a>
</p>
<p>
	<label><?php echo _("Site Language");?>:</label>
	<select name="LANGUAGE" >
	    <?php
	    $languages = scandir('../languages');
        if(!in_array('en_US',$languages) ) {
           $languages[] = 'en_US';
           sort($languages);
        }
	    foreach ($languages as $lang) {
            if( strpos($lang,'.')==false && $lang!='.' && $lang!='..' && !preg_match("/^_/",$lang)){
               if ($LANGUAGE == $lang) {
                   echo "<option value=\"$lang\" selected=\"selected\">$lang</option>";
               } else {
                   echo "<option value=\"$lang\">$lang</option>";
               }
	     	}
	    }
	    ?>
	</select>&nbsp;<?php echo _("you can add more languages in");?> /contact-files/languages
</p>
<p>
	<label><?php echo _("Time Zone");?>:</label>
	<select id="TIMEZONE" name="TIMEZONE">
	<?php
	/*$timezone_identifiers = DateTimeZone::listIdentifiers();
	foreach( $timezone_identifiers as $value ){
		if ( preg_match( '/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific|Australia)\//', $value ) ){
	    	$ex=explode("/",$value);//obtain continent,city
	    	if ($continent!=$ex[0]){
	    		if ($continent!="") echo '</optgroup>';
	    		echo '<optgroup label="'.$ex[0].'">';
	    	}

	    	$city=$ex[1];
	    	$continent=$ex[0];
	    	echo '<option value="'.$value.'">'.$city.'</option>';
            $array[] = $value;
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
	    echo '<option value="'.$value.'">'.$value.'</option>';
	}
	?>
	</select>&nbsp;<?php echo _("used to format the time in the email footer message");?>
</p>
<p>
	<input type="submit" name="action" id="action" value="<?php echo _("Continue");?> >>" class="button-submit" />
</p>
</fieldset>
</form>
<?php } elseif ($step=="review"){?>
<h2><?php echo _("Review & Install");?></h2>
<p><?php echo _("Please review the following summary and click the button below to install");?> Fast Secure Contact Form - PHP</p>
<form id="install" action="" method="post">
<input type="hidden" name="terms" id="terms" value="1" />
<input type="hidden" name="SITE_URL" id="SITE_URL" value="<?php echo $SITE_URL;?>" />
<input type="hidden" name="SITE_PATH" id="SITE_PATH" value="<?php echo $SITE_PATH;?>" />
<input type="hidden" name="SITE_NAME" id="SITE_NAME" value="<?php echo $SITE_NAME;?>" />
<input type="hidden" name="ADMIN_NAME" id="ADMIN_NAME" value="<?php echo $ADMIN_NAME;?>" />
<input type="hidden" name="ADMIN_EMAIL" id="ADMIN_EMAIL" value="<?php echo $ADMIN_EMAIL;?>" />
<input type="hidden" name="ADMIN_USR" id="ADMIN_USR" value="<?php echo $ADMIN_USR;?>" />
<input type="hidden" name="ADMIN_PWD" id="ADMIN_PWD" value="<?php echo $ADMIN_PWD;?>" />
<input type="hidden" name="SITE_CHARSET" id="SITE_CHARSET" value="<?php echo $SITE_CHARSET;?>" />
<input type="hidden" name="LANGUAGE" id="LANGUAGE" value="<?php echo $LANGUAGE;?>" />
<input type="hidden" name="TIMEZONE" id="TIMEZONE" value="<?php echo $TIMEZONE;?>" />

<input type="hidden" name="step" id="step" value="install" />
<div class="form-tab"><?php echo _("Path Settings");?></div>
<div class="clear"></div>
<fieldset>
<p>
    <label><?php echo _("Full URL to");?>: /contact-files</label>&nbsp;(<?php echo _("with http://");?>)&nbsp;<?php echo _("please check this carefully, no slash on end");?>
    <input type="text" readonly="readonly" value="<?php echo $SITE_URL;?>" class="text-long" />
</p>
<p>
    <label><?php echo _("File Path to");?>: /contact-files</label>&nbsp;<?php echo _("please check this carefully, no slash on end");?>
    <input type="text" readonly="readonly" value="<?php echo $SITE_PATH;?>" class="text-long" />
</p>
</fieldset>


<div class="form-tab"><?php echo _("Basic Configuration");?></div>
<div class="clear"></div>
<fieldset>
<p>
	<label><?php echo _("Site Name");?>:</label>&nbsp;<?php echo _("the name of this web site");?>
	<input type="text" readonly="readonly" value="<?php echo $SITE_NAME;?>" class="text-long" />
</p>
<p>
	<label><?php echo _("Admin Name");?>:</label>&nbsp;<?php echo _("for notifications, and used as recipient name in the emails sent from the forms");?>.
    <input type="text" readonly="readonly" value="<?php echo $ADMIN_NAME;?>" class="text-long" />
</p>
<p>
	<label><?php echo _("Email address");?>:</label>&nbsp;<?php echo _("for notifications, and used as recipient email in the emails sent from the forms");?>.
	<input type="text" readonly="readonly" value="<?php echo $ADMIN_EMAIL;?>" class="text-long" />
</p>
<p>
	<label><?php echo _("Admin Login User");?>:</label>&nbsp;<?php echo _("needed to login and configure forms");?>
    <input type="text" readonly="readonly" value="<?php echo $ADMIN_USR;?>" class="text-long" />
</p>
<p>
	<label><?php echo _("Admin Login Password");?>:</label>&nbsp;<?php echo _("write this down and remember it!");?>
	<input type="text" readonly="readonly" value="<?php echo $ADMIN_PWD;?>" class="text-long" />
</p>
<p>
	<label><?php echo _("Site Character Encoding");?>:</label>&nbsp;<?php echo _("The character encoding of your site, (UTF-8 is recommended)");?>
	<input type="text" readonly="readonly" value="<?php echo $SITE_CHARSET;?>" class="text-long" />
</p>
<p>
	<label><?php echo _("Site language");?>:</label>&nbsp;<?php echo _("you can add more languages in");?> /contact-files/languages
	<input type="text" readonly="readonly" value="<?php echo $LANGUAGE;?>" class="text-long" />
</p>
<p>
	<label><?php echo _("Time Zone");?>:</label>&nbsp;<?php echo _("used to format the time in the email footer message");?>
	<input type="text" readonly="readonly" value="<?php echo $TIMEZONE;?>" class="text-long" />
</p>
</fieldset>
<p>
<input type="submit" name="submit" id="submit" value="<?php echo _("Install");?>" class="button-submit" />
</p>
</form>
<?php }?>
<?php }
}//end if config file exists
?>
</div>
<div class="clear"></div>
</div>
<div id="footer">
    <ul>
        <li class="credits">
        &copy; <?php echo date('Y'); ?> <a href="http://www.fastsecurecontactform.com/"><?php echo _('Fast Secure Contact Form - PHP'); ?></a></li>
        <li class="copyright"><a href="http://www.gnu.org/licenses/gpl.html" target="_blank"><?php echo _('GNU General Public License'); ?></a></li>
    </ul>
<center><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input name="cmd" value="_s-xclick" type="hidden">
<input name="hosted_button_id" value="LV2DK8MC8QV6J" type="hidden">
<input src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="Paypal Donate" border="0" type="image">
<img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" border="0" height="1" width="1">
</form></center>
</div>
</div>
</body>
</html>
<?php

// functions for protecting and validating form input vars
function ctf_output_string($string) {
    return str_replace('"', '&quot;', stripslashes($string));
} // end function ctf_output_string

function fsc_install_do($fsc_gb_settings) {

 // deal with quotes
 foreach($fsc_gb_settings as $key => $val) {
         $fsc_gb_settings[$key] = str_replace('&quot;','"',trim($val));
 }

// Set language
//$language = 'en_US';
$language = $fsc_gb_settings['language'];

// set path to
$contact_form_path = '../';

require_once $contact_form_path . 'contact-form.php';
if (class_exists("FSCForm") && !isset($fsc_form) ) {
 $fsc_form = new FSCForm();
}

if (isset($fsc_form)) {
   $fsc_form->set_language($language, '..');
   $fsc_form->install_do($fsc_gb_settings);
}

} // end function fsc_install_do

/**
 * Fix $_SERVER variables for various setups.
 *
 * @access private
 * @since 2.9.8.4
 */
function install_fix_server_vars() {
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

?>