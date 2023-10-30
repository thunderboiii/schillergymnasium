<?php
if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){							// Sicherstellen, dass Übertragung verschlüsselt ist
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}


// zentrale Einstellung: WELCHES HALBJAHR?
$halbjahr = array( 	'nr'		=>	'2',
					'kurz'		=>	'201819b',
					'mittel' 	=>	'2018-2019.2',
					'lang'		=>	'2018/19, <u>2. Halbjahr</u>');

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
    <title>Schiller&shy;gymnasium Anrechnungs&shy;stunden 2018/19.1</title>
  </head>
  <body>
  <h1 id="logo"><a href="./index.php">Schiller&shy;gymnasium Anrechnungs&shy;stunden <?php echo $halbjahr['lang']; ?></a></h1>

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

// UV einlesen (enthält Kollegen und Kurse):
$dateinameuv = 'UV'.$halbjahr['kurz'].'.txt';
$db = file($dateinameuv);
// ABI einlesen (enthält Abi-Korrekturen):
$dateinameabi = 'UV'.$halbjahr['kurz'].'ABI.txt';
if (file_exists($dateinameabi)) {
	$db2 = file($dateinameabi);
	for ($i = 0; $i < count($db2); $i++) {
		$zeile = $db2[$i];
		$zeile = explode("\t", $zeile);
		$zeile = array_map('trim', $zeile);
		$db2[$i] = $zeile;
	}
}
else {
	$db2 = false;
}// print_r($db2);

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
		if (($aktueller_kurs_roh[0] != 'Q2') || ($halbjahr['nr'] != '2')) {
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
		}
		unset($aktueller_kurs_roh);
	}
	unset($aktueller_lehrer_roh);
	$abi_erst	= array_keys(array_column($db2, 2), $lehrer[$i]['kuerzel']);
	$abi_zweit	= array_keys(array_column($db2, 4), $lehrer[$i]['kuerzel']);
	$index_beginnen_bei = count($lehrer[$i]["korrekturkurs"]);
	for ($l = 0; $l < count($abi_erst); $l++) {
		$lehrer[$i]['abi_erst'][$index_beginnen_bei + $l] = array (
			'klasse'	=>	'Q2',
			'fach'		=>	$db2[$abi_erst[$l]][1],
			'erstzweit'	=>	'erst',
			'kursart'	=>	$db2[$abi_erst[$l]][0],
			'anzahl'	=>	$db2[$abi_erst[$l]][6]
		);
	}
	$index_beginnen_bei = count($lehrer[$i]["korrekturkurs"]) + count($lehrer[$i]['abi_erst']);
		for ($l = 0; $l < count($abi_zweit); $l++) {
		$lehrer[$i]['abi_zweit'][$index_beginnen_bei + $l] = array (
			'klasse'	=>	'Q2',
			'fach'		=>	$db2[$abi_zweit[$l]][1],
			'erstzweit'	=>	'zweit',
			'kursart'	=>	$db2[$abi_zweit[$l]][0],
			'anzahl'	=>	$db2[$abi_zweit[$l]][6],
			'kurs_von'	=>	str_replace('ext', 'extern', $db2[$abi_zweit[$l]][2])
		);
	}
	//print_r($abi_erst);
}//print_r($lehrer);

$keineanrechnung = array("PER", "NN1", "NN2", "NN3", "NN4", "ALB", "FEG", "BNA", "NOL", "BÜR", "KIA", "UH");
// 2018-2018.1: $elternzeit = array("AT", "KN", "RAU");
// 2018-2018.2:
$elternzeit = array("AT", "HDN", "KN", "RAU", "STP");
// Kontroll-Tabelle ausgeben, in der alle eingelesenen Daten mit Kursen und Korrekturkursen stehen:
// * dabei von vornherein auschließen: [(alle, die _keinen_ Korrekturkurs haben)Nein, zulassen wg. Klassenleitung] Vertretungslehrer und Sonderpädagogen:
if (isset($_GET["tabelle"]) && ($_GET["tabelle"])) {  // d.h. $_GET["tabelle"] == 1 oder == "txt" (daraus später Fallunterscheidung 2a vs 2b)
	echo '<table>';
	// 1. Daten vorbereiten:
	for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
		if (/*!empty($lehrer[$i]["korrekturkurs"]) && */!in_array($lehrer[$i]["kuerzel"], array_merge($keineanrechnung, $elternzeit)))	{ $lehrer[$i]["korrekturlehrer"] = TRUE;  }	// * [(alle, die _keinen_ Korrekturkurs haben)Nein, zulassen wg. Klassenleitung] Vertretungslehrer und Sonderpädagogen
																														else			{ $lehrer[$i]["korrekturlehrer"] = FALSE; }
		$pfad_zur_einzelnen_eventuellen_Kollegen_Eintragungsdatei = $_SERVER["DOCUMENT_ROOT"].'untis/rstkxzw3/zfweo87/2018-2019.2.bearbeitet/'.$lehrer[$i]["kuerzel"].'.txt';
		if (file_exists($pfad_zur_einzelnen_eventuellen_Kollegen_Eintragungsdatei))														{ $lehrer[$i]["eingetragen"] = TRUE;  }
																														else			{ $lehrer[$i]["eingetragen"] = FALSE; }
		if (isset($lehrer[$i]['abi_erst'])) {
			$lehrer[$i]["korrekturkurs"] = $lehrer[$i]["korrekturkurs"] + $lehrer[$i]['abi_erst'];
		}
		if (isset($lehrer[$i]['abi_zweit'])) {
			$lehrer[$i]["korrekturkurs"] = $lehrer[$i]["korrekturkurs"] + $lehrer[$i]['abi_zweit'];
		}
		// debug: if ($lehrer[$i]["kuerzel"] == 'AC') { var_dump($lehrer[$i]["abi_erst"]);exit(); }
		
		if ($lehrer[$i]["eingetragen"])	{
			$lehrer[$i]["Eintragungen"] = file($pfad_zur_einzelnen_eventuellen_Kollegen_Eintragungsdatei);
			// 'normale' Korrekturkurse:
			for ($j = 0; $j < count($lehrer[$i]["korrekturkurs"]); $j++) {	// passend zu den Elementen von $lehrer[$i]["korrekturkurs"] durch die Datei [Kürzel].txt gehen und Daten 'einsammeln':
				$j_zeile = $j*7 + 1;
				for ($k = 0; $k < 7; $k++) {
					$eintragung = $lehrer[$i]["Eintragungen"][$j_zeile + $k];
					$eintragung = explode("\t", $eintragung);
					
					if (isset($lehrer[$i]["korrekturkurs"][$j]['erstzweit'])) {
						
						// Häufigkeit => stattdessen Dauer: (vertauscht bei Abi)
						$suchen = array('normalGK', 'normalLK');
						$ersetzen = array('3,5', '5');
						$eintragung[1] = str_replace($suchen, $ersetzen, $eintragung[1]);
						
						// Dauer => stattdessen Häufigkeit: (vertauscht bei Abi)
						$suchen = 'Abi';
						if ($lehrer[$i]["korrekturkurs"][$j]['erstzweit'] == 'erst')	{	$ersetzen = '2';	}
						if ($lehrer[$i]["korrekturkurs"][$j]['erstzweit'] == 'zweit')	{	$ersetzen = '0,5';	}
						$eintragung[1] = str_replace($suchen, $ersetzen, $eintragung[1]);
						
					}
					
					$lehrer[$i]["Eintragungen"][$j_zeile + $k] = array ( "label" =>	trim($eintragung[0]),
																		 "wert"  =>	trim($eintragung[1])	);
				}
			}
		/*	// Abi-Erstkorektur:
			for ($j = count($lehrer[$i]["korrekturkurs"]); $j < (count($lehrer[$i]["korrekturkurs"])+count($lehrer[$i]["abi_erst"])); $j++) {	// passend zu den Elementen von $lehrer[$i]["korrekturkurs"] durch die Datei [Kürzel].txt gehen und Daten 'einsammeln':
				$j_zeile = $j*7 + 1;
				for ($k = 0; $k < 7; $k++) {
					$eintragung = $lehrer[$i]["Eintragungen"][$j_zeile + $k];
					$eintragung = explode("\t", $eintragung);
					$lehrer[$i]["Eintragungen"][$j_zeile + $k] = array ( "label" =>	trim($eintragung[0]),
																		 "wert"  =>	trim($eintragung[1])	);
				}
			} */

			// Klassenleitung und Kommentar: nach letztem Korrekturkurs (for j maximal + for k maximal) ab der nächsten Zeile (+1):
			
			$KL_ab_zeile = $j_zeile + $k;
			$letzte_zeile = count($lehrer[$i]["Eintragungen"]);
			// eingelesen und folgendermaßen benannt werden sollen:
			$labels = array ("klassenleitung_janein", "klassenleitung_klasse", "klassenleitung_nr", "klassenleitung_aufteilung", "kommentar"); // alt: array ("Klassenlehrer", "Klasse", "1oder2");
			for ($j = $KL_ab_zeile; $j < $letzte_zeile; $j++) {
				$eintragung = trim($lehrer[$i]["Eintragungen"][$j]);
				$eintragung = explode("\t", $eintragung);
				if (!empty(trim($eintragung[0])) && in_array(trim($eintragung[0]), $labels) && (count($eintragung) > 1)) {
					$lehrer[$i]["Eintragungen"][trim($eintragung[0])] = trim($eintragung[1]);
				}
			}
		}
	}
	// 2. Daten anzeigen:
	$style_aktiv = "border:1px solid black;";
	$style_hervorgehoben = "border:1px solid black;background-color:#ffa;";
	$style_inaktiv = "border:1px solid #888;color:#888;background-color:#ddd;";
	// 2a) normale Tabelle ("&tabelle=1") zum Lesen (inkl. aller Eintragungen):
	if ($_GET["tabelle"] == 1) {
		for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
			if ($lehrer[$i]["korrekturlehrer"]) { $style = $style_aktiv; } else {$style = $style_inaktiv; }
			echo '<tr>';
			echo "<td style=\"$style\">".$lehrer[$i]["kuerzel"]."</td>\n";
			echo "<td style=\"$style\">".$lehrer[$i]["stelle"]."</td>\n";
			echo "<td style=\"$style\">".$lehrer[$i]["uebr_entlastung"]."</td>\n";
			echo "<td style=\"$style\">".$lehrer[$i]["stunden_soll"]."</td>\n";
			for ($j = 0; $j < count($lehrer[$i]["kurs"]); $j++) {			 // Elementweise durch array $lehrer[$i]["kurs"] gehen
				echo "<td style=\"$style\">".$lehrer[$i]["kurs"][$j]["klasse"]." ".$lehrer[$i]["kurs"][$j]["fach"]."</td>";
			}
			echo "<td style=\"$style\">".$lehrer[$i]["stunden_ist"]."</td>\n";
			echo '</tr>';
			echo '<tr>';
			echo '<td style="text-align:right;" colspan="4">davon Korrekturkurse:</td>'."\n";
			for ($j = 0; $j < count($lehrer[$i]["korrekturkurs"]); $j++) {	// Elementweise durch array $lehrer[$i]["korrekturkurs"] gehen
				echo "<td style=\"$style\">".$lehrer[$i]["korrekturkurs"][$j]["klasse"]." ".$lehrer[$i]["korrekturkurs"][$j]["fach"]."</td>\n";
			}
			echo '</tr>'."\n";
			if ($lehrer[$i]["eingetragen"])	{
				echo '<tr><td style="text-align:right;" colspan="4">dazu eingetragen:</td>';
				for ($j = 0; $j < count($lehrer[$i]["korrekturkurs"]); $j++) {	// passend zu den Elementen von $lehrer[$i]["korrekturkurs"] durch die eingelesenen Eintragungen (aus Datei [Kürzel].txt s.o.) gehen:
					echo "<td style=\"$style\">";
					$j_zeile = $j*7 + 1;
					echo $lehrer[$i]["Eintragungen"][$j_zeile + 1]["wert"]." x ".$lehrer[$i]["Eintragungen"][$j_zeile + 2]["wert"]." x ".$lehrer[$i]["Eintragungen"][$j_zeile + 3]["wert"];
					echo "</td>\n";
				}
				echo "</tr>\n";
				echo '<tr><td style="text-align:right;" colspan="4">alternativ:</td>';
				for ($j = 0; $j < count($lehrer[$i]["korrekturkurs"]); $j++) {	// passend zu den Elementen von $lehrer[$i]["korrekturkurs"] durch die Datei [Kürzel].txt gehen:
					echo "<td style=\"$style\">";
					$j_zeile = $j*7 + 1;
					for ($k = 4; $k < 7; $k++) {
						if ($lehrer[$i]["Eintragungen"][$j_zeile + $k]["wert"]) { echo $lehrer[$i]["Eintragungen"][$j_zeile + $k]["wert"]; }
						if (($k < 6) && ($lehrer[$i]["Eintragungen"][$j_zeile + $k]["wert"]) && ($lehrer[$i]["Eintragungen"][$j_zeile + $k + 1]["wert"])) { echo ", "; }
					// echo $lehrer[$i]["Eintragungen"][$j_zeile + $k]["wert"].", ".$lehrer[$i]["Eintragungen"][$j_zeile + 5]["wert"].", ".$lehrer[$i]["Eintragungen"][$j_zeile + 6]["wert"];
					}
					echo "</td>\n";
				}
			}
			elseif ($lehrer[$i]["korrekturlehrer"]) {  		// das könnte auch wahr sein, wenn kein Korrekturlehrer, sondern nur Klassenlehrer (wie DZ)...
			echo '<tr>
					<td colspan="4">&nbsp;</td>';
				if (count($lehrer[$i]["korrekturkurs"])) {	// ...daher so noch unterscheiden.
					$colspan = count($lehrer[$i]["korrekturkurs"]);
					echo '<td style="text-align:left;background:#fdd;color:#975;" colspan="'.$colspan.'">nichts eingetragen, obwohl Korrekturlehrer (und evtl. Klassenlehrer)</td>';
				}
				else {
					$colspan = count($lehrer[$i]["kurs"]) + 1;
					echo '<td style="text-align:left;background:#ffe0b3;color:#876;" colspan="'.$colspan.'">nichts eingetragen, obwohl evtl. Klassenlehrer</td>';
				}
			}
			echo '</tr>'."\n".'<tr><td>&nbsp;</td></tr>'."\n";
		}
	}
	// 2b) Tabelle für Copy & Paste (über notepad) => Text zum Einfügen in Excel-Datei "Berechnung":
	if ($_GET["tabelle"] == "txt") {
		for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
			if (!in_array($lehrer[$i]["kuerzel"], $keineanrechnung)) {	// nur potentielle Korrekturlehrer/Klassenlehrer (also keine Sonderpädagogen, Referendare, Vertretungslehrer) anzeigen
				$style = $style_aktiv;
				echo '<tr>';
				echo "<td style=\"$style\">".$lehrer[$i]["kuerzel"]."</td>\n";
				if ($lehrer[$i]["eingetragen"])	{
					for ($j = 0; $j < count($lehrer[$i]["korrekturkurs"]); $j++) {	// passend zu den Elementen von $lehrer[$i]["korrekturkurs"] durch die Datei [Kürzel].txt gehen:
						$j_zeile = $j*7 + 1;
						echo "<td style=\"${style}font-size:50%;\">".$lehrer[$i]["Eintragungen"][$j_zeile]["wert"]."</td>\n";
						echo "<td style=\"${style}width:3em;\">".$lehrer[$i]["Eintragungen"][$j_zeile + 1]["wert"]."</td>\n";
						if ($lehrer[$i]["Eintragungen"][$j_zeile + 2]["wert"] == 60) { $lehrer[$i]["Eintragungen"][$j_zeile + 2]["wert"] = "1,333"; }
						echo "<td style=\"${style}width:3em;\">".$lehrer[$i]["Eintragungen"][$j_zeile + 2]["wert"]."</td>\n";
						echo "<td style=\"${style}width:3em;\">".$lehrer[$i]["Eintragungen"][$j_zeile + 3]["wert"]."</td>\n";
					}
					echo "</tr>\n";
				}
				elseif ($lehrer[$i]["korrekturlehrer"]) {  		// das könnte auch wahr sein, wenn kein Korrekturlehrer, sondern nur Klassenlehrer (wie DZ)...
					if (count($lehrer[$i]["korrekturkurs"])) {	// ...daher so noch unterscheiden.
						echo '<td style="text-align:left;background:#fdd;color:#975;" colspan="12">nichts eingetragen, obwohl Korrekturlehrer (und evtl. Klassenlehrer)</td>';
					} else {
						echo '<td style="text-align:left;background:#ffe0b3;color:#876;" colspan="12">nichts eingetragen, obwohl evtl. Klassenlehrer</td>';
					}
				}
			}
		}
	}
	// 2c) Tabelle Klassenleitungen (für Copy & Paste):
	if ($_GET['tabelle'] == 'KL') {
		for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
			if (!in_array($lehrer[$i]["kuerzel"], $keineanrechnung)) {	// nur potentielle Korrekturlehrer/Klassenlehrer (also keine Sonderpädagogen, Referendare, Vertretungslehrer) anzeigen
				$style = $style_aktiv;
				if (isset($lehrer[$i]["Eintragungen"]["klassenleitung_janein"]) && ($lehrer[$i]["Eintragungen"]["klassenleitung_janein"] == "ja")) {
					$style = $style_hervorgehoben;
				}
				echo '<tr>'."\n";
				echo "<td style=\"$style\">".$lehrer[$i]["kuerzel"].'</td>'."\n";
				echo "<td style=\"${style}width:3em;\">";
				if (isset($lehrer[$i]["Eintragungen"]["klassenleitung_klasse"])) { echo $lehrer[$i]["Eintragungen"]["klassenleitung_klasse"]; }
				echo '</td>'."\n";
				echo "<td style=\"${style}width:3em;\">";
				if (isset($lehrer[$i]["Eintragungen"]["klassenleitung_nr"])) { echo $lehrer[$i]["Eintragungen"]["klassenleitung_nr"]; }
				echo '</td>'."\n";
				echo "<td style=\"${style}width:3em;\">";
				if (isset($lehrer[$i]["Eintragungen"]["klassenleitung_aufteilung"])) { echo $lehrer[$i]["Eintragungen"]["klassenleitung_aufteilung"]; }
				echo '</td>'."\n";
				echo "<td style=\"${style}width:3em;white-space:nowrap;font-size:80%;\">";
				if (isset($lehrer[$i]["Eintragungen"]["kommentar"])) { echo $lehrer[$i]["Eintragungen"]["kommentar"]; }
				echo '</td>'."\n";
				echo '</tr>'."\n";
			}
		}
	}
	// 2d) Tabelle Klassenleitungen und Kommentare (alles außer den Korrekturkursen):
	if ($_GET["tabelle"] == "Rest") {
		for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
			if (!in_array($lehrer[$i]["kuerzel"], $keineanrechnung)) {	// nur potentielle Korrekturlehrer/Klassenlehrer (also keine Sonderpädagogen, Referendare, Vertretungslehrer) anzeigen
				$style = $style_aktiv;
				if (isset($lehrer[$i]["Eintragungen"]["klassenleitung_janein"]) && ($lehrer[$i]["Eintragungen"]["klassenleitung_janein"] == "ja")) {
					$style = $style_hervorgehoben;
				}
				echo '<tr>';
				echo "<td style=\"$style\">".$lehrer[$i]["kuerzel"]."</td>\n";
				for ($j = 0; $j < count($labels); $j++) {
					if (isset($lehrer[$i]["Eintragungen"][$labels[$j]])) {
						echo "<td style=\"${style}width:3em;\">".$lehrer[$i]["Eintragungen"][$labels[$j]]."</td>\n";
					}
				}
				// print_r($lehrer[$i]["Eintragungen"]);
				/*
				elseif ($lehrer[$i]["korrekturlehrer"]) {  		// das könnte auch wahr sein, wenn kein Klassenlehrer, aber Korrekturlehrer (wie DR)...
					if (count($lehrer[$i]["korrekturkurs"])) {	// ...daher so noch unterscheiden.
						echo '<td style="text-align:left;background:#fdd;color:#975;" colspan="12">nichts eingetragen, obwohl Korrekturlehrer (und evtl. Klassenlehrer)</td>';
					} else {
						echo '<td style="text-align:left;background:#ffe0b3;color:#876;" colspan="12">nichts eingetragen, obwohl evtl. Klassenlehrer</td>';
					}
				} */
			}
		}
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
					if (/*!empty($lehrer[$i]["korrekturkurs"]) && */!in_array($lehrer[$i]["kuerzel"], array_merge($keineanrechnung, $elternzeit)))	{
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
		echo "Nach dem Stand der vorliegenden Unterrichtsverteilung müsste es folgende Kurse mit Klassenarbeiten/Klausuren geben:<sup>1)</sup>\n";
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
		foreach ($lehrer[$nr]["abi_erst"] as $j => $kurs) {	// Elementweise durch array $lehrer[$nr]["abi_erst"] gehen
			echo '<div class="rahmen" id="abi_erst'.$j.'">';
			echo '<input type="hidden" name="abi_erst'.$j.'" value="'.$lehrer[$nr]["abi_erst"][$j]["klasse"]." ".$lehrer[$nr]["abi_erst"][$j]["fach"].'">'."\n";
			echo $lehrer[$nr]["abi_erst"][$j]["klasse"]." ".$lehrer[$nr]["abi_erst"][$j]["fach"].":\n";
			echo '<select id="wieoft'.$j.'" name="häufigkeit'.$j.'" class="häufigkeit">
					<option value="normal'.$lehrer[$nr]["abi_erst"][$j]["kursart"].'">1x Vorabi + 1x Abi-Erstkorrektur</option>
					<option value="a">andere Eingabe machen...</option>
					<option value="n">Diesen Kurs habe ich nicht.</option>
				  </select>'."\n";
			echo '<input type="hidden" name="dauer'.$j.'" value="Abi"';
			echo '<div id="normal'.$j.'">'."\n";
			echo '('.$lehrer[$nr]["abi_erst"][$j]["kursart"].')'."\n";

			echo '<label for="schreiber">Anzahl schreibende Schüler: <input id="schreiber" type="number" name="schreiber'.$j.'" value="'.$lehrer[$nr]["abi_erst"][$j]["anzahl"].'"></label>'."\n";
			echo '<textarea class="bemerkung" placeholder="ggf. Bemerkung" name="bemerkung'.$j.'"></textarea>';
			echo '</div>'."\n";
			echo '<div class="andereeingabe" id="anders'.$j.'" style="display:none;"><textarea class="andereeingabe" name="andereeingabe'.$j.'"
				placeholder="Der Kurs lässt sich nicht mit den angebotenen Vorgaben erfassen? Geben Sie hier als Prosa Ihre Informationen zu den Korrekturen in diesem Kurs ein."></textarea>
			</div>'."\n";
			echo '<div class="nicht" id="nicht'.$j.'" style="display:none;"><textarea class="nicht" name="nicht'.$j.'" placeholder="ggf. Erläuterung"></textarea></div>'."\n";
			echo '</div>';
		} // einzelne Abi-Erstkorrekturen $j durchlaufen Ende.
		foreach ($lehrer[$nr]["abi_zweit"] as $j => $kurs) {	// Elementweise durch array $lehrer[$nr]["abi_zweit"] gehen
			echo '<div class="rahmen" id="abi_zweit'.$j.'">';
			echo '<input type="hidden" name="abi_zweit'.$j.'" value="'.$lehrer[$nr]['abi_zweit'][$j]['klasse'].' '.$lehrer[$nr]['abi_zweit'][$j]['fach'].' von '.$lehrer[$nr]['abi_zweit'][$j]['kurs_von'].'">'."\n";
			echo $lehrer[$nr]["abi_zweit"][$j]["klasse"]." ".$lehrer[$nr]["abi_zweit"][$j]["fach"].":\n";
			echo '<select id="wieoft'.$j.'" name="häufigkeit'.$j.'" class="häufigkeit">
					<option value="normal'.$lehrer[$nr]["abi_zweit"][$j]["kursart"].'">Abi-Zweitkorrektur</option>
					<option value="a">andere Eingabe machen...</option>
					<option value="n">Diesen Korrekturkurs habe ich nicht.</option>
				  </select>'."\n";
			echo '<input type="hidden" name="dauer'.$j.'" value="Abi"';
			echo '<div id="normal'.$j.'">'."\n";
			echo '('.$lehrer[$nr]["abi_zweit"][$j]["kursart"].' von '.$lehrer[$nr]["abi_zweit"][$j]["kurs_von"].')'."\n";

			echo '<label for="schreiber">Anzahl schreibende Schüler: <input id="schreiber" type="number" name="schreiber'.$j.'" value="'.$lehrer[$nr]["abi_zweit"][$j]["anzahl"].'"></label>'."\n";
			echo '<textarea class="bemerkung" placeholder="ggf. Bemerkung" name="bemerkung'.$j.'"></textarea>';
			echo '</div>'."\n";
			echo '<div class="andereeingabe" id="anders'.$j.'" style="display:none;"><textarea class="andereeingabe" name="andereeingabe'.$j.'"
				placeholder="Der Kurs lässt sich nicht mit den angebotenen Vorgaben erfassen? Geben Sie hier als Prosa Ihre Informationen zu den Korrekturen in diesem Kurs ein."></textarea>
			</div>'."\n";
			echo '<div class="nicht" id="nicht'.$j.'" style="display:none;"><textarea class="nicht" name="nicht'.$j.'" placeholder="ggf. Erläuterung"></textarea></div>'."\n";
			echo '</div>';
		} // einzelne Abi-Erstkorrekturen $j durchlaufen Ende.
		// Klassenleitung:
		echo '<div class="rahmen">
				Haben Sie in diesem Halbjahr eine Klassenleitung in der Sek.Ⅰ?<sup>2)</sup>
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
		echo '<div style="font-size:0.8em;"><sup>1)</sup> Hinweise zur Oberstufe:<br>
				- Es werden bis jetzt keine Kursarten GK/LK (außer im Abi) und keine Kursnummern mit angezeigt. Wenn Sie in einer Stufe zwei Kurse im gleichen Fach haben (z.B. Q1: Ku-LK1 und Ku-GK3),
				  können Sie sich aussuchen, welche Zahlen Sie bei welchem Kurs eintragen.<br>
				- Außerdem können vereinzelt Kurse mit angezeigt werden, in denen kein/e Schüler/in das Fach schriftlich gewählt hat. Dann einfach 0 eintragen oder
				  einen Kommentar o.Ä.<br>
				<sup>2)</sup> Unter Klassenleitung in diesem Formular müssen keine Stufenleitungen in der Oberstufe eingegeben werden, da diese aus einem anderen Topf entlastet werden
				  (direkt in der vorderen Entlastungs-Spalte in der \'Unterrichtsverteilung Lehrer\', schon vor der Entlastungsstundenberechnung,
				  die hier jetzt vorgenommen werden soll).</div>'."\n\n";

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
// feldart und ggf. Nummer aus key extrahieren: ("dauer4" -> "dauer", "4")
preg_match_all('/\d+/', $key, $matches, PREG_OFFSET_CAPTURE);
$gefundene_zahlen = $matches[0];
if (!empty($gefundene_zahlen)) {
	$letzte_gefundene_zahl = $gefundene_zahlen[(count($gefundene_zahlen) - 1)];
	$feldart = substr($key, 0, $letzte_gefundene_zahl[1]) ;
	$knumber = $letzte_gefundene_zahl[0];
}
else { $feldart = $key; }
// alt: if (ctype_digit(substr($key, -1))) { $feldart = substr($key, 0, -1); $knumber = substr($key, -1); } else { $feldart = $key; }
// if ($feldart == "häufigkeit") { $zeile[] = "korrekturkurs".$knumber."\t".$lehrer[$lnumber]["korrekturkurs"][$knumber]["klasse"]." ".$lehrer[$lnumber]["korrekturkurs"][$knumber]["fach"]."\n"; }
$value = preg_replace('/[\n\r]/', '/', $value);
$zeile[] = $key."\t".$value."\n";
}
$dateinamevollerpfad = $_SERVER["DOCUMENT_ROOT"].'untis/rstkxzw3/zfweo87/2018-2019.2.roh/'.$_POST["kuerzel"];
if (file_exists($dateinamevollerpfad.'.txt')) { $dateinamevollerpfad = $dateinamevollerpfad.'.'.date("Y-m-d-H-i-s"); }
$dateinamevollerpfad = $dateinamevollerpfad.'.txt';
$datei = fopen($dateinamevollerpfad,"w");                // und so abspeichern
fwrite($datei, implode($zeile));
fclose($datei);
// Und zur Info zeigen, was abgespeichert wurde:

$translate = array ("kuerzel" => "Lehrer", "korrekturkurs" => "Klasse/Kurs", "häufigkeit" => "Häufigkeit", "dauer" => "Dauer", "schreiber" => "Anzahl Schreiber", "bemerkung" => "Bemerkung", "andereeingabe" => "andere Eingabe",
					"nicht" => "(ggf. Bemerkung falscher Kurs)", "klassenleitung_janein" => "Klassenleitung", 'abi_erst' => 'Vorabi + Abi-Erstkorrektur', 'abi_zweit' => 'Abi-Zweitkorrektur',
					"klassenleitung_klasse" => "Klassenleitung Klasse", "klassenleitung_nr" => "1./2. Klassenl.", "klassenleitung_aufteilung" => "Aufteilung", "kommentar" => "ggf. zusätzl. Kommentar");
echo 'Diese Eingaben wurden gespeichert:'."\n";
foreach ($_POST as $key => $value) {  // ALLE übergebenen Formulardaten durchlaufen
$knumber = -1;
// feldart und ggf. Nummer aus key extrahieren: ("dauer4" -> "dauer", "4")
preg_match_all('/\d+/', $key, $matches, PREG_OFFSET_CAPTURE);
$gefundene_zahlen = $matches[0];
if (!empty($gefundene_zahlen)) {
	$letzte_gefundene_zahl = $gefundene_zahlen[(count($gefundene_zahlen) - 1)];
	$feldart = substr($key, 0, $letzte_gefundene_zahl[1]) ;
	$knumber = $letzte_gefundene_zahl[0];
}
else { $feldart = $key; }

// alt; if (ctype_digit(substr($key, -1))) { $feldart = substr($key, 0, -1); $knumber = substr($key, -1); } else { $feldart = $key; }

if (strpos("(kuerzel|korrekturkurs|klassenleitung_janein|klassenleitung_klasse|kommentar|abi_erst|abi_zweit)", $feldart)) { echo "\n".'<div class="rahmen">'; }
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