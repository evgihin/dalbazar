<?php

class m140130_110704_add_date_to_sended_sms extends CDbMigration
{
	public function up()
	{
            $this->addColumn("sended_sms", "time", "int");
	}

	public function down()
	{
		$this->dropColumn("sended_sms", "time");
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