<?php
include_once './utils/response.php';
include_once './config/database.php';

class RequisitoController {
    private $database;

    public function __construct() {
        $this->database = new Database();
    }

    // ------------------------------------------------------
    public function insertRequisito(
        $req_nombrerequisito,
        $req_esobligatorio,
        $req_esformato,
        $req_formatopdf_url
    ) {
         $query = "INSERT INTO siga_requisito_tramite (
                req_nombrerequisito,
                req_esobligatorio,
                req_esformato,
                req_formatopdf_url
              ) VALUES (
                ?, ?, ?, ?
              ) RETURNING req_idreq"; // Asumiendo que "req_idreq" es el nombre de la columna del ID
    
    
        try {
            $result = $this->database->insertar($query, [
                $req_nombrerequisito,
                $req_esobligatorio,
                $req_esformato,
                $req_formatopdf_url
            ], 'siga_requisito_tramite_req_idreq_seq'); // Secuencia de la tabla en PostgreSQL
    
            if ($result) {
                response::success($result, 'Requisito insertado correctamente');
            } else {
                response::error('Error al insertar el requisito');
            }
        } catch (PDOException $e) {
            response::error('Error al insertar el requisito: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function getRequisito($req_idreq) {
        $query = "SELECT 
                    req_idreq AS idreq,
                    req_nombrerequisito AS reqnombre,
                    req_esobligatorio AS esobligatorio,
                    req_esformato AS esformato,
                    req_formatopdf_url AS formatopdf
                FROM siga_requisito_tramite 
                WHERE req_idreq = ?";

        try {
            $result = $this->database->ejecutarConsulta($query, [$req_idreq]);

            if ($result) {
                response::success($result[0], 'Consulta de requisito exitosa');
            } else {
                response::error('No se encontró el requisito');
            }
        } catch (PDOException $e) {
            response::error('Error al consultar el requisito: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function getRequisitos() {
        $query = "SELECT
                    req_idreq AS idreq,
                    req_nombrerequisito AS reqnombre,
                    req_esobligatorio AS esobligatorio,
                    req_esformato AS esformato,
                    req_formatopdf_url AS formatopdf
                FROM siga_requisito_tramite";

        try {
            $result = $this->database->ejecutarConsulta($query);

            if ($result) {
                response::success($result, 'Lista de requisitos obtenida correctamente');
            } else {
                response::error('No se encontraron requisitos registrados');
            }
        } catch (PDOException $e) {
            response::error('Error al consultar los requisitos: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function updateRequisito(
        $req_idreq,
        $req_nombrerequisito,
        $req_esobligatorio,
        $req_esformato,
        $req_formatopdf_url
    ) {
        $query = "UPDATE siga_requisito_tramite SET 
                    req_nombrerequisito = ?,
                    req_esobligatorio = ?,
                    req_esformato = ?,
                    req_formatopdf_url = ?
                WHERE req_idreq = ?";

        try {
            $result = $this->database->ejecutarActualizacion($query, [
                $req_nombrerequisito,
                $req_esobligatorio,
                $req_esformato,
                $req_formatopdf_url,
                $req_idreq
            ]);

            if ($result) {
                response::success($result, 'Requisito actualizado correctamente');
            } else {
                response::error('Error al actualizar el requisito');
            }
        } catch (PDOException $e) {
            response::error('Error al actualizar el requisito: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function deleteRequisito($req_idreq) {
        $query = "DELETE FROM siga_requisito_tramite WHERE req_idreq = ?";

        try {
            $result = $this->database->ejecutarActualizacion($query, [$req_idreq]);

            if ($result) {
                response::success(null, 'Requisito eliminado correctamente');
            } else {
                response::error('Error al eliminar el requisito');
            }
        } catch (PDOException $e) {
            response::error('Error al eliminar el requisito: ' . $e->getMessage());
        }
    }

    //-------------------------------------------------------------

    // ------------------------------------------------------
    public function add_Requisito_tramite(
        $req_id,
        $tra_id
    ) {
        $query = "INSERT INTO siga_merge_tramiterequisito (tram_id, req_idreq)
                    VALUES (?, ?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $tra_id, $req_id               
            ], 'siga_merge_tramiterequisito_mtr_id_seq'); // Secuencia de la tabla en PostgreSQL
    
            if ($result) {
                response::success($result, 'Requisito asignado correctamente');
            } else {
                response::error('Error al asignar requisito');
            }
        } catch (PDOException $e) {
            response::error('Error al asignar requisito: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function delete_Requisito_tramite($idasignacion) {
        $query = "DELETE FROM siga_merge_tramiterequisito WHERE mtr_id = ?";

        try {
            $result = $this->database->ejecutarActualizacion($query, [$idasignacion]);
            if ($result) {
                response::success(null, 'Asignación eliminada correctamente');
            } else {
                response::error('Error al eliminar el requisito asignado');
            }
        } catch (PDOException $e) {
            response::error('Error al eliminar el requisito asignado: ' . $e->getMessage());
        }
    }

    public function get_lista_RequisitosAsignados($tram_id) {
        // Consulta para obtener trámites con sus requisitos en formato JSON
        $query = "SELECT 
                    mer.mtr_id as idasignacion,
                    r.req_idreq as idreq,
                    r.req_nombrerequisito as nombrerequisito,
                    r.req_esobligatorio as esobligatorio,
                    r.req_esformato as esformato,
                    r.req_formatopdf_url as formatopdf_url
                FROM 
                    siga_requisito_tramite r
                JOIN 
                    siga_merge_tramiterequisito mer ON mer.req_idreq = r.req_idreq
                JOIN 
                    siga_tramite tra ON tra.tram_id = mer.tram_id                    
                WHERE mer.tram_id = '".$tram_id."';";

                try {
                    $result = $this->database->ejecutarConsulta($query);
                    if ($result) {
                        response::success($result, 'Lista de requisitos asignados');
                    } else {
                        response::error('No se encontraron requisitos asignados');
                    }
                } catch (PDOException $e) {
                    response::error('Error al consultar los requisitos: ' . $e->getMessage());
                }
    }

    // ------------------------------------------------------
    public function getTramitesConRequisitos($tram_id) {
        // Consulta para obtener trámites con sus requisitos en formato JSON
        $query = "SELECT 
                    t.tram_id,
                    t.tram_nombretramite,
                    t.tram_descripcion,
                    json_agg(
                        json_build_object(
                            'req_idreq', r.req_idreq,
                            'req_nombrerequisito', r.req_nombrerequisito,
                            'req_esobligatorio', r.req_esobligatorio,
                            'req_esformato', r.req_esformato,
                            'req_formatopdf_url', r.req_formatopdf_url
                        )
                    ) AS requisitos
                  FROM 
                    siga_tramite t
                  JOIN 
                    merge_tramiterequisito m ON t.tram_id = m.tram_id
                  JOIN 
                    siga_requisito_tramite r ON m.req_idreq = r.req_idreq
                  WHERE 
                    t.tram_id = :tram_id
                  GROUP BY 
                    t.tram_id, t.tram_nombretramite, t.tram_descripcion";

        try {
            // Ejecutar consulta
            $stmt = $this->database->ejecutarConsulta($query);
            $stmt->bindParam(':tram_id', $tram_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                // Enviar la respuesta usando la clase response
                response::success($result[0], 'Consulta de licencia exitosa');
            } else {
                // No se encontraron resultados
                response::error('No se encontraron trámites con ese ID');
            }
        } catch (PDOException $e) {
            // En caso de error en la consulta
            response::error('Error al consultar los trámites: ' . $e->getMessage());
        }
    }


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
}
?>
