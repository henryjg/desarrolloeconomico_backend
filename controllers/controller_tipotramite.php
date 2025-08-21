<?php
include_once './utils/response.php';
include_once './config/database.php';

class TramiteController {
    private $database;

    public function __construct() {
        $this->database = new Database();
    }

    // ------------------------------------------------------
    public function insertTramite(
        $tram_nombretramite,
        $tram_descripcion,
        $tram_codigo,
        $tram_tipomonto,
        $tram_monto,
        $tram_plazo,
        $tram_duracion,
        $tram_comentario,
        $tram_requisito,
        $tram_oficina_id,
        $tram_categoria
    ) {
        // Consulta SQL con placeholders
        $query = "INSERT INTO siga_tramite (
                    tram_nombretramite,
                    tram_descripcion,
                    tram_codigo,
                    tram_tipomonto,
                    tram_monto,
                    tram_plazo,
                    tram_duracion,
                    tram_comentario,
                    tram_requisito,
                    tram_oficina_id,
                    tram_categoria
                  ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                  ) RETURNING tram_id";
        try {
            // Ejecutar la consulta con la clase Database
            $result = $this->database->insertar($query, [
                $tram_nombretramite,
                $tram_descripcion,
                $tram_codigo,
                $tram_tipomonto,
                $tram_monto,
                $tram_plazo,
                $tram_duracion,
                $tram_comentario,
                $tram_requisito,
                $tram_oficina_id,
                $tram_categoria
            ],'siga_tramite_tram_id_seq'); // Asegúrate de que esta secuencia exista en tu base de datos
    
            if ($result) {
                response::success($result, 'Trámite insertado correctamente');
            } else {
                response::error('Error al insertar el trámite');
            }
        } catch (PDOException $e) {
            // Mostrar el mensaje de error en detalle
            response::error('Error al insertar el trámite: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function getTramite($tram_id) {
        $query = "SELECT 
                    tram_id AS id,
                    tram_nombretramite AS nombretramite,
                    tram_descripcion AS descripcion,
                    tram_codigo AS codigo,
                    tram_tipomonto AS tipomonto,
                    tram_monto AS monto,
                    tram_plazo AS plazo,
                    tram_duracion AS duracion,
                    tram_comentario AS comentario,
                    tram_requisito AS requisito,
                    tram_oficina_id AS oficina_id,
                    tram_categoria AS categoria
                FROM siga_tramite 
                WHERE tram_id = ?";

        try {
            $result = $this->database->ejecutarConsulta($query, [$tram_id]);

            if ($result) {
                response::success($result[0], 'Consulta de trámite exitosa');
            } else {
                response::error('No se encontró el trámite');
            }
        } catch (PDOException $e) {
            response::error('Error al consultar el trámite: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function getTramites() {
        $query = "SELECT
                    tram_id AS id,
                    tram_nombretramite AS nombretramite,
                    tram_descripcion AS descripcion,
                    tram_codigo AS codigo,
                    tram_tipomonto AS tipomonto,
                    tram_monto AS monto,
                    tram_plazo AS plazo,
                    tram_duracion AS duracion,
                    tram_comentario AS comentario,
                    tram_requisito AS requisito,
                    tram_oficina_id AS oficina_id,
                    tram_categoria AS categoria
                FROM siga_tramite";

        try {
            $result = $this->database->ejecutarConsulta($query);

            if ($result) {
                response::success($result, 'Lista de trámites obtenida correctamente');
            } else {
                response::error('No se encontraron trámites registrados');
            }
        } catch (PDOException $e) {
            response::error('Error al consultar los trámites: ' . $e->getMessage());
        }
    }

     // ------------------------------------------------------
     public function getTramitesOficina($id_oficina) {
        $query = "SELECT
                    tram_id AS id,
                    tram_nombretramite AS nombretramite,
                    tram_descripcion AS descripcion,
                    tram_codigo AS codigo,
                    tram_tipomonto AS tipomonto,
                    tram_monto AS monto,
                    tram_plazo AS plazo,
                    tram_duracion AS duracion,
                    tram_comentario AS comentario,
                    tram_requisito AS requisito,
                    tram_oficina_id AS oficina_id,
                    tram_categoria AS categoria
                FROM siga_tramite
                WHERE tram_oficina_id='".$id_oficina."'";
        try {
            $result = $this->database->ejecutarConsulta($query);

            if ($result) {
                response::success($result, 'Lista de trámites obtenida correctamente');
            } else {
                response::error('No se encontraron trámites registrados');
            }
        } catch (PDOException $e) {
            response::error('Error al consultar los trámites: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function updateTramite(
        $tram_id,
        $tram_nombretramite,
        $tram_descripcion,
        $tram_codigo,
        $tram_tipomonto,
        $tram_monto,
        $tram_plazo,
        $tram_duracion,
        $tram_comentario,
        $tram_requisito,
        $tram_oficina_id,
        $tram_categoria
    ) {
        $query = "UPDATE siga_tramite SET 
                    tram_nombretramite = ?,
                    tram_descripcion = ?,
                    tram_codigo = ?,
                    tram_tipomonto = ?,
                    tram_monto = ?,
                    tram_plazo = ?,
                    tram_duracion = ?,
                    tram_comentario = ?,
                    tram_requisito = ?,
                    tram_oficina_id = ?,
                    tram_categoria  = ?
                WHERE tram_id = ?";

        try {
            $result = $this->database->ejecutarActualizacion($query, [
                $tram_nombretramite,
                $tram_descripcion,
                $tram_codigo,
                $tram_tipomonto,
                $tram_monto,
                $tram_plazo,
                $tram_duracion,
                $tram_comentario,
                $tram_requisito,
                $tram_oficina_id,
                $tram_categoria,
                $tram_id
            ]);

            if ($result) {
                response::success($result, 'Trámite actualizado correctamente');
            } else {
                response::error('Error al actualizar el trámite');
            }
        } catch (PDOException $e) {
            response::error('Error al actualizar el trámite: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------
    public function deleteTramite($tram_id) {
        $query = "DELETE FROM siga_tramite WHERE tram_id = ?";

        try {
            $result = $this->database->ejecutarActualizacion($query, [$tram_id]);

            if ($result) {
                response::success(null, 'Trámite eliminado correctamente');
            } else {
                response::error('Error al eliminar el trámite');
            }
        } catch (PDOException $e) {
            response::error('Error al eliminar el trámite: ' . $e->getMessage());
        }
    }

    //----------------------------------------------------------------------------
}
?>
