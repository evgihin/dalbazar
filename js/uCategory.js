/*
 * Описываем действия на странице категории
 */
$(function(){
	var filterForm = $('#filters'); //форма фильтра
	var content = $('#section'); //блок с контентом
	var submitBtn = $('#submitFilter'); //кнопка "применить фильтр"
	var needUpdateDepend = false; //устанавливаем true, когда необходимо обновить зависимые фильтры

	//постраничная навигация
	content.on('click','#catPagination a',function(){
		filterForm.attr('action',$(this).attr('href')).submit();
		return false;
	});

	//управление размером страницы
	content.on('click','#catPageSize a',function(){
		$('#pagesize').val($(this).data('size'));
		filterForm.submit();
		return false;
	})

	//анимация красивых объявлений
	bpPx = 0;
	function changePos(){
		$('.ALPictured').css('backgroundPosition','center '+bpPx+'px');
		bpPx-=0.5;
		if (bpPx==-32665) bpPx*=-1;
	}
	setInterval(changePos, 15);

	//дальше всё про страницу фильтров

	/**
	 * проверяет, зависит ли кто-нибудь от фильтра с указанным ИД-ом
	 */
	function anyDepend(id){
		var depended = false;
		filterForm.find('.filter').each(function(){
			if ($(this).data('depend')==id) {
				depended = true;
				return false;
			}
		});
		return depended;
	}

	//получает и впихивает значение по кнопке "обновить все параметры",
	//потом обновляет все зависимые параметры и перегружает список объявлений
	function appendAllParams(d){
		if (d && d.update){
			$('#filter'+d.update.id+'>.filterParams').html(d.update.html); //обновляем код фильтра

			//если есть зависимые фильтры, то обновляем их, иначе просто обновляем форму
			if (anyDepend(d.update.id))
				needUpdateDepend = true;
			filterForm.submit();
		}
	}

	//обновляет зависимые фильтры и выполняет callback
	function updateDepend(callback){
		if (needUpdateDepend)
			$.post(updateDepAddr,filterForm.formSerialize(), function(d){ //запрашиваем зависимые параметры
				if (d && d.depend){
					for (var id in d.depend) {
						$('#filter'+id+'>.filterParams').html(d.depend[id]);
					}
				}
				needUpdateDepend = false;
				callback();
			},"json");
		else
			callback();
	}

	//событие обновления, когда отправляется форма на сервер
	function appendJson(d){
		if (d.data)
			content.html(d.data);

	//@todo сделать обновление адресной строки браузера
	}

	//настраиваем событие ajaxForm
	filterForm.submit(function(){
		updateDepend(function(){
			filterForm.ajaxSubmit({
				success:appendJson,
				dataType:'json'
			});
		});
		return false;
	});

	//кнопка "применить фильтр"
	var timer;
	submitBtn.click(function(){
		filterForm.submit();
		$(this).hide(200);
	});
	function showUpdButton(elem){
		clearTimeout(timer);
		submitBtn.stop(true,true);
		submitBtn.css({
			left:elem.position().left+elem.width()-50,
			top:elem.position().top+elem.height()/3
		}).show();
		timer = setTimeout(function(){
			submitBtn.hide(200)
		}, 3000);
	};
	filterForm.on('change','input',function(){
		if (anyDepend($(this).parents('.filter').data('id')))
			needUpdateDepend = true;
		showUpdButton($(this).parents('div.filter'));
	});

	//Кнопка отчистки фильтра
	$('.filterClear>a').click(function(){
		var filter = $(this).parents('.filter');
		filter.find('input[type=checkbox]:checked').removeAttr('checked');
		filter.find('.intSlider').slider("values",[
			filter.find('input.intSliderMin').val(),
			filter.find('input.intSliderMax').val()
			]);
		filterForm.submit();
		return false;
	});

	//действие кнопки "все фильтры"
	filterForm.on('click','.allParams',function(){
		$.post($(this).attr('href'),filterForm.formSerialize(), function(d){
			//параметры диалога
			var dlgparams = {
				width:735,
				modal:true,
				buttons:{
					"Применить":function(){
						$('#allParams').submit();//отправляем форму на сервер
						$(this).dialog('close').remove(); //закрываем форму
					}
				},
				closeText:"Закрыть без применения",
				title:"Выберите необходимые параметры"
			};

			//если элемент есть на стрнице - просто заполняем его, иначе создаем новый
			if ($('.dlg').size()>0){
				elem = $('.dlg').eq(0);
				elem.html(d).dialog(dlgparams);
			}
			else elem = $('<div class="dlg">').html(d).dialog(dlgparams);
			elem.find('#allParams').ajaxForm({
				dataType:'json',
				success:appendAllParams
			});
		});
		return false;
	});
})