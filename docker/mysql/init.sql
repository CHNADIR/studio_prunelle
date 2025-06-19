-- Créer la base de données si elle n'existe pas déjà
CREATE DATABASE IF NOT EXISTS prunelle;

-- Utiliser la base de données prunelle
USE prunelle;

-- S'assurer que l'utilisateur existe et lui donner tous les droits sur la base
GRANT ALL PRIVILEGES ON prunelle.* TO 'user_prunelle'@'%';
FLUSH PRIVILEGES;