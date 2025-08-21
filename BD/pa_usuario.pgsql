CREATE OR REPLACE FUNCTION usuario_insertar(
    p_username VARCHAR(255),
    p_usuario VARCHAR(45),
    p_password VARCHAR(255),
    p_jerarquia VARCHAR(255),
    p_rol_id BIGINT,
    p_buzon_id BIGINT -- This will be the primary buzon to assign
) RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
    new_usr_id BIGINT;
BEGIN 
    INSERT INTO siga_usuario (
        usr_username,
        usr_usuario,
        usr_password,
        usr_jerarquia,
        usr_rol_id
    ) VALUES (
        p_username,
        p_usuario,
        p_password,
        p_jerarquia,
        p_rol_id
    )
    RETURNING usr_id INTO new_usr_id;

    -- If a buzon_id was provided, create the association in the intermediate table
    IF p_buzon_id IS NOT NULL THEN
        INSERT INTO siga_asignacion_usuario_buzon (asig_usrid, asig_buzonid)
        VALUES (new_usr_id, p_buzon_id);
    END IF;

    RETURN new_usr_id;

EXCEPTION
    WHEN unique_violation THEN
        RAISE NOTICE 'Unique constraint violation. Username already exists: %', SQLERRM;
        RETURN -1;
    WHEN not_null_violation THEN
        RAISE NOTICE 'Not null constraint violation: %', SQLERRM;
        RETURN -2;
    WHEN OTHERS THEN
        RAISE NOTICE 'Unexpected error during insertion: %', SQLERRM;
        RETURN -3;
END;
$$;




--- *------------------------------------------------------------------------------
-- Actualizar la función usuario_obtenerdatos para usar JSON
CREATE OR REPLACE FUNCTION usuario_obtenerdatos(p_usr_id BIGINT)
RETURNS TABLE(
    id BIGINT,
    username VARCHAR(255),
    usuario VARCHAR(45),
    esactivo BOOLEAN,
    fechareg TIMESTAMP,
    rol_id BIGINT,
    rol_nombre VARCHAR(125),
    buzon_id VARCHAR(45),
    buzon_nombre VARCHAR(255),
    buzon_sigla VARCHAR(45),
    buzon_responsable VARCHAR(45)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        su.usr_id as id,
        su.usr_username as username,
        su.usr_usuario as usuario,
        su.usr_jerarquia as jerarquia,
        su.usr_esactivo as esactivo,
        su.usr_fechareg as fechareg,
        su.usr_rol_id as rol_id,
        rou.rol_nombre as rol_nombre,
        buz.buzon_id as buzon_id,
        buz.buzon_nombre as buzon_nombre,
        buz.buzon_sigla as buzon_sigla,
        buz.buzon_responsable as buzon_responsable
    FROM siga_usuario AS su
    LEFT JOIN siga_rolusuario rou ON su.usr_rol_id = rou.rol_id
    LEFT JOIN siga_asignacion_usuario_buzon aub ON su.usr_id = aub.asig_usrid
    LEFT JOIN siga_buzon buz ON buz.buzon_id = aub.asig_buzonid
    WHERE su.usr_id = p_usr_id;
END;
$$ LANGUAGE plpgsql;

--- *------------------------------------------------------------------------------

-- También actualizar la función usuario_obtenerlista para usar JSON
CREATE OR REPLACE FUNCTION usuario_obtenerlista()
RETURNS TABLE(
    id BIGINT,
    username VARCHAR(255),
    usuario VARCHAR(45),
    jerarquia VARCHAR(255),
    esactivo BOOLEAN,
    fechareg TIMESTAMP,
    rol_id BIGINT,
    rol_nombre VARCHAR(125),
    buzones JSONB
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        su.usr_id as id,
        su.usr_username as username,
        su.usr_usuario as usuario,
        su.usr_jerarquia as jerarquia,
        su.usr_esactivo as esactivo,
        su.usr_fechareg as fechareg,
        su.usr_rol_id as rol_id,
        rou.rol_nombre as rol_nombre,
        -- Usamos JSONB_AGG para crear el array de buzones con sus campos como objetos
        CASE 
            WHEN COUNT(buz.buzon_id) > 0 THEN
                JSONB_AGG(
                    JSONB_BUILD_OBJECT(
                        'buzon_id', buz.buzon_id,
                        'buzon_nombre', buz.buzon_nombre,
                        'buzon_sigla', buz.buzon_sigla,
                        'buzon_responsable', buz.buzon_responsable
                    )
                )
            ELSE
                '[]'::JSONB
        END as buzones
    FROM siga_usuario AS su
    LEFT JOIN siga_rolusuario rou ON su.usr_rol_id = rou.rol_id
    LEFT JOIN siga_asignacion_usuario_buzon aub ON su.usr_id = aub.asig_usrid
    LEFT JOIN siga_buzon buz ON buz.buzon_id = aub.asig_buzonid
    GROUP BY su.usr_id, rou.rol_nombre;
END;
$$ LANGUAGE plpgsql;

--- *------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION usuario_obtener_credenciales(
    p_usuario VARCHAR(45)
)
RETURNS TABLE(
    id BIGINT,
    password VARCHAR(255),
    esactivo BOOLEAN
) AS $$
BEGIN
    RETURN QUERY
    SELECT usr_id as id, usr_password as password, usr_esactivo as esactivo
    FROM siga_usuario
    WHERE usr_usuario = p_usuario;
END;
$$ LANGUAGE plpgsql;

--- *------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION usuario_actualizardatos(
    p_usr_id BIGINT,
    p_username VARCHAR(255),
    p_jerarquia VARCHAR(255),
    p_password VARCHAR(255),
    p_rol_id BIGINT,
    p_buzon_id BIGINT -- This will be used to update the primary buzon
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_usuario
    SET
        usr_username = p_username,
        usr_jerarquia = p_jerarquia,
        usr_rol_id = p_rol_id
    WHERE usr_id = p_usr_id;

    GET DIAGNOSTICS rows_affected = ROW_COUNT;

    IF rows_affected = 0 THEN
        RETURN -1; -- User not found
    END IF;
    
    -- If password is provided, update it
    IF p_password IS NOT NULL AND p_password <> '' THEN
        UPDATE siga_usuario
        SET usr_password = p_password
        WHERE usr_id = p_usr_id;
    END IF;

    -- If buzon_id is provided, replace the user's primary buzon 
    IF p_buzon_id IS NOT NULL THEN
        -- Delete existing relationships
        DELETE FROM siga_asignacion_usuario_buzon 
        WHERE asig_usrid = p_usr_id;
        
        -- Add the new primary buzon
        INSERT INTO siga_asignacion_usuario_buzon (asig_usrid, asig_buzonid)
        VALUES (p_usr_id, p_buzon_id);
    END IF;

    RETURN rows_affected;

EXCEPTION
    WHEN unique_violation THEN
        RETURN -2; -- Duplicate username
    WHEN OTHERS THEN
        RETURN -3; -- Unexpected error
END;
$$;

--- *------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION usuario_eliminar(p_usr_id BIGINT) 
RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
    pase_count INTEGER;
BEGIN
    -- Verificar si el usuario tiene pases de documentos donde es origen o destino
    SELECT COUNT(*) INTO pase_count 
    FROM siga_documento_pase 
    WHERE pase_buzonorigen_id = p_usr_id OR pase_buzondestino_id = p_usr_id;
    
    IF pase_count > 0 THEN
        RETURN -1; -- El usuario está relacionado con pases de documentos
    END IF;

    -- Verificar si hay buzones asignados a este usuario
    SELECT COUNT(*) INTO pase_count 
    FROM siga_asignacion_usuario_buzon 
    WHERE asig_usrid = p_usr_id;
    
    IF pase_count > 0 THEN
        -- Eliminar primero las asignaciones de buzones
        DELETE FROM siga_asignacion_usuario_buzon WHERE asig_usrid = p_usr_id;
    END IF;
    
    -- Si no hay dependencias críticas, proceder con la eliminación
    DELETE FROM siga_usuario WHERE usr_id = p_usr_id;
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    
    RETURN rows_affected;
EXCEPTION
    WHEN foreign_key_violation THEN
        RETURN -3; -- Violación de clave foránea no capturada en verificaciones previas
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la eliminación del usuario: %', SQLERRM;
        RETURN -99; -- Error desconocido
END;
$$;

--- *------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION usuario_actualizarpassword(
    p_usr_id BIGINT,
    p_password VARCHAR(255)
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_usuario
    SET
        usr_password = p_password
    WHERE usr_id = p_usr_id;

    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
END;
$$;

--- *------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION usuario_actualizarestado(
    p_usr_id BIGINT,
    p_esactivo BOOLEAN
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_usuario
    SET
        usr_esactivo = p_esactivo,
        usr_fechareg = CURRENT_TIMESTAMP
    WHERE usr_id = p_usr_id;

    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
END;
$$;

--- *------------------------------------------------------------------------------

-- Function to assign a user to an additional mailbox
CREATE OR REPLACE FUNCTION usuario_asignar_buzon(
    p_usr_id BIGINT,
    p_buzon_id BIGINT
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
BEGIN
    -- Check if the assignment already exists
    IF EXISTS (SELECT 1 FROM siga_asignacion_usuario_buzon 
               WHERE asig_usrid = p_usr_id AND asig_buzonid = p_buzon_id) THEN
        RETURN 0; -- Already assigned
    END IF;

    INSERT INTO siga_asignacion_usuario_buzon (asig_usrid, asig_buzonid)
    VALUES (p_usr_id, p_buzon_id);
    
    RETURN 1; -- Successfully assigned

EXCEPTION
    WHEN OTHERS THEN
        RETURN -1; -- Error during assignment
END;
$$;

--- *------------------------------------------------------------------------------

-- Function to remove a user from a mailbox
CREATE OR REPLACE FUNCTION usuario_desasignar_buzon(
    p_usr_id BIGINT,
    p_buzon_id BIGINT
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    DELETE FROM siga_asignacion_usuario_buzon 
    WHERE asig_usrid = p_usr_id AND asig_buzonid = p_buzon_id;
    
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
END;
$$;

--- *------------------------------------------------------------------------------

-- Function to get all mailboxes for a specific user
CREATE OR REPLACE FUNCTION usuario_obtener_buzones(p_usr_id BIGINT)
RETURNS TABLE(
    buzon_id BIGINT,
    buzon_nombre VARCHAR(255),
    buzon_sigla VARCHAR(45),
    buzon_tipo VARCHAR(45),
    buzon_responsable VARCHAR(45),
    fecha_asignacion TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        b.buzon_id,
        b.buzon_nombre,
        b.buzon_sigla,
        b.buzon_tipo,
        b.buzon_responsable,
        aub.asig_fecha_registro as fecha_asignacion
    FROM siga_asignacion_usuario_buzon aub
    JOIN siga_buzon b ON b.buzon_id = aub.asig_buzonid
    WHERE aub.asig_usrid = p_usr_id;
END;
$$ LANGUAGE plpgsql;


















