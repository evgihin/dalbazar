<?php

class m140311_120201_add_farpost_relations extends CDbMigration
{
	public function up()
	{
            $this->execute("CREATE TABLE `farpost_filter_relation` (
`farpost_filter_relation_id`  int NOT NULL AUTO_INCREMENT ,
`farpost_category_id`  int NULL ,
`name`  varchar(255) NULL ,
`filter_id`  int NULL ,
PRIMARY KEY (`farpost_filter_relation_id`),
INDEX `search` (`name`, `farpost_category_id`) 
)
COMMENT='Хранит связи фильтров фарпоста и основного сайта'
;");
            $this->execute("CREATE TABLE `farpost_param_relation` (
`farpost_param_relation_id`  int NOT NULL AUTO_INCREMENT ,
`farpost_filter_relation_id`  int NULL ,
`name`  varchar(255) NULL ,
`filter_param_id`  int NULL ,
PRIMARY KEY (`farpost_param_relation_id`),
INDEX `search` (`name`, `farpost_filter_relation_id`) 
)
COMMENT='Хранит связи параметров (значений фильтра) фарпоста и основного сайта'
;");
	}

	public function down()
	{
		$this->dropTable("farpost_filter_relation");
                $this->dropTable("farpost_param_relation");
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