<?php

class m140222_020328_add_company_table extends CDbMigration {

    public function up() {
        //добавляем таблицу компании
        $this->execute("CREATE TABLE `company` (
`company_id`  int NOT NULL AUTO_INCREMENT ,
`catalog_id`  int NULL ,
`link`  tinytext NULL COMMENT 'Ссылка на сайт предприятия' ,
`adress`  text NULL COMMENT 'Рабочий адресс предприятия' ,
`description`  text NULL COMMENT 'Описание предприятия' ,
`schedule`  tinytext NULL COMMENT 'График работы предприятия' ,
`district`  tinytext NULL COMMENT 'Район города' ,
`create_time`  int NULL ,
`user_id`  int NULL ,
`removed`  tinyint NOT NULL DEFAULT 0,
PRIMARY KEY (`company_id`)
)
COMMENT='Предприятия для каталога предприятий'");
        //модифицируем дерево каталога
        $this->execute("ALTER TABLE `catalog`
DROP COLUMN `link`,
DROP COLUMN `adres`,
DROP COLUMN `description`,
DROP COLUMN `graphic`,
DROP COLUMN `city_distinct`,
ADD COLUMN `catalog_parent_id`  int NULL AFTER `catalog_id`,
ADD COLUMN `name`  text NULL AFTER `catalog_parent_id`,
ADD COLUMN `path`  text NULL COMMENT 'Путь до родительского каталога, строится через точку, например 0.10.12' AFTER `name`,
ADD COLUMN `pos`  smallint NULL COMMENT 'Позиция в списке' AFTER `path`,
ADD COLUMN `create_time`  int NULL COMMENT 'Дата создания элемента' AFTER `pos`,
ADD COLUMN `removed`  tinyint NOT NULL DEFAULT 0 AFTER `create_time`,
COMMENT='Дерево категорий каталога предприятий, сам каталог предприятий находится в таблице company'");
        $this->dropTable("catalog_tree");
    }

    public function down() {
        $this->dropTable('company');
        $this->dropTable('catalog');
        $this->createTable('catalog', array(
            'catalog_id' => 'pk',
            'link' => 'tinytext',
            'adres' => 'text',
            'description' => 'text',
            'graphic' => 'tinytext',
            'city_distinct' => 'tinytext'
        ));
        $this->createTable('catalog_tree', array(
            'catalog_tree_id' => 'pk',
            'catalog_ancestor_id' => 'int',
            'catalog_descendant_id' => 'int'
        ));
        $this->execute("ALTER TABLE catalog AUTO_INCREMENT=1;");
    }

}
