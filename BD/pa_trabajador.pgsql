CREATE OR REPLACE FUNCTION trabajador_insertar(
    p_dni VARCHAR(8),
    p_nombre VARCHAR(255),
    p_apellidopat VARCHAR(255),
    p_apellidomat VARCHAR(255),
    p_email VARCHAR(255),
    p_telefono VARCHAR(45),
    p_celular VARCHAR(45),
    p_fotourl VARCHAR(255),
    p_cargo VARCHAR(255),
    p_usuario VARCHAR(45),
    p_password VARCHAR(255),
    p_fnacimiento TIMESTAMP,
    p_oficina_id BIGINT,
    p_rol_id BIGINT
) RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
    new_tra_id BIGINT;
BEGIN
    INSERT INTO siga_trabajador (
        tra_dni,
        tra_nombre,
        tra_apellidopat,
        tra_apellidomat,
        tra_email,
        tra_telefono,
        tra_celular,
        tra_fotourl,
        tra_cargo,
        tra_usuario,
        tra_password,
        tra_fnacimiento,
        tra_oficina_id,
        tra_rol_id
    ) VALUES (
        p_dni,
        p_nombre,
        p_apellidopat,
        p_apellidomat,
        p_email,
        p_telefono,
        p_celular,
        p_fotourl,
        p_cargo,
        p_usuario,
        p_password,
        p_fnacimiento,
        p_oficina_id,
        p_rol_id
    )
    RETURNING tra_id INTO new_tra_id;

    RETURN new_tra_id;

EXCEPTION
    WHEN unique_violation THEN
        RAISE NOTICE 'Violación de restricción UNIQUE. DNI o usuario ya existen: %', SQLERRM;
        RETURN -1;
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL. Algunos campos requeridos están vacíos: %', SQLERRM;
        RETURN -2;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la inserción: %', SQLERRM;
        RETURN -3;
END;
$$;


--- *------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION trabajador_obtenerdatos(p_tra_id BIGINT)
RETURNS TABLE(
    id BIGINT,
    dni VARCHAR(8),
    nombre VARCHAR(255),
    apellidopat VARCHAR(255),
    apellidomat VARCHAR(255),
    email VARCHAR(255),
    telefono VARCHAR(45),
    celular VARCHAR(45),
    fotourl VARCHAR(255),
    cargo VARCHAR(255),
    usuario VARCHAR(45),
    esactivo BOOLEAN,
    fnacimiento TIMESTAMP,
    fechareg TIMESTAMP,
    oficina_id BIGINT,
    rol_id BIGINT,
    oficina_nombre VARCHAR(245)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        st.tra_id as id,
        st.tra_dni as dni,
        st.tra_nombre as nombre,
        st.tra_apellidopat as apellidopat,
        st.tra_apellidomat as apellidomat,
        st.tra_email as email,
        st.tra_telefono as telefono,
        st.tra_celular as celular,
        CASE WHEN st.tra_fotourl = '' THEN 'uploads/trabajador/avatar.png' ELSE st.tra_fotourl END AS fotourl,
        st.tra_cargo as cargo,
        st.tra_usuario as usuario,
        st.tra_esactivo as esactivo,
        st.tra_fnacimiento as fnacimiento,
        st.tra_fechareg as fechareg,
        st.tra_oficina_id as oficina_id,
        st.tra_rol_id as rol_id,        -- Cambié el orden, ahora está antes de oficina_nombre
        Ofi.ofi_nombre as oficina_nombre
    FROM siga_trabajador AS st
    LEFT JOIN siga_oficina Ofi ON Ofi.ofi_id = st.tra_oficina_id
    WHERE st.tra_id = p_tra_id;
END;
$$ LANGUAGE plpgsql;



--- *------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION trabajador_obtenerEliminados()
RETURNS TABLE(
    id BIGINT,
    dni VARCHAR(8),
    nombre VARCHAR(255),
    apellidopat VARCHAR(255),
    apellidomat VARCHAR(255),
    email VARCHAR(255),
    telefono VARCHAR(45),
    celular VARCHAR(45),
    fotourl VARCHAR(255),
    cargo VARCHAR(255),
    usuario VARCHAR(45),
    esactivo BOOLEAN,
    fnacimiento TIMESTAMP,
    fechareg TIMESTAMP,
    oficina_id BIGINT,
    rol_id BIGINT
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        st.tra_id as id,
        st.tra_dni as dni,
        st.tra_nombre as nombre,
        st.tra_apellidopat as apellidopat,
        st.tra_apellidomat as apellidomat,
        st.tra_email as email,
        st.tra_telefono as telefono,
        st.tra_celular as celular,
        CASE WHEN st.tra_fotourl = '' THEN 'uploads/trabajador/avatar.png' ELSE st.tra_fotourl END AS fotourl,
        st.tra_cargo as cargo,
        st.tra_usuario as usuario,
        st.tra_esactivo as esactivo,
        st.tra_fnacimiento as fnacimiento,
        st.tra_fechareg as fechareg,
        st.tra_oficina_id as oficina_id,
        st.tra_rol_id as rol_id
    FROM siga_trabajador AS st
    WHERE st.tra_esactivo = FALSE;
END;
$$ LANGUAGE plpgsql;



--- *------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION trabajador_obtenerlista()
RETURNS TABLE(
    id BIGINT,
    dni VARCHAR(8),
    nombre VARCHAR(255),
    apellidopat VARCHAR(255),
    apellidomat VARCHAR(255),
    email VARCHAR(255),
    telefono VARCHAR(45),
    celular VARCHAR(45),
    fotourl VARCHAR(255),
    cargo VARCHAR(255),
    usuario VARCHAR(45),
    esactivo BOOLEAN,
    fnacimiento TIMESTAMP,
    fechareg TIMESTAMP,
    oficina_id BIGINT,
    nombreoficina VARCHAR(45),
    rol_id BIGINT
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        st.tra_id as id,
        st.tra_dni as dni,
        st.tra_nombre as nombre,
        st.tra_apellidopat as apellidopat,
        st.tra_apellidomat as apellidomat,
        st.tra_email as email,
        st.tra_telefono as telefono,
        st.tra_celular as celular,
        CASE WHEN st.tra_fotourl = '' THEN 'uploads/trabajador/avatar.png' ELSE st.tra_fotourl END AS fotourl,
        st.tra_cargo as cargo,
        st.tra_usuario as usuario,
        st.tra_esactivo as esactivo,
        st.tra_fnacimiento as fnacimiento,
        st.tra_fechareg as fechareg,
        st.tra_oficina_id as oficina_id,
		ofi.ofi_nombre as nombreoficina,
        st.tra_rol_id as rol_id
    FROM siga_trabajador AS st
	inner join  siga_oficina ofi  on  ofi.ofi_id = st.tra_oficina_id;
END;
$$ LANGUAGE plpgsql;


--- *------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION trabajador_obtener_credenciales(
    p_usuario VARCHAR(45)
)
RETURNS TABLE(
    id BIGINT,
    password VARCHAR(255),
    esactivo BOOLEAN
) AS $$
BEGIN
    RETURN QUERY
    SELECT tra_id as id, tra_password as password, tra_esactivo as esactivo
    FROM siga_trabajador
    WHERE tra_usuario = p_usuario;
END;
$$ LANGUAGE plpgsql;


--- *------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION trabajador_actualizardatos(
    p_tra_id BIGINT,
    p_dni VARCHAR(8),
    p_nombre VARCHAR(255),
    p_apellidopat VARCHAR(255),
    p_apellidomat VARCHAR(255),
    p_email VARCHAR(255),
    p_telefono VARCHAR(45),
    p_celular VARCHAR(45),
    p_fotourl VARCHAR(255),
    p_cargo VARCHAR(255),
    p_esactivo BOOLEAN,
    p_fnacimiento TIMESTAMP,
    p_oficina_id BIGINT,
    p_rol_id BIGINT
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_trabajador
    SET
        tra_dni = p_dni,
        tra_nombre = p_nombre,
        tra_apellidopat = p_apellidopat,
        tra_apellidomat = p_apellidomat,
        tra_email = p_email,
        tra_telefono = p_telefono,
        tra_celular = p_celular,
        tra_fotourl = p_fotourl,
        tra_cargo = p_cargo,
        tra_esactivo = p_esactivo,
        tra_fnacimiento = p_fnacimiento,
        tra_oficina_id = p_oficina_id,
        tra_rol_id = p_rol_id
    WHERE tra_id = p_tra_id;
    
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    
    IF rows_affected = 0 THEN
        RETURN -1; -- Indica que no se encontró el trabajador con ese ID
    END IF;
    
    RETURN rows_affected;

EXCEPTION
    WHEN unique_violation THEN
        RETURN -2; -- Error de duplicación (DNI o algún campo único)
    WHEN OTHERS THEN
        RETURN -3; -- Error inesperado
END;
$$;

--- *------------------------------------------------------------------------------



CREATE OR REPLACE FUNCTION trabajador_actualizarfotoperfil(
    p_tra_id BIGINT,
    p_fotourl VARCHAR(255)
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_trabajador
    SET
        tra_fotourl = p_fotourl
    WHERE tra_id = p_tra_id;
    
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
END;
$$;



--- *------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION trabajador_actualizarpassword(
    p_tra_id BIGINT,
    p_password VARCHAR(255)   
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_trabajador
    SET
        tra_password = p_password
    WHERE tra_id     = p_tra_id;
    
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
END;
$$;


--- *------------------------------------------------------------------------------

CREATE OR REPLACE FUNCTION trabajador_actualizarestado(
    p_tra_id BIGINT,
    p_esactivo BOOLEAN
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    UPDATE siga_trabajador
    SET
        tra_esactivo = p_esactivo,
        tra_fechareg = CURRENT_TIMESTAMP
    WHERE tra_id = p_tra_id;
    
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
END;
$$;


--- *------------------------------------------------------------------------------


CREATE OR REPLACE FUNCTION trabajador_eliminar(p_tra_id BIGINT) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    rows_affected INTEGER;
BEGIN
    DELETE FROM siga_trabajador WHERE tra_id = p_tra_id;
    GET DIAGNOSTICS rows_affected = ROW_COUNT;
    RETURN rows_affected;
END;
$$;











































CREATE OR REPLACE FUNCTION licencia_insertar(
    p_licencia_tipo VARCHAR(55),
    p_licencia_numero INTEGER,
    p_licencia_titular VARCHAR(255),
    p_licencia_ruc VARCHAR(20),
    p_licencia_nombrecomercial VARCHAR(255),
    p_licencia_dnilegal VARCHAR(55),
    p_licencia_nombrerepresentante VARCHAR(55),
    p_licencia_actividadcomercial VARCHAR(255),
    p_licencia_area VARCHAR(55),
    p_licencia_direccioncomercial VARCHAR(55),
    p_licencia_tipoinspeccion VARCHAR(55),
    p_licencia_resultado_zonificacion VARCHAR(55),
    p_licencia_resultado_itse VARCHAR(55),
    p_licencia_estado_vigencia VARCHAR(55),
    p_licencia_riesgo VARCHAR(55),
    p_licencia_fechaemision TIMESTAMP,
    p_licencia_observacion VARCHAR(455),
    p_licencia_documento_id BIGINT
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_affected INT;
BEGIN
    INSERT INTO siga_licencia (
        licencia_tipo,
        licencia_numero,
        licencia_titular,
        licencia_ruc,
        licencia_nombrecomercial,
        licencia_dnilegal,
        licencia_nombrerepresentante,
        licencia_actividadcomercial,
        licencia_area,
        licencia_direccioncomercial,
        licencia_tipoinspeccion,
        licencia_resultado_zonificacion,
        licencia_resultado_itse,
        licencia_estado_vigencia,
        licencia_riesgo,
        licencia_fechaemision,
        licencia_anio,
        licencia_estado,
        licencia_observacion,
        licencia_fecharegistro,
        licencia_fechaultimamod,
        licencia_resolucion_url,
        licencia_documento_url,
        licencia_documento_id,
        licencia_consultasverificacion,
        licencia_codigoqr
    ) VALUES (
        p_licencia_tipo,
        p_licencia_numero,
        p_licencia_titular,
        p_licencia_ruc,
        p_licencia_nombrecomercial,
        p_licencia_dnilegal,
        p_licencia_nombrerepresentante,
        p_licencia_actividadcomercial,
        p_licencia_area,
        p_licencia_direccioncomercial,
        p_licencia_tipoinspeccion,
        p_licencia_resultado_zonificacion,
        p_licencia_resultado_itse,
        p_licencia_estado_vigencia,
        p_licencia_riesgo,
        p_licencia_fechaemision,
        EXTRACT(YEAR FROM p_licencia_fechaemision),
        'activo',
        p_licencia_observacion,
        CURRENT_TIMESTAMP,
        CURRENT_TIMESTAMP,
        '',
        '',
        p_licencia_documento_id,
        0,
        ''
    );

    GET DIAGNOSTICS v_rows_affected = ROW_COUNT;

    RETURN v_rows_affected > 0;
END;
$$;


CREATE OR REPLACE FUNCTION licencia_actualizar(
    p_licencia_idlic BIGINT,
    p_licencia_tipo VARCHAR(55),
    p_licencia_numero INTEGER,
    p_licencia_titular VARCHAR(255),
    p_licencia_ruc VARCHAR(20),
    p_licencia_nombrecomercial VARCHAR(255),
    p_licencia_dnilegal VARCHAR(55),
    p_licencia_nombrerepresentante VARCHAR(55),
    p_licencia_actividadcomercial VARCHAR(255),
    p_licencia_area VARCHAR(55),
    p_licencia_direccioncomercial VARCHAR(55),
    p_licencia_tipoinspeccion VARCHAR(55),
    p_licencia_resultado_zonificacion VARCHAR(55),
    p_licencia_resultado_itse VARCHAR(55),
    p_licencia_estado_vigencia VARCHAR(55),
    p_licencia_riesgo VARCHAR(55),
    p_licencia_fechaemision TIMESTAMP,
    p_licencia_estado VARCHAR(55),
    p_licencia_observacion VARCHAR(455)
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_affected INT;
BEGIN
    UPDATE siga_licencia
    SET
        licencia_tipo = p_licencia_tipo,
        licencia_numero = p_licencia_numero,
        licencia_titular = p_licencia_titular,
        licencia_ruc = p_licencia_ruc,
        licencia_nombrecomercial = p_licencia_nombrecomercial,
        licencia_dnilegal = p_licencia_dnilegal,
        licencia_nombrerepresentante = p_licencia_nombrerepresentante,
        licencia_actividadcomercial = p_licencia_actividadcomercial,
        licencia_area = p_licencia_area,
        licencia_direccioncomercial = p_licencia_direccioncomercial,
        licencia_tipoinspeccion = p_licencia_tipoinspeccion,
        licencia_resultado_zonificacion = p_licencia_resultado_zonificacion,
        licencia_resultado_itse = p_licencia_resultado_itse,
        licencia_estado_vigencia = p_licencia_estado_vigencia,
        licencia_riesgo = p_licencia_riesgo,
        licencia_fechaemision = p_licencia_fechaemision,
        licencia_anio = EXTRACT(YEAR FROM p_licencia_fechaemision),
        licencia_estado = p_licencia_estado,
        licencia_observacion = p_licencia_observacion,
        licencia_fechaultimamod = CURRENT_TIMESTAMP
    WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS v_rows_affected = ROW_COUNT;

    RETURN v_rows_affected > 0;
END;
$$;



CREATE OR REPLACE FUNCTION licencia_actualizardocumentos(
    p_licencia_idlic BIGINT,
    p_licencia_documento_url VARCHAR(455),
    p_licencia_documento_id BIGINT,
    p_licencia_consultasverificacion INTEGER
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_affected INT;
BEGIN
    UPDATE siga_licencia
    SET
        licencia_resolucion_url = p_licencia_resolucion_url,
        licencia_documento_url = p_licencia_documento_url,
        licencia_documento_id = p_licencia_documento_id,
        licencia_consultasverificacion = p_licencia_consultasverificacion,
        licencia_fechaultimamod = CURRENT_TIMESTAMP
    WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS v_rows_affected = ROW_COUNT;

    RETURN v_rows_affected > 0;
END;
$$;



CREATE OR REPLACE FUNCTION licencia_eliminar(p_licencia_idlic BIGINT)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_affected INT;
BEGIN
    DELETE FROM siga_licencia WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS v_rows_affected = ROW_COUNT;

    RETURN v_rows_affected > 0;
END;
$$;



CREATE OR REPLACE FUNCTION licencia_obtenerdatos(p_licencia_idlic BIGINT)
RETURNS TABLE(
    idlic BIGINT,
    tipo VARCHAR(55),
    numero INTEGER,
    titular VARCHAR(255),
    ruc VARCHAR(20),
    nombrecomercial VARCHAR(255),
    dnilegal VARCHAR(55),
    nombrerepresentante VARCHAR(55),
    actividadcomercial VARCHAR(255),
    area VARCHAR(55),
    direccioncomercial VARCHAR(55),
    tipoinspeccion VARCHAR(55),
    resultado_zonificacion VARCHAR(55),
    resultado_itse VARCHAR(55),
    estado_vigencia VARCHAR(55),
    riesgo VARCHAR(55),
    fechaemision TIMESTAMP,
    anio VARCHAR(55),
    estado VARCHAR(55),
    observacion VARCHAR(455),
    fecharegistro TIMESTAMP,
    fechaultimamod TIMESTAMP,
    resolucion_url VARCHAR(455),
    documento_url VARCHAR(455),
    documento_id BIGINT,
    consultasverificacion INTEGER,
    codigoqr VARCHAR(255)
) 
LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT
        licencia_idlic as idlic,
        licencia_tipo as tipo,
        licencia_numero as numero,
        licencia_titular as titular,
        licencia_ruc as ruc,
        licencia_nombrecomercial as nombrecomercial,
        licencia_dnilegal as dnilegal,
        licencia_nombrerepresentante as nombrerepresentante,
        licencia_actividadcomercial as actividadcomercial,
        licencia_area as area,
        licencia_direccioncomercial as direccioncomercial,
        licencia_tipoinspeccion as tipoinspeccion,
        licencia_resultado_zonificacion as resultado_zonificacion,
        licencia_resultado_itse as resultado_itse,
        licencia_estado_vigencia as estado_vigencia,
        licencia_riesgo as riesgo,
        licencia_fechaemision as fechaemision,
        licencia_anio as anio,
        licencia_estado as estado,
        licencia_observacion as observacion,
        licencia_fecharegistro as fecharegistro,
        licencia_fechaultimamod as fechaultimamod,
        licencia_resolucion_url as resolucion_url,
        licencia_documento_url as documento_url,
        licencia_documento_id as documento_id,
        licencia_consultasverificacion as consultasverificacion,
        licencia_codigoqr as codigoqr
    FROM siga_licencia
    WHERE licencia_idlic = p_licencia_idlic;
END;
$$;



CREATE OR REPLACE FUNCTION licencia_listar()
RETURNS TABLE(
    idlic BIGINT,
    tipo VARCHAR(55),
    numero INTEGER,
    titular VARCHAR(255),
    ruc VARCHAR(20),
    nombrecomercial VARCHAR(255),
    dnilegal VARCHAR(55),
    nombrerepresentante VARCHAR(55),
    actividadcomercial VARCHAR(255),
    area VARCHAR(55),
    direccioncomercial VARCHAR(55),
    tipoinspeccion VARCHAR(55),
    resultado_zonificacion VARCHAR(55),
    resultado_itse VARCHAR(55),
    estado_vigencia VARCHAR(55),
    riesgo VARCHAR(55),
    fechaemision TIMESTAMP,
    anio VARCHAR(55),
    estado VARCHAR(55),
    observacion VARCHAR(455),
    fecharegistro TIMESTAMP,
    fechaultimamod TIMESTAMP,
    resolucion_url VARCHAR(455),
    documento_url VARCHAR(455),
    documento_id BIGINT,
    consultasverificacion INTEGER,
    codigoqr VARCHAR(255)
)
LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT
        l.licencia_idlic as idlic,
        l.licencia_tipo as tipo,
        l.licencia_numero as numero,
        l.licencia_titular as titular,
        l.licencia_ruc as ruc,
        l.licencia_nombrecomercial as nombrecomercial,
        l.licencia_dnilegal as dnilegal,
        l.licencia_nombrerepresentante as nombrerepresentante,
        l.licencia_actividadcomercial as actividadcomercial,
        l.licencia_area as area,
        l.licencia_direccioncomercial as direccioncomercial,
        l.licencia_tipoinspeccion as tipoinspeccion,
        l.licencia_resultado_zonificacion as resultado_zonificacion,
        l.licencia_resultado_itse as resultado_itse,
        l.licencia_estado_vigencia as estado_vigencia,
        l.licencia_riesgo as riesgo,
        l.licencia_fechaemision as fechaemision,
        l.licencia_anio as anio,
        l.licencia_estado as estado,
        l.licencia_observacion as observacion,
        l.licencia_fecharegistro as fecharegistro,
        l.licencia_fechaultimamod as fechaultimamod,
        l.licencia_resolucion_url as resolucion_url,
        l.licencia_documento_url as documento_url,
        l.licencia_documento_id as documento_id,
        l.licencia_consultasverificacion as consultasverificacion,
        l.licencia_codigoqr as codigoqr
    FROM siga_licencia l;
END;
$$;
