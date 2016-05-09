<?php

define('INCLUDE_CHECK',true);

require 'connect.php';
require 'functions.php';
// Those two files can be included only if INCLUDE_CHECK is defined


session_name('tzLogin');
// Starting the session

session_set_cookie_params(2*7*24*60*60);
// Making the cookie live for 2 weeks

session_start();

if($_SESSION['id'] && !isset($_COOKIE['tzRemember']) && !$_SESSION['rememberMe'])
{
	// If you are logged in, but you don't have the tzRemember cookie (browser restart)
	// and you have not checked the rememberMe checkbox:

	$_SESSION = array();
	session_destroy();
	
	// Destroy the session
}


if(isset($_GET['logoff']))
{
	$_SESSION = array();
	session_destroy();
	
	header("Location: demo.php");
	exit;
}

if($_POST['submit']=='Login')
{
	// Checking whether the Login form has been submitted
	
	$err = array();
	// Will hold our errors
	
	
	if(!$_POST['username'] || !$_POST['password'])
		$err[] = 'All the fields must be filled in!';
	
	if(!count($err))
	{
		$_POST['username'] = mysql_real_escape_string($_POST['username']);

		$_POST['password'] = mysql_real_escape_string($_POST['password']);
		$_POST['rememberMe'] = (int)$_POST['rememberMe'];
		
		// Escaping all input data

		$row = mysql_fetch_assoc(mysql_query("SELECT id,usr FROM tz_members WHERE usr='{$_POST['username']}' AND pass='".md5($_POST['password'])."'"));

		if($row['usr'])
		{
			// If everything is OK login
			
			$_SESSION['usr']=$row['usr'];
			$_SESSION['id'] = $row['id'];
			$_SESSION['rememberMe'] = $_POST['rememberMe'];
			
			// Store some data in the session
			
			setcookie('tzRemember',$_POST['rememberMe']);
		}
		else $err[]='Wrong username and/or password!';
	}
	
	if($err)
	$_SESSION['msg']['login-err'] = implode('<br />',$err);
	// Save the error messages in the session

	header("Location: demo.php");
	exit;
}
else if($_POST['submit']=='Register')
{
	// If the Register form has been submitted
	
	$err = array();
	
	if(strlen($_POST['username'])<4 || strlen($_POST['username'])>32)
	{
		$err[]='Your username must be between 3 and 32 characters!';
	}
	
	if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['username']))
	{
		$err[]='Your username contains invalid characters!';
	}
/*	
	if(!checkEmail($_POST['email']))
	{
		$err[]='Your email is not valid!';
	}
*/	
	if(!count($err))
	{
		// If there are no errors
		
		$pass = mysql_real_escape_string($_POST['password']);
        //substr(md5($_SERVER['REMOTE_ADDR'].microtime().rand(1,100000)),0,6);
		// Generate a random password
		
		//$_POST['email'] = mysql_real_escape_string($_POST['email']);
		$_POST['username'] = mysql_real_escape_string($_POST['username']);
		// Escape the input data
		
		
		mysql_query("	INSERT INTO tz_members(usr,pass,regIP,dt)
						VALUES(
						
							'".$_POST['username']."',
							'".md5($pass)."',
							'".$_SERVER['REMOTE_ADDR']."',
							NOW()
							
						)");
		
		if(mysql_affected_rows($link)==1)
		{
            $row = mysql_fetch_assoc(mysql_query("SELECT id,usr FROM tz_members WHERE usr='{$_POST['username']}' AND pass='".md5($_POST['password'])."'"));
            $_SESSION['usr']=$row['usr'];
            $_SESSION['id'] = $row['id'];
            
            // Store some data in the session
            
            setcookie('tzRemember');
/*
			send_mail(	'demo-test@tutorialzine.com',
						$_POST['email'],
						'Registration System Demo - Your New Password',
						'Your password is: '.$pass);

			$_SESSION['msg']['reg-success']='We sent you an email with your new password!';
*/
		}
		else $err[]='This username is already taken!';
	}

	if(count($err))
	{
		$_SESSION['msg']['reg-err'] = implode('<br />',$err);
	}	
	
	header("Location: demo.php");
	exit;
}

$script = '';

if($_SESSION['msg'])
{
	// The script below shows the sliding panel on page load
	
	$script = '
	<script type="text/javascript">
	
		$(function(){
		
			$("div#panel").show();
			$("#toggle a").toggle();
		});
	
	</script>';
	
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>A Cool Login System With PHP MySQL &amp jQuery | Tutorialzine demo</title>
    
    <link rel="stylesheet" type="text/css" href="demo.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="login_panel/css/slide.css" media="screen" />
    
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
    
    <!-- PNG FIX for IE6 -->
    <!-- http://24ways.org/2007/supersleight-transparent-png-in-ie6 -->
    <!--[if lte IE 6]>
        <script type="text/javascript" src="login_panel/js/pngfix/supersleight-min.js"></script>
    <![endif]-->
    
    <script src="login_panel/js/slide.js" type="text/javascript"></script>
    
    <?php echo $script; ?>
</head>

<body>

<!-- Panel -->
<div id="toppanel">
	<div id="panel">
		<div class="content clearfix">
			<div class="left">
				<h1>The Sliding jQuery Panel</h1>
				<h2>A register/login solution</h2>		
				<p class="grey">You are free to use this login and registration system in you sites!</p>
				<h2>A Big Thanks</h2>
				<p class="grey">This tutorial was built on top of <a href="http://web-kreation.com/index.php/tutorials/nice-clean-sliding-login-panel-built-with-jquery" title="Go to site">Web-Kreation</a>'s amazing sliding panel.</p>
			</div>
            
            
            <?php
			
			if(!$_SESSION['id']):
			
			?>
            
			<div class="left">
				<!-- Login Form -->
				<form class="clearfix" action="" method="post">
					<h1>Member Login</h1>
                    
                    <?php
						
						if($_SESSION['msg']['login-err'])
						{
							echo '<div class="err">'.$_SESSION['msg']['login-err'].'</div>';
							unset($_SESSION['msg']['login-err']);
						}
					?>
					
					<label class="grey" for="username">Username:</label>
					<input class="field" type="text" name="username" id="username" value="" size="23" />
					<label class="grey" for="password">Password:</label>
					<input class="field" type="password" name="password" id="password" size="23" />
	            	<label><input name="rememberMe" id="rememberMe" type="checkbox" checked="checked" value="1" /> &nbsp;Remember me</label>
        			<div class="clear"></div>
					<input type="submit" name="submit" value="Login" class="bt_login" />
				</form>
			</div>
			<div class="left right">			
				<!-- Register Form -->
				<form action="" method="post">
					<h1>Not a member yet? Sign Up!</h1>		
                    
                    <?php
						
						if($_SESSION['msg']['reg-err'])
						{
							echo '<div class="err">'.$_SESSION['msg']['reg-err'].'</div>';
							unset($_SESSION['msg']['reg-err']);
						}
						
						if($_SESSION['msg']['reg-success'])
						{
							echo '<div class="success">'.$_SESSION['msg']['reg-success'].'</div>';
							unset($_SESSION['msg']['reg-success']);
						}
					?>
                    		
					<label class="grey" for="username">Username:</label>
					<input class="field" type="text" name="username" id="username" value="" size="23" />
					<label class="grey" for="password">Password:</label>
					<input class="field" type="text" name="password" id="password" size="23" />
					<label>A password will not  be e-mailed to you.</label>
					<input type="submit" name="submit" value="Register" class="bt_register" />
				</form>
			</div>
            
            <?php
			
			else:
			
			?>
            
            <div class="left">
            
            <h1>Members panel</h1>
            
            <a href="?logoff">Log off</a>
            
            </div>
            
            <div class="left right">
            </div>
            
            <?php
			endif;
			?>
		</div>
	</div> <!-- /login -->	

    <!-- The tab on top -->	
	<div class="tab">
		<ul class="login">
	    	<li class="left">&nbsp;</li>
	        <li>Hello <?php echo $_SESSION['usr'] ? $_SESSION['usr'] : 'Guest';?>!</li>
			<li class="sep">|</li>
			<li id="toggle">
				<a id="open" class="open" href="#"><?php echo $_SESSION['id']?'Open Panel':'Log In | Register';?></a>
				<a id="close" style="display: none;" class="close" href="#">Close Panel</a>			
			</li>
	    	<li class="right">&nbsp;</li>
		</ul> 
	</div> <!-- / top -->
	
</div> <!--panel -->

<div class="pageContent">
    <div id="main">
  <div class="container">
    <h1>Registered Users Only!</h1>
    <h2>Login to view this resource!</h2>
    </div>
    
    <div class="container">
    
    <?php
    if($_SESSION['id'])
    echo '<h1>Hello, '.$_SESSION['usr'].'! You are registered and logged in!</h1>';
    // echo '<a id="selectfiles" href="javascript:void(0);" class="btn">选择文件</a>';
    // echo '<a id="postfiles" href="javascript:void(0);" class="btn">开始上传</a>';
    else echo '<h1>Please, <a href="demo.php">login</a> and come back later!</h1>';
    ?>
    </div>


    <?php
    if($_SESSION['id']){
    echo '<br>';
    echo '你好啊！';
    echo '<form name=theform>';
    echo '<input type="radio" name="myradio" value="local_name" checked=true/> 上传文件名保持和本地一致';
    echo '</form>';
    echo '<br/>';
    
    echo '<div id="container">';
    echo '<a id="selectfiles" href="javascript:void(0);" class="btn">选择文件</a>';
    echo '<a id="postfiles" href="javascript:void(0);" class="btn">开始上传</a>';
    echo '</div>';

    echo '<h4>选择的文件列表:</h4>';
    echo '<div id="ossfile">你的浏览器不支持flash，silverlight活着html5！</div>';}
    ?>

<!--
    <br>
    <form name=theform>
    <input type="radio" name="myradio" value="local_name" checked=true/> 上传文件名字保持本地文件名字
    <input type="radio" name="myradio" value="random_name"/> 上传文件名字是随机名字，后缀保留
    </form>

    <br/>

    <div id="container">
        <a id="selectfiles" href="javascript:void(0);" class="btn">选择文件</a>
        <a id="postfiles" href="javascript:void(0);" class="btn">开始上传</a>
    </div>

    <h4>您选择的文件列表：</h4>
    <div id="ossfile">你的浏览器不支持flash,Silverlight或者html5!</div>
-->

   
    <?php

    if($_SESSION['id']) {

        $con = mysql_connect("localhost","root","twinpack");
        if (!$con)
        {
            die('Could not connect: ' . mysql_error());
        }

        mysql_select_db("FAMILYMART", $con);

        $result = mysql_query("SELECT * FROM pic_tbl WHERE usrid='".$_SESSION['usr']."'");


        echo "<table border='1'>
        <tr>
        <th>Firstname</th>
        <th>Lastname</th>
        </tr>";

        while($row = mysql_fetch_array($result))
        {
            echo "<tr>";
            echo "<td>" . $row['userid'] . "</td>";
            echo "<td>" . "<img src=". $row['picurl']. ">" . "</td>";
            echo "</tr>";
        }
        echo "</table>";

         //header('Content-type: image/jpeg');
        //echo file_get_contents('http://ship01.oss-cn-shanghai.aliyuncs.com/warehouse/Screenshot_20160311_105556.png');
        
        echo '<img src="http://ship01.oss-cn-shanghai.aliyuncs.com/warehouse/Screenshot_20160311_105556.png"></a>';
        
        echo $_SESSION['usr'];
        echo 'here';


        mysql_close($con);

    }
    ?>


  <div class="container tutorial-info">
  This is a tutorialzine demo. View the <a href="http://tutorialzine.com/2009/10/cool-login-system-php-jquery/" target="_blank">original tutorial</a>, or download the <a href="demo.zip">source files</a>.    </div>
</div>
</div>

</body>
<script type="text/javascript" src="lib/plupload-2.1.2/js/plupload.full.min.js"></script>
<script type="text/javascript" src="upload.js"></script>
</html>
