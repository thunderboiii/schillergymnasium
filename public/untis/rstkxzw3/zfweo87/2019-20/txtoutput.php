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
//	echo '$_SERVER["DOCUMENT_ROOT"]: "'.$_SERVER["DOCUMENT_ROOT"].'"'."<br>\n";
//	echo '$_SERVER["SCRIPT_NAME"]: "'.$_SERVER['SCRIPT_NAME'].'"';

session_start();

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
    <title>Schiller&shy;gymnasium Anrechnungs&shy;stunden 20../..</title>
  </head>
  <body>
  <h1 id="logo"><a href="./index.php">Schiller&shy;gymnasium Anrechnungs&shy;stunden 20../.., ?. Halbjahr</a></h1>

<?php if ((isset($_SESSION["log"])) && ($_SESSION["log"] == 1)) {

class Lehrer {
	public $kuerzel;
	public $stellenumfang;
	public $uebr_entlastung;
}

function korrekturkurs ($kurs) {
	$klasse	= $kurs[0];
	$fach	= $kurs[1];
	// Sport nie Korrektur, ebenso Geschichte/SoWi-Zusatz, Vokalprakt. Kurs, Literatur:
	$muendlich = array ("Sp", "GZ", "SZ", "IV", "VP", "Li");		// Der vokalpraktsiche Kurs kann "IV" oder "VP" heißen.
	if (in_array($fach, $muendlich))	{ return false; }
	// Hauptfächer:
	$hauptfaecher = array ("D", "E", "M", "F", "L", "E5", "E6", "L5", "L6", "F6");
	if (in_array($fach, $hauptfaecher))	{ return true; }
	// WP II:
	$wpII = array ("DG", "G/C", "B/C", "F8", "If");			// "If" ist nicht eindeutig WP II, sondern auch SekII, aber auch da kommt es ja als Korrekturkurs in Frage, insofern darf das ruhig direkt 'true' returnen.
	if (in_array($fach, $wpII))			{ return true; }
	// oder alles MÖgliche in der Oberstufe:
	$oberstufe = array ("EF", "Q1", "Q2");					// Da Sport/GZ/SZ/VP oben schon mit 'return false' ausgeschlossen wurden, können hier einfach alle anderen Oberstufenkurse als grundsätzliche Korrekturkurse betrachtet werden.
	if (in_array($klasse, $oberstufe))	{ return true; }
	// in allen anderen Fällen:
	return false;
}

$vollerdateiname = 'UV201819a.txt';                                               // "UV....txt" einlesen (enthält Kollegen und Kurse)
$db = file($vollerdateiname);

// Datei in brauchbares Array umwandeln:
$lehrer = array (); // Hier soll alles am Ende schön geordnet rein
for ($i = 0; $i < count($db); $i++) {   // zeilenweise durch Textdati "UV....txt" gehen
	$aktueller_lehrer_roh = $db[$i];
	$aktueller_lehrer_roh = explode("\t", $aktueller_lehrer_roh);
	for ($j = 0; $j < count($aktueller_lehrer_roh); $j++) {	// ganze Zeile (ein Lehrer) in einzene Felder teilen und von Leerzeichen befreien, leere Felder löschen
		$aktueller_lehrer_roh[$j] = trim($aktueller_lehrer_roh[$j]);
		if (($j >= 4) && ($aktueller_lehrer_roh[$j] == "")) { array_splice($aktueller_lehrer_roh, $j, 1); $j = $j - 1; } // leere Kurse entfernen (Kurse erst ab Element 4; davor Stunden und übr. Entlastung
	}
	// Ergebnis: array 'aktueller_lehrer_roh' mit allen Feldern (0-3 Kürzel, Stelle, übr. Entlastung, Stunden_Soll, ab 4: Kurse, letztes Feld: Stunden_Ist)
	
	// in 'Lehrer'-Array einfügen:
	$lehrer[] = array (
		"kuerzel" => $aktueller_lehrer_roh[0],
		"stelle" => $aktueller_lehrer_roh[1],
		"uebr_entlastung" => $aktueller_lehrer_roh[2],
		"stunden_soll" => $aktueller_lehrer_roh[3],
		"kurs" => array (),
		"korrekturkurs" => array (),
		"stunden_ist" => $aktueller_lehrer_roh[count($aktueller_lehrer_roh)-1]
	);
	for ($k = 4; $k <= (count($aktueller_lehrer_roh)-2); $k++) {
		$aktueller_kurs_roh = $aktueller_lehrer_roh[$k];
		$aktueller_kurs_roh = explode(" ", $aktueller_kurs_roh);
		$lehrer[$i]["kurs"][] = array (
			"klasse"	=>	$aktueller_kurs_roh[0],
			"fach"		=>	$aktueller_kurs_roh[1]
		);
		if (korrekturkurs($aktueller_kurs_roh)) {
			$lehrer[$i]["korrekturkurs"][] = array (
				"klasse"	=>	$aktueller_kurs_roh[0],
				"fach"		=>	$aktueller_kurs_roh[1]
			);
		}
		unset($aktueller_kurs_roh);
	}
	unset($aktueller_lehrer_roh);
}

$keineanrechnung = array("PER", "NN1", "NN2", "NN3", "NN4", "ALB", "FEG", "BNA", "NOL", "BÜR", "KIA", "AT", "KN", "RAU", "UH");
// Kontroll-Tabelle ausgeben, in der alle eingelesenen Daten mit Kursen und Korrekturkursen stehen:
// * dabei von vornherein auschließen: [(alle, die _keinen_ Korrekturkurs haben)Nein, zulassen wg. Klassenleitung] Vertretungslehrer und Sonderpädagogen:
if (isset($_GET["tabelle"]) && ($_GET["tabelle"] == 1)) {
echo '<table>';
for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
	if (/*!empty($lehrer[$i]["korrekturkurs"]) && */!in_array($lehrer[$i]["kuerzel"], $keineanrechnung))	{ $style = '"border:1px solid black;"'; }	// * [(alle, die _keinen_ Korrekturkurs haben)Nein, zulassen wg. Klassenleitung] Vertretungslehrer und Sonderpädagogen
																							else			{ $style = '"border:1px solid #888;color:#888;background-color:#ddd;"'; }
	echo '<tr>';
		echo "<td style=$style>".$lehrer[$i]["kuerzel"]."</td>";
		echo "<td style=$style>".$lehrer[$i]["stelle"]."</td>";
		echo "<td style=$style>".$lehrer[$i]["uebr_entlastung"]."</td>";
		echo "<td style=$style>".$lehrer[$i]["stunden_soll"]."</td>";
		for ($j = 0; $j < count($lehrer[$i]["kurs"]); $j++) {			 // Elementweise durch array $lehrer[$i]["kurs"] gehen
			echo "<td style=$style>".$lehrer[$i]["kurs"][$j]["klasse"]." ".$lehrer[$i]["kurs"][$j]["fach"]."</td>";
		}
		echo "<td style=$style>".$lehrer[$i]["stunden_ist"]."</td>";
	echo '</tr>';
		echo '<tr>';
		echo '<td style="text-align:right;" colspan="4">davon Korrekturkurse:</td>';
		for ($j = 0; $j < count($lehrer[$i]["korrekturkurs"]); $j++) {	// Elementweise durch array $lehrer[$i]["korrekturkurs"] gehen
			echo "<td style=$style>".$lehrer[$i]["korrekturkurs"][$j]["klasse"]." ".$lehrer[$i]["korrekturkurs"][$j]["fach"]."</td>";
		}
	echo '</tr><tr><td>&nbsp;</td></tr>';

}
echo '</table>';
}
else
{
// Dialog für Eintragungen:

// 1. Kürzel auswählen:
// wenn noch keines ausgewählt ist
if ((!isset($_GET["anmelden_mit_kuerzel"]) && !isset($_POST["kuerzel"])) || (isset($_GET["anmelden_mit_kuerzel"]) && ($_GET["anmelden_mit_kuerzel"]=="0"))) { ?>
	<form action="index.php" method="get">
		<div class="rahmen">
			<select name="anmelden_mit_kuerzel">
				<option value="0">--Kürzel auswählen:--</option>
<?php			for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
					if (/*!empty($lehrer[$i]["korrekturkurs"]) && */!in_array($lehrer[$i]["kuerzel"], $keineanrechnung))	{
						echo '<option>'.$lehrer[$i]["kuerzel"].'</option>'."\n";
					}
				}
?>			</select>
			<button id="submit" class="luftigrund">weiter</button>
		</div>
	</form>
	<div style="font-size:0.8em;0 padding:20px;margin:0 20px;"><a href="index.php?logout=1">Logout</a></div>
<?php
}
elseif (isset($_GET["anmelden_mit_kuerzel"]) && !isset($_POST["kuerzel"]))  { // Und dann (Kürzel also schon ausgewählt):
	
	// Lehrernummer zum Kürzel im Array suchen (und dabei sichergehen, dass eine eindeutige Nummer gefunden wurde):
	$lnummern = array_keys(array_combine(array_keys($lehrer), array_column($lehrer, 'kuerzel')), $_GET["anmelden_mit_kuerzel"]);
	if (count($lnummern)>1) { echo 'Fehler: Kuerzel doppelt vorhanden?'; }
	elseif (count($lnummern) == 0) { echo 'Fehler: Kuerzel nicht vorhanden?'; }
	else { // eindeutige Lehrernummer:
		$nr = $lnummern[0];
		echo "angemeldet als: ".$lehrer[$nr]["kuerzel"]." (<a href='index.php'>ändern</a>)<br><br>\n";
		echo "Nach dem Stand der vorliegenden Unterrichtsverteilung müsste es folgende Kurse mit Klassenarbeiten/Klausuren geben:*\n";
		if (empty($lehrer[$nr]["korrekturkurs"])) { echo "keine\n"; }
		echo "<br><br>\n";
		echo '<form action="index.php" method="post" name="formular" id="formular">';
		echo '<input type="hidden" name="kuerzel" value="'.$lehrer[$nr]["kuerzel"].'">'."\n";
		for ($j = 0; $j < count($lehrer[$nr]["korrekturkurs"]); $j++) {	// Elementweise durch array $lehrer[$nr]["korrekturkurs"] gehen
			echo '<div class="rahmen" id="kurs'.$j.'">';
			echo '<input type="hidden" name="korrekturkurs'.$j.'" value="'.$lehrer[$nr]["korrekturkurs"][$j]["klasse"]." ".$lehrer[$nr]["korrekturkurs"][$j]["fach"].'">'."\n";
			echo $lehrer[$nr]["korrekturkurs"][$j]["klasse"]." ".$lehrer[$nr]["korrekturkurs"][$j]["fach"].":\n";
			echo '<select id="wieoft'.$j.'" name="häufigkeit'.$j.'" class="häufigkeit">
					<option value="0">--Häufigkeit:--</option>
					<option value="1">1x im Halbjahr</option>
					<option value="2">2x im Halbjahr</option>
					<option value="3">3x im Halbjahr</option>
					<option value="a">andere Eingabe machen...</option>
					<option value="n">Diesen Kurs habe ich nicht.</option>
				  </select>'."\n";
			echo '<div id="normal'.$j.'">
					<select name="dauer'.$j.'">
						<option value="0">--Dauer:--</option>
						<option value="1">1 Schulstunde</option>
						<option value="60">60 min.</option>
						<option value="2">2 Schulstunden</option>
						<option value="3">3 Schulstunden</option>
						<option value="4">4 Schulstunden</option>
						<option value="5">5 Schulstunden</option>
						<option value="a">andere Eingabe machen</option>
					</select>'."\n";

			echo '<label for="schreiber">Anzahl schreibende Schüler: <input id="schreiber" type="number" name="schreiber'.$j.'"></label>'."\n";
			echo '<textarea class="bemerkung" placeholder="ggf. Bemerkung" name="bemerkung'.$j.'"></textarea>';
			echo '</div>'."\n";
			echo '<div class="andereeingabe" id="anders'.$j.'" style="display:none;"><textarea class="andereeingabe" name="andereeingabe'.$j.'"
				placeholder="Der Kurs lässt sich nicht mit den angebotenen Vorgaben erfassen? Geben Sie hier als Prosa Ihre Informationen zu den Korrekturen in diesem Kurs ein."></textarea>
			</div>'."\n";
			echo '<div class="nicht" id="nicht'.$j.'" style="display:none;"><textarea class="nicht" name="nicht'.$j.'" placeholder="ggf. Erläuterung"></textarea></div>'."\n";
			echo '</div>';
		} // einzelne Korrekturkurse $j durchlaufen Ende.
		// Klassenleitung:
		echo '<div class="rahmen">
				Haben Sie in diesem Halbjahr eine Klassenleitung?
				<label for="klassenein"><input type="radio" id="klassenein" value="nein" name="klassenleitung_janein">Nein</label>
				<label for="klasseja"  ><input type="radio" id="klasseja"   value="ja"   name="klassenleitung_janein">Ja  </label><br>
				<div id="klassenleitung">
					Klasse: <input type="text" id="klasse" name="klassenleitung_klasse">
					<label for="klassenleitung_1"><input type="radio" id="klassenleitung_1" value="1" name="klassenleitung_nr">1. Klassenleitung</label>
					<label for="klassenleitung_2"><input type="radio" id="klassenleitung_2" value="2" name="klassenleitung_nr">2. Klassenleitung</label><br>';
			// alt:	<select name="klassenleitung_nr"><option value="1">1. Klassenleitung</option><option>2. Klassenleitung</option></select><br>
		echo		'Wie teilt sich das Klassenteam die Punkte auf? (ohne Angabe gilt: 1. Klassenleitung: 80 Punkte, 2. Klassenleitung: 40 Punkte)
					<input type="text" name="klassenleitung_aufteilung">
				</div>
			</div>';
		// zusätzliches Kommentarfeld:
		echo '<div class="rahmen">
				Kommentarfeld: Hier ist noch Platz für zusätzliche Erläuterungen (z.B.: Fehlt hier oben ein Korrekturkurs? Dann hier unten bitte per Hand alles Notwendige eintragen.
				Waren Sie in diesem Halbjahr beurlaubt, z.B. in Elternzeit, von wann bis wann? Haben Sie zusätzliche Korrekturen für jemand anders übernommen? etc.)<br>
				<textarea id="kommentarfeld" placeholder="zusätzl. Kommentarfeld" name="kommentar"></textarea>
			</div>';
		echo '<div class="rahmen">Bitte vergewissern Sie sich, dass alle Eintragungen richtig sind, denn sie werden
					beim Klicken auf "Absenden" sofort abgespeichert: <button>Absenden!</button><br>
					<div style="font-size:0.8em;">Oder: <a href="index.php?logout=1">Logout ohne Speichern</a></div>
					</div>'."\n";
		echo "</form>\n";
		echo '<div style="font-size:0.8em;">* Hinweise zur Oberstufe:<br>
				- Es werden keine Kursarten GK/LK und Kursnummern mit angezeigt. Wenn Sie zwei Grundkurse bzw. einen LK und einen GK des gleichen Fachs in derselben Stufe haben,
				  können Sie sich aussuchen, welche Zahlen Sie bei welchem Kurs eintragen.<br>
				- Außerdem können vereinzelt Kurse mit angezeigt werden, in denen kein/e Schüler/in das Fach schriftlich gewählt hat. Dann einfach 0 eintragen oder
				  einen Kommentar o.Ä.</div>'."\n\n";
			echo '<script>
					// Kurse: Anzeige bei "andere Eingabe machen..." ändern:
					var elems = document.getElementsByClassName("häufigkeit");
					for (i = 0; i < elems.length; i++)	{
						var elem = elems[i];
						elem.addEventListener("change", function(index) { return function() { reagieren(index); } }(i) );
					}
					function reagieren(nr) {
							var x = document.getElementsByClassName("häufigkeit")[nr].value;
							if (x == "a")		{	document.getElementById("normal"+nr).style.display = "none";
													document.getElementById("anders"+nr).style.display = "inline-block";
													document.getElementById("nicht"+nr).style.display = "none";			}
							else if (x == "n")	{ 	document.getElementById("normal"+nr).style.display = "none";
													document.getElementById("anders"+nr).style.display = "none";
													document.getElementById("nicht"+nr).style.display = "inline-block";	}
							else				{ 	document.getElementById("normal"+nr).style.display = "inline-block";
													document.getElementById("anders"+nr).style.display = "none";
													document.getElementById("nicht"+nr).style.display = "none";		}	
					}
					
					// Klassenleitung: Anzeige bei "Ja"/"Nein" ändern:
					var klassenein = document.getElementById("klassenein");
					var klasseja   = document.getElementById("klasseja");
					
					klassenein.addEventListener("change", einausblenden);
					  klasseja.addEventListener("change", einausblenden);
					
					function einausblenden() {
						var y = document.getElementById("klassenein").checked;
						var z = document.getElementById("klasseja").checked;
//						alert ("y = " + y + ", z = " + z);
						if (z == true)				{	document.getElementById("klassenleitung").style.display = "block";	}
						else						{ 	document.getElementById("klassenleitung").style.display = "none";	}
					}

				  </script>';
	}	// eindeutige Lehrenummer -> Formular Ende
} // Kürzel ausgewählt Ende



// Datenformular gesendet, Kontrolle zeigen:

/*		***deaktiviert, weil zu viel Programmiergefummel! ***

elseif (!isset($_GET["anmelden_mit_kuerzel"]) && isset($_POST["kuerzel"]))  {
	// Lehrernummer zum Kürzel im Array suchen (und dabei sichergehen, dass eine eindeutige Nummer gefunden wurde):
	$lnummern = array_keys(array_combine(array_keys($lehrer), array_column($lehrer, 'kuerzel')), $_POST["kuerzel"]);
	if (count($lnummern)>1) { echo 'Fehler: Kuerzel doppelt vorhanden?'; }
	elseif (count($lnummern) == 0) { echo 'Fehler: Kuerzel nicht vorhanden?'; }
	else { // eindeutige Lehrernummer:
	$lnumber = $lnummern[0];

$translate = array ("kuerzel" => "Lehrer", "häufigkeit" => "Häufigkeit", "dauer" => "Dauer", "schreiber" => "Anzahl Schreiber", "bemerkung" => "Bemerkung", "andereeingabe" => "andere Eingabe", "nicht" => "Diesen Kurs habe ich nicht.",
					"klassenleitung_klasse" => "Klassenleitung Klasse", "klassenleitung_nr" => "", "klassenleitung_aufteilung" => "Aufteilung", "kommentar" => "ggf. zusätzl. Kommentar");
echo '<form>';
foreach ($_POST as $key => $value) {  // ALLE übergebenen Formulardaten durchlaufen
$knumber = -1;
if (ctype_digit(substr($key, -1))) { $feldart = substr($key, 0, -1); $knumber = substr($key, -1); } else { $feldart = $key; }
if (strpos("(häufigkeit|klassenleitung_klasse|kommentar)", $feldart)) { echo "\n".'<div class="rahmen">'.$lehrer[$lnumber]["korrekturkurs"][$knumber]["klasse"]." ".$lehrer[$lnumber]["korrekturkurs"][$knumber]["fach"]."\n"; }
echo strtr($feldart, $translate).": ";
echo '<input type="text" class="readonly" readonly name="'.$key.'" value="'.$value.'">'."\n";
if (strpos("(nicht|klassenleitung_aufteilung|kommentar)", $feldart)) { echo '</div>'."\n"; }
// echo $feldart.(($number>=0) ? "_".$number : "").": ".$value."<br>\n";
}
echo '</form>';
// echo "<pre>";
// var_dump($_POST);
// echo "</pre>";
} // eindeutige Lehrernummer Ende
} // Datenformular gesendet, Kontrolle zeigen Ende

*/


// erhaltene Daten abspeichern:
elseif (!isset($_GET["anmelden_mit_kuerzel"]) && isset($_POST["kuerzel"]))  {
/*	// Lehrernummer zum Kürzel im Array suchen (und dabei sichergehen, dass eine eindeutige Nummer gefunden wurde):
	$lnummern = array_keys(array_combine(array_keys($lehrer), array_column($lehrer, 'kuerzel')), $_POST["kuerzel"]);
	if (count($lnummern)>1) { echo 'Fehler: Kuerzel doppelt vorhanden?'; }
	elseif (count($lnummern) == 0) { echo 'Fehler: Kuerzel nicht vorhanden?'; }
	else { // eindeutige Lehrernummer:
	$lnumber = $lnummern[0]; */

$zeile = array();
foreach ($_POST as $key => $value) {  // ALLE übergebenen Formulardaten durchlaufen
$knumber = -1;
if (ctype_digit(substr($key, -1))) { $feldart = substr($key, 0, -1); $knumber = substr($key, -1); } else { $feldart = $key; }
// if ($feldart == "häufigkeit") { $zeile[] = "korrekturkurs".$knumber."\t".$lehrer[$lnumber]["korrekturkurs"][$knumber]["klasse"]." ".$lehrer[$lnumber]["korrekturkurs"][$knumber]["fach"]."\n"; }
$zeile[] = $key."\t".$value."\n";
}
$dateinamevollerpfad = $_SERVER["DOCUMENT_ROOT"].'untis/rstkxzw3/zfweo87/2018-2019.1.roh/'.$_POST["kuerzel"];
if (file_exists($dateinamevollerpfad.'.txt')) { $dateinamevollerpfad = $dateinamevollerpfad.'.'.date("Y-m-d-H-i-s"); }
$dateinamevollerpfad = $dateinamevollerpfad.'.txt';
$datei = fopen($dateinamevollerpfad,"w");                // und so abspeichern
fwrite($datei, implode($zeile));
fclose($datei);
// Und zur Info zeigen, was abgespeichert wurde:

$translate = array ("kuerzel" => "Lehrer", "korrekturkurs" => "Klasse/Kurs", "häufigkeit" => "Häufigkeit", "dauer" => "Dauer", "schreiber" => "Anzahl Schreiber", "bemerkung" => "Bemerkung", "andereeingabe" => "andere Eingabe",
					"nicht" => "(ggf. Bemerkung falscher Kurs)", "klassenleitung_janein" => "Klassenleitung",
					"klassenleitung_klasse" => "Klassenleitung Klasse", "klassenleitung_nr" => "1./2. Klassenl.", "klassenleitung_aufteilung" => "Aufteilung", "kommentar" => "ggf. zusätzl. Kommentar");
echo 'Diese Eingaben wurden gespeichert:'."\n";
foreach ($_POST as $key => $value) {  // ALLE übergebenen Formulardaten durchlaufen
$knumber = -1;
if (ctype_digit(substr($key, -1))) { $feldart = substr($key, 0, -1); $knumber = substr($key, -1); } else { $feldart = $key; }
if (strpos("(kuerzel|korrekturkurs|klassenleitung_janein|klassenleitung_klasse|kommentar)", $feldart)) { echo "\n".'<div class="rahmen">'; }
// if ($feldart == "häufigkeit") { echo '<div class="readonly">Klasse/Kurs: '.$lehrer[$lnumber]["korrekturkurs"][$knumber]["klasse"]." ".$lehrer[$lnumber]["korrekturkurs"][$knumber]["fach"]."</div>\n"; }
echo '<div class="readonly">'.strtr($feldart, $translate).": ";
echo $value."</div>\n";
if (strpos("(kuerzel|nicht|klassenleitung_janein|klassenleitung_aufteilung|kommentar)", $feldart)) { echo '</div>'."\n"; }

} // Formulardaten durchlaufen Ende
echo '<a href="index.php?logout=1">Logout</a>';
// } // eindeutige Lehrernummer Ende
} // erhaltene Daten abspeichern Ende

} // else (nicht GET_tabelle=1)

} // wenn eingeloggt

else
{?>

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