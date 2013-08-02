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
            if($("#crearGrupo").val()===""){
                    $(".activar-agregar").show();
                    $(".next-space").hide();
                }else{
                    $(".activar-agregar").hide();
                    $(".next-space").show();
                }
        });

//        $(".next-grupo").click(function(){
//            $(".next-space").hide();
//            $(".crear-evento-grupos").show();
//            $("#map").juGoogleMap({
//                editable:true,
//                dataBound:{
//                    lat:'#mapLocationLat',
//                    lng:'#mapLocationLon',
//                    city:'#cityId',
//                    address:'#address',
//                    addressRef:'#addressReference'
//                },
//                center:{lat:-12.047816, lng:-77.062203},
//                zoom:8  
//            });
//        });

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
                va_nombre: "required",
                va_direccion: "required",
                va_referencia: "required",
                editor1: "required"
            },
            messages: {
                va_nombre: "Ingrese un nombre del evento",
                va_direccion: "Ingrese la dirección del evento",
                va_referencia : "Ingrese dirección de referencia",
                editor1 : "Ingrese una descripción del evento"
            }
        });
    });
}

var valregistro=function(elemento){
    $(elemento).validate({
        rules: {
            va_nombre: {
                required: true
            },          
            va_email: {
                required: true,
                email: true
            },
            va_contrasena:{
                required : true,
                minlength:8             
            },
            va_contrasena2:{
                required : true,
                equalTo: "#va_contrasena",
                minlength:8               
            }
        },
        messages:{
            va_nombre: {
                required:"Por favor ingresar un nombre"
            },
            va_email:{
                required:"Por favor ingresa un Email"
            },
            va_contrasena: {
                required : "Ingrese la clave",
                minlength:"Ingresa un password de 8 caracteres a mas"
            },
            va_contrasena2: {
                required : "Repita la clave",            
                minlength:"Ingresa un password de 8 caracteres a mas",
                equalTo : "Ingrese el mismo valor de Clave"
            }
         
        }
            
    });
}
