<?php
    $CONEXION = mysqli_connect($HOST, $USER, $PASSWORD, $DB);
    if (mysqli_connect_errno($CONEXION)) 
    {
        echo "No se pudo conectar a la base de datos: " . mysqli_connect_error();
    }
    
    $CONEXION->set_charset($CHARSET);      
?>
