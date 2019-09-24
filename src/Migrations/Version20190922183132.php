<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190922183132 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE todo_item (id INT AUTO_INCREMENT NOT NULL, list_id INT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, status SMALLINT DEFAULT 0 NOT NULL, deleted TINYINT(1) DEFAULT \'0\' NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME on update CURRENT_TIMESTAMP, UNIQUE INDEX UNIQ_40CA430177153098 (code), INDEX IDX_40CA43013DAE168B (list_id), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE todo_list (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(255) NOT NULL, deleted TINYINT(1) DEFAULT \'0\' NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME on update CURRENT_TIMESTAMP, UNIQUE INDEX UNIQ_1B199E0777153098 (code), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE todo_item ADD CONSTRAINT FK_40CA43013DAE168B FOREIGN KEY (list_id) REFERENCES todo_list (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE todo_item DROP FOREIGN KEY FK_40CA43013DAE168B');
        $this->addSql('DROP TABLE todo_item');
        $this->addSql('DROP TABLE todo_list');
    }
}
