SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
-- -----------------------------------------------------
-- Schema simulado_saep_02
-- -----------------------------------------------------

CREATE SCHEMA IF NOT EXISTS `simulado_saep_02` ;
USE `simulado_saep_02`;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`insumos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`insumos` (
  `id` INT NOT NULL,
  `codigo` VARCHAR(45) NOT NULL,
  `preco` FLOAT NOT NULL,
  `categoria` VARCHAR(100) NOT NULL,
  `estoque_ativo` INT NOT NULL,
  `estoque_minimo` INT NOT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `simulado_saep_02`.`usuarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `simulado_saep_02`.`usuarios` (
  `id` INT NOT NULL AUTO_INCREMENT primary key,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `simulado_saep_02`.`movimentacoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `simulado_saep_02`.`movimentacoes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `usuarios_id` INT NOT NULL,
  `insumos_id` INT NOT NULL,
  `tipo` INT NOT NULL COMMENT '1-entrada, 2-saída',
  `data` DATE NOT NULL,
  `quantidade` INT NOT NULL,
  `saldo_anterior` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_movimentacoes_usuarios_idx` (`usuarios_id` ASC) VISIBLE,
  INDEX `fk_movimentacoes_insumos2_idx` (`insumos_id` ASC) VISIBLE,
  CONSTRAINT `fk_movimentacoes_usuarios`
    FOREIGN KEY (`usuarios_id`)
    REFERENCES `simulado_saep_02`.`usuarios` (`id`),
  CONSTRAINT `fk_movimentacoes_insumos2`
    FOREIGN KEY (`insumos_id`)
    REFERENCES `mydb`.`insumos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

INSERT INTO insumos (id, codigo, nome, preco, categoria, estoque_ativo, estoque_minimo, ativo) values
  (1, 'I001', 'Chapa de Aço', 40.00, 'metal', 40, 20, 1),
  (2, 'I002', 'Tinta Azul Royal', 96.70, 'tinturas', 134, 50, 1),
  (3, 'I003', 'Parafuso', 4.00, 'parafusos', 490, 150, 1);

INSERT INTO usuarios (id, nome, email, senha, ativo) values
  (1, 'Marcos Gustavo', 'marcos@gmail.com', 'Amendoim', 1),
  (2, 'Jorge', 'email2@gmail.com', '1234', 1),
  (3, 'Melquisedeque', 'email3@gmail.com', '1234', 1);

INSERT INTO movimentacoes (id, usuario_id, insumos_id, tipo, data, quantidade, saldo_anterior) values 
  (1, 1, 2, 2, str_to_date('02/10/2026', '%d/%m/%Y'), 5, 134),
  (2, 1, 3, 1, str_to_date('02/10/2026', '%d/%m/%Y'), 10, 490),
  (3, 2, 1, 2, str_to_date('02/10/2026', '%d/%m/%Y'), 21, 40);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
