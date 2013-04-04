function show(id){element=document.getElementById(id);if(element!=null){element.style.display='block';}}
function hide(id){element=document.getElementById(id);if(element!=null){element.style.display='none';}}
function openClose(id){element=document.getElementById(id);if(element!=null){if(element.style.display=='block'){hide(id);}
else{show(id);}}}
function toggleVisibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
}
var errorColor="#EFC2C4";var normalColor="#FFFFFF";function ValidationException(codigo,mensaje,campo){this.codigo=(codigo==undefined)?0:codigo;this.mensaje=(mensaje==undefined)?null:mensaje;this.campo=(campo==undefined)?null:campo;}
function validateElements(elementos){for(var idx=0;idx<elementos.length;idx++){var vacio=elementos[idx].getAttribute("lang");if(((vacio=="false")&&(elementos[idx].value==""))||elementos[idx].style.backgroundColor=="rgb(251, 195, 17)"){throw new ValidationException(1,"Needed: ",elementos[idx])}}}
function checkForm(form){var ok;try{validateElements(form.elements);ok=true;}catch(ex){ex.campo.style.backgroundColor=errorColor;ex.campo.focus();ok=false;}
if(ok==true)document.getElementById('submit').value="loading...";return ok;}
function validateEmail(email){if(!isEmail(email.value)){email.style.backgroundColor=errorColor;}
else{email.style.backgroundColor=normalColor;}}
function validateText(e){if(e.value.length<3){e.style.backgroundColor=errorColor;}
else{e.style.backgroundColor=normalColor;}}
function validateNumber(e){if(e.value.length<1){e.style.backgroundColor=errorColor;}
else{e.style.backgroundColor=normalColor;}}
function isNumberKey(evt){var charCode=(evt.which)?evt.which:event.keyCode;if((charCode==46||charCode==8||charCode==45||charCode==47)||(charCode>=48&&charCode<=57)){return true;}
else{return false;}}
function isAlphaKey(evt){var charCode=(evt.which)?evt.which:event.keyCode;if((charCode==231||charCode==199)||(charCode==241||charCode==209)||(charCode==8||charCode==32)||((charCode>=65&&charCode<=90)||(charCode>=97&&charCode<=122))){return true;}
else{return false;}}
function isEmail(valor){if(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(valor)){return(true)}else{return false;}}

