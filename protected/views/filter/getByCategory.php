<?php /* @var $this FilterController */ ?>
<?php /* @var $filters array */ ?>
<?php /* @var $params array */ ?>
<?php /* @var $values array */ ?>
<?php /* @var $categoryId int */ ?>
<?php /* @var $advertId int */ ?>
<div id="<?= 'filters' . $categoryId ?>">
  <?php foreach ($filters as $filter) { ?>
    <div class="advAddField">
      <?php
      switch ($filter['type']) {
        case 's':
          $this->renderPartial('getByCategory_S', array(
              'filter' => $filter,
              'params' => ( isset($params[$filter['filter_id']]) ) ? $params[$filter['filter_id']] : array(),
              'value' => ( isset($values[$filter['filter_id']]['filter_param_id']) ) ? $values[$filter['filter_id']]['filter_param_id'] : false,
              'fieldName' => $fieldName
          ));
          break;
        case 'i':
          if (!empty($values[$filter['filter_id']]['value_short'])) {
            $value = $values[$filter['filter_id']]['value_short'];
          } elseif ($advertId) {
            $value = false;
          } else {
            $value = $filter['from'];
          }
          $this->renderPartial('getByCategory_I', array(
              'filter' => $filter,
              'value' => $value,
              'fieldName' => $fieldName
          ));
          break;
      }
      ?>
    </div>
  <?php } ?>
</div>
<script>
  function disableIField(){
    if (this.checked)
      $(this).siblings("input[type=text]").attr("disabled", "disabled");
    else
      $(this).siblings("input[type=text]").removeAttr("disabled");
  }

  $('#<?= 'filters' . $categoryId ?> .advUnknown').on('click change',disableIField).each(disableIField);

  $( "#<?= 'filters' . $categoryId ?> .advAddSlider" ).each(function(){
    var elem = $(this).siblings('input');
    $(this).slider({
      min: parseInt(elem.attr('min')),
      max: parseInt(elem.attr('max')),
      step: parseInt(elem.attr('step')),
      value:  parseInt(elem.val()),
      slide: function( event, ui ) {
        $(this).siblings('input').val(ui.value);
      },
      change: function(event, ui){
        $(this).siblings('input').val(ui.value);
      }
    });
  });
  $("#<?= 'filters' . $categoryId ?>").on('change','.slided', function(){$(this).siblings('.advAddSlider').slider( "value" ,$(this).val());} );

  function updateDepends(){
    if (typeof($(this).data('id'))!="undefined" && $(this).val()!='-1'){
      var id = $(this).data('id');
      var val = $(this).val();
      $('#<?= 'filters' . $categoryId ?> select').each(function(){
        if ($(this).data('depend')==id){
          $.post('<?= $this->createUrl('filter/getDependByParam', array('advert_id' => $advertId)) ?>', { 'id' : $(this).data('id'), 'value' : val },$.proxy(function(data){
            $(this).html(data);
            $(this).change();
          },this));
        }
      })
    }
  }
  $('#<?= 'filters' . $categoryId ?>').on('change','select',updateDepends);
  $('#<?= 'filters' . $categoryId ?> select').each(updateDepends);
</script>