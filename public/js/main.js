$(document).ready(function(){
	$('.dropdown-toggle').dropdown();
});

function crearevento(){
	$(document).ready(function(){
        $('.fileupload').fileupload();

        $("[data-toggle='tooltip']").tooltip();
        $("#imageUrl").juImgPicker({
            maxFileSize:'4M'
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

        $("#crearEventos").validate({
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
    $(document).on('ready', function(){
        //var addresspicker = $( "#addresspicker" ).addresspicker();
        var addresspickerMap = $("#addresspicker_map" ).addresspicker({
            regionBias: "fr",
        //updateCallback: showCallback,
          elements: {
            map:      "#map",
            lat:      "#mapLocationLat",
            lng:      "#mapLocationLon",
            street_number: '#street_number',
            route: '#route',
            locality: '#locality',
            administrative_area_level_2: '#administrative_area_level_2',
            administrative_area_level_1: '#administrative_area_level_1',
            country:  '#country',
            postal_code: '#postal_code',
            type:    '#type' 
          }
        });

        var gmarker = addresspickerMap.addresspicker( "marker");    
        gmarker.setVisible(true);
        addresspickerMap.addresspicker("updatePosition");

        $("#addresspicker_map").addresspicker(
            "option", 
            "reverseGeocode", 
            "true"
        ); 

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
  $("#usuario").validate({
    rules: {
      va_email: {
        required: true,
        email: true,
        remote: {
          type:'POST',
          dataType: 'json',
          url: '/validar-correo',
          data:{
            va_email: function(){
              return $('#va_email').val();
            }
          },
          dataFilter: function(data){
            var obj = jQuery.parseJSON(data);
            if(obj.success == true){
              return true
            }else{
              return false
            }
          }
        }
      },
      va_contrasena:{
        required : true,
        minlength: 8,
        remote: {
          type:'POST',
          dataType: 'json',
          url: '/validar-contrasena',
          data:{
            va_contrasena: function(){
              return $('#inputPassword').val();
            },
            va_email: function(){
              return $('#va_email').val();
            }
          },
          dataFilter: function(data){
            var obj = jQuery.parseJSON(data);
            if(obj.success == true){
              return true
            }else{
              return false
            }
          }
        }
      }
    },
    messages:{
      va_email:{
        required:"Por favor ingresa un Email",
        email: "Ingrese un correo valido",
        remote: "Correo incorrecto"
      },
      va_contrasena: {
        required : "Ingrese la clave",
        minlength:"Ingresa un password de 8 caracteres a mas",
        remote: "Contraseña invalida"
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
                required:"Por favor ingresar un nombre"
            },
            va_email:{
                required:"Por favor ingresa un Email",
                email: "Ingrese un correo valido"
            },
            va_contrasena: {
                required : "Ingrese la clave",
                minlength:"Ingresa un password de 8 caracteres a mas"
            },
            verificar_contrasena: {
                required : "Repita la clave",            
                minlength:"Ingresa un password de 8 caracteres a mas",
                equalTo : "Ingrese el mismo valor de Clave"
            }
         
        }
            
    });
}


