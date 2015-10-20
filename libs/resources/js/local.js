$( document ).ready(function() {
				var opts = {
				  lines: 11 // The number of lines to draw
				, length: 0 // The length of each line
				, width: 8 // The line thickness
				, radius: 42 // The radius of the inner circle
				, scale: 1 // Scales overall size of the spinner
				, corners: 1 // Corner roundness (0..1)
				, color: '#000' // #rgb or #rrggbb or array of colors
				, opacity: 0.25 // Opacity of the lines
				, rotate: 40 // The rotation offset
				, direction: 1 // 1: clockwise, -1: counterclockwise
				, speed: 1.3 // Rounds per second
				, trail: 84 // Afterglow percentage
				, fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
				, zIndex: 2e9 // The z-index (defaults to 2000000000)
				, className: 'spinner' // The CSS class to assign to the spinner
				, top: '50%' // Top position relative to parent
				, left: '50%' // Left position relative to parent
				, shadow: true // Whether to render a shadow
				, hwaccel: false // Whether to use hardware acceleration
				, position: 'absolute' // Element positioning
				};
				var target = document.getElementById('loader');

				$('.fancybox').fancybox({
					padding:0,
					autoPlay: true
					
				});
				
				
				$(document).ready(function(){
					$('#btn').click(function(){
					$.get('page.html').success(function(data)
					 {
						 $('#div_content').html(data);
					 });
					 });
					 });
				

				function append_download_link(url) {
					var spinner = new Spinner(opts).spin(target);

					$.ajax({
						url:url,
						success:function(result){
							$("#display-response").html(result);
							spinner.stop();
							$("#download-modal").modal({
								show: true
							});
						}
					});
				}

				function get_all_selected_albums() {
					var selected_albums;
					var i = 0;
					$(".select-album").each(function () {
						if ($(this).is(":checked")) {
							if (!selected_albums) {
								selected_albums = $(this).val();
							} else {
								selected_albums = selected_albums + "/" + $(this).val();
							}
						}
					});

					return selected_albums;
				}

				$(".single-download").on("click", function() {
					var rel = $(this).attr("rel");
					var album = rel.split(",");

					append_download_link("download_album.php?zip=1&single_album="+album[0]+","+album[1]);
				});

				$("#download-selected-albums").on("click", function() {
					var selected_albums = get_all_selected_albums();
					append_download_link("download_album.php?zip=1&selected_albums="+selected_albums);
				});

				$("#download-all-albums").on("click", function() {
					append_download_link("download_album.php?zip=1&all_albums=all_albums");
				});


				function getParameterByName(name) {
					name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
					var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
						results = regex.exec(location.search);
					return results === null ? "null" : decodeURIComponent(results[1].replace(/\+/g, " "));
				}

				function display_message( response ) {
					if ( response == 1 ) {
						$("#display-response").html('<div class="alert alert-success" role="alert">Album(s) is successfully moved to Picasa</div>');
						$("#download-modal").modal({
							show: true
						});
					} else if ( response == 0 ) {
						console.log(response);
						$("#display-response").html('<div class="alert alert-danger" role="alert">Due to some reasons album(s) is not moves to Picasa</div>');
						$("#download-modal").modal({
							show: true
						});
					}
				}

				get_params();

				function get_params() {
					var response = getParameterByName('response');
					display_message(response);
				}
				

				var google_session_token = '<?php echo $google_session_token;?>';

				function move_to_picasa(param1, param2) {
					if (google_session_token) {
						var spinner = new Spinner(opts).spin(target);

						$.ajax({
							url:"download_album.php?ajax=1&"+param1+"="+param2,
							success:function(result){
								spinner.stop();
								display_message(result);
							}
						});
					} else {
						window.location.href = "libs/google_login.php?"+param1+"="+param2;
					}
				}

				$(".move-single-album").on("click", function() {
					var single_album = $(this).attr("rel");
					move_to_picasa("single_album", single_album);
				});

				$("#move-selected-albums").on("click", function() {
					var selected_albums = get_all_selected_albums();
					move_to_picasa("selected_albums", selected_albums);
				});

				$("#move_all").on("click", function() {
					move_to_picasa("all_albums", "all_albums");
				});
			});