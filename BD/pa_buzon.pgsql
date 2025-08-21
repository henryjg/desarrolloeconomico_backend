CREATE OR REPLACE FUNCTION buzon_insertar(
    p_tipo VARCHAR(45),
    p_nombre VARCHAR(255),
    p_sigla VARCHAR(45),
    p_estado VARCHAR(45),
    p_responsable VARCHAR(45),
    p_correonotificacion VARCHAR(255)
) RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
    new_buzon_id BIGINT;
BEGIN
    INSERT INTO siga_buzon (
        buzon_tipo,
        buzon_nombre,
        buzon_sigla,
        buzon_estado,
        buzon_responsable,
        buzon_correonotificaion
    ) VALUES (
        p_tipo,
        p_nombre,
        p_sigla,
        p_estado,
        p_responsable,
        p_correonotificacion
    )
    RETURNING buzon_id INTO new_buzon_id;

    RETURN new_buzon_id;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la inserción: %', SQLERRM;
        RETURN -1;
END;
$$;

-- Obtener un buzón
CREATE OR REPLACE FUNCTION buzon_obtener(p_buzon_id BIGINT)
RETURNS TABLE(
    id BIGINT,
    tipo VARCHAR(45),
    nombre VARCHAR(255),
    sigla VARCHAR(45),
    estado VARCHAR(45),
    responsable VARCHAR(45),
    fechareg TIMESTAMP,
    correonotificacion VARCHAR(255)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        buzon_id AS id,
        buzon_tipo AS tipo,
        buzon_nombre AS nombre,
        buzon_sigla AS sigla,
        buzon_estado AS estado,
        buzon_responsable AS responsable,
        buzon_fechareg AS fechareg,
        buzon_correonotificaion AS correonotificacion
    FROM siga_buzon
    WHERE buzon_id = p_buzon_id;
END;
$$ LANGUAGE plpgsql;

-- Listar buzones
CREATE OR REPLACE FUNCTION buzon_listar()
RETURNS TABLE(
    id BIGINT,
    tipo VARCHAR(45),
    nombre VARCHAR(255),
    sigla VARCHAR(45),
    estado VARCHAR(45),
    responsable VARCHAR(45),
    fechareg TIMESTAMP,
    correonotificacion VARCHAR(255)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        buzon_id AS id,
        buzon_tipo AS tipo,
        buzon_nombre AS nombre,
        buzon_sigla AS sigla,
        buzon_estado AS estado,
        buzon_responsable AS responsable,
        buzon_fechareg AS fechareg,
        buzon_correonotificaion AS correonotificacion
    FROM siga_buzon;
END;
$$ LANGUAGE plpgsql;

-- Actualizar buzón
CREATE OR REPLACE FUNCTION buzon_actualizar(
    p_buzon_id BIGINT,
    p_tipo VARCHAR(45),
    p_nombre VARCHAR(255),
    p_sigla VARCHAR(45),
    p_estado VARCHAR(45),
    p_responsable VARCHAR(45),
    p_correonotificacion VARCHAR(255)
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_buzon
    SET
        buzon_tipo = p_tipo,
        buzon_nombre = p_nombre,
        buzon_sigla = p_sigla,
        buzon_estado = p_estado,
        buzon_responsable = p_responsable,
        buzon_correonotificaion = p_correonotificacion
    WHERE buzon_id = p_buzon_id;
    
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
END;
$$;

-- Eliminar buzón
CREATE OR REPLACE FUNCTION buzon_eliminar(p_buzon_id BIGINT) 
RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
    doc_count INTEGER;
BEGIN
    -- Verificar si el buzón tiene documentos relacionados
    SELECT COUNT(*) INTO doc_count 
    FROM siga_documento 
    WHERE doc_buzonorigen_id = p_buzon_id;
    
    IF doc_count > 0 THEN
        RETURN -1; -- El buzón tiene documentos dependientes
    END IF;
    
    -- Verificar si hay usuarios asignados a este buzón
    SELECT COUNT(*) INTO doc_count 
    FROM siga_asignacion_usuario_buzon 
    WHERE asig_buzonid = p_buzon_id;
    
    IF doc_count > 0 THEN
        RETURN -2; -- El buzón tiene usuarios asignados
    END IF;
    
    -- Si no hay dependencias, proceder con la eliminación
    DELETE FROM siga_buzon WHERE buzon_id = p_buzon_id;
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    
    RETURN rows_affected;
EXCEPTION
    WHEN foreign_key_violation THEN
        RETURN -3; -- Violación de clave foránea no capturada en verificaciones previas
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la eliminación del buzón: %', SQLERRM;
        RETURN -99; -- Error desconocido
END;
$$;
