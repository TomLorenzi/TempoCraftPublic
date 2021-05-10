<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210403171347 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE craft_card (craft_id INT NOT NULL, card_id INT NOT NULL, INDEX IDX_EE88B5AEE836CCC8 (craft_id), INDEX IDX_EE88B5AE4ACC9A20 (card_id), PRIMARY KEY(craft_id, card_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE craft_item (craft_id INT NOT NULL, item_id INT NOT NULL, INDEX IDX_E7870863E836CCC8 (craft_id), INDEX IDX_E7870863126F525E (item_id), PRIMARY KEY(craft_id, item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE craft_card ADD CONSTRAINT FK_EE88B5AEE836CCC8 FOREIGN KEY (craft_id) REFERENCES craft (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE craft_card ADD CONSTRAINT FK_EE88B5AE4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE craft_item ADD CONSTRAINT FK_E7870863E836CCC8 FOREIGN KEY (craft_id) REFERENCES craft (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE craft_item ADD CONSTRAINT FK_E7870863126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE item_card');
        $this->addSql('ALTER TABLE craft ADD upvote INT DEFAULT NULL, ADD report INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE item_card (item_id INT NOT NULL, card_id INT NOT NULL, INDEX IDX_DE24DC28126F525E (item_id), INDEX IDX_DE24DC284ACC9A20 (card_id), PRIMARY KEY(item_id, card_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE item_card ADD CONSTRAINT FK_DE24DC28126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_card ADD CONSTRAINT FK_DE24DC284ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('DROP TABLE craft_card');
        $this->addSql('DROP TABLE craft_item');
        $this->addSql('ALTER TABLE craft DROP upvote, DROP report');
    }
}
