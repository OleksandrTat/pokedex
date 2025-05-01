-- Registro de evoluciones: Cada vez que se agrega una nueva evolución, registra una auditoría indicando que se ha añadido.
DELIMITER //

CREATE TRIGGER registro_auditoria_evoluciones
AFTER INSERT ON evoluciones
FOR EACH ROW
BEGIN
	INSERT INTO auditoria_evoluciones (operacion) 
	VALUES (
		CONCAT(
			' - podex ID: ', NEW.pokemon_base_id, 
			' - evolucion ID: ', NEW.pokemon_evolucion_id, 
			' - nivel requerido: ', NEW.nivel_requerido, 
			' - metodo: ', NEW.metodo
		)
	);

END//
DELIMITER ;



-- Validación de movimientos: Antes de asignar un movimiento a un Pokémon, valida que el tipo del movimiento coincida con al menos uno de los tipos del Pokémon.
DELIMITER //

CREATE TRIGGER validar_tipo_movimiento
BEFORE INSERT ON pokemon_movimientos
FOR EACH ROW
BEGIN
    DECLARE tipoPrimario VARCHAR(50);
    DECLARE tipoSecundario VARCHAR(50);
    DECLARE tipoMovimiento VARCHAR(50);


    -- Comprobar si existe una entrada en pokedex
    IF EXISTS (SELECT 1 FROM pokedex WHERE id = NEW.pokemon_id) THEN
        SET tipoPrimario = (SELECT tipo_primario FROM pokedex WHERE id = NEW.pokemon_id);
        SET tipoSecundario = (SELECT tipo_secundario FROM pokedex WHERE id = NEW.pokemon_id);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Pokemon no existe.';
    END IF;

    -- Obtener el tipo de movimiento
    SELECT tipo
    INTO tipoMovimiento
    FROM movimientos
    WHERE id = NEW.movimiento_id;


    -- Comprueba si el tipo de movimiento coincide con el tipo de Pokémon
    IF tipoMovimiento <> tipoPrimario 
       AND (tipoSecundario IS NULL OR tipoMovimiento <> tipoSecundario) THEN
        -- Generar un error y bloquear la inserción
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: El movimiento no corresponde a ninguno de los tipos del Pokémon.';
    END IF;

END//

DELIMITER ;



-- Actualizacion automatica de evoluciones: Al actualizar el nivel requerido de una evolucion, registra automaticamente la accion en una auditoria.
DELIMITER //

CREATE TRIGGER tr_auditoria_actualizacion_evoluciones
AFTER UPDATE ON evoluciones
FOR EACH ROW
BEGIN
    -- Verifica si el valor de nivel_requerido ha cambiado
    IF NOT (OLD.nivel_requerido <=> NEW.nivel_requerido) THEN
        INSERT INTO auditoria_evoluciones (operacion)
        VALUES (
            CONCAT(
                'Actualizacion del nivel_requerido en evolucion (ID: ', OLD.id, 
                ') de ', IFNULL(OLD.nivel_requerido, 'NULL'), 
                ' a ', IFNULL(NEW.nivel_requerido, 'NULL')
            )
        );
    END IF;
END//

DELIMITER ;



