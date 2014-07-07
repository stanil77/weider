$(function() {
	function select_all(el) {
        if (typeof window.getSelection != "undefined" && typeof document.createRange != "undefined") {
            var range = document.createRange();
            range.selectNodeContents(el);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        } else if (typeof document.selection != "undefined" && typeof document.body.createTextRange != "undefined") {
            var textRange = document.body.createTextRange();
            textRange.moveToElementText(el);
            textRange.select();
        }
    }
	

	function reloadPage(currentURL) {
		window.location.href = currentURL;
	}

	$(document).ready(function(){ //doc ready!
		//console.log(window.location.pathname);
		//console.log(window.location.hash);
		//console.log(window.location.href);
		
		var alerts = $('<div id="alerts"><span class="fa fa-times closeme"></span><span class="wait fa fa-cog fa-spin"></span><span id="alertmsg"></span></div>');
		$('body').append(alerts);
		
		var alertTimeout;
		
		function showalert(msg, f, undo){
			clearTimeout(alertTimeout);
			var $al = $('#alerts');
			if (msg == '')
				msg = '...';
			$('#alertmsg').text(msg);
			$al.fadeIn('fast');
			if (typeof f == "function") {
				var alertButtons = $('<div class="alertButtons"></div>');
				var accept = $('<span class="accept fa fa-check"></span>');
				var cancel = $('<span class="cancel fa fa-times"></span>');
				$('#alertmsg').append(alertButtons);
				alertButtons.append(accept);
				if (undo){
					alertButtons.append(cancel);
				}
				accept.click(function(){
					f();
				})
				cancel.click(function(){
					hidealert();
				})
			} else {
				alertTimeout = setTimeout(function(){hidealert()},1500)
			}
		}
			
		function hidealert(){
			$('#alerts').fadeOut('fast',function(){
				$('#alertmsg').html('');
			});
		}
		
		$('#alerts .closeme').click(function(){
			hidealert();
		})
		
		
		var currentURL = window.location.href;
		
		currentURL = currentURL.replace(window.location.hash,'');
		
		
		var $mySlides = $(".slides");
		$mySlides.each(function(){
		$(this).sortable({
			placeholder: "ui-state-highlight",
			axis: "y",
			cursor: "move",
			update: function() {
				var order = $(this).sortable("serialize") ;
				//console.log(order)
				$.post(ajaxUrl+"&action=updateSlidesPosition", order);
				}
			});
		})
		$mySlides.hover(function() {
			$(this).css("cursor","move");
			},
			function() {
			$(this).css("cursor","auto");
		});
		function activationSetup() {
			$('.activationForm').each(function(){
				$(this).submit(function(e){
					e.preventDefault();
					var action = $(this).attr('id');
					var message = $('.message', this).text();
					showalert(message,function(){
						$.post(ajaxUrl+"&action="+action, function(data){
							console.log(data);
							showalert(data, function(){reloadPage(currentURL)});		
							//showalert(data);		
						}) ;
					}, true);
				})
			})
		}

		activationSetup();
		
		$('#showAct').click(function(e){
			e.preventDefault();
			$('table.activations').fadeToggle();
		})
		
		// permissions
		$('#accessEdit input').each(function(){
			$(this).change(function(){
				var data = $('#accessEdit').serialize();
				$.post(ajaxUrl+"&action=editPermissions&"+data, function(data){
					//alert(data);
					reloadPage(currentURL);
				});
			})
		})
		
		// update slider Configuration
		$('#sliders_config').submit(function(e){
			e.preventDefault();
			var data = $(this).serializeArray();
			console.log(data);
			$.post(ajaxUrl+"&action=updateConfiguration", data, function(data){
				showalert(data);
			});
		})
		
		//change status
		$('.changeStatus').each(function(){
			$(this).click(function(e){
				e.preventDefault();
				var clicked = $(this);
				var slideId = clicked.attr('data-slide-id');
				$.post(ajaxUrl+"&action=changeStatus&id_slide="+slideId, function(data){
					var response = jQuery.parseJSON(data);
					if (response.success == 1) {
						$('i.fa',clicked).toggleClass('fa-times').toggleClass('fa-check');
					}
					showalert(response.message);
				});
			})
		})
		
		// update DB
		$('#updateDb').click(function(e){
			e.preventDefault();
			$.post(ajaxUrl+"&action=updateDB", function(data){
				showalert(data, function(){reloadPage(currentURL);});
				
			});
		})
		// update Module
		$('#moduleUpdate').submit(function(e){
			e.preventDefault();
			$.post(ajaxUrl+"&action=updateModule", function(data){
				showalert(data, function(){reloadPage(currentURL);});
			});
		})
		
		if ($('.updateFakeMessage').length > 0) {
			var updates = parseInt($('#box-update-modules .value').text());
			$('#box-update-modules .value').text(updates+1);
		}
		
		/** allow only numbers **/
		
		$(".catnumber").keydown(function (e) {
			console.log(e.keyCode);
			if ((e.keyCode > 47 && e.keyCode < 58) //standard nums
				|| (e.keyCode > 95 && e.keyCode < 106) //block nums
				|| e.keyCode == 8 //canc
				|| e.keyCode == 46 //del
				|| (e.keyCode >36 && e.keyCode < 41)) //arrows
				return true;
			return false;
		})
		
		//select hook code with a single click
		$('.hookCode').each(function(){
			$(this).click(function(){
				select_all($(this)[0]);
			})
		})
		
		var Index, CurrentVal;
		var $catTree = $('.catTree');
		$catTree.append('<i class="smallClose fa fa-times"/>')
		var overlay = $('#overlayer');
		$('.catnumber').each(function(i){
			$(this).click(function(){
				Index = i;
				CurrentVal = $(this).val();
				overlay.append($catTree);
				$('li i', $catTree).removeClass('fa-check-circle-o').addClass('fa-circle-o');
				$('li[data-cat="'+CurrentVal+'"] i', $catTree).removeClass('fa-circle-o').addClass('fa-check-circle-o');
				$catTree.addClass('processed');
				
				overlay.fadeIn();
			})
		})
		
		$('.closeme', $catTree).click(function(){
			overlay.fadeOut();
			$('.catnumber').eq(Index).val('');
		});
		
		$('.smallClose', $catTree).click(function(){
			overlay.fadeOut();	
		});
		$('li', $catTree).click(function(){
			overlay.fadeOut();
			$('.catnumber').eq(Index).val($(this).attr('data-cat'));
		})
		
		//animation configs
		var cont = $('.slideChooserCont');
		var conf = $('.position');
		var chooseButtons = $('.slideChoose');
		conf.not('.open').css({
			opacity:0
		})
		var fixsize = $('.fixsize')
		chooseButtons.each(function(){
			$(this).click(function(e){
				e.preventDefault();
				var height = fixsize.height();
				chooseButtons.removeClass('active');
				$(this).addClass('active');
				fixsize.css('height',height+'px');
				var Target = $(this).attr('href');
				conf.not(Target).stop(true,true).animate({
					opacity : 0
				}, 300,function(){
					$(this).hide();
					$(Target).show().stop(true,true).animate({
						opacity : 1
					}, 300, function(){
						fixsize.css('height',$(Target).height());
					});
				});
				
			})
		})
		
		/*$('#imgchooser img.preview').each(function(){
			if ($(this).is(":visible")) {
				$(this).imgAreaSelect({
					handles: true,
					onSelectEnd: function(img, selection){
						console.log(img);
						console.log(selection);
					}
				});
				return false;
			}
		})*/
		
				
	}) // end doc ready
	
	
}(jQuery));
