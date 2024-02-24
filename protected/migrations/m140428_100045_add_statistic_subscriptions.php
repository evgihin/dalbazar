<?php

class m140428_100045_add_statistic_subscriptions extends CDbMigration
{
	public function up()
	{
            $this->execute("CREATE TABLE `statistic_subscription` (
`statistic_subscription_id`  int NULL AUTO_INCREMENT ,
`user_id`  int NULL ,
PRIMARY KEY (`statistic_subscription_id`)
)
COMMENT='Подписка пользователей на статистику сайта'
;");
            $this->execute("CREATE TABLE `user_subscription` (
`user_subscription_id`  int NULL AUTO_INCREMENT ,
`user_id`  int NULL ,
`subscripted_user_id`  int NULL ,
PRIMARY KEY (`user_subscription_id`)
)
COMMENT='Подписка пользователей на статистику других пользователей'
;");
            $this->insert("statistic_subscription", array("user_id"=>1));
            $this->insert("user_subscription", array("user_id"=>"1","subscripted_user_id"=>25));
	}

	public function down()
	{
		$this->dropTable("statistic_subscription");
                $this->dropTable("user_subscription");
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