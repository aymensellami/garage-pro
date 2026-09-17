<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260914011649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT NOT NULL, created_by_id INT DEFAULT NULL, scheduled_at DATETIME NOT NULL, duration INT NOT NULL, reason VARCHAR(255) NOT NULL, status VARCHAR(30) NOT NULL, notes LONGTEXT DEFAULT NULL, reminder_sent TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_FE38F844545317D1 (vehicle_id), INDEX IDX_FE38F844B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(20) DEFAULT NULL, address LONGTEXT DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(10) DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_81398E09E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intervention (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT NOT NULL, mechanic_id INT DEFAULT NULL, reference VARCHAR(30) NOT NULL, description LONGTEXT NOT NULL, operations JSON NOT NULL, estimated_cost NUMERIC(10, 2) NOT NULL, final_cost NUMERIC(10, 2) DEFAULT NULL, status VARCHAR(30) NOT NULL, scheduled_at DATETIME NOT NULL, started_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, duration_minutes INT DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_D11814ABAEA34913 (reference), INDEX IDX_D11814AB545317D1 (vehicle_id), INDEX IDX_D11814AB9A67DB00 (mechanic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intervention_part (id INT AUTO_INCREMENT NOT NULL, intervention_id INT NOT NULL, part_id INT NOT NULL, quantity INT NOT NULL, unit_price_at_time NUMERIC(10, 2) NOT NULL, discount_percent NUMERIC(5, 2) DEFAULT NULL, INDEX IDX_46B068048EAE3863 (intervention_id), INDEX IDX_46B068044CE34BEC (part_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, intervention_id INT NOT NULL, customer_id INT NOT NULL, number VARCHAR(50) NOT NULL, total_ht NUMERIC(10, 2) NOT NULL, tva_rate NUMERIC(5, 2) NOT NULL, total_ttc NUMERIC(10, 2) NOT NULL, status VARCHAR(30) NOT NULL, issued_at DATETIME NOT NULL, due_date DATE NOT NULL, paid_at DATETIME DEFAULT NULL, payment_method VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_9065174496901F54 (number), UNIQUE INDEX UNIQ_906517448EAE3863 (intervention_id), INDEX IDX_906517449395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_line (id INT AUTO_INCREMENT NOT NULL, invoice_id INT NOT NULL, label VARCHAR(255) NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, tva_rate NUMERIC(5, 2) DEFAULT NULL, discount NUMERIC(10, 2) DEFAULT NULL, sort_order INT NOT NULL, INDEX IDX_D3D1D6932989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE maintenance_alert (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT NOT NULL, resolved_by_id INT DEFAULT NULL, type VARCHAR(50) NOT NULL, severity VARCHAR(30) NOT NULL, message VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, triggered_at DATETIME NOT NULL, resolved_at DATETIME DEFAULT NULL, is_resolved TINYINT(1) NOT NULL, INDEX IDX_85E0DB3A545317D1 (vehicle_id), INDEX IDX_85E0DB3A6713A32B (resolved_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE part (id INT AUTO_INCREMENT NOT NULL, supplier_id INT DEFAULT NULL, reference VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, stock_quantity INT NOT NULL, min_stock_alert INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, location VARCHAR(100) DEFAULT NULL, is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_490F70C6AEA34913 (reference), INDEX IDX_490F70C62ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, invoice_id INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, method VARCHAR(50) NOT NULL, transaction_id VARCHAR(100) DEFAULT NULL, paid_at DATETIME NOT NULL, INDEX IDX_6D28840D2989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE purchase_order (id INT AUTO_INCREMENT NOT NULL, supplier_id INT NOT NULL, reference VARCHAR(50) NOT NULL, status VARCHAR(30) NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, ordered_at DATETIME NOT NULL, expected_at DATE DEFAULT NULL, received_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_21E210B2AEA34913 (reference), INDEX IDX_21E210B22ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE purchase_order_line (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, part_id INT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, received_qty INT NOT NULL, INDEX IDX_90D6D92B8D9F6D38 (order_id), INDEX IDX_90D6D92B4CE34BEC (part_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quote (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT NOT NULL, customer_id INT NOT NULL, intervention_id INT DEFAULT NULL, number VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, operations JSON NOT NULL, parts JSON DEFAULT NULL, total_ht NUMERIC(10, 2) NOT NULL, total_ttc NUMERIC(10, 2) NOT NULL, status VARCHAR(30) NOT NULL, valid_until DATE NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_6B71CBF496901F54 (number), INDEX IDX_6B71CBF4545317D1 (vehicle_id), INDEX IDX_6B71CBF49395C3F3 (customer_id), UNIQUE INDEX UNIQ_6B71CBF48EAE3863 (intervention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock_movement (id INT AUTO_INCREMENT NOT NULL, part_id INT NOT NULL, user_id INT NOT NULL, intervention_id INT DEFAULT NULL, type VARCHAR(30) NOT NULL, quantity INT NOT NULL, reason VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_BB1BC1B54CE34BEC (part_id), INDEX IDX_BB1BC1B5A76ED395 (user_id), INDEX IDX_BB1BC1B58EAE3863 (intervention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, contact_name VARCHAR(150) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, address LONGTEXT DEFAULT NULL, siret VARCHAR(20) DEFAULT NULL, payment_terms INT NOT NULL, is_active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, is_active TINYINT(1) NOT NULL, last_login_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicle (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, brand VARCHAR(100) NOT NULL, model VARCHAR(100) NOT NULL, registration VARCHAR(20) NOT NULL, vin VARCHAR(50) DEFAULT NULL, year INT NOT NULL, mileage INT NOT NULL, fuel_type VARCHAR(20) NOT NULL, engine_code VARCHAR(50) DEFAULT NULL, color VARCHAR(50) DEFAULT NULL, technical_control_date DATE DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1B80E48662A8A7A7 (registration), UNIQUE INDEX UNIQ_1B80E486B1085141 (vin), INDEX IDX_1B80E4867E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB9A67DB00 FOREIGN KEY (mechanic_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE intervention_part ADD CONSTRAINT FK_46B068048EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE intervention_part ADD CONSTRAINT FK_46B068044CE34BEC FOREIGN KEY (part_id) REFERENCES part (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517448EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE invoice_line ADD CONSTRAINT FK_D3D1D6932989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE maintenance_alert ADD CONSTRAINT FK_85E0DB3A545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE maintenance_alert ADD CONSTRAINT FK_85E0DB3A6713A32B FOREIGN KEY (resolved_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE part ADD CONSTRAINT FK_490F70C62ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE purchase_order ADD CONSTRAINT FK_21E210B22ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE purchase_order_line ADD CONSTRAINT FK_90D6D92B8D9F6D38 FOREIGN KEY (order_id) REFERENCES purchase_order (id)');
        $this->addSql('ALTER TABLE purchase_order_line ADD CONSTRAINT FK_90D6D92B4CE34BEC FOREIGN KEY (part_id) REFERENCES part (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF4545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF49395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF48EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B54CE34BEC FOREIGN KEY (part_id) REFERENCES part (id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B58EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id)');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4867E3C61F9 FOREIGN KEY (owner_id) REFERENCES customer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844545317D1');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844B03A8386');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB545317D1');
        $this->addSql('ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB9A67DB00');
        $this->addSql('ALTER TABLE intervention_part DROP FOREIGN KEY FK_46B068048EAE3863');
        $this->addSql('ALTER TABLE intervention_part DROP FOREIGN KEY FK_46B068044CE34BEC');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517448EAE3863');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517449395C3F3');
        $this->addSql('ALTER TABLE invoice_line DROP FOREIGN KEY FK_D3D1D6932989F1FD');
        $this->addSql('ALTER TABLE maintenance_alert DROP FOREIGN KEY FK_85E0DB3A545317D1');
        $this->addSql('ALTER TABLE maintenance_alert DROP FOREIGN KEY FK_85E0DB3A6713A32B');
        $this->addSql('ALTER TABLE part DROP FOREIGN KEY FK_490F70C62ADD6D8C');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D2989F1FD');
        $this->addSql('ALTER TABLE purchase_order DROP FOREIGN KEY FK_21E210B22ADD6D8C');
        $this->addSql('ALTER TABLE purchase_order_line DROP FOREIGN KEY FK_90D6D92B8D9F6D38');
        $this->addSql('ALTER TABLE purchase_order_line DROP FOREIGN KEY FK_90D6D92B4CE34BEC');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF4545317D1');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF49395C3F3');
        $this->addSql('ALTER TABLE quote DROP FOREIGN KEY FK_6B71CBF48EAE3863');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B54CE34BEC');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B5A76ED395');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B58EAE3863');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E4867E3C61F9');
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE intervention');
        $this->addSql('DROP TABLE intervention_part');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_line');
        $this->addSql('DROP TABLE maintenance_alert');
        $this->addSql('DROP TABLE part');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE purchase_order');
        $this->addSql('DROP TABLE purchase_order_line');
        $this->addSql('DROP TABLE quote');
        $this->addSql('DROP TABLE stock_movement');
        $this->addSql('DROP TABLE supplier');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE vehicle');
    }
}
