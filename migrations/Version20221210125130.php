<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221210125130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_content DROP FOREIGN KEY FK_51FF8AE4584665A');
        $this->addSql('ALTER TABLE cart_content ADD CONSTRAINT FK_51FF8AE4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_content DROP FOREIGN KEY FK_51FF8AE4584665A');
        $this->addSql('ALTER TABLE cart_content ADD CONSTRAINT FK_51FF8AE4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }
}
