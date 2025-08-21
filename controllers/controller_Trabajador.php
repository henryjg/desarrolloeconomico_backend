<?php
include_once './utils/response.php';
include_once './config/database.php';

class TrabajadorController
{
    private $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    // Login
    public function login($user, $pass)
    {
        $query = "SELECT * FROM trabajador_obtener_credenciales(:usuario)";

        try {
            $result = $this->database->ejecutarConsulta($query, ['usuario' => $user]);

            if (count($result) > 0) {
                $row = $result[0];
                $hashedPassword = $row['password'];
                $esActivo = $row['esactivo'];
                $userId = $row['id'];

                if ($this->verificar_contrasena($pass, $hashedPassword)) {
                    if ($esActivo) {
                        $trabajador = $this->getTrabajador($userId);
                        return $trabajador;
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

    function encriptar($contrasena)
    {
        return password_hash($contrasena, PASSWORD_DEFAULT);
    }

    function verificar_contrasena($contrasena, $hash)
    {
        return password_verify($contrasena, $hash);
    }

    // ---------------------------------------------    
    public function insertTrabajador($dni, $nombre, $apellidopat, $apellidomat, $email, $telefono, $celular, $fotourl, $cargo, $usuario, $password, $fnacimiento, $oficina_id, $rol_id)
    {
        $query = "SELECT trabajador_insertar(:dni, :nombre, :apellidopat, :apellidomat, :email, :telefono, :celular, :fotourl, :cargo, :usuario, :password, :fnacimiento, :oficina_id, :rol_id) AS new_tra_id";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                'dni' => $dni,
                'nombre' => $nombre,
                'apellidopat' => $apellidopat,
                'apellidomat' => $apellidomat,
                'email' => $email,
                'telefono' => $telefono,
                'celular' => $celular,
                'fotourl' => $fotourl,
                'cargo' => $cargo,
                'usuario' => $usuario,
                'password' => $this->encriptar($password),
                'fnacimiento' => $fnacimiento,
                'oficina_id' => $oficina_id,
                'rol_id' => $rol_id
            ]);

            if ($result) {
                $newTraId = $result[0]['new_tra_id'];

                switch ($newTraId) {
                    case -1:
                        Response::error('Error: El DNI o el usuario ya existen en el sistema.');
                        break;
                    case -2:
                        Response::error('Error: Algunos campos obligatorios están vacíos.');
                        break;
                    case -3:
                        Response::error('Error inesperado durante la inserción del trabajador.');
                        break;
                    default:
                        Response::success($newTraId, 'Trabajador insertado correctamente con ID: ' . $newTraId);
                }
            } else {
                Response::error('Error desconocido al insertar el trabajador.');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al insertar el trabajador: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------    
    // Obtener un Trabajador por ID
    public function getTrabajador($id)
    {
        $query = "SELECT * FROM trabajador_obtenerdatos(:id)";
        $result = $this->database->ejecutarConsulta($query, ['id' => $id]);
        if ($result) {
            Response::success($result[0], 'Consulta de trabajador exitosa');
        } else {
            Response::error('No se encontró el trabajador');
        }
    }

    // Listar Trabajadores Activos
    public function getTrabajadores()
    {
        $query = "SELECT * FROM trabajador_obtenerlista()";
        $result = $this->database->ejecutarConsulta($query);
        if ($result) {
            Response::success($result, 'Lista de trabajadores obtenida correctamente');
        } else {
            Response::error('No se encontraron trabajadores registrados');
        }
    }
    // Listar Trabajadores Desactivados
    public function getTrabajadores_eliminados()
    {
        $query = "SELECT * FROM trabajador_obtenereliminados()";
        $result = $this->database->ejecutarConsulta($query);
        if ($result) {
            Response::success($result, 'Lista de trabajadores obtenida correctamente');
        } else {
            Response::error('No se encontraron trabajadores registrados');
        }
    }

    // Actualizar datos del Trabajador
    public function updateTrabajador($tra_id, $tra_dni, $tra_nombre, $tra_apellidopat, $tra_apellidomat, $tra_email, $tra_telefono, $tra_celular, $tra_fotourl, $tra_cargo, $tra_esactivo, $tra_fnacimiento, $tra_oficina_id, $tra_rol_id)
    {
        $query = "SELECT trabajador_actualizardatos(:tra_id, :dni, :nombre, :apellidopat, :apellidomat, :email, :telefono, :celular, :fotourl, :cargo, :esactivo, :fnacimiento, :oficina_id, :rol_id) AS rows_affected";

        try {
            $result = $this->database->ejecutarConsulta($query, [
                'tra_id' => $tra_id,
                'dni' => $tra_dni,
                'nombre' => $tra_nombre,
                'apellidopat' => $tra_apellidopat,
                'apellidomat' => $tra_apellidomat,
                'email' => $tra_email,
                'telefono' => $tra_telefono,
                'celular' => $tra_celular,
                'fotourl' => $tra_fotourl,
                'cargo' => $tra_cargo,
                'esactivo' => $tra_esactivo,
                'fnacimiento' => $tra_fnacimiento,
                'oficina_id' => $tra_oficina_id,
                'rol_id' => $tra_rol_id
            ]);

            if ($result) {
                $rowsAffected = $result[0]['rows_affected'];

                // Manejo de los códigos de retorno específicos de la función PGSQL
                switch ($rowsAffected) {
                    case -1:
                        Response::error('Error: No se encontró un trabajador con el ID especificado.');
                        break;
                    case -2:
                        Response::error('Error: El DNI o algún otro campo único ya existe en el sistema.');
                        break;
                    case -3:
                        Response::error('Error inesperado durante la actualización del trabajador.');
                        break;
                    default:
                        Response::success($rowsAffected, 'Trabajador actualizado correctamente. Filas afectadas: ' . $rowsAffected);
                }
            } else {
                Response::error('Error desconocido al actualizar el trabajador.');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al actualizar el trabajador: ' . $e->getMessage());
        }
    }


    // Actualizar Estado del Trabajador
    public function updateEstado($id, $esactivo)
    {
        $query = "SELECT trabajador_actualizarestado(:id, :esactivo) as rows_affected";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                'id' => $id,
                'esactivo' => $esactivo
            ]);
            $rowsAffected = $result[0]['rows_affected'];
            if($rowsAffected>0){
                if($esactivo){
                    Response::success($rowsAffected, 'Se activó al trabajador');
                }else{
                    Response::success($rowsAffected, 'se desactivó al trabajador');
                }
            }else{
                Response::error('Error al actualizar el estado del trabajador');
            }
            
        } catch (PDOException $e) {
            Response::error('Error al actualizar el estado del trabajador: ' . $e->getMessage());
        }
    }

    public function updatePassword($id, $password)
    {
        $query = "SELECT trabajador_actualizarpassword(:id, :password) as resultado";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                'id' => $id,
                'password' => $this->encriptar($password)
            ]);
            $res = $result[0]['resultado'];
            Response::success($res, 'Contraseña actualizada correctamente');
        } catch (PDOException $e) {
            Response::error('Error al actualizar la contraseña: ' . $e->getMessage());
        }
    }

    public function updateFoto($id, $fotourl)
    {
        $query = "SELECT trabajador_actualizarfotoperfil(:id, :fotourl)";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                'id' => $id,
                'fotourl' => $fotourl
            ]);
            Response::success($result, 'Foto del trabajador actualizada correctamente');
        } catch (PDOException $e) {
            Response::error('Error al actualizar la foto del trabajador: ' . $e->getMessage());
        }
    }

    
    public function detecta_extension($mi_extension) {
        $ext = explode(".", $mi_extension);
        return end($ext);
    }

    // ------------------------------------------------------
    public function Subir_Imagen($file,$ruta) {
        $url = $this->subir_archivo($file,$ruta );
        if($url=="NOPERMITIDO"){
            response::error('Archivo no permitido, seleccione un archivo de imágen válido');
        }else if ($url!='') {
            response::success($url, 'Imágen subida correctamente');
        } else {
            response::error('Error en la subida de la fotografía');
        }
    }

    public function subir_archivo($file, $ruta) {
        $ruta_archivo = "";
        if ($file && isset($file['tmp_name']) && $file['tmp_name'] != "") {
            if (!file_exists($ruta)) {
                mkdir($ruta, 0777, true);
            }

            $nuevo_nombre = "file_" . rand(1000000, 9999999);
            $extension = $this->detecta_extension(basename($file['name']));

            if (!$extension) {
                return "Extension no válida";
            }

            $nuevo_nombre_completo = $nuevo_nombre . '.' . $extension;
            $uploadfile = $ruta . $nuevo_nombre_completo;

            // Lista de tipos permitidos
            $permitidos = array(
                "image/bmp", 
                "image/jpg", 
                "image/jpeg", 
                "image/png"
            );
            //---------------------------------
            if (in_array($file['type'], $permitidos)) {
                if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
                    if (exif_imagetype($uploadfile)) {
                        switch ($file['type']) {
                            case 'image/bmp':
                                $imagen = imagecreatefrombmp($uploadfile);
                                break;
                            case 'image/jpg':
                            case 'image/jpeg':
                                $imagen = imagecreatefromjpeg($uploadfile);
                                break;
                            case 'image/png':
                                $imagen = imagecreatefrompng($uploadfile);
                                break;
                            default:
                                $imagen = false;
                        }

                        if ($imagen !== false) {
                            // Obtener dimensiones de la imagen
                            $ancho_original = imagesx($imagen);
                            $alto_original = imagesy($imagen);

                            // Redimensionar si el ancho es mayor que 1200px
                            $max_ancho = 1200;
                            if ($ancho_original > $max_ancho) {
                                $ratio = $alto_original / $ancho_original;
                                $nuevo_ancho = $max_ancho;
                                $nuevo_alto = $max_ancho * $ratio;

                                // Crear una imagen redimensionada
                                $imagen_redimensionada = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
                                imagecopyresampled($imagen_redimensionada, $imagen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho_original, $alto_original);

                                // Guardar la imagen redimensionada
                                switch ($file['type']) {
                                    case 'image/bmp':
                                        imagebmp($imagen_redimensionada, $uploadfile);
                                        break;
                                    case 'image/jpg':
                                    case 'image/jpeg':
                                        imagejpeg($imagen_redimensionada, $uploadfile);
                                        break;
                                    case 'image/png':
                                        imagepng($imagen_redimensionada, $uploadfile);
                                        break;                                  
                                }

                                imagedestroy($imagen_redimensionada);
                            }
                            imagedestroy($imagen);
                        } else {
                            return "Error al procesar la imagen";
                        }
                        $ruta_archivo = $ruta . $nuevo_nombre_completo;
                    } else {
                        return "El archivo no es una imagen válida.";
                    }
                } else {
                    return "Error al mover el archivo";
                }
            } else {
                return "Formato de archivo no permitido";
            }
        }
        return $ruta_archivo;
    }
}
