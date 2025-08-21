<?php
include_once './utils/response.php';
include_once './config/database.php';
include_once 'controller_mailserver.php';

// $mailserver    = new email_server();   

class UserCasillaController {
    private $database;

    

    public function __construct() {
        $this->database = new Database();
        
    }   

    // Método para el login
    public function loginUserCasilla($user, $pass) {
        $query = "SELECT user_usuario, user_password, user_estado, user_id
                  FROM siga_usuariocasilla
                  WHERE user_usuario = ?";
    
        try {
            $result = $this->database->ejecutarConsulta($query, [$user]);
    
            if (count($result) > 0) {
                $row = $result[0];
                
                if ($this->verificar_contrasena($pass, $row['user_password'])) {
                    if ($row['user_estado']) {
                        $userCasilla = $this->getUserCasilla($row['user_id']);
                        return $userCasilla;
                    } else {
                        Response::error("Su acceso ha sido suspendido");
                    }
                } else {
                    Response::error("La clave es incorrecta");
                }
            } else {
                Response::error("El usuario no existe");
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }
    
    function encriptar($contrasena) {
        return password_hash($contrasena, PASSWORD_DEFAULT);
    }

    function verificar_contrasena($contrasena, $hash) {
        return password_verify($contrasena, $hash);
    }

    // Insertar usuario en casilla
    public function registrarUserCasilla(
        $tipodocumento, 
        $numdocumento, 
        $nombre, 
        $apellidopat, 
        $apellidomat, 
        $razonsocial, 
        $celular, 
        $correo, 
        $password
    ) {
        $query = "SELECT usuariocasilla_insertar(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $tipopersona        = ($tipodocumento == "RUC") ? "Jurídica" : "Natural";
        $nombreusuario      = ($tipodocumento == "RUC") ? $razonsocial : $nombre." ".$apellidopat." ".$apellidomat;
        $codigoverificacion = rand(1000, 9999);
        $usuario            = $numdocumento;
        $rol                = "Externo";
        $encrypted_password = $this->encriptar($password);
        $nombrecompleto=$nombre." ".$apellidopat." ".$apellidomat;

       
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
                $celular,
                $correo,
                $codigoverificacion,
                $nombreusuario,
                $usuario,
                $encrypted_password,
                $rol
            ]);

            $respuesta = $result[0]['usuariocasilla_insertar'];
            
            // Validar el resultado de la función
            if ($respuesta > 0) {
                $mailserver = new email_server();
                $mailserver->sendmail($codigoverificacion, $nombrecompleto, $correo);
                Response::success($respuesta, 'Usuario registrado correctamente');
            } elseif ($respuesta == -1) {
                Response::error('Error: El nombre de usuario ya existe.');
            } elseif ($respuesta == -2) {
                Response::error('Error: El correo electrónico ya existe.');
            } elseif ($respuesta == -4) {
                Response::error('Error: Campos requeridos no proporcionados.');
            } elseif ($respuesta == -5) {
                Response::error('Error inesperado al registrar el usuario.');
            } else {
                Response::error('Error desconocido al registrar el usuario.');
            }

        } catch (PDOException $e) {
            // Manejar errores de base de datos
            Response::error('Error de base de datos: ' . $e->getMessage());
        } catch (Exception $e) {
            // Manejar cualquier otro tipo de error
            Response::error('Error inesperado: ' . $e->getMessage());
        }
    }


    // Obtener usuario por ID
    public function getUserCasilla($user_id) {
        $query = "SELECT * FROM usuariocasilla_obtenerdatos(?)";
        try {
            $result = $this->database->ejecutarConsulta($query, [$user_id]);
            if ($result) {
                Response::success($result[0], 'Consulta de usuario exitosa');
            } else {
                Response::error('No se encontró el usuario');
            }
        } catch (PDOException $e) {
            Response::error("Error en la base de datos: " . $e->getMessage());
        }
    }
    


    // Actualizar datos de un usuario
    public function updateUserCasilla(
        $user_id, 
        $tipopersona, 
        $tipodocumento, 
        $numdocumento, 
        $nombre, 
        $apellidopat, 
        $apellidomat, 
        $razonsocial, 
        $celular, 
        $correo, 
        $rol
    ) {
        $query = "SELECT usuariocasilla_actualizardatos(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [
                $user_id,
                $tipopersona,
                $tipodocumento,
                $numdocumento,
                $nombre,
                $apellidopat,
                $apellidomat,
                $razonsocial,
                $celular,
                $correo,
                $rol
            ]);
            $resultado = $result[0]['resultado'];
            if ($resultado == 1) {
                Response::success($resultado, 'Usuario actualizado correctamente');
            } elseif ($resultado == 0) {
                Response::error('Usuario no encontrado');
            } elseif ($resultado == -1) {
                Response::error('Error: El correo electrónico ya existe.');
            } elseif ($resultado == -3) {
                Response::error('Error: Campos requeridos no proporcionados.');
            } elseif ($resultado == -4) {
                Response::error('Error inesperado al actualizar el usuario.');
            } else {
                Response::error('Error desconocido al actualizar el usuario.');
            }
        } catch (PDOException $e) {
            Response::error('Error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    // Verificar cuenta del usuario
    public function verifyUserCasilla($user_id, $codigo_verificacion) {
        $query = "SELECT usuariocasilla_verificarcuenta(?, ?) as resultado";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$user_id, $codigo_verificacion]);
            
            $resultado = $result[0]['resultado'];
            if ($resultado == 1) {
                Response::success($resultado, 'Usuario verificado correctamente');
            } elseif ($resultado == 0) {
                Response::error('El código ingresado no es válido o el usuario no existe');
            } elseif ($resultado == -1) {
                Response::error('Error inesperado al verificar el usuario');
            } else {
                Response::error('Error desconocido');
            }
        } catch (PDOException $e) {
            Response::error('Error al verificar el usuario: ' . $e->getMessage());
        }
    }

    // Actualizar la contraseña de un usuario
    public function updateUserPassword($user_id, $new_password) {
        $query = "SELECT usuariocasilla_actualizarpassword(?, ?)";
        $encrypted_password = $this->encriptar($new_password);
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$user_id, $encrypted_password]);
            Response::success($result, 'Contraseña actualizada correctamente');
        } catch (PDOException $e) {
            Response::error('Error al actualizar la contraseña: ' . $e->getMessage());
        }
    }

    // Desactivar un usuario
    public function deactivateUserCasilla($user_id) {
        $query = "SELECT usuariocasilla_desactivar(?)";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$user_id]);
            Response::success($result, 'Usuario desactivado correctamente');
        } catch (PDOException $e) {
            Response::error('Error al desactivar el usuario: ' . $e->getMessage());
        }
    }

    // Eliminar usuario
    public function deleteUserCasilla($user_id) {
        $query = "SELECT usuariocasilla_eliminar(?)";
        
        try {
            $result = $this->database->ejecutarConsulta($query, [$user_id]);
            $resultado = $result[0]['resultado'];
            if ($resultado == 1) {
                Response::success($resultado, 'Usuario eliminado correctamente');
            } elseif ($resultado == 0) {
                Response::error('Usuario no encontrado');
            } elseif ($resultado == -1) {
                Response::error('No se puede eliminar el usuario porque hay registros dependientes');
            } elseif ($resultado == -2) {
                Response::error('Error inesperado al eliminar el usuario');
            } else {
                Response::error('Error desconocido');
            }
        } catch (PDOException $e) {
            Response::error('Error al eliminar el usuario: ' . $e->getMessage());
        }
    }
}
?>
