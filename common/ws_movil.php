<?php 

    header('Content-Type: text/html; charset=utf-8');
    include './Configuracion.php';
    include './MySqlConnection.php';

    $m = filter_input(INPUT_GET,'m', FILTER_SANITIZE_STRING);

    if ($m == "LEER_INMUEBLES_ORDENADOS")
    {
        $v          = filter_input(INPUT_GET,'v', FILTER_SANITIZE_STRING);
        $valor  = explode("|", $v);
        $inmuebles  = array();
        $retorno    = array();
        $lat        = $valor[0];
        $lng        = $valor[1];
        $distance   = 16000;

        function getBoundaries($lat, $lng, $distance = 1, $earthRadius = 6371)
        {
            $return         = array();
            $rLat           = deg2rad($lat);
            $rLng           = deg2rad($lng);
            $rAngDist       = $distance/$earthRadius;

            $cardinalCoords = array
            (
                'north' => '0',
                'south' => '180',
                'east'  => '90',
                'west'  => '270'
            );

            foreach ($cardinalCoords as $name => $angle)
            {
                $rAngle         = deg2rad($angle);
                $rLatB          = asin(sin($rLat) * cos($rAngDist) + cos($rLat) * sin($rAngDist) * cos($rAngle));
                $rLonB          = $rLng + atan2(sin($rAngle) * sin($rAngDist) * cos($rLat), cos($rAngDist) - sin($rLat) * sin($rLatB));
                $return[$name]  = array
                (
                    'lat' => (float) rad2deg($rLatB), 
                    'lng' => (float) rad2deg($rLonB)
                );
            }

            return array
            (
                'min_lat'   => $return['south']['lat'],
                'max_lat'   => $return['north']['lat'],
                'min_lng'   => $return['west']['lng'],
                'max_lng'   => $return['east']['lng']
            );
        }

        $box    = getBoundaries($lat, $lng, $distance);
        $SQL    =
            'SELECT
                COD_INMUEBLE,
                NOMBRE, 
                DESCRIPCION,
                LATITUD, 
                LONGITUD, 
                (
                    6371 * ACOS
                    ( 
                        SIN(RADIANS(LATITUD)) 
                        * SIN(RADIANS(' . $lat . ')) 
                        + COS(RADIANS(LONGITUD - ' . $lng . ')) 
                        * COS(RADIANS(LATITUD)) 
                        * COS(RADIANS(' . $lat . '))
                    )
                ) AS DISTANCIA
            FROM 
                tbl_inmuebles
            WHERE 
                (LATITUD BETWEEN ' . $box['min_lat']. ' AND ' . $box['max_lat'] . ')
            AND (LONGITUD BETWEEN ' . $box['min_lng']. ' AND ' . $box['max_lng']. ')                                   
            ORDER BY 
                DISTANCIA ASC';

        $stmt   = mysqli_query($CONEXION, $SQL);

        while($row = mysqli_fetch_array($stmt)) 
        { 
            $inmuebles[] = array
            (
                'COD_INMUEBLE'  => $row['COD_INMUEBLE'],
                'NOMBRE'        => $row['NOMBRE'],
                'DESCRIPCION'   => $row['DESCRIPCION'], 
                'LATITUD'       => $row['LATITUD'], 
                'LONGITUD'      => $row['LONGITUD'], 
                'DISTANCIA'     => $row['DISTANCIA']
            );
        }

        $retorno["INMUEBLES"] = $inmuebles;

        if(isset($_GET["callback"]))
        {	
                echo $_GET["callback"]."(" . json_encode($retorno) . ");";	
        }
        else
        {
            echo  json_encode($retorno);
        } 

    }
    
        if ($m == "LEER_INMUEBLES_SIN_ORDENAR")
    {
        //$v          = filter_input(INPUT_GET,'v', FILTER_SANITIZE_STRING);
        $inmuebles  = array();
        $retorno    = array();
        $lat        = 10.470220;
        $lng        = -66.588290;
        $distance   = 16000;

        function getBoundaries($lat, $lng, $distance = 1, $earthRadius = 6371)
        {
            $return         = array();
            $rLat           = deg2rad($lat);
            $rLng           = deg2rad($lng);
            $rAngDist       = $distance/$earthRadius;

            $cardinalCoords = array
            (
                'north' => '0',
                'south' => '180',
                'east'  => '90',
                'west'  => '270'
            );

            foreach ($cardinalCoords as $name => $angle)
            {
                $rAngle         = deg2rad($angle);
                $rLatB          = asin(sin($rLat) * cos($rAngDist) + cos($rLat) * sin($rAngDist) * cos($rAngle));
                $rLonB          = $rLng + atan2(sin($rAngle) * sin($rAngDist) * cos($rLat), cos($rAngDist) - sin($rLat) * sin($rLatB));
                $return[$name]  = array
                (
                    'lat' => (float) rad2deg($rLatB), 
                    'lng' => (float) rad2deg($rLonB)
                );
            }

            return array
            (
                'min_lat'   => $return['south']['lat'],
                'max_lat'   => $return['north']['lat'],
                'min_lng'   => $return['west']['lng'],
                'max_lng'   => $return['east']['lng']
            );
        }

        $box    = getBoundaries($lat, $lng, $distance);
        $SQL    =
            'SELECT
                COD_INMUEBLE,
                NOMBRE, 
                DESCRIPCION,
                LATITUD, 
                LONGITUD
            FROM 
                tbl_inmuebles;';

        $stmt   = mysqli_query($CONEXION, $SQL);

        while($row = mysqli_fetch_array($stmt)) 
        { 
            $inmuebles[] = array
            (
                'COD_INMUEBLE'  => $row['COD_INMUEBLE'],
                'NOMBRE'        => $row['NOMBRE'],
                'DESCRIPCION'   => $row['DESCRIPCION'], 
                'LATITUD'       => $row['LATITUD'], 
                'LONGITUD'      => $row['LONGITUD']
            );
        }

        $retorno["INMUEBLES"] = $inmuebles;

        if(isset($_GET["callback"]))
        {	
                echo $_GET["callback"]."(" . json_encode($retorno) . ");";	
        }
        else
        {
            echo  json_encode($retorno);
        } 

    }
    
    if ($m == "LEER_INFORMACION_INMUEBLE")
    {
        $v                              = filter_input(INPUT_GET,'v', FILTER_SANITIZE_STRING);
        $sql                            = null;
        $registros                      = null;
        $fila                           = null;
        $numero_de_filas                = null;
        
        $retorno                        = array();
        $resultado                      = array();
        
        $informacion_inmueble           = array();
        $imagenes_inmueble              = array();
        $todas_caracteristicas          = array();
        $caracteristicas_inmueble       = array();
        $todos_alrededores              = array();
        $alrededores_inmueble           = array();

        $sql = "
            SELECT 
                COD_INMUEBLE, 
                NOMBRE, 
                DESCRIPCION, 
				PROPIETARIO,
				CONSTRUCTOR,
				CORREDOR,
                LATITUD, 
                LONGITUD, 
				ALTURA,
				PISOS,
				NUM_UNIDADES,
				FECHA_CONSTRUCCION,
				NUM_ELEVADORES,
                ESTATUS 
            FROM 
                tbl_inmuebles
            WHERE 
                COD_INMUEBLE = '".$v."';";

        $result = mysqli_query($CONEXION, $sql);
        
        while($row = mysqli_fetch_array($result)) 
        { 
            $informacion_inmueble[] = array
            (
                'COD_INMUEBLE'  		=> $row['COD_INMUEBLE'], 
                'NOMBRE'        		=> $row['NOMBRE'], 
                'DESCRIPCION'   		=> $row['DESCRIPCION'], 
				'PROPIETARIO'   		=> $row['PROPIETARIO'],
				'CONSTRUCTOR'   		=> $row['CONSTRUCTOR'],
				'CORREDOR'   			=> $row['CORREDOR'],
                'LATITUD'       		=> $row['LATITUD'],
                'LONGITUD'      		=> $row['LONGITUD'],
				'ALTURA'   				=> $row['ALTURA'],
				'PISOS'   				=> $row['PISOS'],
				'NUM_UNIDADES'  		=> $row['NUM_UNIDADES'],
				'FECHA_CONSTRUCCION'	=> $row['FECHA_CONSTRUCCION'],
				'NUM_ELEVADORES'   		=> $row['NUM_ELEVADORES'],
                'ESTATUS'       		=> $row['ESTATUS']
            );
        }
        
        $sql = "
            SELECT
                COD_IMAGEN_INMUEBLE,
                COD_INMUEBLE,
                NOMBRE, 
                RUTA, 
                EXTENSION, 
                N0MBRE_COMPLETO, 
                ESTATUS 
            FROM 
                tbl_imagenes_inmuebles
            WHERE 
                COD_INMUEBLE = '".$v."' AND TIPO = 'GALERIA';";

        $result = mysqli_query($CONEXION, $sql);
        
        while($row = mysqli_fetch_array($result)) 
        { 
            $imagenes_inmueble[] = array
            (
                'COD_IMAGEN_INMUEBLE'   => $row['COD_IMAGEN_INMUEBLE'],
                'COD_INMUEBLE'  => $row['COD_INMUEBLE'], 
                'NOMBRE'                => $row['NOMBRE'], 
                'RUTA'                  => $row['RUTA'], 
                'EXTENSION'             => $row['EXTENSION'],
                'N0MBRE_COMPLETO'       => $row['N0MBRE_COMPLETO'],
                'ESTATUS'               => $row['ESTATUS']
            );
        }

        $sql = "
            SELECT
                COD_CARACTERISTICA_INMUEBLE,
                COD_TIPO_INMUEBLE, 
                CARACTERISTICA, 
                ESTATUS
            FROM 
                cat_caracteristicas_inmueble
            WHERE
                ESTATUS = 1;";
        
        $result = mysqli_query($CONEXION, $sql);
        
        while($row = mysqli_fetch_array($result)) 
        { 
            $todas_caracteristicas[] = array
            (
                'COD_CARACTERISTICA_INMUEBLE'   => $row['COD_CARACTERISTICA_INMUEBLE'], 
                'COD_TIPO_INMUEBLE'             => $row['COD_TIPO_INMUEBLE'], 
                'CARACTERISTICA'                => $row['CARACTERISTICA'], 
                'ESTATUS'                       => $row['ESTATUS']
            );
        }
        
        $sql = "
            SELECT
                COD_CARACTERISTICA_INMUEBLE,
                COD_TIPO_INMUEBLE, 
                CARACTERISTICA, 
                ESTATUS
            FROM 
                cat_caracteristicas_inmueble
             WHERE 
                COD_CARACTERISTICA_INMUEBLE in 
                (
                    SELECT 
                        COD_CATALOGO 
                    FROM 
                        tbl_caracteristicas_inmuebles 
                    WHERE 
                        COD_INMUEBLE = '".$v."'
                );";
        
        $result = mysqli_query($CONEXION, $sql);
        
        while($row = mysqli_fetch_array($result)) 
        { 
            $caracteristicas_inmueble[] = array
            (
                'COD_CARACTERISTICA_INMUEBLE'   => $row['COD_CARACTERISTICA_INMUEBLE'], 
                'COD_TIPO_INMUEBLE'             => $row['COD_TIPO_INMUEBLE'], 
                'CARACTERISTICA'                => $row['CARACTERISTICA'], 
                'ESTATUS'                       => $row['ESTATUS']
            );
        }
        
        $sql = "
            SELECT
                COD_ALREDEDOR,
                NOMBRE,
                ESTATUS
            FROM 
                cat_alrededores_inmueble
            WHERE
                ESTATUS = 1;";
        
        $result = mysqli_query($CONEXION, $sql);
        
        while($row = mysqli_fetch_array($result)) 
        { 
            $todos_alrededores[] = array
            (
                'COD_ALREDEDOR' => $row['COD_ALREDEDOR'], 
                'NOMBRE'        => $row['NOMBRE'], 
                'ESTATUS'       => $row['ESTATUS']
            );
        }
        
        $sql = "
            SELECT
                COD_ALREDEDOR,
                NOMBRE,
                ESTATUS
            FROM 
                cat_alrededores_inmueble
             WHERE 
                COD_ALREDEDOR IN
                (
                    SELECT 
                        COD_CATALOGO 
                    FROM 
                        tbl_alrededores_inmueble 
                    WHERE 
                        COD_INMUEBLE = '".$v."'
                );";
        
        $result = mysqli_query($CONEXION, $sql);
        
        while($row = mysqli_fetch_array($result)) 
        { 
            $alrededores_inmueble[] = array
            (
                'COD_ALREDEDOR' => $row['COD_ALREDEDOR'], 
                'NOMBRE'        => $row['NOMBRE'], 
                'ESTATUS'       => $row['ESTATUS']
            );
        }

        $retorno["INFORMACION_INMUEBLE"]        = $informacion_inmueble;
        $retorno["IMAGENES_INMUEBLE"]           = $imagenes_inmueble;
        $retorno["CARACTERISTICAS"]             = $todas_caracteristicas;
        $retorno["CARACTERISTICAS_INMUEBLE"]    = $caracteristicas_inmueble;
        $retorno["ALREDEDORES"]                 = $todos_alrededores;
        $retorno["ALREDEDORES_INMUEBLE"]        = $alrededores_inmueble;

        if(isset($_GET["callback"]))
        {	
                echo $_GET["callback"]."(" . json_encode($retorno) . ");";	
        }
        else
        {
            echo  json_encode($retorno);
        } 
    }
    
    if ($m == "LEER_APARTAMENTOS_INMUEBLE")
    {
        $v                              = filter_input(INPUT_GET,'v', FILTER_SANITIZE_STRING);
        $sql                            = null;
        $registros                      = null;
        $fila                           = null;
        $numero_de_filas                = null;
        
        $retorno                        = array();
        $resultado                      = array();
        
        $apartamentos                   = array();
        
        

        $sql = "
            SELECT 
                a.COD_INMUEBLE,
                a.NOMBRE,
                a.DESCRIPCION,
                a.LATITUD,
                a.LONGITUD,
                b.COD_APARTAMENTO,
                b.DESCRIPCION,
                b.PRECIO,
                b.ESTATUS
            FROM
                tbl_inmuebles a,
                tbl_apartamentos b
            WHERE
                a.COD_INMUEBLE 	= b.COD_INMUEBLE
            AND b.ESTATUS       = 1
            AND a.COD_INMUEBLE	= '".$v."'
            ORDER BY
                (b.COD_APARTAMENTO);";

        $result = mysqli_query($CONEXION, $sql);
        
        while($row = mysqli_fetch_array($result)) 
        { 
            $apartamentos[] = array
            (
                'COD_INMUEBLE'      => $row['COD_INMUEBLE'], 
                'NOMBRE'            => $row['NOMBRE'], 
                'DESCRIPCION'       => $row['DESCRIPCION'], 
                'LATITUD'           => $row['LATITUD'],
                'LONGITUD'          => $row['LONGITUD'],
                'COD_APARTAMENTO'   => $row['COD_APARTAMENTO'],
                'DESCRIPCION'       => $row['DESCRIPCION'],
                'PRECIO'            => $row['PRECIO'],
                'ESTATUS'           => $row['ESTATUS']
            );
        }
        

        $retorno["APARTAMENTOS"]    = $apartamentos;


        if(isset($_GET["callback"]))
        {	
                echo $_GET["callback"]."(" . json_encode($retorno) . ");";	
        }
        else
        {
            echo  json_encode($retorno);
        } 
    }

