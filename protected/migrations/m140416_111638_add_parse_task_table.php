<?php

class m140416_111638_add_parse_task_table extends CDbMigration
{
	public function up()
	{
            $this->execute("CREATE TABLE `parse_task` (
                `parse_task_id`  int NOT NULL AUTO_INCREMENT,
                `parser`  varchar(255) NOT NULL COMMENT 'Тип парсера' ,
                `command`  text NOT NULL COMMENT 'Команда, передаваемая в парсер' ,
                `create_time`  int NOT NULL DEFAULT 0 COMMENT 'Дата создания задания' ,
                `end_time`  int NULL DEFAULT 0 COMMENT 'Дата завершения задания' ,
                `complete`  real NOT NULL DEFAULT 0 COMMENT 'Процент готовности' ,
                `description`  text NULL COMMENT 'Описание задания' ,
                `error`  text NULL COMMENT 'Текст ошибки выполнения' ,
                PRIMARY KEY (`parse_task_id`)
                )");
	}

	public function down()
	{
		$this->dropTable("parse_task");
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