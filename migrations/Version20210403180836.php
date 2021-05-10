<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210403180836 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE craft_item');
        $this->addSql('ALTER TABLE craft ADD item_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE craft ADD CONSTRAINT FK_F45C4A84126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_F45C4A84126F525E ON craft (item_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE craft_item (craft_id INT NOT NULL, item_id INT NOT NULL, INDEX IDX_E7870863126F525E (item_id), INDEX IDX_E7870863E836CCC8 (craft_id), PRIMARY KEY(craft_id, item_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE craft_item ADD CONSTRAINT FK_E7870863126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE craft_item ADD CONSTRAINT FK_E7870863E836CCC8 FOREIGN KEY (craft_id) REFERENCES craft (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE craft DROP FOREIGN KEY FK_F45C4A84126F525E');
        $this->addSql('DROP INDEX IDX_F45C4A84126F525E ON craft');
        $this->addSql('ALTER TABLE craft DROP item_id');
    }
}
