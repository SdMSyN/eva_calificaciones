<?php

    include('conexion.php');
    $con->autocommit(FALSE);

    $filaActual = 0;
    $archivo = fopen( "csv/3FV.csv", "r" );
    $filaInicio = 2;
    $columnaInicioCalif = 6;
    $columnaFinCalif = $columnaInicioCalif + 11;
    $columnaCurp = 2; // C
    $columnaNombre = 3; // D
    $idsMaterias = [ 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 23 ];
    $cad = "";
    $banSql = true;
    $idGrado = 3;
    $idGrupo = 6;
    $idTurno = 2;
    $idCiclo = 1;
    $sqlCalifAlum = "";
    $sqlRlAlumMat = "";
    //Lo recorremos
    while( ( $datos = fgetcsv( $archivo, "," ) ) == true ) {
        if( $filaActual < $filaInicio ){ 
            $filaActual++;
            continue;
        }
        $num = count ( $datos );
        // $cad .= $filaActual . ": " . utf8_encode ( $datos[ $columnaNombre ] ) . " - " . utf8_encode ($datos[ $columnaCurp ] ) . " - " ;
        $nameTmp = utf8_encode ( $datos[ $columnaNombre ] );
        $curpTmp = utf8_encode ( $datos[ $columnaCurp ] );
        $sqlCalifAlum = "INSERT INTO califAlumnos ( id_baseCtGrados, id_baseCtGrupos, id_baseCtTurnos, nombre, curp  ) VALUES ";
        $sqlCalifAlum .= " ( $idGrado, $idGrupo, $idTurno, '$nameTmp', '$curpTmp' ) ";
        echo $sqlCalifAlum;
        if( $con->query( $sqlCalifAlum ) === TRUE ){
            $idCalifAlum = $con->insert_id;
            $posIdsMat = 0;
            $sqlRlAlumMat = "INSERT INTO califRlAlumnos_Materias ( id_califAlumnos, id_baseCtMaterias, id_baseCtCiclos, calificacion ) VALUES";
            //Recorremos las columnas de esa linea
            for ( $columna = $columnaInicioCalif; $columna < $columnaFinCalif; $columna++ ) {
                $califTmp = utf8_encode( $datos[$columna] );
                $sqlRlAlumMat .= "   ( $idCalifAlum, $idsMaterias[$posIdsMat], $idCiclo, '$califTmp' ),";
                $posIdsMat++;
            }
            $sqlRlAlumMat = substr( $sqlRlAlumMat, 0, -1 );
            echo $sqlRlAlumMat."<br><br>";
            if( $con->query( $sqlRlAlumMat ) === TRUE ){
                // $cad .= $sqlRlAlumMat . "<br>";
                // $cad .= "<br>";
                $filaActual++;
            }else{
                $banSql = false;
                $cad .= "ERROR: al insertar calificación del alumno: " . $nameTmp . " en la materia: " . $posIdsMat . "<br>".$con->error;
                break;
            }
        } else{
            $banSql = false;
            $cad .= "ERROR: al insertar alumno en la línea: " . $filaActual . "---" . $con->error;
            break;
        }
    }

    if( $banSql ){ 
        $con->commit();
        echo "EXITO";
    } else
        $con->rollback();
    echo $cad ;
    //Cerramos el archivo
    fclose($archivo);
?>