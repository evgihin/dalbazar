<?php

class m140321_004352_modify_farpost_parsed_table extends CDbMigration
{
	public function up()
	{
            $this->addColumn("farpost_parsed", "farpost_category_id", "int");
	}

	public function down()
	{
		$this->dropColumn("farpost_parsed", "farpost_category_id");
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