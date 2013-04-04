<?php
/*
Captcha Test Script
Mike Challis
http://www.642weather.com/weather/scripts.php

Upload this PHP script to your web server and call it from the browser.
The script will test the captcha

*/
//error_reporting(E_ALL ^ E_NOTICE); // Report all errors except E_NOTICE warnings
error_reporting(E_ALL); // Report all errors and warnings (very strict, use for testing only)
ini_set('display_errors', 1); // turn error reporting on

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Captcha Test</title>
<script type="text/javascript" language="javascript">
<!--
function toggleVisibility(id) {
   var e = document.getElementById(id);
   if(e.style.display == 'block')
       e.style.display = 'none';
   else
       e.style.display = 'block';
}

function si_contact_captcha_refresh() {
   var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
   var string_length = 16;
   var prefix = '';
   for (var i=0; i<string_length; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		prefix += chars.substring(rnum,rnum+1);
   }
  document.getElementById('token').value = prefix;

  var si_image = '../securimage_show.php?prefix='+prefix;
  //alert(si_image);
  document.getElementById('si_image').src = si_image;

  //var si_aud = '../securimage_play.php?prefix='+prefix;
  //alert(si_aud);
  //document.getElementById('si_aud').href = si_aud;
}
//-->
</script>
<style type="text/css" media="all">
body
{
	background-color:#E6E6E6;
	font-family:"Courier New", Arial, sans-serif, monospace;
	font-size:1em;
	color:#333333;
}
.group
{
	background-color:#FFFFFF;
	border:1px #CCCCCC solid;
	margin-top:25px;
	margin-bottom:25px;
	text-align:left;
}

.errors {
         color: #ff0000;
}



.title {
       color: black;
       font-weight: bold;
       font-size: 90%;
       margin-top: 2px;
       margin-bottom: 5px;
       }

.field {
        color: black;
        font-size: 90%;
        margin-bottom: 7px;
        }

</style>
</head>

<body>

<div class="group" style="margin-left:20%; margin-right:20%; padding:20px;">
<h2>CAPTCHA Test</h2>

<?php

//phpinfo();


$error = 0;
$hide_form = 0;
$error_captcha = '';
$error_print = '';

// Test for some required things, print error message if not OK.
$requires = 'ok';
if ( !extension_loaded("gd") ) {
       echo '<p class="errors">ERROR: GD image support not detected in PHP!</p>';
       echo '<p>Contact your web host and ask them why GD image support is not enabled for PHP.</p>';
       $requires = 'fail';
}
if ( !function_exists("imagepng") ) {
       echo '<p class="errors">ERROR: imagepng function not detected in PHP!</p>';
       echo '<p>Contact your web host and ask them why the imagepng function is not enabled for PHP.</p>';
       $requires = 'fail';
}
if ( !file_exists("../securimage.php") ) {
       echo '<p class="errors">ERROR: captcha_library not found</p>';
       $requires = 'fail';
}

if ($requires == 'fail') {
  exit;
}

// process form now
if (isset($_POST['action']) && ($_POST['action'] == 'check')) {
   $code    = htmlspecialchars(strip_tags($_POST['code']));


      //captcha without sessions
      if (empty($code) || $code == '') {
         $error = 1;
         $error_captcha = 'Please complete the CAPTCHA.';
      }else if (!isset($_POST['token']) || empty($_POST['token'])) {
         $error = 1;
         $error_captcha = 'Could not find CAPTCHA token.';
      }else{
         $prefix = 'xxxxxx';
         if ( isset($_POST['token']) && is_string($_POST['token']) && preg_match('/^[a-zA-Z0-9]{15,17}$/',$_POST['token'])  ){
           $prefix = $_POST['token'];
         }
         $ctf_captcha_dir = '../temp/';
         if ( is_readable( $ctf_captcha_dir . $prefix . '.php' ) ) {
			include( $ctf_captcha_dir . $prefix . '.php' );
			if ( 0 == strcasecmp( $code, $captcha_word ) ) {
              // captcha was matched
              @unlink ($ctf_captcha_dir . $prefix . '.php');
			} else {
              $error = 1;
              $error_captcha = 'You entered in the wrong CAPTCHA phrase.<br />Please try again.';
            }
	     } else {
           $error = 1;
           $error_captcha = 'Could not read CAPTCHA token file.';
	    }
	  }
  // end captcha check

  if (!$error) {
    echo '<p style="background-color:#99CC99; padding:10px;">Test Passed. The CAPTCHA matched.<br /><br />';
  }

}

if (!$hide_form) {

 echo'

 <p>
  This test will check the function of the CAPTCHA.
  To begin the test, type the phrase in the CAPTCHA field and click "submit", then see if the test passes.
</p>

Note: If the CAPTCHA image is missing text or does not show at all.
<br />
<a href="#" style="cursor:pointer; " title="Click for Help!" onclick="toggleVisibility(\'image_tip\');">Click for Help!</a>

<div style="text-align:left; display:none" id="image_tip">
<br />
<b>If the image is missing the text:</b><br />
Go to the plugin settings and check the setting "Disable CAPTCHA transparent text".
It will not fix it on the test page, but it might fix it on the WordPress forms.
<br/>
<br/>
<b>If the image is missing completely:</b><br />
There is a problem with your PHP server that will prevent the CAPTCHA from working,
check the PHP error log for clues.
</div>

<br/>
<br/>

 <form action="captcha_test.php';

echo '" id="captcha_test" method="post">
 ';

 echo '<div class="title">
 <label for="code">Enter the phrase:</label>
 </div>  '.echo_if_error($error_captcha).'
 <div class="field">
 <input id="code" name="code" type="text" />
 </div>
 ';
    clean_temp_dir('../temp/');
    // pick new prefix token
    $prefix_length = 16;
    $prefix_characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
    $prefix = '';
    $prefix_count = strlen($prefix_characters);
    while ($prefix_length--) {
        $prefix .= $prefix_characters[mt_rand(0, $prefix_count-1)];
    }
  echo '
 <input id="token" type="hidden" name="token" value="'.$prefix.'" />
 <div style="width:430px; height:55px">
 <img id="si_image" style="padding-right:5px; border-style:none; float:left;"
 src="../securimage_show.php?prefix='.$prefix.'"
 alt="CAPTCHA Image" title="CAPTCHA Image" />';
/*
<a id="si_aud" href="../securimage_play.php?prefix='.$prefix.'" title="CAPTCHA Audio">
 <img src="../images/audio_icon.gif" alt="CAPTCHA Audio"
 style="border-style:none; vertical-align:top; border-style:none;" onclick="this.blur()" /></a>
 */
 echo '<br />
 <a href="#" title="Refresh Image" style="border-style:none"
 onclick="javascript:si_contact_captcha_refresh(); return false;">
 <img src="../images/refresh.gif" alt="Refresh Image"
 style="border-style:none; vertical-align:bottom;" onclick="this.blur()" /></a>
 </div>
 ';

 echo '<p>
  <input type="hidden" name="action" value="check" />
  <input type="submit" value="submit" />
  </p>
  </form>

';

} // end if (!$hide_form)

function echo_if_error($this_error){
  global $error;
  if ($error) {
    if (!empty($this_error)) {
         return '<span class="errors">ERROR: ' . $this_error . '</span>'."\n";
    }
  }
}

// needed for emptying temp directories for captcha session files
function clean_temp_dir($dir, $minutes = 30) {
    // deletes all files over xx minutes old in a temp directory
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

?>

<p>
<a href="index.php">Try the PHP Requirements Test</a><br />
<b><a href="captcha_test.php">Try the CAPTCHA Test again</a></b><br />
<a href="../../admin/index.php">Go back to Admin page</a>
</p>

<p>PHP Scripts and WordPress plugins by Mike Challis<br />
<a href="http://www.642weather.com/weather/scripts.php">Free PHP Scripts</a><br />
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=8086141">Donate</a>, even small amounts are appreciated<br />
Contact Mike Challis for support: <a href="http://www.fastsecurecontactform.com/support">(Mike Challis)</a>
</p>
</div>

</body>
</html>