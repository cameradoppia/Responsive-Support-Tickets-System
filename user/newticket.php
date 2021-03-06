<?php 

ini_set('session.auto_start', '0');
ini_set('session.save_path', '../php/config/session');
ini_set('session.hash_function', 'sha512');
ini_set('session.gc_maxlifetime', '1800');
ini_set('session.entropy_file', '/dev/urandom');
ini_set('session.entropy_length', '512');
ini_set('session.gc_probability', '20');
ini_set('session.gc_divisor', '100');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
session_name("RazorphynSupport");
if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
	ini_set('session.cookie_secure', '1');
}
if(isset($_COOKIE['RazorphynSupport']) && !is_string($_COOKIE['RazorphynSupport']) || !preg_match('/^[^[:^ascii:];,\s]{26,40}$/',$_COOKIE['RazorphynSupport'])){
	setcookie(session_name(),'invalid',time()-3600);
	header("location: ../index.php?e=invalid");
	exit();
}
session_start(); 


//Session Check
if(isset($_SESSION['time']) && time()-$_SESSION['time']<=1800)
	$_SESSION['time']=time();
else if(isset($_SESSION['id']) && !isset($_SESSION['time']) || isset($_SESSION['time']) && time()-$_SESSION['time']>1800){
	session_unset();
	session_destroy();
	header("location: ../index.php?e=expired");
	exit();
}
else if(isset($_SESSION['ip']) && $_SESSION['ip']!=retrive_ip()){
	session_unset();
	session_destroy();
	header("location: ../index.php?e=local");
	exit();
}
else if(!isset($_SESSION['status']) || $_SESSION['status']>2){
	 header("location: ../index.php");
	 exit();
}
include_once '../php/mobileESP.php';
$uagent_obj = new uagent_info();
$isMob=$uagent_obj->DetectMobileQuick();
if(is_file('../php/config/setting.txt')) $setting=file('../php/config/setting.txt',FILE_IGNORE_NEW_LINES);
$siteurl=dirname(dirname(curPageURL()));
$siteurl=explode('?',$siteurl);
$siteurl=$siteurl[0];
function curPageURL() {$pageURL= "//";if (isset($_SERVER["HTTPS"]) && $_SERVER["SERVER_PORT"] != "80") $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];else $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];return $pageURL;}
if(!isset($_SESSION['token']['act'])) $_SESSION['token']['act']=random_token(7);
function random_token($length){$valid_chars='abcdefghilmnopqrstuvzkjwxyABCDEFGHILMNOPQRSTUVZKJWXYZ';$random_string = "";$num_valid_chars = strlen($valid_chars);for($i=0;$i<$length;$i++){$random_pick=mt_rand(1, $num_valid_chars);$random_char = $valid_chars[$random_pick-1];$random_string .= $random_char;}return $random_string;}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta name="robots" content="noindex,nofollow">
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<title><?php if(isset($setting[0])) echo $setting[0];?> - New Ticket</title>
		<meta name="viewport" content="width=device-width">
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
		
		
		<!--[if lt IE 9]><script src="../js/html5shiv-printshiv.js"></script><![endif]-->
		<link rel="stylesheet" type="text/css" href="../min/?g=css_i&amp;5259487"/>
		<?php if($isMob) { ?>
			<link rel="stylesheet" type="text/css" href="../min/?g=css_m&amp;5259487"/>
		<?php } ?>
	</head>
	<body>
		<div class="container">
			<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
				<div class='container'>
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#header-nav-collapse">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							</button>
							<a class="navbar-brand" href='../index.php'><?php if(isset($setting[0])) echo $setting[0];?></a>
					</div>
		  
					<div class="collapse navbar-collapse" id="header-nav-collapse">
						<ul class="nav navbar-nav">
							<li><a href="index.php"><i class="glyphicon glyphicon-home"></i> Home</a></li>
							<li><a href="faq.php"><i class="glyphicon glyphicon-flag"></i> FAQs</a></li>
							<?php if(isset($_SESSION['name']) && isset($_SESSION['status']) && $_SESSION['status']<3){ ?>
								<li class="active dropdown" role='button'>
									<a id="drop1" class="dropdown-toggle" role='button' data-toggle="dropdown" href="#">
										<i class="glyphicon glyphicon-folder-close"></i> Tickets<b class="caret"></b>
									</a>
									<ul class="dropdown-menu" aria-labelledby="drop1" role="menu">
										<li role="presentation">
											<a href="index.php" tabindex="-1" role="menuitem"><i class="glyphicon glyphicon-th-list"></i> Tickets List</a>
										</li>
										<li class="active" role="presentation">
											<a href="newticket.php" tabindex="-1" role="menuitem"><i class="glyphicon glyphicon-folder-close"></i> New Ticket</a>
										</li>
										<li role="presentation">
											<a href="search.php" tabindex="-1" role="menuitem"><i class="glyphicon glyphicon-search"></i> Search Tickets</a>
										</li>
									</ul>
								</li>
								<li><a href="setting.php"><i class="glyphicon glyphicon-edit"></i> Account</a></li>
								<?php if(isset($_SESSION['status']) && $_SESSION['status']==2){ ?>
									<li class="dropdown" role='button'>
										<a id="drop1" class="dropdown-toggle" role='button' data-toggle="dropdown" href="#">
											<i class="glyphicon glyphicon-eye-open"></i> Administration<b class="caret"></b>
										</a>
										<ul class="dropdown-menu" aria-labelledby="drop1" role="menu">
											<li role="presentation">
												<a href="admin_setting.php" tabindex="-1" role="menuitem"><i class="glyphicon glyphicon-globe"></i> Site Managment</a>
											</li>
											<li>
												<a href="admin_users.php" tabindex="-1" role="menuitem"><i class="glyphicon glyphicon-user"></i> Users</a>
											</li>
											<li role="presentation">
												<a href="admin_departments.php" tabindex="-1" role="menuitem"><i class="glyphicon glyphicon-briefcase"></i> Deaprtments Managment</a>
											</li>
											<li role="presentation">
												<a href="admin_mail.php" tabindex="-1" role="menuitem"><i class="glyphicon glyphicon-envelope"></i> Mail Settings</a>
											</li>
											<li role="presentation">
												<a href="admin_payment.php" tabindex="-1" role="menuitem"><i class="glyphicon glyphicon-euro"></i> Payment Setting/List</a>
											</li>
											<li role="presentation">
												<a href="admin_faq.php" tabindex="-1" role="menuitem"><i class="glyphicon glyphicon-comment"></i> FAQs Managment</a>
											</li>
											<li role="presentation">
												<a href="admin_reported.php" tabindex="-1" role="menuitem"><i class="glyphicon glyphicon-exclamation-sign"></i> Reported Tickets</a>
											</li>
										</ul>
									</li>
								<?php }} if(isset($_SESSION['name'])){ ?>
									<li><a href='#' onclick='javascript:logout();return false;'><i class="glyphicon glyphicon-off"></i> Logout</a></li>
								<?php } ?>
						</ul>
					</div>
				</div>
			</nav>
			<div class='daddy'>
				<hr>
				<div class="jumbotron" >
					<h1 class='pagefun'>Create New Ticket</h1>
				</div>
				<hr>
				<img id='loading' src='../css/images/loader.gif' alt='Loading' title='Loading'/>
				<form style='display:none' id='createticket' method="POST" action="../php/function.php" target='hidden_upload' enctype="multipart/form-data">
					<input type='hidden' value='Dream' name='<?php echo $_SESSION['token']['act']; ?>' />
					<div class='row main'>
						<div class='sect login activesec'>
								<h3 class='sectname'>Ticket Information</h3>
								<div class='row form-group'>
									<div class='col-md-2'><label for='title'>Title</label></div>
									<div class='col-md-10'><input type="text" class='form-control'  name='title' id="title" placeholder="Title" required /></div>
								</div>
								<div class='row form-group'>
									<div class='col-md-2'><label for='deplist'>Departement</label></div>
									<div class='col-md-4' id='deplist'>

									</div>
									<div class='col-md-2'><label for='priority'>Priority</label></div>
									<div class='col-md-4'>
										<select class='form-control'  name='priority' id='priority'>
											<option value='0'>Low</option>
											<option value='1'>Medium</option>
											<option value='2'>High</option>
											<option value='3'>Urgent</option>
											<option value='4'>Critical</option>
										</select>
									</div>
								</div>
								<br/><br/>
								<h3 class='sectname'>Website Information</h3>
								<div class='row form-group'>
									<div class='col-md-2'><label for='wsurl'>URL</label></div>
									<div class='col-md-4'><input type="url" name='wsurl' class='form-control' id="wsurl" placeholder="Website URL"/></div>
								</div>
								<div class='row form-group'>
									<div class='col-md-2'><label for='contype'>Connection Type</label></div><div class='col-md-4'><select class='form-control'  name="contype" id="contype"><option selected="" value="0">--</option><option value="1">FTP</option><option value="2">FTPS</option><option value="3">SFTP</option><option value="4">SSH</option><option value="5">Other</option></select></div>
									</div>
								<div class='row form-group'>
									<div class='col-md-2'><label for='ftpus'>FTP Username</label></div>
									<div class='col-md-4'><input type="text" class='form-control'  name='ftpus' id="ftpus" placeholder="FTP Username"/></div>
									<div class='col-md-2'><label for='ftppass'>FTP Password</label></div>
									<div class='col-md-4'><input type="password" class='form-control'  name='ftppass' id="ftppass" placeholder="FTP Password" autocomplete="off"/></div>
								</div>
								<br/><br/>
								<h3 class='sectname'>Message</h3>
								<div class='row form-group'>
										<div class='col-md-12 nwm'><textarea name='message' id='message' rows='5' placeholder='Your Message'> </textarea></div>
								</div>
								<br/>
								<?php if(isset($setting[5]) && $setting[5]==1){ ?>
								<h3 class='sectname'>Attachments</h3>
								<span class='attlist'></span>
								<p>To select multiple files: press ctrl+click on the chosen file</p>
								<div class='row form-group'>
									<div class='col-xs-4'><input id='fielduploadinput' type="file" name="filename[]" multiple /></div>
									<div class='col-xs-offset-1 col-xs-3'><span id='resetfile' class='btn btn-danger'>Reset</span></div>
								</div>
								<?php } ?>
								<br/><br/>
								<input type="submit" class="btn btn-success" name='createtk' value='Create New Ticket' id='createtk'/>
						</div>
					</div>
				</form>
				<hr>
			</div>
		</div>
		<iframe style='display:none' name='hidden_upload' id='hidden_upload' src="about:blank" ></iframe>
		<script type="text/javascript"  src="../min/?g=js_i&amp;5259487"></script>
		<?php if(!$isMob) { ?>
			<script type="text/javascript"  src="../lib/ckeditor/ckeditor.js"></script>
		<?php }else { ?>
			<script type="text/javascript"  src="../min/?g=js_m&amp;5259487"></script>
		<?php } ?>
		<script>
			 $(document).ready(function() {
				$.ajax({
					type: "POST",
					url: "../php/function.php",
					data: {<?php echo $_SESSION['token']['act']; ?>: "retrive_depart",sect: "new"},
					dataType: "json",
					success: function (a) {
						if("ret" == a.response){
							$("#loading").remove();
							<?php if (!$isMob) { ?> 
								CKEDITOR.replace('message')
							<?php } else { ?> 
							$("#message").wysihtml5()
							<?php } ?> 
							$("#deplist").html("<select class='form-control'  name='dep' id='dep'>" + a.information + "</select>");
						}
						else if("empty" == a.response){
							 $("#loading").remove(), $("#createticket").html("<p>Sorry, you cannot open a new ticket because: " + a[1] + "</p>");
						}
						else if(a[0]=='sessionerror'){
							switch(a[1]){
								case 0:
									window.location.replace("<?php echo $siteurl.'?e=invalid'; ?>");
									break;
								case 1:
									window.location.replace("<?php echo $siteurl.'?e=expired'; ?>");
									break;
								case 2:
									window.location.replace("<?php echo $siteurl.'?e=local'; ?>");
									break;
								case 3:
									window.location.replace("<?php echo $siteurl.'?e=token'; ?>");
									break;
							}
						}
						else
							$("#loading").remove(), $("#createticket").html("<h4>Error: " + a[0] + " <br/>Please contact the administrator.</h4>");
						$("#createticket").slideToggle(1500)
					}
				}).fail(function (b, a) {noty({text: a,type: "error",timeout: 9E3})});	
				
				$("#createticket").submit(function(){
					<?php if(!$isMob){ ?>
						if(""==CKEDITOR.instances.message.getData().replace(/\s+/g,"")||""==$("#title").val().replace(/\s+/g,""))
					<?php }else { ?>
						if($("#message").val().replace(/\s+/g,'') == '' || $('#title').val().replace(/\s+/g,'')=='')
					<?php } ?>
							return noty({text:"Empty Fields. PLeasy check the title and the message",type:"error",timeout:9E3}),!1;
					$(".main").nimbleLoader("show",{position:"fixed",loaderClass:"loading_bar_body",hasBackground:!0,zIndex:999,backgroundColor:"#fff",backgroundOpacity:0.9});
					return!0
				});

				$(document).on('click','#resetfile',function(){
					$('#fielduploadinput').wrap('<form>').closest('form').get(0).reset();
					$('#fielduploadinput').unwrap();
				});
			});

			function created(a,b){
				if(a==0)
					window.location = "<?php echo dirname(curPageURL()); ?>";
				else if(a==1)
					window.location = b;
				else if(a==2){
					$('#createticket').replaceWith(b);
				}
			}
			function logout(){$.ajax({type:"POST",url:"../php/function.php",data:{<?php echo $_SESSION['token']['act']; ?>:"logout"},dataType:"json",success:function(a){"logout"==a[0]?window.location.reload():alert(a[0])}}).fail(function(a,b){noty({text:b,type:"error",timeout:9E3})})};
			
		</script>
	</body>
</html>