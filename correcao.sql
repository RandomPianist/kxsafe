UPDATE atribuicoes
JOIN (
	SELECT
		cod_externo,
		validade
	FROM produtos
) AS prod ON prod.cod_externo = produto_ou_referencia_valor AND produto_ou_referencia_chave = 'produto'
SET atribuicoes.validade = prod.validade;

UPDATE atribuicoes
JOIN (
	SELECT
		produtos.referencia,
		validade
		
	FROM produtos

	JOIN (
		SELECT
			MAX(id) AS id,
			referencia
		FROM produtos
		WHERE referencia <> ''
		GROUP BY referencia
	) AS aux ON aux.id = produtos.id
) AS prod ON prod.referencia = produto_ou_referencia_valor AND produto_ou_referencia_chave = 'referencia'
SET atribuicoes.validade = prod.validade;