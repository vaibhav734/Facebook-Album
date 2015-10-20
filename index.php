<?php //session_start();?>
<!DOCTYPE html>
<html>
	<head>
		<title>Facebook Album Challenge</title>
		

		<link rel="shortcut icon" type="image/jpg" href="libs/resources/img/favicon.jpg"/>
		<link rel="stylesheet" type="text/css" href="libs/resources/css/jquery.fancybox.css" />
		<link rel="stylesheet" type="text/css" href="libs/resources/css/bootstrap.css" />
		<link rel="stylesheet" type="text/css" href="libs/resources/css/style.css" />

		<script src="libs/resources/js/jquery-2.1.1.min.js"></script>
		<script src="libs/resources/js/spin.min.js"></script>
		<script src="libs/resources/js/jquery.fancybox.js" type="text/javascript" charset="utf-8"></script>
		<script src="libs/resources/js/bootstrap.min.js"></script>
		<script src="libs/resources/js/local.js"></script>
	</head>
	<body>

	<?php
	
		require_once( 'includes.php' );
		
		use Facebook\GraphObject;
		use Facebook\GraphSessionInfo;
		use Facebook\Entities\AccessToken;
		use Facebook\HttpClients\FacebookHttpable;
		use Facebook\HttpClients\FacebookCurl;
		use Facebook\HttpClients\FacebookCurlHttpClient;
		use Facebook\FacebookSession;
		use Facebook\FacebookRedirectLoginHelper;
		use Facebook\FacebookRequest;
		use Facebook\FacebookResponse;
		use Facebook\FacebookSDKException;
		use Facebook\FacebookRequestException;
		use Facebook\FacebookAuthorizationException;

		error_reporting(0);
		FacebookSession::setDefaultApplication( $fb_app_id, $fb_secret_id );

		// login helper with redirect_uri
		$helper = new FacebookRedirectLoginHelper( $fb_login_url );
		
		// see if a existing session exists
		if ( isset( $_SESSION ) && isset( $_SESSION['fb_token'] ) ) {
			// create new session from saved access_token
			$session = new FacebookSession( $_SESSION['fb_token'] );

			try {
				if ( !$session->validate() ) {
				  $session = null;
				}
			} catch ( Exception $e ) {
				// catch any exceptions
				$session = null;
			}
		}  
		 
		if ( !isset( $session ) || $session === null ) {
			try {
				$session = $helper->getSessionFromRedirect();
			} catch( FacebookRequestException $ex ) {
				print_r( $ex );
			} catch( Exception $ex ) {
				print_r( $ex );
			}
		}

		$google_session_token = "";

		// see if we have a session
		if ( isset( $session ) ) {

			require_once( 'libs/resize_image.php' );

			$_SESSION['fb_login_session'] = $session;
			$_SESSION['fb_token'] = $session->getToken();

			// create a session using saved token or the new one we generated at login
			$session = new FacebookSession( $session->getToken() );
			
			$_SESSION['session'] = $session;
			
			$request_user_details = new FacebookRequest( $session, 'GET', '/me?fields=id,name' );
			$response_user_details = $request_user_details->execute();
			$user_details = $response_user_details->getGraphObject()->asArray();
			
			$user_id = $user_details['id'];
			$user_name = $user_details['name'];
			
			
			if ( isset( $_SESSION['google_session_token'] ) ) {
				$google_session_token = $_SESSION['google_session_token'];
			}
?>

				<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
					<div class="container">
						<!-- Brand and toggle get grouped for better mobile display -->
						<div id="nav-menu" class="navbar-header">
							<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-menu">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
							<a class="navbar-brand" href="#" id="username">
								<img src="<?php echo 'https://graph.facebook.com/'.$user_id.'/picture';?>" id="user_photo" class="img-circle" />
								<span style="margin-left: 5px;"><?php echo $user_name;?></span>
							</a>
						</div>

						<!-- Collect the nav links, forms, and other content for toggling -->
						<div id="navbar-collapse-menu" class="collapse navbar-collapse menu-links">
							<ul class="nav navbar-nav pull-right">
								<li>
									<a href="#" id="download-all-albums" class="center">
										
											Download All
										
									</a>
								</li>
								<li>
									<a href="#" id="download-selected-albums" class="center">
								
											Download Selected
										
									</a>
								</li>
								<li>
									<a href="#" id="move_all" class="center">
										
											Move All
										
									</a>
								</li>
								<li>
									<a href="#" id="move-selected-albums" class="center">
										
											Move Selected
										
									</a>
								</li>
								
							<!--	<li>
									<a href="likes.php" id="download-all-albums" class="center">
										
											Likes
										
									</a>
																	
								</li>-->
								
								<li>
									<a href="<?php echo $helper->getLogoutUrl( $session, $fb_logout_url );?>" class="center">
										<span class="glyphicon glyphicon-log-out" aria-hidden="true" ></span>Logout
											
									
									</a>
									
									
								</li>
							</ul>
						</div>
					</div>
				</nav>

				<div class="container" id="main-div">
				
				
				<!-- Page Header -->
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Facebook Album
                    <small>Download Albums From Facebook</small>
                </h1>
            </div>
        </div>
        <!-- /.row -->
				
					<div class="row">
						<span id="loader" class="navbar-fixed-top"></span>

						<div class="modal fade" id="download-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">
											<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
										</button>
										<h4 class="modal-title" id="myModalLabel">Albums Report</h4>
									</div>
									<div class="modal-body" id="display-response">
										<!-- Response is displayed over here -->
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<!--<ul id="thumbnails">-->

						<?php

							// graph api request for user data
							$request_albums = new FacebookRequest( $session, 'GET', '/me/albums?fields=id,cover_photo,from,name' );
							$response_albums = $request_albums->execute();
							
							// get response
							$albums = $response_albums->getGraphObject()->asArray();
							
							if ( !empty( $albums ) ) {
								foreach ( $albums['data'] as $album ) {
									$album = (array) $album;
																				

									$request_album_photos = new FacebookRequest( $session,'GET', '/'.$album['id'].'/photos?fields=source' );
									$response_album_photos = $request_album_photos->execute();			
									$album_photos = $response_album_photos->getGraphObject()->asArray();
									
									$num1 = count($album_photos['data']) + 1;
									$num2 = count($album_photos['data']);
									$num3 = $num1 - $num2; 
									
									if ( !empty( $album_photos ) ) {
										
										foreach ( $album_photos['data'] as $album_photo ) {
											$album_photo = (array) $album_photo;

											foreach ($album as $key => $value ) 
											{ 
												//echo '</br></br>';
												//echo $value;
											}
				
												if ($album['name']== $value && $num3 ==1) {
												$album_cover_photo = $album_photo['source'];
												$album_resized_cover_photo = 'libs/resources/albums/covers/'.$album['id'].'_350X420.jpg';

												if ( !file_exists( $album_resized_cover_photo ) )
													smart_resize_image($album_cover_photo , null, 350 , 420 , false , $album_resized_cover_photo , false , false , 100 );
													

										?>
												<div class="col-sm-6 col-md-4">
													<div class="thumbnail no-border center">
														
														<a href="<?php echo $album_photo['source'];?>" class="fancybox" rel="<?php echo $album['id'];?>">
														  <img src="<?php echo $album_resized_cover_photo;?>" class="image-responsive img-rounded" alt="<?php echo $album['name'];?>" />
														</a>

														<div class="caption">
																<h4><?php echo $album['name'].' ('.count($album_photos['data']).')';?></h4>
															<button rel="<?php echo $album['id'].','.$album['name'];?>" class="single-download btn btn-default pull-left btn-lg" title="Download Album"style="background:#222222;">
																<span class="glyphicon glyphicon-download" aria-hidden="true"style="color:white;"></span>
															</button>

															<input type="checkbox" class="select-album" value="<?php echo $album['id'].','.$album['name'];?>" />

															<span rel="<?php echo $album['id'].','.$album['name'];?>" class="move-single-album btn btn-default btn-lg pull-right" title="Move to Google"style="background:#222222;">
																<span class="glyphicon glyphicon-share-alt" aria-hidden="true"style="color:white;"></span>
															</span>
														</div>
													
													</div>
												</div>
										<?php
											$num3++;
											} else {
										?>
											<a href="<?php echo $album_photo['source'];?>" class="fancybox" rel="<?php echo $album['id'];?>" style="display:none;"></a>
											
										<?php
											}//end of else
									
										 //}//end of inner foreach
										}//end of foreach
									}//end of if
								}
							}
						?>
						<!--</ul>-->
					</div>
					
					<hr>
					
					<!-- Footer -->
					<footer>
						<div class="row">
							<div class="col-lg-12">
								<p>Copyright &copy; Your Website 2014</p>
							</div>
						</div>
						<!-- /.row -->
					</footer>
					
					
				</div>

<?php
		} else {
			$perm = array( "scope" => "email,user_photos" );
?>
			<nav class="navbar navbar-inverse" role="navigation">
				<div class="container-fluid">
					<div class="navbar-header">
						<a class="navbar-brand" href="#">Facebook Album</a>
					</div>
				</div>
			</nav>

			<div id="login-div" class="row">
				<a id="login-link" class="btn btn-primary btn-lg" href="<?php echo $helper->getLoginUrl( $perm );?>">
					Facebook
				</a>
			</div>

<?php   } ?>

	</body>
</html>