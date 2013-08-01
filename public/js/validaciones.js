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

var vallogin=function(elemento){
$(elemento).validate({
        rules: {
                
          va_email: {
            required: true,
            email: true
          },
          va_contrasena:{
                required : true,
                minlength:8             
            }       
        },
        messages:{
            
            va_email:{
                required:"Por favor ingresa un Email"
            },
            va_contrasena: {
                required : "Ingrese un password",
                minlength:"Ingresa un password de 8 caracteres a mas"
            }         
         
        }
            
      });

}

if( $("#registro").length){
    valregistro('#registro');     
};
if( $("#login").length){
    vallogin('#login');     
};
if( $("#actualizar").length){
    valregistro('#actualizar');     
};

if( $("#cambio-pass").length){
    valregistro('#cambio-pass');     
};
if( $("#recuperacion").length){
    valregistro('#recuperacion');     
};

