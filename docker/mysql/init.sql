-- Initialisation de la base GaragePro
CREATE DATABASE IF NOT EXISTS garage_pro_test;
GRANT ALL PRIVILEGES ON garage_pro_test.* TO 'garage_user'@'%';
FLUSH PRIVILEGES;
