<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210409172539 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3E836CCC8');
        $this->addSql('DROP INDEX IDX_A3C664D3E836CCC8 ON subscription');
        $this->addSql('ALTER TABLE subscription ADD item_id INT DEFAULT NULL, DROP craft_id');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_A3C664D3126F525E ON subscription (item_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3126F525E');
        $this->addSql('DROP INDEX IDX_A3C664D3126F525E ON subscription');
        $this->addSql('ALTER TABLE subscription ADD craft_id INT NOT NULL, DROP item_id');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3E836CCC8 FOREIGN KEY (craft_id) REFERENCES craft (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A3C664D3E836CCC8 ON subscription (craft_id)');
    }
}
