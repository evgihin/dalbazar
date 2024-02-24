<?php 
/** @var FilterController $this  */ 
/** @var array $filter  */ 
/** @var array $params  */ 
/** @var int $paramId  */ 

$params = array(0=>"(не указано)")+$params;
echo CHtml::dropDownList("filter[".$filter['filter_id']."]", $paramId, $params,array("depend"=>$filter['depend'],"autocomplete"=>"off"));
