<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260722104843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop the redundant DB-level default on event.status (managed by the entity)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ALTER status DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ALTER status SET DEFAULT \'draft\'');
    }
}
