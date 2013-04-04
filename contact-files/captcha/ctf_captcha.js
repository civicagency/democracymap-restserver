function si_contact_captcha_refresh(form_num,type,securimage_url,securimage_show_url) {
   var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
   var string_length = 16;
   var prefix = '';
   for (var i=0; i<string_length; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		prefix += chars.substring(rnum,rnum+1);
   }
  document.getElementById('si_code_ctf_' + form_num).value = prefix;

  var si_image_ctf = securimage_show_url + prefix;
  document.getElementById('si_image_ctf' + form_num).src = si_image_ctf;

  if(type == 'flash') {
    var si_flash_ctf_url = securimage_url+'/securimage_play.swf?prefix='+prefix+'&bgColor1=#8E9CB6&bgColor2=#fff&iconColor=#000&roundedCorner=5&audio='+securimage_url+'/securimage_play.php?prefix='+prefix;
    var si_flash_ctf = '<object type="application/x-shockwave-flash" data="'+si_flash_ctf_url+'" id="SecurImage_as3_'+form_num+'" width="19" height="19">';
   	    si_flash_ctf += '<param name="allowScriptAccess" value="sameDomain" />';
   	    si_flash_ctf += '<param name="allowFullScreen" value="false" />';
   	    si_flash_ctf += '<param name="movie" value="'+si_flash_ctf_url+'" />';
  	    si_flash_ctf += '<param name="quality" value="high" />';
  	    si_flash_ctf += '<param name="bgcolor" value="#ffffff" />';
  	    si_flash_ctf += '</object>';
       document.getElementById('si_flash_ctf' + form_num).innerHTML = si_flash_ctf;
       return false;
  }
  if(type == 'wav') {
   var si_aud_ctf = securimage_url+'/securimage_play.php?prefix='+prefix;
    document.getElementById('si_aud_ctf' + form_num).href = si_aud_ctf;
  }
}