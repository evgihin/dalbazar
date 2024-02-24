<?php

class m140311_104000_change_column_type_in_farpost_filter extends CDbMigration {

    public function up() {
        $this->execute("ALTER TABLE `farpost_filter`
MODIFY COLUMN `name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Имя фильтра на оригинальном сайте' AFTER `farpost_filter_id`,
MODIFY COLUMN `function`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'функция, запускаемая в класе Farpost для фильтрации' AFTER `name`,
ADD INDEX `search` (`name`) ;
");
    }

    public function down() {
        $this->execute("ALTER TABLE `farpost_filter`
MODIFY COLUMN `name`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Имя фильтра на оригинальном сайте' AFTER `farpost_filter_id`,
MODIFY COLUMN `function`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'функция, запускаемая в класе Farpost для фильтрации' AFTER `name`,
DROP INDEX `search`;
");
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
