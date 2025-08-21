-- Función para insertar un nuevo registro en siga_usuariocasilla

-- Función para insertar un nuevo registro en siga_usuariocasilla
CREATE OR REPLACE FUNCTION usuariocasilla_insertar(
    p_user_tipopersona VARCHAR(20),
    p_user_tipodocumento VARCHAR(20),
    p_user_numdocumento VARCHAR(45),
    p_user_nombre VARCHAR(255),
    p_user_apellidopat VARCHAR(255),
    p_user_apellidomat VARCHAR(255),
    p_user_razonsocial VARCHAR(255),
    p_user_celular VARCHAR(15),
    p_user_correo VARCHAR(45),
    p_user_codigoverificacion VARCHAR(4),
    p_user_nombreusuario VARCHAR(255),
    p_user_usuario VARCHAR(45),
    p_user_password VARCHAR(255),
    p_user_rol VARCHAR(20)
) RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
    new_user_id BIGINT;
    err_constraint text;
BEGIN
    INSERT INTO siga_usuariocasilla (
        user_tipopersona,
        user_tipodocumento,
        user_numdocumento,
        user_nombre,
        user_apellidopat,
        user_apellidomat,
        user_razonsocial,
        user_celular,
        user_correo,
        user_codigoverificacion,
        user_nombreusuario,
        user_usuario,
        user_password,
        user_rol,
        user_estado,
        user_fecharegistro,
        user_fechaactualizacion,
        user_fecha_limite_recuperacion
    ) VALUES (
        p_user_tipopersona,
        p_user_tipodocumento,
        p_user_numdocumento,
        p_user_nombre,
        p_user_apellidopat,
        p_user_apellidomat,
        p_user_razonsocial,
        p_user_celular,
        p_user_correo,
        p_user_codigoverificacion,
        p_user_nombreusuario,
        p_user_usuario,
        p_user_password,
        p_user_rol,
        FALSE,
        CURRENT_DATE,
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP
    )
    RETURNING user_id INTO new_user_id;
    RETURN new_user_id;
EXCEPTION
    WHEN unique_violation THEN
        GET STACKED DIAGNOSTICS err_constraint = CONSTRAINT_NAME;
        IF err_constraint = 'siga_usuariocasilla_user_usuario_key' THEN
            RAISE NOTICE 'Usuario ya existe: %', SQLERRM;
            RETURN -1;
        ELSIF err_constraint = 'siga_usuariocasilla_user_correo_key' THEN
            RAISE NOTICE 'Correo ya existe: %', SQLERRM;
            RETURN -2;
        ELSE
            RAISE NOTICE 'Otra violación única: %', SQLERRM;
            RETURN -3;
        END IF;
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL: %', SQLERRM;
        RETURN -4;
    WHEN SQLSTATE '22001' THEN
        RAISE NOTICE 'Valor demasiado largo para el campo: %', SQLERRM;
        RETURN -5;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la inserción: %', SQLERRM;
        RETURN -6;
END;
$$;






-- Función para obtener datos de un usuariocasilla por ID, incluyendo user_codigoverificacion
CREATE OR REPLACE FUNCTION usuariocasilla_obtenerdatos(p_user_id BIGINT)
RETURNS TABLE(
    id BIGINT,
    tipopersona VARCHAR(20),
    tipodocumento VARCHAR(20),
    numdocumento VARCHAR(45),
    nombre VARCHAR(255),
    apellidopat VARCHAR(255),
    apellidomat VARCHAR(255),
    razonsocial VARCHAR(255),
    celular VARCHAR(15),
    correo VARCHAR(45),
    codigoverificacion VARCHAR(4),
    correoverificado BOOLEAN,
    nombreusuario VARCHAR(255),
    usuario VARCHAR(45),
    estado BOOLEAN,
    rol VARCHAR(20),
    fecharegistro DATE,
    fechaactualizacion TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        su.user_id as id,
        su.user_tipopersona as tipopersona,
        su.user_tipodocumento as tipodocumento,
        su.user_numdocumento as numdocumento,
        su.user_nombre as nombre,
        su.user_apellidopat as apellidopat,
        su.user_apellidomat as apellidomat,
        su.user_razonsocial as razonsocial,
        su.user_celular as celular,
        su.user_correo as correo,
        su.user_codigoverificacion as codigoverificacion,
        su.user_correoverificado as correoverificado,
        su.user_nombreusuario as nombreusuario,
        su.user_usuario as usuario,
        su.user_estado as estado,
        su.user_rol as rol,
        su.user_fecharegistro as fecharegistro,
        su.user_fechaactualizacion as fechaactualizacion
    FROM siga_usuariocasilla AS su
    WHERE su.user_id = p_user_id;
END;
$$ LANGUAGE plpgsql;





-- Función corregida para verificar la cuenta de un usuariocasilla usando el código de verificación
CREATE OR REPLACE FUNCTION usuariocasilla_verificarcuenta(
    p_user_id BIGINT,
    p_codigoverificacion VARCHAR(4)
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_usuariocasilla
    SET
        user_correoverificado = TRUE,
        user_estado = TRUE
    WHERE user_id = p_user_id 
      AND user_codigoverificacion = p_codigoverificacion;
    
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la verificación: %', SQLERRM;
        RETURN -1;
END;
$$;





CREATE OR REPLACE FUNCTION usuariocasilla_actualizardatos(
    p_user_id BIGINT,
    p_user_tipopersona VARCHAR(20),
    p_user_tipodocumento VARCHAR(20),
    p_user_numdocumento VARCHAR(45),
    p_user_nombre VARCHAR(255),
    p_user_apellidopat VARCHAR(255),
    p_user_apellidomat VARCHAR(255),
    p_user_razonsocial VARCHAR(255),
    p_user_celular VARCHAR(15),
    p_user_correo VARCHAR(45),
    p_user_rol VARCHAR(20)
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
    err_constraint text;
BEGIN
    UPDATE siga_usuariocasilla AS su
    SET
        su.user_tipopersona = p_user_tipopersona,
        su.user_tipodocumento = p_user_tipodocumento,
        su.user_numdocumento = p_user_numdocumento,
        su.user_nombre = p_user_nombre,
        su.user_apellidopat = p_user_apellidopat,
        su.user_apellidomat = p_user_apellidomat,
        su.user_razonsocial = p_user_razonsocial,
        su.user_celular = p_user_celular,
        su.user_correo = p_user_correo,
        su.user_rol = p_user_rol,
        su.user_fechaactualizacion = CURRENT_TIMESTAMP
    WHERE su.user_id = p_user_id;
    
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
EXCEPTION
    WHEN unique_violation THEN
        GET STACKED DIAGNOSTICS err_constraint = CONSTRAINT_NAME;
        IF err_constraint = 'siga_usuariocasilla_user_correo_key' THEN
            RAISE NOTICE 'Correo ya existe: %', SQLERRM;
            RETURN -1;
        ELSE
            RAISE NOTICE 'Otra violación única: %', SQLERRM;
            RETURN -2;
        END IF;
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL: %', SQLERRM;
        RETURN -3;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la actualización: %', SQLERRM;
        RETURN -4;
END;
$$;




-- Función para actualizar la contraseña de un administrado
CREATE OR REPLACE FUNCTION usuariocasilla_actualizarpassword(
    p_adm_id BIGINT,
    p_adm_password VARCHAR(255)
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_administrado AS sa
    SET adm_password = p_adm_password,
        adm_fechaactualizacion = CURRENT_TIMESTAMP
    WHERE sa.adm_id = p_adm_id;
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la actualización de contraseña: %', SQLERRM;
        RETURN -1;
END;
$$;




-- Función para desactivar un administrado
CREATE OR REPLACE FUNCTION usuariocasilla_desactivar(
    p_adm_id BIGINT
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_administrado AS sa
    SET adm_estado = 'inactivo',
        adm_fechaactualizacion = CURRENT_TIMESTAMP
    WHERE sa.adm_id = p_adm_id;
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la desactivación: %', SQLERRM;
        RETURN -1;
END;
$$;



-- Función para eliminar un usuariocasilla
CREATE OR REPLACE FUNCTION usuariocasilla_eliminar(p_user_id BIGINT) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    DELETE FROM siga_usuariocasilla AS su WHERE su.user_id = p_user_id;
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
EXCEPTION
    WHEN foreign_key_violation THEN
        RAISE NOTICE 'No se puede eliminar el usuario porque hay registros dependientes: %', SQLERRM;
        RETURN -1;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la eliminación: %', SQLERRM;
        RETURN -2;
END;
$$;

