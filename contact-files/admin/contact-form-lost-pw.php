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

// lost password page
 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $fsc_site['site_charset']; ?>" />
	<title><?php echo _('Fast Secure Contact Form - PHP'); ?> - <?php echo _('Lost Password'); ?></title>
    <meta name="robots" content="noindex" />
	<link rel="stylesheet" type="text/css" href="style.css" media="screen" />
	<script type="text/javascript" src="../common.js"></script>
    <script type="text/javascript" src="../contact-form.js"></script>
</head>
<body>
<div id="container">
    <div id="header">
        <h1><?php echo _('Fast Secure Contact Form - PHP'); ?> - <?php echo _('Lost Password'); ?></h1>
        <ul id="nav_main">
            <li><a href="index.php" title="<?php echo _('Admin'); ?>" class="current"><?php echo _('Admin')?></a></li>
        </ul>
    </div>
    <div id="main">
<div id="content">
<?php
if ( isset($_GET['action']) && $_GET['action'] == 'rp' && isset($_GET['key']) && isset($_GET['login']) ) {

    $ok = 1;
  // key
    $key = '';
    $password_error = '';
    if ( is_string( $_GET['key'] ) && preg_match('/^[a-zA-Z0-9]{20}$/',$_GET['key']))
  	$key = $_GET['key'];
	if ( empty( $key ) || !is_string( $key ) || $key != $fsc_site['pwd_reset_key'] ) {
	   $password_error = _('Invalid key');
       $ok = 0;
    }

    $login = $_GET['login'];
	if ( empty($login) || !is_string($login) || $login != $fsc_site['admin_usr'] ) {
		$password_error = _('Invalid login');
        $ok = 0;
    }
    if ($ok && isset($_POST['pass1']) && $_POST['pass2'] != '' && $_POST['pass1'] == $_POST['pass2'] ) {
      // change now
      // reset the password
      $fsc_site['pwd_reset_key'] = '';
      $fsc_site['admin_pwd'] = 'hashed_'. md5($_POST['pass1']);
      $this->set_option("fsc_site", $fsc_site);
      // set login credentials
      // reset cookie
      $admin_pwd_c = str_replace('hashed_', '',$fsc_site['admin_pwd']);
      // set cookie
      $scripturlparts = explode('/', $_SERVER['PHP_SELF']);
      $scriptfilename = $scripturlparts[count($scripturlparts)-1];
      $cookie_path = preg_replace("/$scriptfilename$/i", '', $_SERVER['PHP_SELF']);
      //setcookie("fsc_verify", md5($fsc_site['admin_usr'].'%'.$admin_pwd_c),  time() + 3600, $cookie_path);
      setcookie("fsc_verify", md5($fsc_site['admin_usr'].'%'.$admin_pwd_c), 0, $cookie_path);
      echo _('Your new password has been reset. Be sure to remember it. Go to <a href="index.php">admin page</a>.');

    } else if($ok) {
      // form to type in new password form
       echo _('This is where you reset your password.');

       if (isset($_POST['pass1']) && $_POST['pass2'] != '' && $_POST['pass1'] != $_POST['pass2']) {
         $password_error = _('Password mismatch');
       }
       if (isset($_POST['pass2']) && $_POST['pass1'] != '' && $_POST['pass1'] != $_POST['pass2']) {
         $password_error = _('Password mismatch');
       }
?>
<br />
<br />
<div class="form-tab"><?php echo _('Reset Password:');?></div>
<div class="clear"></div>
<fieldset>


<form name="resetpasswordform" id="resetpasswordform" action="<?php echo "lost-pw.php?action=rp&key=$key&login=" . rawurlencode($login); ?>" method="post">
	<p>

    <?php
        echo  _('Please enter your new password.') . '<br />'."\n";
        if ( $password_error != '' ) echo '<span style="color:red">'. $password_error .'</span><br />'."\n";
     ?>
     </p>
     <p>
	 <label for="pass"><?php echo _('New Password:') ?><br />
	 <input type="password" name="pass1" id="pass1" class="text-effect" value="" size="20" autocomplete="off"  /></label>
     </p>

     <p>
	 <label for="pass2"><?php echo _('Confirm New Password:') ?><br />
	 <input type="password" name="pass2" id="pass2" class="text-effect" value="" size="20" autocomplete="off"  /></label>
     </p>

     <p>
	<input type="submit" name="Submit" value="<?php echo $this->ctf_output_string( _('Reset Password')); ?>" tabindex="100" />
    </p>
</form>


 </fieldset>
 <?php

    } else {
      // print error and form to start over
       echo _('This is where you retrieve a lost password.');
?>
<br />
<br />
<div class="form-tab"><?php echo _('Lost Password:');?></div>
<div class="clear"></div>
<fieldset>


<form name="lostpasswordform" id="lostpasswordform" action="<?php echo 'lost-pw.php?action=lostpassword' ?>" method="post">
	<p>

    <?php
        echo  _('Please enter your username or email address. You will receive a link to create a new password via email.') . '<br />'."\n";
        if ( $password_error != '' ) echo '<span style="color:red">'._('Password recovery failed:') .' '. $password_error .'</span><br />'."\n";
     ?>
	 <label for="user_login"><?php echo _('Username or E-mail:') ?><br />
	 <input type="text" name="user_login" id="user_login" class="text-effect" value="<?php echo $this->ctf_output_string($user_login); ?>" size="50" tabindex="10" /></label>
     </p>
     <p>
	<input type="submit" name="Submit" value="<?php echo $this->ctf_output_string( _('Get New Password')); ?>" tabindex="100" />
    </p>
</form>


 </fieldset>
 <?php
    }

} else {
// show lost password form

$ok = 0;
$password_error = '';
$user_data = '';
if ( isset($_GET['action']) && $_GET['action'] == 'lostpassword' ) {
	if ( empty( $_POST['user_login'] ) ) {
	   $password_error = _('Enter a username or e-mail address.');
	}else if ( strpos($_POST['user_login'], '@') ) {
	   if (trim($_POST['user_login']) == $fsc_site['admin_email']) {
		$user_data = $fsc_site['admin_email'];
        $ok = 1;
       }
		if ( empty($user_data) )
			$password_error = _('There is no user registered with that email address.');
	} else {
		if (trim($_POST['user_login']) == $fsc_site['admin_usr']) {
		$user_data = $fsc_site['admin_usr'];
        $ok = 1;
       }
		if ( empty($user_data) )
			$password_error = _('There is no user registered with that user name.');
	}
}
if ($ok) {
  // send email now
  //echo 'ok';

  // generate token key
$token_length = 20;
$token_characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
$token = '';
$token_count = strlen($token_characters);
while ($token_length--) {
        $token .= $token_characters[mt_rand(0, $token_count-1)];
}

// save token
$fsc_site['pwd_reset_key'] = $token;
$this->set_option("fsc_site", $fsc_site);

// send email
	$message = _('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
	$message .= $fsc_site['site_url'] . "/admin/index.php\r\n\r\n";
	$message .= sprintf(_('Username: %s'), $fsc_site['admin_usr']) . "\r\n\r\n";
	$message .= _('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
	$message .= _('To reset your password, visit the following address:') . "\r\n\r\n";
	$message .= '<' . $fsc_site['site_url'] . "/admin/lost-pw.php?action=rp&key=$token&login=" . rawurlencode($fsc_site['admin_usr']) . ">\r\n";

    $sitename = $fsc_site['site_name'];

	$title = sprintf( _('[%s] Password Reset'), $sitename );

	if ( $message && !mail($fsc_site['admin_email'], $title, $message) ) {
		die( _('The e-mail could not be sent.') . "<br />\n" . _('Possible reason: your host may have disabled the mail() function...') );
    } else {
        // email sent
       echo _('A link to create a new password has been sent via email. Check your email.');
    }
}

// show login form if not logged in
if (!$ok) {
 $user_login = isset($_POST['user_login']) ? stripslashes($_POST['user_login']) : '';
 echo _('This is where you retrieve a lost password.');
?>
<br />
<br />
<div class="form-tab"><?php echo _('Lost Password:');?></div>
<div class="clear"></div>
<fieldset>


<form name="lostpasswordform" id="lostpasswordform" action="<?php echo 'lost-pw.php?action=lostpassword' ?>" method="post">
	<p>

    <?php
        echo  _('Please enter your username or email address. You will receive a link to create a new password via email.') . '<br />'."\n";
        if ( $password_error != '' ) echo '<span style="color:red">'. $password_error .'</span><br />'."\n";
     ?>
	 <label for="user_login"><?php echo _('Username or E-mail:') ?><br />
	 <input type="text" name="user_login" id="user_login" class="text-effect" value="<?php echo $this->ctf_output_string($user_login); ?>" size="50" tabindex="10" /></label>
     </p>
     <p>
	<input type="submit" name="Submit" value="<?php echo $this->ctf_output_string( _('Get New Password')); ?>" tabindex="100" />
    </p>
</form>


 </fieldset>
<?php
}

}
?>