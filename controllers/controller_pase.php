<?php
include_once './utils/response.php';
include_once './config/database.php';

class PaseController {
    private $database;

    public function __construct() {
        $this->database = new Database();
    }

    // ------------------------------------------------------
    public function insertPase(
        $pase_documento_id,
        $pase_buzonorigen_id,
        $pase_buzondestino_id,
        $pase_tipopase,
        $pase_proveido,
        $pase_observacion,
        $pase_estadopase,
        $pase_usuario_id,
        $pase_usuarionombre,
        $pase_id_previo,
        $pase_tipoaccion,
        $pase_primogenio   
    ) {

        $fecha_actual = (new DateTime())->format('Y-m-d H:i:s');
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
            $result = $this->database->ejecutarConsulta($query, [
                $pase_documento_id,
                $pase_buzonorigen_id,
                $pase_buzondestino_id,
                $pase_tipopase,
                $pase_proveido,
                $pase_observacion,
                $pase_estadopase,
                $pase_usuario_id,
                $pase_usuarionombre,
                $pase_tipoaccion,
                $pase_primogenio,
                $pase_id_previo
            ]); // Secuencia en PostgreSQL
    
            if ($result) {
                //  --------------------------------------------------------------------------------
                // Actualizar ----------------------------------------------------------------------

                $query = "UPDATE siga_documento_pase SET 
                            pase_estadopase = 'Tramitado'
                        WHERE pase_id = ?";
                try {
                    $result = $this->database->ejecutarActualizacion($query, [
                        $pase_id_previo
                    ]);
                    if ($result) {
                        response::success($result, 'Pase insertado correctamente');
                    } else {
                        response::error('Error al actualizar el pase');
                    }
                } catch (PDOException $e) {
                    response::error('Error al actualizar el pase: ' . $e->getMessage());
                }
                //  ----------------------------------------------------------------------------------
                //  ----------------------------------------------------------------------------------


            } else {
                response::error('Error al insertar el pase');
            }
        } catch (PDOException $e) {
            response::error('Error al insertar el pase: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function getPase($pase_id) {
        $query =  " SELECT 
                        p.pase_id,
                        p.pase_fechahoraregistro,
                        p.pase_tipo,
                        p.pase_usuarionombre,
                        p.pase_proveido,
                        p.pase_descripcion,
                        p.pase_estado,
                        p.pase_buzonorigen_id,
                        p.pase_buzondestino_id,
                        p.pase_idprevio
                    FROM 
                        siga_pase p
                    JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
                    JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
                    JOIN siga_documento d ON p.pase_documento_id = d.doc_iddoc
                    WHERE p.pase_id = ?";

        try {
            $result = $this->database->ejecutarConsulta($query, [$pase_id]);

            if ($result) {
                response::success($result[0], 'Consulta de pase exitosa');
            } else {
                response::error('No se encontró el pase');
            }
        } catch (PDOException $e) {
            response::error('Error al consultar el pase: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function getPases() {
        $query = "SELECT 
                    p.pase_id,
                    p.pase_fechahoraregistro,
                    p.pase_tipo,
                    p.pase_usuarionombre,
                    p.pase_proveido,
                    p.pase_descripcion,
                    p.pase_estado,
                    o_origen.ofi_nombre AS oficina_origen,
                    o_destino.ofi_nombre AS oficina_destino,
                    d.doc_codigo AS documento_codigo
                FROM 
                    siga_pase p
                JOIN siga_oficina o_origen ON p.pase_oficina_origen = o_origen.ofi_id
                JOIN siga_oficina o_destino ON p.pase_oficina_destino = o_destino.ofi_id
                JOIN siga_documento d ON p.pase_documento_id = d.doc_iddoc";

        try {
            $result = $this->database->ejecutarConsulta($query);

            if ($result) {
                response::success($result, 'Lista de pases obtenida correctamente');
            } else {
                response::error('No se encontraron pases registrados');
            }
        } catch (PDOException $e) {
            response::error('Error al consultar los pases: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    // public function updatePase(
    //     $pase_id,
    //     $pase_oficina_origen,
    //     $pase_oficina_destino,
    //     $pase_tipo,
    //     $pase_usuario_id,
    //     $pase_usuarionombre,
    //     $pase_proveido,
    //     $pase_descripcion,
    //     $pase_estado,
    //     $pase_documento_id
    // ) {
    //     $query = "UPDATE siga_pase SET 
    //                 pase_oficina_origen = ?,
    //                 pase_oficina_destino = ?,
    //                 pase_tipo = ?,
    //                 pase_usuario_id = ?,
    //                 pase_usuarionombre = ?,
    //                 pase_proveido = ?,
    //                 pase_descripcion = ?,
    //                 pase_estado = ?,
    //                 pase_documento_id = ?
    //             WHERE pase_id = ?";

    //     try {
    //         $result = $this->database->ejecutarActualizacion($query, [
    //             $pase_oficina_origen,
    //             $pase_oficina_destino,
    //             $pase_tipo,
    //             $pase_usuario_id,
    //             $pase_usuarionombre,
    //             $pase_proveido,
    //             $pase_descripcion,
    //             $pase_estado,
    //             $pase_documento_id,
    //             $pase_id
    //         ]);

    //         if ($result) {
    //             response::success($result, 'Pase actualizado correctamente');
    //         } else {
    //             response::error('Error al actualizar el pase');
    //         }
    //     } catch (PDOException $e) {
    //         response::error('Error al actualizar el pase: ' . $e->getMessage());
    //     }
    // }

    
    // ------------------------------------------------------
    
    public function updPase_recepcionar(
        $pase_id
    ) {
        $query = "SELECT pase_actualizar_estado_recibir(?, ?)";
        $pase_estado = 'Recibido';
    
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $pase_id,
                $pase_estado
            ]);
    
            $respuesta = $result[0]['pase_actualizar_estado_recibir'];
    
            switch ($respuesta) {
                case -1:
                    response::error('Error: El pase no existe.');
                    break;
                case -2:
                    response::error('Error inesperado al actualizar el pase.');
                    break;
                default:
                    response::success($respuesta, 'Pase actualizado correctamente');
                    break;
            }
        } catch (PDOException $e) {
            response::error('Error de base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            response::error('Error inesperado: ' . $e->getMessage());
        }
    }
    

    // ------------------------------------------------------
    public function Update_estado_pase_mesapartes(
        $pase_id,$pase_estado
    ) {
        $query = "SELECT pase_actualizar_estado_enviado(?, ?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $pase_id,
                $pase_estado
            ]);
    
            $respuesta = $result[0]['pase_actualizar_estado_enviado'];
    
            switch ($respuesta) {
                case -1:
                    response::error('Error: El pase no existe.');
                    break;
                case -2:
                    response::error('Error inesperado al actualizar el pase.');
                    break;
                default:
                    response::success($respuesta, 'Pase actualizado correctamente');
                    break;
            }
        } catch (PDOException $e) {
            response::error('Error de base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            response::error('Error inesperado: ' . $e->getMessage());
        }
    }


    // ------------------------------------------------------
    public function Update_estado_expediente_proyectado(
        $pase_id,$pase_estado
    ) {
        $query = "SELECT pase_actualizar_estado_enviado(?, ?)";
        // --------------------------
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $pase_id,
                $pase_estado
            ]);
            $respuesta = $result[0]['pase_actualizar_estado_enviado'];
    
            switch ($respuesta) {
                case -1:
                    response::error('Error: El pase no existe.');
                    break;
                case -2:
                    response::error('Error inesperado al actualizar el pase.');
                    break;
                default:
                    response::success($respuesta, 'Pase actualizado correctamente');
                    break;
            }
        } catch (PDOException $e) {
            response::error('Error de base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            response::error('Error inesperado: ' . $e->getMessage());
        }
    }
    

    // ------------------------------------------------------
    public function deletePase($pase_id) {
        $query = "DELETE FROM siga_documento_pase WHERE pase_id = ?";

        try {
            $result = $this->database->ejecutarActualizacion($query, [$pase_id]);

            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            response::error('Error al eliminar el pase: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function getRelacionPaseOficinaUsuario($pase_id) {
        // Traer pase con información de la oficina origen, destino y usuario.
        $query = "SELECT 
                    p.pase_id,
                    p.pase_fechahoraregistro,
                    o_origen.ofi_nombre AS oficina_origen,
                    o_destino.ofi_nombre AS oficina_destino,
                    u.tra_nombre AS usuario_nombre
                FROM 
                    siga_pase p
                JOIN siga_oficina o_origen ON p.pase_oficina_origen = o_origen.ofi_id
                JOIN siga_oficina o_destino ON p.pase_oficina_destino = o_destino.ofi_id
                JOIN siga_trabajador u ON p.pase_usuario_id = u.tra_id
                WHERE p.pase_id = ?";

        try {
            $result = $this->database->ejecutarConsulta($query, [$pase_id]);

            if ($result) {
                response::success($result[0], 'Relación de pase con oficina y usuario obtenida correctamente');
            } else {
                response::error('No se encontró la relación de pase');
            }
        } catch (PDOException $e) {
            response::error('Error al consultar la relación de pase: ' . $e->getMessage());
        }
    }

    // ------------------------------------------
     // ------------------------------------------
    public function archivar_pase_a_despacho($idpase) {
        // Actualizar el estado del pase a 'Recibido' en la tabla siga_pase_documento
            $query ="UPDATE siga_documento_pase SET 
                           pase_estadopase = 'archivo_despacho'
                     WHERE pase_id = ?";
            $parametros = [
                $idpase
            ];
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Se solicitó archivo para aprobación de despacho correctamente');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function desarchivar_pase_a_despacho($idpase) {
        $query =   "UPDATE siga_documento_pase SET 
                           pase_estadopase = 'Desarchivo_despacho'
                    WHERE pase_id = ?";
            $parametros = [
                $idpase
            ];
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Se solicitó Desarchivo para aprobación de despacho correctamente');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    // ------------------------------------------
    public function desarchivar($idpase) {
        // Actualizar el estado del pase a 'Recibido' en la tabla siga_pase_documento
        $query =   "UPDATE siga_documento_pase SET 
                           pase_estadopase = 'Recibido'
                    WHERE pase_id = (Select pase_idprevio 
                                     From siga_documento_pase 
                                     Where pase_id = ?)";
            $parametros = [
                $idpase
            ];

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                   $result_delet =  $this->deletePase($idpase);
                   if($result_delet) {
                       Response::success($result, 'Pase desarchivado correctamente');
                   } else {
                       Response::error('Error al eliminar el pase desarchivado');
                   }
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
       
    }
    // ------------------------------------------
    public function upd_archivar_cambiaestado($idpase,$estado) {
        // Actualizar el estado del pase a 'Recibido' en la tabla siga_pase_documento
        $query =   "UPDATE siga_documento_pase SET 
                           pase_estadopase = ?
                    WHERE pase_id = ?";
            $parametros = [
                $estado,
                $idpase
            ];
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Pase desarchivado correctamente');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
       
    }
}
?>
