<?php
include_once './utils/response.php';
include_once './config/database.php';

class AdministradoController {
    private $database;

    public function __construct() {
        $this->database = new Database();
    }

    // Insertar administrado con restricciones
    public function registrarAdministrado(
        $tipopersona, 
        $tipodocumento, 
        $numdocumento, 
        $nombre, 
        $apellidopat, 
        $apellidomat, 
        $razonsocial, 
        $direccion, 
        $celular, 
        $correo, 
        $ubigeoid,
    ) {
        $query = "SELECT administrado_insertar(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            // Ejecutar consulta
            $result = $this->database->ejecutarConsulta($query, [
                $tipopersona,
                $tipodocumento,
                $numdocumento,
                $nombre,
                $apellidopat,
                $apellidomat,
                $razonsocial,
                $direccion,
                $celular,
                $correo,
                $ubigeoid
            ]);
    
            // Captura el resultado de la función
            $respuesta = $result[0]['administrado_insertar'];
           
            // Validar el resultado de la función
            if ($respuesta > 1) {
                Response::success($respuesta, 'Administrado insertado correctamente');
                // $respuestabuzon = $this->insertBuzon_administrado("Administrado",
                //                                                    $nombre.' '.$apellidopat.' '.$apellidomat,
                //                                                    $nombre.' '.$apellidopat.' '.$apellidomat,
                //                                                    $respuesta);
                // if($respuestabuzon){
                //     Response::success($respuesta, 'Administrado insertado correctamente');
                // }else{
                //     Response::error('No se creo buzón del Administrado.');
                // }
                
            } elseif ($respuesta == -1) {
                Response::error('Error: El número de documento ya existe.');
            } elseif ($respuesta == -2) {
                Response::error('Error: Campos requeridos no proporcionados (violación de NOT NULL).');
            } else {
                Response::error('Error desconocido al insertar el administrado.');
            }
    
        } catch (PDOException $e) {
            // Manejar errores de base de datos
            Response::error('Error de base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            // Manejar cualquier otro tipo de error
            Response::error('Error inesperado: ' . $e->getMessage());
        }
    }

    public function insertBuzon_administrado($buzon_tipo, $buzon_nombre, $buzon_responsable, $buzon_idadministrado) {
        $query = "INSERT INTO public.siga_buzon (
            buzon_tipo, 
            buzon_nombre, 
            buzon_responsable, 
            buzon_idadministrado
        ) VALUES (?, ?, ?, ?) 
        RETURNING buzon_id";
        
        try {
            $params = [
                $buzon_tipo,
                $buzon_nombre,
                $buzon_responsable,
                $buzon_idadministrado
            ];
            
            $result = $this->database->ejecutarConsulta($query, $params);
            
            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            if ($e->getCode() == '23505') {
                return false;
            } else {
                return false;
            }
        }
    }
    
    

    // Obtener administrado por ID
    public function getAdministrado($adm_id) {
        $query = "SELECT * FROM administrado_obtenerdatos(?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [$adm_id]);
            if ($result) {
                Response::success($result[0], 'Consulta de administrado exitosa');
            } else {
                Response::error('No se encontró el administrado');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    // Obtener administrado por ID
    public function getAdministrado_numdoc($doc) {
        $query = "SELECT * FROM administrado_obtenerdatos_pordocumento(?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [$doc]);
            if ($result) {
                Response::success($result[0], 'Consulta de administrado exitosa');
            } else {
                Response::error('No se encontró el administrado');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    public function getAdministrado_busquedanombre($nombre, $apellido) {
        // Base de la consulta
        $query = "SELECT sa.adm_id AS id,
                        sa.adm_tipopersona AS tipopersona,
                        sa.adm_tipodocumento AS tipodocumento,
                        sa.adm_numdocumento AS numdocumento,
                        sa.adm_nombre AS nombre,
                        sa.adm_apellidopat AS apellidopat,
                        sa.adm_apellidomat AS apellidomat,
                        sa.adm_razonsocial AS razonsocial,
                        sa.adm_direccion AS direccion,
                        sa.adm_celular AS celular,
                        sa.adm_correo AS correo,
                        sa.adm_ubigeoid AS ubigeoid,
                        sa.adm_fecharegistro AS fecharegistro,
                        sa.adm_estado AS estado
                  FROM siga_administrado sa
                  WHERE 1=1"; // "1=1" para simplificar la concatenación de condiciones
    
        // Lista de parámetros para la consulta
        $params = [];
    
        // Agregar condiciones dinámicas si los argumentos no están vacíos
        if (!empty($nombre)) {
            $query .= " AND (adm_nombre ILIKE ? OR adm_razonsocial ILIKE ?)";
            $params[] = '%' . $nombre . '%';
            $params[] = '%' . $nombre . '%'; // Agregar el parámetro para razonsocial
        }
    
        if (!empty($apellido)) {
            $query .= " AND adm_apellidopat ILIKE ?";
            $params[] = '%' . $apellido . '%';
        }
    
        try {
            // Ejecutar la consulta con los parámetros
            $result = $this->database->ejecutarConsulta($query, $params);
    
            // Verificar si se encontraron resultados
            if ($result) {
                Response::success($result, 'Consulta de administrado exitosa');
            } else {
                Response::error('No se encontró el administrado');
            }
        } catch (PDOException $e) {
            // Manejo de errores de la base de datos
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }
    

    // Obtener todos los administrados
    public function listarAdministrados() {
        $query = "SELECT * FROM administrado_listartabla()";
        try {
            $result = $this->database->ejecutarConsulta($query);
            if ($result) {
                Response::success($result, 'Lista de administrados obtenida correctamente');
            } else {
                Response::error('No se encontraron administrados registrados');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }

    // Actualizar datos de un administrado
    public function updateAdministrado(
        $adm_id, 
        $tipopersona, 
        $tipodocumento, 
        $numdocumento, 
        $nombre, 
        $apellidopat, 
        $apellidomat, 
        $razonsocial, 
        $direccion, 
        $celular, 
        $correo
    ) {
        $query = "SELECT administrado_actualizar(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $adm_id,
                $tipopersona, 
                $tipodocumento, 
                $numdocumento, 
                $nombre, 
                $apellidopat, 
                $apellidomat, 
                $razonsocial, 
                $direccion, 
                $celular, 
                $correo
            ]);
    
            $respuesta = $result[0]['administrado_actualizar'];
    
            // Validar el resultado de la función
            if ($respuesta == 1) {
                Response::success($respuesta, 'Administrado actualizado correctamente');
            } elseif ($respuesta == -1) {
                Response::error('Error: El número de documento ya existe.');
            } elseif ($respuesta == -2) {
                Response::error('Error: Algunos campos requeridos están vacíos.');
            } elseif ($respuesta == -3) {
                Response::error('Error inesperado durante la actualización.');
            } else {
                Response::error('No se pudo actualizar el administrado. Verifique si los datos cumplen con las restricciones.');
            }
    
        } catch (PDOException $e) {
            Response::error('Error de base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            Response::error('Error inesperado: ' . $e->getMessage());
        }
    }
    

    // Cambiar estado activo/inactivo de un administrado
    public function toggleActivo($adm_id, $esactivo) {
        $query = "SELECT administrado_toggleactive(?, ?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [$adm_id, $esactivo]);
            if ($result) {
                Response::success($result, 'Estado del administrado actualizado correctamente');
            } else {
                Response::error('No se pudo cambiar el estado del administrado');
            }
        } catch (PDOException $e) {
            Response::error('Error al actualizar el estado del administrado: ' . $e->getMessage());
        }
    }

    // Eliminar administrado
    public function deleteAdministrado($adm_id) {
        $query = "SELECT administrado_eliminar(?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [$adm_id]);
            if ($result) {
                Response::success($result, 'Administrado eliminado correctamente');
            } else {
                Response::error('No se pudo eliminar el administrado');
            }
        } catch (PDOException $e) {
            Response::error('Error al eliminar el administrado: ' . $e->getMessage());
        }
    }
}
?>
