<?php
/*  @var $this ParamController */
/*  @var $requiredParams array список параметров, от которых зависит фильтр */
/*  @var $filterId int ИД фильтра */
?><?php
$this->act
        ->save('addDepend')
        ->extendJS('addParam', 'addParam();', 'добавить<br>параметр', 'images/theme/icon-32-new.png');
        
?>

<form action="<?= $this->createUrl('admin/param/insertDepend', array('filter_id' => $filterId)) ?>" id="addDepend" method="post">
	Параметры зависят от: <?= UCHtml::dropDownList('depend', 0, array('0' => '(выбирите значение)') + $requiredParams); ?>
	<table class="list-sortable">
		<tr>
			<th>Имя параметра</th>
			<th>Порядок</th>
			<th></th>
		</tr>
		<tr class="template" style="display: none;">
			<td><input type="text" value="" size="30" name="param[][name]"></td>
			<td>
				<input
					type="text"
					size="4"
					name="param[][pos]"
					value="9999"
					class="list-pos"
					autocomplete="off">
			</td>
			<td><a href="#" title="удалить параметр" class="deletBtn"><img src="images/theme/icon-16-trash.png"></a></td>
		</tr>
	</table>
</form>

<script>
	function addParam(val){
		$('tr.template').clone(true)
		.show()
		.removeClass('template')
		.insertBefore('tr.template')
		.find("input[name='param[][name]']")
		.val(val)
		.focus();
	}

	$('.deletBtn').click(function(){
		$(this).parents('tr').remove();
		return false;
	})

	$(document).keydown(function(e){ //читаем нажатия клавиш
		switch(e.keyCode){
			case 45: addParam(); break; //нажата insert
		}
	});
</script>
<script src="http://i.rdrom.ru/js/firms_models.js?dea76abe" ></script>
<script>
	$(function(){
		var elems = $('#elems');
		for (id in drom_js_models_data){
			elems.append('<option value="'+id+'">'+drom_js_models_data[id][0]+'</option>');
		}

		$('#btn1').click(function(){
			id2 = $('#elems').val();

			for (id in drom_js_models_data[id2][3]){
				addParam(drom_js_models_data[id2][3][id][0]);
			}
		})

	$('#depend').change(function(){
		$('#elems').removeAttr('selected');
		var name = $(this).find(':selected').html();
		$('#elems>*').each(function(){
			if ($(this).html()==name) $(this).attr('selected', 'selected');
		});
		$('#btn1').click();
	});
	})
</script>
<select id='elems'>
</select>
<button id="btn1">ok</button>