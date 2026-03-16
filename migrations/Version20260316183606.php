<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260316183606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE watchlist_asset_user (watchlist_asset_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (watchlist_asset_id, user_id))');
        $this->addSql('CREATE INDEX IDX_51D945F4284DE094 ON watchlist_asset_user (watchlist_asset_id)');
        $this->addSql('CREATE INDEX IDX_51D945F4A76ED395 ON watchlist_asset_user (user_id)');
        $this->addSql('ALTER TABLE watchlist_asset_user ADD CONSTRAINT FK_51D945F4284DE094 FOREIGN KEY (watchlist_asset_id) REFERENCES watchlist_asset (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE watchlist_asset_user ADD CONSTRAINT FK_51D945F4A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE watchlist_asset DROP CONSTRAINT fk_ac7cc1b04a3353d8');
        $this->addSql('DROP INDEX idx_ac7cc1b04a3353d8');
        $this->addSql('ALTER TABLE watchlist_asset DROP app_user_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SYMBOL ON watchlist_asset (symbol)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE watchlist_asset_user DROP CONSTRAINT FK_51D945F4284DE094');
        $this->addSql('ALTER TABLE watchlist_asset_user DROP CONSTRAINT FK_51D945F4A76ED395');
        $this->addSql('DROP TABLE watchlist_asset_user');
        $this->addSql('DROP INDEX UNIQ_SYMBOL');
        $this->addSql('ALTER TABLE watchlist_asset ADD app_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE watchlist_asset ADD CONSTRAINT fk_ac7cc1b04a3353d8 FOREIGN KEY (app_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_ac7cc1b04a3353d8 ON watchlist_asset (app_user_id)');
    }
}
