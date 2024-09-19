UPDATE produtos SET consumo = 0;
UPDATE produtos SET consumo = 1 WHERE descr LIKE '%NIF%' OR descr LIKE '%BOTINA%';