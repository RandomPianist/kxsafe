INSERT INTO estoque (id_mp, qtd, es, descr) (
	SELECT
		mp.id,
		retiradas.qtd,
		'S',
		'RETIRADA'

	FROM retiradas

	JOIN comodatos
		ON comodatos.id = retiradas.id_comodato
		
	JOIN maquinas_produtos AS mp
		ON mp.id_produto = retiradas.id_produto AND mp.id_maquina = comodatos.id_maquina
);