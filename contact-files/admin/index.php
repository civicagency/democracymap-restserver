<?php
/*
Fast and Secure Contact Form
Mike Challis
http://www.642weather.com/weather/scripts.php
*/

// fixes no gettext support error: Fatal error: Call to undefined function _()
if (!function_exists('_')) {
    function _($string) {
          return $string;
    }
}

require_once '../contact-form.php';
if (class_exists("FSCForm") && !isset($fsc_form) ) {
 $fsc_form = new FSCForm();
}

if (isset($fsc_form)) {
   $fsc_form->admin_do();
}

?>
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