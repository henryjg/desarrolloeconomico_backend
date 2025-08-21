
CREATE OR REPLACE PROCEDURE tipo_documento_insertar(
    IN p_tipo_nombre VARCHAR(255)
)
LANGUAGE plpgsql
AS $$
BEGIN
    INSERT INTO siga_tipodocumento (tipo_nombre, tipo_estado)
    VALUES (p_tipo_nombre, 'Activo');
END;
$$;


-- *--------------------------------------------------

CREATE OR REPLACE FUNCTION tipo_documento_obtener(p_tipo_id BIGINT)
RETURNS TABLE(
    tipo_id BIGINT,
    tipo_nombre VARCHAR(255),
    tipo_estado VARCHAR(45)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        tipo_id as id,
        tipo_nombre as nombre,
        tipo_estado as estado
    FROM siga_tipodocumento
    WHERE tipo_id = p_tipo_id;
END;
$$ LANGUAGE plpgsql;


-- *--------------------------------------------------
CREATE OR REPLACE FUNCTION tipo_documento_listar()
RETURNS TABLE(
    tipo_id BIGINT,
    tipo_nombre VARCHAR(255),
    tipo_estado VARCHAR(45)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        tipo_id as id,
        tipo_nombre as nombre,
        tipo_estado as estado
    FROM siga_tipodocumento;
END;
$$ LANGUAGE plpgsql;


-- *--------------------------------------------------
CREATE OR REPLACE PROCEDURE tipo_documento_actualizar(
    IN p_tipo_id BIGINT,
    IN p_tipo_nombre VARCHAR(255),
    IN p_tipo_estado VARCHAR(45)
)
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE siga_tipodocumento
    SET tipo_nombre = p_tipo_nombre,
        tipo_estado = p_tipo_estado
    WHERE tipo_id = p_tipo_id;
END;
$$;

CREATE OR REPLACE PROCEDURE tipo_documento_desactivar(
    IN p_tipo_id BIGINT,
)
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE siga_tipodocumento
    SET tipo_estado = 'Inactivo'
    WHERE tipo_id = p_tipo_id;
END;
$$;

-- *--------------------------------------------------
CREATE OR REPLACE PROCEDURE tipo_documento_eliminar(
    IN p_tipo_id BIGINT
)
LANGUAGE plpgsql
AS $$
BEGIN
    DELETE FROM siga_tipodocumento WHERE tipo_id = p_tipo_id;
END;
$$;


-- *--------------------------------------------------