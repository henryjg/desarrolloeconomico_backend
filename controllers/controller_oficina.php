<?php
include_once './utils/response.php';
include_once './config/database.php';

class OficinaController
{
    private $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    // Insertar oficina
    public function insertOficina($ofi_nombre, $ofi_padre_id)
    {
        // Nota: La función PostgreSQL actualizada solo usa nombre y padre_id
        $query = "SELECT oficina_insertar(:nombre, :padre_id) AS new_ofi_id";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                'nombre' => $ofi_nombre,
                'padre_id' => $ofi_padre_id
            ]);

            if ($result) {
                $newOfiId = $result[0]['new_ofi_id'];
                if ($newOfiId != -1) {
                    Response::success($newOfiId, 'Oficina insertada correctamente con ID: ' . $newOfiId);
                } else {
                    Response::error('Error al insertar la oficina');
                }
            } else {
                Response::error('Error desconocido al insertar la oficina.');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al insertar la oficina: ' . $e->getMessage());
        }
    }


    public function getOficinasArbol()
    {
        // Esta función usa consulta directa a la tabla, actualizamos para usar solo los campos disponibles
        $query = "SELECT ofi_id, ofi_nombre, ofi_padre_id FROM siga_oficina";
        $result = $this->database->ejecutarConsulta($query);

        if (!$result) {
            Response::error('No se encontraron oficinas registradas');
            return;
        }

        // Construye un array asociativo de las oficinas
        $oficinas = [];
        foreach ($result as $row) {
            $oficinas[$row['ofi_id']] = [
                'ofi_id' => $row['ofi_id'],
                'ofi_nombre' => $row['ofi_nombre'],
                'ofi_padre_id' => $row['ofi_padre_id'],
                'hijas' => []  // Inicializa el array de hijas
            ];
        }

        // Construye la jerarquía
        $arbol = [];
        foreach ($oficinas as $id => &$oficina) {
            if ($oficina['ofi_padre_id'] === null) {
                $arbol[] = &$oficina;
            } else {
                $oficinas[$oficina['ofi_padre_id']]['hijas'][] = &$oficina;
            }
        }
        unset($oficina); 
        Response::success($arbol, 'Árbol de oficinas obtenido correctamente');
    }

    // Obtener oficina por ID
    public function getOficina($ofi_id)
    {
        $query = "SELECT * FROM oficina_obtener(:id)";
        try {
            $result = $this->database->ejecutarConsulta($query, ['id' => $ofi_id]);
            if ($result) {
                Response::success($result[0], 'Consulta de oficina exitosa');
            } else {
                Response::error('No se encontró la oficina');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al obtener la oficina: ' . $e->getMessage());
        }
    }

    // Listar todas las oficinas
    public function getOficinas()
    {
        $query = "SELECT ofi_id AS id, ofi_nombre AS nombre, ofi_padre_id AS padre_id 
                  FROM siga_oficina";
        try {
            $result = $this->database->ejecutarConsulta($query);
            if ($result) {
                Response::success($result, 'Lista de oficinas obtenida correctamente');
            } else {
                Response::error('No se encontraron oficinas registradas');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al listar las oficinas: ' . $e->getMessage());
        }
    }

    // Actualizar oficina
    public function updateOficina($ofi_id, $ofi_nombre, $ofi_padre_id)
    {
        // Nota: La función PostgreSQL actualizada solo usa id, nombre y padre_id
        $query = "SELECT oficina_actualizar(:id, :nombre, :padre_id) AS rows_affected";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                'id' => $ofi_id,
                'nombre' => $ofi_nombre,
                'padre_id' => $ofi_padre_id
            ]);

            if ($result) {
                $rowsAffected = $result[0]['rows_affected'];
                if ($rowsAffected > 0) {
                    Response::success($rowsAffected, 'Oficina actualizada correctamente.');
                } elseif ($rowsAffected == -1) {
                    Response::error('No se encontró la oficina para actualizar.');
                } else {
                    Response::error('Error desconocido al actualizar la oficina.');
                }
            } else {
                Response::error('Error desconocido al actualizar la oficina.');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al actualizar la oficina: ' . $e->getMessage());
        }
    }

    // Eliminar oficina
    public function deleteOficina($ofi_id)
    {
        $query = "SELECT oficina_eliminar(:id) AS rows_affected";
        try {
            $result = $this->database->ejecutarConsulta($query, ['id' => $ofi_id]);
            if ($result) {
                $rowsAffected = $result[0]['rows_affected'];
                if ($rowsAffected > 0) {
                    Response::success($rowsAffected, 'Oficina eliminada correctamente.');
                } else {
                    Response::error('No se encontró la oficina para eliminar.');
                }
            } else {
                Response::error('Error desconocido al eliminar la oficina.');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al eliminar la oficina: ' . $e->getMessage());
        }
    }
}
?>
