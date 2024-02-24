<?php

class m140324_132530_add_big_image_field extends CDbMigration
{
	public function up()
	{
            $this->execute("ALTER TABLE `image` ADD COLUMN `name_big`  text NULL AFTER `name`;");
	}

	public function down()
	{
		$this->dropColumn("image", "name_big");
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