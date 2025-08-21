<?php
include_once './utils/response.php';
include_once './config/database.php';

/**
 * DocumentoController2 - Versión Optimizada
 * 
 * Mejoras implementadas:
 * - Eliminación de código repetitivo
 * - Funciones genéricas para consultas similares
 * - Constantes para estados
 * - Mejor manejo de errores
 * - Funciones reutilizables
 */
class DocumentoController2 {
    private $database;

    // Constantes para estados de documentos
    const ESTADOS = [
        'INICIADO' => 'Iniciado',
        'ENVIADO' => 'Enviado',
        'PROYECTADO' => 'Proyectado',
        'OBSERVADO' => 'Observado',
        'FIRMADO' => 'Firmado',
        'ARCHIVADO' => 'Archivado',
        'RECIBIDO' => 'Recibido'
    ];

    // Constantes para tipos de archivo PDF
    const TIPOS_PDF = [
        'PRINCIPAL' => 'pdf_principal',
        'ANEXO1' => 'pdf_anexo1', 
        'ANEXO2' => 'pdf_anexo2'
    ];

    public function __construct() {
        $this->database = new Database();
    }

    // ==================== FUNCIONES GENÉRICAS BASE ====================

    /**
     * Función genérica para listar documentos con filtros dinámicos
     */
    private function listarDocumentosBase($condiciones = [], $parametros = [], $limite = null, $offset = null, $funcionPG = 'documento_interno_obtenerdetalles_id') {
        try {
            // Construir WHERE dinámico
            $whereClause = '';
            if (!empty($condiciones)) {
                $whereClause = 'WHERE ' . implode(' AND ', $condiciones);
            }

            // Construir LIMIT y OFFSET
            $limitClause = '';
            if ($limite !== null) {
                $limitClause = "LIMIT $limite";
                if ($offset !== null) {
                    $limitClause .= " OFFSET $offset";
                }
            }

            $query = "SELECT * FROM $funcionPG() AS docs $whereClause ORDER BY fecharegistro DESC $limitClause";
            
            $result = $this->database->ejecutarConsulta($query, $parametros);

            if ($result && count($result) > 0) {
                Response::success($result, 'Lista de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron documentos registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Función genérica para actualizar estado de documentos
     */
    private function actualizarEstadoDocumento($doc_id, $nuevoEstado, $camposAdicionales = []) {
        try {
            // Campos base para actualizar
            $camposUpdate = ['doc_estado = ?'];
            $parametros = [$nuevoEstado];

            // Agregar campos adicionales
            foreach ($camposAdicionales as $campo => $valor) {
                $camposUpdate[] = "$campo = ?";
                $parametros[] = $valor;
            }

            $query = "UPDATE siga_documento SET " . implode(', ', $camposUpdate) . " WHERE doc_iddoc = ?";
            $parametros[] = $doc_id;

            $result = $this->database->ejecutarActualizacion($query, $parametros);

            if ($result > 0) {
                Response::success(null, "Estado del documento actualizado a: $nuevoEstado");
            } else {
                Response::error('No se encontró el documento para actualizar o no se realizaron cambios');
            }
        } catch (PDOException $e) {
            Response::error("Error al actualizar estado del documento: " . $e->getMessage());
        }
    }

    /**
     * Función genérica para actualizar archivos PDF
     */
    private function actualizarArchivoPDF($iddoc, $tipoArchivo, $rutaArchivo, $estadoFirma = null) {
        try {
            $camposUpdate = [];
            $parametros = [];

            // Determinar qué campo actualizar según el tipo
            switch ($tipoArchivo) {
                case self::TIPOS_PDF['PRINCIPAL']:
                    $camposUpdate[] = 'doc_pdf_principal = ?';
                    $parametros[] = $rutaArchivo;
                    if ($estadoFirma !== null) {
                        $camposUpdate[] = 'doc_pdf_principal_estadofirma = ?';
                        $parametros[] = $estadoFirma;
                    }
                    break;
                case self::TIPOS_PDF['ANEXO1']:
                    $camposUpdate[] = 'doc_pdf_anexo1 = ?';
                    $parametros[] = $rutaArchivo;
                    if ($estadoFirma !== null) {
                        $camposUpdate[] = 'doc_pdf_anexo1_estadofirma = ?';
                        $parametros[] = $estadoFirma;
                    }
                    break;
                case self::TIPOS_PDF['ANEXO2']:
                    $camposUpdate[] = 'doc_pdf_anexo2 = ?';
                    $parametros[] = $rutaArchivo;
                    if ($estadoFirma !== null) {
                        $camposUpdate[] = 'doc_pdf_anexo2_estadofirma = ?';
                        $parametros[] = $estadoFirma;
                    }
                    break;
                default:
                    Response::error('Tipo de archivo PDF no válido');
                    return;
            }

            $query = "UPDATE siga_documento SET " . implode(', ', $camposUpdate) . " WHERE doc_iddoc = ?";
            $parametros[] = $iddoc;

            $result = $this->database->ejecutarActualizacion($query, $parametros);

            if ($result > 0) {
                Response::success(null, 'Archivo PDF actualizado correctamente');
            } else {
                Response::error('No se encontró el documento para actualizar');
            }
        } catch (PDOException $e) {
            Response::error("Error al actualizar archivo PDF: " . $e->getMessage());
        }
    }

    // ==================== FUNCIONES DE LISTADO OPTIMIZADAS ====================

    /**
     * Listar documentos de oficina (reemplaza múltiples funciones similares)
     */
    public function listarDocumentosPorOficina($buzon_id, $estado = null, $limite = null, $offset = null) {
        $condiciones = ['buzonorigen_id = ?'];
        $parametros = [$buzon_id];

        if ($estado) {
            $condiciones[] = 'estado = ?';
            $parametros[] = $estado;
        }

        $this->listarDocumentosBase($condiciones, $parametros, $limite, $offset);
    }

    /**
     * Listar documentos por estado (unifica múltiples funciones)
     */
    public function listarDocumentosPorEstado($estado, $buzon_id = null, $limite = null, $offset = null) {
        $condiciones = ['estado = ?'];
        $parametros = [$estado];

        if ($buzon_id) {
            $condiciones[] = 'buzonorigen_id = ?';
            $parametros[] = $buzon_id;
        }

        $this->listarDocumentosBase($condiciones, $parametros, $limite, $offset);
    }

    /**
     * Listar documentos de mesa de partes (unifica 3 funciones anteriores)
     */
    public function listarDocumentosMesaPartes($estado = null, $limite = null, $offset = null, $paraImprimir = false) {
        $funcionPG = 'documento_externo_obtenerdetalles_id';
        $condiciones = [];
        $parametros = [];

        if ($estado) {
            $condiciones[] = 'estado = ?';
            $parametros[] = $estado;
        }

        // Si es para imprimir, usar límite específico
        if ($paraImprimir && !$limite) {
            $limite = 100;
        }

        $this->listarDocumentosBase($condiciones, $parametros, $limite, $offset, $funcionPG);
    }

    /**
     * Obtener documento por ID (genérico para interno/externo)
     */
    public function obtenerDocumentoPorId($id, $esInterno = true) {
        try {
            $funcionPG = $esInterno ? 'documento_interno_obtenerdetalles_id' : 'documento_externo_obtenerdetalles_id';
            $query = "SELECT * FROM $funcionPG() WHERE iddoc = ?";
            
            $result = $this->database->ejecutarConsulta($query, [$id]);

            if ($result && count($result) > 0) {
                Response::success($result[0], 'Documento obtenido correctamente');
            } else {
                Response::error('No se encontró el documento');
            }
        } catch (PDOException $e) {
            Response::error("Error al obtener documento: " . $e->getMessage());
        }
    }

    // ==================== FUNCIONES DE ACTUALIZACIÓN OPTIMIZADAS ====================

    /**
     * Aceptar documento proyectado
     */
    public function aceptarDocumentoProyectado($iddoc) {
        $this->actualizarEstadoDocumento($iddoc, self::ESTADOS['ENVIADO'], [
            'doc_proyectar' => false
        ]);
    }

    /**
     * Enviar a proyectado
     */
    public function enviarAProyectado($iddoc) {
        $this->actualizarEstadoDocumento($iddoc, self::ESTADOS['PROYECTADO'], [
            'doc_proyectar' => true
        ]);
    }

    /**
     * Observar documento
     */
    public function observarDocumento($iddoc, $observacion = '') {
        $this->actualizarEstadoDocumento($iddoc, self::ESTADOS['OBSERVADO'], [
            'doc_observacion' => $observacion
        ]);
    }

    /**
     * Confirmar envío de documento proyectado
     */
    public function confirmarEnvioDocumentoProyectado($iddoc) {
        $this->actualizarEstadoDocumento($iddoc, self::ESTADOS['ENVIADO'], [
            'doc_proyectar' => false
        ]);
    }

    // ==================== FUNCIONES DE ARCHIVOS OPTIMIZADAS ====================

    /**
     * Confirmar envío de documento firmado - Mejorado
     */
    public function confirmarEnvioDocumentoFirmado($iddoc, $pdf_principal) {
        $this->actualizarArchivoPDF($iddoc, self::TIPOS_PDF['PRINCIPAL'], $pdf_principal, 'Firmado');
        $this->actualizarEstadoDocumento($iddoc, self::ESTADOS['FIRMADO']);
    }

    /**
     * Enviar firmado manualmente - Mejorado
     */
    public function enviarFirmadoManualmente($iddoc, $pdf_principal) {
        $this->actualizarArchivoPDF($iddoc, self::TIPOS_PDF['PRINCIPAL'], $pdf_principal, 'Firmado Manualmente');
        $this->actualizarEstadoDocumento($iddoc, self::ESTADOS['FIRMADO']);
    }

    // ==================== FUNCIONES DE INSERCIÓN/ACTUALIZACIÓN ====================

    /**
     * Insertar documento (usando función PostgreSQL)
     */
    public function insertarDocumento($documento) {
        try {
            $query = "SELECT documento_insertar(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) as resultado";
            
            $parametros = [
                $documento['numerodocumento'] ?? null,
                $documento['numeracion_tipodoc_oficina'] ?? null,
                $documento['procedencia'] ?? '',
                $documento['prioridad'] ?? 'Normal',
                $documento['buzonorigen_id'],
                $documento['cabecera'] ?? '',
                $documento['asunto'],
                $documento['folios'] ?? 1,
                $documento['administrado_id'] ?? null,
                $documento['tipodocumento_id'],
                $documento['descripcion'] ?? '',
                $documento['estado'] ?? self::ESTADOS['INICIADO'],
                $documento['referencias_id'] ?? '',
                $documento['otrasreferencias'] ?? '',
                $documento['estupa'] ?? false,
                $documento['pdf_principal'] ?? null,
                $documento['pdf_principal_html'] ?? null,
                $documento['pdf_anexo1'] ?? null,
                $documento['pdf_anexo2'] ?? null,
                $documento['proyectar'] ?? false,
                $documento['usuarionombre'] ?? '',
                $documento['codigoseguimiento'] ?? '',
                $documento['fechavencimiento'] ?? null,
                $documento['tramitetupa_id'] ?? null
            ];

            $result = $this->database->ejecutarConsulta($query, $parametros);

            if ($result && $result[0]['resultado'] > 0) {
                Response::success(['id' => $result[0]['resultado']], 'Documento insertado correctamente');
            } else {
                $error = $this->interpretarErrorInsercion($result[0]['resultado'] ?? -999);
                Response::error($error);
            }

        } catch (PDOException $e) {
            Response::error("Error al insertar documento: " . $e->getMessage());
        }
    }

    /**
     * Actualizar documento interno (usando función PostgreSQL optimizada)
     */
    public function Actualizar_Documento_Interno($iddoc, $prioridad, $asunto, $folios, $pdf_principal = null, $pdf_principal_html = null, $pdf_principal_estadofirma = null, $pdf_anexo1 = null, $pdf_anexo1_estadofirma = null, $pdf_anexo2 = null, $pdf_anexo2_estadofirma = null) {
        try {
            $query = "SELECT documento_interno_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) as resultado";
            
            $parametros = [
                $iddoc,
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
            ];

            $result = $this->database->ejecutarConsulta($query, $parametros);

            if ($result && $result[0]['resultado'] === true) {
                Response::success(null, 'Documento interno actualizado correctamente');
            } else {
                Response::error('Error al actualizar el documento interno');
            }

        } catch (PDOException $e) {
            Response::error("Error al actualizar documento interno: " . $e->getMessage());
        }
    }

    // ==================== FUNCIONES DE TRAZABILIDAD OPTIMIZADAS ====================

    /**
     * Listar trazabilidad de documento (optimizada)
     */
    public function Listar_trazabilidad($id_primogenia) {
        try {
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
                pase.pase_usuarionombre as usuario_remitente_nombre,
                pase.pase_fechaenvio as pase_fechaenvio,
                pase.pase_fecharecepcion as pase_fecharecepcion
            FROM siga_documento AS sd
            LEFT JOIN siga_buzon AS buzon_documento ON buzon_documento.buzon_id = sd.doc_buzonorigen_id
            LEFT JOIN siga_documento_pase AS pase ON pase.pase_documento_primogenio_id = sd.doc_iddoc
            LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
            LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
            WHERE sd.doc_iddoc = ?
            ORDER BY pase.pase_fechaenvio ASC";

            $result = $this->database->ejecutarConsulta($query, [$id_primogenia]);

            if ($result) {
                Response::success($result, 'Trazabilidad obtenida correctamente');
            } else {
                Response::error('No se encontró trazabilidad para el documento');
            }
        } catch (PDOException $e) {
            Response::error("Error al obtener trazabilidad: " . $e->getMessage());
        }
    }

    // ==================== FUNCIONES DE UTILIDAD ====================

    /**
     * Interpretar códigos de error de inserción PostgreSQL
     */
    private function interpretarErrorInsercion($codigo) {
        switch ($codigo) {
            case -1: return 'Error: Violación de restricción UNIQUE. El código ya existe';
            case -2: return 'Error: Algunos campos requeridos están vacíos';
            case -3: return 'Error inesperado durante la inserción';
            case -4: return 'Error: Textos demasiado largos, superan el tamaño de los campos';
            default: return 'Error desconocido al insertar documento';
        }
    }

    /**
     * Validar estado de documento
     */
    private function validarEstado($estado) {
        return in_array($estado, array_values(self::ESTADOS));
    }

    /**
     * Obtener documentos con paginación
     */
    public function obtenerDocumentosConPaginacion($pagina = 1, $porPagina = 20, $filtros = []) {
        $offset = ($pagina - 1) * $porPagina;
        
        $condiciones = [];
        $parametros = [];

        // Aplicar filtros dinámicamente
        foreach ($filtros as $campo => $valor) {
            $condiciones[] = "$campo = ?";
            $parametros[] = $valor;
        }

        $this->listarDocumentosBase($condiciones, $parametros, $porPagina, $offset);
    }

    // ==================== FUNCIONES COMPATIBILIDAD (MANTENIDAS PARA NO ROMPER API) ====================

    /**
     * Funciones mantenidas para compatibilidad con apiweb.php existente
     */
    
    public function listarDocumentos_deOficina($buzon_id) {
        $this->listarDocumentosPorOficina($buzon_id);
    }

    public function listarDocumentos_Proyectados_Oficina($buzon_id) {
        $this->listarDocumentosPorEstado(self::ESTADOS['PROYECTADO'], $buzon_id);
    }

    public function listarDocumentos_Iniciados_Oficina($buzon_id) {
        $this->listarDocumentosPorEstado(self::ESTADOS['INICIADO'], $buzon_id);
    }

    public function listarDocumentos_Obsevados_Oficina($buzon_id) {
        $this->listarDocumentosPorEstado(self::ESTADOS['OBSERVADO'], $buzon_id);
    }

    public function listarDocumentos_mesapartes_iniciado($limite = null) {
        $this->listarDocumentosMesaPartes(self::ESTADOS['INICIADO'], $limite);
    }

    public function listarDocumentos_mesapartes_enviados($limite = null) {
        $this->listarDocumentosMesaPartes(self::ESTADOS['ENVIADO'], $limite);
    }

    public function listarDocumentos_mesapartes_enviados_imprimir() {
        $this->listarDocumentosMesaPartes(self::ESTADOS['ENVIADO'], null, null, true);
    }

    public function Aceptar_documento_proyectado($iddoc) {
        $this->aceptarDocumentoProyectado($iddoc);
    }

    public function Enviar_a_proyectado($iddoc) {
        $this->enviarAProyectado($iddoc);
    }

    public function Observar_documento($iddoc, $observacion = '') {
        $this->observarDocumento($iddoc, $observacion);
    }

    public function ConfirmarEnvio_documento_proyectado($iddoc) {
        $this->confirmarEnvioDocumentoProyectado($iddoc);
    }

    // ==================== PARTE 1: FUNCIONES BÁSICAS ====================

    /**
     * Validar reCAPTCHA de Google
     */
    public function validarRecaptcha($token) {
        $secretKey = '6LckhAcrAAAAAC_bkzKUB0KyKGUzt2GPyIJUGY0d';
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
        curl_setopt($ch, CURLOPT_CAINFO, "C:\inetpub\wwwroot\cert\cacert.pem");

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

    /**
     * Obtener destinos por ID de documento
     */
    public function get_destinos_por_iddocumento($iddoc) {
        $query = "SELECT documento_destinos(?)";
        $parametros = [$iddoc];
        
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Destinos obtenidos correctamente');
            } else {
                Response::error('No se encontraron destinos para el documento');
            }
        } catch (Exception $e) {
            Response::error('Error al obtener destinos: ' . $e->getMessage());
        }
    }

    /**
     * Generar número de documento aleatorio
     */
    private function generarTextoAleatorio($longitud = 8) {
        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $resultado = '';
        for ($i = 0; $i < $longitud; $i++) {
            $resultado .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        return $resultado;
    }

    /**
     * Generar número de documento
     */
    private function genera_numero_documento($procedencia) {
        $query = "SELECT obtener_numerodocumento(?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [$procedencia]);
            return $result[0]['obtener_numerodocumento'] ?? 1;
        } catch (Exception $e) {
            return 1;
        }
    }

    /**
     * Generar numeración por tipo de documento y oficina
     */
    public function genera_numeracion_tipodoc_oficina_fn($procedencia, $tipodocumento_id, $user_buzonorigen_id) {
        $query = "SELECT generar_numeracion_documento_tipodoc_oficina(?, ?, ?)";
        $parametros = [$procedencia, $tipodocumento_id, $user_buzonorigen_id];
        
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Numeración generada correctamente');
            } else {
                Response::error('Error al generar numeración');
            }
        } catch (Exception $e) {
            Response::error('Error al generar numeración: ' . $e->getMessage());
        }
    }

    /**
     * Generar numeración general por tipo de documento y oficina
     */
    public function genera_numeracion_tipodoc_oficina_fn_general($procedencia, $tipodocumento_id, $user_buzon_oficina_id) {
        $query = "SELECT generar_numeracion_documento_tipodoc_oficina_general(?, ?, ?)";
        $parametros = [$procedencia, $tipodocumento_id, $user_buzon_oficina_id];
        
        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Numeración general generada correctamente');
            } else {
                Response::error('Error al generar numeración general');
            }
        } catch (Exception $e) {
            Response::error('Error al generar numeración general: ' . $e->getMessage());
        }
    }

    private function genera_numeracion_tipodoc_oficina($procedencia, $tipodocumento_id, $buzonorigen_id) {
        $query = "SELECT generar_numeracion_documento_tipodoc_oficina(?, ?, ?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [$procedencia, $tipodocumento_id, $buzonorigen_id]);
            return $result[0]['generar_numeracion_documento_tipodoc_oficina'] ?? 1;
        } catch (Exception $e) {
            return 1;
        }
    }

    // ==================== PARTE 2: FUNCIONES DE REGISTRO ====================

    /**
     * Registrar documento externo - Optimizado
     */
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
        $pdf_anexo2
    ) {
        $numdoc = $this->genera_numero_documento($procedencia);
        $numdoc_tipodoc_oficina = $this->genera_numeracion_tipodoc_oficina($procedencia, $tipodocumento_id, $buzonorigen_id);
        $pdf_principal_html = "";
        $codigoseguridad = $this->generarTextoAleatorio();

        $query = "SELECT documento_insertar(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $result = $this->database->insertar($query, [
                $numdoc, $numdoc_tipodoc_oficina, $procedencia, $prioridad,
                $buzonorigen_id, $cabecera, $asunto, $folios, $administrado_id,
                $tipodocumento_id, $descripcion, $estado, $referencias_id,
                $otrasreferencias, $estupa, $pdf_principal, $pdf_principal_html,
                $pdf_anexo1, $pdf_anexo2, $proyectar, $usuario_nombre,
                $codigoseguridad, $fechavencimiento, $tramitetupa_id
            ]);

            if ($result > 0) {
                $this->insert_primerpase_externo($buzonorigen_id, $buzondestino_id, $usuario_id, $usuario_nombre, $result, "Original", "Pase", $result);
                $this->get_lista_documentos_Externos($result);
            } else {
                $this->manejarErrorInsercion($result);
            }

        } catch (Exception $e) {
            Response::error('Error al registrar documento externo: ' . $e->getMessage());
        }
    }

    /**
     * Registrar documento interno - Optimizado
     */
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
        $numdoc_tipodoc_oficina = ($procedencia == "Interno") ? 
            $this->genera_numeracion_tipodoc_oficina($procedencia, $tipodocumento_id, $pase_buzonorigen_id) : "";
        $codigoseguridad = $this->generarTextoAleatorio();
        
        $query = "SELECT documento_insertar(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $result = $this->database->insertar($query, [
                $numdoc, $numdoc_tipodoc_oficina, $procedencia, $prioridad,
                $pase_buzonorigen_id, $cabecera, $asunto, $folios, $administrado_id,
                $tipodocumento_id, $descripcion, $estado, $referencias_id,
                $otrasreferencias, $estupa, $pdf_principal, $pdf_principal_html,
                $pdf_anexo1, $pdf_anexo2, $proyectar, $usuario_nombre,
                $codigoseguridad, $fechavencimiento, $tramitetupa_id
            ]);

            if ($result > 1) {
                $this->procesarPasesYReferencias($result, $arrayOriginal, $arrayCopia, $referenciasArray, 
                    $pase_buzonorigen_id, $usuario_id, $usuario_nombre, $pase_proveido, $idpase);
                $this->get_lista_documento_Interno($result);
            } else {
                $this->manejarErrorInsercion($result);
            }

        } catch (Exception $e) {
            Response::error('Error al registrar documento interno: ' . $e->getMessage());
        }
    }

    /**
     * Procesar pases y referencias para documento interno
     */
    private function procesarPasesYReferencias($id_nuevodocumento, $arrayOriginal, $arrayCopia, $referenciasArray, 
        $pase_buzonorigen_id, $usuario_id, $usuario_nombre, $pase_proveido, $idpase) {
        
        $id_primogenio = $id_nuevodocumento;
        $referenciaValida = false;
        
        if(!empty($referenciasArray) && count($referenciasArray) > 0) {
            $docExists = $this->verificarDocumentoExiste($referenciasArray[0]);
            if($docExists) {
                $id_primogenio = $referenciasArray[0];
                $referenciaValida = true;
            }
        }
        
        // Procesar pases originales
        foreach ($arrayOriginal as $item) {
            $doc_buzondestino_id = $item['id'] ?? null;
            $this->insert_primerpase($pase_buzonorigen_id, $doc_buzondestino_id, $usuario_id, 
                $usuario_nombre, $id_nuevodocumento, "Original", "Pase", $pase_proveido, $id_primogenio, $idpase);
        }

        // Procesar pases copia
        foreach ($arrayCopia as $itemcopia) {
            $doc_buzondestino_id = $itemcopia['id'] ?? null;
            $this->insert_primerpase($pase_buzonorigen_id, $doc_buzondestino_id, $usuario_id, 
                $usuario_nombre, $id_nuevodocumento, "Copia", "Pase", $pase_proveido, $id_primogenio, $idpase);
        }

        // Procesar referencias
        if($referenciaValida) {
            foreach ($referenciasArray as $itemref) {
                if($this->verificarDocumentoExiste($itemref)) {
                    $this->insert_documento_referencia($id_nuevodocumento, $itemref, "Relacion");
                }
            }
        }

        // Actualizar pase si existe
        if($idpase != '') {
            $this->actualizarPaseTramitado($idpase);
        }
    }

    /**
     * Manejar errores de inserción
     */
    private function manejarErrorInsercion($result) {
        switch($result) {
            case -1:
                Response::error('Error: El código ya existe.');
                break;
            case -2:
                Response::error('Error: Campos requeridos no proporcionados (violación de NOT NULL).');
                break;
            default:
                Response::error('Error desconocido al insertar el documento: ' . $result);
        }
    }

    /**
     * Verificar si un documento existe
     */
    private function verificarDocumentoExiste($documento_id) {
        $query = "SELECT COUNT(*) as count FROM siga_documento WHERE documento_id = ?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$documento_id]);
            return isset($result[0]['count']) && $result[0]['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Actualizar pase como tramitado
     */
    private function actualizarPaseTramitado($idpase) {
        $query = "UPDATE siga_documento_pase SET 
                    pase_estadopase = 'Tramitado',
                    pase_tipoaccion = 'Atendido con documento'
                  WHERE pase_id = ?";
        try {
            $this->database->ejecutarActualizacion($query, [$idpase]);
        } catch (Exception $e) {
            // Log error but don't stop execution
        }
    }

    // ==================== PARTE 3: FUNCIONES AUXILIARES DE PASES ====================

    /**
     * Insertar primer pase externo
     */
    private function insert_primerpase_externo(
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
                    pase_documento_id, pase_buzonorigen_id, pase_buzondestino_id,
                    pase_tipopase, pase_proveido, pase_observacion, pase_estadopase,
                    pase_usuario_id, pase_usuarionombre, pase_tipoaccion, pase_documento_primogenio_id
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $pase_documento_id, $pase_buzonorigen_id, $pase_buzondestino_id,
                $pase_tipo, "", "", "Iniciado", $pase_usuario_id, 
                $pase_usuarionombre, $pase_tipoaccion, $pase_primogenio
            ]);
            return (bool)$result;
        } catch (Exception $e) {
            Response::error('Error al insertar pase externo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Insertar primer pase interno
     */
    private function insert_primerpase(
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
                    pase_documento_id, pase_buzonorigen_id, pase_buzondestino_id,
                    pase_tipopase, pase_proveido, pase_observacion, pase_estadopase,
                    pase_usuario_id, pase_usuarionombre, pase_tipoaccion, 
                    pase_documento_primogenio_id, pase_idprevio
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $pase_documento_id, $pase_buzonorigen_id, $pase_buzondestino_id,
                $pase_tipo, $pase_proveido, "", "Enviado", $pase_usuario_id,
                $pase_usuarionombre, $pase_tipoaccion, $pase_primogenio, $pase_idprevio
            ]);
            return (bool)$result;
        } catch (Exception $e) {
            Response::error('Error al insertar pase interno: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Insertar referencia de documento
     */
    private function insert_documento_referencia(
        $refer_id_documento_origen,  
        $refer_id_documento_referenciado,
        $refer_tiporeferencia  
    ) {
        $query = "INSERT INTO siga_documento_referencia (
                    refer_id_documento_origen, refer_id_documento_referenciado, refer_tiporeferencia
                  ) VALUES (?, ?, ?)";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $refer_id_documento_origen, $refer_id_documento_referenciado, $refer_tiporeferencia
            ]);
            return (bool)$result;
        } catch (Exception $e) {
            Response::error('Error al insertar referencia: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener lista de documentos externos
     */
    private function get_lista_documentos_Externos($documento_id) {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id() WHERE documento_id = ?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$documento_id]);
            if ($result) {
                Response::success($result, 'Documento externo registrado correctamente');
            } else {
                Response::error('Error al obtener documento externo');
            }
        } catch (Exception $e) {
            Response::error('Error al obtener documento externo: ' . $e->getMessage());
        }
    }

    /**
     * Obtener lista de documento interno
     */
    public function get_lista_documento_Interno($documento_id) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id() WHERE documento_id = ?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$documento_id]);
            if ($result) {
                Response::success($result, 'Documento interno registrado correctamente');
            } else {
                Response::error('Error al obtener documento interno');
            }
        } catch (Exception $e) {
            Response::error('Error al obtener documento interno: ' . $e->getMessage());
        }
    }

    // ==================== PARTE 4: FUNCIONES DE ACTUALIZACIÓN ====================

    /**
     * Actualizar documento con proveído - Optimizado
     */
    public function Actualizar_Documento_proveido(
        $iddoc,
        $buzonorigen_id,
        $usuario_id,
        $usuario_nombre,
        $ComentarioProveido,
        $arrayoriginal
    ) {
        // Verificar que no hayan documentos recibidos
        if (!$this->validarDocumentoParaActualizacion($iddoc)) {
            return;
        }

        // Eliminar pases existentes
        if ($this->eliminarPasesDocumento($iddoc)) {
            // Crear nuevos pases
            $this->crearNuevosPases($arrayoriginal, $buzonorigen_id, $usuario_id, $usuario_nombre, $iddoc, $ComentarioProveido);
            Response::success("Completado", 'Registro Actualizado');
        } else {
            Response::error("Error al actualizar documento");
        }
    }

    /**
     * Validar si el documento puede ser actualizado
     */
    private function validarDocumentoParaActualizacion($iddoc) {
        $query = "SELECT COUNT(pase_id) AS recibidos FROM siga_documento_pase 
                  WHERE pase_estadopase='Recibido' AND pase_documento_id = ?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$iddoc]);
            if ($result && $result[0]['recibidos'] > 0) {
                Response::error('No se puede actualizar porque ya fue recepcionado');
                return false;
            }
            return true;
        } catch (Exception $e) {
            Response::error('Error al validar documento: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar pases de documento
     */
    private function eliminarPasesDocumento($iddoc) {
        $query = "DELETE FROM siga_documento_pase WHERE pase_documento_id = ?";
        try {
            $this->database->ejecutarConsulta($query, [$iddoc]);
            return true;
        } catch (Exception $e) {
            Response::error('Error al eliminar pases: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nuevos pases para documento
     */
    private function crearNuevosPases($arrayoriginal, $buzonorigen_id, $usuario_id, $usuario_nombre, $iddoc, $ComentarioProveido) {
        foreach ($arrayoriginal as $item) {
            $doc_buzondestino_id = $item['id'] ?? null;
            $this->insert_primerpase($buzonorigen_id, $doc_buzondestino_id, $usuario_id, 
                $usuario_nombre, $iddoc, "Original", "Pase", $ComentarioProveido, $iddoc, null);
        }
    }

    /**
     * Actualizar documento corrección trabajador - Optimizado
     */
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
        $arrayoriginal,
        $arraycopia,
        $referenciasArray,
        $otrasreferenciasArray,
        $pase_proveido
    ) {
        // Actualizar datos básicos del documento
        $this->actualizarDatosBasicosDocumento($iddoc, $asunto, $folios, $pdf_principal, 
            $pdf_principal_html, $pdf_principal_estadofirma, $pdf_anexo1, 
            $pdf_anexo1_estadofirma, $pdf_anexo2, $pdf_anexo2_estadofirma);

        // Recrear pases y referencias
        $this->recrearPasesYReferencias($iddoc, $arrayoriginal, $arraycopia, $referenciasArray, 
            $buzonorigen_id, $usuario_id, $usuario_nombre, $pase_proveido);

        $this->get_lista_documento_Interno($iddoc);
    }

    /**
     * Actualizar datos básicos del documento
     */
    private function actualizarDatosBasicosDocumento($iddoc, $asunto, $folios, $pdf_principal, 
        $pdf_principal_html, $pdf_principal_estadofirma, $pdf_anexo1, 
        $pdf_anexo1_estadofirma, $pdf_anexo2, $pdf_anexo2_estadofirma) {
        
        $query = "UPDATE siga_documento SET 
                    documento_asunto = ?, documento_folios = ?, 
                    documento_pdf_principal = ?, documento_pdf_principal_html = ?, 
                    documento_pdf_principal_estadofirma = ?,
                    documento_pdf_anexo1 = ?, documento_pdf_anexo1_estadofirma = ?,
                    documento_pdf_anexo2 = ?, documento_pdf_anexo2_estadofirma = ?
                  WHERE documento_id = ?";

        try {
            $this->database->ejecutarActualizacion($query, [
                $asunto, $folios, $pdf_principal, $pdf_principal_html, $pdf_principal_estadofirma,
                $pdf_anexo1, $pdf_anexo1_estadofirma, $pdf_anexo2, $pdf_anexo2_estadofirma, $iddoc
            ]);
        } catch (Exception $e) {
            Response::error('Error al actualizar datos del documento: ' . $e->getMessage());
        }
    }

    /**
     * Recrear pases y referencias del documento
     */
    private function recrearPasesYReferencias($iddoc, $arrayoriginal, $arraycopia, $referenciasArray, 
        $buzonorigen_id, $usuario_id, $usuario_nombre, $pase_proveido) {
        
        // Eliminar pases y referencias existentes
        $this->eliminarPasesDocumento($iddoc);
        $this->eliminarReferenciasDocumento($iddoc);

        // Recrear pases originales
        foreach ($arrayoriginal as $item) {
            $doc_buzondestino_id = $item['id'] ?? null;
            $this->insert_primerpase($buzonorigen_id, $doc_buzondestino_id, $usuario_id, 
                $usuario_nombre, $iddoc, "Original", "Pase", $pase_proveido, $iddoc, null);
        }

        // Recrear pases copia
        foreach ($arraycopia as $itemcopia) {
            $doc_buzondestino_id = $itemcopia['id'] ?? null;
            $this->insert_primerpase($buzonorigen_id, $doc_buzondestino_id, $usuario_id, 
                $usuario_nombre, $iddoc, "Copia", "Pase", $pase_proveido, $iddoc, null);
        }

        // Recrear referencias
        foreach ($referenciasArray as $itemref) {
            if($this->verificarDocumentoExiste($itemref)) {
                $this->insert_documento_referencia($iddoc, $itemref, "Relacion");
            }
        }
    }

    /**
     * Eliminar referencias de documento
     */
    private function eliminarReferenciasDocumento($iddoc) {
        $query = "DELETE FROM siga_documento_referencia WHERE refer_id_documento_origen = ?";
        try {
            $this->database->ejecutarConsulta($query, [$iddoc]);
        } catch (Exception $e) {
            // Log error but don't stop execution
        }
    }

    // ==================== PARTE 5: FUNCIONES DE CONFIRMACIÓN Y ENVÍO ====================

    /**
     * Confirmar envío de documento firmado - Optimizado
     */
    public function Confirmar_EnviodocumentoFirmado($iddoc) {
        if (!is_numeric($iddoc) || $iddoc <= 0) {
            Response::error('ID de documento inválido');
            return;
        }

        try {
            // Verificar estados de firma de los documentos
            $documento = $this->obtenerEstadosFirmaDocumento($iddoc);
            if (!$documento) {
                Response::error('Documento no encontrado');
                return;
            }

            // Validar que todos los documentos requeridos estén firmados
            $faltanFirmar = $this->validarDocumentosFirmados($documento);
            if (!empty($faltanFirmar)) {
                Response::error('Falta firmar: ' . implode(', ', $faltanFirmar));
                return;
            }

            // Actualizar estado del documento
            $this->actualizarEstadoDocumentoFirmado($iddoc);

        } catch (Exception $e) {
            Response::error("Error al confirmar envío: " . $e->getMessage());
        }
    }

    /**
     * Obtener estados de firma del documento
     */
    private function obtenerEstadosFirmaDocumento($iddoc) {
        $query = "SELECT 
                    documento_pdf_principal, documento_pdf_principal_estadofirma,
                    documento_pdf_anexo1, documento_pdf_anexo1_estadofirma,
                    documento_pdf_anexo2, documento_pdf_anexo2_estadofirma
                  FROM siga_documento WHERE documento_id = ?";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$iddoc]);
            return $result[0] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Validar que documentos requeridos estén firmados
     */
    private function validarDocumentosFirmados($documento) {
        $faltanFirmar = [];
        
        // Verificar documento principal
        if (!empty($documento['documento_pdf_principal']) && 
            $documento['documento_pdf_principal_estadofirma'] !== 'Firmado') {
            $faltanFirmar[] = 'Documento principal';
        }

        // Verificar anexo 1
        if (!empty($documento['documento_pdf_anexo1']) && 
            $documento['documento_pdf_anexo1_estadofirma'] !== 'Firmado') {
            $faltanFirmar[] = 'Anexo 1';
        }

        // Verificar anexo 2
        if (!empty($documento['documento_pdf_anexo2']) && 
            $documento['documento_pdf_anexo2_estadofirma'] !== 'Firmado') {
            $faltanFirmar[] = 'Anexo 2';
        }

        return $faltanFirmar;
    }

    /**
     * Actualizar estado del documento firmado
     */
    private function actualizarEstadoDocumentoFirmado($iddoc) {
        $query = "UPDATE siga_documento 
                  SET documento_estado = 'Enviado', documento_proyectar = false 
                  WHERE documento_id = ?";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$iddoc]);
            if ($result > 0) {
                Response::success($result, 'Documentos actualizados correctamente');
            } else {
                Response::error('No se actualizó el documento');
            }
        } catch (Exception $e) {
            Response::error('Error al actualizar documento: ' . $e->getMessage());
        }
    }

    /**
     * Enviar firmado manualmente - Optimizado
     */
    public function Enviar_FirmadoManualmente($iddoc) {
        $query = "UPDATE siga_documento SET 
                    documento_pdf_principal_estadofirma = 'Firmado',
                    documento_proyectar = false,
                    documento_estado = 'Enviado'
                  WHERE documento_id = ?";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$iddoc]);
            if ($result) {
                Response::success($result, 'Documentos actualizados');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (Exception $e) {
            Response::error("Error al enviar firmado manualmente: " . $e->getMessage());
        }
    }

    /**
     * Confirmar proveído - Optimizado
     */
    public function Confirmar_Proveido($iddoc) {
        $query = "UPDATE siga_documento SET documento_estado = 'Enviado' WHERE documento_id = ?";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$iddoc]);
            if ($result) {
                Response::success($result, 'Documentos actualizados');
            } else {
                Response::error('Error en la actualización de documento');
            }
        } catch (Exception $e) {
            Response::error("Error al confirmar proveído: " . $e->getMessage());
        }
    }

    /**
     * Actualizar archivos PDF - Mejorado
     */
    public function Actualizar_archivos($pdf_url, $tipo, $iddoc) {
        $campoArchivo = $this->obtenerCampoArchivoPorTipo($tipo);
        $campoEstado = $campoArchivo . '_estadofirma';
        
        $query = "UPDATE siga_documento SET {$campoArchivo} = ?, {$campoEstado} = 'Firmado' WHERE documento_id = ?";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$pdf_url, $iddoc]);
            if ($result) {
                Response::success($result, 'Archivo actualizado correctamente');
            } else {
                Response::error('Error al actualizar archivo');
            }
        } catch (Exception $e) {
            Response::error("Error al actualizar archivo: " . $e->getMessage());
        }
    }

    /**
     * Obtener campo de archivo por tipo
     */
    private function obtenerCampoArchivoPorTipo($tipo) {
        $campos = [
            'pdf_principal' => 'documento_pdf_principal',
            'pdf_anexo1' => 'documento_pdf_anexo1',
            'pdf_anexo2' => 'documento_pdf_anexo2'
        ];
        
        return $campos[$tipo] ?? 'documento_pdf_principal';
    }

    // ==================== PARTE 6: FUNCIONES DE BÚSQUEDA Y CONSULTA ====================

    /**
     * Obtener referencias de documento - Optimizado
     */
    public function obtener_referencias($tipodocumento_id, $numerodocumento, $anio, $buzonorigen_id) {
        try {
            $query = "SELECT * FROM documento_interno_obtenerdetalles_id() WHERE 1=1";
            $parametros = [];
            
            // Construir condiciones dinámicamente
            $condiciones = [
                'tipodocumento_id' => $tipodocumento_id,
                'buzonorigen_id' => $buzonorigen_id,
                'anio' => $anio
            ];

            foreach ($condiciones as $campo => $valor) {
                if (!empty($valor)) {
                    $query .= " AND {$campo} = ?";
                    $parametros[] = $valor;
                }
            }
            
            // Búsqueda de número documento con LIKE
            if (!empty($numerodocumento)) {
                $query .= " AND CAST(numeracion_tipodoc_oficina AS TEXT) LIKE ?";
                $parametros[] = '%' . $numerodocumento . '%';
            }
            
            $query .= " ORDER BY documento_id ASC";
            
            $result = $this->database->ejecutarConsulta($query, $parametros);
            
            if (count($result) > 0) {
                Response::success($result, 'Referencias obtenidas exitosamente');
            } else {
                Response::error('No se encontraron documentos de referencia');
            }
    
        } catch (Exception $e) {
            Response::error('Error al obtener referencias: ' . $e->getMessage());
        }
    }

    /**
     * Buscar documentos externos por persona - Optimizado
     */
    public function buscar_documentos_externos_persona(
        $administrado_nombre,
        $administrado_apellidopat,
        $administrado_apellidomat,
        $administrado_tipodocumento,
        $administrado_numdocumento,
        $dia,
        $mes,
        $anio
    ) {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id()";
        $condiciones = [];
        $parametros = [];

        // Construir condiciones dinámicamente
        $filtros = [
            'administrado_nombre' => $administrado_nombre,
            'administrado_apellidopat' => $administrado_apellidopat,
            'administrado_apellidomat' => $administrado_apellidomat,
            'administrado_tipodocumento' => $administrado_tipodocumento,
            'administrado_numdocumento' => $administrado_numdocumento
        ];

        foreach ($filtros as $campo => $valor) {
            if (!empty($valor)) {
                $condiciones[] = "{$campo} ILIKE ?";
                $parametros[] = "%{$valor}%";
            }
        }

        // Filtros de fecha
        if (!empty($dia)) {
            $condiciones[] = "EXTRACT(DAY FROM documento_fecharegistro) = ?";
            $parametros[] = $dia;
        }
        if (!empty($mes)) {
            $condiciones[] = "EXTRACT(MONTH FROM documento_fecharegistro) = ?";
            $parametros[] = $mes;
        }
        if (!empty($anio)) {
            $condiciones[] = "EXTRACT(YEAR FROM documento_fecharegistro) = ?";
            $parametros[] = $anio;
        }

        if (!empty($condiciones)) {
            $query .= " WHERE " . implode(" AND ", $condiciones);
        }

        $query .= " ORDER BY documento_fecharegistro DESC";

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Búsqueda de documentos externos completada');
            } else {
                Response::error('No se encontraron documentos externos');
            }
        } catch (Exception $e) {
            Response::error('Error en búsqueda de documentos externos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar documentos internos opcional - Optimizado
     */
    public function buscar_documentos_internos_opcional(
        $tipodocumento_nombre = null,
        $buzonorigen_nombre = null,
        $dia = null,
        $mes = null,
        $anio = null
    ) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id()";
        $condiciones = [];
        $parametros = [];

        // Filtros de texto con ILIKE
        if ($tipodocumento_nombre) {
            $condiciones[] = "tipodocumento_nombre ILIKE ?";
            $parametros[] = "%{$tipodocumento_nombre}%";
        }
        if ($buzonorigen_nombre) {
            $condiciones[] = "buzonorigen_nombre ILIKE ?";
            $parametros[] = "%{$buzonorigen_nombre}%";
        }

        // Filtros de fecha
        if (!empty($dia)) {
            $condiciones[] = "EXTRACT(DAY FROM documento_fecharegistro) = ?";
            $parametros[] = $dia;
        }
        if (!empty($mes)) {
            $condiciones[] = "EXTRACT(MONTH FROM documento_fecharegistro) = ?";
            $parametros[] = $mes;
        }
        if (!empty($anio)) {
            $condiciones[] = "EXTRACT(YEAR FROM documento_fecharegistro) = ?";
            $parametros[] = $anio;
        }

        if (!empty($condiciones)) {
            $query .= " WHERE " . implode(" AND ", $condiciones);
        }

        $query .= " ORDER BY documento_fecharegistro DESC";

        try {
            $result = $this->database->ejecutarConsulta($query, $parametros);
            if ($result) {
                Response::success($result, 'Búsqueda de documentos internos completada');
            } else {
                Response::error('No se encontraron documentos internos');
            }
        } catch (Exception $e) {
            Response::error('Error en búsqueda de documentos internos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar documento interno por año y número
     */
    public function get_lista_documento_Interno_buscar($anio, $nrodocumento) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id() 
                  WHERE anio = ? AND numeracion_tipodoc_oficina = ?
                  ORDER BY documento_id ASC";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$anio, $nrodocumento]);
            if ($result) {
                Response::success($result, 'Documento encontrado');
            } else {
                Response::error('No se encontró el documento especificado');
            }
        } catch (Exception $e) {
            Response::error('Error al buscar documento: ' . $e->getMessage());
        }
    }

    /**
     * Listar documentos externos por número de documento
     */
    public function listarDocumentos_externo_x_nro_documento($numdocumento) {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id() 
                  WHERE administrado_numdocumento = ?
                  ORDER BY documento_fecharegistro DESC";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$numdocumento]);
            if ($result) {
                Response::success($result, 'Documentos externos obtenidos');
            } else {
                Response::error('No se encontraron documentos para el número especificado');
            }
        } catch (Exception $e) {
            Response::error('Error al listar documentos externos: ' . $e->getMessage());
        }
    }

    // ==================== PARTE 7: FUNCIONES DE OBTENCIÓN DE DOCUMENTOS ESPECÍFICOS ====================

    /**
     * Obtener documento externo por ID
     */
    public function get_documento_externo($doc_iddoc) {
        $query = "SELECT * FROM documento_externo_obtenerdetalles_id()
                  WHERE procedencia != 'Interno' AND documento_id = ?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$doc_iddoc]);
            if ($result && count($result) > 0) {
                Response::success($result[0], 'Documento externo obtenido exitosamente');
            } else {
                Response::error('No se encontró el documento externo');
            }
        } catch (Exception $e) {
            Response::error("Error al obtener documento externo: " . $e->getMessage());
        }
    }

    /**
     * Obtener documento interno por ID
     */
    public function get_documento_interno($doc_iddoc) {
        $query = "SELECT * FROM documento_interno_edit_id()
                  WHERE procedencia != 'Externo' AND documento_id = ?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$doc_iddoc]);
            if ($result && count($result) > 0) {
                Response::success($result[0], 'Documento interno obtenido exitosamente');
            } else {
                Response::error('No se encontró el documento interno');
            }
        } catch (Exception $e) {
            Response::error("Error al obtener documento interno: " . $e->getMessage());
        }
    }

    /**
     * Obtener documento interno por año, número y código
     */
    public function get_lista_documento_Interno_anionro($anio, $doc, $codigo) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id()
                  WHERE procedencia != 'Interno' AND anio = ? AND numerodocumento = ? AND codigoseguimiento = ?";
        try {
            $result = $this->database->ejecutarConsulta($query, [$anio, $doc, $codigo]);
            if ($result && count($result) > 0) {
                Response::success($result[0], 'Documento encontrado exitosamente');
            } else {
                Response::error('No se encontró el documento con los parámetros especificados');
            }
        } catch (Exception $e) {
            Response::error("Error al obtener documento: " . $e->getMessage());
        }
    }

    /**
     * Obtener documentos referenciados
     */
    public function Documentos_referenciados($iddocumento) {
        $query = "SELECT documento_referencias(?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [$iddocumento]);
            if ($result) {
                Response::success($result, 'Referencias obtenidas exitosamente');
            } else {
                Response::error('No se encontraron referencias para el documento');
            }
        } catch (Exception $e) {
            Response::error("Error al obtener referencias: " . $e->getMessage());
        }
    }

    // ==================== PARTE 8: FUNCIONES DE LISTADO ESPECÍFICO ====================

    /**
     * Generar libro de cargos PDF
     */
    public function generarLibroCargosPDF($desde, $hasta) {
        $query = "SELECT generar_libro_cargos_pdf(?, ?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [$desde, $hasta]);
            if ($result) {
                Response::success($result, 'Libro de cargos generado exitosamente');
            } else {
                Response::error('Error al generar libro de cargos');
            }
        } catch (Exception $e) {
            Response::error("Error al generar libro de cargos: " . $e->getMessage());
        }
    }

    /**
     * Listar documentos bandeja recibidos mesa partes
     */
    public function listarDocumentos_BandejaRecibidos_MesaPartes($user_buzonorigen_id) {
        $query = "SELECT 
            pase.pase_id as idpase,
            pase.pase_buzonorigen_id as origen_id,
            buzon_pase_origen.buzon_nombre AS origen_nombre,
            pase.pase_buzondestino_id as destino_id,
            buzon_pase_origen.buzon_sigla AS origen_sigla,
            buzon_pase_destino.buzon_nombre AS destino_nombre,
            pase.pase_tipopase as tipopase,
            pase.pase_proveido as pase_proveido,
            pase.pase_observacion as observacion, 
            pase.pase_estadopase as estadopase,
            pase.pase_documento_id as documento_id,
            pase.pase_usuario_id as usuario_remitente_id, 
            pase.pase_usuarionombre as usuario_remitente_nombre,
            pase.pase_fechaenvio as pase_fechaenvio,
            pase.pase_fecharecepcion as pase_fecharecepcion,
            pase.pase_documento_primogenio_id as primogenio_id,
            sd.documento_numerodocumento AS numerodocumento,
            sd.documento_numeracion_tipodoc_oficina AS numeracion_tipodoc_oficina,
            sd.documento_procedencia AS procedencia,
            sd.documento_cabecera AS cabecera,
            sd.documento_asunto AS asunto,
            sd.documento_folios AS folios,
            sd.documento_prioridad AS prioridad,
            sd.documento_pdf_principal AS pdf_principal,
            sd.documento_pdf_anexo1 AS pdf_anexo1,
            sd.documento_pdf_anexo2 AS pdf_anexo2,
            sd.documento_anio AS anio
        FROM siga_documento_pase AS pase
        LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
        LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
        LEFT JOIN siga_documento AS sd ON sd.documento_id = pase.pase_documento_id
        WHERE pase.pase_buzondestino_id = ? AND pase.pase_estadopase = 'Enviado'
        ORDER BY pase.pase_fechaenvio DESC";

        try {
            $result = $this->database->ejecutarConsulta($query, [$user_buzonorigen_id]);
            if ($result) {
                Response::success($result, 'Bandeja de recibidos obtenida exitosamente');
            } else {
                Response::error('No se encontraron documentos en bandeja de recibidos');
            }
        } catch (Exception $e) {
            Response::error("Error al obtener bandeja de recibidos: " . $e->getMessage());
        }
    }

    /**
     * Listar documentos enviados de oficina
     */
    public function listarDocumentos_Enviados_Oficina($user_buzonorigen_id) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id() 
                  WHERE buzonorigen_id = ? AND documento_estado = 'Enviado'
                  ORDER BY documento_fecharegistro DESC";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$user_buzonorigen_id]);
            if ($result) {
                Response::success($result, 'Documentos enviados obtenidos exitosamente');
            } else {
                Response::error('No se encontraron documentos enviados');
            }
        } catch (Exception $e) {
            Response::error("Error al obtener documentos enviados: " . $e->getMessage());
        }
    }

    /**
     * Listar documentos archivados de oficina
     */
    public function listarDocumentos_Archivados_Oficina($estado, $user_buzonorigen_id, $user_buzondestino_id) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id() 
                  WHERE (buzonorigen_id = ? OR buzondestino_id = ?) AND documento_estado = ?
                  ORDER BY documento_fecharegistro DESC";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$user_buzonorigen_id, $user_buzondestino_id, $estado]);
            if ($result) {
                Response::success($result, 'Documentos archivados obtenidos exitosamente');
            } else {
                Response::error('No se encontraron documentos archivados');
            }
        } catch (Exception $e) {
            Response::error("Error al obtener documentos archivados: " . $e->getMessage());
        }
    }

    /**
     * Listar documentos emitidos por usuario y año
     */
    public function listarDocumentosEmitidos_por_usuario_anio($user_buzonorigen_id, $anio) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id() 
                  WHERE buzonorigen_id = ? AND documento_anio = ? AND documento_estado IN ('Enviado', 'Firmado')
                  ORDER BY documento_fecharegistro DESC";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$user_buzonorigen_id, $anio]);
            if ($result) {
                Response::success($result, 'Documentos emitidos obtenidos exitosamente');
            } else {
                Response::error('No se encontraron documentos emitidos para el año especificado');
            }
        } catch (Exception $e) {
            Response::error("Error al obtener documentos emitidos: " . $e->getMessage());
        }
    }

    /**
     * Obtener tipos de documentos generados
     */
    public function Get_TipoDocumentos_Generados($buzonOrigen_id) {
        $query = "SELECT tipodocumento_nombre as nombre, tipodocumento_id as id, COUNT(tipodocumento_nombre) as nrodocumentos
                  FROM documento_interno_obtenerdetalles_id()
                  WHERE buzonorigen_id = ?
                  GROUP BY tipodocumento_nombre, tipodocumento_id
                  ORDER BY nrodocumentos DESC";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$buzonOrigen_id]);
            if ($result) {
                Response::success($result, 'Tipos de documentos generados obtenidos exitosamente');
            } else {
                Response::error('No se encontraron tipos de documentos generados');
            }
        } catch (Exception $e) {
            Response::error("Error al obtener tipos de documentos generados: " . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas por tipo de pases
     */
    public function Get_estadistico_portipopases($buzonOrigen_id) {
        $query = "SELECT 
                    pase_tipopase as tipo,
                    COUNT(*) as cantidad,
                    pase_estadopase as estado
                  FROM siga_documento_pase 
                  WHERE pase_buzonorigen_id = ?
                  GROUP BY pase_tipopase, pase_estadopase
                  ORDER BY cantidad DESC";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$buzonOrigen_id]);
            if ($result) {
                Response::success($result, 'Estadísticas por tipo de pases obtenidas exitosamente');
            } else {
                Response::error('No se encontraron estadísticas de pases');
            }
        } catch (Exception $e) {
            Response::error("Error al obtener estadísticas de pases: " . $e->getMessage());
        }
    }

    /**
     * Obtener lista de documentos generados por tipo
     */
    public function Get_ListaDocumentos_Generados_x_tipo($buzonOrigen_id, $tipodoc) {
        $query = "SELECT * FROM documento_interno_obtenerdetalles_id()
                  WHERE buzonorigen_id = ? AND tipodocumento_id = ?
                  ORDER BY documento_fecharegistro DESC";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$buzonOrigen_id, $tipodoc]);
            if ($result) {
                Response::success($result, 'Lista de documentos por tipo obtenida exitosamente');
            } else {
                Response::error('No se encontraron documentos del tipo especificado');
            }
        } catch (Exception $e) {
            Response::error("Error al obtener documentos por tipo: " . $e->getMessage());
        }
    }

    // ==================== PARTE 9: FUNCIÓN UPDATE DOCUMENTO ====================

    /**
     * Actualizar documento - Optimizado
     */
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
        // Validar fecha de vencimiento
        $fechavencimiento = (empty($fechavencimiento) || $fechavencimiento === '') ? null : $fechavencimiento;

        $query = "SELECT actualizar_documento_externo_y_pase(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $doc_iddoc, $origen, $prioridad, $cabecera, $asunto, $folios,
                $administrado_id, $tipodocumento_id, $descripcion, $estupa,
                $pdf_principal, $p_pase_id, $p_pase_iddestino, $proyectar,
                $usuarionombre, $fechavencimiento, $tramitetupa_id
            ]);

            $respuesta = $result[0]['actualizar_documento_externo_y_pase'] ?? -999;
            $this->manejarRespuestaActualizacion($respuesta);

        } catch (Exception $e) {
            Response::error('Error al actualizar documento: ' . $e->getMessage());
        }
    }

    /**
     * Manejar respuesta de actualización
     */
    private function manejarRespuestaActualizacion($codigo) {
        $mensajes = [
            1 => 'Documento actualizado correctamente',
            -1 => 'Error: El documento no fue encontrado',
            -2 => 'Error: El pase no fue encontrado',
            -3 => 'Error: No se pudo actualizar el documento',
            -4 => 'Error: No se pudo actualizar el pase',
            -5 => 'Error: Violación de clave foránea. Verifique las referencias',
            -6 => 'Error: Valor nulo en columna que no lo permite',
            -7 => 'Error inesperado en la base de datos'
        ];

        if ($codigo === 1) {
            Response::success($codigo, $mensajes[$codigo]);
        } else {
            $mensaje = $mensajes[$codigo] ?? "Error desconocido con código: $codigo";
            Response::error($mensaje);
        }
    }
}
?>
