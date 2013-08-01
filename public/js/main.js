$(document).ready(function(){
	$('.dropdown-toggle').dropdown();
});

function crearevento(){
	$(document).ready(function(){
        $('.fileupload').fileupload();
        $(".form_datetime2").datetimepicker({
            format: "dd MM yyyy - hh:ii",
            autoclose: true,
            todayBtn: false,
            startDate: "2013-07-25 10:00",
            minuteStep: 10,
            pickerPosition: "bottom-left",
            todayHighlight:true
        });

        $("[data-toggle='tooltip']").tooltip();
        $("#imageUrl").juImgPicker({
            maxFileSize:'4M'
        });
        
        
        $("input[type='text'],input[type='checkbox'],input[type='file'],select").bind('keypress', function(){
            var e = event;
            if ((e.keyCode || e.which || e.charCode || 0) !== 13)
                return true;
            else
            {
                var nextInput = $(":input:eq(" + ($(":input").index(this) + 1) + ")").first();
                nextInput.focus();
                event.preventDefault();
                return false;
            }
        });

        //funcionalidad de crear evento
        $("#crearGrupo").change(function(){
            if( $(".crear-evento-grupos").css("display") == 'none' ){
                if($("#crearGrupo").val()===""){
                    $(".activar-agregar").show();
                    $(".next-space").hide();
                }else{
                    $(".activar-agregar").hide();
                    $(".next-space").show();
                }
            }
            else{
                $(".activar-agregar").hide();
                $(".next-space").hide();
            }
        });

        $(".next-grupo").click(function(){
            $(".next-space").hide();
            $(".crear-evento-grupos").show();
            $("#map").juGoogleMap({
                editable:true,
                dataBound:{
                    lat:'#mapLocationLat',
                    lng:'#mapLocationLon',
                    city:'#cityId',
                    address:'#address',
                    addressRef:'#addressReference'
                },
                center:{lat:-12.047816, lng:-77.062203},
                zoom:8  
            });
        });

        $(".btn-agregar").click(function(){
            $(".elige-crea").hide();
            $(".crear-grupos").show();
        });
        $(".btn-cancelar").click(function(){
            $(".elige-crea").show();
            $(".crear-grupos").hide();
        });

        /*Validar Formularios*/

        $("#crear-group").validate({
            rules: {
                va_nombre: "required",
                ta_categoria_in_id: "required",
                va_descripcion: "required"
            },
            messages: {
                va_nombre: "Ingrese un nombre de grupo",
                ta_categoria_in_id: "Ingrese una categoria de grupo",
                va_descripcion : "Ingrese una descripción"                        
            }
        });

        $("#crearEventoG").validate({
            rules: {
                evento: "required",
                address: "required",
                addressReference: "required",
                editor1: "required"
            },
            messages: {
                evento: "Ingrese un nombre del evento",
                address: "Ingrese la dirección del evento",
                addressReference : "Ingrese dirección de referencia",
                editor1 : "Ingrese una descripción del evento"
            }
        });
    });
}
