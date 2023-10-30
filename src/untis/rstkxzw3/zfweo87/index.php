<?php
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){							// Sicherstellen, dass Übertragung verschlüsselt ist
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}

session_save_path('/is/htdocs/wp12599526_UWBR3PUPTC/www/untis/rstkxzw3/sessions');
session_start();


// zentrale Einstellung: WELCHES SCHULJAHR?
$schuljahr = array( 'Pfad' 		=>	'2020-21',
					'Schrift'	=>	'2020/21');

//	Serverinfo:
//	phpinfo();
//	Pfad-Informationen:
//	echo '$_SERVER["DOCUMENT_ROOT"]: "'.$_SERVER["DOCUMENT_ROOT"].'"'."<br>\n";
//	echo '$_SERVER["SCRIPT_NAME"]: "'.$_SERVER['SCRIPT_NAME'].'"';

if (isset($_POST["pass"])) {
	$pass = $_POST["pass"];
	$pass = md5($pass);
}	else { $pass = ''; }
if (isset($_GET["logout"])) {
	$logout = $_GET["logout"];
}	else { $logout = 0; }
if ($logout == 1) {
  $_SESSION["log"] = 0;
}
  
if ($pass == "2023980cc00de27333f8d9bb10aa8a78") {
  $_SESSION["log"] = 1;
}

setlocale(LC_TIME, "de_DE");
date_default_timezone_set('Europe/Berlin');

?>
<!DOCTYPE HTML>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta http-equiv="expires" content="0">
    <meta http-equiv="cache-control" content="no-cache">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css" type="text/css">
    <title>Schiller&shy;gymnasium Anrechnungs&shy;stunden <?php echo $schuljahr['Schrift']; ?></title>
  </head>
  <body>
  <h1 id="logo"><a href="./index.php">Schiller&shy;gymnasium Anrechnungs&shy;stunden <?php echo $schuljahr['Schrift']; ?></a></h1>

<?php
// eingeloggt:
if ((isset($_SESSION["log"])) && ($_SESSION["log"] == 1)) {
	?>
	<a class="rahmen" href="./<?php echo $schuljahr['Pfad']; ?>/index.php?halbjahr=1"><b>1. Halbjahr </b></a>
	<a class="rahmen" href="./<?php echo $schuljahr['Pfad']; ?>/index.php?halbjahr=2"><b>2. Halbjahr </b></a>
	
	<div style="font-size:0.8em;0 padding:20px;margin:0 20px;"><a href="index.php?logout=1">Logout</a></div>
	<?php
}

// nicht eingeloggt:
else {
	?>

	<form action="index.php" method="post" name="login">
		<div class="rahmen">

			<input class="luftigrund" original-title="" name="pass" id="password" value="" placeholder="Passwort" autocomplete="off" autofocus="" autocapitalize="off" autocorrect="off" required="" type="password">
			<input id="submit" class="luftigrund" value="Einloggen" type="submit">
		</div>
			
	</form>
	
	<?php
}
// if (isset($_POST)) var_dump($_POST);
?>
  </body>
</html>