<?php
include_once './utils/response.php';
include_once './config/database.php';

class UbigeoController {
    private $database;

    public function __construct() {
        $this->database = new Database();
    }

    // Método para obtener la estructura jerárquica de Ubigeo
    public function get_ubigeo_json() {
        $query = "SELECT ubi_id, ubi_departamento, ubi_provincia, ubi_distrito, ubi_latitud, ubi_longitud FROM siga_ubigeo ORDER BY ubi_departamento, ubi_provincia, ubi_distrito";
    
        try {
            $result = $this->database->ejecutarConsulta($query);
    
            if ($result) {
                $estructura = [];
    
                foreach ($result as $row) {
                    $departamento = $row['ubi_departamento'];
                    $provincia = $row['ubi_provincia'];
                    $distrito = $row['ubi_distrito'];
                    
                    // Si no existe el departamento, inicialízalo con la etiqueta 'departamento'
                    if (!isset($estructura[$departamento])) {
                        $estructura[$departamento] = [
                            'tipo' => 'departamento',
                            'nombre' => $departamento,
                            'provincias' => []
                        ];
                    }
                    
                    // Si no existe la provincia dentro del departamento, inicialízala con la etiqueta 'provincia'
                    if (!isset($estructura[$departamento]['provincias'][$provincia])) {
                        $estructura[$departamento]['provincias'][$provincia] = [
                            'tipo' => 'provincia',
                            'nombre' => $provincia,
                            'distritos' => []
                        ];
                    }
                    
                    // Agrega el distrito a la provincia con su información correspondiente
                    $estructura[$departamento]['provincias'][$provincia]['distritos'][] = [
                        'id' => $row['ubi_id'],
                        'distrito' => $distrito,
                        'latitud' => $row['ubi_latitud'],
                        'longitud' => $row['ubi_longitud']
                    ];
                }
    
                Response::success($estructura, 'Estructura jerárquica de Ubigeo obtenida correctamente');
            } else {
                Response::error('No se encontraron datos de Ubigeo');
            }
        } catch (PDOException $e) {
            Response::error('Error al obtener los datos de Ubigeo: ' . $e->getMessage());
        }
    }
    

    // Método para obtener el Ubigeo de un departamento específico
    public function get_ubigeo_lista() {
        $query = "SELECT 
                    ubi_id AS id, 
                    CONCAT(ubi_departamento, ' - ', ubi_provincia, ' - ', ubi_distrito) AS ubigeo,
                    ubi_departamento AS departamento, 
                    ubi_provincia AS provincia, 
                    ubi_distrito AS distrito
                  FROM siga_ubigeo
                  ORDER BY ubi_departamento ASC";

        try {
            // Ejecutar consulta con parámetro de departamento
            $result = $this->database->ejecutarConsulta($query);

            if ($result) {
                $ubigeos = [];
                foreach ($result as $ubigeo) {
                    $ubigeos[] = $ubigeo;
                }
                Response::success($ubigeos, 'Lista de ubigeos obtenida correctamente');
            } else {
                Response::error('No se encontraron ubigeos registrados');
            }
        } catch (PDOException $e) {
            Response::error('Error al obtener los ubigeos: ' . $e->getMessage());
        }
    }




    public function obtenerDepartamentos() {
        $query = "SELECT DISTINCT ubi_departamento FROM siga_ubigeo ORDER BY ubi_departamento";
    
        try {
            $result = $this->database->ejecutarConsulta($query);
    
            if ($result) {
                $departamentos = array_map(fn($row) => $row['ubi_departamento'], $result);
                Response::success($departamentos, 'Lista de departamentos obtenida correctamente');
            } else {
                Response::error('No se encontraron departamentos');
            }
        } catch (PDOException $e) {
            Response::error('Error al obtener departamentos: ' . $e->getMessage());
        }
    }

    
    public function obtenerProvincias($departamento) {
        $query = "SELECT DISTINCT ubi_provincia FROM siga_ubigeo WHERE ubi_departamento = ? ORDER BY ubi_provincia";
    
        try {
            $result = $this->database->ejecutarConsulta($query, [$departamento]);
    
            if ($result) {
                $provincias = array_map(fn($row) => $row['ubi_provincia'], $result);
                Response::success($provincias, 'Lista de provincias obtenida correctamente');
            } else {
                Response::error("No se encontraron provincias para el departamento '$departamento'");
            }
        } catch (PDOException $e) {
            Response::error('Error al obtener provincias: ' . $e->getMessage());
        }
    }


    public function obtenerDistritos($provincia) {
        $query = "SELECT ubi_id, ubi_distrito FROM siga_ubigeo WHERE ubi_provincia = ? ORDER BY ubi_distrito";
    
        try {
            $result = $this->database->ejecutarConsulta($query, [$provincia]);
    
            if ($result) {
                $distritos = array_map(function($row) {
                    return [
                        'id' => $row['ubi_id'],
                        'nombre' => $row['ubi_distrito']
                       
                    ];
                }, $result);
                
                Response::success($distritos, 'Lista de distritos obtenida correctamente');
            } else {
                Response::error("No se encontraron distritos para la provincia '$provincia'");
            }
        } catch (PDOException $e) {
            Response::error('Error al obtener distritos: ' . $e->getMessage());
        }
    }
    
    


    
}
?>
