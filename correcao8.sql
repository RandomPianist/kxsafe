UPDATE atribuicoes SET pessoa_ou_setor_chave = UPPER(SUBSTRING(pessoa_ou_setor_chave, 1, 1));
UPDATE atribuicoes SET produto_ou_referencia_chave = UPPER(SUBSTRING(produto_ou_referencia_chave, 1, 1));
ALTER TABLE atribuicoes MODIFY pessoa_ou_setor_chave CHAR;
ALTER TABLE atribuicoes MODIFY produto_ou_referencia_chave CHAR;
ALTER TABLE atribuicoes MODIFY produto_ou_referencia_valor VARCHAR(64);