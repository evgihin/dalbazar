<?php

class m140130_111044_add_date_to_recieved_sms extends CDbMigration
{
	public function up()
	{
            $this->addColumn("recieved_sms", "time", "int");
	}

	public function down()
	{
		$this->dropColumn("recieved_sms", "time");
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