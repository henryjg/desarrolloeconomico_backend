<?php
include_once './utils/response.php';
include_once './config/database.php';

class UsuarioController
{
    private $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    // Login de usuario
    public function login($usuario, $clave)
    {
        $query = "SELECT * FROM usuario_obtener_credenciales(:usuario)";
        $result = $this->database->ejecutarConsulta($query, ['usuario' => $usuario]);

        if ($result) {
            $userData = $result[0];
            $hashedPassword = $userData['password'];
            
            if (password_verify($clave, $hashedPassword)) {
                if ($userData['esactivo']) {
                    // Obtener datos completos del usuario
                    $this->getUsuario($userData['id']);
                } else {
                    Response::error('Usuario inactivo. Contacte al administrador.');
                }
            } else {
                Response::error('Contraseña incorrecta');
            }
        } else {
            Response::error('Usuario no encontrado');
        }
    }

    public function addUsuario(
                $tipodocumento,
                $numdocumento,
                $nombres,
                $apellidos,
                $correo,
                $celular,$username, $usuario, $password, $rol_id, $oficina_id){
        try {

            // Verificar si el Documento de este usuario ya existe
            $checkQuery = "SELECT COUNT(*) as count FROM siga_usuario WHERE usr_numdocumento = '$numdocumento'";
            $checkResult = $this->database->ejecutarConsulta($checkQuery);
            $row = $checkResult && isset($checkResult[0]['count']) ? $checkResult[0] : null;

            if ($row && $row['count'] > 0) {
                Response::error('Usuario ya registrado, DNI ya existe');
                return;
            }

             // Verificar si el nombre de usuario ya existe para otro usuario
            $checkQuery = "SELECT COUNT(*) as count FROM siga_usuario WHERE usr_usuario = '$usuario'";
            $checkResult = $this->database->ejecutarConsulta($checkQuery);
            $row = $checkResult && isset($checkResult[0]['count']) ? $checkResult[0] : null;

            if ($row && $row['count'] > 0) {
                Response::error('El nombre de usuario ya existe');
                return;
            }

             // Verificar si el nombre de usuario ya existe para otro usuario
            $checkQuery = "SELECT COUNT(*) as count FROM siga_usuario WHERE usr_username = '$username'";
            $checkResult = $this->database->ejecutarConsulta($checkQuery);
            $row = $checkResult && isset($checkResult[0]['count']) ? $checkResult[0] : null;

            if ($row && $row['count'] > 0) {
                Response::error('El Username (Nombre a mostrar para este usuario) ya existe');
                return;
            }

            $this->insertUsuario(
                $tipodocumento,
                $numdocumento,
                $nombres,
                $apellidos,
                $correo,
                $celular,
                $username,
                $usuario,
                $password,
                $rol_id,
                $oficina_id
            );
            Response::success('Usuario registrado correctamente');
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function addUsuario_buzon($tipodocumento,
                $numdocumento,
                $nombres,
                $apellidos,
                $correo,
                $celular,
                $username,
                $usuario,
                $password,
                $rol_id,
                $oficina_id,
                $buzon_nombre,
                $buzon_tipo,
                $buzon_sigla
            ){
        try {
            $new_iduser = $this->insertUsuario($tipodocumento,
                $numdocumento,
                $nombres,
                $apellidos,
                $correo,
                $celular,
                $username,
                $usuario,
                $password,
                $rol_id,
                $oficina_id
            );
            if ($new_iduser <= 0) {
                Response::error('Error al crear el usuario');
                return;
            }
            // Insertar buzón
            $new_idbuzon = $this->insertBuzon('Esp. '.$buzon_nombre, $buzon_tipo, $buzon_sigla);
            if ($new_idbuzon > 0) {
                // Asignar buzón al usuario recién creado
                $this->asignarBuzonUsuario($new_iduser, $new_idbuzon);
                // Response::success('Buzón asignado correctamente al usuario');
            } else {
                Response::error('Error al crear el buzón');
            }
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
    
    // --------------------------------------

    public function insertUsuario($tipodocumento,
                $numdocumento,
                $nombres,
                $apellidos,
                $correo,
                $celular,
                $username,
                $usuario,
                $password,
                $rol_id,
                $oficina_id
            ){
        // Validar campos requeridos
        if (empty($username) || empty($usuario) || empty($password) || empty($rol_id) || empty($oficina_id)) {
            throw new Exception('Todos los campos son obligatorios');
        }
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $query = "INSERT INTO siga_usuario (
            usr_username,
            usr_usuario,
            usr_password,
            usr_rol_id,
            usr_esactivo,
            usr_fechareg,
            usr_oficina_id,
            usr_tipodocumento,
            usr_numdocumento,
            usr_nombres,
            usr_apellidos,
            usr_celular,
            usr_correo
        ) VALUES (
            :username,
            :usuario,
            :password,
            :rol_id,
            true,
            NOW(),
            :oficina_id,
            :tipodocumento,
            :numdocumento,
            :nombres,
            :apellidos,
            :correo,
            :celular
        ) RETURNING usr_id";

        try {
            $params = [
                'username' => $username,
                'usuario' => $usuario,
                'password' => $hashedPassword,
                'rol_id' => $rol_id,
                'oficina_id' => $oficina_id,
                'tipodocumento' => $tipodocumento,
                'numdocumento' => $numdocumento,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'correo' => $correo,
                'celular' => $celular
            ];

            $result = $this->database->ejecutarConsulta($query, $params);
            if ($result && isset($result[0]['usr_id'])) {
                $userId = $result[0]['usr_id'];
                return $userId; 
            } else {
                return false;
            }

        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                throw new Exception('El nombre de usuario o algún dato único ya existe');
            } else {
                throw new Exception('Error en la base de datos: ' . $e->getMessage());
            }
        }
    }

    // ------------------------------------------

    public function insertBuzon($nombre, $tipobuzon, $sigla){
        // Validación existente: campos obligatorios
        if (empty($nombre) || empty($sigla)) {
            Response::error('Todos los campos son obligatorios');
            return;
        }
        $correonotificacion=null;
        $estado = true;  

        // Inserción con SQL y RETURNING para obtener el ID generado
        $query = "INSERT INTO siga_buzon (
            buzon_nombre,
            buzon_sigla,
            buzon_estado,
            buzon_correonotificaion,
            buzon_tipo,
            buzon_fechareg
        ) VALUES (
            :nombre,
            :sigla,
            :estado,
            :tipobuzon,
            :correonotificacion,
            NOW()
        ) RETURNING buzon_id";

        try {
            $params = [
                'nombre' => $nombre,
                'sigla' => $sigla,
                'estado' => $estado,
                'tipobuzon' => $tipobuzon,
                'correonotificacion' => $correonotificacion
            ];
            $result = $this->database->ejecutarConsulta($query, $params);

            if ($result && isset($result[0]['buzon_id'])) {
                return $result[0]['buzon_id']; // Retorna el ID del buzón insertado
            } else {
                return false; // No se insertó el buzón
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                Response::error('Error: El buzón ya existe');
            } else {
                Response::error('Error en la base de datos al insertar el buzon: ' . $e->getMessage());
            }
        }
    }


    // Obtener un Usuario por ID
    public function getUsuario($id)
    {
        $query = "SELECT 
                    su.usr_id AS id,
                    su.usr_username AS username,
                    su.usr_usuario AS usuario,
                    su.usr_esactivo AS esactivo,
                    su.usr_fechareg AS fechareg,
                    su.usr_rol_id AS rol_id,
                    rou.rol_nombre AS rol_nombre,
                    ofi.ofi_id AS oficina_id,
                    ofi.ofi_nombre AS oficina_nombre,
                    buz.buzon_id AS buzon_id,
                    buz.buzon_nombre AS buzon_nombre,
                    buz.buzon_sigla AS buzon_sigla,
                    (SELECT su2.usr_username 
                    FROM siga_usuario su2 
                    JOIN siga_asignacion_usuario_buzon aub2 ON su2.usr_id = aub2.asig_usrid 
                    WHERE aub2.asig_buzonid = buz.buzon_id 
                    AND su2.usr_rol_id = 1 -- Assuming rol_id = 1 indicates the responsible user
                    LIMIT 1) AS buzon_responsable
                FROM siga_usuario AS su
                LEFT JOIN siga_rolusuario rou ON su.usr_rol_id = rou.rol_id
                LEFT JOIN siga_oficina ofi ON su.usr_oficina_id = ofi.ofi_id
                LEFT JOIN siga_asignacion_usuario_buzon aub ON su.usr_id = aub.asig_usrid
                LEFT JOIN siga_buzon buz ON buz.buzon_id = aub.asig_buzonid
            WHERE su.usr_id = :id";
        $result = $this->database->ejecutarConsulta($query, ['id' => $id]);
        
        if ($result) {
            Response::success($result[0], 'Consulta de usuario exitosa');
        } else {
            Response::error('No se encontró el usuario');
        }
    }

    public function getUsuario_principaloficina($id_oficina)
    {
        $query = "SELECT  su.usr_id as id,
                    su.usr_username  as username,
                    su.usr_usuario   as usuario,
                    su.usr_esactivo  as esactivo,
                    su.usr_fechareg  as fechareg,
                    su.usr_rol_id    as rol_id,
                    rou.rol_nombre   as rol_nombre,
                    ofi.ofi_id       as oficina_id,
                    ofi.ofi_nombre   as oficina_nombre,
                    buz.buzon_id as buzon_id,
                    buz.buzon_nombre as buzon_nombre,
                    buz.buzon_sigla as buzon_sigla,
                    (SELECT su2.usr_username 
                    FROM siga_usuario su2 
                    JOIN siga_asignacion_usuario_buzon aub2 ON su2.usr_id = aub2.asig_usrid 
                    WHERE aub2.asig_buzonid = buz.buzon_id 
                    AND su2.usr_rol_id = 1 -- Assuming rol_id = 1 indicates the responsible user
                    LIMIT 1) AS buzon_responsable
            FROM siga_usuario AS su
            LEFT JOIN siga_rolusuario rou ON su.usr_rol_id = rou.rol_id
            LEFT JOIN siga_oficina ofi ON su.usr_oficina_id = ofi.ofi_id
            LEFT JOIN siga_asignacion_usuario_buzon aub ON su.usr_id = aub.asig_usrid
            LEFT JOIN siga_buzon buz ON buz.buzon_id = aub.asig_buzonid
            WHERE ofi.ofi_id = :id_oficina and rol_id=3";
        $result = $this->database->ejecutarConsulta($query, ['id_oficina' => $id_oficina]);
        
        if ($result) {
            Response::success($result[0], 'Consulta de usuario exitosa');
        } else {
            Response::error('No se encontró el usuario');
        }
    }


     // Obtener un Usuario por ID
     public function get_buzones_x_Usuario($idusuario){
         $query = "    SELECT
                            su.usr_id as id,
                            su.usr_username  as username,
                            su.usr_usuario   as usuario,
                            su.usr_esactivo  as esactivo,
                            su.usr_fechareg  as fechareg,
                            su.usr_rol_id    as rol_id,
                            rou.rol_nombre   as rol_nombre,
                            ofi.ofi_id       as oficina_id,
                            ofi.ofi_nombre   as oficina_nombre,
                            buz.buzon_id     as buzon_id,
                            buz.buzon_nombre as buzon_nombre,
                            buz.buzon_sigla  as buzon_sigla,
                            CASE WHEN su.usr_rol_id = '1' THEN su.usr_username ELSE NULL END AS buzon_responsable
                        FROM siga_usuario AS su
                        LEFT JOIN siga_rolusuario rou ON su.usr_rol_id = rou.rol_id
                        LEFT JOIN siga_oficina ofi ON su.usr_oficina_id = ofi.ofi_id
                        LEFT JOIN siga_asignacion_usuario_buzon aub ON su.usr_id = aub.asig_usrid
                        LEFT JOIN siga_buzon buz ON buz.buzon_id = aub.asig_buzonid
                        WHERE su.usr_id = :id";
         $result = $this->database->ejecutarConsulta($query, ['id' => $idusuario]);
         
         if ($result) {
             Response::success($result, 'Consulta de usuario exitosa');
         } else {
             Response::error('No se encontró el usuario');
         }
     }

      // Obtener un Usuario por ID
      public function get_buzones_x_Usuario_idbuzon($idusuario, $idbuzon){
          $query = "SELECT
                            su.usr_id as id,
                            su.usr_username  as username,
                            su.usr_usuario   as usuario,
                            su.usr_esactivo  as esactivo,
                            su.usr_fechareg  as fechareg,
                            su.usr_rol_id    as rol_id,
                            rou.rol_nombre   as rol_nombre,
                            ofi.ofi_id       as oficina_id,
                            ofi.ofi_nombre   as oficina_nombre,
                            buz.buzon_id     as buzon_id,
                            buz.buzon_nombre as buzon_nombre,
                            buz.buzon_sigla  as buzon_sigla,
                            CASE WHEN su.usr_rol_id = '1' THEN su.usr_username ELSE NULL END AS buzon_responsable
                        FROM siga_usuario AS su
                        LEFT JOIN siga_rolusuario rou ON su.usr_rol_id = rou.rol_id
                        LEFT JOIN siga_oficina ofi ON su.usr_oficina_id = ofi.ofi_id
                        LEFT JOIN siga_asignacion_usuario_buzon aub ON su.usr_id = aub.asig_usrid
                        LEFT JOIN siga_buzon buz ON buz.buzon_id = aub.asig_buzonid
                        WHERE su.usr_id = :id and buz.buzon_id = :idbuzon";
          $result = $this->database->ejecutarConsulta($query, ['id' => $idusuario, 'idbuzon' => $idbuzon]);
          
          if ($result) {
              Response::success($result[0], 'Consulta de usuario exitosa');
          } else {
              Response::error('No se encontró el usuario');
          }
      }


    // Listar Usuarios
    public function getUsuarios()
    {
        $query = "SELECT * FROM usuario_obtenerlista()";
        $result = $this->database->ejecutarConsulta($query);
        
        if ($result) {
            Response::success($result, 'Lista de usuarios obtenida correctamente');
        } else {
            Response::error('No se encontraron usuarios registrados');
        }
    }

    // Obtener usuarios de trámite documentario
    public function getUsuarios_tramitedocumentario()
    {
        $query = "SELECT  su.usr_id as id,
                            su.usr_username as username,
                            su.usr_usuario as usuario,
                            su.usr_esactivo as esactivo,
                            su.usr_fechareg as fechareg,
                            su.usr_rol_id as rol_id,
                            rou.rol_nombre as rol_nombre,
                            ofi.ofi_id as oficina_id,
                            ofi.ofi_nombre as oficina_nombre
                            FROM siga_usuario AS su
                LEFT JOIN siga_rolusuario rou ON su.usr_rol_id = rou.rol_id
                LEFT JOIN siga_oficina ofi ON su.usr_oficina_id = ofi.ofi_id
                ORDER BY su.usr_id desc";
        $result = $this->database->ejecutarConsulta($query);
        
        if ($result) {
            Response::success($result, 'Lista de usuarios de trámite documentario obtenida correctamente');
        } else {
            Response::error('No se encontraron usuarios de trámite documentario');
        }
    }

    // Actualizar datos del Usuario
    public function updateUsuario($id, $tipodocumento,
                $numdocumento,
                $nombres,
                $apellidos,
                $correo,
                $celular,
                $username,
                $usuario,
                $esactivo,
                $rol_id,
                $oficina_id
            )
    {
        // Validar que todos los datos requeridos estén presentes
        if (empty($id) || empty($username) || $esactivo === null || empty($rol_id)) {
            Response::error('Todos los campos son obligatorios');
            return;
        }

        // Verificar si el nombre de usuario ya existe para otro usuario
        $checkQuery = "SELECT COUNT(*) as count FROM siga_usuario WHERE usr_usuario = '$username' AND usr_id != '$id'";
        $checkResult = $this->database->ejecutarConsulta($checkQuery);
        $row = $checkResult && isset($checkResult[0]['count']) ? $checkResult[0] : null;

        if ($row && $row['count'] > 0) {
            Response::error('El nombre de usuario ya existe');
            return;
        }

        // Actualizar datos (sin actualizar la contraseña)
        $query = "UPDATE siga_usuario SET
        usr_tipodocumento = '$tipodocumento',
        usr_numdocumento = '$numdocumento',
        usr_nombres = '$nombres',
        usr_apellidos = '$apellidos',
        usr_correo = '$correo',
        usr_celular = '$celular',

        usr_usuario = '$usuario',
        usr_username = '$username',
        usr_rol_id = '$rol_id',
        usr_oficina_id = '$oficina_id',
        usr_esactivo = " . ($esactivo ? "true" : "false") . "
        WHERE usr_id = '$id'";

    try {
        $result = $this->database->ejecutarConsulta($query);

        if ($result !== false) {
            Response::success( 'Usuario actualizado correctamente');
        } else {
            Response::error('Error al actualizar el usuario');
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23505') {
            Response::error('El nombre de usuario ya existe');
        } else {
            Response::error('Error en la base de datos: ' . $e->getMessage());
        }
    }
    }

    // Actualizar foto de usuario
    public function updateFoto($id, $fotourl)
    {
        // Implementar según sea necesario para actualizar la foto del usuario
        Response::success(null, 'Función para actualizar foto pendiente de implementación');
    }

    // Actualizar contraseña de usuario
    public function updatePassword($id, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $query = "SELECT usuario_actualizarpassword(:id, :password) as result";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                'id' => $id,
                'password' => $hashedPassword
            ]);

            if ($result && $result[0]['result'] > 0) {
                Response::success(null, 'Contraseña actualizada correctamente');
            } else {
                Response::error('No se pudo actualizar la contraseña');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos: ' . $e->getMessage());
        }
    }

    // Actualizar estado del usuario
    public function updateEstado($id, $esactivo)
    {
        $query = "SELECT usuario_actualizarestado(:id, :esactivo) as result";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                'id' => $id,
                'esactivo' => $esactivo ? 'true' : 'false'
            ]);

            if ($result && $result[0]['result'] > 0) {
                $estado = $esactivo ? 'activado' : 'desactivado';
                Response::success(null, 'Usuario ' . $estado . ' correctamente');
            } else {
                Response::error('No se pudo actualizar el estado del usuario');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos: ' . $e->getMessage());
        }
    }

    // Eliminar Usuario
    public function deleteUsuario($id)
    {
        $query = "SELECT usuario_eliminar(:id) as result";
        try {
            $result = $this->database->ejecutarConsulta($query, ['id' => $id]);
            
            if ($result) {
                $rows_affected = $result[0]['result'];
                if ($rows_affected > 0) {
                    Response::success(null, 'Usuario eliminado correctamente');
                } else if ($rows_affected == -1) {
                    Response::error('No se puede eliminar el usuario porque está relacionado con pases de documentos');
                } else if ($rows_affected == -3) {
                    Response::error('No se puede eliminar el usuario debido a restricciones de clave foránea');
                } else if ($rows_affected == -99) {
                    Response::error('Error desconocido al eliminar el usuario');
                } else {
                    Response::error('No se encontró el usuario para eliminar');
                }
            } else {
                Response::error('Error al ejecutar la consulta de eliminación');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos: ' . $e->getMessage());
        }
    }

    // Obtener unidades orgánicas y buzones (método auxiliar)
    public function get_unidadesorganicas_buzones()
    {
        // La tabla siga_oficina ya no existe, devolvemos solo la lista de buzones
        $query = "SELECT buzon_id AS id, 
                                buzon_nombre AS destino,
                                buzon_sigla AS sigla,
                                MAX((
                                    SELECT us2.usr_username
                                    FROM siga_usuario us2
                                    INNER JOIN siga_asignacion_usuario_buzon abu2 ON abu2.asig_usrid = us2.usr_id
                                    WHERE abu2.asig_buzonid = buzon_id AND us2.usr_rol_id = '1' limit 1
                                )) AS buzon_responsable
                            FROM siga_buzon 
                            INNER JOIN siga_asignacion_usuario_buzon abu ON abu.asig_buzonid = buzon_id
                            INNER JOIN siga_usuario us ON abu.asig_usrid = us.usr_id
                            INNER JOIN siga_rolusuario rol ON rol.rol_id = us.usr_rol_id
                            WHERE rol.rol_id = 3
                            GROUP BY buzon_id, buzon_nombre, buzon_sigla";
                $result = $this->database->ejecutarConsulta($query);
                 
        $result = $this->database->ejecutarConsulta($query);
        
        if ($result) {
            Response::success($result, 'Lista de buzones obtenida correctamente');
        } else {
            Response::error('No se encontraron buzones');
        }
    }

    // Obtener todos los buzones (método auxiliar)
    public function get_all_buzones($idoficina)
    {
    
        if($idoficina && $idoficina!=null){
             $query = "SELECT id, destino, sigla, buzon_responsable
                        FROM (
                            SELECT buzon_id AS id, 
                                buzon_nombre AS destino,
                                buzon_sigla AS sigla,
                                MAX((
                                    SELECT us2.usr_username
                                    FROM siga_usuario us2
                                    INNER JOIN siga_asignacion_usuario_buzon abu2 ON abu2.asig_usrid = us2.usr_id
                                    WHERE abu2.asig_buzonid = buzon_id AND us2.usr_rol_id = '1' limit 1
                                )) AS buzon_responsable,
                                1 AS query_order
                            FROM siga_buzon 
                            INNER JOIN siga_asignacion_usuario_buzon abu ON abu.asig_buzonid = buzon_id
                            INNER JOIN siga_usuario us ON abu.asig_usrid = us.usr_id
                            INNER JOIN siga_rolusuario rol ON rol.rol_id = us.usr_rol_id
                            WHERE rol.rol_id = 3
                            GROUP BY buzon_id, buzon_nombre, buzon_sigla
                            UNION
                            SELECT buzon_id AS id, 
                                CONCAT('Esp. ', us.usr_username) AS destino,
                                buzon_sigla AS sigla,
                                MAX((
                                    SELECT us2.usr_username
                                    FROM siga_usuario us2
                                    INNER JOIN siga_asignacion_usuario_buzon abu2 ON abu2.asig_usrid = us2.usr_id
                                    WHERE abu2.asig_buzonid = buzon_id AND us2.usr_rol_id = '4' limit 1
                                )) AS buzon_responsable,
                                2 AS query_order
                            FROM siga_buzon 
                            INNER JOIN siga_asignacion_usuario_buzon abu ON abu.asig_buzonid = buzon_id
                            INNER JOIN siga_usuario us ON abu.asig_usrid = us.usr_id
                            INNER JOIN siga_rolusuario rol ON rol.rol_id = us.usr_rol_id
                            INNER JOIN siga_oficina ofi ON ofi.ofi_id = us.usr_oficina_id
                            WHERE rol.rol_id = 4 AND us.usr_oficina_id = :idoficina
                            GROUP BY buzon_id, buzon_nombre, buzon_sigla, us.usr_username
                        ) AS combined
                        ORDER BY query_order, destino;";    
                $result = $this->database->ejecutarConsulta($query, ['idoficina' => $idoficina]);
        }else{
                $query = "SELECT buzon_id AS id, 
                                buzon_nombre AS destino,
                                buzon_sigla AS sigla,
                                MAX((
                                    SELECT us2.usr_username
                                    FROM siga_usuario us2
                                    INNER JOIN siga_asignacion_usuario_buzon abu2 ON abu2.asig_usrid = us2.usr_id
                                    WHERE abu2.asig_buzonid = buzon_id AND us2.usr_rol_id = '1' limit 1
                                )) AS buzon_responsable
                            FROM siga_buzon 
                            INNER JOIN siga_asignacion_usuario_buzon abu ON abu.asig_buzonid = buzon_id
                            INNER JOIN siga_usuario us ON abu.asig_usrid = us.usr_id
                            INNER JOIN siga_rolusuario rol ON rol.rol_id = us.usr_rol_id
                            WHERE rol.rol_id = 3
                            GROUP BY buzon_id, buzon_nombre, buzon_sigla";
                $result = $this->database->ejecutarConsulta($query);
        }
        
        if ($result) {
            Response::success($result, 'Buzones obtenidos correctamente');
        } else {
            Response::error('No se encontraron buzones');
        }
    }
    // Obtener todos los buzones (método auxiliar)
    public function get_buzones_trabajadores_oficina($idoficina)
    {
        $query =   "SELECT 
                        buz.buzon_id AS id,
                        buz.buzon_nombre AS destino,
                        buz.buzon_sigla AS sigla,
                        ofi.ofi_nombre AS oficina,
                        CONCAT(us.usr_username, ' (', rol.rol_nombre, ')') AS destino_usuario,
                        rol.rol_nombre AS rol_nombre
                    FROM 
                        siga_buzon buz
                        INNER JOIN siga_asignacion_usuario_buzon abu ON abu.asig_buzonid = buz.buzon_id
                        INNER JOIN siga_usuario us ON abu.asig_usrid = us.usr_id
                        INNER JOIN siga_rolusuario rol ON rol.rol_id = us.usr_rol_id
                        INNER JOIN siga_oficina ofi ON ofi.ofi_id = us.usr_oficina_id
                    WHERE 
                        rol.rol_id IN (1)
                        AND us.usr_oficina_id = :idoficina
                    GROUP BY 
                        buz.buzon_id,
                        buz.buzon_nombre,
                        buz.buzon_sigla,
                        ofi.ofi_nombre,
                        us.usr_username,
                        rol.rol_nombre
                    ORDER BY 
                        buz.buzon_id DESC";
                 
        $result = $this->database->ejecutarConsulta($query, ['idoficina' => $idoficina]);

        if ($result) {
            Response::success($result, 'Buzones obtenidos correctamente');
        } else {
            Response::error('No se encontraron buzones');
        }
    }

    // Asignar buzón a usuario (usa la función del controlador de buzón)
    public function asignarBuzonUsuario($userId, $buzonId)
    {
        $buzonController = new BuzonController();
        $buzonController->assignUserToBuzon($userId, $buzonId);
    }

    // Método para subir imágenes
    public function Subir_Imagen($file_fotoperfil, $ruta)
    {
        // Implementar según sea necesario para subir imágenes
        Response::success(null, 'Función para subir imágenes pendiente de implementación');
    }

      public function getRoles() {
        // $conexion = new Conexion();
        $query = "SELECT 
                   rol_id as id,
                   rol_nombre as nombre
                FROM siga_rolusuario
                ORDER BY rol_id ASC";
        $result = $this->database->ejecutarConsulta($query);

      if ($result) {
            Response::success($result, 'Roles obtenidos correctamente');
        } else {
            Response::error('No se encontraron roles');
        }
    }

     public function getRole($id) {
        // $conexion = new Conexion();
        $query = "SELECT 
                   rol_nombre as nombre
                FROM siga_rolusuario
                WHERE rol_id = $id";
        $result = $this->database->ejecutarConsulta($query);

      if ($result) {
            Response::success($result, 'Roles obtenidos correctamente');
        } else {
            Response::error('No se encontraron roles');
        }
    }

    public function getRolByUserId($userId)
    {
        $query = "SELECT r.rol_id as id, r.rol_nombre as nombre
                  FROM siga_usuario u
                  JOIN siga_rolusuario r ON u.usr_rol_id = r.rol_id
                  WHERE u.usr_id = :userId";
        $result = $this->database->ejecutarConsulta($query, ['userId' => $userId]);

        if ($result) {
            Response::success($result[0], 'Rol obtenido correctamente');
        } else {
            Response::error('No se encontró el rol para el usuario');
        }
    }
}
?>
