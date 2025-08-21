<?php
include_once './utils/response.php';
include_once './config/database.php';

class TipoDocumentoController {
    private $database;

    public function __construct() {
        $this->database = new Database();
    }

    // Método para obtener todos los tipos de documento
    public function get_documentos() {
        $query = "SELECT tipo_id AS id, tipo_nombre as nombre FROM siga_tipodocumento ORDER BY tipo_nombre";

        try {
            $result = $this->database->ejecutarConsulta($query);

            if ($result) {
                Response::success($result, 'Lista de tipos de documentos obtenida correctamente');
            } else {
                Response::error('No se encontraron tipos de documentos');
            }
        } catch (PDOException $e) {
            Response::error('Error al obtener los tipos de documentos: ' . $e->getMessage());
        }
    }

 
    public function insertTipoDocumento($data) {
        $query = "INSERT INTO siga_tipodocumento (tipo_nombre, tipo_estado) VALUES (?, true)";

        try {
            $result = $this->database->ejecutarConsulta($query, [$data['tipo_nombre']]);

            if ($result) {
                Response::success(null, 'Tipo de documento insertado correctamente');
            } else {
                Response::error('Error al insertar el tipo de documento');
            }
        } catch (PDOException $e) {
            Response::error('Error al insertar el tipo de documento: ' . $e->getMessage());
        }
    }

 
 
    public function updateTipoDocumento($data) {
        $query = "UPDATE siga_tipodocumento SET tipo_nombre = ?, tipo_estado = ? WHERE tipo_id = ?";

        try {
            $result = $this->database->ejecutarConsulta($query, [$data['tipo_nombre'], $data['tipo_estado'], $data['tipo_id']]);

            if ($result) {
                Response::success(null, 'Tipo de documento actualizado correctamente');
            } else {
                Response::error('Error al actualizar el tipo de documento');
            }
        } catch (PDOException $e) {
            Response::error('Error al actualizar el tipo de documento: ' . $e->getMessage());
        }
    }

    // Método para eliminar un tipo de documento
    public function deleteTipoDocumento($tipo_id) {
        $query = "DELETE FROM siga_tipodocumento WHERE tipo_id = ?";

        try {
            $result = $this->database->ejecutarConsulta($query, [$tipo_id]);

            if ($result) {
                Response::success(null, 'Tipo de documento eliminado correctamente');
            } else {
                Response::error('Error al eliminar el tipo de documento');
            }
        } catch (PDOException $e) {
            Response::error('Error al eliminar el tipo de documento: ' . $e->getMessage());
        }
    }
}
?>
