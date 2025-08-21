CREATE OR REPLACE FUNCTION insertar_licencia_completa(
    p_licencia_tipotramite_tupa BIGINT,
    p_licencia_negocio_ruc VARCHAR,
    p_licencia_negocio_razonsocial VARCHAR,
    p_licencia_negocio_direccionfiscal VARCHAR,
    p_licencia_negocio_nombrecomercial VARCHAR,
    p_licencia_negocio_actividadcomercial VARCHAR,
    p_licencia_negocio_condicionlocal VARCHAR,
    p_licencia_representantelegal_dni VARCHAR,
    p_licencia_representantelegal_nombre VARCHAR,
    p_licencia_negocio_area VARCHAR,
    p_licencia_negocio_aforo INTEGER,
    p_licencia_negocio_horario VARCHAR,
    p_licencia_pago_monto VARCHAR,
    p_licencia_pago_codoperacion VARCHAR,
    p_licencia_pago_pagovoucher_url VARCHAR,
    p_licencia_dir_direccioncomercial VARCHAR,
    p_licencia_dir_numero VARCHAR,
    p_licencia_dir_letra VARCHAR,
    p_licencia_dir_inter VARCHAR,
    p_licencia_dir_mz VARCHAR,
    p_licencia_dir_lote VARCHAR,
    p_licencia_dir_dpto VARCHAR,
    p_licencia_dir_referencia VARCHAR,
    p_licencia_itse_tipoinspeccion VARCHAR,
    p_licencia_itse_resultado VARCHAR,
    p_licencia_itse_riesgo VARCHAR,
    p_licencia_itse_observacion VARCHAR,
    p_licencia_vigencia_estado VARCHAR,
    p_licencia_vigencia_tipo VARCHAR,
    p_licencia_vigencia_duracionmeses INTEGER,
    p_licencia_fechavencimiento DATE,
    p_licencia_resolucion_numero INTEGER,
    p_licencia_resolucion_url VARCHAR,
    p_licencia_certificado_numerosequencia INTEGER,
    p_licencia_certificado_codigo VARCHAR,
    p_licencia_certificado_qrverificacion VARCHAR,
    p_licencia_certificado_url VARCHAR,
    p_licencia_certificado_numconsultas INTEGER,
    p_licencia_procedencia_solicitud VARCHAR,
    p_licencia_usuarioid BIGINT,
    p_licencia_usuarionombre VARCHAR,
    p_licencia_ubigeoid BIGINT,
    p_licencia_estadotramite VARCHAR,
    p_licencia_documento_id BIGINT
) RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
    new_licencia_id BIGINT;
BEGIN
    -- Intento de inserción
    BEGIN
        INSERT INTO siga_licencia (
            licencia_tipotramite_tupa,
            licencia_negocio_ruc,
            licencia_negocio_razonsocial,
            licencia_negocio_direccionfiscal,
            licencia_negocio_nombrecomercial,
            licencia_negocio_actividadcomercial,
            licencia_negocio_condicionlocal,
            licencia_representantelegal_dni,
            licencia_representantelegal_nombre,
            licencia_negocio_area,
            licencia_negocio_aforo,
            licencia_negocio_horario,
            licencia_pago_monto,
            licencia_pago_codoperacion,
            licencia_pago_pagovoucher_url,
            licencia_dir_direccioncomercial,
            licencia_dir_numero,
            licencia_dir_letra,
            licencia_dir_inter,
            licencia_dir_mz,
            licencia_dir_lote,
            licencia_dir_dpto,
            licencia_dir_referencia,
            licencia_itse_tipoinspeccion,
            licencia_itse_resultado,
            licencia_itse_riesgo,
            licencia_itse_observacion,
            licencia_vigencia_estado,
            licencia_vigencia_tipo,
            licencia_vigencia_duracionmeses,
            licencia_fechavencimiento,
            licencia_resolucion_numero,
            licencia_resolucion_url,
            licencia_certificado_numerosequencia,
            licencia_certificado_codigo,
            licencia_certificado_qrverificacion,
            licencia_certificado_url,
            licencia_certificado_numconsultas,
            licencia_procedencia_solicitud,
            licencia_usuarioid,
            licencia_usuarionombre,
            licencia_ubigeoid,
            licencia_estadotramite,
            licencia_documento_id
        ) RETURNING licencia_idlic INTO new_licencia_id;
    EXCEPTION
        WHEN unique_violation THEN
            RAISE NOTICE 'Violación de restricción UNIQUE: %', SQLERRM;
            RETURN -1;
        WHEN not_null_violation THEN
            RAISE NOTICE 'Violación de restricción NOT NULL: %', SQLERRM;
            RETURN -2;
        WHEN OTHERS THEN
            RAISE NOTICE 'Error inesperado durante la inserción: %', SQLERRM;
            RETURN -3;
    END;

    -- Retornar el ID de la nueva licencia si la inserción fue exitosa
    RETURN new_licencia_id;
END;
$$;



-- *****************************************************************************

CREATE OR REPLACE FUNCTION licencia_insertar_campos_requeridos(
    p_licencia_tipotramite_tupa BIGINT,
    p_licencia_negocio_ruc VARCHAR,
    p_licencia_negocio_razonsocial VARCHAR,
    p_licencia_negocio_direccionfiscal VARCHAR,
    p_licencia_negocio_nombrecomercial VARCHAR,
    p_licencia_negocio_actividadcomercial VARCHAR,
    p_licencia_negocio_condicionlocal VARCHAR,
    p_licencia_representantelegal_dni VARCHAR,
    p_licencia_representantelegal_nombre VARCHAR,
    p_licencia_negocio_area VARCHAR,
    p_licencia_negocio_aforo INTEGER,
    p_licencia_negocio_horario VARCHAR,
    p_licencia_pago_monto VARCHAR,
    p_licencia_pago_codoperacion VARCHAR,
    p_licencia_pago_pagovoucher_url VARCHAR,
    p_licencia_dir_direccioncomercial VARCHAR,
    p_licencia_dir_numero VARCHAR,
    p_licencia_dir_letra VARCHAR,
    p_licencia_dir_inter VARCHAR,
    p_licencia_dir_mz VARCHAR,
    p_licencia_dir_lote VARCHAR,
    p_licencia_dir_dpto VARCHAR,
    p_licencia_dir_referencia VARCHAR,
    p_licencia_itse_tipoinspeccion VARCHAR,
    p_licencia_itse_resultado VARCHAR,
    p_licencia_itse_riesgo VARCHAR,
    p_licencia_itse_observacion VARCHAR,
    p_licencia_procedencia_solicitud VARCHAR,
    p_licencia_usuarioid BIGINT,
    p_licencia_usuarionombre VARCHAR,
    p_licencia_ubigeoid BIGINT,
    p_licencia_estadotramite VARCHAR,
    p_licencia_documento_codexpediente VARCHAR,
    p_licencia_documento_id BIGINT,
    p_fecha_ingreso DATE,
    p_licencia_epoca VARCHAR
) RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
    new_licencia_id BIGINT;
BEGIN
    -- Intento de inserción
    INSERT INTO siga_licencia (
        licencia_tipotramite_tupa,
        licencia_negocio_ruc,
        licencia_negocio_razonsocial,
        licencia_negocio_direccionfiscal,
        licencia_negocio_nombrecomercial,
        licencia_negocio_actividadcomercial,
        licencia_negocio_condicionlocal,
        licencia_representantelegal_dni,
        licencia_representantelegal_nombre,
        licencia_negocio_area,
        licencia_negocio_aforo,
        licencia_negocio_horario,
        licencia_pago_monto,
        licencia_pago_codoperacion,
        licencia_pago_pagovoucher_url,
        licencia_dir_direccioncomercial,
        licencia_dir_numero,
        licencia_dir_letra,
        licencia_dir_inter,
        licencia_dir_mz,
        licencia_dir_lote,
        licencia_dir_dpto,
        licencia_dir_referencia,
        licencia_itse_tipoinspeccion,
        licencia_itse_resultado,
        licencia_itse_riesgo,
        licencia_itse_observacion,
        licencia_procedencia_solicitud,
        licencia_usuarioid,
        licencia_usuarionombre,
        licencia_ubigeoid,
        licencia_estadotramite,
        licencia_documento_codexpediente,
        licencia_documento_id,
        licencia_fecharegistro,
        licencia_epocatramite
    ) 
    VALUES (
        p_licencia_tipotramite_tupa,
        p_licencia_negocio_ruc,
        p_licencia_negocio_razonsocial,
        p_licencia_negocio_direccionfiscal,
        p_licencia_negocio_nombrecomercial,
        p_licencia_negocio_actividadcomercial,
        p_licencia_negocio_condicionlocal,
        p_licencia_representantelegal_dni,
        p_licencia_representantelegal_nombre,
        p_licencia_negocio_area,
        p_licencia_negocio_aforo,
        p_licencia_negocio_horario,
        p_licencia_pago_monto,
        p_licencia_pago_codoperacion,
        p_licencia_pago_pagovoucher_url,
        p_licencia_dir_direccioncomercial,
        p_licencia_dir_numero,
        p_licencia_dir_letra,
        p_licencia_dir_inter,
        p_licencia_dir_mz,
        p_licencia_dir_lote,
        p_licencia_dir_dpto,
        p_licencia_dir_referencia,
        p_licencia_itse_tipoinspeccion,
        p_licencia_itse_resultado,
        p_licencia_itse_riesgo,
        p_licencia_itse_observacion,
        p_licencia_procedencia_solicitud,
        p_licencia_usuarioid,
        p_licencia_usuarionombre,
        p_licencia_ubigeoid,
        p_licencia_estadotramite,
        p_licencia_documento_codexpediente,
        p_licencia_documento_id,
        p_fecha_ingreso,
        p_licencia_epoca
    )
    RETURNING licencia_idlic INTO new_licencia_id;

    -- Retornar el ID de la nueva licencia
    RETURN new_licencia_id;

EXCEPTION
    -- Manejar violaciones de clave única
    WHEN unique_violation THEN
        RAISE NOTICE 'Violación de restricción UNIQUE. El código ya existe: %', SQLERRM;
        RETURN -1;
    -- Manejar violaciones de clave NOT NULL
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL. Algunos campos requeridos están vacíos: %', SQLERRM;
        RETURN -2;
    -- Manejar violaciones de clave foránea (FOREIGN KEY)
    WHEN foreign_key_violation THEN
        -- Podemos revisar el contenido del mensaje de error para decidir qué código retornar
        IF SQLERRM LIKE '%fk_licencia_documento_id%' THEN
            RAISE NOTICE 'Violación de clave foránea. El documento_id no existe: %', SQLERRM;
            RETURN -4; -- Código específico para el documento_id
        ELSIF SQLERRM LIKE '%fk_licencia_ubigeoid%' THEN
            RAISE NOTICE 'Violación de clave foránea. El ubigeoid no existe: %', SQLERRM;
            RETURN -5; -- Código específico para el ubigeoid
        ELSE
            RAISE NOTICE 'Violación de clave foránea desconocida: %', SQLERRM;
            RETURN -6; -- Código genérico para otras claves foráneas
        END IF;
    -- Manejar otros errores
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la inserción: %', SQLERRM;
        RETURN -3;
END;
$$;

-- *****************************************************************************

CREATE OR REPLACE FUNCTION licencia_obtenerdatos(p_licencia_idlic BIGINT)
RETURNS TABLE(
    idlic BIGINT,
    tipotramite_tupa BIGINT,
    nombretramite_tupa VARCHAR(255),
    categoriatramite_tupa VARCHAR(255),
    negocio_ruc VARCHAR(20),
    negocio_razonsocial VARCHAR(255),
    negocio_direccionfiscal VARCHAR(455),
    negocio_nombrecomercial VARCHAR(255),
    negocio_actividadcomercial VARCHAR(450),
    negocio_condicionlocal VARCHAR(450),
    representantelegal_dni VARCHAR(55),
    representantelegal_nombre VARCHAR(255),
    negocio_area VARCHAR(55),
    negocio_aforo INTEGER,
    negocio_horario VARCHAR(125),
    pago_monto VARCHAR(55),
    pago_codoperacion VARCHAR(75),
    pagovoucher_url VARCHAR(55),
    dir_direccioncomercial VARCHAR(55),
    dir_numero VARCHAR(10),
    dir_letra VARCHAR(10),
    dir_inter VARCHAR(10),
    dir_mz VARCHAR(10),
    dir_lote VARCHAR(10),
    dir_dpto VARCHAR(10),
    dir_referencia VARCHAR(55),
    itse_tipoinspeccion VARCHAR(125),
    itse_resultado VARCHAR(125),
    itse_riesgo VARCHAR(55),
    itse_observacion VARCHAR(455),
    vigencia_estado VARCHAR(55),
    vigencia_tipo VARCHAR(125),
    vigencia_duracionmeses INTEGER,
    vigencia_observacion VARCHAR(655),
    fecharecepcion TIMESTAMP,
    fechaemision DATE,
    fechavencimiento DATE,
    fecharegistro TIMESTAMP,
    fechaultimamod TIMESTAMP,
    licencia_zonificacion VARCHAR(125),
    resolucion_numero INTEGER,
    resolucion_codigo VARCHAR(125),
    resolucion_url VARCHAR(455),
    certificado_numerosequencia INTEGER,
    certificado_codigo VARCHAR(125),
    certificado_qrverificacion VARCHAR(125),
    certificado_url VARCHAR(455),
    certificado_numconsultas INTEGER,
    procedencia_solicitud VARCHAR(20),
    usuarioid BIGINT,
    usuarionombre VARCHAR(45),
    ubigeoid BIGINT,
    estadotramite VARCHAR(45),
    documento_codexpediente VARCHAR(45),
    documento_expedienteurl VARCHAR(455),
    documento_id BIGINT,
    epocatramite VARCHAR(45),
    autorizacion_numero INTEGER,
    autorizacion_codigo VARCHAR(45),
    autorizacion_archivourl VARCHAR(245),
    zonificacion VARCHAR(125),
    itse_fechavencimiento DATE
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        l.licencia_idlic AS idlic,
        l.licencia_tipotramite_tupa AS tipotramite_tupa,
        tram.tram_nombretramite AS nombretramite_tupa,
        tram.tram_categoria AS categoriatramite_tupa,
        l.licencia_negocio_ruc AS negocio_ruc,
        l.licencia_negocio_razonsocial AS negocio_razonsocial,
        l.licencia_negocio_direccionfiscal AS negocio_direccionfiscal,
        l.licencia_negocio_nombrecomercial AS negocio_nombrecomercial,
        l.licencia_negocio_actividadcomercial AS negocio_actividadcomercial,
        l.licencia_negocio_condicionlocal AS negocio_condicionlocal,
        l.licencia_representantelegal_dni AS representantelegal_dni,
        l.licencia_representantelegal_nombre AS representantelegal_nombre,
        l.licencia_negocio_area AS negocio_area,
        l.licencia_negocio_aforo AS negocio_aforo,
        l.licencia_negocio_horario AS negocio_horario,
        l.licencia_pago_monto AS pago_monto,
        l.licencia_pago_codoperacion AS pago_codoperacion,
        l.licencia_pago_pagovoucher_url AS pagovoucher_url,
        l.licencia_dir_direccioncomercial AS dir_direccioncomercial,
        l.licencia_dir_numero AS dir_numero,
        l.licencia_dir_letra AS dir_letra,
        l.licencia_dir_inter AS dir_inter,
        l.licencia_dir_mz AS dir_mz,
        l.licencia_dir_lote AS dir_lote,
        l.licencia_dir_dpto AS dir_dpto,
        l.licencia_dir_referencia AS dir_referencia,
        l.licencia_itse_tipoinspeccion AS itse_tipoinspeccion,
        l.licencia_itse_resultado AS itse_resultado,
        l.licencia_itse_riesgo AS itse_riesgo,
        l.licencia_itse_observacion AS itse_observacion,
        l.licencia_vigencia_estado AS vigencia_estado,
        l.licencia_vigencia_tipo AS vigencia_tipo,
        l.licencia_vigencia_duracionmeses AS vigencia_duracionmeses,
        l.licencia_vigencia_observacion AS vigencia_observacion,
        l.licencia_fecharecepcion AS fecharecepcion,
        l.licencia_fechaemision AS fechaemision,
        l.licencia_fechavencimiento AS fechavencimiento,
        l.licencia_fecharegistro AS fecharegistro,
        l.licencia_fechaultimamod AS fechaultimamod,
        l.licencia_zonificacion AS zonificacion,
        l.licencia_resolucion_numero AS resolucion_numero,
        l.licencia_resolucion_codigo AS resolucion_codigo,
        l.licencia_resolucion_url AS resolucion_url,
        l.licencia_certificado_numerosequencia AS certificado_numerosequencia,
        l.licencia_certificado_codigo AS certificado_codigo,
        l.licencia_certificado_qrverificacion AS certificado_qrverificacion,
        l.licencia_certificado_url AS certificado_url,
        l.licencia_certificado_numconsultas AS certificado_numconsultas,
        l.licencia_procedencia_solicitud AS procedencia_solicitud,
        l.licencia_usuarioid AS usuarioid,
        l.licencia_usuarionombre AS usuarionombre,
        l.licencia_ubigeoid AS ubigeoid,
        l.licencia_estadotramite AS estadotramite,
        l.licencia_documento_codexpediente AS documento_codexpediente,
        l.licencia_documento_expedienteurl AS documento_expedienteurl,
        l.licencia_documento_id AS documento_id,
        l.licencia_epocatramite AS epocatramite,
        l.licencia_autorizacion_numero AS autorizacion_numero,
        l.licencia_autorizacion_codigo AS autorizacion_codigo,
        l.licencia_autorizacion_archivourl AS autorizacion_archivourl,
        l.licencia_zonificacion AS zonificacion,
        l.licencia_itse_fechavencimiento AS itse_fechavencimiento
    FROM siga_licencia AS l
    LEFT JOIN siga_tramite tram on tram.tram_id = l.licencia_tipotramite_tupa
    WHERE l.licencia_idlic = p_licencia_idlic;
END;
$$ LANGUAGE plpgsql;



-- *****************************************************************************


-- *****************************************************************************


CREATE OR REPLACE FUNCTION licencia_listartabla_filtro_full(
    p_estado VARCHAR(45) DEFAULT NULL,
    p_mes_registro INTEGER DEFAULT NULL,
    p_anio_registro INTEGER DEFAULT NULL,
    p_fecha_inicio DATE DEFAULT NULL,
    p_fecha_fin DATE DEFAULT NULL
)
RETURNS TABLE(
    idlic BIGINT,
    tipotramite_tupa BIGINT,
    nombretramite_tupa VARCHAR(255),
    categoriatramite_tupa VARCHAR(255),
    negocio_ruc VARCHAR(20),
    negocio_razonsocial VARCHAR(255),
    negocio_nombrecomercial VARCHAR(255),
    dir_direccioncomercial VARCHAR(55),
    negocio_actividadcomercial VARCHAR(450),
    representantelegal_dni VARCHAR(55),
    representantelegal_nombre VARCHAR(255),
    negocio_area VARCHAR(55),
    negocio_aforo INTEGER,
    negocio_horario VARCHAR(125),
    itse_tipoinspeccion VARCHAR(125),
    itse_riesgo VARCHAR(55),
    vigencia_estado VARCHAR(55),
    vigencia_tipo VARCHAR(125),
    vigencia_duracionmeses INTEGER,
    vigencia_observacion VARCHAR(655),
    fecharecepcion TIMESTAMP,
    fechaemision DATE,
    fechavencimiento DATE,
    fecharegistro TIMESTAMP,
    certificado_numerosequencia INTEGER,
    certificado_codigo VARCHAR(125),
    certificado_url VARCHAR(455),
    resolucion_numero INTEGER,
    resolucion_codigo VARCHAR(125),
    procedencia_solicitud VARCHAR(20),
    usuarioid BIGINT,
    usuarionombre VARCHAR(45),
    estadotramite VARCHAR(45),
    documento_codexpediente VARCHAR(45),
    documento_id BIGINT,
    epocatramite VARCHAR(45),
    zonificacion VARCHAR(125),
    itse_fechavencimiento DATE
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        l.licencia_idlic AS idlic,
        l.licencia_tipotramite_tupa AS tipotramite_tupa,
		tram.tram_nombretramite AS nombretramite_tupa,
        tram.tram_categoria AS categoriatramite_tupa,
        l.licencia_negocio_ruc AS negocio_ruc,
        l.licencia_negocio_razonsocial AS negocio_razonsocial,
        l.licencia_negocio_nombrecomercial AS negocio_nombrecomercial,
        l.licencia_dir_direccioncomercial AS dir_direccioncomercial,
        l.licencia_negocio_actividadcomercial AS negocio_actividadcomercial,
        l.licencia_representantelegal_dni AS representantelegal_dni,
        l.licencia_representantelegal_nombre AS representantelegal_nombre,
        l.licencia_negocio_area AS negocio_area,
        l.licencia_negocio_aforo AS negocio_aforo,
        l.licencia_negocio_horario AS negocio_horario,
        l.licencia_itse_tipoinspeccion AS itse_tipoinspeccion,
        l.licencia_itse_riesgo AS itse_riesgo,
        l.licencia_vigencia_estado AS vigencia_estado,
        l.licencia_vigencia_tipo AS vigencia_tipo,
        l.licencia_vigencia_duracionmeses AS vigencia_duracionmeses,
        l.licencia_vigencia_observacion AS vigencia_observacion,
        l.licencia_fecharecepcion AS fecharecepcion,
        l.licencia_fechaemision AS fechaemision,
        l.licencia_fechavencimiento AS fechavencimiento,
        l.licencia_fecharegistro AS fecharegistro,
        l.licencia_certificado_numerosequencia AS certificado_numerosequencia,
        l.licencia_certificado_codigo AS certificado_codigo,
        l.licencia_certificado_url AS certificado_url,
        l.licencia_resolucion_numero AS resolucion_numero,
        l.licencia_resolucion_codigo AS resolucion_codigo,
        l.licencia_procedencia_solicitud AS procedencia_solicitud,
        l.licencia_usuarioid AS usuarioid,
        l.licencia_usuarionombre AS usuarionombre,
        l.licencia_estadotramite AS estadotramite,
        l.licencia_documento_codexpediente AS documento_codexpediente,
        l.licencia_documento_id AS documento_id,
        l.licencia_epocatramite AS epocatramite,
        l.licencia_zonificacion AS zonificacion,
        l.licencia_itse_fechavencimiento AS itse_fechavencimiento
    FROM siga_licencia AS l
	LEFT JOIN siga_tramite tram on tram.tram_id = l.licencia_tipotramite_tupa
    WHERE
        (p_estado IS NULL OR l.licencia_estadotramite = p_estado)
        AND (p_mes_registro IS NULL OR EXTRACT(MONTH FROM l.licencia_fecharegistro) = p_mes_registro)
        AND (p_anio_registro IS NULL OR EXTRACT(YEAR FROM l.licencia_fecharegistro) = p_anio_registro)
        AND ((p_fecha_inicio IS NULL OR l.licencia_fecharegistro >= p_fecha_inicio)
        AND (p_fecha_fin IS NULL OR l.licencia_fecharegistro <= p_fecha_fin));
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION licencia_listartabla_filtro_full_emision(
    p_estado VARCHAR(45) DEFAULT NULL,
    p_mes_registro INTEGER DEFAULT NULL,
    p_anio_registro INTEGER DEFAULT NULL,
    p_fecha_inicio DATE DEFAULT NULL,
    p_fecha_fin DATE DEFAULT NULL,
    p_categoria VARCHAR(255) DEFAULT NULL
)
RETURNS TABLE(
    idlic BIGINT,
    tipotramite_tupa BIGINT,
    nombretramite_tupa VARCHAR(255),
    categoriatramite_tupa VARCHAR(255),
    negocio_ruc VARCHAR(20),
    negocio_razonsocial VARCHAR(255),
    negocio_nombrecomercial VARCHAR(255),
    dir_direccioncomercial VARCHAR(55),
    negocio_actividadcomercial VARCHAR(450),
    representantelegal_dni VARCHAR(55),
    representantelegal_nombre VARCHAR(255),
    negocio_area VARCHAR(55),
    negocio_aforo INTEGER,
    negocio_horario VARCHAR(125),
    itse_tipoinspeccion VARCHAR(125),
    itse_riesgo VARCHAR(55),
    vigencia_estado VARCHAR(55),
    vigencia_tipo VARCHAR(125),
    vigencia_duracionmeses INTEGER,
    vigencia_observacion VARCHAR(655),
    fecharecepcion TIMESTAMP,
    fechaemision DATE,
    fechavencimiento DATE,
    fecharegistro TIMESTAMP,
    certificado_numerosequencia INTEGER,
    certificado_codigo VARCHAR(125),
    certificado_url VARCHAR(455),
    resolucion_numero INTEGER,
    resolucion_codigo VARCHAR(125),
    procedencia_solicitud VARCHAR(20),
    usuarioid BIGINT,
    usuarionombre VARCHAR(45),
    estadotramite VARCHAR(45),
    documento_codexpediente VARCHAR(45),
    documento_id BIGINT,
    epocatramite VARCHAR(45),
    zonificacion VARCHAR(125),
    autorizacion_numero INTEGER,
    autorizacion_codigo VARCHAR(45),
    autorizacion_archivourl VARCHAR(245)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        l.licencia_idlic AS idlic,
        l.licencia_tipotramite_tupa AS tipotramite_tupa,
		tram.tram_nombretramite AS nombretramite_tupa,
        tram.tram_categoria AS categoriatramite_tupa,
        l.licencia_negocio_ruc AS negocio_ruc,
        l.licencia_negocio_razonsocial AS negocio_razonsocial,
        l.licencia_negocio_nombrecomercial AS negocio_nombrecomercial,
        l.licencia_dir_direccioncomercial AS dir_direccioncomercial,
        l.licencia_negocio_actividadcomercial AS negocio_actividadcomercial,
        l.licencia_representantelegal_dni AS representantelegal_dni,
        l.licencia_representantelegal_nombre AS representantelegal_nombre,
        l.licencia_negocio_area AS negocio_area,
        l.licencia_negocio_aforo AS negocio_aforo,
        l.licencia_negocio_horario AS negocio_horario,
        l.licencia_itse_tipoinspeccion AS itse_tipoinspeccion,
        l.licencia_itse_riesgo AS itse_riesgo,
        l.licencia_vigencia_estado AS vigencia_estado,
        l.licencia_vigencia_tipo AS vigencia_tipo,
        l.licencia_vigencia_duracionmeses AS vigencia_duracionmeses,
        l.licencia_vigencia_observacion AS vigencia_observacion,
        l.licencia_fecharecepcion AS fecharecepcion,
        l.licencia_fechaemision AS fechaemision,
        l.licencia_fechavencimiento AS fechavencimiento,
        l.licencia_fecharegistro AS fecharegistro,
        l.licencia_certificado_numerosequencia AS certificado_numerosequencia,
        l.licencia_certificado_codigo AS certificado_codigo,
        l.licencia_certificado_url AS certificado_url,
        l.licencia_resolucion_numero AS resolucion_numero,
        l.licencia_resolucion_codigo AS resolucion_codigo,
        l.licencia_procedencia_solicitud AS procedencia_solicitud,
        l.licencia_usuarioid AS usuarioid,
        l.licencia_usuarionombre AS usuarionombre,
        l.licencia_estadotramite AS estadotramite,
        l.licencia_documento_codexpediente AS documento_codexpediente,
        l.licencia_documento_id AS documento_id,
        l.licencia_epocatramite AS epocatramite,
        l.licencia_zonificacion AS zonificacion,
        l.licencia_autorizacion_numero AS autorizacion_numero,
        l.licencia_autorizacion_codigo AS autorizacion_codigo,
        l.licencia_autorizacion_archivourl AS autorizacion_archivourl
    FROM siga_licencia AS l
	LEFT JOIN siga_tramite tram on tram.tram_id = l.licencia_tipotramite_tupa
    WHERE
        (p_estado IS NULL OR l.licencia_estadotramite = p_estado)
        AND (p_mes_registro IS NULL OR EXTRACT(MONTH FROM l.licencia_fechaemision) = p_mes_registro)
        AND (p_anio_registro IS NULL OR EXTRACT(YEAR FROM l.licencia_fechaemision) = p_anio_registro)
        AND ((p_fecha_inicio IS NULL OR l.licencia_fechaemision >= p_fecha_inicio)
        AND (p_fecha_fin IS NULL OR l.licencia_fechaemision <= p_fecha_fin) 
        AND (p_categoria IS NULL OR tram.tram_categoria = p_categoria)
        );
END;
$$ LANGUAGE plpgsql;

-- **************************************************************************


CREATE OR REPLACE FUNCTION licencia_actualizar(
    p_licencia_idlic BIGINT,
    p_licencia_tipotramite_tupa BIGINT,
    p_licencia_negocio_ruc VARCHAR,
    p_licencia_negocio_razonsocial VARCHAR,
    p_licencia_negocio_direccionfiscal VARCHAR,
    p_licencia_negocio_nombrecomercial VARCHAR,
    p_licencia_negocio_actividadcomercial VARCHAR,
    p_licencia_negocio_condicionlocal VARCHAR,
    p_licencia_representantelegal_dni VARCHAR,
    p_licencia_representantelegal_nombre VARCHAR,
    p_licencia_negocio_area VARCHAR,
    p_licencia_negocio_aforo INTEGER,
    p_licencia_negocio_horario VARCHAR,
    p_licencia_pago_monto VARCHAR,
    p_licencia_pago_codoperacion VARCHAR,
    p_licencia_dir_direccioncomercial VARCHAR,
    p_licencia_dir_numero VARCHAR,
    p_licencia_dir_letra VARCHAR,
    p_licencia_dir_inter VARCHAR,
    p_licencia_dir_mz VARCHAR,
    p_licencia_dir_lote VARCHAR,
    p_licencia_dir_dpto VARCHAR,
    p_licencia_dir_referencia VARCHAR,
    p_licencia_itse_tipoinspeccion VARCHAR,
    p_licencia_itse_resultado VARCHAR,
    p_licencia_itse_riesgo VARCHAR,
    p_licencia_itse_observacion VARCHAR,
    p_licencia_documento_codexpediente VARCHAR
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    rows_updated INTEGER;
BEGIN
    UPDATE siga_licencia
    SET
        licencia_tipotramite_tupa = p_licencia_tipotramite_tupa,
        licencia_negocio_ruc = p_licencia_negocio_ruc,
        licencia_negocio_razonsocial = p_licencia_negocio_razonsocial,
        licencia_negocio_direccionfiscal = p_licencia_negocio_direccionfiscal,
        licencia_negocio_nombrecomercial = p_licencia_negocio_nombrecomercial,
        licencia_negocio_actividadcomercial = p_licencia_negocio_actividadcomercial,
        licencia_negocio_condicionlocal = p_licencia_negocio_condicionlocal,
        licencia_representantelegal_dni = p_licencia_representantelegal_dni,
        licencia_representantelegal_nombre = p_licencia_representantelegal_nombre,
        licencia_negocio_area = p_licencia_negocio_area,
        licencia_negocio_aforo = p_licencia_negocio_aforo,
        licencia_negocio_horario = p_licencia_negocio_horario,
        licencia_pago_monto = p_licencia_pago_monto,
        licencia_pago_codoperacion = p_licencia_pago_codoperacion,
        licencia_dir_direccioncomercial = p_licencia_dir_direccioncomercial,
        licencia_dir_numero = p_licencia_dir_numero,
        licencia_dir_letra = p_licencia_dir_letra,
        licencia_dir_inter = p_licencia_dir_inter,
        licencia_dir_mz = p_licencia_dir_mz,
        licencia_dir_lote = p_licencia_dir_lote,
        licencia_dir_dpto = p_licencia_dir_dpto,
        licencia_dir_referencia = p_licencia_dir_referencia,
        licencia_itse_tipoinspeccion = p_licencia_itse_tipoinspeccion,
        licencia_itse_resultado = p_licencia_itse_resultado,
        licencia_itse_riesgo = p_licencia_itse_riesgo,
        licencia_itse_observacion = p_licencia_itse_observacion,
        licencia_documento_codexpediente=p_licencia_documento_codexpediente

    WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    RETURN rows_updated > 0;
END;
$$;



-- **************************************************************************

CREATE OR REPLACE FUNCTION licencia_aceptar_solicitud(
    p_licencia_idlic BIGINT
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    rows_updated INTEGER;
BEGIN
    UPDATE siga_licencia
    SET
        licencia_estadotramite = 'EN PROCESO',
        licencia_fecharecepcion = CURRENT_TIMESTAMP,
        licencia_fechaultimamod = CURRENT_TIMESTAMP
        
    WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    RETURN rows_updated > 0;
END;
$$;

-- **************************************************************************

CREATE OR REPLACE FUNCTION licencia_rechazar_solicitud(
    p_licencia_idlic BIGINT,
    p_licencia_vigencia_observacion VARCHAR
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    rows_updated INTEGER;
BEGIN
    UPDATE siga_licencia
    SET
        licencia_estadotramite = 'RECHAZADA',
        licencia_vigencia_observacion = p_licencia_vigencia_observacion,
        licencia_fechaultimamod = CURRENT_TIMESTAMP 
    WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    RETURN rows_updated > 0;
END;
$$;

-- **************************************************************************


CREATE OR REPLACE FUNCTION licencia_cambiar_estadolicencia(
    p_licencia_idlic BIGINT,
    p_licencia_estadotramite VARCHAR,
    p_licencia_vigencia_observacion VARCHAR
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    rows_updated INTEGER;
BEGIN
    UPDATE siga_licencia
    SET
        licencia_estadotramite = p_licencia_estadotramite,
        licencia_vigencia_observacion = p_licencia_vigencia_observacion,
        licencia_vigencia_estado = 'NO VIGENTE'   
    WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    RETURN rows_updated > 0;
END;
$$;

-- **************************************************************************

CREATE OR REPLACE FUNCTION licencia_cambiar_estadolicencia(
    p_licencia_idlic BIGINT,
    p_licencia_vigencia_estado VARCHAR,
    p_licencia_vigencia_observacion VARCHAR
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    rows_updated INTEGER;
BEGIN
    UPDATE siga_licencia
    SET
        licencia_vigencia_estado = p_licencia_vigencia_estado,
        licencia_vigencia_observacion = p_licencia_vigencia_observacion    
    WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    RETURN rows_updated > 0;
END;
$$;


-- **************************************************************************


CREATE OR REPLACE FUNCTION licencia_guardar_zonificacion(
    p_licencia_idlic BIGINT,
    p_zonificacion VARCHAR
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    rows_updated INTEGER;
BEGIN
    UPDATE siga_licencia
    SET
        licencia_zonificacion = p_zonificacion
    WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    RETURN rows_updated > 0;
END;
$$;

-- **************************************************************************
CREATE OR REPLACE FUNCTION licencia_guardar_vigencia(
    p_licencia_idlic BIGINT,
    p_vigencia VARCHAR
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    rows_updated INTEGER;
BEGIN
    UPDATE siga_licencia
    SET
        licencia_vigencia_tipo = p_vigencia   
    WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    RETURN rows_updated > 0;
END;
$$;

-- **************************************************************************
CREATE OR REPLACE FUNCTION licencia_guardar_itsefechavencimiento(
    p_licencia_idlic BIGINT,
    p_fechavencimiento DATE
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    rows_updated INTEGER;
BEGIN
    UPDATE siga_licencia
    SET
        licencia_itse_fechavencimiento = p_fechavencimiento   
    WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    RETURN rows_updated > 0;
END;
$$;
-- **************************************************************************

CREATE OR REPLACE FUNCTION licencia_eliminar(
    p_licencia_idlic BIGINT
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
BEGIN
    DELETE FROM siga_licencia
    WHERE licencia_idlic = p_licencia_idlic;

    -- Si se elimina correctamente, retorna 1 como código de éxito
    RETURN 1;
EXCEPTION
    WHEN foreign_key_violation THEN
        RAISE NOTICE 'No se puede eliminar la licencia debido a una violación de clave foránea: %', SQLERRM;
        RETURN -1; -- Código para violación de clave foránea
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado al intentar eliminar la licencia: %', SQLERRM;
        RETURN -2; -- Código para otros errores
END;
$$;






-- **************************************************************************

CREATE OR REPLACE FUNCTION licencia_rechazar_solicitud(
    p_licencia_idlic BIGINT,
    p_licencia_vigencia_observacion VARCHAR
)
RETURNS BOOLEAN
LANGUAGE plpgsql
AS $$
DECLARE
    rows_updated INTEGER;
BEGIN
    UPDATE siga_licencia
    SET
        licencia_estadotramite = 'RECHAZADA',
        licencia_vigencia_observacion = p_licencia_vigencia_observacion,
        licencia_fechaultimamod = CURRENT_TIMESTAMP 
    WHERE licencia_idlic = p_licencia_idlic;

    GET DIAGNOSTICS rows_updated = ROW_COUNT;

    RETURN rows_updated > 0;
END;
$$;

-- **************************************************************************





-- Función para insertar un nuevo documento
CREATE OR REPLACE FUNCTION historial_licencia_insertar(
    p_hislic_mensaje VARCHAR(255),
    p_trabajador_id VARCHAR(255),
    p_trabajador_nombre BIGINT,
    p_licencia_id BIGINT
) RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
    new_hislic_id BIGINT;
BEGIN
    -- Intento de inserción
    INSERT INTO siga_licencia_historial (
        hislic_mensaje,
        trabajador_id,
        trabajador_nombre,
        licencia_id

    ) VALUES (
        p_hislic_mensaje,
        p_trabajador_id,
        p_trabajador_nombre,
        p_licencia_id
    )
    RETURNING hislic_id INTO new_hislic_id;
    -- Retornar el ID del nuevo documento
    RETURN new_hislic_id;

EXCEPTION
    WHEN unique_violation THEN
        RAISE NOTICE 'Violación de restricción UNIQUE. El código ya existe: %', SQLERRM;
        RETURN -1;
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL. Algunos campos requeridos están vacíos: %', SQLERRM;
        RETURN -2;
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la inserción: %', SQLERRM;
        RETURN -3;
END;
$$;