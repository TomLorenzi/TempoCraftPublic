<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210404120315 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE report (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, craft_id INT DEFAULT NULL, INDEX IDX_C42F7784A76ED395 (user_id), INDEX IDX_C42F7784E836CCC8 (craft_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE up_vote (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, craft_id INT DEFAULT NULL, INDEX IDX_E9092F51A76ED395 (user_id), INDEX IDX_E9092F51E836CCC8 (craft_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784E836CCC8 FOREIGN KEY (craft_id) REFERENCES craft (id)');
        $this->addSql('ALTER TABLE up_vote ADD CONSTRAINT FK_E9092F51A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE up_vote ADD CONSTRAINT FK_E9092F51E836CCC8 FOREIGN KEY (craft_id) REFERENCES craft (id)');
        $this->addSql('ALTER TABLE craft DROP upvote, DROP report');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE up_vote');
        $this->addSql('ALTER TABLE craft ADD upvote INT DEFAULT NULL, ADD report INT DEFAULT NULL');
    }
}
