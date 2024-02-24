<?php

class m140119_101824_remove_phones extends CDbMigration
{
	public function up()
	{
            $this->dropColumn("user", "phone1");
            $this->dropColumn("user", "phone2");
	}

	public function down()
	{
		$this->addColumn("user", "phone1", "tinytext");
		$this->addColumn("user", "phone2", "tinytext");
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