(function( $ ) { // JQuery wrapper

	if ($.fn && $.fn.juGoogleMap)
	{
		$.fn.juGoogleMap.defaults.i18n = {
	    	overlayTitle:'Clic para activar el mapa',
			btnLocatorTitle:'Ver mi ubicación',
			btnCenterMarker:'Ver la dirección seleccionada',
			btnRouteTitle:'Obtener ruta desde tu ubiación hasta el destino',
			search:'Buscar...',
			noMarker:'Debes seleccionar una ubicación haciendo clic en el mapa'
		};
	}
	
	if ($.fn && $.fn.juImgPicker)
	{
		$.fn.juImgPicker.defaults.i18n = {
	    	tooBig:'La imagen es muy grande',
    		tooBigPixels:'La imagen tiene %w de ancho y %h de alto. Por favor reduce las dimensiones de la imagen',
			invalidType:'Por favor selecciona una imagen válida',
			upload:'Cargar',
			plainMessage:'Estás por subir <br/><strong>%s</strong>',
			noPreviewSupport:"Tu navegador no soporta vista previa de imágenes con HTML5. Por favor considera utilizar un navegador más moderno."
	    };
	}
    
	if ($.fn && $.fn.datetimepicker)
	{
	    $.fn.datetimepicker.dates['es'] = {
			days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"],
			daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
			daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa", "Do"],
			months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
			monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
			today: "Hoy",
			suffix: [],
			meridiem: []
		};
	}
    
	if ($.fn && $.fn.datepicker)
	{
	    $.fn.datepicker.dates['es'] = {
			days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"],
			daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"],
			daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa", "Do"],
			months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
			monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
			today: "Hoy"
		};
	}
	
// End JQuery wrapper
}( jQuery ));