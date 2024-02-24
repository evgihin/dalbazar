<?php

class m140414_120638_add_log_table extends CDbMigration {

    public function up() {
        $this->execute("CREATE TABLE `log` (
`log_id`  int NOT NULL AUTO_INCREMENT ,
`user_id`  int NULL ,
`model`  varchar(255) NULL COMMENT 'Сущность, над которой проводятся действия' ,
`action`  varchar(255) NULL COMMENT 'Действие, которое проводится над сайтом' ,
`description`  text NULL ,
`path`  varchar(255) NOT NULL DEFAULT 'site' COMMENT 'Раздел сайта, над которым проводятся операции' ,
`params`  text NULL COMMENT 'Дополнительные параметры действия' ,
`time` int NOT NULL ,
PRIMARY KEY (`log_id`)
)
;");
    }

    public function down() {
        $this->dropTable("log");
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
