<?php

class m140130_115410_add_stop_login_base extends CDbMigration
{
	public function up()
	{
            $this->createTable("stop_login", array(
                "stop_login_id"=>'pk',
                "login"=>'tinytext'
            ));
            $this->insert("stop_login", array("login"=>"admin"));
            $this->insert("stop_login", array("login"=>"user"));
            $this->insert("stop_login", array("login"=>"example"));
	}

	public function down()
	{
		$this->dropTable("stop_login");
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