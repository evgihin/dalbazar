<?php

class m140115_152903_add_dongle extends CDbMigration
{
	public function up()
	{
            $this->addColumn('user', 'dongle', 'text');
	}

	public function down()
	{
		$this->dropColumn('user', 'dongle');
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