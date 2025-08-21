CREATE OR REPLACE FUNCTION administrado_obtenerdatos(p_adm_id BIGINT)
RETURNS TABLE(
    id BIGINT,
    tipopersona VARCHAR(255),
    tipodocumento VARCHAR(45),
    numdocumento VARCHAR(11),
    nombre VARCHAR(255),
    apellidopat VARCHAR(255),
    apellidomat VARCHAR(255),
    razonsocial VARCHAR(255),
    direccion VARCHAR(255),
    celular VARCHAR(45),
    correo VARCHAR(45),
    ubigeoid BIGINT,
    fecharegistro TIMESTAMP,
    estado VARCHAR(45),
    nombreusuario VARCHAR(255),
    usuario VARCHAR(45)
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        sa.adm_id AS id,
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
        sa.adm_estado AS estado,
        sa.adm_nombreusuario AS nombreusuario,
        sa.adm_usuario AS usuario
    FROM siga_administrado AS sa
    WHERE sa.adm_id = p_adm_id;
END;
$$ LANGUAGE plpgsql;


-------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION administrado_obtenerdatos_pordocumento(doc VARCHAR)
RETURNS TABLE(
    id BIGINT,
    tipopersona VARCHAR(255),
    tipodocumento VARCHAR(45),
    numdocumento VARCHAR(11),
    nombre VARCHAR(255),
    apellidopat VARCHAR(255),
    apellidomat VARCHAR(255),
    razonsocial VARCHAR(255),
    direccion VARCHAR(255),
    celular VARCHAR(45),
    correo VARCHAR(45),
    ubigeoid BIGINT,
    fecharegistro TIMESTAMP,
    estado VARCHAR(45),
    nombreusuario VARCHAR(255),
    usuario VARCHAR(45)
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        sa.adm_id AS id,
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
        sa.adm_estado AS estado,
        sa.adm_nombreusuario AS nombreusuario,
        sa.adm_usuario AS usuario
    FROM siga_administrado AS sa
    WHERE sa.adm_numdocumento = doc;
END;
$$ LANGUAGE plpgsql;

-----------------------------------------------------------
CREATE OR REPLACE FUNCTION administrado_listartabla()
RETURNS TABLE(
    id BIGINT,
    tipopersona VARCHAR(255),
    tipodocumento VARCHAR(45),
    numdocumento VARCHAR(11),
    nombre VARCHAR(255),
    apellidopat VARCHAR(255),
    apellidomat VARCHAR(255),
    direccion VARCHAR(255),
    celular VARCHAR(45),
    correo VARCHAR(45),
    fecharegistro TIMESTAMP,
    estado VARCHAR(45),
    nombreusuario VARCHAR(255),
    usuario VARCHAR(45)
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        sa.adm_id AS id,
        sa.adm_tipopersona AS tipopersona,
        sa.adm_tipodocumento AS tipodocumento,
        sa.adm_numdocumento AS numdocumento,
        sa.adm_nombre AS nombre,
        sa.adm_apellidopat AS apellidopat,
        sa.adm_apellidomat AS apellidomat,
        sa.adm_direccion AS direccion,
        sa.adm_celular AS celular,
        sa.adm_correo AS correo,
        sa.adm_fecharegistro AS fecharegistro,
        sa.adm_estado AS estado,
        sa.adm_nombreusuario AS nombreusuario,
        sa.adm_usuario AS usuario
    FROM siga_administrado AS sa;
END;
$$ LANGUAGE plpgsql;


-----------------------------------------------------------
CREATE OR REPLACE FUNCTION administrado_insertar(
    p_adm_tipopersona VARCHAR(255),
    p_adm_tipodocumento VARCHAR(45),
    p_adm_numdocumento VARCHAR(11),
    p_adm_nombre VARCHAR(255),
    p_adm_apellidopat VARCHAR(255),
    p_adm_apellidomat VARCHAR(255),
    p_adm_razonsocial VARCHAR(255),
    p_adm_direccion VARCHAR(255),
    p_adm_celular VARCHAR(45),
    p_adm_correo VARCHAR(45),
    p_adm_ubigeoid BIGINT
) RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
    new_adm_id BIGINT;
BEGIN
    -- Intento de inserción
    INSERT INTO siga_administrado (
        adm_tipopersona,
        adm_tipodocumento,
        adm_numdocumento,
        adm_nombre,
        adm_apellidopat,
        adm_apellidomat,
        adm_razonsocial,
        adm_direccion,
        adm_celular,
        adm_correo,
        adm_ubigeoid,
        adm_estado,
        adm_fecharegistro,
        adm_usuario,
        adm_rol

    ) VALUES (
        p_adm_tipopersona,
        p_adm_tipodocumento,
        p_adm_numdocumento,
        p_adm_nombre,
        p_adm_apellidopat,
        p_adm_apellidomat,
        p_adm_razonsocial,
        p_adm_direccion,
        p_adm_celular,
        p_adm_correo,
        p_adm_ubigeoid,
        'Inactivo',
        CURRENT_TIMESTAMP,
        p_adm_numdocumento,
        5
    )
    RETURNING adm_id INTO new_adm_id;

    -- Retornar el ID del nuevo administrado
    RETURN new_adm_id;

EXCEPTION
    -- Manejo de violación de restricciones UNIQUE (número de documento duplicado)
    WHEN unique_violation THEN
        RAISE NOTICE 'Violación de restricción UNIQUE. El número de documento ya existe: %', SQLERRM;
        RETURN -1;

    -- Manejo de violación de restricciones NOT NULL
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL. Algunos campos requeridos están vacíos: %', SQLERRM;
        RETURN -2;

    -- Captura de cualquier otro error
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la inserción: %', SQLERRM;
        RETURN -3;
END;
$$;


-----------------------------------------------------------
CREATE OR REPLACE FUNCTION administrado_actualizar(
    p_adm_id BIGINT,
    p_adm_tipopersona VARCHAR(255),
    p_adm_tipodocumento VARCHAR(45),
    p_adm_numdocumento VARCHAR(11),
    p_adm_nombre VARCHAR(255),
    p_adm_apellidopat VARCHAR(255),
    p_adm_apellidomat VARCHAR(255),
    p_adm_razonsocial VARCHAR(255),
    p_adm_direccion VARCHAR(255),
    p_adm_celular VARCHAR(45),
    p_adm_correo VARCHAR(45)
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_updated INTEGER;
BEGIN
    -- Intento de actualización
    UPDATE siga_administrado
    SET
        adm_tipopersona = p_adm_tipopersona,
        adm_tipodocumento = p_adm_tipodocumento,
        adm_numdocumento = p_adm_numdocumento,
        adm_nombre = p_adm_nombre,
        adm_apellidopat = p_adm_apellidopat,
        adm_apellidomat = p_adm_apellidomat,
        adm_razonsocial = p_adm_razonsocial,
        adm_direccion = p_adm_direccion,
        adm_celular = p_adm_celular,
        adm_correo = p_adm_correo
    WHERE adm_id = p_adm_id;

    GET DIAGNOSTICS v_rows_updated = ROW_COUNT;

    -- Retorna 1 si se actualizó al menos una fila
    RETURN v_rows_updated;

EXCEPTION
    -- Manejo de violación de restricciones UNIQUE
    WHEN unique_violation THEN
        RAISE NOTICE 'Violación de restricción UNIQUE. El número de documento ya existe: %', SQLERRM;
        RETURN -1;

    -- Manejo de violación de restricciones NOT NULL
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL. Algunos campos requeridos están vacíos: %', SQLERRM;
        RETURN -2;

    -- Captura de cualquier otro error
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la actualización: %', SQLERRM;
        RETURN -3;
END;
$$;


-----------------------------------------------------------

-----------------------------------------------------------

-- CREATE OR REPLACE FUNCTION administrado_toggleactive(
--     p_adm_id BIGINT,
--     p_adm_esactivo BOOLEAN
-- ) RETURNS BOOLEAN
-- LANGUAGE plpgsql
-- AS $$
-- DECLARE
--     v_rows_updated INTEGER;
-- BEGIN
--     UPDATE siga_administrado
--     SET
--         adm_esactivo = p_adm_esactivo
--     WHERE adm_id = p_adm_id;

--     GET DIAGNOSTICS v_rows_updated = ROW_COUNT;

--     RETURN v_rows_updated > 0;
-- END;
-- $$;
