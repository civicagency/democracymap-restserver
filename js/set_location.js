	$(document).ready(function(){



		if (navigator.geolocation) 
		{
			navigator.geolocation.getCurrentPosition( 

				function (position) {  

				// Did we get the position correctly?
			 	// alert (position.coords.latitude);

				// To see everything available in the position.coords array:
				// for (key in position.coords) {alert(key)}

				$('#addressid').attr("value", position.coords.latitude + ',' + position.coords.longitude);
				$("form:first").submit();

				}, 
				// next function is the error callback
				function (error)
				{
					switch(error.code) 
					{
						case error.TIMEOUT:
							alert ('Timeout');
							break;
						case error.POSITION_UNAVAILABLE:
							alert ('Position unavailable');
							break;
						case error.PERMISSION_DENIED:
							alert ('Permission denied');
							break;
						case error.UNKNOWN_ERROR:
							alert ('Unknown error');
							break;
					}
				}
				);
			}

 });