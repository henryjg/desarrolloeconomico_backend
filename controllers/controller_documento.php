<?php
include_once './utils/response.php';
include_once './config/database.php';

class DocumentoController {
    private $database;

    public function __construct() {
        $this->database = new Database();
    }

    // ----------------------------------------
    // ADD DOCUMENTO EXTERNO 

    public function registrarDocumento_Externo(
        $procedencia, 
        $buzonorigen_id, 
        $buzondestino_id,
        $prioridad, 
        $cabecera,
        $asunto, 
        $folios, 
        $administrado_id, 
        $tipodocumento_id, 
        $descripcion, 
        $estado, 
        $referencias_id, 
        $otrasreferencias, 
        $estupa, 
        $fechavencimiento,
        $proyectar,
        $usuarionombre,
        $tramitetupa_id,
        $usuario_id,
        $usuario_nombre,
        $pdf_principal,
        $pdf_anexo1,
        $pdf_anexo2,

    ) {
      
        $numdoc = $this->genera_numero_documento($procedencia);
        $numdoc_tipodoc_oficina = $this->genera_numeracion_tipodoc_oficina($procedencia, $tipodocumento_id, $buzonorigen_id);
        $pdf_principal_html ="";

        $codigoseguridad = $this->generarTextoAleatorio();

        $query = "SELECT documento_insertar(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            
            $result = $this->database->insertar($query, [
                $numdoc,
                $numdoc_tipodoc_oficina,
                $procedencia,
                $prioridad,
                $buzonorigen_id,
                $cabecera,
                $asunto,
                $folios,
                $administrado_id,
                $tipodocumento_id,
                $descripcion,
                $estado,
                $referencias_id,
                $otrasreferencias,
                $estupa,
                $pdf_principal,
                $pdf_principal_html,
                $pdf_anexo1,
                $pdf_anexo2,
                $proyectar,
                $usuario_nombre,
                $codigoseguridad,
                $fechavencimiento,
                $tramitetupa_id
            ]);


            if ($result > 0) {
                $this->insert_primerpase_externo($buzonorigen_id,$buzondestino_id,$usuario_id,$usuario_nombre,$result,"Original","Pase",$result);
                $this->get_lista_documentos_Externos($result);
                // response::success($result, 'Nuevo registro de licencia insertado correctamente');
            } elseif ($result == -1) {
                Response::error('Error: El código ya existe.');
            } elseif ($result == -2) {
                Response::error('Error: Campos requeridos no proporcionados (violación de NOT NULL).');
            } else {
                Response::error('Error desconocido al insertar el documento'.$result);
            }

        } catch (PDOException $e) {
            Response::error('Error de base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            Response::error('Error inesperado: ' . $e->getMessage());
        }
    }




    // Insertar documento con restricciones
    public function registrarDocumento_Interno(
        $procedencia, 
        $pase_buzonorigen_id, 
        $prioridad,
        $cabecera, 
        $asunto, 
        $folios, 
        $administrado_id, 
        $tipodocumento_id, 
        $descripcion, 
        $estado, 
        $referencias_id, 
        $otrasreferencias, 
        $estupa, 
        $fechavencimiento,
        $proyectar,
        $tramitetupa_id,
        $usuario_id,
        $usuario_nombre,
        $pdf_principal,
        $pdf_principal_html,
        $pdf_anexo1,
        $pdf_anexo2,
        $arrayOriginal,
        $arrayCopia,
        $referenciasArray,
        $otrasreferenciasArray,
        $idpase,
        $pase_proveido = ""
    ) {
        
    $numdoc = $this->genera_numero_documento($procedencia);
    if($procedencia=="Interno"){
        $numdoc_tipodoc_oficina = $this->genera_numeracion_tipodoc_oficina($procedencia, $tipodocumento_id, $pase_buzonorigen_id);
    }else{
        $numdoc_tipodoc_oficina = "";
    }
    $codigoseguridad = $this->generarTextoAleatorio();
    
    $query = "SELECT documento_insertar(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    try {
        $result = $this->database->insertar($query, [
            $numdoc,
            $numdoc_tipodoc_oficina,
            $procedencia,
            $prioridad,
            $pase_buzonorigen_id,
            $cabecera,
            $asunto,
            $folios,
            $administrado_id,
            $tipodocumento_id,
            $descripcion,
            $estado,
            $referencias_id,
            $otrasreferencias,
            $estupa,                
            $pdf_principal,
            $pdf_principal_html,
            $pdf_anexo1,
            $pdf_anexo2,
            $proyectar,
            $usuario_nombre,
            $codigoseguridad,
            $fechavencimiento,
            $tramitetupa_id
        ]);

        if ($result > 1) {
            $id_nuevodocumento = $result;
            
            // IMPORTANTE: Verificar primero si el nuevo documento será su propio primogenio
            // Esto corrige el error de foreign key constraint
            $id_primogenio = $id_nuevodocumento; // Por defecto, el nuevo documento es su propio primogenio
            
            // Verificar si hay referencias y existen en la base de datos
            $referenciaValida = false;
            if(!empty($referenciasArray) && count($referenciasArray) > 0) {
                // Verificar si la referencia existe antes de asignarla como primogenio
                $docExists = $this->verificarDocumentoExiste($referenciasArray[0]);
                if($docExists) {
                    $id_primogenio = $referenciasArray[0];
                    $referenciaValida = true;
                }
            }
            
            // PASES ORIGINAL
            foreach ($arrayOriginal as $item) {
                $doc_buzondestino_id = $item['id'] ?? null;
                $this->insert_primerpase($pase_buzonorigen_id, $doc_buzondestino_id, $usuario_id, $usuario_nombre, $id_nuevodocumento, "Original", "Pase", $pase_proveido, $id_primogenio,$idpase);
            }

            // PASES COPIA
            foreach ($arrayCopia as $itemcopia) {
                $doc_buzondestino_id = $itemcopia['id'] ?? null;
                $this->insert_primerpase($pase_buzonorigen_id, $doc_buzondestino_id, $usuario_id, $usuario_nombre, $id_nuevodocumento, "Copia", "Pase", $pase_proveido, $id_primogenio,$idpase);
            }

            // REFERENCIAS - Solo agregar si hay referencias válidas
            if($referenciaValida) {
                foreach ($referenciasArray as $itemref) {
                    if($this->verificarDocumentoExiste($itemref)) {
                        $this->insert_documento_referencia($result, $itemref, "Relacion");
                    }
                }
            }

            // Actualizar ----------------------------------------------------------------------
            if($idpase != '') {
                $query = "UPDATE siga_documento_pase SET 
                            pase_estadopase = 'Tramitado',
                            pase_tipoaccion = 'Atendido con documento'
                        WHERE pase_id = ?";
                try {
                    $resultado_consulta = $this->database->ejecutarActualizacion($query, [
                        $idpase
                    ]);
                    if ($resultado_consulta) {
                        // response::success($resultado_consulta, 'Pase Actualizado correctamente');
                    } else {
                        // response::error('Error al actualizar el pase '.$idpase);
                    }
                } catch (PDOException $e) {
                    // response::error('Error al actualizar el pase: ' . $e->getMessage());
                }
            }
            //  ----------------------------------------------------------------------------------
            //  ----------------------------------------------------------------------------------

            $this->get_lista_documento_Interno($result);                
            // response::success($result, 'Nuevo registro de licencia insertado correctamente');
        } elseif ($result == -1) {
            Response::error('Error: El código ya existe.');
        } elseif ($result == -2) {
            Response::error('Error: Campos requeridos no proporcionados (violación de NOT NULL).');
        } else {
            Response::error('Error desconocido al insertar el documento'.$result);
        }

    } catch (PDOException $e) {
        Response::error('Error de base de datos: ' . $e->getMessage());
    } catch (Exception $e) {
        Response::error('Error inesperado: ' . $e->getMessage());
    }
}

// Función auxiliar para verificar si un documento existe
private function verificarDocumentoExiste($doc_id) {
    $query = "SELECT COUNT(*) as existe FROM siga_documento WHERE doc_iddoc = ?";
    try {
        $result = $this->database->ejecutarConsulta($query, [$doc_id]);
        if($result && isset($result[0]['existe'])) {
            return (int)$result[0]['existe'] > 0;
        }
        return false;
    } catch (PDOException $e) {
        // Si hay error, asumimos que no existe para evitar foreign key violations
        return false;
    }
}

    public function Actualizar_Documento_proveido(
        $iddoc,
        $buzonorigen_id,
        $usuario_id,
        $usuario_nombre,
        $ComentarioProveido,
        $arrayoriginal
    ) {

        //VERIFICAMOS QUE NO HAYAN RECIBIDOS  ----------------------------------------
        // ---------------------------------------------------------------------------

        $query = "SELECT COUNT(pase_id) AS recibidos FROM siga_documento_pase 
        WHERE  pase_estadopase='Recibido' AND pase_documento_id = ?";  
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $iddoc
            ]);
            if ($result) {
                $nro_recibidos = $result[0]['recibidos'];
                if($nro_recibidos>0){
                    response::error('No se puede actualizar por que ya fue recepcionado');
                    // return;
                }
            }else{
                echo "oa";
            }
        } catch (PDOException $e) {
             response::error('Error al insertar el pase: ' . $e->getMessage());
        }
        
        

        //OBTENER DATOS DE PASE Y PRIMOGENIO  ----------------------------------------
        // ---------------------------------------------------------------------------
        // $primogenioid ="";
        // $query_primogenio = "SELECT pase_documento_primogenio_id as primogenioid FROM siga_documento_pase 
        // WHERE pase_documento_id = ?";
        // try {
        //     $result_primogenio = $this->database->ejecutarConsulta($query_primogenio, [
        //         $iddoc
        //     ]);
        //     if ($result_primogenio) {
        //         $primogenioid = $result_primogenio[0]['primogenioid'];
        //         // echo "primogenio".$primogenioid;
        //     } else {
        //         // return false;
        //         // echo "primogenio";
        //     }
        // } catch (PDOException $e) {
        //     response::error('Error al insertar el pase: ' . $e->getMessage());
        // }
        // echo "primogenio";

           
        //ELIMINAMOS PASES Y VOLVEMOS A CREAR  ---------------------------------------
        // ---------------------------------------------------------------------------

        $query_delete = "DELETE FROM siga_documento_pase WHERE pase_documento_id = ?";      
        $respuesta_delete = false; // Inicializamos como falso
        try {
            $result_delete = $this->database->ejecutarConsulta($query_delete, [
            $iddoc
            ]);
            // La ejecución correcta de la consulta sin errores significa éxito
            // Incluso si no hay filas para eliminar, la operación es exitosa
            $respuesta_delete = true;
        } catch (PDOException $e) {
            $respuesta_delete = false;
            response::error('Error al eliminar el pase: ' . $e->getMessage());
        }

        // CREAMOS NUEVAMENTE LAS RELACIONES ELIMINADAS ------------------------------
        // ---------------------------------------------------------------------------
        if($respuesta_delete){
            //PASES ORIGINAL
            foreach ($arrayoriginal as $item) {
                $doc_buzondestino_id = $item['id'] ?? null;
                $respuesta = $this->insert_primerpase($buzonorigen_id,$doc_buzondestino_id,$usuario_id,$usuario_nombre,$iddoc,"Original","Pase",$ComentarioProveido, $iddoc,null);
            }
            if($respuesta){
                response::success("Completado", 'Registro Actualizado');
            }
        }else{
            response::error("Error sas");
        }
        
    }

    // Insertar documento con restricciones
    public function Actualizar_Documento_Interno(
        $iddoc,
        $asunto,
        $folios,
        $pdf_principal,
        $pdf_principal_html,
        $pdf_principal_estadofirma,
        $pdf_anexo1,
        $pdf_anexo1_estadofirma,
        $pdf_anexo2,
        $pdf_anexo2_estadofirma,
        $buzonorigen_id,
        $usuario_id,
        $usuario_nombre,
        $arrayOriginal,
        $arrayCopia,
        $referenciasArray,
        $otrasreferenciasArray,
        $pase_proveido
    ) {

        //VERIFICAMOS QUE NO HAYAN RECIBIDOS  ----------------------------------------
        // ---------------------------------------------------------------------------

        $query = "SELECT COUNT(pase_id) AS recibidos FROM siga_documento_pase 
        WHERE  pase_estadopase='Recibido' AND pase_documento_id = ?";  
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $iddoc
            ]);
            if ($result) {
                $nro_recibidos = $result[0]['recibidos'];
                if($nro_recibidos>0){
                    response::error('No se puede actualizar por que ya fue recepcionado');
                    return;
                }
            } 
        } catch (PDOException $e) {
             response::error('Error al insertar el pase: ' . $e->getMessage());
        }


        //OBTENER DATOS DE PASE Y PRIMOGENIO  ----------------------------------------
        // ---------------------------------------------------------------------------

        $query_primogenio = "SELECT pase_documento_primogenio_id as primogenioid FROM siga_documento_pase 
        WHERE pase_documento_id = ?";
        try {
            $result_primogenio = $this->database->ejecutarConsulta($query_primogenio, [
                $iddoc
            ]);
            if ($result_primogenio) {
                $primogenioid = $result_primogenio[0]['primogenioid'];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            response::error('Error al insertar el pase: ' . $e->getMessage());
        }


        // SI NO HAY RECIBIDOS ACTUALIZAMOS EL DOCUMENTO -----------------------------
        // ---------------------------------------------------------------------------

        $query = "SELECT documento_interno_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $iddoc,
                $asunto,
                $folios,
                $pdf_principal,
                $pdf_principal_html,
                $pdf_principal_estadofirma,
                $pdf_anexo1,
                $pdf_anexo1_estadofirma,
                $pdf_anexo2,
                $pdf_anexo2_estadofirma
            ]);


            if ($result) {
           
                //ELIMINAMOS PASES Y VOLVEMOS A CREAR  ---------------------------------------
                // ---------------------------------------------------------------------------

                $query_delete = "DELETE FROM siga_documento_pase  WHERE pase_documento_id = ?";      
                $respuesta_delete = false;
                try {
                    $result_delete = $this->database->ejecutarConsulta($query_delete, [
                        $iddoc
                    ]);
                    if ($result_delete) {
                        $respuesta_delete = true; 
                    } else {
                        $respuesta_delete = false;
                    }
                } catch (PDOException $e) {
                    response::error('Error al insertar el pase: ' . $e->getMessage());
                }

                // CREAMOS NUEVAMENTE LAS RELACIONES ELIMINADAS ------------------------------
                // ---------------------------------------------------------------------------

                if($respuesta_delete){
                    
                    //PASES ORIGINAL
                    foreach ($arrayOriginal as $item) {
                        $doc_buzondestino_id = $item['id'] ?? null;
                        $this->insert_primerpase($buzonorigen_id,$doc_buzondestino_id,$usuario_id,$usuario_nombre,$iddoc,"Original","Pase",$pase_proveido, $primogenioid,null);
                    }

                    //PASES ORIGINAL
                    foreach ($arrayCopia as $itemcopia) {
                        $doc_buzondestino_id = $itemcopia['id'] ?? null;
                        $this->insert_primerpase($buzonorigen_id,$doc_buzondestino_id,$usuario_id,$usuario_nombre,$iddoc,"Copia","Pase", $pase_proveido, $primogenioid,null);
                    }
                }
                
                //PASES PASE
                // foreach ($referenciasArray as $itemref) {
                //     $id_doc_referenciado = $itemref;
                //     $this->insert_documento_referencia($result,$id_doc_referenciado,"Relacion");
                // }

                if($result[0]['documento_interno_update']){
                    response::success($result[0]['documento_interno_update'], 'Registro Actualizado');
                }else{
                    Response::error('No se actualizó '.$result[0]['documento_interno_update']);
                }         
                
            } else {
                Response::error('Error desconocido al insertar el documento'.$result);
            }

        } catch (PDOException $e) {
            Response::error('Error de base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            Response::error('Error inesperado: ' . $e->getMessage());
        }
    }



    // Insertar documento con restricciones
    public function Actualizar_Documento_Correccion_Trabajador(
        $iddoc,
        $asunto,
        $folios,
        $pdf_principal,
        $pdf_principal_html,
        $pdf_principal_estadofirma,
        $pdf_anexo1,
        $pdf_anexo1_estadofirma,
        $pdf_anexo2,
        $pdf_anexo2_estadofirma,
        $buzonorigen_id,
        $usuario_id,
        $usuario_nombre,
        $arrayOriginal,
        $arrayCopia,
        $referenciasArray,
        $otrasreferenciasArray,
        $pase_proveido
    ) {


        //OBTENER DATOS DE PASE Y PRIMOGENIO  ---------------------------------------------

        $query_primogenio = "SELECT pase_documento_primogenio_id as primogenioid FROM siga_documento_pase 
        WHERE pase_documento_id = ?";
        try {
            $result_primogenio = $this->database->ejecutarConsulta($query_primogenio, [
                $iddoc
            ]);
            if ($result_primogenio) {
                $primogenioid = $result_primogenio[0]['primogenioid'];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            response::error('Error al insertar el pase: ' . $e->getMessage());
        }
        // SI NO HAY RECIBIDOS ACTUALIZAMOS EL DOCUMENTO ----------------------------------

        $query = "SELECT documento_interno_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $iddoc,
                $asunto,
                $folios,
                $pdf_principal,
                $pdf_principal_html,
                $pdf_principal_estadofirma,
                $pdf_anexo1,
                $pdf_anexo1_estadofirma,
                $pdf_anexo2,
                $pdf_anexo2_estadofirma
            ]);


            if ($result) {
           
                    
                //PASES ORIGINAL
                // foreach ($arrayOriginal as $item) {
                //     $doc_buzondestino_id = $item['id'] ?? null;
                //     $this->insert_primerpase($buzonorigen_id,$doc_buzondestino_id,$usuario_id,$usuario_nombre,$iddoc,"Original","Pase",$pase_proveido, $primogenioid);
                // }

                if($result[0]['documento_interno_update']){
                    response::success($result[0]['documento_interno_update'], 'Registro Actualizado');
                }else{
                    Response::error('No se actualizó '.$result[0]['documento_interno_update']);
                }         
                
            } else {
                Response::error('Error desconocido al insertar el documento'.$result);
            }

        } catch (PDOException $e) {
            Response::error('Error de base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            Response::error('Error inesperado: ' . $e->getMessage());
        }
    }



    // ------------------------------------------------------
    // GUARDAR PRIMER PASE _ INICIADO
    public function insert_primerpase_externo(
        $pase_buzonorigen_id,
        $pase_buzondestino_id,
        $pase_usuario_id,
        $pase_usuarionombre,
        $pase_documento_id,
        $pase_tipo,
        $pase_tipoaccion,
        $pase_primogenio
    ) {
        $query = "INSERT INTO siga_documento_pase (
                    pase_documento_id,
                    pase_buzonorigen_id,
                    pase_buzondestino_id,
                    pase_tipopase,
                    pase_proveido,
                    pase_observacion,
                    pase_estadopase,
                    pase_usuario_id,
                    pase_usuarionombre,
                    pase_tipoaccion,
                    pase_documento_primogenio_id
                  ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                  )";
    
        try {
            $pase_proveido ="";
            $pase_observacion ="";
            $pase_estado="Iniciado";

            $result = $this->database->ejecutarConsulta($query, [
                $pase_documento_id,
                $pase_buzonorigen_id,
                $pase_buzondestino_id,
                $pase_tipo,
                $pase_proveido,
                $pase_observacion,
                $pase_estado,
                $pase_usuario_id,
                $pase_usuarionombre,
                $pase_tipoaccion,
                $pase_primogenio
            ]);
    
            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            response::error('Error al insertar el pase: ' . $e->getMessage());
        }
    }
    


    // ------------------------------------------------------
    // GUARDAR PRIMER PASE _ INICIADO DOCUMENTO INTERNO
    public function insert_primerpase(
        $pase_buzonorigen_id,
        $pase_buzondestino_id,
        $pase_usuario_id,
        $pase_usuarionombre,
        $pase_documento_id,
        $pase_tipo,
        $pase_tipoaccion,
        $pase_proveido,
        $pase_primogenio,
        $pase_idprevio
    ) {
        $query = "INSERT INTO siga_documento_pase (
                    pase_documento_id,
                    pase_buzonorigen_id,
                    pase_buzondestino_id,
                    pase_tipopase,
                    pase_proveido,
                    pase_observacion,
                    pase_estadopase,
                    pase_usuario_id,
                    pase_usuarionombre,
                    pase_tipoaccion,
                    pase_documento_primogenio_id,
                    pase_idprevio
                  ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                  )";
    
        try {
            
            $pase_observacion ="";
            $pase_estado="Enviado";

            $result = $this->database->ejecutarConsulta($query, [
                $pase_documento_id,
                $pase_buzonorigen_id,
                $pase_buzondestino_id,
                $pase_tipo,
                $pase_proveido,
                $pase_observacion,
                $pase_estado,
                $pase_usuario_id,
                $pase_usuarionombre,
                $pase_tipoaccion,
                $pase_primogenio,
                $pase_idprevio
            ]);
    
            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            response::error('Error al insertar el pase: ' . $e->getMessage());
        }
    }




    // ------------------------------------------------------
    // GUARDAR PRIMER PASE _ INICIADO
    public function insert_documento_referencia(
        $refer_id_documento_origen,  
        $refer_id_documento_referenciado,
        $refer_tiporeferencia  
    ) {
        // Consulta para insertar en la tabla siga_documento_referencia
        $query_referencia = "INSERT INTO siga_documento_referencia (
                                refer_id_documento_origen,
                                refer_id_documento_referenciado,
                                refer_tiporeferencia
                              ) VALUES (
                                ?, ?, ?
                              )";
        
        try {
            // Ejecutar la consulta para insertar en siga_documento_referencia
            $result_referencia = $this->database->ejecutarConsulta($query_referencia, [
                $refer_id_documento_origen,
                $refer_id_documento_referenciado,
                $refer_tiporeferencia
            ]);
    
            // Verificar si la inserción fue exitosa
            if ($result_referencia) {
                return true; // Inserción exitosa
            } else {
                return false; // Falló la inserción
            }
        } catch (PDOException $e) {
            response::error('Error al insertar la referencia: ' . $e->getMessage());
        }
    }
    


   
    // ----------------------------------------
    // GENERA CORRELATIVO (N° DOCUMENTO - AÑO)
    public function genera_numero_documento($procedencia) {
        $anioActual = date("Y");
        try {
            // Lógica para el incremento del correlativo
            if ($procedencia == 'Interno') {
                // Si es "Interno", se genera un correlativo consecutivo
                $query = "SELECT COALESCE(
                            (
                                SELECT doc_numerodocumento + 1
                                FROM siga_documento 
                                WHERE EXTRACT(YEAR FROM doc_fecharegistro) = ? and
                                      doc_procedencia = ?
                                ORDER BY doc_iddoc DESC
                                LIMIT 1
                            ), 1
                        ) AS siguiente";
                $params = [$anioActual, $procedencia];
            } else {
                // Si es Externo, ExternoCasilla o ExternoVirtual, el correlativo aumenta sin importar si es el mismo valor
                $query = "SELECT COALESCE(
                            (
                                SELECT MAX(doc_numerodocumento) + 1
                                FROM siga_documento 
                                WHERE EXTRACT(YEAR FROM doc_fecharegistro) = ? and
                                      doc_procedencia IN ('Externo', 'ExternoCasilla', 'ExternoVirtual')
                            ), 1
                        ) AS siguiente";
                $params = [$anioActual];
            }
    
            // Ejecutar consulta
            $result = $this->database->ejecutarConsulta($query, $params);
    
            // Obtener el nuevo código
            $nuevoCodigo = $result[0]['siguiente'];
    
            return $nuevoCodigo;
    
        } catch (PDOException $e) {
            throw new Exception('Error al generar el código del documento: ' . $e->getMessage());
        }
    }
    


    // ----------------------------------------
    // GENERA CORRELATIVO (N° DOCUMENTO - TIPO_DOCUMENTO_USRORIGFEN_ AÑO)
    public function genera_numeracion_tipodoc_oficina($procedencia, $tipodocumento_id, $user_buzonorigen_id) {
        $anioActual = date("Y");
        try {
            // Prepara la consulta para obtener el último código generado en el año actual
            $query = "SELECT COALESCE(
                        (
                            SELECT doc_numeracion_tipodoc_oficina + 1
                            FROM siga_documento 
                            WHERE EXTRACT(YEAR FROM doc_fecharegistro) = ? and
                                  doc_procedencia= ? and  doc_tipodocumento_id= ? and  doc_buzonorigen_id= ?
                            ORDER BY doc_iddoc DESC
                            LIMIT 1
                        ), 1
                    ) AS siguiente";
            
            $result = $this->database->ejecutarConsulta($query, [$anioActual, $procedencia, $tipodocumento_id, $user_buzonorigen_id]);
    
         
            $nuevoCodigo = $result[0]['siguiente'];
    
            return $nuevoCodigo;
    
        } catch (PDOException $e) {
            throw new Exception('Error al generar el código del documento: ' . $e->getMessage());
        }
    }

    // GENERA CORRELATIVO (N° DOCUMENTO - TIPO_DOCUMENTO_USRORIGFEN_ AÑO)
    public function genera_numeracion_tipodoc_oficina_general($procedencia, $tipodocumento_id, $user_buzon_oficina_id) {
        //OBTENER BUZON DE ORIGEN PRINCIPAL
        $query_buzon = "  SELECT  
                    buz.buzon_id as buzon_id
                    FROM siga_usuario AS su
                    LEFT JOIN siga_rolusuario rou ON su.usr_rol_id = rou.rol_id
                    LEFT JOIN siga_oficina ofi ON su.usr_oficina_id = ofi.ofi_id
                    LEFT JOIN siga_asignacion_usuario_buzon aub ON su.usr_id = aub.asig_usrid
                    LEFT JOIN siga_buzon buz ON buz.buzon_id = aub.asig_buzonid
                    where ofi.ofi_id =? and rou.rol_id=3";
        $result_buzon = $this->database->ejecutarConsulta($query_buzon, [$user_buzon_oficina_id]);
        $user_buzonorigen_id = $result_buzon[0]['buzon_id'];

        // *********************************************************
        // *********************************************************

        $anioActual = date("Y");
        try {
            $query = "SELECT COALESCE(
                        (
                            SELECT doc_numeracion_tipodoc_oficina + 1
                            FROM siga_documento 
                            WHERE EXTRACT(YEAR FROM doc_fecharegistro) = ? and
                                  doc_procedencia= ? and  doc_tipodocumento_id= ? and  doc_buzonorigen_id= ?
                            ORDER BY doc_iddoc DESC
                            LIMIT 1
                        ), 1
                    ) AS siguiente";
            
            $result = $this->database->ejecutarConsulta($query, [$anioActual, $procedencia, $tipodocumento_id, $user_buzonorigen_id]);
            $nuevoCodigo = $result[0]['siguiente'];
    
            return $nuevoCodigo;
    
        } catch (PDOException $e) {
            throw new Exception('Error al generar el código del documento: ' . $e->getMessage());
        }
    }

   

    
    public function genera_numero_documento_fn($procedencia) {
        $nuevoCodigo=$this->genera_numero_documento($procedencia);
        Response::success($nuevoCodigo, 'Consulta de documento exitosa');
    }

    public function genera_numeracion_tipodoc_oficina_fn($procedencia, $tipodocumento_id, $user_buzonorigen_id) {
        $nuevoCodigo = $this->genera_numeracion_tipodoc_oficina($procedencia, $tipodocumento_id, $user_buzonorigen_id);   
        Response::success($nuevoCodigo, 'Consulta de documento exitosa'); 
    }

     public function genera_numeracion_tipodoc_oficina_fn_general($procedencia, $tipodocumento_id, $user_buzon_oficina_id) {
        $nuevoCodigo = $this->genera_numeracion_tipodoc_oficina_general($procedencia, $tipodocumento_id, $user_buzon_oficina_id);   
        Response::success($nuevoCodigo, 'Consulta de Documento de Buzon Principal exitosa'); 
    }
 
    // ----------------------------------------
    public function obtener_referencias($tipodocumento_id, $numerodocumento, $anio, $buzonorigen_id) {
        try {
            // Construir la consulta base
            $query = "SELECT * FROM documento_interno_obtenerdetalles_id() WHERE 1=1";
            
            // Arreglo para almacenar los parámetros
            $parametros = [];
            
            // Condición para tipodocumento_id
            if (!empty($tipodocumento_id)) {
                $query .= " AND tipodocumento_id = ?";
                $parametros[] = $tipodocumento_id;
            }
            
            // Condición para buzonorigen_id
            if (!empty($buzonorigen_id)) {
                $query .= " AND buzonorigen_id = ?";
                $parametros[] = $buzonorigen_id;
            }
            
            // Condición para anio
            if (!empty($anio)) {
                $query .= " AND anio = ?";
                $parametros[] = $anio;
            }
            
            // Condición para numerodocumento
            if (!empty($numerodocumento)) {
                $query .= " AND CAST(numeracion_tipodoc_oficina AS TEXT) LIKE ?";
                $parametros[] = '%' . $numerodocumento . '%';
            }
            
            // Ordenar los resultados por iddoc
            $query .= " ORDER BY iddoc ASC";
    
            // Ejecuta la consulta con los parámetros preparados
            $result = $this->database->ejecutarConsulta($query, $parametros);
    
            // Verifica si hay resultados
            if (count($result) > 0) {
                Response::success($result, 'Consulta de documento exitosa');
            } else {
                Response::error('No se encontraron documentos');
            }
    
        } catch (PDOException $e) {
            throw new Exception('Error al consultar referencias: ' . $e->getMessage());
        }
    }
    
    

    // ----------------------------------------
    // BUSQUEDA OPCIONAL DE DOCUMENTOS INTERNOS
    public function buscar_documentos_internos_opcional(
        $tipodocumento_nombre = null,
        $buzonorigen_nombre = null,
        $dia = null,
        $mes = null,
        $anio = null
    ) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id()";
        $where = [];
        $params = [];

        if ($tipodocumento_nombre) {
            $where[] = "tipodocumento_nombre ILIKE ?";
            $params[] = "%$tipodocumento_nombre%";
        }
        if ($buzonorigen_nombre) {
            $where[] = "buzonorigen_nombre ILIKE ?";
            $params[] = "%$buzonorigen_nombre%";
        }
        if ($dia) {
            $where[] = "EXTRACT(DAY FROM fecharegistro) = ?";
            $params[] = $dia;
        }
        if ($mes) {
            $where[] = "mes = ?";
            $params[] = $mes;
        }
        if ($anio) {
            $where[] = "anio = ?";
            $params[] = $anio;
        }

        if ($where) {
            $query .= " WHERE tipodocumento_id!=1 AND procedencia='Interno' AND " . implode(" AND ", $where);
        }

        try {
            $result = $this->database->ejecutarConsulta($query, $params);
            if ($result) {
                Response::success($result, 'Consulta de documentos internos exitosa');
            } else {
                Response::error('No se encontraron documentos internos');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }
    // ----------------------------------------
    // BUSQUEDA OPCIONAL DE DOCUMENTOS EXTERNOS
    public function buscar_documentos_externos_persona(
        $administrado_nombre = null,
        $administrado_apellidopat = null,
        $administrado_apellidomat = null,
        $administrado_tipodocumento = null,
        $administrado_numdocumento = null,
        $dia = null,
        $mes = null,
        $anio = null
    ) {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id()";
        $where = [];
        $params = [];

        if ($administrado_nombre) {
            $where[] = "administrado_nombre ILIKE ?";
            $params[] = "%$administrado_nombre%";
        }
        if ($administrado_apellidopat) {
            $where[] = "administrado_apellidopat ILIKE ?";
            $params[] = "%$administrado_apellidopat%";
        }
        if ($administrado_apellidomat) {
            $where[] = "administrado_apellidomat ILIKE ?";
            $params[] = "%$administrado_apellidomat%";
        }
        if ($administrado_tipodocumento) {
            $where[] = "administrado_tipodocumento = ?";
            $params[] = $administrado_tipodocumento;
        }
        if ($administrado_numdocumento) {
            $where[] = "administrado_numdocumento = ?";
            $params[] = $administrado_numdocumento;
        }
        if ($dia) {
            $where[] = "EXTRACT(DAY FROM fecharegistro) = ?";
            $params[] = $dia;
        }
        if ($mes) {
            $where[] = "mes = ?";
            $params[] = $mes;
        }
        if ($anio) {
            $where[] = "anio = ?";
            $params[] = $anio;
        }

        if ($where) {
            $query .= " WHERE " . implode(" AND ", $where);
        }

        try {
            $result = $this->database->ejecutarConsulta($query, $params);
            if ($result) {
                Response::success($result, 'Consulta de documentos exitosa');
            } else {
                Response::error('No se encontraron documentos');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    // ----------------------------------------
    public function get_lista_documento_Interno_buscar($anio, $doc) {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id()";
        $where = [];
        $params = [];
        if ($anio) {
            $where[] = "anio = ?";
            $params[] = $anio;
        }
        if ($doc) {
            $where[] = "numerodocumento = ?";
            $params[] = $doc;
        }
        if ($where) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        try {
            $result = $this->database->ejecutarConsulta($query, $params);
            if ($result) {
                Response::success($result, 'Consulta de documento exitosa');
            } else {
                Response::error('No se encontró el documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }
    // ----------------------------------------
    // POR ID DOCUMENTO
    // ----------------------------------------
    public function get_lista_documentos_Externos($doc_iddoc) {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id()
                 WHERE iddoc=?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$doc_iddoc]);
            if ($result) {
                Response::success($result[0], 'Consulta de documento exitosa');
            } else {
                Response::error('No se encontró el documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }
    

    // ----------------------------------------
    public function get_lista_documento_Interno($doc_iddoc) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id()
                  WHERE iddoc=?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$doc_iddoc]);
            if ($result) {
                Response::success($result[0], 'Consulta de documento exitosa');
            } else {
                Response::error('No se encontró el documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    // ----------------------------------------
    public function get_documento_externo($doc_iddoc) {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id()
                  WHERE procedencia!='Interno' AND iddoc=?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$doc_iddoc]);
            if ($result) {
                Response::success($result[0], 'Consulta de documento exitosa');
            } else {
                Response::error('No se encontró el documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

     // ----------------------------------------
     public function get_documento_interno($doc_iddoc) {
        $query = "SELECT * FROM documento_interno_edit_id()
                  WHERE procedencia!='Externo' AND iddoc=?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$doc_iddoc]);
            if ($result) {
                Response::success($result[0], 'Consulta de documento exitosa');
            } else {
                Response::error('No se encontró el documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }


    // ----------------------------------------
    public function get_lista_documento_Interno_anionro($anio,$doc, $codigo) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id()
                  WHERE procedencia!='Interno' AND anio=? AND numerodocumento=? AND codigoseguimiento=?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$anio, $doc, $codigo]);
            if ($result) {
                Response::success($result[0], 'Consulta de documento exitosa');
            } else {
                Response::error('No se encontró el documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    // POR ESTADO DE PRIMERA PASE

    // ----------------------------------------
    // OBTENER LISTA DOCUMENTOS MESA PARTES
    
    public function listarDocumentos_mesapartes_iniciado() {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id() 
                  WHERE pase_estadopase='Iniciado'
                  and  procedencia IN ('Externo', 'ExternoCasilla', 'ExternoVirtual')
                  ORDER BY pase_fechaenvio DESC";
        try {
            $result = $this->database->ejecutarConsulta($query);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    // public function listarDocumentos_interno_iniciado() {
    //     $query = "SELECT * FROM documento_interno_obtenerdetalles_id()
    //         Where procedencia='Interno'
    //         ORDER BY pase_fechaenvio DESC";
    //     try {
    //         $result = $this->database->ejecutarConsulta($query);
    //         if ($result) {
    //             Response::success($result, 'Lista de documentos obtenida correctamente');
    //         } else {
    //             Response::error('No se encontraron documentos registrados');
    //         }
    //     } catch (PDOException $e) {
    //         Response::error("Error en la base de datos: " . $e->getMessage());
    //     }
    // }

    public function listarDocumentos_externo_x_nro_documento($numdoc) {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id() 
                  WHERE pase_estadopase='Iniciado'
                  and  procedencia IN ('Externo', 'ExternoCasilla', 'ExternoVirtual') and
                  administrado_numdocumento=?
                  ORDER BY pase_fechaenvio DESC";
        try {
            $result = $this->database->ejecutarConsulta($query, [$numdoc]);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function listarDocumentos_mesapartes_enviados() {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id() 
                  WHERE pase_estadopase='Enviado' or pase_estadopase='Recibido' or pase_estadopase='Tramitado' or pase_estadopase='Archivado'
                  and  procedencia IN ('Externo', 'ExternoCasilla', 'ExternoVirtual')
                  ORDER BY pase_fechaenvio DESC";
        try {
            $result = $this->database->ejecutarConsulta($query);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }    


    public function listarDocumentos_mesapartes_enviados_paginado(
        $pagina = 1, 
        $registrosPorPagina = 25, 
        $busqueda = '', 
        $campoBusqueda = '', 
        $ordenarPor = 'pase_fechaenvio', 
        $direccionOrden = 'DESC'
    ) {
        try {
            // Validar parámetros de entrada
            $pagina = max(1, intval($pagina));
            $registrosPorPagina = max(1, min(100, intval($registrosPorPagina))); // Máximo 100 registros
            $offset = ($pagina - 1) * $registrosPorPagina;
            
            // Campos válidos para ordenamiento (seguridad)
            $camposValidosOrden = [
                'pase_fechaenvio', 'numerodocumento', 'anio', 'cabecera', 'asunto', 
                'administrado_nombre', 'tipodocumento_nombre', 'pase_buzondestino_nombre',
                'procedencia', 'prioridad', 'pase_estadopase'
            ];
            
            if (!in_array($ordenarPor, $camposValidosOrden)) {
                $ordenarPor = 'pase_fechaenvio';
            }
            
            $direccionOrden = strtoupper($direccionOrden) === 'ASC' ? 'ASC' : 'DESC';
            
            // Construir condiciones WHERE
            $whereConditions = [
                "(pase_estadopase='Enviado' OR pase_estadopase='Recibido' OR pase_estadopase='Tramitado' OR pase_estadopase='Archivado')",
                "procedencia IN ('Externo', 'ExternoCasilla', 'ExternoVirtual')"
            ];
            
            $parametros = [];
            
            // Agregar búsqueda si se proporciona
            if (!empty($busqueda)) {
                $busqueda = trim($busqueda);
                
                if (!empty($campoBusqueda)) {
                    // Búsqueda en campo específico
                    $camposValidosBusqueda = [
                        'numerodocumento', 'cabecera', 'asunto', 'administrado_nombre', 
                        'administrado_apellidopat', 'administrado_apellidomat', 'administrado_razonsocial',
                        'tipodocumento_nombre', 'pase_buzondestino_nombre', 'procedencia',
                        'administrado_numdocumento', 'codigoseguimiento'
                    ];
                    
                    if (in_array($campoBusqueda, $camposValidosBusqueda)) {
                        if ($campoBusqueda === 'numerodocumento') {
                            $whereConditions[] = "CAST(numerodocumento AS TEXT) LIKE ?";
                            $parametros[] = '%' . $busqueda . '%';
                        } else {
                            $whereConditions[] = "$campoBusqueda ILIKE ?";
                            $parametros[] = '%' . $busqueda . '%';
                        }
                    }
                } else {
                    // Búsqueda general en múltiples campos
                    $whereConditions[] = "(
                        cabecera ILIKE ? OR 
                        asunto ILIKE ? OR 
                        administrado_nombre ILIKE ? OR 
                        administrado_apellidopat ILIKE ? OR 
                        administrado_apellidomat ILIKE ? OR 
                        administrado_razonsocial ILIKE ? OR 
                        tipodocumento_nombre ILIKE ? OR 
                        CAST(numerodocumento AS TEXT) LIKE ? OR
                        administrado_numdocumento LIKE ? OR
                        codigoseguimiento ILIKE ?
                    )";
                    
                    // Agregar el mismo parámetro de búsqueda 10 veces
                    for ($i = 0; $i < 10; $i++) {
                        $parametros[] = '%' . $busqueda . '%';
                    }
                }
            }
            
            // Construir la consulta WHERE
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
            
            // 1. Consulta para contar el total de registros
            $queryCount = "SELECT COUNT(*) as total FROM documento_externo_obtenerdetalles_id() $whereClause";
            $resultCount = $this->database->ejecutarConsulta($queryCount, $parametros);
            $totalRegistros = $resultCount[0]['total'];
            
            // 2. Consulta para obtener los datos paginados
            $queryData = "SELECT * FROM documento_externo_obtenerdetalles_id() 
                        $whereClause 
                        ORDER BY $ordenarPor $direccionOrden 
                        LIMIT ? OFFSET ?";
            
            // Agregar parámetros de paginación
            $parametrosPaginacion = array_merge($parametros, [$registrosPorPagina, $offset]);
            $resultData = $this->database->ejecutarConsulta($queryData, $parametrosPaginacion);
            
            // Calcular información de paginación
            $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
            $paginaAnterior = $pagina > 1 ? $pagina - 1 : null;
            $paginaSiguiente = $pagina < $totalPaginas ? $pagina + 1 : null;
            
            // Generar array de páginas para navegación (máximo 10 páginas visibles)
            $paginasVisibles = $this->generarPaginasVisibles($pagina, $totalPaginas, 10);
            
            // Preparar respuesta
            $respuesta = [
                'datos' => $resultData ?: [],
                'paginacion' => [
                    'pagina_actual' => $pagina,
                    'registros_por_pagina' => $registrosPorPagina,
                    'total_registros' => intval($totalRegistros),
                    'total_paginas' => $totalPaginas,
                    'pagina_anterior' => $paginaAnterior,
                    'pagina_siguiente' => $paginaSiguiente,
                    'paginas_visibles' => $paginasVisibles,
                    'desde' => $offset + 1,
                    'hasta' => min($offset + $registrosPorPagina, $totalRegistros)
                ],
                'busqueda' => [
                    'termino' => $busqueda,
                    'campo' => $campoBusqueda,
                    'resultados_encontrados' => intval($totalRegistros)
                ],
                'ordenamiento' => [
                    'campo' => $ordenarPor,
                    'direccion' => $direccionOrden
                ]
            ];
            
            if ($totalRegistros > 0) {
                Response::success($respuesta, "Se encontraron $totalRegistros documentos");
            } else {
                Response::success($respuesta, 'No se encontraron documentos que coincidan con los criterios');
            }
            
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            Response::error("Error inesperado: " . $e->getMessage());
        }
    }

    /**
     * Generar array de páginas visibles para la navegación
     */
    private function generarPaginasVisibles($paginaActual, $totalPaginas, $maximoPaginas = 10) {
        $paginas = [];
        
        if ($totalPaginas <= $maximoPaginas) {
            // Si hay pocas páginas, mostrar todas
            for ($i = 1; $i <= $totalPaginas; $i++) {
                $paginas[] = $i;
            }
        } else {
            // Calcular rango de páginas a mostrar
            $mitad = floor($maximoPaginas / 2);
            $inicio = max(1, $paginaActual - $mitad);
            $fin = min($totalPaginas, $paginaActual + $mitad);
            
            // Ajustar si estamos muy cerca del inicio o fin
            if ($fin - $inicio + 1 < $maximoPaginas) {
                if ($inicio === 1) {
                    $fin = min($totalPaginas, $inicio + $maximoPaginas - 1);
                } else {
                    $inicio = max(1, $fin - $maximoPaginas + 1);
                }
            }
            
            // Agregar primera página si no está incluida
            if ($inicio > 1) {
                $paginas[] = 1;
                if ($inicio > 2) {
                    $paginas[] = '...';
                }
            }
            
            // Agregar páginas del rango
            for ($i = $inicio; $i <= $fin; $i++) {
                $paginas[] = $i;
            }
            
            // Agregar última página si no está incluida
            if ($fin < $totalPaginas) {
                if ($fin < $totalPaginas - 1) {
                    $paginas[] = '...';
                }
                $paginas[] = $totalPaginas;
            }
        }
        
        return $paginas;
    }
    
    public function listarDocumentos_mesapartes_enviados_imprimir($desde = null, $hasta = null) {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id() 
                  WHERE pase_estadopase='Enviado' or pase_estadopase='Recibido' or pase_estadopase='Tramitado' or pase_estadopase='Archivado'
                  and  procedencia IN ('Externo', 'ExternoCasilla', 'ExternoVirtual')
                  ORDER BY pase_fechaenvio DESC";
        
        // Si se especifican límites, agregar LIMIT y OFFSET
        $parametros = [];
        if ($desde !== null && $hasta !== null) {
            $limite = $hasta - $desde + 1;
            $offset = $desde - 1;
            $query .= " LIMIT ? OFFSET ?";
            $parametros = [$limite, $offset];
        }
        
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }
    
    

    // ----------------------------------------
    public function get_destinos_por_iddocumento($doc_iddoc) {
        $query = "SELECT 
                    p.pase_buzondestino_id as id,
                    buzon_pase_destino.buzon_nombre AS destino,
                    p.pase_tipopase AS tipobuzon
                FROM 
                    siga_documento_pase p
                    JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = p.pase_buzonorigen_id
                    JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = p.pase_buzondestino_id
                    JOIN siga_documento d ON p.pase_documento_id = d.doc_iddoc
                WHERE 
                    p.pase_documento_id = ?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$doc_iddoc]);
            if ($result) {
                Response::success($result, 'Consulta de documento exitosa');
            } else {
                Response::error('No se encontró el documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    

    // ----------------------------------------
    // Actualizar un documento
    // ----------------------------------------
    public function updateDocumento(
        $doc_iddoc,
        $origen,
        $prioridad,
        $cabecera,
        $asunto,
        $folios,
        $administrado_id,
        $tipodocumento_id,
        $descripcion,
        $estupa,
        $pdf_principal,
        $p_pase_id,
        $p_pase_iddestino,
        $fechavencimiento,
        $proyectar,
        $usuarionombre,
        $tramitetupa_id
    ) {
        // Validar que $fechavencimiento sea una fecha válida o NULL
        $fechavencimiento = (empty($fechavencimiento) || $fechavencimiento === '') ? null : $fechavencimiento;

        $query = "SELECT actualizar_documento_externo_y_pase(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                            $doc_iddoc,
                            $origen,
                            $prioridad,
                            $cabecera,
                            $asunto,
                            $folios,
                            $administrado_id,
                            $tipodocumento_id,
                            $descripcion,
                            $estupa,
                            $pdf_principal,
                            $p_pase_id,
                            $p_pase_iddestino,
                            $proyectar,
                            $usuarionombre,
                            $fechavencimiento,
                            $tramitetupa_id
            ]);

            $respuesta = $result[0]['actualizar_documento_externo_y_pase'];

            switch ($respuesta) {
                case 1:
                    Response::success($respuesta, 'Documento actualizado correctamente');
                    break;
                case -1:
                    Response::error('Error: El documento no fue encontrado.');
                    break;
                case -2:
                    Response::error('Error: El pase no fue encontrado.');
                    break;
                case -3:
                    Response::error('Error: No se pudo actualizar el documento.');
                    break;
                case -4:
                    Response::error('Error: No se pudo actualizar el pase.');
                    break;
                case -5:
                    Response::error('Error: Violación de clave foránea. Verifique las referencias.');
                    break;
                case -6:
                    Response::error('Error: Se intentó ingresar un valor nulo en una columna que no lo permite.');
                    break;
                case -7:
                    Response::error('Error inesperado en la base de datos.');
                    break;
                default:
                    Response::error('Error desconocido con código: ' . $respuesta);
                    break;
            }
        } catch (PDOException $e) {
            Response::error('Error de base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            Response::error('Error inesperado: ' . $e->getMessage());
        }
    }

    // ----------------------------------------
    // Actualizar un documento
    // ----------------------------------------
    public function updateDocumento_interno(
        $doc_iddoc,
        $prioridad,
        $asunto,
        $folios,
        $pdf_principal,
        $pdf_principal_html,
        $pdf_principal_estadofirma,
        $pdf_anexo1,
        $pdf_anexo1_estadofirma,
        $pdf_anexo2,
        $pdf_anexo2_estadofirma
    ) {
        // Preparar la consulta SQL para llamar a la función PostgreSQL
        $query = "SELECT update_documento($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)";
    
        try {
            // Ejecutar la consulta con los parámetros
            $result = $this->database->ejecutarConsulta($query, [
                $doc_iddoc,
                $prioridad,
                $asunto,
                $folios,
                $pdf_principal,
                $pdf_principal_html,
                $pdf_principal_estadofirma,
                $pdf_anexo1,
                $pdf_anexo1_estadofirma,
                $pdf_anexo2,
                $pdf_anexo2_estadofirma
            ]);
    
            // Obtener el resultado (la función devuelve un BOOLEAN)
            $respuesta = $result[0]['update_documento'];
    
            // Manejar la respuesta
            if ($respuesta === true) {
                Response::success(true, 'Documento actualizado correctamente');
            } else {
                Response::error('No se pudo actualizar el documento o no se encontró el ID especificado.');
            }
        } catch (PDOException $e) {
            Response::error('Error de base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            Response::error('Error inesperado: ' . $e->getMessage());
        }
    }


    public function listarDocumentosEmitidos_por_usuario_anio($user_buzonorigen_id, $usranio) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id()
                    WHERE buzonorigen_id = ? AND
                        anio = ? 
                    ORDER BY numerodocumento DESC";

                $parametros = [
                    $user_buzonorigen_id,
                    $usranio
                ];
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    // ----------------------------------------
    // RUTA DE PASES
    // ----------------------------------------
    
    public function listarDocumentos_deOficina($estado, $id_buzondestino) {
        $query = "SELECT 
                        pase.pase_id as idpase,
                        pase.pase_buzonorigen_id as origen_id,
                        buzon_pase_origen.buzon_nombre AS origen_nombre,
                        buzon_pase_origen.buzon_sigla AS origen_sigla,
                        pase.pase_buzondestino_id as destino_id,
                        buzon_pase_destino.buzon_nombre AS destino_nombre,
                        pase.pase_tipopase as tipopase,
                        pase.pase_proveido as pase_proveido,
                        pase.pase_observacion as observacion, 
                        pase.pase_estadopase as estadopase,
                        pase.pase_documento_id as documento_id,
                        pase.pase_usuario_id as usuario_remitente_id, 
                        pase.pase_usuarionombre as usuari_remitente_onombre,
                        pase.pase_fechaenvio as pase_fechaenvio,
                        pase.pase_fecharecepcion as pase_fecharecepcion,
                        pase.pase_documento_primogenio_id as primogenio_id,
                        sd.doc_numerodocumento AS numerodocumento,
                        buzon_documento.buzon_nombre As origen,
                        buzon_documento.buzon_sigla as sigla,
                        sd.doc_numeracion_tipodoc_oficina AS numeracion_tipodoc_oficina,
                        sd.doc_procedencia AS procedencia,
                        sd.doc_cabecera AS cabecera,
                        sd.doc_asunto AS asunto,
                        sd.doc_folios AS folios,
                        sd.doc_prioridad AS prioridad,
                        sd.doc_pdf_principal AS pdf_principal,
                        sd.doc_pdf_anexo1 AS pdf_anexo1,
                        sd.doc_pdf_anexo2 AS pdf_anexo2,
                        sd.doc_anio AS anio,
                        sd.doc_mes AS mes,
                        sd.doc_proyectar AS proyectar,
                        sd.doc_descripcion  AS descripcion,
                        sd.doc_tipodocumento_id AS tipodocumento_id,
                        sd.doc_referencias_id AS referencias_id,
                        tip.tipo_nombre AS tipodocumento_nombre,
                        sd2.doc_numerodocumento AS expediente,
                        sd2.doc_procedencia AS expediente_procedencia,
                        sd2.doc_cabecera AS expediente_cabecera,
                        adm.adm_nombre AS administrado_nombre,
                        adm.adm_apellidopat AS administrado_apellidopat,
                        adm.adm_apellidomat AS administrado_apellidomat,
                        adm.adm_tipodocumento AS administrado_tipodocumento,
                        adm.adm_numdocumento AS administrado_numdocumento,
                        adm.adm_razonsocial AS administrado_razonsocial
                    FROM siga_documento_pase as pase
                    LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                    LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                    INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
                    LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
                    LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
                    LEFT JOIN siga_documento AS sd2 ON sd2.doc_iddoc = pase.pase_documento_primogenio_id
                    LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
                    WHERE pase_buzondestino_id = ? AND pase.pase_estadopase = ? AND sd.doc_estado = 'Enviado'
                    ORDER BY pase_fechaenvio DESC";

            $parametros = [
                $id_buzondestino,
                $estado
            ]; 
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    // ----------------------------------------
    // ----------------------------------------
    // ----------------------------------------
    // ----------------------------------------
    
    public function listarDocumentos_Enviados_Oficina($id_buzonorigen) {
        $query = "SELECT 
                        pase.pase_id as idpase,
                        pase.pase_buzonorigen_id as origen_id,
                        buzon_pase_origen.buzon_nombre AS origen_nombre,
                        buzon_pase_origen.buzon_sigla AS origen_sigla,
                        pase.pase_buzondestino_id as destino_id,
                        buzon_pase_destino.buzon_nombre AS destino_nombre,
                        pase.pase_tipopase as tipopase,
                        pase.pase_proveido as pase_proveido,
                        pase.pase_observacion as observacion, 
                        pase.pase_estadopase as estadopase,
                        pase.pase_documento_id as documento_id,
                        pase.pase_usuario_id as usuario_remitente_id, 
                        pase.pase_usuarionombre as usuari_remitente_onombre,
                        pase.pase_fechaenvio as pase_fechaenvio,
                        pase.pase_fecharecepcion as pase_fecharecepcion,
                        pase.pase_documento_primogenio_id as primogenio_id,
                        sd.doc_numerodocumento AS numerodocumento,
                        buzon_documento.buzon_nombre As origen,
                        buzon_documento.buzon_sigla as sigla,
                        sd.doc_numeracion_tipodoc_oficina AS numeracion_tipodoc_oficina,
                        sd.doc_procedencia AS procedencia,
                        sd.doc_cabecera AS cabecera,
                        sd.doc_asunto AS asunto,
                        sd.doc_folios AS folios,
                        sd.doc_prioridad AS prioridad,
                        sd.doc_pdf_principal AS pdf_principal,
                        sd.doc_pdf_anexo1 AS pdf_anexo1,
                        sd.doc_pdf_anexo2 AS pdf_anexo2,
                        sd.doc_anio AS anio,
                        sd.doc_mes AS mes,
                        sd.doc_proyectar AS proyectar,
                        sd.doc_descripcion  AS descripcion,
                        sd.doc_tipodocumento_id AS tipodocumento_id,
                        sd.doc_referencias_id as referencias_id,
                        tip.tipo_nombre AS tipodocumento_nombre,
                        sd2.doc_numerodocumento AS expediente,
                        sd2.doc_procedencia AS expediente_procedencia,
                        sd2.doc_cabecera AS expediente_cabecera,
                        adm.adm_nombre AS administrado_nombre,
                        adm.adm_apellidopat AS administrado_apellidopat,
                        adm.adm_apellidomat AS administrado_apellidomat,
                        adm.adm_tipodocumento AS administrado_tipodocumento,
                        adm.adm_numdocumento AS administrado_numdocumento,
                        adm.adm_razonsocial AS administrado_razonsocial
                    FROM siga_documento_pase as pase
                    LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                    LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                    INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
                    LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
                    LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
                    LEFT JOIN siga_documento AS sd2 ON sd2.doc_iddoc = pase.pase_documento_primogenio_id
                    LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
                    WHERE pase_buzonorigen_id = ? AND (pase.pase_estadopase = 'Iniciado' or pase.pase_estadopase = 'Enviado' or pase.pase_estadopase = 'Tramitado' or pase.pase_estadopase = 'Recibido')
                          AND sd.doc_estado = 'Enviado'
                    ORDER BY pase_fechaenvio DESC";
            $parametros = [ $id_buzonorigen ];
            
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function listarDocumentos_Archivados_Oficina($estado, $id_buzonorigen, $id_buzondestino) {
        $query = "SELECT 
                        pase.pase_id as idpase,
                        pase.pase_buzonorigen_id as origen_id,
                        buzon_pase_origen.buzon_nombre AS origen_nombre,
                        buzon_pase_origen.buzon_sigla AS origen_sigla,
                        pase.pase_buzondestino_id as destino_id,
                        buzon_pase_destino.buzon_nombre AS destino_nombre,
                        pase.pase_tipopase as tipopase,
                        pase.pase_proveido as pase_proveido,
                        pase.pase_observacion as observacion, 
                        pase.pase_estadopase as estadopase,
                        pase.pase_documento_id as documento_id,
                        pase.pase_usuario_id as usuario_remitente_id, 
                        pase.pase_usuarionombre as usuari_remitente_onombre,
                        pase.pase_fechaenvio as pase_fechaenvio,
                        pase.pase_fecharecepcion as pase_fecharecepcion,
                        pase.pase_documento_primogenio_id as primogenio_id,
                        sd.doc_numerodocumento AS numerodocumento,
                        buzon_documento.buzon_nombre As origen,
                        buzon_documento.buzon_sigla as sigla,
                        sd.doc_numeracion_tipodoc_oficina AS numeracion_tipodoc_oficina,
                        sd.doc_procedencia AS procedencia,
                        sd.doc_cabecera AS cabecera,
                        sd.doc_asunto AS asunto,
                        sd.doc_folios AS folios,
                        sd.doc_prioridad AS prioridad,
                        sd.doc_pdf_principal AS pdf_principal,
                        sd.doc_pdf_anexo1 AS pdf_anexo1,
                        sd.doc_pdf_anexo2 AS pdf_anexo2,
                        sd.doc_anio AS anio,
                        sd.doc_mes AS mes,
                        sd.doc_proyectar AS proyectar,
                        sd.doc_descripcion  AS descripcion,
                        sd.doc_tipodocumento_id AS tipodocumento_id,
                        sd.doc_referencias_id as referencias_id,
                        tip.tipo_nombre AS tipodocumento_nombre,
                        sd2.doc_numerodocumento AS expediente,
                        sd2.doc_procedencia AS expediente_procedencia,
                        sd2.doc_cabecera AS expediente_cabecera,
                        adm.adm_nombre AS administrado_nombre,
                        adm.adm_apellidopat AS administrado_apellidopat,
                        adm.adm_apellidomat AS administrado_apellidomat,
                        adm.adm_tipodocumento AS administrado_tipodocumento,
                        adm.adm_numdocumento AS administrado_numdocumento,
                        adm.adm_razonsocial AS administrado_razonsocial
                    FROM siga_documento_pase as pase
                    LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                    LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                    INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
                    LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
                    LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
                    LEFT JOIN siga_documento AS sd2 ON sd2.doc_iddoc = pase.pase_documento_primogenio_id
                    LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
                    WHERE pase_buzonorigen_id = ? AND  pase_buzondestino_id = ? AND pase.pase_estadopase = 'Archivo' 
                             AND sd.doc_estado = 'Enviado'
                    ORDER BY pase_fechaenvio DESC";

            $parametros = [
                $id_buzonorigen,
                $id_buzondestino
            ];
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function listarDocumentos_Proyectados_Oficina($id_buzonorigen) {
        $query = "SELECT * from documento_interno_obtenerdetalles_id()
                 WHERE estado='Proyectado' AND proyectar=true AND buzonorigen_id=?";
            $parametros = [ $id_buzonorigen ];
            
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }
    public function listarDocumentos_Proyectados_NombreUsuario($nombre_usuario) {
        $query = "SELECT * from documento_interno_obtenerdetalles_id()
                 WHERE estado='Proyectado' AND proyectar=true AND usuario_nombre=?";
            $parametros = [ $nombre_usuario ];
            
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function listarDocumentos_Iniciados_Oficina($id_buzonorigen) {
        $query = "SELECT 
        sd.doc_iddoc AS iddoc,
        sd.doc_numerodocumento AS numerodocumento, 
        sd.doc_numeracion_tipodoc_oficina AS numeracion_tipodoc_oficina,
        sd.doc_procedencia AS procedencia,
        sd.doc_buzonorigen_id AS buzonorigen_id,
        buzon_origen.buzon_nombre AS buzonorigen_nombre,
        buzon_origen.buzon_sigla AS buzon_sigla,
        sd.doc_cabecera AS cabecera,
        sd.doc_asunto AS asunto,
        sd.doc_prioridad AS prioridad,
        sd.doc_folios AS folios,
        sd.doc_administrado_id AS administrado_id,
        sd.doc_tipodocumento_id AS tipodocumento_id,
        tip.tipo_nombre AS tipodocumento_nombre,
        sd.doc_descripcion AS descripcion,
        sd.doc_estado AS estado,
        sd.doc_referencias_id AS referencias_id,
        sd.doc_otrasreferencias AS otrasreferencias,
        sd.doc_estupa AS estupa,
        sd.doc_fechavencimiento AS fechavencimiento,
        sd.doc_proyectar AS proyectar,
        sd.doc_usuarionombre AS usuarionombre,
        sd.doc_tramitetupa_id AS tramitetupa_id,
        tram.tram_nombretramite AS tramitetupa_nombre,
        sd.doc_fecharegistro AS fecharegistro,
        sd.doc_mes AS mes,
        sd.doc_anio AS anio,        
        pase.pase_id AS pase_id,
        pase.pase_buzonorigen_id AS pase_buzonorigen_id,
        buzon_pase_origen.buzon_nombre AS pase_buzonorigen_nombre,
        buzon_pase_origen.buzon_sigla AS origen_sigla,
        pase.pase_buzondestino_id AS pase_buzondestino_id,
        buzon_pase_destino.buzon_nombre AS pase_buzondestino_nombre,
        pase.pase_fechaenvio AS pase_fechaenvio,
        pase.pase_fecharecepcion AS pase_fecharecepcion,
        pase.pase_tipopase AS pase_tipopase,
        pase.pase_proveido AS pase_proveido,
        pase.pase_observacion AS pase_observacion,
        pase.pase_estadopase AS pase_estadopase,
        pase.pase_documento_primogenio_id AS primogenio_id,
        pase.pase_usuario_id AS usuario_id,
        pase.pase_usuarionombre AS usuario_nombre,
        sd.doc_pdf_principal AS pdf_principal,
        sd.doc_pdf_principal_estadofirma AS pdf_principal_estadofirma,
        sd.doc_pdf_anexo1 AS pdf_anexo1,
        sd.doc_pdf_anexo1_estadofirma AS pdf_anexo1_estadofirma,
        sd.doc_pdf_anexo2 AS pdf_anexo2,
        sd.doc_pdf_anexo2_estadofirma AS pdf_anexo2_estadofirma,
        sd.doc_codigoseguimiento AS codigoseguimiento,
        adm.adm_nombre AS administrado_nombre,
                        adm.adm_apellidopat AS administrado_apellidopat,
                        adm.adm_apellidomat AS administrado_apellidomat,
                        adm.adm_tipodocumento AS administrado_tipodocumento,
                        adm.adm_numdocumento AS administrado_numdocumento,
                        adm.adm_razonsocial AS administrado_razonsocial
    FROM siga_documento AS sd
    LEFT JOIN siga_buzon AS buzon_origen ON buzon_origen.buzon_id = sd.doc_buzonorigen_id
    LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
    LEFT JOIN siga_tramite AS tram ON tram.tram_id = sd.doc_tramitetupa_id
    LEFT JOIN siga_documento_pase AS pase ON pase.pase_documento_id = sd.doc_iddoc
    LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
    LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                        LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id

	WHERE ((sd.doc_estado = 'Iniciado'  and sd.doc_buzonorigen_id= ?)) or 
          (pase.pase_buzonorigen_id=? and (pase.pase_estadopase='Archivo_porconfirmar' or pase.pase_estadopase='Desarchivo_porconfirmar') )
    ORDER BY pase.pase_fechaenvio DESC";
            $parametros = [ $id_buzonorigen,$id_buzonorigen ];
            
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function listarDocumentos_Obsevados_Oficina($id_buzonorigen) {
        $query = "SELECT * from documento_interno_obtenerdetalles_id()
                 WHERE estado='Observado' AND buzonorigen_id=?";
            $parametros = [ $id_buzonorigen ];
            
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }
    public function listarDocumentos_Obsevados_NombreUsuario($nombre_usuario) {
        $query = "SELECT * from documento_interno_obtenerdetalles_id()
                 WHERE estado='Observado' AND usuario_nombre=?";
            $parametros = [ $nombre_usuario ];
            
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    

    
    public function Listar_ruta_pases($id_documento) {
        $query = "SELECT 
                sd.doc_iddoc AS iddoc,
                buzon_documento.buzon_nombre As origen,
                buzon_documento.buzon_sigla as sigla,
                pase.pase_id as idpase,
                pase.pase_buzonorigen_id as origen_id,
                buzon_pase_origen.buzon_nombre AS origen_nombre,
                pase.pase_buzondestino_id as destino_id,
                buzon_pase_destino.buzon_nombre AS destino_nombre,
                pase.pase_tipopase as tipopase,
                pase.pase_proveido as pase_proveido,
                pase.pase_observacion as observacion, 
                pase.pase_estadopase as estadopase,
                pase.pase_documento_id as documento_id,
                pase.pase_usuario_id as usuario_remitente_id, 
                pase.pase_usuarionombre as usuari_remitente_onombre,
                pase.pase_fechaenvio as pase_fechaenvio,
                pase.pase_fecharecepcion as pase_fecharecepcion,
                pase.pase_documento_primogenio_id as primogenio_id,
                sd.doc_numerodocumento AS numerodocumento,
                sd.doc_numeracion_tipodoc_oficina AS numeracion_tipodoc_oficina,
                sd.doc_procedencia AS procedencia,
                sd.doc_cabecera AS cabecera,
                sd.doc_asunto AS asunto,
                sd.doc_folios AS folios,
                sd.doc_prioridad AS prioridad,
                sd.doc_pdf_principal AS pdf_principal,
                sd.doc_pdf_anexo1 AS pdf_anexo1,
                sd.doc_pdf_anexo2 AS pdf_anexo2,
                sd.doc_anio AS anio,
                sd.doc_mes AS mes,
                sd.doc_proyectar AS proyectar,
                sd.doc_descripcion  AS descripcion,
                sd.doc_tipodocumento_id AS tipodocumento_id,
                sd.doc_referencias_id as referencias_id,
                tip.tipo_nombre AS tipodocumento_nombre,
                adm.adm_nombre AS administrado_nombre,
                adm.adm_apellidopat AS administrado_apellidopat,
                adm.adm_apellidomat AS administrado_apellidomat
            FROM siga_documento_pase as pase
            LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
            LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
            INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
            LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
            LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
            LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
            WHERE sd.doc_iddoc = ? AND sd.doc_estado = 'Enviado'
            ORDER BY pase_fechaenvio asc";

            $parametros = [
                $id_documento
            ];
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de Pases obtenida correctamente');
            } else {
                Response::error('No se encontraron Pases registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    
    public function Listar_trazabilidad($id_primogenia) {
        $query = "SELECT 
                sd.doc_iddoc AS iddoc,
                buzon_documento.buzon_nombre As origen,
                buzon_documento.buzon_sigla as sigla,
                pase.pase_id as idpase,
                pase.pase_buzonorigen_id as origen_id,
                buzon_pase_origen.buzon_nombre AS origen_nombre,
                pase.pase_buzondestino_id as destino_id,
                buzon_pase_destino.buzon_nombre AS destino_nombre,
                pase.pase_tipopase as tipopase,
                pase.pase_proveido as pase_proveido,
                pase.pase_observacion as observacion, 
                pase.pase_estadopase as estadopase,
                pase.pase_documento_id as documento_id,
                pase.pase_usuario_id as usuario_remitente_id, 
                pase.pase_usuarionombre as usuari_remitente_onombre,
                pase.pase_fechaenvio as pase_fechaenvio,
                pase.pase_fecharecepcion as pase_fecharecepcion,
                pase.pase_documento_primogenio_id as primogenio_id,
                sd.doc_numerodocumento AS numerodocumento,
                sd.doc_numeracion_tipodoc_oficina AS numeracion_tipodoc_oficina,
                sd.doc_procedencia AS procedencia,
                sd.doc_cabecera AS cabecera,
                sd.doc_asunto AS asunto,
                sd.doc_folios AS folios,
                sd.doc_prioridad AS prioridad,
                sd.doc_pdf_principal AS pdf_principal,
                sd.doc_pdf_anexo1 AS pdf_anexo1,
                sd.doc_pdf_anexo2 AS pdf_anexo2,
                sd.doc_anio AS anio,
                sd.doc_mes AS mes,
                sd.doc_proyectar AS proyectar,
                sd.doc_descripcion  AS descripcion,
                sd.doc_tipodocumento_id AS tipodocumento_id,
                sd.doc_referencias_id as referencias_id,
                tip.tipo_nombre AS tipodocumento_nombre,
                adm.adm_nombre AS administrado_nombre,
                adm.adm_apellidopat AS administrado_apellidopat,
                adm.adm_apellidomat AS administrado_apellidomat
            FROM siga_documento_pase as pase 
            LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
            LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
            INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
            LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
            LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
            LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
            WHERE pase.pase_documento_primogenio_id = ? AND sd.doc_estado = 'Enviado'
            ORDER BY pase_fechaenvio asc";

            $parametros = [
                $id_primogenia
            ];

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de Pases obtenida correctamente');
            } else {
                Response::error('No se encontraron Pases registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }


    public function Documentos_referenciados($id_primogenia) {
        $query = "SELECT DISTINCT ON (sd.doc_iddoc) 
                    sd.doc_iddoc AS iddoc,
                    buzon_documento.buzon_nombre As origen,
                    buzon_documento.buzon_sigla as sigla,
                    pase.pase_id as idpase,
                    pase.pase_buzonorigen_id as origen_id,
                    buzon_pase_origen.buzon_nombre AS origen_nombre,
                    pase.pase_buzondestino_id as destino_id,
                    buzon_pase_destino.buzon_nombre AS destino_nombre,
                    pase.pase_tipopase as tipopase,
                    pase.pase_proveido as pase_proveido,
                    pase.pase_observacion as observacion, 
                    pase.pase_estadopase as estadopase,
                    pase.pase_documento_id as documento_id,
                    pase.pase_usuario_id as usuario_remitente_id, 
                    pase.pase_usuarionombre as usuari_remitente_onombre,
                    pase.pase_fechaenvio as pase_fechaenvio,
                    pase.pase_fecharecepcion as pase_fecharecepcion,
                    pase.pase_documento_primogenio_id as primogenio_id,
                    sd.doc_numerodocumento AS numerodocumento,
                    sd.doc_numeracion_tipodoc_oficina AS numeracion_tipodoc_oficina,
                    sd.doc_procedencia AS procedencia,
                    sd.doc_cabecera AS cabecera,
                    sd.doc_asunto AS asunto,
                    sd.doc_folios AS folios,
                    sd.doc_prioridad AS prioridad,
                    sd.doc_pdf_principal AS pdf_principal,
                    sd.doc_pdf_anexo1 AS pdf_anexo1,
                    sd.doc_pdf_anexo2 AS pdf_anexo2,
                    sd.doc_fecharegistro AS fecharegistro,
                    sd.doc_anio AS anio,
                    sd.doc_mes AS mes,
                    sd.doc_proyectar AS proyectar,
                    sd.doc_descripcion  AS descripcion,
                    sd.doc_tipodocumento_id AS tipodocumento_id,
                    sd.doc_referencias_id as referencias_id,
                    tip.tipo_nombre AS tipodocumento_nombre,
                    adm.adm_nombre AS administrado_nombre,
                    adm.adm_apellidopat AS administrado_apellidopat,
                    adm.adm_apellidomat AS administrado_apellidomat
            FROM siga_documento_pase as pase
            LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
            LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
            INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
            LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
            LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
            LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
            WHERE pase.pase_documento_primogenio_id = ? AND sd.doc_estado = 'Enviado'
            ORDER BY sd.doc_iddoc, pase.pase_fechaenvio ASC";

            $parametros = [
                $id_primogenia
            ];
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de Pases obtenida correctamente');
            } else {
                Response::error('No se encontraron Pases registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function validarRecaptcha($token) {
        $secretKey = '6LckhAcrAAAAAC_bkzKUB0KyKGUzt2GPyIJUGY0d'; // Reemplaza con tu Secret Key
        $url = 'https://www.google.com/recaptcha/api/siteverify';

        $data = [
            'secret'   => $secretKey,
            'response' => $token
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CAINFO, "C:\inetpub\wwwroot\cert\cacert.pem"); // Ajusta la ruta según tu configuración

        $response = curl_exec($ch);

        if ($response === false) {
            Response::error("Error en cURL: " . curl_error($ch));
            curl_close($ch);
            return false;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Response::error("Error HTTP: $httpCode, Respuesta: $response");
            return false;
        }

        $responseData = json_decode($response);
        if ($responseData === null || !isset($responseData->success)) {
            Response::error("Error al decodificar JSON: $response");
            return false;
        }

        return $responseData->success;
    }

    

    //PARA DOCUMENTOS EMITIDOS.
    
    public function Get_TipoDocumentos_Generados($buzonOrigen_id) {
        $query = "SELECT tipodocumento_nombre as nombre, tipodocumento_id as id, COUNT(tipodocumento_nombre) as nrodocumentos
                         FROM documento_interno_obtenerdetalles_id()
                         WHERE buzonorigen_id=?
                         GROUP BY tipodocumento_nombre, tipodocumento_id";
            $parametros = [
                $buzonOrigen_id
            ];
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de Documentos Cargada Satisfactoriamente');
            } else {
               
                Response::error('Error en la carga de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function Get_ListaDocumentos_Generados_x_tipo($buzonOrigen_id, $tipodoc) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id()
                            WHERE buzonorigen_id=? and tipodocumento_id=?";
            $parametros = [
                $buzonOrigen_id, $tipodoc
            ];
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de Documentos Cargada Satisfactoriamente');
            } else {
                Response::error('Error en la carga de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function Get_estadistico_portipopases($buzonOrigen_id) {
        $query = "SELECT tipodocumento_nombre, COUNT(tipodocumento_nombre) as nrodocumento, 
                    COUNT(CASE WHEN pase_estadopase = 'Iniciado' THEN 1 END) as Iniciado,  
                    COUNT(CASE WHEN estado = 'Enviado' THEN 1 END) as Enviado,
                    COUNT(CASE WHEN pase_estadopase = 'Recibido' THEN 1 END) as Recibido,
                    COUNT(CASE WHEN pase_estadopase = 'Archivo' THEN 1 END) as Archivado,
                    COUNT(CASE WHEN pase_estadopase = 'Tramitado' THEN 1 END) as Tramitado
                    FROM ObtenerPases_documentos()
                    WHERE pase_buzonorigen_id=? 
                    GROUP BY tipodocumento_nombre";
            $parametros = [
                $buzonOrigen_id
            ];
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Lista de Documentos Cargada Satisfactoriamente');
            } else {
                Response::error('Error en la carga de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    // ------------------------------------------
    public function Aceptar_documento_proyectado($doc_id) {

        $query =   "UPDATE siga_documento SET 
                           doc_estado = 'Iniciado'
                    WHERE doc_iddoc = ?";
            $parametros = [
                $doc_id
            ];

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Estado de documento actualizado');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    

    // ------------------------------------------
    public function Enviar_a_proyectado($doc_id) {

        $query =   "UPDATE siga_documento SET 
                           doc_estado = 'Proyectado',
                           doc_proyectar = true
                    WHERE doc_iddoc = ?";
            $parametros = [
                $doc_id
            ];

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Estado de documento actualizado');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }
    // ------------------------------------------
    public function Observar_documento($doc_id, $motivo) {

        $query =   "UPDATE siga_documento SET 
                           doc_estado = 'Observado',
                           doc_descripcion = ?
                    WHERE doc_iddoc = ?";
            $parametros = [
                $motivo,
                $doc_id
            ];

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Estado de documento actualizado');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }


     // ------------------------------------------
     public function Actualizar_archivos($pdf_link,$pdf_tipo,$iddoc) {

            if($pdf_tipo==="pdf_principal"){
                $query =   "UPDATE  siga_documento SET 
                                    doc_pdf_principal = ?,
                                    doc_pdf_principal_estadofirma = 'Firmado',
                                    doc_proyectar = false
                                    -- doc_estado = 'Enviado'
                            WHERE doc_iddoc = ?";

            }else if($pdf_tipo==="pdf_anexo1"){
                $query =   "UPDATE  siga_documento SET 
                                    doc_pdf_anexo1 = ?,
                                    doc_pdf_anexo1_estadofirma = 'Firmado',
                                    doc_proyectar = false
                                    -- doc_estado = 'Enviado'
                            WHERE   doc_iddoc = ?";   

            }else if($pdf_tipo==="pdf_anexo2"){
                $query =   "UPDATE  siga_documento SET 
                                    doc_pdf_anexo2 = ?,
                                    doc_pdf_anexo2_estadofirma = 'Firmado',
                                    doc_proyectar = false
                                    -- doc_estado = 'Enviado'
                            WHERE doc_iddoc = ?";       
            }
            
            $parametros = [
                $pdf_link,
                $iddoc
            ];

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Documentos actualizados');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    // ------------------------------------------
    public function Confirmar_EnviodocumentoFirmado($iddoc) {
        // Validar que $iddoc sea un entero positivo
        if (!is_numeric($iddoc) || $iddoc <= 0) {
            Response::error('ID de documento inválido');
            return;
        }

        try {
            // Consulta SELECT para verificar los estados de los documentos
            $querySelect = "
                SELECT 
                    doc_pdf_principal, 
                    doc_pdf_principal_estadofirma, 
                    doc_pdf_anexo1, 
                    doc_pdf_anexo1_estadofirma, 
                    doc_pdf_anexo2, 
                    doc_pdf_anexo2_estadofirma
                FROM siga_documento
                WHERE doc_iddoc = ?
            ";
            $parametrosSelect = [$iddoc];
            $resultSelect = $this->database->ejecutarConsulta($querySelect, $parametrosSelect);

            // Verificar si se encontró el documento
            if (!$resultSelect || count($resultSelect) === 0) {
                Response::error('Documento no encontrado para el ID proporcionado');
                return;
            }

            // Obtener el primer (y único) registro
            $documento = $resultSelect[0];

            // Lista para almacenar los documentos que faltan firmar
            $faltanFirmar = [];

            // Verificar doc_pdf_principal
            if (!empty($documento['doc_pdf_principal']) && $documento['doc_pdf_principal_estadofirma'] !== 'Firmado') {
                $faltanFirmar[] = 'Documento principal';
            }

            // Verificar doc_pdf_anexo1
            if (!empty($documento['doc_pdf_anexo1']) && $documento['doc_pdf_anexo1_estadofirma'] !== 'Firmado') {
                $faltanFirmar[] = 'Anexo 1';
            }

            // Verificar doc_pdf_anexo2
            if (!empty($documento['doc_pdf_anexo2']) && $documento['doc_pdf_anexo2_estadofirma'] !== 'Firmado') {
                $faltanFirmar[] = 'Anexo 2';
            }

            // Si hay documentos sin firmar, retornar error personalizado
            if (!empty($faltanFirmar)) {
                $mensajeError = 'Falta firmar: ' . implode(', ', $faltanFirmar);
                Response::error($mensajeError);
                return;
            }

            // Si todos los documentos están firmados (o no existen), proceder con el UPDATE
            $queryUpdate = " UPDATE siga_documento
                             SET doc_estado = 'Enviado', doc_proyectar = false
                             WHERE doc_iddoc = ?";
            $parametrosUpdate = [$iddoc];

            $resultUpdate = $this->database->ejecutarConsulta($queryUpdate, $parametrosUpdate);

            // Verificar si se actualizó el registro
            if ($resultUpdate > 0) {
                Response::success($resultUpdate, 'Documentos actualizados correctamente');
            } else {
                Response::error('No se actualizó el documento. Verifique el ID o el estado de los documentos.');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            Response::error("Error inesperado: " . $e->getMessage());
        }
    }

    // ------------------------------------------
    public function Enviar_FirmadoManualmente($iddoc) {

            $query =   "UPDATE  siga_documento SET 
                                doc_pdf_principal_estadofirma = 'Firmado',
                                doc_proyectar = false,
                                doc_estado = 'Enviado'
                        WHERE doc_iddoc = ?";
            
            $parametros = [
                $iddoc
            ];

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Documentos actualizados');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

     // ------------------------------------------
    public function Confirmar_Proveido($iddoc) {

            $query =   "UPDATE  siga_documento SET 
                                doc_estado = 'Enviado'
                        WHERE doc_iddoc = ?";
            
            $parametros = [
                $iddoc
            ];

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Documentos actualizados');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }


    // ------------------------------------------
    public function ConfirmarEnvio_documento_proyectado($doc_id) {

        $query =   "UPDATE siga_documento SET 
                           doc_estado = 'Enviado',
                           doc_proyectar = false
                    WHERE doc_iddoc = ?";
            $parametros = [
                $doc_id
            ];

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Estado de documento actualizado');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }


    function generarTextoAleatorio($longitud = 5) {
        // Definir los caracteres permitidos (sin símbolos HTML o JavaScript)
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+-*@';
        
        // Iniciar el texto aleatorio
        $textoAleatorio = '';
        
        // Generar el texto aleatorio
        for ($i = 0; $i < $longitud; $i++) {
            $textoAleatorio .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        
        return $textoAleatorio;
    }
    

    // Generar PDF de libro de cargos de expedientes
    public function generarLibroCargosPDF($desde = null, $hasta = null) {
        require_once __DIR__ . '/../vendor/autoload.php';
        $dompdf = new \Dompdf\Dompdf();

        // Obtener los datos
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id() 
                  WHERE (pase_estadopase='Enviado' or pase_estadopase='Recibido' or pase_estadopase='Tramitado' or pase_estadopase='Archivado')
                  and procedencia IN ('Externo', 'ExternoCasilla', 'ExternoVirtual')
                  ORDER BY pase_fechaenvio DESC";
        $parametros = [];
        if ($desde !== null && $hasta !== null) {
            $limite = $hasta - $desde + 1;
            $offset = $desde - 1;
            $query .= " LIMIT ? OFFSET ?";
            $parametros = [$limite, $offset];
        }
        $result = $this->database->ejecutarConsulta($query, $parametros);
        // Generar HTML
        $fechaHora = date('d/m/Y h:i A');
        $logo = realpath(__DIR__ . '/../uploads/mdvologo350.png');
        $html = '<style>
            @page { margin: 120px 30px 60px 30px; }
            body { font-family: Arial, sans-serif; font-size: 10px; }
            table { font-family: Arial, sans-serif; border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #000; padding: 4px; text-align: left; }
            th { background: #f0f0f0; }
            .firma { height: 100px; width: 200px;}
            .header { position: fixed; top: -100px; left: 0; right: 0; height: 100px; text-align: center; }
            .footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 40px; text-align: right; font-size: 10px; color: #888; }
        </style>';
        $html .= '<div class="header">
            <table style="width:100%; border:none; margin-bottom:5px;">
                <tr style="border:none;">
                    <td style="width:90px; border:none; text-align:left;">
                        MUNICIPALIDAD DISTRITAL VEINTISEIS DE OCTUBRE
                    </td>
                    <td style="border:none; text-align:center;">
                        <div style="font-size:19px; font-weight:bold;">HOJA DE CARGOS DE EXPEDIENTES</div>
                        <div style="font-size:14px; font-weight:bold;">SISTEMA DE TRAMITE DOCUMENTARIO</div>
                    </td>
                    <td style="width:120px; border:none; text-align:right; font-size:11px;">
                        Fecha impresión:<br><b>' . $fechaHora . '</b>
                    </td>
                </tr>
            </table>
        </div>';
        $html .= '<script type="text/php">
            if (isset($pdf)) {
                $font = $fontMetrics->get_font("Arial", "normal");
                $size = 10;
                $pdf->page_text(500, 25, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size, array(0,0,0));
            }
        </script>';
        $html .= '<table style="margin-top:10px;">';
        $html .= '<tr>
            <th>N°</th>
            <th>DETALLE</th>
            <th>DESTINO</th>
            <th>FECHA<br>HORA</th>
            <th>FIRMA/RECIBIDO POR</th>
        </tr>';
        $i = 1;
        foreach ($result as $row) {
            $html .= '<tr>';
            // N° de orden
            $html .= '<td style="text-align:center;">' . $i . '</td>';
            // Detalle (cabecera, asunto, solicitante, etc.)
            $detalle = '';
            $detalle .= '<div style="font-weight:bold;"> NRO EXP: ' . htmlspecialchars($row['numerodocumento']). ' -' . $row['anio'] . '</div>';
            if ($row['procedencia'] !== 'Interno') {
                
                $detalle .= '<div style="font-weight:bold;">' . htmlspecialchars($row['tipodocumento_nombre']) . ' ' . htmlspecialchars($row['cabecera']) . '</div>';
                if ($row['administrado_tipodocumento'] === 'RUC') {
                    $detalle .= '<div style="font-size:9px;">(SOLICITANTE: ' . htmlspecialchars($row['administrado_razonsocial']) . ')</div>';
                } else {
                    $detalle .= '<div style="font-size:9px;">(Adm: ' . htmlspecialchars($row['administrado_nombre']) . ' ' . htmlspecialchars($row['administrado_apellidopat']) . ' ' . htmlspecialchars($row['administrado_apellidomat']) . ')</div>';
                }
            } else {
                $detalle .= '<div style="font-weight:bold;">' . htmlspecialchars($row['tipodocumento_nombre']) . ' - N° - ' . htmlspecialchars($row['numeracion_tipodoc_oficina']) . ' - ' . htmlspecialchars($row['anio']) . ' - ' . htmlspecialchars($row['origen_sigla']) . ' - MDVO/D</div>';
            }
            $detalle .= '<div style="font-size:11px; text-align:justify;font-size:9px;">' . htmlspecialchars($row['asunto']);
            if ($row['prioridad'] === 'Urgente') {
                $detalle .= ' <span style="color:#b91c1c; font-weight:bold;">[Urgente]</span>';
            }
            if (!empty($row['descripcion'])) {
                $detalle .= '<div style="color:#92400e; font-style:italic; font-size:10px;">' . htmlspecialchars($row['descripcion']) . '</div>';
            }

            $detalle .= '</div>';
            if (!empty($row['pase_proveido'])) {
                $detalle .= '<div style="color:#92400e; font-style:italic; font-size:10px;">' . htmlspecialchars($row['pase_proveido']) . '</div>';
            }
            $html .= '<td>' . $detalle . '</td>';
            // Destino
            $html .= '<td>' . htmlspecialchars($row['pase_buzondestino_nombre']) . '</td>';
            // Fecha/Hora Envío
            $fechaEnvio = '';
            if (!empty($row['pase_fechaenvio'])) {
                $timestamp = strtotime($row['pase_fechaenvio']);
                $fechaEnvio = $timestamp ? date('d/m/Y h:i A', $timestamp) : htmlspecialchars($row['pase_fechaenvio']);
            }
            $html .= '<td>' . $fechaEnvio . '</td>';
            // Firma/Recibido por
            $html .= '<td class="firma"></td>';
            $html .= '</tr>';
            $i++;
        }
        $html .= '</table>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Guardar el PDF en uploads/documentos_tramite/ con nombre único
        $nombreArchivo = 'pdf_' . date('dmY_His') . rand(1000,9999) . '.pdf';
        $rutaCarpeta = realpath(__DIR__ . '/../uploads/documentos_tramite');
        if (!$rutaCarpeta) {
            mkdir(__DIR__ . '/../uploads/documentos_tramite', 0777, true);
            $rutaCarpeta = realpath(__DIR__ . '/../uploads/documentos_tramite');
        }
        $rutaCompleta = $rutaCarpeta . DIRECTORY_SEPARATOR . $nombreArchivo;
        file_put_contents($rutaCompleta, $dompdf->output());

        // Construir la URL de descarga relativa
        $urlDescarga = '/uploads/documentos_tramite/' . $nombreArchivo;

        // Devolver JSON con la URL de descarga
        require_once __DIR__ . '/../utils/response.php';
        Response::success(['url' => $urlDescarga], 'PDF generado correctamente');
        return;
    }

    // ESTADISTICOS 

    // ------------------------------------------
    public function Obtener_stat_documentos($buzon_id) {

        $query =   "WITH Enviados AS (
                        SELECT 
                            buzon_pase_origen.buzon_nombre AS buzon_nombress,
                            buzon_pase_origen.buzon_id AS buzon_id,
                            buzon_pase_origen.buzon_tipo AS buzon_tipo,
                            COUNT(*) FILTER (WHERE sd.doc_estado = 'Enviado' AND pase.pase_estadopase IN ('Enviado', 'Tramitado', 'Iniciado', 'Recibido')) AS Enviados,
                            0 AS Recibidos,
                            0 AS PorRecibir,
                            0 AS Archivado
                        FROM siga_documento_pase AS pase
                        LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                        LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                        INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
                        LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
                        LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
                        LEFT JOIN siga_documento AS sd2 ON sd2.doc_iddoc = pase.pase_documento_primogenio_id
                        LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
                        WHERE buzon_pase_origen.buzon_id = ?
                        GROUP BY buzon_pase_origen.buzon_nombre, buzon_pase_origen.buzon_id, buzon_pase_origen.buzon_tipo
                    ),
                    Recibidos AS (
                        SELECT 
                            buzon_pase_destino.buzon_nombre AS buzon_nombress,
                            buzon_pase_destino.buzon_id AS buzon_id,
                            buzon_pase_destino.buzon_tipo AS buzon_tipo,
                            0 AS Enviados,
                            COUNT(*) FILTER (WHERE sd.doc_estado = 'Enviado' AND pase.pase_estadopase = 'Recibido') AS Recibidos,
                            0 AS PorRecibir,
                            0 AS Archivado
                        FROM siga_documento_pase AS pase
                        LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                        LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                        INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
                        LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
                        LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
                        LEFT JOIN siga_documento AS sd2 ON sd2.doc_iddoc = pase.pase_documento_primogenio_id
                        LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
                        WHERE buzon_pase_destino.buzon_id = ?
                        GROUP BY buzon_pase_destino.buzon_nombre, buzon_pase_destino.buzon_id, buzon_pase_destino.buzon_tipo
                    ),
                    PorRecibir AS (
                        SELECT 
                            buzon_pase_destino.buzon_nombre AS buzon_nombress,
                            buzon_pase_destino.buzon_id AS buzon_id,
                            buzon_pase_destino.buzon_tipo AS buzon_tipo,
                            0 AS Enviados,
                            0 AS Recibidos,
                            COUNT(*) FILTER (WHERE sd.doc_estado = 'Enviado' AND pase.pase_estadopase = 'Enviado') AS PorRecibir,
                            0 AS Archivado
                        FROM siga_documento_pase AS pase
                        LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                        LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                        INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
                        LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
                        LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
                        LEFT JOIN siga_documento AS sd2 ON sd2.doc_iddoc = pase.pase_documento_primogenio_id
                        LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
                        WHERE buzon_pase_destino.buzon_id = ?
                        GROUP BY buzon_pase_destino.buzon_nombre, buzon_pase_destino.buzon_id, buzon_pase_destino.buzon_tipo
                    ),
                    Archivado AS (
                        SELECT 
                            buzon_pase_origen.buzon_nombre AS buzon_nombress,
                            buzon_pase_origen.buzon_id AS buzon_id,
                            buzon_pase_origen.buzon_tipo AS buzon_tipo,
                            0 AS Enviados,
                            0 AS Recibidos,
                            0 AS PorRecibir,
                            COUNT(*) FILTER (WHERE sd.doc_estado = 'Enviado' AND pase.pase_estadopase = 'Archivo' AND buzon_pase_origen.buzon_id = buzon_pase_destino.buzon_id) AS Archivado
                        FROM siga_documento_pase AS pase
                        LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                        LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                        INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
                        LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
                        LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
                        LEFT JOIN siga_documento AS sd2 ON sd2.doc_iddoc = pase.pase_documento_primogenio_id
                        LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
                        WHERE buzon_pase_origen.buzon_id = ?
                        GROUP BY buzon_pase_origen.buzon_nombre, buzon_pase_origen.buzon_id, buzon_pase_origen.buzon_tipo
                    )
                    SELECT 
                        COALESCE(e.buzon_nombress, r.buzon_nombress, p.buzon_nombress, a.buzon_nombress) AS buzon_nombress,
                        COALESCE(e.buzon_id, r.buzon_id, p.buzon_id, a.buzon_id) AS buzon_id,
                        COALESCE(e.buzon_tipo, r.buzon_tipo, p.buzon_tipo, a.buzon_tipo) AS buzon_tipo,
                        COALESCE(SUM(e.Enviados), 0) AS Enviados,
                        COALESCE(SUM(r.Recibidos), 0) AS Recibidos,
                        COALESCE(SUM(p.PorRecibir), 0) AS PorRecibir,
                        COALESCE(SUM(a.Archivado), 0) AS Archivado
                    FROM Enviados e
                    FULL OUTER JOIN Recibidos r ON e.buzon_nombress = r.buzon_nombress AND e.buzon_id = r.buzon_id
                    FULL OUTER JOIN PorRecibir p ON COALESCE(e.buzon_nombress, r.buzon_nombress) = p.buzon_nombress AND COALESCE(e.buzon_id, r.buzon_id) = p.buzon_id
                    FULL OUTER JOIN Archivado a ON COALESCE(e.buzon_nombress, r.buzon_nombress, p.buzon_nombress) = a.buzon_nombress AND COALESCE(e.buzon_id, r.buzon_id, p.buzon_id) = a.buzon_id
                    GROUP BY COALESCE(e.buzon_nombress, r.buzon_nombress, p.buzon_nombress, a.buzon_nombress), 
                            COALESCE(e.buzon_id, r.buzon_id, p.buzon_id, a.buzon_id), 
                            COALESCE(e.buzon_tipo, r.buzon_tipo, p.buzon_tipo, a.buzon_tipo)
                    ORDER BY buzon_tipo DESC, buzon_nombress;";
            $parametros = [
                $buzon_id, 
                $buzon_id,
                $buzon_id,
                $buzon_id
            ];

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Estadísticas de documentos obtenidas correctamente');
            } else {
                Response::error('No se encontraron datos para las estadísticas de documentos');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }


    // ------------------------------------------
    public function Obtener_stat_documentos_todos() {

        $query =   "WITH Enviados AS (
                SELECT 
                    buzon_pase_origen.buzon_nombre AS buzon_nombres,
                    buzon_pase_origen.buzon_id AS buzon_id,
                    buzon_pase_origen.buzon_tipo AS buzon_tipo,
                    COUNT(*) FILTER (WHERE sd.doc_estado = 'Enviado' AND pase.pase_estadopase IN ('Enviado', 'Tramitado', 'Iniciado', 'Recibido')) AS Enviados,
                    0 AS Recibidos,
                    0 AS PorRecibir,
                    0 AS Archivado
                FROM siga_documento_pase AS pase
                LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
                LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
                LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
                LEFT JOIN siga_documento AS sd2 ON sd2.doc_iddoc = pase.pase_documento_primogenio_id
                LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
                GROUP BY buzon_pase_origen.buzon_nombre, buzon_pase_origen.buzon_id, buzon_pase_origen.buzon_tipo
            ),
            Recibidos AS (
                SELECT 
                    buzon_pase_destino.buzon_nombre AS buzon_nombres,
                    buzon_pase_destino.buzon_id AS buzon_id,
                    buzon_pase_destino.buzon_tipo AS buzon_tipo,
                    0 AS Enviados,
                    COUNT(*) FILTER (WHERE sd.doc_estado = 'Enviado' AND pase.pase_estadopase = 'Recibido') AS Recibidos,
                    0 AS PorRecibir,
                    0 AS Archivado
                FROM siga_documento_pase AS pase
                LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
                LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
                LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
                LEFT JOIN siga_documento AS sd2 ON sd2.doc_iddoc = pase.pase_documento_primogenio_id
                LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
                GROUP BY buzon_pase_destino.buzon_nombre, buzon_pase_destino.buzon_id, buzon_pase_origen.buzon_tipo
            ),
            PorRecibir AS (
                SELECT 
                    buzon_pase_destino.buzon_nombre AS buzon_nombres,
                    buzon_pase_destino.buzon_id AS buzon_id,
                    buzon_pase_destino.buzon_tipo AS buzon_tipo,
                    0 AS Enviados,
                    0 AS Recibidos,
                    COUNT(*) FILTER (WHERE sd.doc_estado = 'Enviado' AND pase.pase_estadopase = 'Enviado') AS PorRecibir,
                    0 AS Archivado
                FROM siga_documento_pase AS pase
                LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
                LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
                LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
                LEFT JOIN siga_documento AS sd2 ON sd2.doc_iddoc = pase.pase_documento_primogenio_id
                LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
                GROUP BY buzon_pase_destino.buzon_nombre, buzon_pase_destino.buzon_id, buzon_pase_origen.buzon_tipo
            ),
            Archivado AS (
                SELECT 
                    buzon_pase_origen.buzon_nombre AS buzon_nombres,
                    buzon_pase_origen.buzon_id AS buzon_id,
                    buzon_pase_origen.buzon_tipo AS buzon_tipo,
                    0 AS Enviados,
                    0 AS Recibidos,
                    0 AS PorRecibir,
                    COUNT(*) FILTER (WHERE sd.doc_estado = 'Enviado' AND pase.pase_estadopase = 'Archivo' AND buzon_pase_origen.buzon_id = buzon_pase_destino.buzon_id) AS Archivado
                FROM siga_documento_pase AS pase
                LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                INNER JOIN siga_documento sd ON sd.doc_iddoc = pase.pase_documento_id
                LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
                LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
                LEFT JOIN siga_documento AS sd2 ON sd2.doc_iddoc = pase.pase_documento_primogenio_id
                LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
                GROUP BY buzon_pase_origen.buzon_nombre, buzon_pase_origen.buzon_id, buzon_pase_origen.buzon_tipo
            )
            SELECT 
                COALESCE(e.buzon_nombres, r.buzon_nombres, p.buzon_nombres, a.buzon_nombres) AS buzon_nombres,
                COALESCE(e.buzon_id, r.buzon_id, p.buzon_id, a.buzon_id) AS buzon_id,
                COALESCE(e.buzon_tipo, r.buzon_tipo, p.buzon_tipo, a.buzon_tipo) AS buzon_tipo,
                COALESCE(SUM(e.Enviados), 0) AS Enviados,
                COALESCE(SUM(r.Recibidos), 0) AS Recibidos,
                COALESCE(SUM(p.PorRecibir), 0) AS PorRecibir,
                COALESCE(SUM(a.Archivado), 0) AS Archivado
            FROM Enviados e
            FULL OUTER JOIN Recibidos r ON e.buzon_nombres = r.buzon_nombres AND e.buzon_id = r.buzon_id
            FULL OUTER JOIN PorRecibir p ON COALESCE(e.buzon_nombres, r.buzon_nombres) = p.buzon_nombres AND COALESCE(e.buzon_id, r.buzon_id) = p.buzon_id
            FULL OUTER JOIN Archivado a ON COALESCE(e.buzon_nombres, r.buzon_nombres, p.buzon_nombres) = a.buzon_nombres AND COALESCE(e.buzon_id, r.buzon_id, p.buzon_id) = a.buzon_id

            GROUP BY COALESCE(e.buzon_nombres, r.buzon_nombres, p.buzon_nombres, a.buzon_nombres), COALESCE(e.buzon_id, r.buzon_id, p.buzon_id, a.buzon_id), COALESCE(e.buzon_tipo, r.buzon_tipo, p.buzon_tipo, a.buzon_tipo)
            ORDER BY buzon_tipo desc, buzon_nombres
            ";
        try {
            $result = $this->database->ejecutarConsulta($query);
            if ($result) {
                Response::success($result, 'Estadísticas de documentos obtenidas correctamente');
            } else {
                Response::error('No se encontraron datos para las estadísticas de documentos');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }
}
