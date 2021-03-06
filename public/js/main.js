jQuery(document).ready(function(){
	jQuery('.dropdown-toggle').dropdown();
  jQuery('#search-home').focus(function() {
    jQuery('#search-home').val('');
  });
  jQuery('#search-home').click(function() {
    jQuery('#search-home').val('');
  });
});

function crearevento(){
	$(document).ready(function(){
        $('.fileupload').fileupload();

        $("[data-toggle='tooltip']").tooltip();
        $("#imageUrl").juImgPicker({
            maxFileSize:'2M'
        });
        //funcionalidad de crear evento
        $("#crearGrupo").change(function(){
            var valueGrupo = $("#crearGrupo").val();
            if($("#crearGrupo").val()===""){
                $(".activar-agregar").show();
                $(".next-space").hide();
            }else{
                $(".next-grupo").attr("href", "/agregar-evento/"+valueGrupo);                
                $(".activar-agregar").hide();
                $(".next-space").show();
            }
        });        
        /*$("#map").juGoogleMap({
            editable:true,
            dataBound:{
                city:'#cityId',
                address:'#address',
                addressRef:'#addressReference'
            },
            center:{lat:-12.047816, lng:-77.062203},
            zoom:8
        });*/

        $("input[type='text'],input[type='checkbox'],input[type='file'],select").bind('keypress', function(){
            var e = event;
            if ((e.keyCode || e.which || e.charCode || 0) !== 13)
                return true;
            else{
                var nextInput = $(":input:eq(" + ($(":input").index(this) + 1) + ")").first();
                nextInput.focus();
                event.preventDefault();
                return false;
            }
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

        $("#crearEventos").validate({
            rules: {
                va_nombre: "required",
                va_direccion: "required",
                va_referencia: "required",
                editor1: "required",
                va_tipo: "required",
                va_fecha: "required"
            },
            messages: {
              va_nombre: "Por favor ingrese un nombre del evento",
              va_direccion: "Por favor ingrese la dirección del evento",
              va_referencia : "Por favor ingrese dirección de referencia",
              editor1 : "Por favor ingrese una descripción del evento",
              va_tipo: "Por favor seleccione una categoría",
              va_fecha: "Por favor ingrese una fecha"
            }
        });
    });
}

function cargarFecha(){
    $(document).on('ready', function(){
        $(".form_datetime2").datetimepicker({
            format: "dd MM yyyy - hh:ii",
            autoclose: true,
            todayBtn: false,
            startDate: fecha_actual,
            minuteStep: 10,
            pickerPosition: "bottom-left",
            todayHighlight:true
        });
    });
}

function cargarMapa(){
    $(document).ready(function(){
        
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
        zoom:12  });
      
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

    });
}

function actualizarDatos(){
    $(document).ready(function(){
        $("#datos").on("click",function(){
          $(".mifoto").slideUp();
          $(".cface").slideUp();              
          $(".noti").slideUp();
          $(".ocultar").hide();
          $(".misdatos").animate({
            'width': "100%",
            'height': "100%"
          });
          $(".modificardatos").slideDown();
          $(".misdatos span").slideDown();
        });

        $(".misdatos").delegate("span","click",function(){
         $(".modificardatos").hide(); 
         $(".misdatos span").slideUp(); 
          $(".ocultar").slideDown();      
         $(".misdatos").animate({
         'width': "48.717948717948715%",
         'height': "180px"
           });        
            $(".mifoto").slideDown();
             $(".cface").slideDown();              
            $(".noti").slideDown();            
           
        });

        $("#inoti").on("click",function(){
                  $(".ocultarnoti").hide();

                 $(".notificaciones").slideDown();
                 $(".noti").animate({
                 'width': "48.93617021276595%",
                 'height': "180px"
                  });
                 $(".noti span").slideDown();
        });
        $(".noti").delegate("span","click",function(){
             $(".notificaciones").hide();
                  $(".ocultarnoti").slideDown(); 
                     $(".noti span").hide(); 
                     $(".noti").animate({
                     'width': "48.93617021276595%",
                     'height': "180px"
                       }); 
            });
         $("#afoto").on("click",function(){
                  $(".ocultarfoto").hide();
                 $(".cambiofoto").slideDown();
                 $(".mifoto").animate({
                 'width': "48.93617021276595%",
                 'height': "180px"
                  });
                 $(".mifoto span").slideDown();
        });
          $(".mifoto").delegate("span","click",function(){
             $(".cambiofoto").hide();
              $(".ocultarfoto").slideDown(); 
                $(".mifoto span").hide(); 
                $(".mifoto").animate({
                'width': "48.93617021276595%",
                'height': "180px"
              }); 
            });
    });
}
function valUsuario(){
  jQuery("#usuario").validate({
    rules: {
      va_email: {
        required: true,
        email: true
      },
      va_contrasena:{
        required : true,
        minlength: 8
      }
    },
    messages:{
      va_email:{
        required:"Por favor ingresa su Email",
        email: "Por favor ingrese su correo valido"
        //,remote: "Correo incorrecto"
      },
      va_contrasena: {
        required : "Por favor ingrese su contraseña",
        minlength:"Por favor ingresa una contraseña de 8 caracteres a mas"
        //,remote: "Contraseña inválida"
      }
    }
  });
}


var valregistro = function(elemento){
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
            verificar_contrasena:{
                required : true,
                equalTo: "#va_contrasena",
                minlength:8               
            }
        },
        messages:{
            va_nombre: {
                required:"Por favor ingrese su nombre"
            },
            va_email:{
                required:"Por favor ingrese su correo electrónico",
                email: "Por favor ingrese su correo valido"
            },
            va_contrasena: {
                required : "Por favor ingrese su contraseña",
                minlength:"Por favor ingrese una contraseña de 8 caracteres a mas"
            },
            verificar_contrasena: {
                required : "Por favor confirme su contraseña",            
                minlength:"Por favor ingresa una contraseña de 8 caracteres a mas",
                equalTo : "Por favor ingrese la misma contraseña"
            }
         
        }
            
    });
}

var valCrearEditar = function(elemento){
  $(elemento).validate({
    rules: {
      va_pais: "required",
      va_ciudad: "required",
      va_nombre: "required",
      ta_categoria_in_id: "required",
      va_descripcion: "required",
      pais: "required",
      ta_ubigeo_in_id: "required"
    },
    messages: {
      va_pais: "Por favor elige su país",
      va_ciudad: "Por favor elige una ciudad",
      va_nombre: "Por favor ingrese un nombre de grupo",
      ta_categoria_in_id: "Por favor seleccione una categoría de grupo",
      va_descripcion : "Por favor ingrese una descripción",
      pais: "Por favor elige un país",
      ta_ubigeo_in_id: "Por favor elige la ciudad"
    }
  });
}

var valactualizar = function(elemento){
  $(elemento).validate({
    rules: {
      va_nombre: {
        required: true
      },          
      va_email: {
        required: true,
        email: true
      }
    },
    messages:{
      va_nombre: {
        required:"Por favor ingrese su nombre"
      },
      va_email:{
        required:"Por favor ingrese su correo electrónico",
        email: "Por favor ingrese un correo electrónico válido"
      }
    }   
  });
}


