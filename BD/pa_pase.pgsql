-- *--------------------------------------------------
CREATE OR REPLACE FUNCTION pase_insertar(
    p_pase_buzonorigen_id BIGINT,
    p_pase_buzondestino_id BIGINT,
    p_pase_tipo VARCHAR(45),
    p_pase_proveido VARCHAR(255),
    p_pase_observacion VARCHAR(255),
    p_pase_estado VARCHAR(255),
    p_pase_documento_id BIGINT,
    p_pase_usuario_id VARCHAR(255),
    p_pase_usuarionombre VARCHAR(255)
) RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
    new_pase_id BIGINT;
BEGIN
    INSERT INTO siga_pase (
        pase_buzonorigen_id,
        pase_buzondestino_id,
        pase_tipo,
        pase_proveido,
        pase_observacion,
        pase_estado,
        pase_documento_id,
        pase_usuario_id,
        pase_usuarionombre
    ) VALUES (
        p_pase_buzonorigen_id,
        p_pase_buzondestino_id,
        p_pase_tipo,
        p_pase_proveido,
        p_pase_observacion,
        p_pase_estado,
        p_pase_documento_id,
        p_pase_usuario_id,
        p_pase_usuarionombre
    ) RETURNING pase_id INTO new_pase_id;

    RETURN new_pase_id;

EXCEPTION
    WHEN unique_violation THEN
        RAISE NOTICE 'Violación de restricción UNIQUE. Los datos ya existen: %', SQLERRM;
        RETURN -1;
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL. Campos requeridos vacíos: %', SQLERRM;
        RETURN -2;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la inserción: %', SQLERRM;
        RETURN -3;
END;
$$;

-- *--------------------------------------------------
CREATE OR REPLACE FUNCTION pase_obtener(p_pase_id BIGINT)
RETURNS TABLE(
    pase_id BIGINT,
    pase_buzonorigen_id BIGINT,
    pase_buzondestino_id BIGINT,
    pase_tipo VARCHAR(45),
    pase_proveido VARCHAR(255),
    pase_observacion VARCHAR(255),
    pase_estado VARCHAR(255),
    pase_documento_id BIGINT,
    pase_usuario_id VARCHAR(255),
    pase_usuarionombre VARCHAR(255),
    pase_fechahoraregistro TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        pase_id,
        pase_buzonorigen_id,
        pase_buzondestino_id,
        pase_tipo,
        pase_proveido,
        pase_observacion,
        pase_estado,
        pase_documento_id,
        pase_usuario_id,
        pase_usuarionombre,
        pase_fechahoraregistro
    FROM siga_pase
    WHERE pase_id = p_pase_id;
END;
$$ LANGUAGE plpgsql;

-- *--------------------------------------------------
CREATE OR REPLACE FUNCTION pase_listar()
RETURNS TABLE(
    pase_id BIGINT,
    pase_buzonorigen_id BIGINT,
    pase_buzondestino_id BIGINT,
    pase_tipo VARCHAR(45),
    pase_proveido VARCHAR(255),
    pase_observacion VARCHAR(255),
    pase_estado VARCHAR(255),
    pase_documento_id BIGINT,
    pase_usuario_id VARCHAR(255),
    pase_usuarionombre VARCHAR(255),
    pase_fechahoraregistro TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        pase_id,
        pase_buzonorigen_id,
        pase_buzondestino_id,
        pase_tipo,
        pase_proveido,
        pase_observacion,
        pase_estado,
        pase_documento_id,
        pase_usuario_id,
        pase_usuarionombre,
        pase_fechahoraregistro
    FROM siga_pase;
END;
$$ LANGUAGE plpgsql;

-- *--------------------------------------------------
CREATE OR REPLACE FUNCTION pase_actualizar(
    p_pase_id BIGINT,
    p_pase_buzonorigen_id BIGINT,
    p_pase_buzondestino_id BIGINT,
    p_pase_tipo VARCHAR(45),
    p_pase_proveido VARCHAR(255),
    p_pase_observacion VARCHAR(255),
    p_pase_estado VARCHAR(255),
    p_pase_documento_id BIGINT,
    p_pase_usuario_id VARCHAR(255),
    p_pase_usuarionombre VARCHAR(255)
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_updated INTEGER;
BEGIN
    UPDATE siga_pase
    SET
        pase_buzonorigen_id = p_pase_buzonorigen_id,
        pase_buzondestino_id = p_pase_buzondestino_id,
        pase_tipo = p_pase_tipo,
        pase_proveido = p_pase_proveido,
        pase_observacion = p_pase_observacion,
        pase_estado = p_pase_estado,
        pase_documento_id = p_pase_documento_id,
        pase_usuario_id = p_pase_usuario_id,
        pase_usuarionombre = p_pase_usuarionombre
    WHERE pase_id = p_pase_id;

    GET DIAGNOSTICS v_rows_updated = ROW_COUNT;

    RETURN v_rows_updated;

EXCEPTION
    WHEN unique_violation THEN
        RAISE NOTICE 'Violación de restricción UNIQUE. Los datos ya existen: %', SQLERRM;
        RETURN -1;
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL. Campos requeridos vacíos: %', SQLERRM;
        RETURN -2;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la actualización: %', SQLERRM;
        RETURN -3;
END;
$$;

-- *--------------------------------------------------
CREATE OR REPLACE FUNCTION pase_eliminar(
    p_pase_id BIGINT
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_deleted INTEGER;
BEGIN
    DELETE FROM siga_pase WHERE pase_id = p_pase_id;

    GET DIAGNOSTICS v_rows_deleted = ROW_COUNT;

    RETURN v_rows_deleted;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la eliminación: %', SQLERRM;
        RETURN -1;
END;
$$;






-- *--------------------------------------------------
CREATE OR REPLACE FUNCTION pase_actualizar_estado_enviado(
    p_pase_id BIGINT,
    p_pase_estado VARCHAR(255)
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_updated INTEGER;
BEGIN
    UPDATE siga_pase
    SET
        pase_estado = p_pase_estado,
        pase_fechaenvio = CURRENT_TIMESTAMP
    WHERE pase_id = p_pase_id;

    GET DIAGNOSTICS v_rows_updated = ROW_COUNT;

    RETURN v_rows_updated;

EXCEPTION
    WHEN unique_violation THEN
        RAISE NOTICE 'Violación de restricción UNIQUE. Los datos ya existen: %', SQLERRM;
        RETURN -1;
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL. Campos requeridos vacíos: %', SQLERRM;
        RETURN -2;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la actualización: %', SQLERRM;
        RETURN -3;
END;
$$;

-- *--------------------------------------------------
CREATE OR REPLACE FUNCTION pase_actualizar_estado_recibir(
    p_pase_id BIGINT,
    p_pase_estado VARCHAR(255)
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_updated INTEGER;
BEGIN
    UPDATE siga_pase
    SET
        pase_estado = p_pase_estado,
        pase_fecharecepcion = CURRENT_TIMESTAMP
    WHERE pase_id = p_pase_id;

    GET DIAGNOSTICS v_rows_updated = ROW_COUNT;

    RETURN v_rows_updated;

EXCEPTION
    WHEN unique_violation THEN
        RAISE NOTICE 'Violación de restricción UNIQUE. Los datos ya existen: %', SQLERRM;
        RETURN -1;
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL. Campos requeridos vacíos: %', SQLERRM;
        RETURN -2;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la actualización: %', SQLERRM;
        RETURN -3;
END;
$$;