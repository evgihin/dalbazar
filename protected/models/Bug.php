<?php

/**
 * This is the model class for table "bug".
 *
 * The followings are the available columns in table 'bug':
 * @property integer $bug_id
 * @property string $link
 * @property string $text
 * @property integer $creation
 * @property string $user_agent
 * @property string $ip
 * @property integer $solved
 * @property string $note
 */
class Bug extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Bug the static model class
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
		return 'bug';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('creation, solved', 'numerical', 'integerOnly'=>true),
			array('link, text, user_agent, ip, note', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('bug_id, link, text, creation, user_agent, ip, solved, note', 'safe', 'on'=>'search'),
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
			'bug_id' => 'Bug',
			'link' => 'Link',
			'text' => 'Text',
			'creation' => 'Creation',
			'user_agent' => 'User Agent',
			'ip' => 'Ip',
			'solved' => 'Solved',
			'note' => 'Note',
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

		$criteria->compare('bug_id',$this->bug_id);
		$criteria->compare('link',$this->link,true);
		$criteria->compare('text',$this->text,true);
		$criteria->compare('creation',$this->creation);
		$criteria->compare('user_agent',$this->user_agent,true);
		$criteria->compare('ip',$this->ip,true);
		$criteria->compare('solved',$this->solved);
		$criteria->compare('note',$this->note,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}