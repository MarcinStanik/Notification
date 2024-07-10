<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708013416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
CREATE TABLE IF NOT EXISTS notification (
    id INT UNSIGNED AUTO_INCREMENT NOT NULL,
    chanel_type ENUM('EMAIL', 'SMS', 'PUSH_NOTIFICATION', 'FACEBOOK_MESSENGER') NOT NULL,
    subject VARCHAR(1024) DEFAULT NULL,
    text_body LONGTEXT DEFAULT NULL,
    html_body LONGTEXT DEFAULT NULL,
    settings JSON DEFAULT NULL,
    created_at DATETIME NOT NULL,

    PRIMARY KEY(id),

    INDEX notification_INDEX01 (chanel_type ASC),
    INDEX notification_INDEX02 (created_at ASC)

) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
        ");

        $this->addSql("
CREATE TABLE IF NOT EXISTS notification_recipient (
    id INT UNSIGNED AUTO_INCREMENT NOT NULL,
    notification_id INT UNSIGNED NOT NULL,
    recipient VARCHAR(256) NOT NULL COMMENT 'email address, mobile number',
    user_identifier VARCHAR(128) COMMENT 'user id or other user identifier',
    status ENUM('NEW', 'IN_PROGRESS', 'SENT', 'ERROR') NOT NULL,
    status_date DATETIME NOT NULL,
    provider_name VARCHAR(64) NULL,
    send_at DATETIME NULL,
    send_attempt_count SMALLINT NOT NULL,
    send_report JSON DEFAULT NULL,

    PRIMARY KEY(id),

    INDEX fk_notification_recipient_idx (notification_id),

    INDEX notification_recipient_INDEX01 (status ASC),
    INDEX notification_recipient_INDEX02 (recipient ASC),
    INDEX notification_recipient_INDEX03 (user_identifier ASC),
    INDEX notification_recipient_INDEX04 (status_date ASC),
    INDEX notification_recipient_INDEX05 (send_at ASC),
    INDEX notification_recipient_INDEX06 (provider_name ASC),

    CONSTRAINT fk_notification_recipient_notification
        FOREIGN KEY (notification_id)
        REFERENCES notification (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification_recipient DROP FOREIGN KEY fk_notification_recipient_notification');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE notification_recipient');
    }
}
