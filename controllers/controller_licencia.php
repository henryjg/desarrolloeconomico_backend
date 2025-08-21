<?php
include_once './utils/response.php';
include_once './config/database.php';
require_once './vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use Dompdf\Dompdf;
use Dompdf\Options;

class LicenciaController {
    private $database;

    public function __construct() {
        $this->database = new Database();
    }


    public function insertar_nuevalicencia(
        $tipotramite_tupa,
        $negocio_ruc,
        $negocio_razonsocial,
        $negocio_direccionfiscal,
        $negocio_nombrecomercial,
        $negocio_actividadcomercial,
        $negocio_condicionlocal,
        $representantelegal_dni,
        $representantelegal_nombre,
        $negocio_area,
        $negocio_aforo,
        $negocio_horario,
        $pago_monto,
        $pago_codoperacion,
        $pago_pagovoucher_url,
        $dir_direccioncomercial,
        $dir_numero,
        $dir_letra,
        $dir_inter,
        $dir_mz,
        $dir_lote,
        $dir_dpto,
        $dir_referencia,
        $itse_tipoinspeccion,
        $itse_resultado,
        $itse_riesgo,
        $itse_observacion,
        $procedencia_solicitud,
        $usuarioid,
        $usuarionombre,
        $ubigeoid,
        $estadotramite,
        $documento_numexpediente,
        $documento_id,
        $fechaingreso,
        $epoca
    ) {
        $query = "SELECT licencia_insertar_campos_requeridos(
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                  ) AS new_licencia_id";
    
        try {
            // Verifica que los parámetros existan antes de hacer la consulta
            if (!isset($tipotramite_tupa, $negocio_ruc, $negocio_razonsocial, $negocio_direccionfiscal, $negocio_nombrecomercial, 
                       $negocio_actividadcomercial, $negocio_condicionlocal, $representantelegal_dni, $representantelegal_nombre, 
                       $negocio_area, $negocio_aforo, $negocio_horario, $pago_monto, $pago_codoperacion, $pago_pagovoucher_url, 
                       $dir_direccioncomercial, $dir_numero, $dir_letra, $dir_inter, $dir_mz, $dir_lote, $dir_dpto, 
                       $dir_referencia, $itse_tipoinspeccion, $itse_resultado, $itse_riesgo, $itse_observacion, 
                       $procedencia_solicitud, $usuarioid, $usuarionombre, $ubigeoid, $estadotramite, $documento_numexpediente, $documento_id, $fechaingreso, $epoca)) {
                throw new Exception('Uno o más parámetros no están definidos.');
            }
    
            // Ejecutar la consulta con todos los parámetros
            $result = $this->database->insertar($query, [
                $tipotramite_tupa,
                $negocio_ruc,
                $negocio_razonsocial,
                $negocio_direccionfiscal,
                $negocio_nombrecomercial,
                $negocio_actividadcomercial,
                $negocio_condicionlocal,
                $representantelegal_dni,
                $representantelegal_nombre,
                $negocio_area,
                $negocio_aforo,
                $negocio_horario,
                $pago_monto,
                $pago_codoperacion,
                $pago_pagovoucher_url,
                $dir_direccioncomercial,
                $dir_numero,
                $dir_letra,
                $dir_inter,
                $dir_mz,
                $dir_lote,
                $dir_dpto,
                $dir_referencia,
                $itse_tipoinspeccion,
                $itse_resultado,
                $itse_riesgo,
                $itse_observacion,
                $procedencia_solicitud,
                $usuarioid,
                $usuarionombre,
                $ubigeoid,
                $estadotramite,
                $documento_numexpediente,
                $documento_id,
                $fechaingreso,
                $epoca
            ]);
            
            // Manejar el resultado de la función
            if ($result > 1) {
                $newLicenciaId = $result;
                response::success($newLicenciaId, 'Nuevo registro de licencia insertado correctamente');
            } elseif ($result == -1) {
                Response::error('Error: El código ya existe (violación de clave UNIQUE).');
            } elseif ($result == -2) {
                Response::error('Error: Campos requeridos no proporcionados (violación de NOT NULL).');
            } elseif ($result == -4) {
                Response::error('Error: El documento_id proporcionado no existe (violación de clave foránea).');
            } elseif ($result == -5) {
                Response::error('Error: El ubigeoid proporcionado no existe (violación de clave foránea).');
            } else {
                response::error('Error al insertar el nuevo registro de licencia. Código de error: ' . $result);
            }
    
        } catch (Exception $e) {
            response::error('Error al insertar el nuevo registro de licencia: ' . $e->getMessage());
        }
    }
    
    
    


    // ------------------------------------------------------
    public function getLicencia($licencia_idlic) {
        $query = "SELECT * FROM licencia_obtenerdatos(?)";
        $result = $this->database->ejecutarConsulta($query, [$licencia_idlic]);
    
        if ($result) {
            // Decodifica cualquier entidad HTML en los datos obtenidos
            $decodedResult = array_map(function($value) {
                return is_string($value) ? htmlspecialchars_decode($value, ENT_QUOTES) : $value;
            }, $result[0]);
    
            response::success($decodedResult, 'Consulta de licencia exitosa');
        } else {
            response::error('No se encontró la licencia');
        }
    }
    

    // ------------------------------------------------------
    public function getLicencias() {
        $query = "SELECT * FROM licencia_listartabla()";
        $result = $this->database->ejecutarConsulta($query);
    
        if ($result) {
            // Decodificar las entidades HTML en los resultados
            $decodedResult = array_map(function($row) {
                return array_map(function($value) {
                    return is_string($value) ? htmlspecialchars_decode($value, ENT_QUOTES) : $value;
                }, $row);
            }, $result);
    
            response::success($decodedResult, 'Lista de licencias obtenida correctamente');
        } else {
            response::error('No se encontraron licencias registradas');
        }
    }
    

    //


    public function getLicencias_filtros_bajanula($mes = null, $anio = null, $fechainicio = null, $fechafin = null) {
        $query = "SELECT * FROM licencia_listar_bajanula_filtro_full(?, ?, ?, ?) ORDER BY fecharegistro desc";
        $filters = [
            $mes ?: null,
            $anio ?: null,
            $fechainicio ?: null,
            $fechafin ?: null
        ];
    
        try {
            $result = $this->database->ejecutarConsulta($query, $filters);
    
            if ($result) {
                // Decodificar las entidades HTML en los resultados
                $decodedResult = array_map(function($row) {
                    return array_map(function($value) {
                        return is_string($value) ? htmlspecialchars_decode($value, ENT_QUOTES) : $value;
                    }, $row);
                }, $result);
    
                response::success($decodedResult, 'Lista de licencias obtenida correctamente');
            } else {
                response::error('No se encontraron licencias registradas');
            }
        } catch (PDOException $e) {
            response::error('Error al obtener la lista de licencias: ' . $e->getMessage());
        }
    }
    
    


    public function getLicencias_filtros_validado_full($estado = null, $mes = null, $anio = null, $fechainicio = null, $fechafin = null) {
        $query = "SELECT * FROM licencia_listartabla_filtro_full(?, ?, ?, ?, ?) ORDER BY fecharegistro desc";
        
        // $params = [$estado, $mes, $anio, $fechainicio, $fechafin];
        
        $filters = [];
        
        if (!is_null($estado) && $estado !== '') {
            $filters[] = $estado;
        } else {
            $filters[] = null;
        }
    
        if (!is_null($mes) && $mes !== '') {
            $filters[] = $mes;
        } else {
            $filters[] = null;
        }
    
        if (!is_null($anio) && $anio !== '') {
            $filters[] = $anio;
        } else {
            $filters[] = null;
        }
    
        if (!is_null($fechainicio) && $fechainicio !== '') {
            $filters[] = $fechainicio;
        } else {
            $filters[] = null;
        }
    
        if (!is_null($fechafin) && $fechafin !== '') {
            $filters[] = $fechafin;
        } else {
            $filters[] = null;
        }
    
        try {
            // Ejecuta la consulta con los parámetros
            $result = $this->database->ejecutarConsulta($query, $filters);
    
            if ($result) {
                // Decodificar las entidades HTML en los resultados
                $decodedResult = array_map(function($row) {
                    return array_map(function($value) {
                        return is_string($value) ? htmlspecialchars_decode($value, ENT_QUOTES) : $value;
                    }, $row);
                }, $result);
    
                response::success($decodedResult, 'Lista de licencias obtenida correctamente');
            } else {
                response::error('No se encontraron licencias registradas');
            }
        } catch (PDOException $e) {
            response::error('Error al obtener la lista de licencias: ' . $e->getMessage());
        }
    }

    public function getLicencias_filtros_licenciasEmitidas($estado = null, $mes = null, $anio = null, $fechainicio = null, $fechafin = null, $categoria = null) {
        $query = "SELECT * FROM licencia_listartabla_filtro_full_emision(?, ?, ?, ?, ?, ?)
                         ORDER BY certificado_numerosequencia desc";
        
        // $params = [$estado, $mes, $anio, $fechainicio, $fechafin];
        
        $filters = [];
        
        if (!is_null($estado) && $estado !== '') {
            $filters[] = $estado;
        } else {
            $filters[] = null;
        }
    
        if (!is_null($mes) && $mes !== '') {
            $filters[] = $mes;
        } else {
            $filters[] = null;
        }
    
        if (!is_null($anio) && $anio !== '') {
            $filters[] = $anio;
        } else {
            $filters[] = null;
        }
    
        if (!is_null($fechainicio) && $fechainicio !== '') {
            $filters[] = $fechainicio;
        } else {
            $filters[] = null;
        }
    
        if (!is_null($fechafin) && $fechafin !== '') {
            $filters[] = $fechafin;
        } else {
            $filters[] = null;
        }
        
        if (!is_null($categoria) && $categoria !== '') {
            $filters[] = $categoria;
        } else {
            $filters[] = null;
        }
    
        try {
            // Ejecuta la consulta con los parámetros
            $result = $this->database->ejecutarConsulta($query, $filters);
    
            if ($result) {
                // Decodificar las entidades HTML en los resultados
                $decodedResult = array_map(function($row) {
                    return array_map(function($value) {
                        return is_string($value) ? htmlspecialchars_decode($value, ENT_QUOTES) : $value;
                    }, $row);
                }, $result);
    
                response::success($decodedResult, 'Lista de licencias obtenida correctamente');
            } else {
                response::error('No se encontraron licencias registradas');
            }
        } catch (PDOException $e) {
            response::error('Error al obtener la lista de licencias: ' . $e->getMessage());
        }
    }
    
    

    // ------------------------------------------------------
    public function updateLicencia(
        $idlic,
        $tipotramite_tupa,
        $negocio_ruc,
        $negocio_razonsocial,
        $negocio_direccionfiscal,
        $negocio_nombrecomercial,
        $negocio_actividadcomercial,
        $negocio_condicionlocal,
        $representantelegal_dni,
        $representantelegal_nombre,
        $negocio_area,
        $negocio_aforo,
        $negocio_horario,
        $pago_monto,
        $pago_codoperacion,
        $dir_direccioncomercial,
        $dir_numero,
        $dir_letra,
        $dir_inter,
        $dir_mz,
        $dir_lote,
        $dir_dpto,
        $dir_referencia,
        $itse_tipoinspeccion,
        $itse_resultado,
        $itse_riesgo,
        $itse_observacion,
        $documento_codexpediente
    ) {
        $query = "SELECT licencia_actualizar(
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,  ?, ?, ?, ?, ?, ?, ?, ?
        ) AS success";
    
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $idlic,
                $tipotramite_tupa,
                $negocio_ruc,
                $negocio_razonsocial,
                $negocio_direccionfiscal,
                $negocio_nombrecomercial,
                $negocio_actividadcomercial,
                $negocio_condicionlocal,
                $representantelegal_dni,
                $representantelegal_nombre,
                $negocio_area,
                $negocio_aforo,
                $negocio_horario,
                $pago_monto,
                $pago_codoperacion,
                $dir_direccioncomercial,
                $dir_numero,
                $dir_letra,
                $dir_inter,
                $dir_mz,
                $dir_lote,
                $dir_dpto,
                $dir_referencia,
                $itse_tipoinspeccion,
                $itse_resultado,
                $itse_riesgo,
                $itse_observacion,
                $documento_codexpediente
            ]);
    
            // Verificar si la actualización fue exitosa en base al valor booleano retornado
            if ($result && isset($result[0]['success']) && $result[0]['success'] === true) {
                response::success($result[0]['success'], 'Licencia actualizada correctamente');
            } else {
                response::error('No se encontró la licencia o no se realizaron cambios');
            }
        } catch (PDOException $e) {
            response::error('Error al actualizar la licencia: ' . $e->getMessage());
        }
    }
    


    // ------------------------------------------------------



    // ----------------------------------------------------
    public function aceptarLicenciaSolicitud($idlic) {
        $query = "SELECT licencia_aceptar_solicitud(?) AS success";
    
        try {
            $result = $this->database->ejecutarConsulta($query, [$idlic]);
    
            if ($result && isset($result[0]['success']) && $result[0]['success'] === true) {
                response::success(null, 'Solicitud de licencia aceptada y en proceso');
            } else {
                response::error('No se encontró la licencia o no se realizaron cambios');
            }
        } catch (PDOException $e) {
            response::error('Error al aceptar la solicitud de licencia: ' . $e->getMessage());
        }
    }
    
    // ----------------------------------------------------
    public function rechazarLicenciaSolicitud($idlic, $vigencia_observacion) {
        $query = "SELECT licencia_rechazar_solicitud(?, ?) AS success";
    
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $idlic,
                $vigencia_observacion
            ]);
    
            if ($result && isset($result[0]['success']) && $result[0]['success'] === true) {
                response::success(null, 'Solicitud de licencia rechazada correctamente');
            } else {
                response::error('No se encontró la licencia o no se realizaron cambios');
            }
        } catch (PDOException $e) {
            response::error('Error al rechazar la solicitud de licencia: ' . $e->getMessage());
        }
    }
    public function DarBajaLicencia($idlic, $estado, $vigencia_observacion) {
        $query = "SELECT licencia_cambiar_estadolicencia(?, ?, ?) AS success";
    
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $idlic,
                $estado,
                $vigencia_observacion
            ]);
    
            if ($result && isset($result[0]['success']) && $result[0]['success'] === true) {
                response::success(null, 'Solicitud de licencia rechazada correctamente');
            } else {
                response::error('No se encontró la licencia o no se realizaron cambios');
            }
        } catch (PDOException $e) {
            response::error('Error al rechazar la solicitud de licencia: ' . $e->getMessage());
        }
    }

    // ----------------------------------------------------
    public function cambiarEstadoLicencia($idlic, $vigencia_estado, $vigencia_observacion) {
        $query = "SELECT licencia_cambiar_estadolicencia(?, ?, ?) AS success";
    
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $idlic,
                $vigencia_estado,
                $vigencia_observacion
            ]);
    
            if ($result && isset($result[0]['success']) && $result[0]['success'] === true) {
                response::success(null, 'Estado de licencia actualizado correctamente');
            } else {
                response::error('No se encontró la licencia o no se realizaron cambios');
            }
        } catch (PDOException $e) {
            response::error('Error al cambiar el estado de la licencia: ' . $e->getMessage());
        }
    }

    // ----------------------------------------------------
    public function guardar_zonificacion($idlic, $zonificacion) {
        $query = "SELECT licencia_guardar_zonificacion(?, ?) AS success";

        try {
            $result = $this->database->ejecutarConsulta($query, [
                $idlic,
                $zonificacion
            ]);

            if ($result && isset($result[0]['success']) && $result[0]['success'] === true) {
                response::success(null, 'Se Guardó correctamente');
            } else {
                response::error('No se encontró la licencia o no se realizaron cambios');
            }
        } catch (PDOException $e) {
            response::error('Error al cambiar el estado de la licencia: ' . $e->getMessage());
        }
    }
    
    // ----------------------------------------------------
    public function guardar_fechavencimiento($idlic, $fecha) {
        $query = "SELECT licencia_guardar_itsefechavencimiento(?, ?) AS success";

        try {
            $result = $this->database->ejecutarConsulta($query, [
                $idlic,
                $fecha
            ]);

            if ($result && isset($result[0]['success']) && $result[0]['success'] === true) {
                response::success(null, 'Se Guardó correctamente');
            } else {
                response::error('No se encontró la licencia o no se realizaron cambios');
            }
        } catch (PDOException $e) {
            response::error('Error al cambiar el estado de la licencia: ' . $e->getMessage());
        }
    }
    
    // ----------------------------------------------------
    public function guardar_Vigencia($idlic, $vigencia) {
        $query = "SELECT licencia_guardar_vigencia(?, ?) AS success";

        try {
            $result = $this->database->ejecutarConsulta($query, [
                $idlic,
                $vigencia
            ]);

            if ($result && isset($result[0]['success']) && $result[0]['success'] === true) {
                response::success(null, 'Se Guardó correctamente');
            } else {
                response::error('No se encontró la licencia o no se realizaron cambios');
            }
        } catch (PDOException $e) {
            response::error('Error al cambiar el estado de la licencia: ' . $e->getMessage());
        }
    }
    // ------------------------------------------------------
    public function deleteLicencia($idlic) {
        $query = "SELECT licencia_eliminar(?)";
    
        try {
            $result = $this->database->ejecutarConsulta($query, [$idlic]);
    
            if ($result && $result[0]['licencia_eliminar'] == 1) {
                response::success(null, 'Licencia eliminada correctamente');
            } elseif ($result[0]['licencia_eliminar'] == -1) {
                response::error('Error: No se puede eliminar la licencia debido a una violación de clave foránea.');
            } elseif ($result[0]['licencia_eliminar'] == -2) {
                response::error('Error inesperado al eliminar la licencia.');
            } else {
                response::error('Error desconocido al eliminar la licencia.');
            }
        } catch (PDOException $e) {
            response::error('Error al eliminar la licencia: ' . $e->getMessage());
        }
    }


    // ----------------------------------------------------
    public function obtener_correlativo() {
        $query = "SELECT 
            COALESCE(MAX(licencia_certificado_numerosequencia), 0) + 1 AS siguiente_certificado,
            COALESCE(MAX(licencia_resolucion_numero), 0) + 1 AS siguiente_resolucion,
            COALESCE(MAX(licencia_autorizacion_numero), 0) + 1 AS siguiente_numautorizacion
        FROM siga_licencia
        WHERE EXTRACT(YEAR FROM licencia_fechaemision) = EXTRACT(YEAR FROM CURRENT_DATE)
        AND licencia_estadotramite = 'EMITIDO'";
    
        try {
            $result = $this->database->ejecutarConsulta($query);
    
            if ($result && isset($result[0])) {
                response::success($result[0], 'Solicitud de licencia aceptada y en proceso');
            } else {
                response::error('No se encontró la licencia o no se realizaron cambios');
            }
        } catch (PDOException $e) {
            response::error('Error al aceptar la solicitud de licencia: ' . $e->getMessage());
        }
    }
    public function obtener_correlativoResolucion() {
        $query = "SELECT COALESCE(MAX(licencia_resolucion_numero), 0) + 1 AS siguiente_resolucion
                  FROM siga_licencia
                  WHERE EXTRACT(YEAR FROM licencia_fechaemision) = EXTRACT(YEAR FROM CURRENT_DATE)
                  AND licencia_estadotramite = 'EMITIDO'";
        try {
            $result = $this->database->ejecutarConsulta($query);
    
            if ($result && isset($result[0]['siguiente_resolucion'])) {
                return $result[0]['siguiente_resolucion'];
            } else {
                return "Invalido";
            }
        } catch (PDOException $e) {
            response::error('Error al aceptar la solicitud de licencia: ' . $e->getMessage());
        }
    }
        
    
    
    // ------------------------------------------------------
    public function SubirResolucionAdjunta(
        $idlic,
        $file,
        $numeroresolucion
    ) {
        $url = $this->subir_archivo_pdf($file);
        $query = "UPDATE siga_licencia SET 
                    licencia_resolucion_url = ?,
                    licencia_resolucion_numero = ?
                WHERE licencia_idlic = ?";
        try {
            $result = $this->database->ejecutarActualizacion($query, [
                $url,
                $numeroresolucion,
                $idlic
            ]);

            if ($result) {
                response::success($url, 'Resolución actualizado correctamente');
            } else {
                response::error('Error al actualizar el trámite');
            }
        } catch (PDOException $e) {
            response::error('Error al actualizar el trámite: ' . $e->getMessage());
        }
    }
     // ------------------------------------------------------
     public function SubirResolucionGenerada(
        $idlic,
        $url
    ) {
        $query = "UPDATE siga_licencia SET 
                    licencia_resolucion_url = ?
                WHERE licencia_idlic = ?";
        try {
            $result = $this->database->ejecutarActualizacion($query, [
                $url,
                $idlic
            ]);

            if ($result) {
                response::success($url, 'Resolución actualizado correctamente');
            } else {
                response::error('Error al actualizar el trámite');
            }
        } catch (PDOException $e) {
            response::error('Error al actualizar el trámite: ' . $e->getMessage());
        }
    }
    // -------------------------------------------------

    

    public function SubirAutorizacionGenerada(
        $idlic,
        $file,
        $numautorizacion,
        $codautorizacion,
        $numresolucion,
        $codresolucion,
        $qrverificacion,
        $duracion,
    ) {
        $url = $this->subir_archivo_pdf($file);
        
        // Selecciona la fecha de vencimiento según la condición de infinito
        $fechaVencimiento = $duracion=="DEFINITIVA" ? "NULL" : "CURRENT_DATE + INTERVAL '1 year'";
    
        $query = "UPDATE siga_licencia SET 
                    licencia_certificado_url = ?,
                    licencia_autorizacion_numero = ?,
                    licencia_autorizacion_codigo = ?,  
                    licencia_certificado_qrverificacion = ?,
                    licencia_resolucion_numero = ?,
                    licencia_resolucion_codigo = ?,
                    licencia_vigencia_estado = 'VIGENTE',
                    licencia_estadotramite = 'EMITIDO',
                    licencia_fechaemision = CURRENT_DATE,
                    licencia_fechavencimiento = $fechaVencimiento 
                  WHERE licencia_idlic = ?";
                  
        try {
            $result = $this->database->ejecutarActualizacion($query, [
                $url,
                $numautorizacion,
                $codautorizacion,
                $qrverificacion,
                $numresolucion,
                $codresolucion,
                $idlic
            ]);
    
            if ($result) {
                response::success($url, 'Trámite actualizado correctamente');
            } else {
                response::error('Error al actualizar el trámite');
            }
        } catch (PDOException $e) {
            response::error('Error al actualizar el trámite: ' . $e->getMessage());
        }
    }
    // ------------------------------------------------------
    public function SubirLicenciaGenerada(
        $idlic,
        $file,
        $sequiencia,
        $codlicencia,
        $numresolucion,
        $codresolucion,
        $qrverificacion,
        $duracion,
    ) {
        $url = $this->subir_archivo_pdf($file);
        
        // Selecciona la fecha de vencimiento según la condición de infinito
        $fechaVencimiento = $duracion=="DEFINITIVA" ? "NULL" : "CURRENT_DATE + INTERVAL '1 year'";
    
        $query = "UPDATE siga_licencia SET 
                    licencia_certificado_url = ?,
                    licencia_certificado_numerosequencia = ?,
                    licencia_certificado_codigo = ?,  
                    licencia_certificado_qrverificacion = ?,
                    licencia_resolucion_numero = ?,
                    licencia_resolucion_codigo = ?,
                    licencia_vigencia_estado = 'VIGENTE',
                    licencia_estadotramite = 'EMITIDO',
                    licencia_fechaemision = CURRENT_DATE,
                    licencia_fechavencimiento = $fechaVencimiento 
                  WHERE licencia_idlic = ?";
                  
        try {
            $result = $this->database->ejecutarActualizacion($query, [
                $url,
                $sequiencia,
                $codlicencia,
                $qrverificacion,
                $numresolucion,
                $codresolucion,
                $idlic
            ]);
    
            if ($result) {
                response::success($url, 'Trámite actualizado correctamente');
            } else {
                response::error('Error al actualizar el trámite');
            }
        } catch (PDOException $e) {
            response::error('Error al actualizar el trámite: ' . $e->getMessage());
        }
    }

    
    public function SubirLicenciaAdjunta(
        $idlic,
        $file,
        $sequiencia,
        $codlicencia,
        $qrverificacion,
        $fecha // Agregar un parámetro para determinar si la fecha es infinita
    ) {
        $url = $this->subir_archivo_pdf($file);
        
        // Selecciona la fecha de vencimiento según la condición de infinito
        $fecha_emision    = $fecha=="" ? "CURRENT_DATE" : "'$fecha'::date";
        $fechaVencimiento = "NULL";
    
        $query = "UPDATE siga_licencia SET 
                    licencia_certificado_url = ?,
                    licencia_certificado_numerosequencia = ?,
                    licencia_certificado_codigo = ?,  
                    licencia_certificado_qrverificacion = ?,
                    licencia_vigencia_estado = 'VIGENTE',
                    licencia_estadotramite = 'EMITIDO',
                    licencia_fechaemision  = $fecha_emision,
                    licencia_fechavencimiento = $fechaVencimiento
                  WHERE licencia_idlic = ?";
                  
        try {
            $result = $this->database->ejecutarActualizacion($query, [
                $url,
                $sequiencia,
                $codlicencia,
                $qrverificacion,
                $idlic
            ]);
    
            if ($result) {
                response::success($url, 'Trámite actualizado correctamente');
            } else {
                response::error('Error al actualizar el trámite');
            }
        } catch (PDOException $e) {
            response::error('Error al actualizar el trámite: ' . $e->getMessage());
        }
    }
    

    // ------------------------------------------------------
    public function subir_archivo_pdf($file){
        if($file!=""){
            // Declarar la ruta
            $ruta = 'uploads/documentos/';
    
            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }
    
            $nuevo_nombre = "file_".rand(1000000, 9999999);
            $nuevo_nombre_completo = $nuevo_nombre . '.' . $this->detecta_extension(basename($file['name']));
            $uploadfile = $ruta . $nuevo_nombre_completo;
            $ruta_archivo = $ruta . $nuevo_nombre_completo;
    
            $restriccionLogo = "NOPERMITIDO";
    
            // Validamos Tipo --------------------------------------------------------
            $permitidos = array("application/pdf");
            if (in_array($file['type'], $permitidos)) {
                $restriccionLogo = "PERMITIDO";
    
                if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
                    $restriccionLogo = "PERMITIDO";
                }
            }
    
        } else {
            $ruta_archivo = "";
        }
        return $ruta_archivo;
    }
    public function detecta_extension($mi_extension) {
        $ext = explode(".", $mi_extension);
        return end($ext);
    }

    public function obtenerDatos_paraResolucion($idlic) {
        $query = "
            SELECT
                licencia_documento_codexpediente as numexpediente,
                licencia_resolucion_numero AS numresolucion,
                TO_CHAR(CURRENT_DATE, 'DD \"de\" TMMonth \"de\" YYYY') AS fechahoylarga,
                licencia_representantelegal_nombre AS representantelegal,
                licencia_dir_direccioncomercial AS direccioncomercial,
                licencia_negocio_actividadcomercial AS actividadcomercial,              
                licencia_negocio_ruc AS ruc,
                licencia_representantelegal_dni AS dni,
                licencia_pago_codoperacion AS recibopagonumero,
                licencia_negocio_condicionlocal AS zonificacion,
                licencia_negocio_horario AS horarioatencion,
                licencia_idlic AS nrolicencia,
                licencia_negocio_razonsocial AS razonsocial,
                licencia_negocio_nombrecomercial AS nombrecomercial,
                licencia_sqnegocio_area AS area,
                licencia_zonificacion AS zonificacion ,
                licencia_autorizacion_numero as autorizacion_numero,
                licencia_autorizacion_codigo as autorizacion_codigo
            FROM 
                siga_licencia
            WHERE 
                licencia_idlic = ?
        ";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$idlic]);
            if ($result) {
                return $result[0]; // Retorna el primer registro si existe
            } else {
                response::error('No se encontraron datos para el ID de licencia proporcionado');
            }
        } catch (PDOException $e) {
            response::error('Error al obtener datos de la licencia: ' . $e->getMessage());
        }
    }
    


    // ------------------------------------------------------
    public function generarResolucion($idlic){      
        Settings::setTempDir('./tmp');
        // Cargar los datos desde la base de datos
        $datosLicencia = $this->obtenerDatos_paraResolucion($idlic);

        // Cargar la plantilla existente
        $templateProcessor = new TemplateProcessor('./resolucion.docx');

        // Reemplazar los campos de la plantilla con los datos obtenidos
        $templateProcessor->setValue('fechahora', date('d/m/Y - H:i'));
        $templateProcessor->setValue('numresolucion', $datosLicencia['numresolucion'] ?? '');
        $templateProcessor->setValue('numexp', $datosLicencia['numexpediente'] ?? '');
        $templateProcessor->setValue('fechahoylarga', $datosLicencia['fechahoylarga'] ?? '');
        $templateProcessor->setValue('representantelegal', $datosLicencia['representantelegal'] ?? '');
        $templateProcessor->setValue('direccioncomercial', $datosLicencia['direccioncomercial'] ?? '');
        $templateProcessor->setValue('nombrecomercial', $datosLicencia['nombrecomercial'] ?? '');
        $templateProcessor->setValue('actividadcomercial', $datosLicencia['actividadcomercial'] ?? '');
        $templateProcessor->setValue('ruc', $datosLicencia['ruc'] ?? '');
        $templateProcessor->setValue('dni', $datosLicencia['dni'] ?? '');
        $templateProcessor->setValue('recibopagonumero', $datosLicencia['recibopagonumero'] ?? '');
        $templateProcessor->setValue('zonificacion', $datosLicencia['zonificacion'] ?? '');
        $templateProcessor->setValue('horarioatencion', $datosLicencia['horarioatencion'] ?? '');
        $templateProcessor->setValue('nrolicencia', $datosLicencia['nrolicencia'] ?? '');
        $templateProcessor->setValue('razonsocial', $datosLicencia['razonsocial'] ?? '');
        $templateProcessor->setValue('nombrecomercial', $datosLicencia['nombrecomercial'] ?? '');
        $templateProcessor->setValue('area', $datosLicencia['area'] ?? '');
        $templateProcessor->setValue('zonificacion', $datosLicencia['zonificacion'] ?? '');

        // Guardar el archivo Word generado temporalmente
        $tempFile = 'carta_generada.docx';
        $templateProcessor->saveAs($tempFile);

        

        // Convertir el archivo Word a HTML
        $phpWord = IOFactory::load($tempFile);
        $xmlWriter = IOFactory::createWriter($phpWord, 'HTML');
        ob_start();
        $xmlWriter->save('php://output');
        $htmlContent = ob_get_clean();

        // Forzar la codificación UTF-8 para todo el contenido
        $htmlContent = mb_convert_encoding($htmlContent, 'UTF-8', 'auto');

        // Configuración de Dompdf con opciones personalizadas
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Permite cargar imágenes externas o rutas absolutas
        $options->set('defaultFont', 'helvetica'); // Usar la fuente Helvetica

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);

        // Ajustar los márgenes en cm (Dompdf utiliza milímetros)
        $dompdf->setPaper('A4', 'portrait'); // Formato A4 vertical

        // Crear estilos personalizados para los márgenes
        $customCss = '
            <style>
                 @page :first {
                    margin-top: 1cm; /* Menor margen superior solo en la primera página */
                    margin-bottom: 2.1cm;
                    margin-left: 2.8cm;
                    margin-right: 2.8cm;
                }

                /* Márgenes para el resto de las páginas */
                @page {
                    margin-top: 2.5cm;
                    margin-bottom: 2.1cm;
                    margin-left: 2.8cm;
                    margin-right: 2.8cm;
                }
                body {
                    

                    font-family: FreeSerif, sans-serif;
                    font-weight: normal;
                }
              
                p {
                    text-indent: 1.5cm;   
                    text-align: justify; 
                    margin-bottom: 10px; 
                    page-break-inside: avoid;
                }
                div {
                    page-break-inside: avoid; 
                }
                /* Selecciona solo la primera tabla */
                table:first-of-type {
                    font-size: 8px; /* Tamaño de fuente de 8 para la primera tabla */
                }
                table:first-of-type img {
                    width: 30px; /* Tamaño más pequeño para la imagen en la primera tabla */
                    height: auto;
                }
                table:first-of-type tr th:first-child, 
                table:first-of-type tr td:first-child {
                    width: 20%; /* Primera columna 40% */
                }

                table:first-of-type tr th:last-child, 
                table:first-of-type tr td:last-child {
                    width: 80%; /* Segunda columna 60% */
                }
                /* Estilos para eliminar los bordes de la tabla */
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 10px;
                    border: 0;
                    line-height: 1;
                }
                /* Aplicar anchos específicos a las dos columnas */
                table tr th:first-child, 
                table tr td:first-child {
                    width: 40%; /* Primera columna 40% */
                }

                table tr th:last-child, 
                table tr td:last-child {
                    width: 60%; /* Segunda columna 60% */
                }
                table p{
                    text-indent: 0cm; /* Eliminar la sangría */
                }

                table, th, td {
                    border: none !important; /* Fuerza la eliminación de los bordes */
                    
                }
                table tr td {
                    padding: 0px 0; /* Reducir el padding vertical */
                    margin: 0px;
                }

                th, td {
                    padding: 0px 0; /* Reducir el padding vertical */
                    text-align: left;
                    vertical-align: top;
                    border: none !important;
                }
                /* Ajustar el tamaño de las celdas para que no se distorsionen */
                table {
                    table-layout: auto;
                }
                    /* Evitar que se genere el div con page-break */
                div[style*="page-break-before"] {
                        display: none !important;
                }
                div {
                    page-break-inside: avoid; /* Evita los saltos dentro de un elemento */
                }
            </style>
        ';

   
        $title = 'RG - ' . $datosLicencia['numresolucion'].'MDVO';
        $headerHtml = "
            <header>
                <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
                <title>{$title}</title> <!-- Set the title here -->
            </header>
                " . $htmlContent;
        
        // Aplicar los márgenes y la fuente FreeSerif en el HTML
        $htmlContentWithMargins = $customCss . $headerHtml;

        $dompdf->loadHtml($htmlContentWithMargins);
        // Set custom metadata, including the title
        
        $dompdf->render();
        
        file_put_contents('prubea.html', $htmlContentWithMargins);


        // $dompdf->loadHtml($htmlContentWithMargins);
        $pdfFileName = 'resolucion_' . $idlic . '.pdf';
        $pdfDirectory = 'uploads/licencias/';
        $pdfFilePath = $pdfDirectory . $pdfFileName;

        // Crear el directorio si no existe
        if (!is_dir($pdfDirectory)) {
            mkdir($pdfDirectory, 0777, true); // Crea el directorio con permisos
        }

        // Guardar el PDF en la ruta especificada
        file_put_contents($pdfFilePath, $dompdf->output());

        // Generar la URL del PDF
        $pdfUrl = 'uploads/licencias/' . $pdfFileName;

        // Llamar a la función para actualizar la URL en la base de datos
        $this->SubirResolucionGenerada($idlic, $pdfUrl);
        
    }
}
?>
