-- Función para obtener detalles de un documento específico usando doc_iddoc

CREATE OR REPLACE FUNCTION documento_externo_obtenerdetalles_id()
RETURNS TABLE(
    iddoc BIGINT,
    numerodocumento INTEGER,
    numeracion_tipodoc_oficina INTEGER,
    procedencia VARCHAR(255),
    buzonorigen_id BIGINT,
    buzonorigen_nombre VARCHAR(255),
    cabecera VARCHAR(125),
    asunto VARCHAR(255),
    prioridad VARCHAR(255),
    folios INTEGER,
    administrado_id BIGINT,
    administrado_nombre VARCHAR(255),
    administrado_apellidopat VARCHAR(255),
    administrado_apellidomat VARCHAR(255),
    administrado_tipodocumento VARCHAR(45),
    administrado_numdocumento VARCHAR(11),
    administrado_razonsocial VARCHAR(255),
    administrado_celular VARCHAR(45),
    administrado_correo VARCHAR(45),
    tipodocumento_id BIGINT,
    tipodocumento_nombre VARCHAR(255),
    descripcion TEXT,
    estado VARCHAR(255),
    referencias_id VARCHAR(255),
    otrasreferencias VARCHAR(255),
    estupa BOOLEAN,
    fechavencimiento DATE,
    proyectar BOOLEAN,
    usuarionombre VARCHAR(255),
    tramitetupa_id BIGINT,
    tramitetupa_nombre VARCHAR(255),
    fecharegistro TIMESTAMP,
    mes INTEGER,
    anio INTEGER,
    pase_id BIGINT,
    pase_buzonorigen_id BIGINT,
    pase_buzonorigen_nombre VARCHAR(255),
    pase_buzondestino_id BIGINT,
    pase_buzondestino_nombre VARCHAR(255),
    pase_fechaenvio TIMESTAMP,
    pase_fecharecepcion TIMESTAMP,
    pase_tipopase VARCHAR(45), 
    pase_proveido VARCHAR(255),
    pase_observacion VARCHAR(255),
    pase_estadopase VARCHAR(255),
    primogenio_id BIGINT,
    usuario_id VARCHAR(255),
    usuario_nombre VARCHAR(255),
    pdf_principal VARCHAR(255),
    pdf_anexo1 VARCHAR(255),
    pdf_anexo2 VARCHAR(255),
    codigoseguimiento VARCHAR(8)
) AS $$
BEGIN
    RETURN QUERY
    WITH primer_pase AS (
        SELECT 
            pase.pase_id,
            pase.pase_documento_id,
            pase.pase_buzonorigen_id,
            pase.pase_buzondestino_id,
			pase.pase_tipopase,
			pase.pase_proveido,
            pase.pase_observacion,
            pase.pase_estadopase,
			pase.pase_fechaenvio,
			pase.pase_fecharecepcion,
            pase.pase_usuarionombre,
            pase.pase_usuario_id,
            pase.pase_documento_primogenio_id,
            ROW_NUMBER() OVER (PARTITION BY pase.pase_documento_id ORDER BY pase.pase_fechaenvio ASC) AS rn
        FROM siga_documento_pase AS pase
    )
    SELECT 
        sd.doc_iddoc AS iddoc,
        sd.doc_numerodocumento AS numerodocumento, 
		sd.doc_numeracion_tipodoc_oficina as numeracion_tipodoc_oficina,
        sd.doc_procedencia AS procedencia,
        sd.doc_buzonorigen_id AS buzonorigen_id,
        buzon_origen.buzon_nombre AS buzonorigen_nombre,
        sd.doc_cabecera AS cabecera,
        sd.doc_asunto AS asunto,
        sd.doc_prioridad AS prioridad,
        sd.doc_folios AS folios,
        sd.doc_administrado_id AS administrado_id,
        adm.adm_nombre AS administrado_nombre,
        adm.adm_apellidopat AS administrado_apellidopat,
        adm.adm_apellidomat AS administrado_apellidomat,
        adm.adm_tipodocumento AS administrado_tipodocumento,
        adm.adm_numdocumento AS administrado_numdocumento,        
        adm.adm_razonsocial AS administrado_razonsocial,
        adm.adm_celular AS administrado_celular,
        adm.adm_correo AS administrado_correo,
        sd.doc_tipodocumento_id AS tipodocumento_id,
        tip.tipo_nombre AS tipodocumento_nombre,
        sd.doc_descripcion AS descripcion,
        sd.doc_estado AS estado,
        sd.doc_referencias_id AS referencias_id,
        sd.doc_otrasreferencias AS otrasreferencias,
        sd.doc_estupa AS estupa,
        sd.doc_fechavencimiento AS fechavencimiento,
        sd.doc_proyectar AS proyectar,
        sd.doc_usuarionombre AS usuarionombre,
        sd.doc_tramitetupa_id AS tramitetupa_id,
        tram.tram_nombretramite AS tramitetupa_nombre,
        sd.doc_fecharegistro AS fecharegistro,
        sd.doc_mes AS mes,
        sd.doc_anio AS anio,
        pase.pase_id AS pase_id,
        pase.pase_buzonorigen_id AS pase_buzonorigen_id,
        buzon_pase_origen.buzon_nombre AS pase_buzonorigen_nombre,
        pase.pase_buzondestino_id AS pase_buzondestino_id,
        buzon_pase_destino.buzon_nombre AS pase_buzondestino_nombre,
		pase.pase_fechaenvio AS pase_fechaenvio,
        pase.pase_fecharecepcion AS pase_fecharecepcion,
        pase.pase_tipopase AS pase_tipopase,
        pase.pase_proveido AS pase_proveido,
        pase.pase_observacion AS pase_observacion,
        pase.pase_estadopase AS pase_estadopase,
        pase.pase_documento_primogenio_id AS primogenio_id,
        pase.pase_usuario_id AS usuario_id,
        pase.pase_usuarionombre AS usuario_nombre,
        sd.doc_pdf_principal AS pdf_principal,
        sd.doc_pdf_anexo1 AS pdf_anexo1,
        sd.doc_pdf_anexo2 AS pdf_anexo2,
        sd.doc_codigoseguimiento AS codigoseguimiento
    FROM siga_documento AS sd
    LEFT JOIN siga_buzon AS buzon_origen ON buzon_origen.buzon_id = sd.doc_buzonorigen_id
    LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
    LEFT JOIN siga_tramite AS tram ON tram.tram_id = sd.doc_tramitetupa_id
    LEFT JOIN (
        SELECT * 
        FROM primer_pase 
        WHERE rn = 1
    ) AS pase ON pase.pase_documento_id = sd.doc_iddoc
    LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
    LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
    INNER JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id;
END;
$$ LANGUAGE plpgsql;



-- ******************************************************************
-- ******************************************************************


CREATE OR REPLACE FUNCTION documento_interno_obtenerdetalles_id()
RETURNS TABLE(
    iddoc BIGINT,
    numerodocumento INTEGER,
    numeracion_tipodoc_oficina INTEGER,
    procedencia VARCHAR(255),
    buzonorigen_id BIGINT,
    buzonorigen_nombre VARCHAR(255),
    buzon_sigla VARCHAR(45),
    cabecera VARCHAR(125),
    asunto VARCHAR(255),
    prioridad VARCHAR(255),
    folios INTEGER,
    administrado_id BIGINT,
    tipodocumento_id BIGINT,
    tipodocumento_nombre VARCHAR(255),
    descripcion TEXT,
    estado VARCHAR(255),
    referencias_id VARCHAR(255),
    otrasreferencias VARCHAR(255),
    estupa BOOLEAN,
    fechavencimiento DATE,
    proyectar BOOLEAN,
    usuarionombre VARCHAR(255),
    tramitetupa_id BIGINT,
    tramitetupa_nombre VARCHAR(255),
    fecharegistro TIMESTAMP,
    mes INTEGER,
    anio INTEGER,
    pase_id BIGINT,
    pase_buzonorigen_id BIGINT,
    pase_buzonorigen_nombre VARCHAR(255),
    pase_buzondestino_id BIGINT,
    pase_buzondestino_nombre VARCHAR(255),
    pase_fechaenvio TIMESTAMP,
    pase_fecharecepcion TIMESTAMP,
    pase_tipopase VARCHAR(45), 
    pase_proveido VARCHAR(255),
    pase_observacion VARCHAR(255),
    pase_estadopase VARCHAR(255),
    primogenio_id BIGINT,
    usuario_id VARCHAR(255),
    usuario_nombre VARCHAR(255),
    pdf_principal VARCHAR(255),
    pdf_principal_estadofirma VARCHAR(255),
    pdf_anexo1 VARCHAR(255),
    pdf_anexo1_estadofirma VARCHAR(45),
    pdf_anexo2 VARCHAR(255),
    pdf_anexo2_estadofirma VARCHAR(45),
    codigoseguimiento VARCHAR(8)
) AS $$
BEGIN
    RETURN QUERY
    WITH primer_pase AS (
        SELECT 
            pase.pase_id,
            pase.pase_documento_id,
            pase.pase_buzonorigen_id,
            pase.pase_buzondestino_id,
			pase.pase_tipopase,
			pase.pase_proveido,
            pase.pase_observacion,
            pase.pase_estadopase,
			pase.pase_fechaenvio,
			pase.pase_fecharecepcion,
            pase.pase_usuarionombre,
            pase.pase_usuario_id,
            pase.pase_documento_primogenio_id,
            ROW_NUMBER() OVER (PARTITION BY pase.pase_documento_id ORDER BY pase.pase_fechaenvio ASC) AS rn
        FROM siga_documento_pase AS pase
    )
    SELECT 
        sd.doc_iddoc AS iddoc,
        sd.doc_numerodocumento AS numerodocumento, 
		sd.doc_numeracion_tipodoc_oficina as numeracion_tipodoc_oficina,
        sd.doc_procedencia AS procedencia,
        sd.doc_buzonorigen_id AS buzonorigen_id,
        buzon_origen.buzon_nombre AS buzonorigen_nombre,
		buzon_origen.buzon_sigla as buzon_sigla,
        sd.doc_cabecera AS cabecera,
        sd.doc_asunto AS asunto,
        sd.doc_prioridad AS prioridad,
        sd.doc_folios AS folios,
        sd.doc_administrado_id AS administrado_id,
        sd.doc_tipodocumento_id AS tipodocumento_id,
        tip.tipo_nombre AS tipodocumento_nombre,
        sd.doc_descripcion AS descripcion,
        sd.doc_estado AS estado,
        sd.doc_referencias_id AS referencias_id,
        sd.doc_otrasreferencias AS otrasreferencias,
        sd.doc_estupa AS estupa,
        sd.doc_fechavencimiento AS fechavencimiento,
        sd.doc_proyectar AS proyectar,
        sd.doc_usuarionombre AS usuarionombre,
        sd.doc_tramitetupa_id AS tramitetupa_id,
        tram.tram_nombretramite AS tramitetupa_nombre,
        sd.doc_fecharegistro AS fecharegistro,
        sd.doc_mes AS mes,
        sd.doc_anio AS anio,        
        pase.pase_id AS pase_id,
        pase.pase_buzonorigen_id AS pase_buzonorigen_id,
        buzon_pase_origen.buzon_nombre AS pase_buzonorigen_nombre,
        buzon_pase_origen.buzon_sigla AS origen_sigla,
        pase.pase_buzondestino_id AS pase_buzondestino_id,
        buzon_pase_destino.buzon_nombre AS pase_buzondestino_nombre,
        pase.pase_fechaenvio AS pase_fechaenvio,
        pase.pase_fecharecepcion AS pase_fecharecepcion,
        pase.pase_tipopase AS pase_tipopase,
        pase.pase_proveido AS pase_proveido,
        pase.pase_observacion AS pase_observacion,
        pase.pase_estadopase AS pase_estadopase,
        pase.pase_documento_primogenio_id AS primogenio_id,
        pase.pase_usuario_id AS usuario_id,
        pase.pase_usuarionombre AS usuario_nombre,
        sd.doc_pdf_principal AS pdf_principal,
        sd.doc_pdf_principal_estadofirma AS pdf_principal_estadofirma,
        sd.doc_pdf_anexo1 AS pdf_anexo1,
        sd.doc_pdf_anexo1_estadofirma AS pdf_anexo1_estadofirma,
        sd.doc_pdf_anexo2 AS pdf_anexo2,
        sd.doc_pdf_anexo2_estadofirma AS pdf_anexo2_estadofirma,
        sd.doc_codigoseguimiento AS codigoseguimiento
    FROM siga_documento AS sd
    LEFT JOIN siga_buzon AS buzon_origen ON buzon_origen.buzon_id = sd.doc_buzonorigen_id
    LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
    LEFT JOIN siga_tramite AS tram ON tram.tram_id = sd.doc_tramitetupa_id
    LEFT JOIN (
        SELECT  *
        FROM primer_pase 
        WHERE rn = 1
    ) AS pase ON pase.pase_documento_id = sd.doc_iddoc
    LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
    LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id;
END;
$$ LANGUAGE plpgsql;


-- ******************************************************************
-- ******************************************************************

CREATE OR REPLACE FUNCTION documento_interno_edit_id()
RETURNS TABLE (
    iddoc BIGINT,
    numerodocumento INTEGER,
    numeracion_tipodoc_oficina INTEGER,
    procedencia VARCHAR(255),
    buzonorigen_id BIGINT,
    buzonorigen_nombre VARCHAR(255),
    buzon_sigla VARCHAR(45),
    cabecera VARCHAR(125),
    asunto VARCHAR(255),
    prioridad VARCHAR(255),
    folios INTEGER,
    administrado_id BIGINT,
    tipodocumento_id BIGINT,
    tipodocumento_nombre VARCHAR(255),
    descripcion TEXT,
    estado VARCHAR(255),
    referencias_id VARCHAR(255),
    otrasreferencias VARCHAR(255),
    estupa BOOLEAN,
    fechavencimiento DATE,
    proyectar BOOLEAN,
    tramitetupa_id BIGINT,
    tramitetupa_nombre VARCHAR(255),
    fecharegistro TIMESTAMP,
    mes INTEGER,
    anio INTEGER,
    usuario_nombre VARCHAR(255),
    pdf_principal VARCHAR(255),
    pdf_principal_html TEXT,
    pdf_principal_estadofirma VARCHAR(255),
    pdf_anexo1 VARCHAR(255),
    pdf_anexo1_estadofirma VARCHAR(45),
    pdf_anexo2 VARCHAR(255),
    pdf_anexo2_estadofirma VARCHAR(45)
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        sd.doc_iddoc AS iddoc,
        sd.doc_numerodocumento AS numerodocumento,
        sd.doc_numeracion_tipodoc_oficina AS numeracion_tipodoc_oficina,
        sd.doc_procedencia AS procedencia,
        sd.doc_buzonorigen_id AS buzonorigen_id,
        buzon_origen.buzon_nombre AS buzonorigen_nombre,
        buzon_origen.buzon_sigla AS buzon_sigla,
        sd.doc_cabecera AS cabecera,
        sd.doc_asunto AS asunto,
        sd.doc_prioridad AS prioridad,
        sd.doc_folios AS folios,
        sd.doc_administrado_id AS administrado_id,
        sd.doc_tipodocumento_id AS tipodocumento_id,
        tip.tipo_nombre AS tipodocumento_nombre,
        sd.doc_descripcion AS descripcion,
        sd.doc_estado AS estado,
        sd.doc_referencias_id AS referencias_id,
        sd.doc_otrasreferencias AS otrasreferencias,
        sd.doc_estupa AS estupa,
        sd.doc_fechavencimiento AS fechavencimiento,
        sd.doc_proyectar AS proyectar,
        sd.doc_tramitetupa_id AS tramitetupa_id,
        tram.tram_nombretramite AS tramitetupa_nombre,
        sd.doc_fecharegistro AS fecharegistro,
        sd.doc_mes AS mes,
        sd.doc_anio AS anio,
        sd.doc_usuarionombre AS usuario_nombre,
        sd.doc_pdf_principal AS pdf_principal,
        sd.doc_pdf_principal_html AS pdf_principal_html,
        sd.doc_pdf_principal_estadofirma AS pdf_principal_estadofirma,
        sd.doc_pdf_anexo1 AS pdf_anexo1,
        sd.doc_pdf_anexo1_estadofirma AS pdf_anexo1_estadofirma,
        sd.doc_pdf_anexo2 AS pdf_anexo2,
        sd.doc_pdf_anexo2_estadofirma AS pdf_anexo2_estadofirma
    FROM siga_documento AS sd
    LEFT JOIN siga_buzon AS buzon_origen 
        ON buzon_origen.buzon_id = sd.doc_buzonorigen_id
    LEFT JOIN siga_tipodocumento AS tip 
        ON tip.tipo_id = sd.doc_tipodocumento_id
    LEFT JOIN siga_tramite AS tram 
        ON tram.tram_id = sd.doc_tramitetupa_id;
END;
$$ LANGUAGE plpgsql;



-- ******************************************************************
-- ******************************************************************

CREATE OR REPLACE FUNCTION ObtenerPases_documentos()
RETURNS TABLE(
    iddoc BIGINT,
    numerodocumento INTEGER,
    numeracion_tipodoc_oficina INTEGER,
    procedencia VARCHAR(255),
    documento_buzonorigen_id BIGINT,
    documento_buzonorigen_nombre VARCHAR(255),
    buzon_sigla VARCHAR(45),
    cabecera VARCHAR(125),
    asunto VARCHAR(255),
    prioridad VARCHAR(255),
    folios INTEGER,
    administrado_id BIGINT,
    administrado_nombre VARCHAR(255),
    administrado_apellidopat VARCHAR(255),
    administrado_apellidomat VARCHAR(255),
    administrado_tipodocumento VARCHAR(45),
    administrado_numdocumento VARCHAR(11),
    administrado_razonsocial VARCHAR(255),
    administrado_celular VARCHAR(45),
    administrado_correo VARCHAR(45),
    tipodocumento_id BIGINT,
    tipodocumento_nombre VARCHAR(255),
    descripcion TEXT,
    estado VARCHAR(255),
    referencias_id VARCHAR(255),
    otrasreferencias VARCHAR(255),
    estupa BOOLEAN,
    fechavencimiento DATE,
    proyectar BOOLEAN,
    usuarionombre VARCHAR(255),
    tramitetupa_id BIGINT,
    tramitetupa_nombre VARCHAR(255),
    fecharegistro TIMESTAMP,
    mes INTEGER,
    anio INTEGER,
    pase_id BIGINT,
    pase_buzonorigen_id BIGINT,
    pase_buzonorigen_nombre VARCHAR(255),
    pase_buzondestino_id BIGINT,
    pase_buzondestino_nombre VARCHAR(255),
    pase_fechaenvio TIMESTAMP,
    pase_fecharecepcion TIMESTAMP,
    pase_tipopase VARCHAR(45),
    pase_proveido VARCHAR(255),
    pase_observacion VARCHAR(255),
    pase_estadopase VARCHAR(255),
    primogenio_id BIGINT,
    usuario_id VARCHAR(255),
    usuario_nombre VARCHAR(255),
    pdf_principal VARCHAR(255),
    pdf_anexo1 VARCHAR(255),
    pdf_anexo2 VARCHAR(255)
) AS $$
BEGIN
    RETURN QUERY
    WITH primer_pase AS (
        SELECT 
            pase.pase_id,
            pase.pase_documento_id,
            pase.pase_buzonorigen_id,
            pase.pase_buzondestino_id,
            pase.pase_tipopase,
            pase.pase_proveido,
            pase.pase_observacion,
            pase.pase_estadopase,
            pase.pase_fechaenvio,
            pase.pase_fecharecepcion,
            pase.pase_usuarionombre,
            pase.pase_usuario_id,
            pase.pase_documento_primogenio_id,
            ROW_NUMBER() OVER (PARTITION BY pase.pase_documento_id ORDER BY pase.pase_fechaenvio ASC) AS rn
        FROM siga_documento_pase AS pase
    )
    SELECT 
        sd.doc_iddoc AS iddoc,
        sd.doc_numerodocumento AS numerodocumento, 
        sd.doc_numeracion_tipodoc_oficina AS numeracion_tipodoc_oficina,
        sd.doc_procedencia AS procedencia,
        sd.doc_buzonorigen_id AS documento_buzonorigen_id,
        buzon_origen.buzon_nombre AS documento_buzonorigen_nombre,
        buzon_origen.buzon_sigla AS buzon_sigla,
        sd.doc_cabecera AS cabecera,
        sd.doc_asunto AS asunto,
        sd.doc_prioridad AS prioridad,
        sd.doc_folios AS folios,
        sd.doc_administrado_id AS administrado_id,
        adm.adm_nombre AS administrado_nombre,
        adm.adm_apellidopat AS administrado_apellidopat,
        adm.adm_apellidomat AS administrado_apellidomat,
        adm.adm_tipodocumento AS administrado_tipodocumento,
        adm.adm_numdocumento AS administrado_numdocumento,        
        adm.adm_razonsocial AS administrado_razonsocial,
        adm.adm_celular AS administrado_celular,
        adm.adm_correo AS administrado_correo,
        sd.doc_tipodocumento_id AS tipodocumento_id,
        tip.tipo_nombre AS tipodocumento_nombre,
        sd.doc_descripcion AS descripcion,
        sd.doc_estado AS estado,
        sd.doc_referencias_id AS referencias_id,
        sd.doc_otrasreferencias AS otrasreferencias,
        sd.doc_estupa AS estupa,
        sd.doc_fechavencimiento AS fechavencimiento,
        sd.doc_proyectar AS proyectar,
        sd.doc_usuarionombre AS usuarionombre,
        sd.doc_tramitetupa_id AS tramitetupa_id,
        tram.tram_nombretramite AS tramitetupa_nombre,
        sd.doc_fecharegistro AS fecharegistro,
        sd.doc_mes AS mes,
        sd.doc_anio AS anio,        
        pase.pase_id AS pase_id,
        pase.pase_buzonorigen_id AS pase_buzonorigen_id,
        buzon_pase_origen.buzon_nombre AS pase_buzonorigen_nombre,
        pase.pase_buzondestino_id AS pase_buzondestino_id,
        buzon_pase_destino.buzon_nombre AS pase_buzondestino_nombre,
        pase.pase_fechaenvio AS pase_fechaenvio,
        pase.pase_fecharecepcion AS pase_fecharecepcion,
        pase.pase_tipopase AS pase_tipopase,
        pase.pase_proveido AS pase_proveido,
        pase.pase_observacion AS pase_observacion,
        pase.pase_estadopase AS pase_estadopase,
        pase.pase_documento_primogenio_id AS primogenio_id,
        pase.pase_usuario_id AS usuario_id,
        pase.pase_usuarionombre AS usuario_nombre,
        sd.doc_pdf_principal AS pdf_principal,
        sd.doc_pdf_anexo1 AS pdf_anexo1,
        sd.doc_pdf_anexo2 AS pdf_anexo2
    FROM siga_documento AS sd
    LEFT JOIN siga_buzon AS buzon_origen ON buzon_origen.buzon_id = sd.doc_buzonorigen_id
    LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
    LEFT JOIN siga_tramite AS tram ON tram.tram_id = sd.doc_tramitetupa_id
    LEFT JOIN (
        SELECT *
        FROM primer_pase
    ) AS pase ON pase.pase_documento_id = sd.doc_iddoc
    LEFT JOIN siga_buzon AS buzon_pase_origen ON buzon_pase_origen.buzon_id = pase.pase_buzonorigen_id
    LEFT JOIN siga_buzon AS buzon_pase_destino ON buzon_pase_destino.buzon_id = pase.pase_buzondestino_id
    LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id;
END;
$$ LANGUAGE plpgsql;




-- CREATE OR REPLACE FUNCTION documento_listar_ingreso_mesapartes_con_pase()
-- RETURNS TABLE(
--     iddoc BIGINT,
--     codigo INTEGER,
--     procedencia VARCHAR(255),
--     buzonorigen_id BIGINT,
--     buzonorigen_nombre VARCHAR(255),
--     asunto VARCHAR(255),
--     folios INTEGER,
--     administrado_id BIGINT,
--     administrado_nombre VARCHAR(255),
--     administrado_apellidopat VARCHAR(255),
--     administrado_apellidomat VARCHAR(255),
--     administrado_tipodocumento VARCHAR(45),
--     administrado_numdocumento VARCHAR(11),
--     administrado_razonsocial VARCHAR(255),
--     administrado_celular VARCHAR(45),
--     administrado_correo VARCHAR(45),
--     tipodocumento_id BIGINT,
--     tipodocumento_nombre VARCHAR(255),
--     descripcion TEXT,
--     estado VARCHAR(255),
--     referencias_id VARCHAR(255),
--     otrasreferencias VARCHAR(255),
--     estupa BOOLEAN,
--     fechavencimiento DATE,
--     tramitetupa_id BIGINT,
--     tramitetupa_nombre VARCHAR(255),
--     fecharegistro TIMESTAMP,
--     pase_id BIGINT,
--     pase_idorigen BIGINT,
--     pase_nombre_origen VARCHAR(255),
--     pase_iddestino BIGINT,
--     pase_nombre_destino VARCHAR(255),
--     usuario_id VARCHAR(255),
--     usuario_nombre VARCHAR(255),
--     pase_estado VARCHAR(255),
--     pdf_principal VARCHAR(255),
--     pdf_anexo1 VARCHAR(255),
--     pdf_anexo2 VARCHAR(255)
-- ) AS $$
-- BEGIN
--     RETURN QUERY
--     WITH primer_pase AS (
--         SELECT 
--             pase.pase_id,
--             pase.pase_documento_id,
--             pase.pase_buzonid_origen,
--             pase.pase_buzonid_destino,
--             pase.pase_usuarionombre,
--             pase.pase_usuario_id,
--             pase.pase_estado,
--             ROW_NUMBER() OVER (PARTITION BY pase.pase_documento_id ORDER BY pase.pase_fechahoraregistro ASC) AS rn
--         FROM siga_pase AS pase
--     )
--     SELECT 
--         sd.doc_iddoc AS iddoc,
--         sd.doc_codigo AS codigo,
--         sd.doc_procedencia AS procedencia,
--         sd.doc_buzonorigen_id AS buzonorigen_id,
--         usr_origen.usr_username AS buzonorigen_nombre,
--         sd.doc_asunto AS asunto,
--         sd.doc_folios AS folios,
--         sd.doc_administrado_id AS administrado_id,
--         adm.adm_nombre AS administrado_nombre,
--         adm.adm_apellidopat AS administrado_apellidopat,
--         adm.adm_apellidomat AS administrado_apellidomat,
--         adm.adm_tipodocumento AS administrado_tipodocumento,
--         adm.adm_numdocumento AS administrado_numdocumento,
--         adm.adm_razonsocial AS administrado_razonsocial,
--         adm.adm_celular AS administrado_celular,
--         adm.adm_correo AS administrado_correo,
--         sd.doc_tipodocumento_id AS tipodocumento_id,
--         tip.tipo_nombre AS tipodocumento_nombre,
--         sd.doc_descripcion AS descripcion,
--         sd.doc_estado AS estado,
--         sd.doc_referencias_id AS referencias_id,
--         sd.doc_otrasreferencias AS otrasreferencias,
--         sd.doc_estupa AS estupa,
--         sd.doc_fechavencimiento AS fechavencimiento,
--         sd.doc_tramitetupa_id AS tramitetupa_id,
--         tram.tram_nombretramite AS tramitetupa_nombre,
--         sd.doc_fecharegistro AS fecharegistro,
--         pase.pase_id AS pase_id,
--         pase.pase_buzonid_origen AS pase_idorigen,
--         usr_pase_origen.usr_username AS pase_nombre_origen,
--         pase.pase_buzonid_destino AS pase_iddestino,
--         usr_pase_destino.usr_username AS pase_nombre_destino,
--         pase.pase_usuario_id AS usuario_id,
--         pase.pase_usuarionombre AS usuario_nombre,
--         pase.pase_estado AS pase_estado,
--         sd.doc_pdf_principal AS pdf_principal,
--         sd.doc_pdf_anexo1 AS pdf_anexo1,
--         sd.doc_pdf_anexo2 AS pdf_anexo2
--     FROM siga_documento AS sd
--     LEFT JOIN siga_usuario AS usr_origen ON usr_origen.usr_id = sd.doc_buzonorigen_id
--     LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
--     LEFT JOIN siga_tramite AS tram ON tram.tram_id = sd.doc_tramitetupa_id
--     LEFT JOIN (
--         SELECT * 
--         FROM primer_pase 
--         WHERE rn = 1
--     ) AS pase ON pase.pase_documento_id = sd.doc_iddoc
--     LEFT JOIN siga_usuario AS usr_pase_origen ON usr_pase_origen.usr_id = pase.pase_buzonid_origen
--     LEFT JOIN siga_usuario AS usr_pase_destino ON usr_pase_destino.usr_id = pase.pase_buzonid_destino
--     INNER JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id;
-- END;
-- $$ LANGUAGE plpgsql;



-- Función para insertar un nuevo documento
CREATE OR REPLACE FUNCTION documento_insertar(
    p_doc_numerodocumento INTEGER,
    p_doc_numeracion_tipodoc_oficina INTEGER,
    p_doc_procedencia VARCHAR(255),
    p_doc_prioridad VARCHAR(255),
    p_doc_buzonorigen_id BIGINT,
    p_doc_cabecera VARCHAR(125),
    p_doc_asunto VARCHAR(255),
    p_doc_folios INTEGER,
    p_doc_administrado_id BIGINT,
    p_doc_tipodocumento_id BIGINT,
    p_doc_descripcion TEXT,
    p_doc_estado VARCHAR(255),
    p_doc_referencias_id VARCHAR(255),
    p_doc_otrasreferencias VARCHAR(255),
    p_doc_estupa BOOLEAN,
    p_doc_pdf_principal VARCHAR(255),
    p_doc_pdf_principal_html TEXT,
    p_doc_pdf_anexo1 VARCHAR(255),
    p_doc_pdf_anexo2 VARCHAR(255),
    p_doc_proyectar BOOLEAN,
    p_doc_usuarionombre VARCHAR(45),
    p_doc_codigoseguimiento VARCHAR(8),
    p_doc_fechavencimiento DATE DEFAULT NULL,
    p_doc_tramitetupa_id BIGINT DEFAULT NULL
) RETURNS BIGINT
LANGUAGE plpgsql
AS $$
DECLARE
    new_doc_id BIGINT;
BEGIN
    -- Intento de inserción
    INSERT INTO siga_documento (
        doc_numerodocumento,
        doc_numeracion_tipodoc_oficina,
        doc_procedencia,
        doc_prioridad,
        doc_buzonorigen_id,
        doc_cabecera,
        doc_asunto,
        doc_folios,
        doc_administrado_id,
        doc_tipodocumento_id,
        doc_descripcion,
        doc_estado,
        doc_referencias_id,
        doc_otrasreferencias,
        doc_estupa,
        doc_fechavencimiento,
        doc_proyectar,
        doc_usuarionombre,
        doc_tramitetupa_id,
        doc_pdf_principal,
        doc_pdf_principal_html,
        doc_pdf_anexo1,
        doc_pdf_anexo2,
        doc_codigoseguimiento
    ) VALUES (
        p_doc_numerodocumento,
        p_doc_numeracion_tipodoc_oficina,
        p_doc_procedencia,
        p_doc_prioridad,
        p_doc_buzonorigen_id,
        p_doc_cabecera,
        p_doc_asunto,
        p_doc_folios,
        p_doc_administrado_id,
        p_doc_tipodocumento_id,
        p_doc_descripcion,
        p_doc_estado,
        p_doc_referencias_id,
        p_doc_otrasreferencias,
        p_doc_estupa,
        p_doc_fechavencimiento,
        p_doc_proyectar,
        p_doc_usuarionombre,
        p_doc_tramitetupa_id,
        p_doc_pdf_principal,
        p_doc_pdf_principal_html,
        p_doc_pdf_anexo1,
        p_doc_pdf_anexo2,
        p_doc_codigoseguimiento
    )
    RETURNING doc_iddoc INTO new_doc_id;

    -- Retornar el ID del nuevo documento
    RETURN new_doc_id;

EXCEPTION
    WHEN unique_violation THEN
        RAISE NOTICE 'Violación de restricción UNIQUE. El código ya existe';
        RETURN -1;
        
    WHEN not_null_violation THEN
        RAISE NOTICE 'Violación de restricción NOT NULL. Algunos campos requeridos están vacíos';
        RETURN -2;

    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado durante la inserción: %', SQLERRM;
        RETURN -3;

    WHEN string_data_right_truncation THEN
        RAISE NOTICE 'Error: Textos demasiado largos, superan el tamaño de los campos';
        RETURN -4;
END;
$$;





-- -- Función para actualizar un documento existente
CREATE OR REPLACE FUNCTION actualizar_documento_externo_y_pase(
    p_doc_iddoc BIGINT,
    p_doc_buzonorigen_id BIGINT,
    p_doc_prioridad VARCHAR(255),
    p_doc_cabecera VARCHAR(125),
    p_doc_asunto VARCHAR(255),
    p_doc_folios INTEGER,
    p_doc_administrado_id BIGINT,
    p_doc_tipodocumento_id BIGINT,
    p_doc_descripcion TEXT,
    p_doc_estupa BOOLEAN,
    p_doc_pdf_principal VARCHAR(255),    
    p_pase_id BIGINT,
    p_pase_iddestino BIGINT,
    p_doc_proyectar BOOLEAN,
    p_doc_usuarionombre VARCHAR(45),
    p_doc_fechavencimiento DATE DEFAULT NULL,    
    p_doc_tramitetupa_id BIGINT DEFAULT NULL
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_updated_doc INTEGER;
    v_rows_updated_pase INTEGER;
    v_doc_exists BOOLEAN;
    v_pase_exists BOOLEAN;
BEGIN
    -- Verificar si el documento existe
    SELECT EXISTS(SELECT 1 FROM siga_documento WHERE doc_iddoc = p_doc_iddoc) INTO v_doc_exists;
    IF NOT v_doc_exists THEN
        RETURN -1; -- Documento no existe
    END IF;
    
    -- Verificar si el pase existe
    SELECT EXISTS(SELECT 1 FROM siga_documento_pase WHERE pase_id = p_pase_id) INTO v_pase_exists;
    IF NOT v_pase_exists THEN
        RETURN -2; -- Pase no existe
    END IF;
    
    -- Actualización de la tabla siga_documento
    UPDATE siga_documento
    SET
        doc_buzonorigen_id = p_doc_buzonorigen_id,
        doc_prioridad = p_doc_prioridad,
        doc_cabecera = p_doc_cabecera,
        doc_asunto = p_doc_asunto,
        doc_folios = p_doc_folios,
        doc_administrado_id = p_doc_administrado_id,
        doc_tipodocumento_id = p_doc_tipodocumento_id,
        doc_descripcion = p_doc_descripcion,
        doc_estupa = p_doc_estupa,
        doc_pdf_principal = p_doc_pdf_principal,
        doc_proyectar = p_doc_proyectar,
        doc_usuarionombre = p_doc_usuarionombre,
        doc_fechavencimiento = p_doc_fechavencimiento,        
        doc_tramitetupa_id = p_doc_tramitetupa_id
    WHERE doc_iddoc = p_doc_iddoc;
    
    -- Obtener el número de filas actualizadas en siga_documento
    GET DIAGNOSTICS v_rows_updated_doc = ROW_COUNT;

    -- Actualización de la tabla siga_pase
    UPDATE siga_documento_pase
    SET
        pase_buzondestino_id = p_pase_iddestino
    WHERE pase_id = p_pase_id;
    
    -- Obtener el número de filas actualizadas en siga_pase
    GET DIAGNOSTICS v_rows_updated_pase = ROW_COUNT;

    -- Verificar si se actualizaron filas en ambas tablas
    IF v_rows_updated_doc = 0 THEN
        RETURN -3; -- No se pudo actualizar el documento
    END IF;
    
    IF v_rows_updated_pase = 0 THEN
        RETURN -4; -- No se pudo actualizar el pase
    END IF;

    -- Retornar éxito si ambas actualizaciones se realizaron correctamente
    RETURN 1;

EXCEPTION
    WHEN foreign_key_violation THEN
        RETURN -5; -- Violación de clave foránea
    WHEN null_value_not_allowed THEN
        RETURN -6; -- Valor nulo no permitido
    WHEN others THEN
        RETURN -7; -- Error inesperado
END;
$$;





-- -- Función para actualizar un documento existente
CREATE OR REPLACE FUNCTION actualizar_documento_externo_y_pase(
    p_doc_iddoc BIGINT,
    p_doc_buzonorigen_id BIGINT,
    p_doc_prioridad VARCHAR(255),
    p_doc_cabecera VARCHAR(125),
    p_doc_asunto VARCHAR(255),
    p_doc_folios INTEGER,
    p_doc_tipodocumento_id BIGINT,
    p_doc_descripcion TEXT,
    p_doc_estupa BOOLEAN,
    p_doc_pdf_principal VARCHAR(255),    
    p_doc_pdf_anexo1 VARCHAR(255),    
    p_doc_pdf_principal VARCHAR(255),    
    p_pase_id BIGINT,
    p_pase_iddestino BIGINT,
    p_doc_proyectar BOOLEAN,
    p_doc_usuarionombre VARCHAR(45),
    p_doc_fechavencimiento DATE DEFAULT NULL,    
    p_doc_tramitetupa_id BIGINT DEFAULT NULL
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_updated_doc INTEGER;
    v_rows_updated_pase INTEGER;
    v_doc_exists BOOLEAN;
    v_pase_exists BOOLEAN;
BEGIN
    -- Verificar si el documento existe
    SELECT EXISTS(SELECT 1 FROM siga_documento WHERE doc_iddoc = p_doc_iddoc) INTO v_doc_exists;
    IF NOT v_doc_exists THEN
        RETURN -1; -- Documento no existe
    END IF;
    
    -- Verificar si el pase existe
    SELECT EXISTS(SELECT 1 FROM siga_documento_pase WHERE pase_id = p_pase_id) INTO v_pase_exists;
    IF NOT v_pase_exists THEN
        RETURN -2; -- Pase no existe
    END IF;
    
    -- Actualización de la tabla siga_documento
    UPDATE siga_documento
    SET
        doc_buzonorigen_id = p_doc_buzonorigen_id,
        doc_prioridad = p_doc_prioridad,
        doc_cabecera = p_doc_cabecera,
        doc_asunto = p_doc_asunto,
        doc_folios = p_doc_folios,
        doc_tipodocumento_id = p_doc_tipodocumento_id,
        doc_descripcion = p_doc_descripcion,
        doc_estupa = p_doc_estupa,
        doc_pdf_principal = p_doc_pdf_principal,
        doc_proyectar = p_doc_proyectar,
        doc_usuarionombre = p_doc_usuarionombre,
        doc_fechavencimiento = p_doc_fechavencimiento,        
        doc_tramitetupa_id = p_doc_tramitetupa_id
    WHERE doc_iddoc = p_doc_iddoc;
    
    -- Obtener el número de filas actualizadas en siga_documento
    GET DIAGNOSTICS v_rows_updated_doc = ROW_COUNT;

    -- Actualización de la tabla siga_pase
    UPDATE siga_documento_pase
    SET
        pase_buzondestino_id = p_pase_iddestino
    WHERE pase_id = p_pase_id;
    
    -- Obtener el número de filas actualizadas en siga_pase
    GET DIAGNOSTICS v_rows_updated_pase = ROW_COUNT;

    -- Verificar si se actualizaron filas en ambas tablas
    IF v_rows_updated_doc = 0 THEN
        RETURN -3; -- No se pudo actualizar el documento
    END IF;
    
    IF v_rows_updated_pase = 0 THEN
        RETURN -4; -- No se pudo actualizar el pase
    END IF;

    -- Retornar éxito si ambas actualizaciones se realizaron correctamente
    RETURN 1;

EXCEPTION
    WHEN foreign_key_violation THEN
        RETURN -5; -- Violación de clave foránea
    WHEN null_value_not_allowed THEN
        RETURN -6; -- Valor nulo no permitido
    WHEN others THEN
        RETURN -7; -- Error inesperado
END;
$$;




-------------------------------------------
CREATE OR REPLACE FUNCTION documento_interno_update(
    p_doc_iddoc BIGINT,
    p_prioridad VARCHAR(255),
    p_asunto VARCHAR(255),
    p_folios INTEGER,
    p_pdf_principal VARCHAR(255),
    p_pdf_principal_html TEXT,
    p_pdf_principal_estadofirma VARCHAR(45),
    p_pdf_anexo1 VARCHAR(255),
    p_pdf_anexo1_estadofirma VARCHAR(45),
    p_pdf_anexo2 VARCHAR(255),
    p_pdf_anexo2_estadofirma VARCHAR(45)
) RETURNS BOOLEAN AS $$
BEGIN
    -- Verificar si el documento existe
    IF NOT EXISTS (SELECT 1 FROM siga_documento WHERE doc_iddoc = p_doc_iddoc) THEN
        RAISE NOTICE 'El documento con ID % no existe.', p_doc_iddoc;
        RETURN FALSE;
    END IF;

    -- Actualizar los campos especificados
    UPDATE siga_documento
    SET 
        doc_prioridad = p_prioridad,
        doc_asunto = p_asunto,
        doc_folios = p_folios,
        doc_pdf_principal = p_pdf_principal,
        doc_pdf_principal_html = p_pdf_principal_html,
        doc_pdf_principal_estadofirma = p_pdf_principal_estadofirma,
        doc_pdf_anexo1 = p_pdf_anexo1,
        doc_pdf_anexo1_estadofirma = p_pdf_anexo1_estadofirma,
        doc_pdf_anexo2 = p_pdf_anexo2,
        doc_pdf_anexo2_estadofirma = p_pdf_anexo2_estadofirma
    WHERE doc_iddoc = p_doc_iddoc;

    -- Verificar si la actualización fue exitosa
    IF FOUND THEN
        RAISE NOTICE 'Documento con ID % actualizado exitosamente.', p_doc_iddoc;
        RETURN TRUE;
    ELSE
        RAISE NOTICE 'No se pudo actualizar el documento con ID %.', p_doc_iddoc;
        RETURN FALSE;
    END IF;
EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error al actualizar el documento: %', SQLERRM;
        RETURN FALSE;
END;
$$ LANGUAGE plpgsql;





CREATE OR REPLACE FUNCTION documento_obtener_todos()
RETURNS TABLE(
    iddoc BIGINT,
    numerodocumento INTEGER,
    numeracion_tipodoc_oficina INTEGER,
    procedencia VARCHAR(255),
    buzonorigen_id BIGINT,
    buzonorigen_nombre VARCHAR(255),
    buzon_sigla VARCHAR(45),
    cabecera VARCHAR(125),
    asunto VARCHAR(255),
    prioridad VARCHAR(255),
    folios INTEGER,
    administrado_id BIGINT,
    administrado_nombre VARCHAR(255),
    administrado_apellidopat VARCHAR(255),
    administrado_apellidomat VARCHAR(255),
    administrado_tipodocumento VARCHAR(45),
    administrado_numdocumento VARCHAR(11),
    administrado_razonsocial VARCHAR(255),
    administrado_celular VARCHAR(45),
    administrado_correo VARCHAR(45),
    tipodocumento_id BIGINT,
    tipodocumento_nombre VARCHAR(255),
    descripcion TEXT,
    estado VARCHAR(255),
    referencias_id VARCHAR(255),
    otrasreferencias VARCHAR(255),
    estupa BOOLEAN,
    fechavencimiento DATE,
    proyectar BOOLEAN,
    usuarionombre VARCHAR(255),
    tramitetupa_id BIGINT,
    tramitetupa_nombre VARCHAR(255),
    fecharegistro TIMESTAMP,
    mes INTEGER,
    anio INTEGER,
    pdf_principal VARCHAR(255),
    pdf_anexo1 VARCHAR(255),
    pdf_anexo2 VARCHAR(255)
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        sd.doc_iddoc AS iddoc,
        sd.doc_numerodocumento AS numerodocumento, 
        sd.doc_numeracion_tipodoc_oficina AS numeracion_tipodoc_oficina,
        sd.doc_procedencia AS procedencia,
        sd.doc_buzonorigen_id AS buzonorigen_id,
        buzon_origen.buzon_nombre AS buzonorigen_nombre,
        buzon_origen.buzon_sigla AS buzonorigen_sigla,
        sd.doc_cabecera AS cabecera,
        sd.doc_asunto AS asunto,
        sd.doc_prioridad AS prioridad,
        sd.doc_folios AS folios,
        sd.doc_administrado_id AS administrado_id,
        adm.adm_nombre AS administrado_nombre,
        adm.adm_apellidopat AS administrado_apellidopat,
        adm.adm_apellidomat AS administrado_apellidomat,
        adm.adm_tipodocumento AS administrado_tipodocumento,
        adm.adm_numdocumento AS administrado_numdocumento,        
        adm.adm_razonsocial AS administrado_razonsocial,
        adm.adm_celular AS administrado_celular,
        adm.adm_correo AS administrado_correo,
        sd.doc_tipodocumento_id AS tipodocumento_id,
        tip.tipo_nombre AS tipodocumento_nombre,
        sd.doc_descripcion AS descripcion,
        sd.doc_estado AS estado,
        sd.doc_referencias_id AS referencias_id,
        sd.doc_otrasreferencias AS otrasreferencias,
        sd.doc_estupa AS estupa,
        sd.doc_fechavencimiento AS fechavencimiento,
        sd.doc_proyectar AS proyectar,
        sd.doc_usuarionombre AS usuarionombre,        
        sd.doc_tramitetupa_id AS tramitetupa_id,
        tram.tram_nombretramite AS tramitetupa_nombre,
        sd.doc_fecharegistro AS fecharegistro,
        sd.doc_mes AS mes,
        sd.doc_anio AS anio,
        sd.doc_pdf_principal AS pdf_principal,
        sd.doc_pdf_anexo1 AS pdf_anexo1,
        sd.doc_pdf_anexo2 AS pdf_anexo2
    FROM siga_documento AS sd
    LEFT JOIN siga_buzon AS buzon_origen ON buzon_origen.buzon_id = sd.doc_buzonorigen_id
    LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
    LEFT JOIN siga_tramite AS tram ON tram.tram_id = sd.doc_tramitetupa_id
    LEFT JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
    ORDER BY sd.doc_fecharegistro DESC;
END;
$$ LANGUAGE plpgsql;








-- -- Función para actualizar un documento existente
CREATE OR REPLACE FUNCTION documento_actualizar_estado_documento(
    p_doc_iddoc BIGINT,
    p_doc_proyectar BOOLEAN,
    p_doc_estado VARCHAR(45)
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_updated_doc INTEGER;
    v_rows_updated_pase INTEGER;
    v_doc_exists BOOLEAN;
    v_pase_exists BOOLEAN;
BEGIN
    -- Verificar si el documento existe
    SELECT EXISTS(SELECT 1 FROM siga_documento WHERE doc_iddoc = p_doc_iddoc) INTO v_doc_exists;
    IF NOT v_doc_exists THEN
        RETURN -1; -- Documento no existe
    END IF;
    
    -- Verificar si el pase existe
    SELECT EXISTS(SELECT 1 FROM siga_documento_pase WHERE pase_id = p_pase_id) INTO v_pase_exists;
    IF NOT v_pase_exists THEN
        RETURN -2; -- Pase no existe
    END IF;
    
    -- Actualización de la tabla siga_documento
    UPDATE siga_documento
    SET
        doc_buzonorigen_id = p_doc_buzonorigen_id,
        doc_prioridad = p_doc_prioridad,
        doc_cabecera = p_doc_cabecera,
        doc_asunto = p_doc_asunto,
        doc_folios = p_doc_folios,
        doc_administrado_id = p_doc_administrado_id,
        doc_tipodocumento_id = p_doc_tipodocumento_id,
        doc_descripcion = p_doc_descripcion,
        doc_estupa = p_doc_estupa,
        doc_pdf_principal = p_doc_pdf_principal,
        doc_proyectar = p_doc_proyectar,
        doc_usuarionombre = p_doc_usuarionombre,
        doc_fechavencimiento = p_doc_fechavencimiento,        
        doc_tramitetupa_id = p_doc_tramitetupa_id
    WHERE doc_iddoc = p_doc_iddoc;
    
    -- Obtener el número de filas actualizadas en siga_documento
    GET DIAGNOSTICS v_rows_updated_doc = ROW_COUNT;

    -- Actualización de la tabla siga_pase
    UPDATE siga_documento_pase
    SET
        pase_buzondestino_id = p_pase_iddestino
    WHERE pase_id = p_pase_id;
    
    -- Obtener el número de filas actualizadas en siga_pase
    GET DIAGNOSTICS v_rows_updated_pase = ROW_COUNT;

    -- Verificar si se actualizaron filas en ambas tablas
    IF v_rows_updated_doc = 0 THEN
        RETURN -3; -- No se pudo actualizar el documento
    END IF;
    
    IF v_rows_updated_pase = 0 THEN
        RETURN -4; -- No se pudo actualizar el pase
    END IF;

    -- Retornar éxito si ambas actualizaciones se realizaron correctamente
    RETURN 1;

EXCEPTION
    WHEN foreign_key_violation THEN
        RETURN -5; -- Violación de clave foránea
    WHEN null_value_not_allowed THEN
        RETURN -6; -- Valor nulo no permitido
    WHEN others THEN
        RETURN -7; -- Error inesperado
END;
$$;