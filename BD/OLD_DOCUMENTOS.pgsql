-- Función para obtener detalles de un documento específico usando doc_iddoc

CREATE OR REPLACE FUNCTION documento_obtenerdetalles_id(p_doc_iddoc BIGINT)
RETURNS TABLE(
    iddoc BIGINT,
    codigo INTEGER,
    procedencia VARCHAR(255),
    usrorigen_id BIGINT,
    usrorigen_username VARCHAR(255),
    asunto VARCHAR(255),
    folios INTEGER,
    administrado_id BIGINT,
    administrado_nombre VARCHAR(255),
    administrado_apellidopat VARCHAR(255),
    administrado_apellidomat VARCHAR(255),
    tipodocumento_id BIGINT,
    tipodocumento_nombre VARCHAR(255),
    descripcion TEXT,
    estado VARCHAR(255),
    referencias_id VARCHAR(255),
    otrasreferencias VARCHAR(255),
    estupa BOOLEAN,
    fechavencimiento DATE,
    tramitetupa_id BIGINT,
    tramitetupa_nombre VARCHAR(255),
    fecharegistro TIMESTAMP,
    pase_id BIGINT,
    pase_idorigen BIGINT,
    pase_nombre_origen VARCHAR(255),
    pase_iddestino BIGINT,
    pase_nombre_destino VARCHAR(255),
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
            pase.pase_usr_idorigen,
            pase.pase_usr_iddestino,
            pase.pase_usuarionombre,
            pase.pase_usuario_id,
            ROW_NUMBER() OVER (PARTITION BY pase.pase_documento_id ORDER BY pase.pase_fechahoraregistro ASC) AS rn
        FROM siga_pase AS pase
    )
    SELECT 
        sd.doc_iddoc AS iddoc,
        sd.doc_codigo AS codigo,
        sd.doc_procedencia AS procedencia,
        sd.doc_usrorigen_id AS usrorigen_id,
        usr_origen.usr_username AS usrorigen_username,
        sd.doc_asunto AS asunto,
        sd.doc_folios AS folios,
        sd.doc_administrado_id AS administrado_id,
        adm.adm_nombre AS administrado_nombre,
        adm.adm_apellidopat AS administrado_apellidopat,
        adm.adm_apellidomat AS administrado_apellidomat,
        sd.doc_tipodocumento_id AS tipodocumento_id,
        tip.tipo_nombre AS tipodocumento_nombre,
        sd.doc_descripcion AS descripcion,
        sd.doc_estado AS estado,
        sd.doc_referencias_id AS referencias_id,
        sd.doc_otrasreferencias AS otrasreferencias,
        sd.doc_estupa AS estupa,
        sd.doc_fechavencimiento AS fechavencimiento,
        sd.doc_tramitetupa_id AS tramitetupa_id,
        tram.tram_nombretramite AS tramitetupa_nombre,
        sd.doc_fecharegistro AS fecharegistro,
        pase.pase_id AS pase_id,
        pase.pase_usr_idorigen AS pase_idorigen,
        usr_pase_origen.usr_username AS pase_nombre_origen,
        pase.pase_usr_iddestino AS pase_iddestino,
        usr_pase_destino.usr_username AS pase_nombre_destino,
        pase.pase_usuario_id AS usuario_id,
        pase.pase_usuarionombre AS usuario_nombre,
        sd.doc_pdf_principal AS pdf_principal,
        sd.doc_pdf_anexo1 AS pdf_anexo1,
        sd.doc_pdf_anexo2 AS pdf_anexo2
    FROM siga_documento AS sd
    LEFT JOIN siga_usuario AS usr_origen ON usr_origen.usr_id = sd.doc_usrorigen_id
    LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
    LEFT JOIN siga_tramite AS tram ON tram.tram_id = sd.doc_tramitetupa_id
    LEFT JOIN (
        SELECT * 
        FROM primer_pase 
        WHERE rn = 1
    ) AS pase ON pase.pase_documento_id = sd.doc_iddoc
    LEFT JOIN siga_usuario AS usr_pase_origen ON usr_pase_origen.usr_id = pase.pase_usr_idorigen
    LEFT JOIN siga_usuario AS usr_pase_destino ON usr_pase_destino.usr_id = pase.pase_usr_iddestino
    INNER JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id
    WHERE sd.doc_iddoc = p_doc_iddoc;
END;
$$ LANGUAGE plpgsql;



CREATE OR REPLACE FUNCTION documento_interno_obtenerdetalles_id(p_doc_iddoc BIGINT)
RETURNS TABLE(
    iddoc BIGINT,
    codigo INTEGER,
    procedencia VARCHAR(255),
    usrorigen_id BIGINT,
    usrorigen_username VARCHAR(255),
    asunto VARCHAR(255),
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
    tramitetupa_id BIGINT,
    tramitetupa_nombre VARCHAR(255),
    fecharegistro TIMESTAMP,
    pase_id BIGINT,
    pase_idorigen BIGINT,
    pase_nombre_origen VARCHAR(255),
    pase_iddestino BIGINT,
    pase_nombre_destino VARCHAR(255),
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
            pase.pase_usr_idorigen,
            pase.pase_usr_iddestino,
            pase.pase_usuarionombre,
            pase.pase_usuario_id,
            ROW_NUMBER() OVER (PARTITION BY pase.pase_documento_id ORDER BY pase.pase_fechahoraregistro ASC) AS rn
        FROM siga_pase AS pase
    )
    SELECT 
        sd.doc_iddoc AS iddoc,
        sd.doc_codigo AS codigo,
        sd.doc_procedencia AS procedencia,
        sd.doc_usrorigen_id AS usrorigen_id,
        usr_origen.usr_username AS usrorigen_username,
        sd.doc_asunto AS asunto,
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
        sd.doc_tramitetupa_id AS tramitetupa_id,
        tram.tram_nombretramite AS tramitetupa_nombre,
        sd.doc_fecharegistro AS fecharegistro,
        pase.pase_id AS pase_id,
        pase.pase_usr_idorigen AS pase_idorigen,
        usr_pase_origen.usr_username AS pase_nombre_origen,
        pase.pase_usr_iddestino AS pase_iddestino,
        usr_pase_destino.usr_username AS pase_nombre_destino,
        pase.pase_usuario_id AS usuario_id,
        pase.pase_usuarionombre AS usuario_nombre,
        sd.doc_pdf_principal AS pdf_principal,
        sd.doc_pdf_anexo1 AS pdf_anexo1,
        sd.doc_pdf_anexo2 AS pdf_anexo2
    FROM siga_documento AS sd
    LEFT JOIN siga_usuario AS usr_origen ON usr_origen.usr_id = sd.doc_usrorigen_id
    LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
    LEFT JOIN siga_tramite AS tram ON tram.tram_id = sd.doc_tramitetupa_id
    LEFT JOIN (
        SELECT * 
        FROM primer_pase 
        WHERE rn = 1
    ) AS pase ON pase.pase_documento_id = sd.doc_iddoc
    LEFT JOIN siga_usuario AS usr_pase_origen ON usr_pase_origen.usr_id = pase.pase_usr_idorigen
    LEFT JOIN siga_usuario AS usr_pase_destino ON usr_pase_destino.usr_id = pase.pase_usr_iddestino
    WHERE sd.doc_iddoc = p_doc_iddoc;
END;
$$ LANGUAGE plpgsql;
-- Función para obtener datos de un documento por ID con detalles adicionales
-- MODIFICADO

-- CREATE OR REPLACE FUNCTION documento_listar_documentos_externos()
-- RETURNS TABLE(
--     iddoc BIGINT,
--     codigo INTEGER,
--     procedencia VARCHAR(255),
--     usrorigen_id BIGINT,
--     usrorigen_nombre VARCHAR(255),
--     asunto VARCHAR(255),
--     folios INTEGER,
--     administrado_id BIGINT,
--     tipodocumento_id BIGINT,
--     tipodocumento_nombre VARCHAR(255),
--     descripcion TEXT,
--     estado VARCHAR(255),
--     referencias_id VARCHAR(255),
--     otrasreferencias VARCHAR(255),
--     estupa BOOLEAN,
--     casilla_id BIGINT,
--     fechavencimiento DATE,
--     tramitetupa_id BIGINT,
--     tramitetupa_nombre VARCHAR(255),
--     fecharegistro TIMESTAMP
-- ) AS $$
-- BEGIN
--     RETURN QUERY
--     SELECT 
--         sd.doc_iddoc AS iddoc,
--         sd.doc_codigo AS codigo,
--         sd.doc_procedencia AS procedencia,
--         sd.doc_usrorigen_id AS usrorigen_id,
--         usr.usr_username AS usrorigen_nombre,
--         sd.doc_asunto AS asunto,
--         sd.doc_folios AS folios,
--         sd.doc_administrado_id AS administrado_id,
--         sd.doc_tipodocumento_id AS tipodocumento_id,
--         tip.tipo_nombre AS tipodocumento_nombre,
--         sd.doc_descripcion AS descripcion,
--         sd.doc_estado AS estado,
--         sd.doc_referencias_id AS referencias_id,
--         sd.doc_otrasreferencias AS otrasreferencias,
--         sd.doc_estupa AS estupa,
--         sd.doc_casilla_id AS casilla_id,
--         sd.doc_fechavencimiento AS fechavencimiento,
--         sd.doc_tramitetupa_id AS tramitetupa_id,
--         tram.tram_nombretramite AS tramitetupa_nombre,
--         sd.doc_fecharegistro AS fecharegistro
--     FROM siga_documento AS sd
--     LEFT JOIN siga_usuario AS usr ON usr.usr_id = sd.doc_usrorigen_id
--     LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
--     LEFT JOIN siga_tramite AS tram ON tram.tram_id = sd.doc_tramitetupa_id;
-- END;
-- $$ LANGUAGE plpgsql;


-- Función para obtener datos de un documento por ID con detalles adicionales

-- CREATE OR REPLACE FUNCTION documento_obtenerdatos_completos()
-- RETURNS TABLE(
--     iddoc BIGINT,
--     codigo INTEGER,
--     procedencia VARCHAR(255),
--     usrorigen_id BIGINT,
--     usrorigen_username VARCHAR(255),
--     asunto VARCHAR(255),
--     folios INTEGER,
--     administrado_id BIGINT,
--     adm_nombre VARCHAR(255),
--     adm_apellidopat VARCHAR(255),
--     adm_apellidomat VARCHAR(255),
--     tipodocumento_id BIGINT,
--     tipodocumento_nombre VARCHAR(255),
--     descripcion TEXT,
--     estado VARCHAR(255),
--     referencias_id VARCHAR(255),
--     otrasreferencias VARCHAR(255),
--     estupa BOOLEAN,
--     casilla_id BIGINT,
--     fechavencimiento DATE,
--     tramitetupa_id BIGINT,
--     tramitetupa_nombre VARCHAR(255),
--     fecharegistro TIMESTAMP
-- ) AS $$
-- BEGIN
--     RETURN QUERY
--     SELECT 
--         sd.doc_iddoc AS iddoc,
--         sd.doc_codigo AS codigo,
--         sd.doc_procedencia AS procedencia,
--         sd.doc_usrorigen_id AS usrorigen_id,
--         usr.usr_username AS usrorigen_username,
--         sd.doc_asunto AS asunto,
--         sd.doc_folios AS folios,
--         sd.doc_administrado_id AS administrado_id,
--         adm.adm_nombre AS adm_nombre,
--         adm.adm_apellidopat AS adm_apellidopat,
--         adm.adm_apellidomat AS adm_apellidomat,
--         sd.doc_tipodocumento_id AS tipodocumento_id,
--         tip.tipo_nombre AS tipodocumento_nombre,
--         sd.doc_descripcion AS descripcion,
--         sd.doc_estado AS estado,
--         sd.doc_referencias_id AS referencias_id,
--         sd.doc_otrasreferencias AS otrasreferencias,
--         sd.doc_estupa AS estupa,
--         sd.doc_casilla_id AS casilla_id,
--         sd.doc_fechavencimiento AS fechavencimiento,
--         sd.doc_tramitetupa_id AS tramitetupa_id,
--         tram.tram_nombretramite AS tramitetupa_nombre,
--         sd.doc_fecharegistro AS fecharegistro
--     FROM siga_documento AS sd
--     LEFT JOIN siga_usuario AS usr ON usr.usr_id = sd.doc_usrorigen_id
--     LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
--     LEFT JOIN siga_tramite AS tram ON tram.tram_id = sd.doc_tramitetupa_id
--     INNER JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id;
-- END;
-- $$ LANGUAGE plpgsql;




-- Función para obtener datos de un documento por ID con detalles adicionales
-- MESA DE PARTES

CREATE OR REPLACE FUNCTION documento_listar_ingreso_mesapartes_con_pase()
RETURNS TABLE(
    iddoc BIGINT,
    codigo INTEGER,
    procedencia VARCHAR(255),
    usrorigen_id BIGINT,
    usrorigen_username VARCHAR(255),
    asunto VARCHAR(255),
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
    tramitetupa_id BIGINT,
    tramitetupa_nombre VARCHAR(255),
    fecharegistro TIMESTAMP,
    pase_id BIGINT,
    pase_idorigen BIGINT,
    pase_nombre_origen VARCHAR(255),
    pase_iddestino BIGINT,
    pase_nombre_destino VARCHAR(255),
    usuario_id VARCHAR(255),
    usuario_nombre VARCHAR(255),
    pase_estado VARCHAR(255),
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
            pase.pase_usr_idorigen,
            pase.pase_usr_iddestino,
            pase.pase_usuarionombre,
            pase.pase_usuario_id,
            pase.pase_estado,
            ROW_NUMBER() OVER (PARTITION BY pase.pase_documento_id ORDER BY pase.pase_fechahoraregistro ASC) AS rn
        FROM siga_pase AS pase
    )
    SELECT 
        sd.doc_iddoc AS iddoc,
        sd.doc_codigo AS codigo,
        sd.doc_procedencia AS procedencia,
        sd.doc_usrorigen_id AS usrorigen_id,
        usr_origen.usr_username AS usrorigen_username,
        sd.doc_asunto AS asunto,
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
        sd.doc_tramitetupa_id AS tramitetupa_id,
        tram.tram_nombretramite AS tramitetupa_nombre,
        sd.doc_fecharegistro AS fecharegistro,
        pase.pase_id AS pase_id,
        pase.pase_usr_idorigen AS pase_idorigen,
        usr_pase_origen.usr_username AS pase_nombre_origen,
        pase.pase_usr_iddestino AS pase_iddestino,
        usr_pase_destino.usr_username AS pase_nombre_destino,
        pase.pase_usuario_id AS usuario_id,
        pase.pase_usuarionombre AS usuario_nombre,
        pase.pase_estado AS pase_estado,
        sd.doc_pdf_principal AS pdf_principal,
        sd.doc_pdf_anexo1 AS pdf_anexo1,
        sd.doc_pdf_anexo2 AS pdf_anexo2
    FROM siga_documento AS sd
    LEFT JOIN siga_usuario AS usr_origen ON usr_origen.usr_id = sd.doc_usrorigen_id
    LEFT JOIN siga_tipodocumento AS tip ON tip.tipo_id = sd.doc_tipodocumento_id
    LEFT JOIN siga_tramite AS tram ON tram.tram_id = sd.doc_tramitetupa_id
    LEFT JOIN (
        SELECT * 
        FROM primer_pase 
        WHERE rn = 1
    ) AS pase ON pase.pase_documento_id = sd.doc_iddoc
    LEFT JOIN siga_usuario AS usr_pase_origen ON usr_pase_origen.usr_id = pase.pase_usr_idorigen
    LEFT JOIN siga_usuario AS usr_pase_destino ON usr_pase_destino.usr_id = pase.pase_usr_iddestino
    INNER JOIN siga_administrado AS adm ON adm.adm_id = sd.doc_administrado_id;
END;
$$ LANGUAGE plpgsql;





-- Función para obtener datos de un documento por ID

-- CREATE OR REPLACE FUNCTION documento_obtenerdatos(p_doc_iddoc BIGINT)
-- RETURNS TABLE(
--     iddoc BIGINT,
--     codigo INTEGER,
--     procedencia VARCHAR(255),
--     usrorigen_id BIGINT,
--     asunto VARCHAR(255),
--     folios INTEGER,
--     administrado_id BIGINT,
--     tipodocumento_id BIGINT,
--     descripcion TEXT,
--     estado VARCHAR(255),
--     referencias_id VARCHAR(255),
--     otrasreferencias VARCHAR(255),
--     estupa BOOLEAN,
--     casilla_id BIGINT,
--     fechavencimiento DATE,
--     tramitetupa_id BIGINT,
--     fecharegistro TIMESTAMP
-- ) AS $$
-- BEGIN
--     RETURN QUERY
--     SELECT 
--         sd.doc_iddoc AS iddoc,
--         sd.doc_codigo AS codigo,
--         sd.doc_procedencia AS procedencia,
--         sd.doc_usrorigen_id AS usrorigen_id,
--         sd.doc_asunto AS asunto,
--         sd.doc_folios AS folios,
--         sd.doc_administrado_id AS administrado_id,
--         sd.doc_tipodocumento_id AS tipodocumento_id,
--         sd.doc_descripcion AS descripcion,
--         sd.doc_estado AS estado,
--         sd.doc_referencias_id AS referencias_id,
--         sd.doc_otrasreferencias AS otrasreferencias,
--         sd.doc_estupa AS estupa,
--         sd.doc_casilla_id AS casilla_id,
--         sd.doc_fechavencimiento AS fechavencimiento,
--         sd.doc_tramitetupa_id AS tramitetupa_id,
--         sd.doc_fecharegistro AS fecharegistro
--     FROM siga_documento AS sd
--     WHERE sd.doc_iddoc = p_doc_iddoc;
-- END;
-- $$ LANGUAGE plpgsql;



-- Función para insertar un nuevo documento
CREATE OR REPLACE FUNCTION documento_insertar(
    p_doc_codigo INTEGER,
    p_doc_procedencia VARCHAR(255),
    p_doc_usrorigen_id BIGINT,
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
    p_doc_pdf_anexo1 VARCHAR(255),
    p_doc_pdf_anexo2 VARCHAR(255),
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
        doc_codigo,
        doc_procedencia,
        doc_usrorigen_id,
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
        doc_tramitetupa_id,
        doc_pdf_principal,
        doc_pdf_anexo1,
        doc_pdf_anexo2
    ) VALUES (
        p_doc_codigo,
        p_doc_procedencia,
        p_doc_usrorigen_id,
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
        p_doc_tramitetupa_id,
        p_doc_pdf_principal,
        p_doc_pdf_anexo1,
        p_doc_pdf_anexo2
    )
    RETURNING doc_iddoc INTO new_doc_id;

    -- Retornar el ID del nuevo documento
    RETURN new_doc_id;

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

    WHEN string_data_right_truncation THEN
        RAISE NOTICE 'Error: el texto es demasiado largo para el campo "doc_asunto". Por favor, acorte el texto: %', SQLERRM;
        RETURN -4;
END;
$$;


-- Función para actualizar un documento existente

CREATE OR REPLACE FUNCTION documento_actualizar_documento_y_pase(
    p_doc_iddoc BIGINT,
    p_doc_usrorigen_id BIGINT,
    p_doc_asunto VARCHAR(255),
    p_doc_folios INTEGER,
    p_doc_administrado_id BIGINT,
    p_doc_tipodocumento_id BIGINT,
    p_doc_descripcion TEXT,
    p_doc_estupa BOOLEAN,
    p_doc_pdf_principal VARCHAR(255),    
    p_pase_id BIGINT,
    p_pase_iddestino BIGINT,
    p_doc_fechavencimiento DATE DEFAULT NULL,
    p_doc_tramitetupa_id BIGINT DEFAULT NULL
) RETURNS INTEGER
LANGUAGE plpgsql
AS $$
DECLARE
    v_rows_updated_doc INTEGER;
    v_rows_updated_pase INTEGER;
BEGIN
    -- Actualización de la tabla siga_documento
    UPDATE siga_documento
    SET
        doc_usrorigen_id = p_doc_usrorigen_id,
        doc_asunto = p_doc_asunto,
        doc_folios = p_doc_folios,
        doc_administrado_id = p_doc_administrado_id,
        doc_tipodocumento_id = p_doc_tipodocumento_id,
        doc_descripcion = p_doc_descripcion,
        doc_estupa = p_doc_estupa,
        doc_pdf_principal = p_doc_pdf_principal,
        doc_fechavencimiento = p_doc_fechavencimiento,
        doc_tramitetupa_id = p_doc_tramitetupa_id
    WHERE doc_iddoc = p_doc_iddoc;

    -- Obtener el número de filas actualizadas en siga_documento
    GET DIAGNOSTICS v_rows_updated_doc = ROW_COUNT;

    -- Actualización de la tabla siga_pase
    UPDATE siga_pase
    SET
        pase_usr_iddestino = p_pase_iddestino
    WHERE pase_id = p_pase_id;

    -- Obtener el número de filas actualizadas en siga_pase
    GET DIAGNOSTICS v_rows_updated_pase = ROW_COUNT;

    -- Verificar si se actualizaron filas en ambas tablas
    IF v_rows_updated_doc = 0 THEN
        RAISE NOTICE 'No se encontró el documento con iddoc % para actualizar.', p_doc_iddoc;
        RETURN -1;
    ELSIF v_rows_updated_pase = 0 THEN
        RAISE NOTICE 'No se encontró el pase con id % para actualizar.', p_pase_id;
        RETURN -2;
    END IF;

    -- Retornar éxito si ambas actualizaciones se realizaron correctamente
    RETURN 1;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error inesperado: %', SQLERRM;
        RETURN -3;
END;
$$;

