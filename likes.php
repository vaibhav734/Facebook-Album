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

	</head>
	<body>
	
	
<?php
session_start();
/**
* Description : This application will helps you to get the all public details of user
* first we should include all required files provided by Facebook SDK
*/

	require_once('libs/Facebook/GraphObject.php');
	require_once('libs/Facebook/GraphSessionInfo.php');
	require_once('libs/Facebook/Entities/AccessToken.php');
	require_once('libs/Facebook/HttpClients/FacebookHttpable.php');
	require_once('libs/Facebook/HttpClients/FacebookCurl.php');
	require_once('libs/Facebook/HttpClients/FacebookCurlHttpClient.php');
	require_once('libs/Facebook/FacebookSession.php' );
	require_once('libs/Facebook/FacebookRedirectLoginHelper.php' );
	require_once('libs/Facebook/FacebookRequest.php' );
	require_once('libs/Facebook/FacebookResponse.php' );
	require_once('libs/Facebook/FacebookSDKException.php' );
	require_once('libs/Facebook/FacebookRequestException.php' );
	require_once('libs/Facebook/FacebookAuthorizationException.php' );
	require_once('libs/Facebook/GraphUser.php' );

use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
Use Facebook\FacebookSession;
Use Facebook\FacebookRedirectLoginHelper;
error_reporting(0);
/**
* here you have to provide your application ID and SECRET
* which are given by facebook after creating the application
*/

//curl_setopt(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
FacebookSession::setDefaultApplication('482081285307797', 'a3af071bc153c12ef3068510de339754');
$helper = new FacebookRedirectLoginHelper('http://localhost/demo1/index.php');

		//$session = $helper->getSessionFromRedirect();

		if ( isset( $_SESSION ) && isset( $_SESSION['fb_token'] ) ) {
			// create new session from saved access_token
			$session = new FacebookSession( $_SESSION['fb_token'] );

			try {
				facebook::$CURL_OPTS[CURLOPT_CAINFO] = 'libs/Facebook/HttpClients/fb_ca_chain_bundle.crt';
				
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
		
		 
		
echo "<title>FBApp-Details</title>";
/**
* Code to get the public details of user
*/
	if (isset( $session ) && $session != NULL) {
		try {
			$_SESSION['fb_token'] = $session->getToken();
			
			//get user details id name and email
			$user_profile = new FacebookRequest( $session, 'GET', '/me?fields=id,name,email' );
			$response_user = $user_profile->execute();
			$info = $response_user->getGraphObject()->asArray();
			echo "</br>";
			echo "Name : " . $info ['name']. "</br>";
			echo "Email : " . $info ['email'] . "</br>";
		} catch (FacebookRequestException $e) {
			echo "Exception occured, code: " . $e->getCode();
			echo " with message: " . $e->getMessage();
		}
		
		
		try{
			$request = new FacebookRequest( $session,'GET','/me/albums?fields=id,cover_photo,name');
			$response = $request->execute();
			$albums = $response->getGraphObject()->asArray();
							
				if ( !empty( $albums ) ) {
					foreach ( $albums['data'] as $album ) {
						$album = (array) $album;
						$request_album_photos = new FacebookRequest( $session,'GET', '/'.$album['id'].'/photos?fields=likes.summary(1),source,id' );
						$response_album_photos = $request_album_photos->execute();			
						$album_photos = $response_album_photos->getGraphObject()->asArray();
									
						if ( !empty( $album_photos ) ) {
							
							foreach ( $album_photos['data'] as $album_photo ) {
								$album_photo = (array) $album_photo;
								
								foreach ( $album_photo['likes'] as $alb ) {
									$pho = (array) $alb;
									
									foreach ( $pho as $key => $alb1 ) {
										$pho1 = (array) $alb1;
																								
										if($key == 'total_count'){
											$temp = $pho1[0];
									
								if ( $temp>=1) {
									
							
								?>
								
								<div class="col-sm-6 col-md-41">
									<div class="thumbnail no-border center1">
									<a href="<?php echo $album_photo['source'];?>" class="fancybox" rel="<?php echo $album['id'];?>" title="<?php echo $temp ?>">
									<img src="<?php echo $album_photo['source'];?>" title="hello" style="width:350px;height:400px;" class="image-responsive img-rounded" alt="<?php echo $album['name'];?>"/>
									</a>
									<hr> 
									</div>
								</div>
								<?php
								$num3++;
								}
								}
								
								}
									
							}
							
								}
						
					
					}
					
				}
			}
				

			
		}catch(FacebookRequestException $e){
			echo "Exception occured, code: " . $e->getCode();
			echo " with message: " . $e->getMessage();
		}
		
		
	}
	else {
		
	echo '<a href="' . $helper->getLoginUrl(array(
	'scope' => 'email,user_photos')) . '"><font size="6"><i><strong>
	Get Details</strong></i></font></a>';
	}
?>
<script type="text/javascript" charset="utf-8">
	
	$('.fancybox').fancybox({
					padding:0,
					autoPlay: true
					
				});
		
</script>

	</body>
</html>