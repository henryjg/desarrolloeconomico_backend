-- *--------------------------------------------------

CREATE OR REPLACE FUNCTION oficina_insertar(
    p_nombre VARCHAR(45),
    p_padre_id BIGINT
) RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
    new_ofi_id BIGINT;
BEGIN
    INSERT INTO siga_oficina (
        ofi_nombre,
        ofi_padre_id
    ) VALUES (
        p_nombre,
        p_padre_id
    )
    RETURNING ofi_id INTO new_ofi_id;

    RETURN new_ofi_id;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la inserción: %', SQLERRM;
        RETURN -1;
END;
$$;


-- *--------------------------------------------------

CREATE OR REPLACE FUNCTION oficina_actualizar(
    p_ofi_id BIGINT,
    p_nombre VARCHAR(45),
    p_padre_id BIGINT
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_oficina
    SET
        ofi_nombre = p_nombre,
        ofi_padre_id = p_padre_id
    WHERE ofi_id = p_ofi_id;
    
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    
    IF rows_affected = 0 THEN
        RETURN -1; -- Indica que no se encontró la oficina con ese ID
    END IF;
    
    RETURN rows_affected;

EXCEPTION
    WHEN OTHERS THEN
        RETURN -2; -- Error inesperado
END;
$$;


-- *--------------------------------------------------

CREATE OR REPLACE FUNCTION oficina_eliminar(p_ofi_id BIGINT) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    DELETE FROM siga_oficina WHERE ofi_id = p_ofi_id;
    
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
END;
$$;


-- *--------------------------------------------------

CREATE OR REPLACE FUNCTION oficina_obtener(p_ofi_id BIGINT)
RETURNS TABLE(
    id BIGINT,
    nombre VARCHAR(45),
    padre_id BIGINT
) LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT
        ofi_id as id,
        ofi_nombre as nombre,
        ofi_padre_id as padre_id
    FROM siga_oficina
    WHERE ofi_id = p_ofi_id;
END;
$$;


-- *--------------------------------------------------

CREATE OR REPLACE FUNCTION oficina_listar()
RETURNS TABLE(
    id BIGINT,
    nombre VARCHAR(45),
    padre_id BIGINT
) LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT
        ofi_id as id,
        ofi_nombre as nombre,
        ofi_padre_id as padre_id
    FROM siga_oficina;
END;
$$;

-- *--------------------------------------------------
