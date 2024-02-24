<?php

/**
 * This is the model class for table "category".
 *
 * The followings are the available columns in table 'category':
 * @property integer $category_id
 * @property string $alias
 * @property string $name
 * @property string $category_parent_id
 * @property string $img
 * @property integer $pos
 * @property integer $level
 * @property integer $active
 * @property integer $top
 * @property string $flypage
 * @property string $template
 */
class AdminCategory extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AdminCategory the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'category';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('pos, level, active, top', 'numerical', 'integerOnly'=>true),
			array('category_parent_id', 'length', 'max'=>11),
			array('alias, name, img, flypage, template', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('category_id, alias, name, category_parent_id, img, pos, level, active, top, flypage, template', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'category_id' => 'Category',
			'alias' => 'Alias',
			'name' => 'Name',
			'category_parent_id' => 'Category Parent',
			'img' => 'Img',
			'pos' => 'Pos',
			'level' => 'Level',
			'active' => 'Active',
			'top' => 'Top',
			'flypage' => 'Flypage',
			'template' => 'Template',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('alias',$this->alias,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('category_parent_id',$this->category_parent_id,true);
		$criteria->compare('img',$this->img,true);
		$criteria->compare('pos',$this->pos);
		$criteria->compare('level',$this->level);
		$criteria->compare('active',$this->active);
		$criteria->compare('top',$this->top);
		$criteria->compare('flypage',$this->flypage,true);
		$criteria->compare('template',$this->template,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}