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

if(!preg_match('/^[0-9]{1,15}$/',$_GET['id']))
	header("location: index.php");

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
if(!isset($_SESSION['token']['act'])) $_SESSION['token']['act']=random_token(7);
function random_token($length){$valid_chars='abcdefghilmnopqrstuvzkjwxyABCDEFGHILMNOPQRSTUVZKJWXYZ';$random_string = "";$num_valid_chars = strlen($valid_chars);for($i=0;$i<$length;$i++){$random_pick=mt_rand(1, $num_valid_chars);$random_char = $valid_chars[$random_pick-1];$random_string .= $random_char;}return $random_string;}

include_once '../php/config/database.php';

if(is_file('../php/config/setting.txt')) $setting=file('../php/config/setting.txt',FILE_IGNORE_NEW_LINES);


	include_once '../php/mobileESP.php';
	$uagent_obj = new uagent_info();
	$isMob=$uagent_obj->DetectMobileQuick();
	try{
		$DBH = new PDO("mysql:host=$Hostname;dbname=$DatabaseName", $Username, $Password);  
		$DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		if($_SESSION['status']==2 || $_SESSION['status']==1){
			$query = "SELECT 
							a.id,
							a.ref_id,
							a.title,
							a.user_id,
							a.operator_id,
							a.ticket_status,
							a.department_id,
							a.website,
							a.contype,
							a.ftp_user,
							a.ftp_password,
							b.rate,
							b.note,
							c.reason
						FROM ".$SupportTicketsTable." a
						LEFT JOIN ".$SupportRateTable." b
							ON b.id=a.id
						LEFT JOIN ".$SupportFlagTable." c
							ON (c.tk_id=a.id AND c.usr_id='".$_SESSION['id']."')
						WHERE a.id=?  LIMIT 1";
		}
		else{
			$query = "SELECT 
							a.id,
							a.ref_id,
							a.title,
							a.user_id,
							a.operator_id,
							a.ticket_status,
							a.department_id,
							a.website,
							a.contype,
							a.ftp_user,
							a.ftp_password,
							b.rate,
							b.note,
							c.reason
						FROM ".$SupportTicketsTable." a
						LEFT JOIN ".$SupportRateTable." b
							ON b.id=a.id
						LEFT JOIN ".$SupportFlagTable." c
							ON (c.tk_id=a.id AND c.usr_id=".$_SESSION['id'].")
						WHERE a.id=? AND a.user_id=".$_SESSION['id']." LIMIT 1";
		}
		$STH = $DBH->prepare($query);
		$STH->bindParam(1,$_GET['id'],PDO::PARAM_INT);
		$STH->execute();
			$STH->setFetchMode(PDO::FETCH_ASSOC);
			$a = $STH->fetch();
			if(!empty($a)){
				do{
					$tkid=$a['id'];
					$refid=$a['ref_id'];
					$title=htmlspecialchars($a['title'],ENT_QUOTES,'UTF-8');
					$usrid=$a['user_id'];
					$opid=$a['operator_id'];
					$stat=$a['ticket_status'];
					$departmentid=$a['department_id'];
					$cweb=htmlspecialchars(mb_convert_encoding($a['website'], "UTF-8", "UTF-8"),ENT_QUOTES,'UTF-8');
					$connection=$a['contype'];
					$usercred=htmlspecialchars(mb_convert_encoding($a['ftp_user'], "UTF-8", "UTF-8"),ENT_QUOTES,'UTF-8');
					$conpass=$a['ftp_password'];
					$rate=$a['rate'];
					$note=htmlspecialchars(mb_convert_encoding($a['note'], "UTF-8", "UTF-8"),ENT_QUOTES,'UTF-8');
					$reason=htmlspecialchars($a['reason'],ENT_QUOTES,'UTF-8');
					$_SESSION['tickets'][$_GET['id']]=array('id'=>$tkid,'usr_id'=>$usrid,'op_id'=>$opid,'status'=>$stat,'ref_id'=>$refid);
				}while ($a = $STH->fetch());
				unset($a);
				$rate=($rate!=NULL)? $rate:'';
				if($conpass!='' && $conpass!=null){
					$crypttable=array('X'=>'a','k'=>'b','Z'=>'c',2=>'d','d'=>'e',6=>'f','o'=>'g','R'=>'h',3=>'i','M'=>'j','s'=>'k','j'=>'l',8=>'m','i'=>'n','L'=>'o','W'=>'p',0=>'q',9=>'r','G'=>'s','C'=>'t','t'=>'u',4=>'v',7=>'w','U'=>'x','p'=>'y','F'=>'z','q'=>0,'a'=>1,'H'=>2,'e'=>3,'N'=>4,1=>5,5=>6,'B'=>7,'v'=>8,'y'=>9,'K'=>'A','Q'=>'B','x'=>'C','u'=>'D','f'=>'E','T'=>'F','c'=>'G','w'=>'H','D'=>'I','b'=>'J','z'=>'K','V'=>'L','Y'=>'M','A'=>'N','n'=>'O','r'=>'P','O'=>'Q','g'=>'R','E'=>'S','I'=>'T','J'=>'U','P'=>'V','m'=>'W','S'=>'X','h'=>'Y','l'=>'Z');

					$conpass=str_split($conpass);
					$c=count($conpass);
					for($i=0;$i<$c;$i++){
						if(array_key_exists($conpass[$i],$crypttable))
							$conpass[$i]=$crypttable[$crypttable[$conpass[$i]]];
					}
					$conpass=htmlspecialchars(mb_convert_encoding(implode('',$conpass), "UTF-8", "UTF-8"),ENT_QUOTES,'UTF-8');
				}
				$query = "SELECT 
								(SELECT COUNT(*) FROM ".$SupportMessagesTable." WHERE ticket_id=?) as qta,
								a.id,
								b.name,
								a.message,
								a.created_time,
								a.attachment
							FROM ".$SupportMessagesTable." a
							LEFT JOIN ".$SupportUserTable." b
								ON b.id=a.user_id
							WHERE a.ticket_id=? ORDER BY a.created_time DESC LIMIT 10";
				$STH = $DBH->prepare($query);
				$STH->bindParam(1,$_GET['id'],PDO::PARAM_INT);
				$STH->bindParam(2,$_GET['id'],PDO::PARAM_INT);
				$STH->execute();
				$STH->setFetchMode(PDO::FETCH_ASSOC);
				$a = $STH->fetch();
				$totnummsg=$a['qta'];
				if(!empty($a)){
					$list=array();
					$messageid=array();
					$count=0;
					do{
						$list[$a['id']]=array(0=>htmlspecialchars($a['name'],ENT_QUOTES,'UTF-8'),1=>$a['message'],2=>$a['created_time'],3=>($totnummsg-$count));
						if($a['attachment']==1)
							$messageid[]=$a['id'];
						$count++;
					}while ($a = $STH->fetch());
					unset($a);

					if(count($messageid)>0){
						$messageid=implode(',',$messageid);
						$query = "SELECT `id`,`uploader`,`name`,`message_id` FROM ".$SupportUploadTable." WHERE message_id IN (".$messageid.")";
						$STH = $DBH->prepare($query);
						$STH->execute();
						$STH->setFetchMode(PDO::FETCH_ASSOC);
						$a = $STH->fetch();
						if(!empty($a)){
							do{
								if($_SESSION['id']==$a['uploader'])
									$list[$a['message_id']][]=' <form class="download_form" method="POST" action="../php/function.php" target="hidden_upload" enctype="multipart/form-data"><input type="hidden" name="ticket_id" value="'.$_GET['id'].'"/><input type="hidden" name="file_download" value="'.$a['id'].'"/><input type="submit" class="btn btn-link download" value="'.htmlspecialchars($a['name'],ENT_QUOTES,'UTF-8').'"> &nbsp;&nbsp; <i class="glyphicon glyphicon-remove-sign remfile" title="Delete File" alt="Delete File"></i></form>';
								else
									$list[$a['message_id']][]=' <form class="download_form" method="POST" action="../php/function.php" target="hidden_upload" enctype="multipart/form-data"><input type="hidden" name="ticket_id" value="'.$_GET['id'].'"/><input type="hidden" name="file_download" value="'.$a['id'].'"/><input type="submit" class="btn btn-link download" value="'.htmlspecialchars($a['name'],ENT_QUOTES,'UTF-8').'"></form>';
							}while ($a = $STH->fetch());
						}
						unset($a);
					}
					$list=array_values($list);
				}
				else{
					header("location: index.php");
				}
			}
			else{
				header("location: index.php");
			}
	}
	catch(PDOException $e){  
		file_put_contents('PDOErrors', "File: ".$e->getFile().' on line '.$e->getLine()."\nError: ".$e->getMessage()."\n", FILE_APPEND);
		$error='We are sorry, but an error has occurred, please contact the administrator if it persist';
	}
	$DBH=null;
										

function retrive_depa_names($Hostname, $Username, $Password, $DatabaseName, $SupportDepaTable,$except){
	if(isset($_SESSION['status'])){
		try{
			$DBH = new PDO("mysql:host=$Hostname;dbname=$DatabaseName", $Username, $Password);  
			$DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
			$query = "SELECT `id`,`department_name`,`free` FROM ".$SupportDepaTable." WHERE id=? OR active='1'";
			$STH = $DBH->prepare($query);
			$STH->bindParam(1,$except,PDO::PARAM_INT);
			$STH->execute();
			$STH->setFetchMode(PDO::FETCH_ASSOC);
			$b=array("<option disabled>Free</option>");
			$a = $STH->fetch();
			if(!empty($a)){
				$pay=array("<option disabled>Premium</option>");
				do{
					if($a['free']==1)
						$b[]="<option value='".$a['id']."'>".htmlspecialchars($a['department_name'],ENT_QUOTES,'UTF-8')."</option>";
					else
						$pay[]="<option value='".$a['id']."'>".htmlspecialchars($a['department_name'],ENT_QUOTES,'UTF-8')."</option>";
				}while ($a = $STH->fetch());
				if(count($pay)>1)
					$b=array_merge($b,$pay);
				else{
					unset($b[0]);
				}
				return implode(' ',$b);
			}
			else
				return false;
		}
		catch(PDOException $e){  
			file_put_contents('PDOErrors', "File: ".$e->getFile().' on line '.$e->getLine()."\nError: ".$e->getMessage()."\n", FILE_APPEND);
			return json_encode(array(0=>"Can't retrieve Departments"));
		}
	}
}

function retrive_depa_operators($Hostname, $Username, $Password, $DatabaseName, $SupportUserTable,$SupportUserPerDepaTable,$departmentid,$exop){
	if(isset($_SESSION['status'])){
		try{
			$DBH = new PDO("mysql:host=$Hostname;dbname=$DatabaseName", $Username, $Password);  
			$DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$query="SELECT 
						id,
						name,
						status 
					FROM (
							(SELECT a.id,a.name,a.status  FROM ".$SupportUserTable." a WHERE  a.status='2' AND a.id!='".$_SESSION['id']."' AND a.id!='".$exop."')
								UNION 
							(SELECT a.id,a.name,a.status  FROM  ".$SupportUserTable." a LEFT JOIN  ".$SupportUserPerDepaTable." b ON a.id=b.user_id  WHERE b.department_id=? AND a.id!='".$_SESSION['id']."')
						) 
					AS  tab ORDER BY tab.status ASC, tab.name ASC";
			$STH = $DBH->prepare($query);
			$STH->bindParam(1,$departmentid,PDO::PARAM_INT);
			$STH->execute();
			$STH->setFetchMode(PDO::FETCH_ASSOC);
			$b=array();
			$a = $STH->fetch();
			if(!empty($a)){
				do{
					$b[$a['id']]=htmlspecialchars($a['name'],ENT_QUOTES,'UTF-8');
				}while ($a = $STH->fetch());
			}
			$DBH=null;
			return json_encode($b);
		}
		catch(PDOException $e){  
			file_put_contents('PDOErrors', "File: ".$e->getFile().' on line '.$e->getLine()."\nError: ".$e->getMessage()."\n", FILE_APPEND);
			$DBH=null;
			return json_encode(array(0=>"Can't retrieve Operators"));
		}
	}
}

$siteurl=dirname(dirname(curPageURL()));
$siteurl=explode('?',$siteurl);
$siteurl=$siteurl[0];
function curPageURL() {$pageURL= "//";if (isset($_SERVER["HTTPS"]) && $_SERVER["SERVER_PORT"] != "80") $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];else $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];return $pageURL;}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta name="robots" content="noindex,nofollow">
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<title><?php if(isset($setting[0])) echo $setting[0];?> - View Ticket</title>
		<meta name="viewport" content="width=device-width">
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
		
		<!--[if lt IE 9]><script src="js/html5shiv-printshiv.js"></script><![endif]-->
		<link rel="stylesheet" type="text/css" href="../min/?g=css_i&amp;5259487"/>
		<link rel="stylesheet" type="text/css" href="../min/?g=css_d&amp;5259487"/>
		<link rel="stylesheet" type="text/css" href="../min/?f=css/jRating.jquery.css&amp;5259487"/>
		<?php if($isMob) { ?>
			<link rel="stylesheet" type="text/css" href="../min/?g=css_m&amp;5259487"/>
		<?php } ?>
	</head>
	<body>
		<?php if(isset($error)) echo '<script>alert("'.$error.'");</script>'; ?>
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
								<li class="dropdown" role='button'>
									<a id="drop1" class="dropdown-toggle" role='button' data-toggle="dropdown" href="#">
										<i class="glyphicon glyphicon-folder-close"></i> Tickets<b class="caret"></b>
									</a>
									<ul class="dropdown-menu" aria-labelledby="drop1" role="menu">
										<li role="presentation">
											<a href="index.php" tabindex="-1" role="menuitem"><i class="glyphicon glyphicon-th-list"></i> Tickets List</a>
										</li>
										<li role="presentation">
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
			<div class='daddy main'>
				<hr>
				<div class="jumbotron" >
					<h1 class='pagefun'><?php echo $title; ?></h1>
				</div>
				<hr>
				<div class='row form-group refid'>
					<div class='col-md-2'><strong>Reference ID</strong></div>
					<div class='col-md-10' ><span id='reference_id'><?php echo $refid; ?></span></div>
				</div>
				<hr>
				<p class='cif'><i class='glyphicon glyphicon-plus-sign'></i> Website Information </p>
				<div class='expande'>
					<div class='row form-group'>
						<div class='col-md-2'><strong>Website</strong></div>
						<div class='col-md-4'><input type='text' class='form-control'  id='webs' value="<?php echo $cweb; ?>"/></div>
						<div class='col-md-2'><strong>Connection Type</strong></div>
						<div class='col-md-4'><select class='form-control'  id='contype'><option selected="" value="0">--</option><option value="1">FTP</option><option value="2">FTPS</option><option value="3">SFTP</option><option value="4">SSH</option><option value="5">Other</option></select></div>
					</div>
					<div class='row form-group'>
						<div class='col-md-2'><strong>Username</strong></div>
						<div class='col-md-4'><input type='text' class='form-control' id='conuser' value="<?php echo addslashes($usercred); ?>"/></div>
						<div class='col-md-2'><div class='col-md-7'><strong>Password</strong></div><div class='col-md-5'><button id='showhide' class='btn btn-info'>Show</button></div></div>
						<div class='col-md-4' id='passcont'><input class='form-control'  type='password' id='conpass' value="<?php echo addslashes($conpass); ?>" autocomplete="off" /></div>
					</div>
					<input type='submit' class='btn btn-success' id='updtconn' onclick='javascript:return false;' value='Update'/>
				</div>
				<hr>
				<p class='cif'><i class='glyphicon glyphicon-plus-sign'></i> Edit Ticket Title and Status </p>
				<div class='expande'>
					<div class='row form-group'>
						<div class='col-md-2'>Update Title</div>
						<div class='col-md-3'><input type='text' class='form-control' id='nwtittk' value='<?php echo $title; ?>' required/></div>
						<div class='col-md-1'><input type='submit' class='btn btn-success' id='updtitle' onclick='javascript:return false;' value='Update'/></div>
					</div>
					<div class='row form-group'>
						<div class='col-md-2'>Update Status</div>
						<div class='col-md-3'><select class='form-control'  id='statustk'><option value='1'>Open</option><option value='0'>Closed</option></select></div>
						<div class='col-md-1'><input type='submit' class='btn btn-success' id='updstatus' onclick='javascript:return false;' value='Update'/></div>
					</div>
					<?php if($_SESSION['tickets'][$_GET['id']]['usr_id']==$_SESSION['id'] && $setting[7]==1){ ?>
						<div class='ratingsect row form-group' <?php if($stat!=0) echo 'style="display:none"' ;?>>
							<div class='row form-group'>
								<div class='col-md-2'>Rate Operator</div>
								<div class='col-md-4'><input type='hidden' id='tkid' value='<?php echo $_GET['id'];?>' /><div class="razorate" data-average="<?php echo ($rate!='')? $rate:0;?>" data-id="<?php echo ($opid*3823);?>"></div></div>
							</div>
								<div class='row form-group'>
								<div class='col-md-6'><textarea id='rcomment' rows='7' placeholder='Add a comment' required><?php if(isset($note))echo $note; ?></textarea></div>
							</div>
							<div class='row form-group'>
							<div class='col-md-2 col-md-offset-2'><input id='submitrate' type='submit' class='btn btn-success' onclick='javascript:return false;' value='Rate'/></div>
							</div>
						</div>
						<br/>
					<?php } ?>
				</div>
				<?php if($_SESSION['status']==1){ $b=retrive_depa_names($Hostname, $Username, $Password, $DatabaseName, $SupportDepaTable, $departmentid);?>
					<hr>
					<p class='cif'><i class='glyphicon glyphicon-plus-sign'></i> Change Ticket Department </p>
					<div class='expande'>
						<div class='row form-group'>
							<div class='col-md-2'>Change Departement</div>
							<div class='col-md-3'><select class='form-control'  id='departments'><?php echo $b; ?></select></div>
							<div class='col-md-1'><input type='submit' class='btn btn-success' id='updtdpop' onclick='javascript:return false;' value='Update'/></div>
						</div>
					</div>
				<?php } if($_SESSION['status']==2){ $b=retrive_depa_names($Hostname, $Username, $Password, $DatabaseName, $SupportDepaTable, $departmentid);$c=json_decode(retrive_depa_operators($Hostname, $Username, $Password, $DatabaseName, $SupportUserTable, $SupportUserPerDepaTable, $departmentid, $opid));?>
					<hr>
					<p class='cif'><i class='glyphicon glyphicon-plus-sign'></i> Change Ticket Department and Operator</p>
					<div class='expande'>
						<div class='row form-group'>
							<div class='col-md-2'>Change Departement</div>
							<div class='col-md-3'><select class='form-control'  id='departments'><?php if($b!=false)echo $b;?></select></div>
						</div>
						<div class='row form-group'>
							<div class='col-md-2'>Change Operator</div>.
							<div class='col-md-3'><label class="checkbox inline"><input type='checkbox' id='autass' value='yes'/> Automatic Assignment</label></div>
						</div>
						<div class='row form-group'>
							<div class='col-md-2'></div>
							<div class='col-md-3'><select class='form-control'  id='operat'><option value="0">---</option><?php if($c!=false){foreach($c as $key=>$val) echo '<option value="'.$key.'">'.$val.'</option>';} ?></select></div>
							<div class='col-md-1'><input type='submit' class='btn btn-success' id='updtdpadmin' onclick='javascript:return false;' value='Update'/></div>
						</div>
					</div>
				<?php } ?>
					<hr>
					<p class='cif'><i class="glyphicon glyphicon-plus-sign"></i> Report a Problem with this ticket</p>
					<div class='expande' >
						<div class='row form-group'>
							<div class='col-md-2'>Report Ticket</div>
							<div class='col-md-8'><textarea class='form-control' id='problem' rows='7' placeholder='Write your complaint' required><?php if(isset($reason))echo $reason; ?></textarea></div>
						</div>
						<div class='row form-group'>
							<div class='col-md-2 col-md-offset-5'><input type='submit' class='btn btn-warning' id='subrepo' onclick='javascript:return false;' value='Submit your Complaint'/></div>
						</div>
					</div>
				<hr>
				<?php 
				if(!isset($error)){?>
					<h3 class='sectname'>Reply</h3>
					<form id='formreply' method="POST" action="../php/function.php" target='hidden_upload' enctype="multipart/form-data">
						<input type='hidden' name='id' value='<?php echo $_GET['id']; ?>' />
						<input type='hidden' name='<?php echo $_SESSION['token']['act']; ?>' value='Night' />
						<div class='row form-group'>
							<div class='col-md-12'><textarea name='message' id='message' rows="5" placeholder='Your Reply'> </textarea></div>
						</div>
						<?php if(isset($setting[5]) && $setting[5]==1){ ?>
							<h3 class='sectname'>Attachments</h3>
							<span class='attlist'></span>
							<br/>
							<p>To select multiple files: press ctrl+click on the chosen file</p>
							<div class='row form-group'>
								<!--<div class="row uploadfilebox"><div class="col-md-4"><input id='fielduploadinput' type="file" name="filename[]" multiple /></div></div>-->
								<div class='col-md-4'><input id='fielduploadinput' type="file" name="filename[]" multiple /></div><div class='col-md-3'><span id='resetfile' class='btn btn-danger'>Reset</span></div>
							</div>
						<?php } ?>
						<br/><br/>
						<input type='submit' name='post_reply' id='post_reply' value='Post Reply' class='btn btn-success'/>
					</form>
					<hr>
					<h3 class='sectname'>Messages</h3>
					<div id="messages">
						<?php 
							for($i=0;$i<$count;$i++){
								if($list[$i]['usr_id']==$_SESSION['id'])
									echo '<div class="row evenmessage"><div class="row"><div class="col-md-2 usrinfo"><p class="username">'.$list[$i][0].'</p><p class="date">'.$list[$i][2].'</p><p class="postnumber">Post Number: '.$list[$i][3].'</p></div><div class="col-md-8 messagecell">'.$list[$i][1].'</div></div>';
								else
									echo '<div class="row oddmessage"><div class="row"><div class="col-md-2 usrinfo"><p class="username">'.$list[$i][0].'</p><p class="date">'.$list[$i][2].'</p><p class="postnumber">Post Number: '.$list[$i][3].'</p></div><div class="col-md-8 messagecell">'.$list[$i][1].'</div></div>';

								$upcount=count($list[$i]);
								if($upcount>4){
									echo '<div class="row form-group attachment"><div class="col-md-2 col-md-offset-1 attachmentsec">Attachment</div><div class="col-md-8">';
									for($j=4;$j<$upcount;$j++)
										echo $list[$i][$j];
									echo'</div></div>';
								}
								echo '</div>';
							}
						?>
					</div>
				<?php } else { ?>
					<p><?php echo $error; ?></p>
				<?php } ?>
					</div>
		</div>
	<iframe style='display:none' name='hidden_upload' id='hidden_upload' src="about:blank"></iframe>
	
	<script type="text/javascript"  src="../min/?g=js_i&amp;5259487"></script>
	<script type="text/javascript"  src="../min/?g=js_d&amp;5259487"></script>
	<?php if(!$isMob) { ?>
		<script type="text/javascript"  src="../lib/ckeditor/ckeditor.js"></script>
		<script type="text/javascript"  src="../js/ckbotfix.js"></script>
	<?php }else { ?>
		<script type="text/javascript"  src="../min/?g=js_m&amp;5259487"></script>
	<?php } ?>
	<script type="text/javascript"  src="../min/?f=js/jRating.jquery.js,js/loadmessages.js&amp;5259487"></script>
	<script>
	var add=0,editor, writing=false, totalmsg=<?php echo $totnummsg;?>;
	$(document).ready(function(){

		$('#statustk').val("<?php echo ($stat==2 || $stat==1) ?  1:0; ?>").change();
		$('#contype').val('<?php echo $connection; ?>').change();
		
		$('#messages').scrollPagination({scroll:false,id:'<?php echo $_GET['id'];?>'});
		$('.loading-bar').delay(300).show('scale',null,400);
		
		<?php if($_SESSION['status']==1) { ?>
			$('#departments').val("<?php echo $departmentid; ?>").change();
			$('#operat option[value="<?php echo $opid; ?>"]').attr('selected','selected');
		<?php } else if($_SESSION['status']==2) { ?>
			$('#departments').val("<?php echo $departmentid; ?>").change();
			$('#operat option[value="<?php echo $opid; ?>"]').attr('selected','selected');
		<?php } if(!$isMob) {?>
			CKEDITOR.replace('message');
		<?php }else { ?>
			$('#message').wysihtml5();
		<?php } if($_SESSION['tickets'][$_GET['id']]['usr_id']==$_SESSION['id'] && $setting[7]==1){ ?>
		$(".razorate").jRating();
		<?php } ?>

		$("#formreply").submit(function(){if(""==<?php if(!$isMob) { ?>CKEDITOR.instances.message.getData().replace(/\s+/g,"")<?php }else { ?>$('#message').val().replace(/\s+/g,'')<?php } ?>)return noty({text:"Empty Message",type:"error",timeout:9E3}),!1;$("#formreply").nimbleLoader("show",{position:"absolute",loaderClass:"loading_bar_body",hasBackground:!0,zIndex:999,backgroundColor:"#fff",backgroundOpacity:0.9});return!0});
		
		//Add redirect
		$("#subrepo").click(function () {
			var a = $("#problem").val();
			if("" != a.replace(/\s+/g, ""))
				$.ajax({
					type: "POST",
					url: "../php/function.php",
					data: { <?php echo $_SESSION['token']['act']; ?> : "report_ticket",message: a,id: "<?php echo $_GET['id'];?>"},
					dataType: "json",
					success: function (b) {
						"Submitted" == b[0] ? noty({text: "Your complaint has been submitted",type: "success",timeout: 9E3}) : noty({text: b[0],type: "error",timeout: 9E3})
					}
				}).fail(function (b, a) {noty({text: a,type: "error",timeout: 9E3})})
			else
				noty({text: "The message cannot be empty",type: "error",timeout: 9E3})
		});		
		$("#showhide").click(function(){var a=$("#conpass").val()+"";$("#conpass").is(":password")?($("#passcont").html('<input type="text" class="form-control"  id="conpass" />'),$("#conpass").val(a),$("#showhide").text("Hide")):($("#passcont").html('<input type="password" class="form-control"  id="conpass" autocomplete="off" />'),$("#conpass").val(a),$("#showhide").text("Show"))});
		
		//Add redirect
		$("#updstatus").click(function() { var a = $("#statustk").val(); $.ajax({type:"POST", url:"../php/function.php", data:{<?php echo $_SESSION['token']['act']; ?>:"update_status", status:a, id:"<?php echo $_GET['id'];?>"}, dataType:"json", success:function(b) { "Saved" == b[0] ? (0 == a ? $(".ratingsect").slideToggle(800) : $(".ratingsect").slideToggle(800), noty({text:"Updated", type:"success", timeout:9E3})) : noty({text:b[0], type:"error", timeout:9E3}) }}).fail(function(b, a) { noty({text:a, type:"error", timeout:9E3}) }) });

		//Update Ticket Title
		$("#updtitle").click(function () {
			var a = $("#nwtittk").val().replace(/\s+/g, " ");
			"" != a.replace(/\s+/g, "") ? $.ajax({
				type: "POST",
				url: "../php/function.php",
				data: {<?php echo $_SESSION['token']['act']; ?>: "update_ticket_title",tit: a,id: "<?php echo $_GET['id'];?>"},
				dataType: "json",
				success: function (b) {
					if("Updated" == b[0])
						$(".pagefun").text(b[1])
					else if(b[0]=='sessionerror'){
						switch(b[1]){
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
						noty({text: b[0],type: "error",timeout: 9E3})
				}
			}).fail(function (b, a) {noty({text: a,type: "error",timeout: 9E3})}) : noty({text: "Empty Title",type: "error",timeout: 9E3})
		});	
		
		//Update Ticket Connection
		$("#updtconn").click(function () {
			var a = $("#contype > option:checked").val(),
				web = $('#webs').val(),
				c = $("#conuser").val(),
				d = $("#conpass").val();
			$.ajax({
				type: "POST",
				url: "../php/function.php",
				data: {<?php echo $_SESSION['token']['act']; ?>: "update_ticket_connection",website: web,contype: a,user: c,pass: d,id: "<?php echo $_GET['id'];?>"},
				dataType: "json",
				success: function (b) {
					if("Updated" == b[0])
						noty({text: "Updated",type: "success",timeout: 9E3})
					else if(b[0]=='sessionerror'){
						switch(b[1]){
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
						noty({text: b[0],type: "error",timeout: 9E3})
				}
			}).fail(function (b, a) {noty({text: a,type: "error",timeout: 9E3})})
		});
		
		//Show Hidden Form
		$('.cif').click(function(){
			el=$(this).children('i');
			if(el.hasClass('glyphicon glyphicon-plus-sign')){
				el.removeClass('glyphicon glyphicon-plus-sign');
				el.addClass('glyphicon glyphicon-minus-sign');
				$(this).next('div').slideToggle(800);
			}
			else{
				el.removeClass('glyphicon glyphicon-minus-sign');
				el.addClass('glyphicon glyphicon-plus-sign');
				$(this).next('div').slideToggle(800);
			}
		});
		
		//Remove Uploaded File
		$(document).on('click','.remfile',function(){
			if(confirm('Do you really want to delete this file?')){
				var dom=$(this), file_id=dom.parent().children('input[name="file_download"]').val();
				$.ajax({
					type: 'POST',url: '../php/function.php',data: {<?php echo $_SESSION['token']['act']; ?>:'del_post_file',file_id:file_id,id:'<?php echo $_GET['id'];?>'},dataType : 'json',
					success : function (a) {
						if(a[0]=='Deleted'){
							var par=dom.parent().parent();
							dom.parent().remove();
							if(par.children('form').length==0)
								par.parent().remove();
							noty({text: 'The file has been deleted',type:'success',timeout:9000});
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
							noty({text: a[0],type:'error',timeout:9000});
					}
				}).fail(function(jqXHR, textStatus){noty({text: textStatus,type:'error',timeout:9000});});
			}
		});
		
		$(document).on('click','#resetfile',function(){
			$('#fielduploadinput').wrap('<form>').closest('form').get(0).reset();
			$('#fielduploadinput').unwrap();
		});

		//Check if writing ---- Problem: doesn't identify first character and last delete
		<?php if(!$isMob) { ?>
			CKEDITOR.instances.message.on('key', function(event) {
				var m=$.trim(CKEDITOR.instances.message.getData().replace(/\s+/g,""));
				writing=(m=="")? false:true;
			});
		<?php }else { ?>
		$(document).on('change','#message',function(){
			var m=$.trim($("#message").val().replace(/\s+/g,''));
			writing=(m=='')? false:true;
		});
		<?php } ?>
		
		<?php if($_SESSION['status']==1) { ?>
			$('#updtdpop').click(function(){
				var dpid=$('#departments').val();
				$.ajax({
					type: 'POST',url: '../php/function.php',data: {<?php echo $_SESSION['token']['act']; ?>:'move_opera_ticket',dpid:dpid,id:'<?php echo $_GET['id'];?>'},dataType : 'json',
					success : function (a) {
						if(a[0]=='Moved')
							noty({text: 'Moved',type:'success',timeout:9000})
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
							noty({text: a[0],type:'error',timeout:9000});
					}
				}).fail(function(jqXHR, textStatus){noty({text: textStatus,type:'error',timeout:9000});});
			});
		<?php } ?>
		
		<?php if($_SESSION['status']==2) { ?>
			$("#autass").on("click", function() { 1 == $("#autass:checked").length ? $("#operat").attr("disabled", "disabled") : $("#operat").removeAttr("disabled") });
			
			$(document).on("change", "#departments", function() { $("#departments").attr("disabled", "disabled"); var b = $("#departments > option:checked").val(); $.ajax({type:"POST", url:"../php/admin_function.php", data:{<?php echo $_SESSION['token']['act']; ?>:"retrive_operator_assign", id:b,enc:'<?php echo $_GET['id'];?>'}, dataType:"json", success:function(a) { "Ex" == a[0] ? (a[0] = "", $("#operat").html(a.join(""))) : noty({text:a[0], type:"error", timeout:9E3}); $("#departments").removeAttr("disabled") }}).fail(function(a, b) { noty({text:b, type:"error", timeout:9E3}) }) });
			
			$("#updtdpadmin").click(function() { var a = $("#departments").val(), c = 1 == $("#autass:checked").length ? -1 : $("#operat").val(); $.ajax({type:"POST", url:"../php/admin_function.php", data:{<?php echo $_SESSION['token']['act']; ?>:"move_admin_ticket", dpid:a, opid:c, id:"<?php echo $_GET['id'];?>"}, dataType:"json", success:function(b) { "AMoved" == b[0] ? noty({text:"Moved", type:"success", timeout:9E3}) : noty({text:b[0], type:"error", timeout:9E3}) }}).fail(function(b, a) { noty({text:a, type:"error", timeout:9E3}) }) });

		<?php } ?>
		
	});
	function logout(){$.ajax({type:"POST",url:"../php/function.php",data:{<?php echo $_SESSION['token']['act']; ?>:"logout"},dataType:"json",success:function(a){"logout"==a[0]?window.location.reload():alert(a[0])}}).fail(function(a,b){noty({text:b,type:"error",timeout:9E3})})};
	
	function post_reply(mess, dat, name, up) {
		totalmsg++;
		<?php if(!$isMob){ ?> 
			CKEDITOR.instances.message.setData(''); 
		<?php } else { ?> 
			editor.setValue("", true); 
		<?php } ?> 

		$(".uploadfilebox").each(function () {$(this).remove()});
		
		$(".attlist").append("<div class='row uploadfilebox'></div>");
		tail = [];
		tail.push('<div class="row evenmessage" style="display:none"><div class="row"><div class="col-md-2 usrinfo"><p class="username">' + name + '</p><p class="date">' + dat + '</p><p class="postnumber">Post Number: '+totalmsg+'</p></div><div class="col-md-8 messagecell">' + mess + "</div></div>");
		if (null != up){
			tail.push('<div class="row attachment"><div class="col-md-2 col-md-offset-1 attachmentsec">Attachment</div><div class="col-md-8">');
			var count= up.length;
			for (i=0; i < count; i++) 
				tail.push("<form class='download_form' method='POST' action='../php/function.php' target='hidden_upload' enctype='multipart/form-data'><input type='hidden' name='ticket_id' value='" + up[i][0] + "'/><input type='hidden' name='file_download' value='" + up[i][1] + "'/><input type='submit' class='btn btn-link download' value='" + up[i][2] + "'> &nbsp;&nbsp; <i class='glyphicon glyphicon-remove-sign remfile' title='Delete File' alt='Delete File'></i></form>");
			tail.push("</div></div>");
		}
		tail.push("</div>");
		$(".newest").remove();
		$("#messages").children(".row:first").before(tail.join(""));
		$("#messages").children(".row:first").delay(300).show('scale');
		$('#fielduploadinput').wrap('<form>').closest('form').get(0).reset();
		$('#fielduploadinput').unwrap();
		add++;
		writing=false;
	}
	
	$(window).bind('beforeunload', function(){
		if(writing==true){
			return 'Do you want to leave the page and discard the post?';
		}
	});
	</script>
  </body>
</html>