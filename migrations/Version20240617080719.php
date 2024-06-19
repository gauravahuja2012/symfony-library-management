<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240617080719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE borrows (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, book_id INT NOT NULL, borrow_date DATETIME NOT NULL, return_date DATETIME DEFAULT NULL, INDEX IDX_D03AA72FA76ED395 (user_id), INDEX IDX_D03AA72F16A2B381 (book_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE borrows ADD CONSTRAINT FK_D03AA72FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE borrows ADD CONSTRAINT FK_D03AA72F16A2B381 FOREIGN KEY (book_id) REFERENCES books (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE borrows DROP FOREIGN KEY FK_D03AA72FA76ED395');
        $this->addSql('ALTER TABLE borrows DROP FOREIGN KEY FK_D03AA72F16A2B381');
        $this->addSql('DROP TABLE borrows');
    }
}
