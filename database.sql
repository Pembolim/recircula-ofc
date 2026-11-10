CREATE DATABASE IF NOT EXISTS recircula_completo
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE recircula_completo;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE objetos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(120) NOT NULL,
    descricao TEXT NOT NULL,
    tipo ENUM('doacao', 'troca') NOT NULL,
    imagem VARCHAR(255) DEFAULT NULL,
    status ENUM('disponivel', 'em_negociacao', 'concluido') DEFAULT 'disponivel',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE solicitacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    objeto_id INT NOT NULL,
    solicitante_id INT NOT NULL,
    dono_id INT NOT NULL,
    mensagem_inicial VARCHAR(500) DEFAULT NULL,
    status ENUM('pendente', 'aceita', 'recusada', 'concluida') DEFAULT 'pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (objeto_id) REFERENCES objetos(id) ON DELETE CASCADE,
    FOREIGN KEY (solicitante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (dono_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT NOT NULL,
    remetente_id INT NOT NULL,
    mensagem TEXT NOT NULL,
    enviado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (remetente_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
