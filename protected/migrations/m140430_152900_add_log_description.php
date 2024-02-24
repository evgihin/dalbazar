<?php

class m140430_152900_add_log_description extends CDbMigration
{
	public function up()
	{
            $this->execute("CREATE TABLE `log_description` (
`log_description_id`  int NOT NULL AUTO_INCREMENT ,
`path`  varchar(255) NOT NULL ,
`model`  varchar(255) NOT NULL ,
`action`  varchar(255) NOT NULL ,
`description`  tinytext NOT NULL ,
PRIMARY KEY (`log_description_id`),
UNIQUE INDEX `path_model_action` (`path`, `model`, `action`) USING HASH 
)
COMMENT='Описание всех действий админки'
;");
	}

	public function down()
	{
		$this->delete("log_description");
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