
CREATE OR REPLACE PROCEDURE requisito_tramite_insertar(
    IN p_req_nombrerequisito VARCHAR(455),
    IN p_req_esobligatorio BOOLEAN,
    IN p_req_esformato BOOLEAN,
    IN p_req_formatopdf_url VARCHAR(255)
)
LANGUAGE plpgsql
AS $$
BEGIN
    INSERT INTO siga_requisito_tramite (
        req_nombrerequisito,
        req_esobligatorio,
        req_esformato,
        req_formatopdf_url
    ) VALUES (
        p_req_nombrerequisito,
        p_req_esobligatorio,
        p_req_esformato,
        p_req_formatopdf_url
    );
END;
$$;


-- *--------------------------------------------------
CREATE OR REPLACE FUNCTION requisito_tramite_obtener(p_req_idreq BIGINT)
RETURNS TABLE(
    req_idreq BIGINT,
    req_nombrerequisito VARCHAR(455),
    req_esobligatorio BOOLEAN,
    req_esformato BOOLEAN,
    req_formatopdf_url VARCHAR(255)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        req_idreq as idreq,
        req_nombrerequisito as nombrerequisito,
        req_esobligatorio as esobligatorio,
        req_esformato as esformato ,
        req_formatopdf_url as formatopdf_url
    FROM siga_requisito_tramite
    WHERE req_idreq = p_req_idreq;
END;
$$ LANGUAGE plpgsql;

-- *--------------------------------------------------

CREATE OR REPLACE FUNCTION requisito_tramite_listar()
RETURNS TABLE(
    req_idreq BIGINT,
    req_nombrerequisito VARCHAR(455),
    req_esobligatorio BOOLEAN,
    req_esformato BOOLEAN,
    req_formatopdf_url VARCHAR(255)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        req_idreq as idreq,
        req_nombrerequisito as nombrerequisito,
        req_esobligatorio as esobligatorio,
        req_esformato as esformato ,
        req_formatopdf_url as formatopdf_url
    FROM siga_requisito_tramite;
END;
$$ LANGUAGE plpgsql;



-- *--------------------------------------------------
CREATE OR REPLACE PROCEDURE requisito_tramite_actualizar(
    IN p_req_idreq BIGINT,
    IN p_req_nombrerequisito VARCHAR(455),
    IN p_req_esobligatorio BOOLEAN,
    IN p_req_esformato BOOLEAN,
    IN p_req_formatopdf_url VARCHAR(255)
)
LANGUAGE plpgsql
AS $$
BEGIN
    UPDATE siga_requisito_tramite
    SET
        req_nombrerequisito = p_req_nombrerequisito,
        req_esobligatorio = p_req_esobligatorio,
        req_esformato = p_req_esformato,
        req_formatopdf_url = p_req_formatopdf_url
    WHERE req_idreq = p_req_idreq;
END;
$$;



-- *--------------------------------------------------
CREATE OR REPLACE PROCEDURE requisito_tramite_eliminar(
    IN p_req_idreq BIGINT
)
LANGUAGE plpgsql
AS $$
BEGIN
    DELETE FROM siga_requisito_tramite WHERE req_idreq = p_req_idreq;
END;
$$;



-- *--------------------------------------------------



-- *--------------------------------------------------