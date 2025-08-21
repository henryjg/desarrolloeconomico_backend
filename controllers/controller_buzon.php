<?php
include_once './utils/response.php';
include_once './config/database.php';

class BuzonController
{
    private $database;

    public function __construct()
    {
        $this->database = new Database();
    }

   // Insertar Buzon
    public function insertBuzon($nombre, $sigla, $estado, $tipobuzon, $correonotificacion = null)
    {
        // Validación existente: campos obligatorios
        if (empty($nombre) || empty($sigla) || empty($estado)) {
            Response::error('Todos los campos son obligatorios');
            return;
        }

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
                Response::success(['id' => $result[0]['buzon_id']], 'Buzon insertado correctamente');
            } else {
                Response::error('Error al insertar el buzon');
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                Response::error('Error: El buzón ya existe');
            } else {
                Response::error('Error en la base de datos al insertar el buzon: ' . $e->getMessage());
            }
        }
    }
    // Insertar Buzon
    public function insertBuzon_AsignarUsuario($nombre, $sigla, $estado, $tipobuzon, $correonotificacion = null,$iduserId)
    {
        // Validación existente: campos obligatorios
        if (empty($nombre) || empty($sigla) || empty($estado) || empty($tipobuzon) || empty($iduserId)) {
            Response::error('Todos los campos son obligatorios');
            return;
        }

        // Inserción con SQL y RETURNING para obtener el ID generado
        $query = "INSERT INTO siga_buzon (
            buzon_nombre,
            buzon_sigla,
            buzon_estado,
            buzon_correonotificaion,
            buzon_fechareg,
            buzon_tipo
        ) VALUES (
            :nombre,
            :sigla,
            :estado,
            :correonotificacion,
            NOW(),
            :tipobuzon
        ) RETURNING buzon_id";

        try {
            $params = [
                'nombre' => $nombre,
                'sigla' => $sigla,
                'estado' => $estado,
                'correonotificacion' => $correonotificacion,
                'tipobuzon' => $tipobuzon
            ];
            $result = $this->database->ejecutarConsulta($query, $params);

            if ($result && isset($result[0]['buzon_id'])) {
                $idBuzon = $result[0]['buzon_id'];
                // Asignar el usuario al buzón recién creado
                $this -> assignUserToBuzon($iduserId, $idBuzon);

            } else {
                Response::error('Error al insertar el buzon');
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                Response::error('Error: El buzón ya existe');
            } else {
                Response::error('Error en la base de datos al insertar el buzon: ' . $e->getMessage());
            }
        }
    }

    // Obtener un Buzon por ID
    public function getBuzon($id)
    {
        $query = "SELECT buzon_id AS id, 
                         buzon_nombre AS nombre,
                         buzon_sigla AS sigla,
                         buzon_estado AS estado,
                         buzon_fechareg AS fechareg,
                         buzon_tipo AS tipo
                  FROM siga_buzon WHERE buzon_id = :id";
        $result = $this->database->ejecutarConsulta($query, ['id' => $id]);
        if ($result) {
            Response::success($result[0], 'Consulta de buzon exitosa');
        } else {
            Response::error('No se encontró el buzon');
        }
    }

    // Listar Buzones
        public function getBuzones()
        {
            $query = "SELECT 
                        b.buzon_id AS id,
                        b.buzon_nombre AS nombre,
                        b.buzon_sigla AS sigla,
                        b.buzon_estado AS estado,
                        MAX(CASE WHEN u.usr_rol_id = 1 THEN u.usr_username ELSE NULL END) AS responsable,
                        STRING_AGG(u.usr_usuario, ',') AS usuarios,
                        b.buzon_tipo AS tipo,
                        b.buzon_fechareg AS fechareg 
                    FROM siga_buzon b
                    LEFT JOIN siga_asignacion_usuario_buzon aub ON b.buzon_id = aub.asig_buzonid
                    LEFT JOIN siga_usuario u ON aub.asig_usrid = u.usr_id
                    GROUP BY b.buzon_id, b.buzon_nombre, b.buzon_sigla, b.buzon_estado, b.buzon_fechareg
                    ORDER BY b.buzon_id DESC";
            
            try {
                $result = $this->database->ejecutarConsulta($query);
                if ($result) {
                    // Opcional: Convertir la cadena de usuarios en un array
                    foreach ($result as &$row) {
                        $row['usuarios'] = $row['usuarios'] ? explode(',', $row['usuarios']) : [];
                    }
                    Response::success($result, 'Lista de buzones obtenida correctamente');
                } else {
                    Response::error('No se encontraron buzones registrados');
                }
            } catch (Exception $e) {
                Response::error('Error al ejecutar la consulta: ' . $e->getMessage());
            }
        }

    // Actualizar datos del Buzon
    public function updateBuzon($id, $tipo, $nombre, $sigla, $responsable, $correonotificacion = null)
    {
        // Validación existente: campos obligatorios
        if (empty($id) || empty($tipo) || empty($nombre) || empty($sigla) || empty($responsable)) {
            Response::error('Todos los campos son obligatorios');
            return;
        }

        $query = "UPDATE siga_buzon SET
                    buzon_tipo = :tipo,
                    buzon_nombre = :nombre,
                    buzon_sigla = :sigla,
                    buzon_estado = 'Activo',
                    buzon_responsable = :responsable,
                    buzon_correonotificaion = :correonotificacion
                    WHERE buzon_id = :id";

        try {
            $params = [
                'id' => $id,
                'tipo' => $tipo,
                'nombre' => $nombre,
                'sigla' => $sigla,
                'responsable' => $responsable,
                'correonotificacion' => $correonotificacion
            ];
            $result = $this->database->ejecutarConsulta($query, $params);

            if ($result !== false) {
                Response::success( 'Buzon actualizado correctamente');
            } else {
                Response::error('Error al actualizar el buzon');
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                Response::error('El nombre o la sigla del buzón ya existe');
            } else {
                Response::error('Error en la base de datos al actualizar el buzon: ' . $e->getMessage());
            }
        }
    }

    // Eliminar Buzon
    public function deleteBuzon($id)
    {
        $query = "SELECT buzon_eliminar(:id) as rows_affected";
        try {
            $result = $this->database->ejecutarConsulta($query, ['id' => $id]);
            if ($result) {
                $rows_affected = $result[0]['rows_affected'];
                if ($rows_affected > 0) {
                    Response::success($rows_affected, 'Buzon eliminado correctamente');
                } else if ($rows_affected == -1) {
                    Response::error('No se puede eliminar el buzón porque tiene documentos relacionados');
                } else if ($rows_affected == -2) {
                    Response::error('No se puede eliminar el buzón porque tiene usuarios asignados');
                } else if ($rows_affected == -3) {
                    Response::error('No se puede eliminar el buzón debido a restricciones de clave foránea');
                } else if ($rows_affected == -99) {
                    Response::error('Error desconocido al eliminar el buzón');
                } else {
                    Response::error('No se encontró el buzón para eliminar');
                }
            } else {
                Response::error('Error al ejecutar la consulta de eliminación');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al eliminar el buzon: ' . $e->getMessage());
        }
    }

    // Asignar usuario a buzon
    public function assignUserToBuzon($userId, $buzonId)
    {
        // Verificar si ya existe la asignación
        $checkQuery = "SELECT 1 FROM siga_asignacion_usuario_buzon 
                       WHERE asig_usrid = :userId AND asig_buzonid = :buzonId";
        $exists = $this->database->ejecutarConsulta($checkQuery, [
            'userId' => $userId,
            'buzonId' => $buzonId
        ]);
        
        if ($exists) {
            Response::success(null, 'El usuario ya está asignado a este buzón');
            return;
        }
        
        $query = "INSERT INTO siga_asignacion_usuario_buzon (asig_usrid, asig_buzonid)
                  VALUES (:userId, :buzonId)";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                'userId' => $userId,
                'buzonId' => $buzonId
            ]);
            
            if ($result) {
                Response::success(null, 'Usuario asignado al buzon correctamente');
            } else {
                Response::error('Error desconocido al asignar usuario al buzón');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al asignar usuario al buzón: ' . $e->getMessage());
        }
    }
    
    // Eliminar asignación usuario-buzon
    public function removeUserFromBuzon($userId, $buzonId)
    {
        $query = "DELETE FROM siga_asignacion_usuario_buzon 
                  WHERE asig_usrid = :userId AND asig_buzonid = :buzonId";
        try {
            $result = $this->database->ejecutarConsulta($query, [
                'userId' => $userId,
                'buzonId' => $buzonId
            ]);
            
            if ($result) {
                Response::success(null, 'Usuario removido del buzón correctamente');
            } else {
                Response::error('Error desconocido al remover usuario del buzón o la asignación no existía');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al remover usuario del buzón: ' . $e->getMessage());
        }
    }
    
    // Obtener usuarios asignados a un buzón
    public function getUsersForBuzon($buzonId)
    {
        $query = "SELECT u.usr_id AS id, u.usr_username AS username,
                  u.usr_usuario AS usuario,
                 u.usr_esactivo AS esactivo, u.usr_fechareg AS fechareg,
                 aub.asig_fecha_registro AS fecha_asignacion
                 FROM siga_asignacion_usuario_buzon aub
                 JOIN siga_usuario u ON u.usr_id = aub.asig_usrid
                 WHERE aub.asig_buzonid = :buzonId";
        
        try {
            $result = $this->database->ejecutarConsulta($query, ['buzonId' => $buzonId]);
            
            if ($result) {
                Response::success($result, 'Listado de usuarios del buzón obtenido correctamente');
            } else {
                Response::error('No se encontraron usuarios asignados a este buzón');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al obtener usuarios del buzón: ' . $e->getMessage());
        }
    }
    
    // Obtener buzones asignados a un usuario
    public function getBuzonesForUser($userId)
    {
        $query = "SELECT b.buzon_id AS id, b.buzon_nombre AS nombre, b.buzon_sigla AS sigla,
                 b.buzon_estado AS estado,
                 aub.asig_fecha_registro AS fecha_asignacion
                 FROM siga_asignacion_usuario_buzon aub
                 JOIN siga_buzon b ON b.buzon_id = aub.asig_buzonid
                 WHERE aub.asig_usrid = :userId";
        
        try {
            $result = $this->database->ejecutarConsulta($query, ['userId' => $userId]);
            
            if ($result) {
                Response::success($result, 'Listado de buzones del usuario obtenido correctamente');
            } else {
                Response::error('No se encontraron buzones asignados a este usuario');
            }
        } catch (PDOException $e) {
            Response::error('Error en la base de datos al obtener buzones del usuario: ' . $e->getMessage());
        }
    }

    public function eliminarBuzon($id) {
    try {
        // Verificar documentos en siga_documento
        $queryDoc = "SELECT count(*) as cantidad_documentos_oficina FROM public.siga_documento WHERE doc_buzonorigen_id = :id";
        $resultDoc = $this->database->ejecutarConsulta($queryDoc, ['id' => $id]);
        $cantidadDocumentos = $resultDoc[0]['cantidad_documentos_oficina'];

        // Verificar pases de origen en siga_documento_pase
        $queryPaseOrigen = "SELECT count(*) as cantidad_pases_origen FROM public.siga_documento_pase WHERE pase_buzonorigen_id = :id";
        $resultPaseOrigen = $this->database->ejecutarConsulta($queryPaseOrigen, ['id' => $id]);
        $cantidadPasesOrigen = $resultPaseOrigen[0]['cantidad_pases_origen'];

        // Verificar pases de destino en siga_documento_pase
        $queryPaseDestino = "SELECT count(*) as cantidad_pases_destino FROM public.siga_documento_pase WHERE siga_documento_pase.pase_buzondestino_id = :id";
        $resultPaseDestino = $this->database->ejecutarConsulta($queryPaseDestino, ['id' => $id]);
        $cantidadPasesDestino = $resultPaseDestino[0]['cantidad_pases_destino'];

        // Si alguna cantidad es mayor o igual a 0, no se elimina
        if ($cantidadDocumentos > 0) {
            Response::error('No se puede eliminar el buzón porque tiene documentos relacionados');
            return;
        }
        if ($cantidadPasesOrigen > 0) {
            Response::error('No se puede eliminar el buzón porque tiene pases de origen relacionados');
            return;
        }
        if ($cantidadPasesDestino > 0) {
            Response::error('No se puede eliminar el buzón porque tiene pases de destino relacionados');
            return;
        }

        // Si pasa todas las verificaciones, eliminar el buzón
        $queryDelete = "DELETE FROM siga_buzon WHERE buzon_id = :id";
        $resultDelete = $this->database->ejecutarConsulta($queryDelete, ['id' => $id]);

        if ($resultDelete !== false) {
            Response::success(null, 'Buzón eliminado correctamente');
        } else {
            Response::error('No se encontró el buzón para eliminar');
        }
    } catch (PDOException $e) {
        Response::error('Error en la base de datos al eliminar el buzón: ' . $e->getMessage());
    }
}
}
?>
