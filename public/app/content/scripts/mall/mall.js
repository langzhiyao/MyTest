$(function(){
	// 显示更多
	$('.icon-gengduo').on('click', function(event){
		if(event.target.dataset.select == 'false'){
			$('.sub-nav').show();
			event.target.dataset.select = 'true';
		}else {
			$('.sub-nav').hide();
			event.target.dataset.select = 'false';
		}
	})

	var tabs = $('.tabs_mall li i');
	var content = $('.content_wrap');

	for(var i = 0; i < tabs.length; i++){
		tabs[i].index = i;
		tabs[i].onclick = function(event){
			for(var i = 0; i < tabs.length; i++){
				tabs[i].className = '';
				content[i].className = 'none';
			}
			this.className = 'action';
			content[this.index].className = '';
		}
	}

})
