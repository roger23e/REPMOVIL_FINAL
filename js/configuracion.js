var LATITUD_INMUEBLE        = null;
var LONGITUD_INMUEBLE       = null;
var METODO                  = null;
var VALOR                   = null;
var LOADER                  = null;
var ERROR                   = null;
var DATA                    = null;

var URL                     = "http://apartrack.com/REPMOVIL/common/ws_movil.php?format=json";
//var URL                     = "http://localhost/repmovil5/public_html/common/ws_movil.php?format=json";
var RUTA                    = "/REPMOVIL/";
var RUTA_IMAGEN             = "http://apartrack.com/REP/modulos/inmuebles/imagenes/"; 
var RUTA_IMAGEN_APARTAMENTO = "http://apartrack.com/REP/modulos/apartamentos/imagenes/"; 


function call_Ajax_Jsonp(URL, METODO, VALOR, LOADER, ERROR, FN) 
{
    $.ajax
    ({
        type: "GET",
        url: URL,
        data:
        {
            m: METODO,
            v: VALOR
        },
        dataType: "jsonp",
        jsonp: "callback",
        jsonpCallback: "JasonpCallback",
        async: false,
        beforeSend: function()
        {
            $(LOADER).css("display", "block");
            $(LOADER).css("padding-bottom", "15px");
        },
        complete: function()
        {
            $(LOADER).css("display", "none");
        },
        success: function(data)
        {
            FN(data);
        },
        error: function () 
        {
            $(ERROR).css("display", "block");
            $(ERROR).html("<p style='padding:10px; color:red;'>No se pudo conectar, por favor intente nuevamente.</p>");
        }
    });
}