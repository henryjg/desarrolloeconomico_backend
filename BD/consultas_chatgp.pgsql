
-- 📌 Consulta para obtener la trazabilidad completa de un documento

WITH RECURSIVE RutaDocumentos AS (
    -- Punto de inicio: Documento inicial (Por ejemplo, D1)
    SELECT 
        d.doc_iddoc,
        d.doc_numerodocumento,
        CAST(NULL AS BIGINT) AS documento_referenciado, -- Convertimos NULL a BIGINT
        CAST('Inicio' AS VARCHAR(50)) AS tipo_referencia, -- Convertimos 'Inicio' a VARCHAR(50)
        d.doc_asunto,
        d.doc_fecharegistro,
        d.doc_estado
    FROM siga_documento d
    WHERE d.doc_numerodocumento = 1  -- Aquí se coloca el número del primer documento (D1)
    
    UNION ALL
    
    -- Obtener documentos relacionados a través de referencias
    SELECT 
        r.refer_id_documento_origen AS doc_iddoc,
        d1.doc_numerodocumento,
        r.refer_id_documento_referenciado AS documento_referenciado,
        r.refer_tiporeferencia AS tipo_referencia,
        d1.doc_asunto,
        d1.doc_fecharegistro,
        d1.doc_estado
    FROM siga_documento_referencia r
    JOIN siga_documento d1 ON r.refer_id_documento_origen = d1.doc_iddoc
    JOIN siga_documento d2 ON r.refer_id_documento_referenciado = d2.doc_iddoc
    JOIN RutaDocumentos rd ON rd.doc_numerodocumento = d2.doc_numerodocumento
)

SELECT * FROM RutaDocumentos ORDER BY doc_fecharegistro;




-- 📌 Explicación de la consulta
-- Punto de inicio: Se selecciona el primer documento (D1) que inicia la ruta.
-- Consulta recursiva:
-- Se obtiene la información de los documentos que están referenciados en la tabla siga_documento_referencia.
-- Se unen los documentos origen con los documentos referenciados.
-- Se sigue recorriendo hasta obtener el último documento en la cadena.
-- Ordenamiento: Se ordena por doc_fecharegistro para mostrar la trazabilidad en orden cronológico.

















-- 📌 Consulta para ver el historial de movimientos del documento
-- Si deseas ver cómo se ha movido un documento entre oficinas, usa esta consulta:


SELECT 
    p.pase_documento_id,
    d.doc_numerodocumento,
    o1.usr_username AS oficina_origen,
    o2.usr_username AS oficina_destino,
    p.pase_tipopase,
    p.pase_estadopase,
    p.pase_fechaenvio,
    p.pase_fecharecepcion
FROM siga_documento_pase p
JOIN siga_documento d ON p.pase_documento_id = d.doc_iddoc
JOIN siga_usuario o1 ON p.pase_usr_idorigen = o1.usr_id
JOIN siga_usuario o2 ON p.pase_usr_iddestino = o2.usr_id
ORDER BY p.pase_fechaenvio;













WITH RECURSIVE RutaDocumentos AS (
    -- Punto de inicio: Documento inicial (Por ejemplo, D1)
    SELECT 
        d.doc_iddoc,
        d.doc_numerodocumento,
        CAST(NULL AS BIGINT) AS documento_referenciado, -- Convertimos NULL a BIGINT
        CAST('Inicio' AS VARCHAR(50)) AS tipo_referencia, -- Convertimos 'Inicio' a VARCHAR(50)
        d.doc_asunto,
        d.doc_fecharegistro,
        d.doc_estado
    FROM siga_documento d
    WHERE d.doc_numerodocumento = 1  -- Aquí se coloca el número del primer documento (D1)
    
    UNION ALL
    
    -- Obtener documentos relacionados a través de referencias
    SELECT 
        r.refer_id_documento_origen AS doc_iddoc,
        d1.doc_numerodocumento,
        r.refer_id_documento_referenciado AS documento_referenciado,
        r.refer_tiporeferencia AS tipo_referencia,
        d1.doc_asunto,
        d1.doc_fecharegistro,
        d1.doc_estado
    FROM siga_documento_referencia r
    JOIN siga_documento d1 ON r.refer_id_documento_origen = d1.doc_iddoc
    JOIN siga_documento d2 ON r.refer_id_documento_referenciado = d2.doc_iddoc
    JOIN RutaDocumentos rd ON rd.doc_numerodocumento = d2.doc_numerodocumento
)

SELECT * FROM RutaDocumentos ORDER BY doc_fecharegistro;




WITH RECURSIVE RutaDocumentos AS (
    -- Punto de inicio: Documento inicial (D1)
    SELECT 
        d.doc_iddoc,
        d.doc_numerodocumento,
        CAST(NULL AS BIGINT) AS documento_referenciado, -- Convertimos NULL a BIGINT
        CAST('Inicio' AS VARCHAR(50)) AS tipo_referencia, -- Convertimos 'Inicio' a VARCHAR(50)
        d.doc_asunto,
        d.doc_fecharegistro,
        d.doc_estado
    FROM siga_documento d
    WHERE d.doc_numerodocumento = 1  -- Cambia el número según el documento de inicio
    
    UNION ALL
    
    -- Obtener documentos relacionados (Referencias)
    SELECT 
        r.refer_id_documento_origen AS doc_iddoc,
        d1.doc_numerodocumento,
        r.refer_id_documento_referenciado AS documento_referenciado, -- Ya es BIGINT
        CAST(r.refer_tiporeferencia AS VARCHAR(50)) AS tipo_referencia, -- Forzamos que coincida el tipo
        d1.doc_asunto,
        d1.doc_fecharegistro,
        d1.doc_estado
    FROM siga_documento_referencia r
    JOIN siga_documento d1 ON r.refer_id_documento_origen = d1.doc_iddoc
    JOIN siga_documento d2 ON r.refer_id_documento_referenciado = d2.doc_iddoc
    JOIN RutaDocumentos rd ON rd.doc_numerodocumento = d2.doc_numerodocumento
)

SELECT 
    rd.doc_iddoc,
    rd.doc_numerodocumento,
    rd.documento_referenciado,
    rd.tipo_referencia,
    rd.doc_asunto,
    rd.doc_fecharegistro,
    rd.doc_estado,
    p.pase_usr_idorigen AS oficina_origen,
    p.pase_usr_iddestino AS oficina_destino,
    p.pase_tipopase,
    p.pase_estadopase,
    p.pase_fechaenvio,
    p.pase_fecharecepcion
FROM RutaDocumentos rd
LEFT JOIN siga_documento_pase p ON rd.doc_iddoc = p.pase_documento_id
ORDER BY rd.doc_fecharegistro;















-- Insertar Oficinas
INSERT INTO Oficina (nombre_oficina, siglas) VALUES 
('Oficina A', 'A'), ('Oficina B', 'B'), ('Oficina C', 'C'), ('Oficina D', 'D'), ('Oficina E', 'E');

-- Insertar Documento D1 en Oficina A
INSERT INTO Documento (numero_documento, tipo_documento, asunto, oficina_origen) 
VALUES ('D1', 'Memorando', 'Solicitud de presupuesto', 1);

-- Movimiento de D1 de A a B
INSERT INTO MovimientoDocumento (id_documento, oficina_origen, oficina_destino) 
VALUES (1, 1, 2);

-- Insertar Documento D2 en Oficina B (Solicitud de informe a varias oficinas)
INSERT INTO Documento (numero_documento, tipo_documento, asunto, oficina_origen) 
VALUES ('D2', 'Solicitud', 'Solicitud de informe de presupuesto', 2);

-- Movimiento de D2 de B a C
INSERT INTO MovimientoDocumento (id_documento, oficina_origen, oficina_destino) 
VALUES (2, 2, 3);

-- Movimiento de D2 de B a D
INSERT INTO MovimientoDocumento (id_documento, oficina_origen, oficina_destino) 
VALUES (2, 2, 4);

-- Movimiento de D2 de B a E
INSERT INTO MovimientoDocumento (id_documento, oficina_origen, oficina_destino) 
VALUES (2, 2, 5);

-- Insertar Documento D3 en Oficina C (Respuesta a B)
INSERT INTO Documento (numero_documento, tipo_documento, asunto, oficina_origen) 
VALUES ('D3', 'Informe', 'Presupuesto oficina C', 3);

-- Relacionar D3 con D2 (respuesta)
INSERT INTO ReferenciaDocumento (id_documento_origen, id_documento_referenciado, tipo_referencia) 
VALUES (3, 2, 'Respuesta');

-- Movimiento de D3 de C a B
INSERT INTO MovimientoDocumento (id_documento, oficina_origen, oficina_destino) 
VALUES (3, 3, 2);

-- Insertar Documento D4 en Oficina D (Respuesta a B)
INSERT INTO Documento (numero_documento, tipo_documento, asunto, oficina_origen) 
VALUES ('D4', 'Informe', 'Presupuesto oficina D', 4);

-- Relacionar D4 con D2 (respuesta)
INSERT INTO ReferenciaDocumento (id_documento_origen, id_documento_referenciado, tipo_referencia) 
VALUES (4, 2, 'Respuesta');

-- Movimiento de D4 de D a B
INSERT INTO MovimientoDocumento (id_documento, oficina_origen, oficina_destino) 
VALUES (4, 4, 2);

-- Insertar Documento D5 en Oficina E (Respuesta a B)
INSERT INTO Documento (numero_documento, tipo_documento, asunto, oficina_origen) 
VALUES ('D5', 'Informe', 'Presupuesto oficina E', 5);

-- Relacionar D5 con D2 (respuesta)
INSERT INTO ReferenciaDocumento (id_documento_origen, id_documento_referenciado, tipo_referencia) 
VALUES (5, 2, 'Respuesta');

-- Movimiento de D5 de E a B
INSERT INTO MovimientoDocumento (id_documento, oficina_origen, oficina_destino) 
VALUES (5, 5, 2);

-- Insertar Documento D6 en Oficina B (Consolidado con las respuestas de C, D y E)
INSERT INTO Documento (numero_documento, tipo_documento, asunto, oficina_origen) 
VALUES ('D6', 'Consolidado', 'Informe final consolidado de presupuesto', 2);

-- Relacionar D6 con D3, D4 y D5 (referencia a todas las respuestas)
INSERT INTO ReferenciaDocumento (id_documento_origen, id_documento_referenciado, tipo_referencia) 
VALUES (6, 3, 'Consolidado'),
       (6, 4, 'Consolidado'),
       (6, 5, 'Consolidado');

-- Movimiento de D6 de B a A
INSERT INTO MovimientoDocumento (id_documento, oficina_origen, oficina_destino) 
VALUES (6, 2, 1);











