
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
    zonificacion VARCHAR(125)
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
        l.licencia_zonificacion AS zonificacion
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