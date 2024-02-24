<?php
/* @var $this AdminController
 * @var $mainFilter array
 * @var $id int
 */
?>
<h1 class="title">Параметры <?= ($id) ? 'фильтра "' . $mainFilter['name'] . '"' : 'нового фильтра' ?></h1>
<form id="filterEdit" method="post" action="<?= ($id) ? $this->createUrl('admin/filter/save', array('filterId' => $id)) : $this->createUrl('admin/filter/insert') ?>">
  <label>Тип:</label>
  <input type="radio" value="i" id="filterType1" name="filter[type]" <?= ($mainFilter['type'] == 'i') ? 'checked' : '' ?>><label for="filterType1">число</label>
  <input type="radio" value="s" id="filterType2" name="filter[type]" <?= ($mainFilter['type'] == 's') ? 'checked' : '' ?>><label for="filterType2">список</label><br>
  <label for="filterName">Имя фильтра:</label>
  <input type="text" id="filterName" name="filter[name]" value="<?= $mainFilter['name'] ?>"><br>

  <label>Категории фильтра:</label><br>
  
  <div id="FACatButtons">
    <input type="button" value="добавить ->" id="categoryAdd"><br>
    <input type="button" value="<- удалить" id="categoryDelete">
  </div>
  <div id="FACatFrom" class="floatleft">
    <select id="categoryFrom" autocomplete="off" multiple="1" size="10">
      <?php foreach ($categories as $category) { ?>
        <optgroup label="<?= $category['name'] ?>">
          <?php
          if (isset($subCategories[$category['category_id']]))
            foreach ($subCategories[$category['category_id']] as $subCategory)
              if (!isset($categoryXref[$subCategory['category_id']])) {
                ?>
                <option value="<?= $subCategory['category_id'] ?>"><?= $subCategory['name'] ?></option>
                <?php
              } else
                $categoryXref[$subCategory['category_id']]['name'] = $subCategory['name'];
          ?>
        </optgroup>
        <?php
      }
      ?>
    </select>
  </div>
  <div id="FACatTo" class="floatright">
    <select id="categoryTo" autocomplete="off" multiple size="10">
      <?php
      foreach ($categoryXref as $id => $category)
        if (isset($category['name'])) {
          ?>
          <option value="<?= $id ?>"><?= $category['name'] ?></option>
          <?php
        }
      ?>
    </select>
  </div>

  <div id="iForm" style="display: none;">
    <label for="ifilterFrom">Значение от:</label>
    <input type="text" id="ifilterFrom" name="filter[from]" value="<?= $mainFilter['from'] ?>"><br>
    <label for="ifilterTo">Значение до:</label>
    <input type="text" id="ifilterTo" name="filter[to]" value="<?= $mainFilter['to'] ?>"><br>
    <label for="ifilterStep">Шаг значения:</label>
    <input type="text" id="ifilterStep" name="filter[step]" value="<?= $mainFilter['step'] ?>"><br>
    <label for="ifilterPiece">Единица измерения:<?= Helpers::htmlTooltip("Например: <b>г.</b> для указания года выпуска") ?></label>
    <input type="text" id="ifilterPiece" name="filter[piece]" value="<?= $mainFilter['piece'] ?>"><br>
  </div>
  <div class="clear"></div><br>

  <div id="sForm" style="display: none;">

    <label for="sfilterDepend">Зависит от:</label>
    <select id="sfilterDepend" name="filter[depend]">
      <option value="0" <?= ($mainFilter['depend'] == 0) ? 'selected=""' : '' ?>>(не зависит)</option>
      <?php
      foreach ($filters as $filter)
        if ($filter['filter_id'] != $id) {
          ?>
          <option value="<?= $filter['filter_id'] ?>" <?= ($filter['filter_id'] == $mainFilter['depend']) ? 'selected=""' : '' ?>>
          <?= $filter['name'] ?>
          </option>
          <?php
        }
      ?>
    </select><br>

    <label for="sfilterDepend" title="Первые сколько параметров отображать в первую очередь?">Количество важных параметров:</label>
    <input type="text" id="sfilterPiece" name="filter[top_count]" value="<?= $mainFilter['top_count'] ?>" size="4">шт.<br>

    <table class="list-sortable" style="width: 100%;">
      <tr>
        <th>ИД</th>
        <th>Имя параметра</th>
        <th>Порядок</th>
        <th></th>
      </tr>
      <?php
      $i = 0;
      foreach ($params as $param) {
        $i++;
        ?>
        <tr class="<?= ($i % 2) ? 'odd' : 'even' ?>">
          <td class="paramId"><?= $param['filter_param_id'] ?></td>
          <td class="paramName">
            <input
              type="text"
              size="50"
              name="oldparam[<?= $param['filter_param_id'] ?>]"
              value="<?= $param['name'] ?>">
          </td>
          <td>
            <input
              type="text"
              size="4"
              name="pos[<?= $param['filter_param_id'] ?>]"
              value="<?= $param['pos'] ?>"
              class="list-pos"
              autocomplete="off">
          </td>
          <td class="deleteBtn"><a href="#"><img src="images/theme/icon-16-trash.png"></a></td>
        </tr>
<?php } ?>
      <tr class="aparamTemplate" style="display:none;">
        <td></td>
        <td class="paramName">
          <input
            type="text"
            size="50"
            name="newparam[]"
            value="">
        </td>
        <td>
          <span title="можно менять только после сохранения">9999</span>
        </td>
        <td class="deleteBtn"><a href="#" title="удалить параметр"><img src="images/theme/icon-16-trash.png"></a></td>
      </tr>
    </table>
  </div>
</form>
<script>
  function changeBlock(){
    if ($('#filterEdit input[name="filter[type]"]:checked').val()=='i'){
      $('#iForm').show();
      $('#sForm').hide();
    }
    if ($('#filterEdit input[name="filter[type]"]:checked').val()=='s'){
      $('#iForm').hide();
      $('#sForm').show();
    }
  }
  changeBlock();

  function addParam(val){
    var template = $('tr.aparamTemplate:last')
    var nnew = template.clone(true);
    nnew.removeClass('aparamTemplate').show().insertBefore(template);
    nnew.find('input[type=text]').eq(0).val(val).focus();
  };

<?= (!$id) ? 'addParam();' : '' ?>

  $('.deleteBtn').click(function(){
    var row = $(this).parents('tr').eq(0);
    var id = row.find('td.paramId').text();
    if (id!='') {
      row.find('td.paramName input').attr('name','delparam['+id+']');
      row.hide();
    } else {
      row.remove();
    }
    return false;
  })
  $('#filterEdit input[name="filter[type]"]').change(changeBlock);

  $('#categoryAdd').click(function(){
    $('#categoryFrom option:selected').remove().appendTo('#categoryTo');
  });
  //$('#filterEdit').on('click','#categoryDelete',function(){
  $('#categoryDelete').click(function(){
    $('#categoryTo option:selected').remove().appendTo('#categoryFrom');
  });
  $('#filterEdit').submit(function(){
    if ($('#categoryTo option').size()==0) {
      alert('Вы не привязали фильтр ни к одной категории. Сохранение невозможно!');
      return false;
    }
    $('#categoryTo option').each(function(){
      $('#filterEdit').append('<input type="hidden" name="categories[]" value="'+$(this).attr('value')+'">');
    });
  });

  $(document).keydown(function(e){ //читаем нажатия клавиш
    switch(e.keyCode){
      case 45: addParam(); break; //нажата insert
    }
  });
</script>