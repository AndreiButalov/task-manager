<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260618063157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE board_members (board_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (board_id, user_id))');
        $this->addSql('CREATE INDEX IDX_DBEFAF0E7EC5785 ON board_members (board_id)');
        $this->addSql('CREATE INDEX IDX_DBEFAF0A76ED395 ON board_members (user_id)');
        $this->addSql('CREATE TABLE task_assignees (task_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (task_id, user_id))');
        $this->addSql('CREATE INDEX IDX_6DEED38D8DB60186 ON task_assignees (task_id)');
        $this->addSql('CREATE INDEX IDX_6DEED38DA76ED395 ON task_assignees (user_id)');
        $this->addSql('ALTER TABLE board_members ADD CONSTRAINT FK_DBEFAF0E7EC5785 FOREIGN KEY (board_id) REFERENCES board (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE board_members ADD CONSTRAINT FK_DBEFAF0A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_assignees ADD CONSTRAINT FK_6DEED38D8DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_assignees ADD CONSTRAINT FK_6DEED38DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE board_members DROP CONSTRAINT FK_DBEFAF0E7EC5785');
        $this->addSql('ALTER TABLE board_members DROP CONSTRAINT FK_DBEFAF0A76ED395');
        $this->addSql('ALTER TABLE task_assignees DROP CONSTRAINT FK_6DEED38D8DB60186');
        $this->addSql('ALTER TABLE task_assignees DROP CONSTRAINT FK_6DEED38DA76ED395');
        $this->addSql('DROP TABLE board_members');
        $this->addSql('DROP TABLE task_assignees');
    }
}
