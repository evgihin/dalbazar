<?php

class m140301_031955_add_level_to_catalog extends CDbMigration {

    public function up() {
        $this->addColumn("catalog", "level", "tinyint");
        $pathes = $this->dbConnection->createCommand()
                ->select("path")
                ->from("catalog")
                ->queryColumn();
        if ($pathes)
            foreach ($pathes as $path) {
                $p = array_reverse(explode(".", $path));
                $this->dbConnection->createCommand()->update("catalog", array(
                    "level" => count($p) - 1
                        ), "catalog_id=:cid", array(":cid" => $p[0]));
            }
    }

    public function down() {
        $this->dropColumn("catalog", "level");
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
