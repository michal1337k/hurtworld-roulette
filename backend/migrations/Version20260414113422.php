<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414113422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inventory (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, item_id INT NOT NULL, player_id VARCHAR(32) NOT NULL, INDEX IDX_B12D4A36126F525E (item_id), INDEX IDX_B12D4A3699E6F5DF (player_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) NOT NULL, chance DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (steam_id VARCHAR(32) NOT NULL, nickname VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, balance INT DEFAULT NULL, roles JSON NOT NULL, PRIMARY KEY (steam_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A3699E6F5DF FOREIGN KEY (player_id) REFERENCES `user` (steam_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36126F525E');
        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A3699E6F5DF');
        $this->addSql('DROP TABLE inventory');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE `user`');
    }
}
