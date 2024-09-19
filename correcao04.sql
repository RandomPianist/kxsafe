CREATE TABLE temp AS (
    SELECT id
    FROM atribuicoes
    LEFT JOIN (
        SELECT referencia
        FROM produtos 
        JOIN atribuicoes
            ON produto_ou_referencia_chave = 'referencia' AND produto_ou_referencia_valor = produtos.referencia
        WHERE atribuicoes.lixeira = 0
        GROUP BY referencia
    ) AS prod ON prod.referencia = produto_ou_referencia_valor
    WHERE prod.referencia IS NULL
      AND lixeira = 0
      AND produto_ou_referencia_chave = 'referencia'
);

UPDATE atribuicoes AS main
JOIN temp AS aux ON aux.id = main.id
SET main.lixeira = 1;

DROP TABLE temp;