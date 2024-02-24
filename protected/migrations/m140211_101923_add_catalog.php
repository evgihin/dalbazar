<?php

class m140211_101923_add_catalog extends CDbMigration
{
	public function up()
	{
            $this->createTable('catalog', array(
                'catalog_id'=>'pk',
                'link'=>'tinytext',
                'adres'=>'text',
                'description'=>'text',
                'graphic'=>'tinytext',
                'city_distinct'=>'tinytext'
            ));
            $this->createTable('catalog_tree', array(
                'catalog_tree_id'=>'pk',
                'catalog_ancestor_id'=>'int',
                'catalog_descendant_id'=>'int'
            ));
            $this->execute("ALTER TABLE catalog AUTO_INCREMENT=1;");
	}

	public function down()
	{
		$this->dropTable('catalog');
		$this->dropTable('catalog_tree');
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