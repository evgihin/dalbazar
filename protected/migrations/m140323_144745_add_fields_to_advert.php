<?php

class m140323_144745_add_fields_to_advert extends CDbMigration
{
	public function up()
	{
            echo "добавляем поля provider (поставщик), contacts (контактные данные), link (ссылка на оригинал)\n";
            $this->execute("ALTER TABLE `advert`
ADD COLUMN `provider`  varchar(255) NULL DEFAULT 'site' COMMENT 'Источник объявления' AFTER `create_time`,
ADD COLUMN `contacts`  text NULL COMMENT 'Контакты объявления в строковой текстовой форме' AFTER `provider`,
ADD COLUMN `link`  text NULL COMMENT 'ссылка на оригинал' AFTER `contacts`;");
	}

	public function down()
	{
		echo "удаляем поля provider (поставщик), contacts (контактные данные), link (ссылка на оригинал)\n";
		$this->dropColumn("advert", "provider");
		$this->dropColumn("advert", "contacts");
		$this->dropColumn("advert", "link");
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}