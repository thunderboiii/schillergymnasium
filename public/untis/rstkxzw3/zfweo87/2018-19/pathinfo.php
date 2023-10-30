<?php
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){							// Sicherstellen, dass Übertragung verschlüsselt ist
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}
//	Serverinfo:
//	phpinfo();
//	Pfad-Informationen:
	echo '$_SERVER["DOCUMENT_ROOT"]: "'.$_SERVER["DOCUMENT_ROOT"].'"'."<br>\n";
	echo '$_SERVER["SCRIPT_NAME"]: "'.$_SERVER['SCRIPT_NAME'].'"';