<?php
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off") {							// Sicherstellen, dass Übertragung verschlüsselt ist
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}

session_save_path('/is/htdocs/wp12599526_UWBR3PUPTC/www/untis/rstkxzw3/sessions');
session_start();


// zentrale Einstellung: WELCHES HALBJAHR?

	// Infos sammeln:

if (isset($_GET['halbjahr'])	 && (($_GET['halbjahr'] === '1')	 || ($_GET['halbjahr'] === '2'	 )))	{	$halbjahr_get = $_GET['halbjahr'];			}
																									else	{	$halbjahr_get = false;						}

if (isset($_SESSION['halbjahr']) && (($_SESSION['halbjahr'] === '1') || ($_SESSION['halbjahr'] === '2')))	{	$halbjahr_session = $_SESSION['halbjahr'];	}
																									else	{	$halbjahr_session = false;					}

	// Halbjahr entscheiden:

$halbjahr_nr = '1';	// default  (= Priorität 3)
if ($halbjahr_session)	$halbjahr_nr = $halbjahr_session;	// überschreibe, wenn schon in der session gesetzt (= Priorität 2)
if ($halbjahr_get)	 	$halbjahr_nr = $halbjahr_get;		// überschreibe, wenn get gesetzt (= Priorität 1!)

	// Ergebnis in Session setzen

$_SESSION['halbjahr'] = $halbjahr_nr;

$schuljahr = array( 'human'		=>	'2019/20',
					'pfad'		=>	'2019-20');
$halbjahr = array( 	'nr'		=>	$halbjahr_nr,
					'kurz'		=>	$halbjahr_nr,
					'mittel' 	=>	$halbjahr_nr,
/*deprecated: */	'lang'		=>	$schuljahr['human'].', <u>'.$halbjahr_nr.'. Halbjahr</u>');

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
    <link rel="stylesheet" href="../styles.css" type="text/css">
	<script type="text/javascript" src="script.js" charset="utf-8"></script>
    <title>Schillergy. Anrechnung <?php echo $schuljahr['human'].'.'.$halbjahr['nr']; ?></title>
  </head>
  <body onload="init();">
  <h1 id="logo">
	<a href="./index.php">Schiller&shy;gymnasium Anrechnungs&shy;stunden <?php echo $schuljahr['human']; ?>:<br>
		<b><u style="color:#d2b02a;"><?php echo $halbjahr_nr; ?>. Halbjahr</u></b>
	</a>
</h1>

<?php

if ((isset($_SESSION["log"])) && ($_SESSION["log"] == 1)) {

class Lehrer {
	public $kuerzel;
	public $stellenumfang;
	public $uebr_entlastung;
}

// String in Zahl (float) umwandeln:
function num_from_str(string $number, $dec_point = null) {
    /* alt: if (empty($dec_point)) {
        $locale = localeconv();
        $dec_point = $locale['decimal_point'];
    }*/
	// Minuszeichen in ASCII-Minuszeichen umwandeln:
	$number = str_replace('–', '-', $number); // 2013 => 002D
	$number = str_replace('−', '-', $number); // 2212 => 002D
	// erst Tausender-Punkt bzw. -Abstand tilgen, dann Komma in Punkt umwandeln:
	$number = str_replace('.', '', $number); // Tausender-Trennzeichen '.' weg
	$number = str_replace(' ', '', $number); // Tausender-Trennzeichen ' ' weg
	$number = str_replace(',', '.', $number); // Komma => Punkt
	// Regex "alles AUSSER Ziffern, Dezimalzeichen (Komma bzw. Punkt) und Minuszeichen weg, nur das übrig lassen:
	/* alt: $regex = '/[^-\d'.preg_quote($dec_point).']/'; // \x{2212}\x{2013} funktioniert irgendwie noch nicht => später: vernünftiger Umgang mit Minuszeichen in regex => jetzt müsste aber gehen: string_replace! */
	$regex = '/[^-\d\.]/';
	$number = preg_replace($regex, '', $number);
	/* alt: jetzt schon oben // Ersetze so, dass das Dezimalzeichen auf jeden Fall ein Punkt ist:
	$number = str_replace($dec_point, '.', $number); */
	// Wandle dann String in Zahl (float) um:
	$number = floatval($number);
	
    return $number;
}

// Zahl (float) in String umwandeln:
function str_from_num($number, $dec_point = null) {
    /* alt: if (empty($dec_point)) {
        $locale = localeconv();
        $dec_point = $locale['decimal_point'];
    }*/
	// String:
	$number_string = strval($number);
	// ASCII-Minuszeichen in schönes Minuszeichen umwandeln:
	$number_string = str_replace('-', '–', $number_string); // 002D  =>  U+2212: −, U+2013*: –, U+2014: —   (* = aktuell gewählt)
	// Punkt in Komma:
	$number_string = str_replace('.', ',', $number_string); // Punkt =>  Komma
	
    return $number_string;
}

function korrekturkurs ($kurs) {
	$klasse	= $kurs[0];
	// alt: $fach	= $kurs[1];
	$fach_lang	= explode('-', $kurs[1]); // -GKx ggf. abtrennen
	$fach		= $fach_lang[0];
	$kursart = (isset($fach_lang[1])) ? $fach_lang[1] : 'unbekannt';
	
	// Sport nie Korrektur, ebenso Geschichte/SoWi-Zusatz, Vokalprakt. Kurs, Literatur:
	$muendliche_faecher = array ("Sp", "GZ", "SZ", "IV", "VP", "Li");		// Der vokalpraktsiche Kurs kann "IV" oder "VP" heißen.
	if (in_array($fach, $muendliche_faecher))	{ return false; }
	// mündliche Kursarten:
	$muendlicher_kursarten = array ('VK', 'PK', 'ZK');
	if (in_array($kursart, $muendlicher_kursarten))	{ return false; }
	// Hauptfächer:
	$hauptfaecher = array ("D", "E", "M", "F", "L", "E5", "E6", "L5", "L6", "F6");
	if (in_array($fach, $hauptfaecher))	{ return true; }
	// WP II:
	$wpII = array ("DG", "G/C", "B/C", "F8", "If");			// "If" ist nicht eindeutig WP II, sondern auch SekII, aber auch da kommt es ja als Korrekturkurs in Frage, insofern darf das ruhig direkt 'true' returnen.
	if (in_array($fach, $wpII))			{ return true; }
	// oder alles Mögliche in der Oberstufe:
	$oberstufe = array ("EF", "Q1", "Q2");					// Da Sport/GZ/SZ/VP und Kursarten PK/GK/ZK oben schon mit 'return false' ausgeschlossen wurden, können hier einfach alle anderen Oberstufenkurse als grundsätzliche Korrekturkurse betrachtet werden.
	if (in_array($klasse, $oberstufe))	{ return true; }
	// in allen anderen Fällen:
	return false;
}
$komfaktor = array (5 =>	  '1',
					6 =>	  '1',
					7 =>	'1,5',
					8 =>	'1,5',
					9 =>	'1,5',
					10 =>	'1,5',
					'EF' =>	  '2',
					'Q1' =>	  '2',
					'Q2' =>	  '2'); 

// 1) UV einlesen (enthält Kollegen und Kurse):
$dateinameuv = 'UV'.$halbjahr['kurz'].'.txt';
$uvl = array();
if (file_exists($dateinameuv)) {
	$temp = file($dateinameuv);
	for ($i = 0; $i < count($temp); $i++) {
		$zeile = $temp[$i];
		$zeile = explode("\t", $zeile);
		$zeile = array_map('trim', $zeile);
		if (count($zeile) > 1) $uvl[] = $zeile;
	}
}

// 2) ABI-Klausuren einlesen (enthält Abi-Korrekturen):
$dateiname_abi_klausuren = 'UV'.$halbjahr['kurz'].'ABI_1K.txt';
if (file_exists($dateiname_abi_klausuren)) {
	$temp = file($dateiname_abi_klausuren);
	$abi_schr = array();
	for ($i = 0; $i < count($temp); $i++) {
		$zeile = $temp[$i];
		$zeile = explode("\t", $zeile);
		$zeile = array_map('trim', $zeile);
		if (count($zeile) > 1) $abi_schr[] = $zeile;
	}
}
else {
	$abi_schr = false;
}// print_r($abi_schr);

// 3) ABI-müPrü einlesen (enthält Blöcke (nur Prüfer)):
$dateiname_abi_muendl_prue = 'UV'.$halbjahr['kurz'].'ABI_2M.txt';
if (file_exists($dateiname_abi_muendl_prue)) {
	$temp = file($dateiname_abi_muendl_prue);
	$abi_mue = array();
	for ($i = 1; $i < count($temp); $i++) { // Bei Zeile 1 beginnen (Zeile 0 = Überschriften!)
		$zeile = $temp[$i];
		$zeile = explode("\t", $zeile);
		$zeile = array_map('trim', $zeile);
		if (count($zeile) > 1) $abi_mue[] = $zeile;
	}
}
else {
	$abi_mue = false;
}

// Datei(en) in brauchbares Array umwandeln:
$lehrer = array (); // Hier soll alles am Ende schön geordnet rein
for ($i = 0; $i < count($uvl); $i++) {   // zeilenweise durch Textdati "UV....txt" gehen
	$aktueller_lehrer_roh = $uvl[$i];
	// schon oben passiert: $aktueller_lehrer_roh = explode("\t", $aktueller_lehrer_roh);
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
		if (($aktueller_kurs_roh[0] != 'Q2') || ($halbjahr['nr'] != '2')) { // nur "normale" Kurse sammeln (else = "Q2" UND "2. Halbjahr" => nicht aus UV mitnehmen, sondern dafür gilt UV2ABI.txt!)
			$lehrer[$i]["kurs"][] = array (
				"klasse"	=>	$aktueller_kurs_roh[0],
				"fach"		=>	$aktueller_kurs_roh[1]
			);
			if (korrekturkurs($aktueller_kurs_roh)) {
				$lehrer[$i]["korrekturkurs"][] = array (
					"klasse"	=>	$aktueller_kurs_roh[0],
					"fach"		=>	$aktueller_kurs_roh[1],
					"stufe"		=>	(in_array($aktueller_kurs_roh[0], array('EF', 'Q1', 'Q2'))) ? $aktueller_kurs_roh[0] : preg_replace('/[^\d]/', '', $aktueller_kurs_roh[0])
				);
			}
		}
		unset($aktueller_kurs_roh);
	}
	unset($aktueller_lehrer_roh);
	
	// ggf. Abi:
	$lehrer[$i]['abi_erst'] = array();
	$lehrer[$i]['abi_zweit'] = array();
	$lehrer[$i]['abi_mue'] = array();

	// a) Abi-Klausuren:
	if ($abi_schr) {
		$abi_erst	= array_keys(array_column($abi_schr, 2), $lehrer[$i]['kuerzel']);
		$abi_zweit	= array_keys(array_column($abi_schr, 4), $lehrer[$i]['kuerzel']);
		$index_beginnen_bei = count($lehrer[$i]["korrekturkurs"]);
		for ($l = 0; $l < count($abi_erst); $l++) {
			// GK/LK von Nummer trennen:
			$kurs_art_und_nr = $abi_schr[$abi_erst[$l]][0];
			if (preg_match('/[^\d]+/',	$kurs_art_und_nr, $matches)) { $kursart = $matches[0]; } else { $kursart = ''; }
			if (preg_match('/\d+/',		$kurs_art_und_nr, $matches)) { $kursnr  = $matches[0]; } else { $kursnr  = ''; }
			// Daten zusammensetzen:
			$lehrer[$i]['abi_erst'][$index_beginnen_bei + $l] = array (
				'klasse'	=>	'Q2',
				'stufe'		=>	'Q2',
				'fach'		=>	$abi_schr[$abi_erst[$l]][1],
				'erstzweit'	=>	'erst',
				'kursart'	=>	$kursart,
				'kursnr'	=>	$kursnr,
				'anzahl'	=>	$abi_schr[$abi_erst[$l]][6]
			);
		}
		$index_beginnen_bei = count($lehrer[$i]["korrekturkurs"]) + count($lehrer[$i]['abi_erst']);
			for ($l = 0; $l < count($abi_zweit); $l++) {
			// GK/LK von Nummer trennen:
			$kurs_art_und_nr = $abi_schr[$abi_zweit[$l]][0];
			if (preg_match('/[^\d]+/',	$kurs_art_und_nr, $matches)) { $kursart = $matches[0]; } else { $kursart = ''; }
			if (preg_match('/\d+/',		$kurs_art_und_nr, $matches)) { $kursnr  = $matches[0]; } else { $kursnr  = ''; }
			// Daten zusammensetzen:
			$lehrer[$i]['abi_zweit'][$index_beginnen_bei + $l] = array (
				'klasse'	=>	'Q2',
				'stufe'		=>	'Q2',
				'fach'		=>	$abi_schr[$abi_zweit[$l]][1],
				'erstzweit'	=>	'zweit',
				'kursart'	=>	$kursart,
				'kursnr'	=>	$kursnr,
				'anzahl'	=>	$abi_schr[$abi_zweit[$l]][6],
				'kurs_von'	=>	str_replace('ext', 'extern', $abi_schr[$abi_zweit[$l]][2])
			);
		}
	}
	
	// b) Abi-müPrü:
	if ($abi_mue) {
		$pruef_bloecke_keys	= array_keys(array_column($abi_mue, 4), $lehrer[$i]['kuerzel']);
		// Tabelle auswerten 1. Schritt (A4, A1-3, Kursart, Fach extrahieren):
		$pruefer_bloecke_temp = array();
		for ($l = 0; $l < count($pruef_bloecke_keys); $l++) {
			// A4 oder A1-3? und Kursart:
			$art_des_termins = $abi_mue[$pruef_bloecke_keys[$l]][0];
			$art_des_termins_lang = '';
			if ($art_des_termins === 'A4')		$art_des_termins_lang = 'Abi: mündliche Prüfung im 4. Abiturfach';
			if ($art_des_termins === 'A1-3')	$art_des_termins_lang = 'Abi: mündliche Nachprüfung in einem (schriftlichen) Abiturfach';
			$kursart = $abi_mue[$pruef_bloecke_keys[$l]][1];
			// Kurs:
			$kurs_e	 = $abi_mue[$pruef_bloecke_keys[$l]][2];
			/* if (preg_match('/[^\d]+/',	$kurs, $matches)) { $fach = $matches[0];	}	else { $fach = '';		}
			if (preg_match('/\d+/',		$kurs, $matches)) { $kursnr  = $matches[0]; }	else { $kursnr  = '';	} */
			// Prüfungsblock:
			$block	 = $abi_mue[$pruef_bloecke_keys[$l]][3];
			if (preg_match('/[^\d]+/',	$block, $matches)) { $fach = $matches[0];	 }	else { $fach = '';		}
			if (preg_match('/\d+/',		$block, $matches)) { $blocknr = $matches[0]; }	else { $blocknr  = '';	}
			// Daten zusammensetzen:
			$zusammenfass_kriterium = $art_des_termins.'_'.$kursart.'_'.$fach;
			$pruefer_bloecke_temp[$zusammenfass_kriterium][] = array (
				'klasse'		=>	'Q2',
				'stufe'			=>	'Q2',
				'prüArt'		=>	$art_des_termins,
				'prüArt_lang'	=>	$art_des_termins_lang,
				'kursart'		=>	$kursart,
				'fach'			=>	$fach,
				'kurs(e)'		=>	'„'.$kurs_e.'“',
				'blocknr'		=>	$blocknr,
				'kursnr'		=>	$kursnr,
				'wert'			=>	$abi_mue[$pruef_bloecke_keys[$l]][5],
				'hinweis'		=>	$abi_mue[$pruef_bloecke_keys[$l]][6]
			);
		}
		// Tabelle auswerten 2. Schritt (Prüfungsblöcke eines Prüfers bei gleichem Fach, gleicher Kursart und gleicher Termin-Art zusammenfassen):
		$pruefer_bloecke = array();
		foreach ($pruefer_bloecke_temp as $zusammenfass_kriterium => $bloecke_gleicher_art) {
			$wert_summe = 0;
			$kurs_e	= '';
			$hinweis = '';
			/* ohne Schleife ginge es so: $werte_pro_block = array_column($bloecke_gleicher_art, 'wert')
			$werte_pro_block = array_map('num_from_str', $werte_pro_block);
			$werte_summe = str_from_num(array_sum($werte_pro_block)); */
			for ($l = 0; $l < count($bloecke_gleicher_art); $l++) {
				$wert_summe += num_from_str($bloecke_gleicher_art[$l]['wert']);
				$kurs_e = (($kurs_e) ? ($kurs_e.', ') : '').$bloecke_gleicher_art[$l]['kurs(e)'];
				$hinweis = $hinweis.((($hinweis) && ($bloecke_gleicher_art[$l]['hinweis'])) ? (', ') : '').$bloecke_gleicher_art[$l]['hinweis'];
			}
			$pruefer_bloecke[] = array (
				'klasse'		=>	'Q2',
				'stufe'			=>	'Q2',
				'prüArt'		=>	$bloecke_gleicher_art[0]['prüArt'],
				'prüArt_lang'	=>	$bloecke_gleicher_art[0]['prüArt_lang'],
				'kursart'		=>	$bloecke_gleicher_art[0]['kursart'],
				'fach'			=>	$bloecke_gleicher_art[0]['fach'],
				'kurs(e)'		=>	$kurs_e,
				'wert'			=>	str_from_num($wert_summe),
				'hinweis'		=>	$hinweis
			);
		}
		$index_beginnen_bei = count($lehrer[$i]["korrekturkurs"]) + count($lehrer[$i]['abi_erst']) + count($lehrer[$i]['abi_zweit']);
		for ($l = 0; $l < count($pruefer_bloecke); $l++) {
			$lehrer[$i]['abi_mue'][$index_beginnen_bei + $l] = $pruefer_bloecke[$l];
		}
	}
	//print_r($abi_erst);
} if (isset($_GET['debug']) && ($_GET['debug'] === 'abim')) { print_r($lehrer); exit(); }
//print_r($lehrer);

$keineanrechnung = array("PER", "NN1", "NN2", "NN3", "NN4", "ALB", "FEG", "KWH", "NOL", "BÜR");
// 2018-2018.1: $elternzeit = array("AT", "KN", "RAU");
// 2018-2018.2:
$elternzeit = array("HDN", "RAU", "SPN", "VDB", "GU", "GRW", "JUN");
// Kontroll-Tabelle ausgeben, in der alle eingelesenen Daten mit Kursen und Korrekturkursen stehen:
// * dabei von vornherein auschließen: [(alle, die _keinen_ Korrekturkurs haben)Nein, zulassen wg. Klassenleitung] Vertretungslehrer und Sonderpädagogen:
if (isset($_GET["tabelle"]) && ($_GET["tabelle"])) {  // d.h. $_GET["tabelle"] == 1 oder == "txt" (daraus später Fallunterscheidung 2a vs 2b)
	echo '<table>';
	// 1. Daten vorbereiten:
	for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
		if (/*!empty($lehrer[$i]["korrekturkurs"]) && */!in_array($lehrer[$i]["kuerzel"], array_merge($keineanrechnung, $elternzeit)))	{ $lehrer[$i]["korrekturlehrer"] = TRUE;  }	// * [(alle, die _keinen_ Korrekturkurs haben)Nein, zulassen wg. Klassenleitung] Vertretungslehrer und Sonderpädagogen
																														else			{ $lehrer[$i]["korrekturlehrer"] = FALSE; }
		$pfad_zur_einzelnen_eventuellen_Kollegen_Eintragungsdatei = $_SERVER["DOCUMENT_ROOT"].'untis/rstkxzw3/zfweo87/'.$schuljahr['pfad'].'/'.$halbjahr['kurz'].'bearbeitet/'.$lehrer[$i]["kuerzel"].'.txt';
		if (file_exists($pfad_zur_einzelnen_eventuellen_Kollegen_Eintragungsdatei))														{ $lehrer[$i]["eingetragen"] = TRUE;  }
																														else			{ $lehrer[$i]["eingetragen"] = FALSE; }
		/*********** Das sollte am besten überflüssig werden, damit es nur noch nach den Eintragungen geht: *******************/
		if (isset($lehrer[$i]['abi_erst'])) {
			$lehrer[$i]["korrekturkurs"] = $lehrer[$i]["korrekturkurs"] + $lehrer[$i]['abi_erst'];
		}
		if (isset($lehrer[$i]['abi_zweit'])) {
			$lehrer[$i]["korrekturkurs"] = $lehrer[$i]["korrekturkurs"] + $lehrer[$i]['abi_zweit'];
		}
		if (isset($lehrer[$i]['abi_mue'])) {
			$lehrer[$i]["korrekturkurs"] = $lehrer[$i]["korrekturkurs"] + $lehrer[$i]['abi_mue'];
		}
		/**********************************************************************************************************************/
		// debug: if ($lehrer[$i]["kuerzel"] == 'AC') { var_dump($lehrer[$i]["abi_erst"]);exit(); }
		
		if ($lehrer[$i]["eingetragen"])	{
			$eintragungen_aus_datei = file($pfad_zur_einzelnen_eventuellen_Kollegen_Eintragungsdatei);
			$eintragungen = array (	'korrekturkurs' => '', 'abi_erst' => '',	'abi_zweit' => '', 			'häufigkeit' => '', 		'dauer' => '', 			'dauer_anders' => '',
									'schreiber' => '', 'bemerkung' => '',		'nachschr' => '',			'facharbeiten' => '',		'andereeingabe' => '',	'nicht' => '',
									'abi_mue_A4_fach_und_kursart' => '',		'abi_A4_mue_bloecke' => '', 'abi_mue_bemerkung' => '',
									'klasse' => '', 'fach' => '', 'stufe' => '');
			// Eintragungen zeilenweise auswerten:
			$zeilennr = 1;
			$zeile = $eintragungen_aus_datei[$zeilennr];
			$eintragung = explode("\t", $zeile);
			while ((count($eintragung) > 1) && preg_match('/\d+$/', $eintragung[0])) {
				// Zeile zerlegen: Label, Korrkturkursnr. und Wert:
				$matches = array ();
				preg_match('/^(.*?)(\d*)$/', $eintragung[0], $matches);
				$korrkurs_nr = $matches[2];
				$tag		 = $matches[1];
				$wert		 = trim($eintragung[1]);
				
				// tag-abhänginge Auswertung:
				if ($tag === 'häufigkeit') {
					if ($wert === '_') $wert = '0';
				}
				if ($tag === 'dauer') {
					if ($wert > 10) $wert = str_replace(',0000', '', number_format($wert / 45, 4, ',', ''));
				}
				if ($tag === 'nachschr') {
					if ($wert === 'nein')	$wert = '0';
					if ($wert === 'ja')		$wert = '1';
				}
				if ($tag === 'korrekturkurs') {
					$gruppen_teile = explode(' ', $wert);
					$eintragungen['klasse']	=	$gruppen_teile[0];
					$eintragungen['fach']	=	$gruppen_teile[1];
					$eintragungen['stufe']	=	(in_array($eintragungen['klasse'], array('EF', 'Q1', 'Q2'))) ? $eintragungen['klasse'] : preg_replace('/[^\d]/', '', $eintragungen['klasse']);
				}
				

				// *** Hauptteil: Eintragung weitergeben! **************
				$eintragungen[$tag] = $wert;
				// *****************************************************
																		 
				// nächste Zeile schon mal holen (für nächste while-Prüfung):
				$zeilennr++;
				$zeile = $eintragungen_aus_datei[$zeilennr];
				$eintragung = explode("\t", $zeile);
				$matches = array ();
				preg_match('/^(.*?)(\d*)$/', $eintragung[0], $matches);
				$naechste_korrkurs_nr	= $matches[2];
				$naechster_tag			= $matches[1];
				// alt: $naechste_korrkurs_nr = preg_replace('/[^\d]/',	'', $eintragung[0]);

				// Korrekturkurs mit gerade verarbeiteter Eintragung vollständig? => Dann abschließende Bearbeitung:
				if ($korrkurs_nr != $naechste_korrkurs_nr) {
					
					// (0) abschließende Abi-Bearbeitung:
					if ($eintragungen['abi_erst'] || $eintragungen['abi_zweit']) {
						
						$eintragungen['klasse'] = 'Q2';
						$eintragungen['stufe']	= 'Q2';
							
						// (0a) Häufigkeit => da steht beim Abi die Dauer: (vertauscht)
						$suchen = array('normalGK', 'normalLK');
						$ersetzen = array('3,5', '5');
						$eintragungen['häufigkeit'] = str_replace($suchen, $ersetzen, $eintragungen['häufigkeit']);
						
						// (0b) Dauer => da steht beim Abi die Häufigkeit: (vertauscht)
						$suchen = 'Abi';
						if ($eintragungen['abi_erst'])	{	$ersetzen = '2';	}
						if ($eintragungen['abi_zweit'])	{	$ersetzen = '0,5';	}
						$eintragungen['dauer'] = str_replace($suchen, $ersetzen, $eintragungen['dauer']);

						// (0c) Tausche bei Abi Häufigkeit und Dauer:
						$dauer = $eintragungen['häufigkeit'];
						$hauef = $eintragungen['dauer'];
						$eintragungen['dauer']		= $dauer;
						$eintragungen['häufigkeit'] = $hauef;
						// (0d) setze Namen des Korrekturkurses aufschlussreich:
						if ($eintragungen['abi_erst'])	$eintragungen['korrekturkurs'] = $eintragungen['abi_erst'].' (Vorabi + Abi-Erstkorr.)';
						if ($eintragungen['abi_zweit'])	$eintragungen['korrekturkurs'] = $eintragungen['abi_zweit'].' (Abi-Zweitkorr.)';
					}
					// weitergeben:
					$lehrer[$i]["Eintragungen"][$korrkurs_nr] = $eintragungen;
					// initialisieren für nächsten Korrekturkurs:
					$eintragungen = array(	'korrekturkurs' => '', 'abi_erst' => '',	'abi_zweit' => '', 			'häufigkeit' => '', 		'dauer' => '', 			'dauer_anders' => '',
											'schreiber' => '', 'bemerkung' => '',		'nachschr' => '',			'facharbeiten' => '',		'andereeingabe' => '',	'nicht' => '',
											'abi_mue_A4_fach_und_kursart' => '',		'abi_A4_mue_bloecke' => '', 'abi_mue_bemerkung' => '',
											'klasse' => '', 'fach' => '', 'stufe' => '');
				}
			}

			// Klassenleitung und Kommentar: nach letztem Korrekturkurs (zeilennr müsste da stehen geblieben sein):
			
			$KL_ab_zeile = $zeilennr;
			$letzte_zeile = count($eintragungen_aus_datei); // alt: $lehrer[$i]["Eintragungen"]
			// eingelesen und folgendermaßen benannt werden sollen:
			$labels = array ("klassenleitung_janein", "klassenleitung_klasse", "klassenleitung_nr", "klassenleitung_aufteilung", "kommentar"); // alt: array ("Klassenlehrer", "Klasse", "1oder2");
			for ($j = $KL_ab_zeile; $j < $letzte_zeile; $j++) {
				$eintragung = trim($eintragungen_aus_datei[$j]); // alt: $lehrer[$i]["Eintragungen"][$j]
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
					echo $lehrer[$i]["Eintragungen"][$j]['häufigkeit']." x ".$lehrer[$i]["Eintragungen"][$j]['dauer']." x ".$lehrer[$i]["Eintragungen"][$j]['schreiber']."<br>\n";
					if ($lehrer[$i]["Eintragungen"][$j]['nachschr']) echo $lehrer[$i]["Eintragungen"][$j]['nachschr'].'-mal nachgeschrieben';
					echo "</td>\n";
				}
				echo "</tr>\n";
				echo '<tr><td style="text-align:right;" colspan="4">Sonstiges:</td>';
				for ($j = 0; $j < count($lehrer[$i]["korrekturkurs"]); $j++) {	// passend zu den Elementen von $lehrer[$i]["korrekturkurs"] durch die Datei [Kürzel].txt gehen:
					echo "<td style=\"$style\">";
					$sonstiges = '';
					foreach ($lehrer[$i]["Eintragungen"][$j] as $tag => $wert) {
						if (!in_array($tag, array('korrekturkurs', 'häufigkeit', 'dauer', 'schreiber', 'nachschr'))) {
							if ($wert) $sonstiges .= $wert.", ";
						}
					}
					$sonstiges = preg_replace('/, $/', '', $sonstiges);
					echo $sonstiges;
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
	if ($_GET["tabelle"] === 'txt') {
		for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
			if (!in_array($lehrer[$i]["kuerzel"], $keineanrechnung)) {	// nur potentielle Korrekturlehrer/Klassenlehrer (also keine Sonderpädagogen, Referendare, Vertretungslehrer) anzeigen
				$style = $style_aktiv;
				echo '<tr>';
				echo "<td style=\"$style\">".$lehrer[$i]["kuerzel"]."</td>\n";
				if ($lehrer[$i]["eingetragen"])	{
					for ($j = 0; $j < count($lehrer[$i]["korrekturkurs"]); $j++) {	// passend zu den Elementen von $lehrer[$i]["korrekturkurs"] durch die Datei [Kürzel].txt gehen:
						echo "<td style=\"${style}font-size:50%;\">".$lehrer[$i]["Eintragungen"][$j]["korrekturkurs"]."</td>\n";
						echo "<td style=\"${style}width:3em;\">".$lehrer[$i]["Eintragungen"][$j]['häufigkeit']."</td>\n";
						if ($lehrer[$i]["Eintragungen"][$j]['dauer'] == 60) { $lehrer[$i]["Eintragungen"][$j]['dauer'] = "1,333"; }
						echo "<td style=\"${style}width:3em;\">".$lehrer[$i]["Eintragungen"][$j]['dauer']."</td>\n";
						//echo "<td style=\"${style}width:3em;\">".$lehrer[$i]["Eintragungen"][$j]['dauer_anders']."</td>\n";
						echo "<td style=\"${style}width:3em;\">".$lehrer[$i]["Eintragungen"][$j]['schreiber']."</td>\n";
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


	// 2c) Tabelle für NEUE Berechnung ab 2019/20 => Punkte-Berechnung hier in php => zum Einfügen dann direkt in Excel-"Entlastung":
	if ($_GET["tabelle"] === 'NEU') {
		
		// Daten zusammenstellen:
		for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
		
			$auswertung = array();
		
			$auswertung['kuerzel'] = $lehrer[$i]['kuerzel'];
			$auswertung['eingetragen'] = $lehrer[$i]['eingetragen'];
			$auswertung['punkte_summe_num'] = 0;
			$auswertung['anrechnung'] = false;
			$auswertung['kommentar'] = $lehrer[$i]['Eintragungen']['kommentar'];
		
			if (!in_array($auswertung['kuerzel'], $keineanrechnung)) {	// nur potentielle Korrekturlehrer/Klassenlehrer (also keine Sonderpädagogen, Referendare, Vertretungslehrer) anzeigen
			
				$auswertung['anrechnung'] = true;
				$auswertung['sets'] = array();
			
				if ($auswertung['eingetragen'])	{
					
					// erst nur ausrechnen: (anzeigen später, nachdem die Summe angezeigt wurde)
					for ($j = 0; $j < count($lehrer[$i]["Eintragungen"]); $j++) {	// set-weise durch die Datei [Kürzel].txt gehen

						// es gibt nicht nur numerische keys, daher zählt count zuviel => filtern:
						if (isset($lehrer[$i]["Eintragungen"][$j])) {
					
							$set = $lehrer[$i]["Eintragungen"][$j]; // alt: = array();
							
							// schriftlich:
							if ($set['korrekturkurs']) {
								
								$set['name']	= $set['korrekturkurs'];
								
								$set['schriftlich'] = true;
								$set['mündlich'] = false;

								/* automatisch:
								$set['klasse']	= $lehrer[$i]["Eintragungen"][$j]['klasse'];
								$set['stufe']	= $lehrer[$i]["Eintragungen"][$j]['stufe']; */

								/*$set['schreiber']		= $lehrer[$i]["Eintragungen"][$j]['schreiber'];*/					$set['schreiber_num']		= num_from_str($set['schreiber']);
								$set['haeuf']			= $set['häufigkeit'];												$set['haeuf_num']			= num_from_str($set['haeuf']);
								$set['faktor']			= $komfaktor[$set['stufe']];										$set['faktor_num']			= num_from_str($set['faktor']);
								/* $set['laenge']			= $lehrer[$i]["Eintragungen"][$j]['dauer'];*/					$set['dauer_num']			= num_from_str($set['dauer']);
								/*$set['nachschr']		= $lehrer[$i]["Eintragungen"][$j]['nachschr'];*/					$set['nachschr_num']		= num_from_str($set['nachschr']);
								/*$set['facharbeiten']	= $lehrer[$i]["Eintragungen"][$j]['facharbeiten'];*/				$set['facharbeiten_num']	= num_from_str($set['facharbeiten']);
								
								$set['punkte_ohneNF_num'] = round( ($set['schreiber_num'] + 3) * $set['haeuf_num'] * $set['faktor_num'] * $set['dauer_num'],   1);
								$set['punkte_ohneNF'] = str_from_num($set['punkte_ohneNF_num']);

								// Nachschreiber:
								$set['n_addiere_num'] = 0;
								if ($set['nachschr_num']) {
									$set['n_addiere_num'] =  round( $set['nachschr_num'] * 3 * $set['faktor_num'] * $set['dauer_num'],    1);
								}
								$set['n_addiere'] = str_from_num($set['n_addiere_num']);
								
								// Facharbeiten:
								$set['f_subtrahiere_num']	= 0;
								$set['f_addiere_num']		= 0;
								if ($set['facharbeiten_num']) {
									$set['f_subtrahiere_num'] =	 round( $set['facharbeiten_num'] * $set['haeuf_num'] * $set['faktor_num'] * $set['dauer_num'],    1);
									$set['f_addiere_num'] =  	 $set['facharbeiten_num'] * 25; // 25 für die ersten beiden
									if ($set['facharbeiten_num'] > 2) { $set['f_addiere_num'] += ($set['facharbeiten_num'] - 2) * 5; } // ab der dritten 30 (also jeweils 5 dazu für die, die über 2 hinausgehen)
								}
								$set['f_subtrahiere']	= str_from_num($set['f_subtrahiere_num']);
								$set['f_addiere']		= str_from_num($set['f_addiere_num']);
								
								$set['punkte_num'] = $set['punkte_ohneNF_num'] + $set['n_addiere_num'] - $set['f_subtrahiere_num'] + $set['f_addiere_num'];
								$set['punkte'] = str_from_num($set['punkte_num']);
								
								$auswertung['punkte_summe_num'] += $set['punkte_num'];
								
							}
							// mündlich A4:
							elseif ($set['abi_mue_A4_fach_und_kursart']) {
								
								$set['name'] = $set['abi_mue_A4_fach_und_kursart'];
								
								$set['schriftlich'] = false;
								$set['mündlich'] = 'A4';
								
								$set['bloecke']	= $set['abi_A4_mue_bloecke'];	$set['bloecke_num']	= num_from_str($set['bloecke']);
								$set['punkte_num'] = $set['bloecke_num'] * 30;
								$set['punkte'] = str_from_num($set['punkte_num']);
								$auswertung['punkte_summe_num'] += $set['punkte_num'];
							}
							// mündlich A1-3:
							elseif ($set['abi_mue_A123_fach_und_kursart']) {
								
								$set['name'] = $set['abi_mue_A123_fach_und_kursart'];

								$set['schriftlich'] = false;
								$set['mündlich'] = 'A1-3';
								
								$set['bloecke']	= $set['abi_A123_mue_bloecke'];	$set['bloecke_num']	= num_from_str($set['bloecke']);
								$set['punkte'] = $set['bloecke_num'] * 30;
								$set['punkte'] = str_from_num($set['punkte_num']);
								$auswertung['punkte_summe_num'] += $set['punkte_num'];
							}

							$auswertung['sets'][$j] = $set;
						}
					}
				}
			}
				
			$auswertung['punkte_summe'] = str_from_num($auswertung['punkte_summe_num']);
			$lehrer[$i]['auswertung'] = $auswertung;
		}
		
		
		// Daten anzeigen:
		for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
			
			$auswertung = $lehrer[$i]['auswertung'];

			if ($auswertung['anrechnung']) { // nur die Lehrer, die oben nicht im Array "keineanrechnung" (Referendare, Sonderpädagogen, ...) gefunden wurden
				
				$sets = $auswertung['sets'];
				
				$style = $style_aktiv;
				echo '<tr style="page-break-inside:avoid !important;">';
				echo "<td style=\"${style}page-break-inside:avoid !important;\">".$auswertung['kuerzel']."</td>\n";
				echo "<td style=\"${style}page-break-inside:avoid !important;\">".$auswertung['punkte_summe'].'</td>';
				
				if ($auswertung['eingetragen']) { // eingetragen
				
					for ($j = 0; $j < count($sets); $j++) {
					
						$set = $sets[$j];
						
						echo "<td style=\"${style}width:24em;vertical-align:top;page-break-inside:avoid !important;\">";
						
						// schriftlich:
						if ($set['schriftlich']) {

							echo '<u>Korrekurkurs: '.$set['name']."</u><br>\n";	// alt: $lehrer[$i]["Eintragungen"][$j]['korrekturkurs']
							
							echo 'Anzahl: Sockel 3 + '.$set['schreiber']." Schreiber = ".str_from_num($set['schreiber_num']+3)."<br>\n";
							$styleH = '';
							if (($halbjahr_nr === '2') && ($set['haeuf_num'] > 1) && ($set['klasse'] !== 'Q2')) $styleH = 'font-weight:bold;color:red;';
							echo '<span style="'.$styleH.'">Häufigkeit: '.$set['haeuf']."</span><br>\n";
							echo 'Komplexitätsfaktor: '.$set['faktor']."<br>\n";
							echo 'Länge der KA/Klausur: '.$set['dauer']."<br>\n";
							
							echo '<br>';
							echo '= '. $set['punkte_ohneNF'].' Punkte'."<br>\n";

							if ($set['nachschr_num']) {
								echo '<br>';
								echo '+ '.$set['n_addiere'].' ('.$set['nachschr'].' Nachschreibtermin(e) x 3 x Faktor '.$set['faktor'].' x Länge '.$set['dauer'].')'."<br>\n";
							}
							if ($set['facharbeiten_num']) {
								echo '<br>';
								echo '+ '.$set['f_addiere'].	' ('.$set['facharbeiten'].' Facharbeit(en); je 25 Punkte für die ersten beiden, je 30 für jede weitere)<br>'."\n";
								echo '– '.$set['f_subtrahiere'].' ('.$set['facharbeiten'].' Klausurschreiber weniger beim 1. Termin x 1 x Faktor '.$set['faktor'].' x Länge '.$set['dauer'].')<br>'."\n";
							}
							if ($set['nachschr_num']+$set['facharbeiten_num']) {
								echo '<br>';
								echo '= '. $set['punkte'].' Punkte';
							}

						}
						// mündlich:
						if ($set['mündlich'] === 'A4') {

							echo '<u>mündliche Abi-Prüfung (A4): '.$set['name']."</u><br>\n";	// alt: $lehrer[$i]["Eintragungen"][$j]['abi_mue_A4_fach_und_kursart']
							echo 'Anzahl Aufgaben: '.$set['bloecke']." x 30 Punkte<br>\n";
							echo '= '. $set['punkte'].' Punkte'."<br>\n";
							
						}
						if ($set['mündlich'] === 'A1-3') {

							echo '<u>mündliche Abi-Nachprüfung (A1-3): '.$set['name']."</u><br>\n";	// alt: $lehrer[$i]["Eintragungen"][$j]['abi_mue_A123_fach_und_kursart']
							echo 'Anzahl Aufgaben: '.$set['bloecke']." x 30 Punkte<br>\n";
							echo '= '.$set['punkte'].' Punkte'."<br>\n";
							
						}
						
						
						// ggf. Sonstiges (andere Dauer, Bemerkung, sonstige Prosa) anzeigen:
						if ($set['dauer_anders'] || $set['bemerkung'] || $set['andereeingabe'] || $set['nicht'])	echo '<br><br><span style="color:red;">';
						if ($set['dauer_anders'])	echo 'andere Dauer: '.			$set['dauer_anders'].'<br>';
						if ($set['bemerkung'])		echo 'Bemerkung: '.				$set['bemerkung'].'<br>';
						if ($set['andereeingabe'])	echo 'andere Eingabe: '.		$set['andereeingabe'].'<br>';
						if ($set['nicht'])			echo 'habe den Kurs nicht: '.	$set['nicht'];
						if ($set['dauer_anders'] || $set['bemerkung'] || $set['andereeingabe'] || $set['nicht'])	echo '</span>';
					
						echo '</td>'."\n";
					
					}
					
					// ggf. Kommentar anzeigen:
					if ($auswertung['kommentar']) {
						echo '<td style="color:red;">Kommentar: '.$auswertung['kommentar'].'</td>';
					}
					
				}
				
				elseif ($lehrer[$i]["korrekturlehrer"]) {  		// das könnte auch wahr sein, wenn kein Korrekturlehrer, sondern nur Klassenlehrer (wie DZ)...

					if (count($lehrer[$i]["korrekturkurs"])) {	// ...daher so noch unterscheiden.
						echo '<td style="text-align:left;background:#fdd;color:#975;" colspan="12">nichts eingetragen, obwohl potentielle(r) Korrekturkurs(e) und evtl. Klassenlehrer</td>';
					} else {
						echo '<td style="text-align:left;background:#ffe0b3;color:#876;" colspan="12">nichts eingetragen, obwohl evtl. Klassenlehrer</td>';
					}
				}
				

				
				echo "</tr>\n";
			}
		}
	}


	// 2d) Tabelle Klassenleitungen (für Copy & Paste):
	if ($_GET['tabelle'] === 'KL') {
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
	// 2e) Tabelle Klassenleitungen und Kommentare (alles außer den Korrekturkursen):
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
		<div class="rahmen"><div>
			<select name="anmelden_mit_kuerzel">
				<option value="0">--Kürzel auswählen:--</option>
<?php			for ($i = 0; $i < count($lehrer); $i++) {   // Elementweise durch array $lehrer gehen
					if (/*!empty($lehrer[$i]["korrekturkurs"]) && */!in_array($lehrer[$i]["kuerzel"], array_merge($keineanrechnung, $elternzeit)))	{
						echo '<option>'.$lehrer[$i]["kuerzel"].'</option>'."\n";
					}
				}
?>			</select>
			<button id="submit" class="luftigrund">weiter</button>
		</div></div>
	</form>
	<div style="font-size:0.8em;0 padding:20px;margin:0 20px;"><a href="../index.php">Halbjahr wechseln</a> | <a href="index.php?logout=1">Logout</a></div>
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
		
		// "normale" Korrekturkurse (aus UV): Elementweise durch array $lehrer[$nr]["korrekturkurs"] gehen
		for ($j = 0; $j < count($lehrer[$nr]["korrekturkurs"]); $j++) {
			echo '<div class="rahmen" id="kurs'.$j.'">';
			echo '<input type="hidden" name="korrekturkurs'.$j.'" value="'.$lehrer[$nr]["korrekturkurs"][$j]["klasse"]." ".$lehrer[$nr]["korrekturkurs"][$j]["fach"].'">'."\n";
			echo '<div class="rahmentitle">'.$lehrer[$nr]["korrekturkurs"][$j]["klasse"]." ".$lehrer[$nr]["korrekturkurs"][$j]["fach"].':</div>'."\n";
			echo '<div class="rahmenrest">
						<select id="wieoft'.$j.'" name="häufigkeit'.$j.'" class="häufigkeit" data-andere="true" data-nicht="true">';
			if ($halbjahr_nr == '1') {
				echo '			<option value="_">--Häufigkeit:--</option>
								<option value="1">1x im Halbjahr</option>
								<option value="2">2x im Halbjahr</option>
								<option value="3">3x im Halbjahr</option>';
			}
			if ($halbjahr_nr == '2') {
				echo '			<option value="_">Wie oft wurde (bzw. wird noch) geschrieben?</option>
								<option value="0">kein Mal in diesem Halbjahr</option>
								<option value="1">1x in diesem Halbjahr</option>
								<option value="2">2x in diesem Halbjahr</option>
								<option value="3">3x in diesem Halbjahr</option>';
			}
			echo '				<option value="a">andere Eingabe machen...</option>
								<option value="n">Diesen Kurs habe ich nicht.</option>
						</select>'."\n";
			echo '<div id="normal'.$j.'">
					<select name="dauer'.$j.'">';
			if ($lehrer[$nr]["korrekturkurs"][$j]["klasse"] === 'Q1') {
				echo '<option value="0">--Dauer:--</option>
						<option value="90">90 min.</option>
						<option value="135">135 min.</option>
						<option value="180">180 min.</option>
						<option value="225">225 min.</option>
						<option value="a">andere Dauer eingeben...</option>'; // <option value="225">225 min.</option>
			}
			else {
				echo '<option value="0">--Dauer:--</option>
						<option value="1">1 Schulstunde</option>
						<option value="60">60 min.</option>
						<option value="2">2 Schulstunden</option>
						<option value="3">3 Schulstunden</option>
						<option value="4">4 Schulstunden</option>
						<option value="5">5 Schulstunden</option>
						<option value="a">andere Dauer eingeben...</option>';
			}
			echo '</select>'."\n";
			echo '<div id="anders_second'.$j.'" style="display:none;"><textarea class="andere_dauer" placeholder="andere Dauer" name="dauer_anders'.$j.'"></textarea></div>'."\n";
			echo '<label for="schreiber">Anzahl schreibende Schüler: <input id="schreiber" type="number" name="schreiber'.$j.'"></label>'."\n";
			echo '<textarea class="bemerkung" placeholder="ggf. Bemerkung" name="bemerkung'.$j.'" style="margin-right:3em;"></textarea>';
			echo '<input type="hidden" name="nachschr'.$j.'" value="nein">'."\n";
			if (($lehrer[$nr]["korrekturkurs"][$j]["klasse"] === 'Q1') && ($halbjahr_nr == '2')) { $list_item = '- '; $list_style = ' style="margin:0 0 0 0.8em;text-indent:-0.8em;"'; } else { $list_item = ''; $list_style = ''; }
			echo '<div style="font-size:74%;margin:0;"><div'.$list_style.'>'.$list_item.'Ich musste bei <input type="number" name="nachschr'.$j.'" value="0"> Termin(en) dieses Halbjahres eine Nachschreibearbeit erstellen.</div>'."\n";
			if (($lehrer[$nr]["korrekturkurs"][$j]["klasse"] === 'Q1') && ($halbjahr_nr == '2')) {
				echo '<br><div'.$list_style.'>- Von den schreibenden Schülern fertigten <input type="number" name="facharbeiten'.$j.'" value="0"> im 1. Quartal eine Facharbeit an.</div>'."\n";
			}
			echo '</div>'."\n";
			echo '</div>'."\n";
			echo '<div class="andereeingabe" id="anders'.$j.'" style="display:none;"><textarea class="andereeingabe" name="andereeingabe'.$j.'"
				placeholder="Der Kurs lässt sich nicht mit den angebotenen Vorgaben erfassen? Geben Sie hier als Prosa Ihre Informationen zu den Korrekturen in diesem Kurs ein."></textarea>
			</div>'."\n";
			echo '<div class="nicht" id="nicht'.$j.'" style="display:none;"><textarea class="nicht" name="nicht'.$j.'" placeholder="ggf. Erläuterung"></textarea></div>'."\n";
			echo '</div></div>';
		}
		
		// Abi Erst-Korrekturen: Elementweise durch array $lehrer[$nr]["abi_erst"] gehen
		foreach ($lehrer[$nr]["abi_erst"] as $j => $kurs) {
			echo '<div class="rahmen" id="abi_erst'.$j.'">';
			echo '<input type="hidden" name="abi_erst'.$j.'" value="'.$lehrer[$nr]["abi_erst"][$j]["klasse"].' '.$lehrer[$nr]["abi_erst"][$j]["fach"].'-'.$lehrer[$nr]["abi_erst"][$j]["kursart"].$lehrer[$nr]["abi_erst"][$j]["kursnr"].'">'."\n";
			echo $lehrer[$nr]["abi_erst"][$j]["klasse"]." ".$lehrer[$nr]["abi_erst"][$j]["fach"].":\n";
			echo '<select id="wieoft'.$j.'" name="häufigkeit'.$j.'" class="häufigkeit">
					<option value="normal'.$lehrer[$nr]["abi_erst"][$j]["kursart"].'">1x Vorabi + 1x Abi-Erstkorrektur</option>
					<option value="a">andere Eingabe machen...</option>
					<option value="n">Diesen Kurs habe ich nicht.</option>
				  </select>'."\n";
			echo '('.$lehrer[$nr]["abi_erst"][$j]["kursart"].$lehrer[$nr]["abi_erst"][$j]["kursnr"].')'."\n";
			echo '<input type="hidden" name="dauer'.$j.'" value="Abi">';
			echo '<input type="hidden" name="dauer_anders'.$j.'" value="">';
			echo '<div id="normal'.$j.'">'."\n";

			echo '<label for="schreiber">Anzahl schreibende Schüler: <input id="schreiber" type="number" name="schreiber'.$j.'" value="'.$lehrer[$nr]["abi_erst"][$j]["anzahl"].'"></label>'."\n";
			echo '<textarea class="bemerkung" placeholder="ggf. Bemerkung" name="bemerkung'.$j.'"></textarea>';
			echo '<input type="hidden" name="nachschr'.$j.'" value="nein">';
			echo '</div>'."\n";
			echo '<div class="andereeingabe" id="anders'.$j.'" style="display:none;"><textarea class="andereeingabe" name="andereeingabe'.$j.'"
				placeholder="Der Kurs lässt sich nicht mit den angebotenen Vorgaben erfassen? Geben Sie hier als Prosa Ihre Informationen zu den Korrekturen in diesem Kurs ein."></textarea>
			</div>'."\n";
			echo '<div class="nicht" id="nicht'.$j.'" style="display:none;"><textarea class="nicht" name="nicht'.$j.'" placeholder="ggf. Erläuterung"></textarea></div>'."\n";
			echo '</div>';
		}
		
		// Abi Zweit-Korrekturen: Elementweise durch array $lehrer[$nr]["abi_zweit"] gehen
		foreach ($lehrer[$nr]["abi_zweit"] as $j => $kurs) {
			echo '<div class="rahmen" id="abi_zweit'.$j.'">';
			echo '<input type="hidden" name="abi_zweit'.$j.'" value="'.$lehrer[$nr]['abi_zweit'][$j]['klasse'].' '.$lehrer[$nr]['abi_zweit'][$j]['fach'].'-'.$lehrer[$nr]["abi_zweit"][$j]["kursart"].$lehrer[$nr]["abi_zweit"][$j]["kursnr"].' von '.$lehrer[$nr]['abi_zweit'][$j]['kurs_von'].'">'."\n";
			echo $lehrer[$nr]["abi_zweit"][$j]["klasse"]." ".$lehrer[$nr]["abi_zweit"][$j]["fach"].":\n";
			echo '<select id="wieoft'.$j.'" name="häufigkeit'.$j.'" class="häufigkeit">
					<option value="normal'.$lehrer[$nr]["abi_zweit"][$j]["kursart"].'">Abi-Zweitkorrektur</option>
					<option value="a">andere Eingabe machen...</option>
					<option value="n">Diesen Korrekturkurs habe ich nicht.</option>
				  </select>'."\n";
			echo '('.$lehrer[$nr]["abi_zweit"][$j]["kursart"].$lehrer[$nr]["abi_zweit"][$j]["kursnr"].' von '.$lehrer[$nr]["abi_zweit"][$j]["kurs_von"].')'."\n";
			echo '<input type="hidden" name="dauer'.$j.'" value="Abi">';
			echo '<input type="hidden" name="dauer_anders'.$j.'" value="">';
			echo '<div id="normal'.$j.'">'."\n";

			echo '<label for="schreiber">Anzahl schreibende Schüler: <input id="schreiber" type="number" name="schreiber'.$j.'" value="'.$lehrer[$nr]["abi_zweit"][$j]["anzahl"].'"></label>'."\n";
			echo '<textarea class="bemerkung" placeholder="ggf. Bemerkung" name="bemerkung'.$j.'"></textarea>';
			echo '<input type="hidden" name="nachschr'.$j.'" value="nein">';
			echo '</div>'."\n";
			echo '<div class="andereeingabe" id="anders'.$j.'" style="display:none;"><textarea class="andereeingabe" name="andereeingabe'.$j.'"
				placeholder="Der Kurs lässt sich nicht mit den angebotenen Vorgaben erfassen? Geben Sie hier als Prosa Ihre Informationen zu den Korrekturen in diesem Kurs ein."></textarea>
			</div>'."\n";
			echo '<div class="nicht" id="nicht'.$j.'" style="display:none;"><textarea class="nicht" name="nicht'.$j.'" placeholder="ggf. Erläuterung"></textarea></div>'."\n";
			echo '</div>';
		} // einzelne Zweit-Erstkorrekturen $j durchlaufen Ende.
		
		// Abi mündliche Prüfungen: Elementweise durch array $lehrer[$nr]["abi_mue"] gehen
		foreach ($lehrer[$nr]["abi_mue"] as $j => $block) {
			echo '<div class="rahmen" id="abi_mue_'.$j.'">';
			echo $lehrer[$nr]["abi_mue"][$j]['prüArt_lang'].'<br><br>'."\n";
			echo '<u>'.$lehrer[$nr]['abi_mue'][$j]['fach'].' '.$lehrer[$nr]["abi_mue"][$j]["kursart"].'</u>:&nbsp;&nbsp;&nbsp;';
			echo '<input type="hidden" name="abi_mue_A4_fach_und_kursart'.$j.'" value="'.$lehrer[$nr]['abi_mue'][$j]['fach'].' '.$lehrer[$nr]["abi_mue"][$j]["kursart"].'">';
			$singular_plural = 'Prüfungsblöcke';
			if ($lehrer[$nr]["abi_mue"][$j]['wert'] === '1') $singular_plural = 'Prüfungsblock';
			echo $lehrer[$nr]["abi_mue"][$j]['wert'].' '.$singular_plural.' als Prüfer'."\n";
			echo '<input type="hidden" name="abi_A4_mue_bloecke'.$j.'" value="'.$lehrer[$nr]["abi_mue"][$j]['wert'].'">';
			echo '('.$lehrer[$nr]["abi_mue"][$j]['kurs(e)'].')'."\n";
			echo '<span style="font-size:70%;">'.$lehrer[$nr]["abi_mue"][$j]["hinweis"].'</span><br>'."\n";
			echo '<div class="abi_mue_bemerkung" id="abi_mue_bemerkung'.$j.'">
					<textarea class="bemerkung" name="abi_mue_bemerkung'.$j.'" placeholder="ggf. Bemerkung"></textarea>
				  </div>'."\n";
			echo '</div>';
		} // einzelne mündliche Prüfungen $j durchlaufen Ende.
		
		// Klassenleitung:
		echo '<div class="rahmen">
				Haben Sie in diesem Halbjahr eine Klassenleitung in der Sek.Ⅰ ?<sup>2)</sup>
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
				Kommentarfeld: zusätzliche Erläuterungen, z.B.<br>
				- Ich musste einen Abivorschlag bei der Bezirksregierung bzw. in Soest einreichen (Anzahl Vorschläge, mit/ohne Erwartungshorizont)<br>
				- Hier oben fehlt ein Korrekturkurs. => Infos hier bitte per Hand eintragen.<br>
				- Beurlaubungen (z.B. Elternzeit) in diesem Halbjahr  => von wann bis wann?<br>
				- Ich habe zusätzliche Korrekturen für jemand anders übernommen => Infos hier bitte per Hand eintragen.<br>
				<textarea id="kommentarfeld" placeholder="zusätzl. Kommentarfeld" name="kommentar"></textarea>
			</div>';
		echo '<div class="rahmen">Bitte vergewissern Sie sich, dass alle Eintragungen richtig sind, denn sie werden
					beim Klicken auf "Absenden" sofort abgespeichert: <button>Absenden!</button><br>
					<div style="font-size:0.8em;">Oder:
					<a href="../index.php">Halbjahr wechseln</a> |
					<a href="index.php">Lehrer wechseln</a> |
					<a href="index.php?logout=1">Logout ohne Speichern</a></div>
					</div>'."\n";
		echo "</form>\n";
		echo '<div style="font-size:0.8em;"><sup>1)</sup> Hinweise zur Oberstufe:<br>
				- Es werden ggf. keine Kursnummern angezeigt. Wenn Sie in einer Stufe zwei Kurse derselben Kursart im gleichen Fach haben (z.B. Q1: Ku-GK1 und Ku-GK3),
				  können Sie sich aussuchen, welche Zahlen Sie bei welchem Kurs eintragen.<br>
				- Außerdem können vereinzelt Kurse mit angezeigt werden, in denen kein/e Schüler/in das Fach schriftlich gewählt hat. Dann bitte einfach 0 eintragen oder
				  einen Kommentar o.Ä.<br>
				<sup>2)</sup> Unter Klassenleitung in diesem Formular müssen <i>keine Stufenleitungen in der Oberstufe</i> eingegeben werden, da diese aus einem anderen Topf entlastet werden
				  (direkt in der vorderen Entlastungs-Spalte in der \'Unterrichtsverteilung Lehrer\', schon vor der Entlastungsstundenberechnung,
				  die hier jetzt vorgenommen werden soll).</div>'."\n\n";


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
else {
	$feldart = $key;
	$letzte_gefundene_zahl = null;
}
// alt: if (ctype_digit(substr($key, -1))) { $feldart = substr($key, 0, -1); $knumber = substr($key, -1); } else { $feldart = $key; }
// if ($feldart == "häufigkeit") { $zeile[] = "korrekturkurs".$knumber."\t".$lehrer[$lnumber]["korrekturkurs"][$knumber]["klasse"]." ".$lehrer[$lnumber]["korrekturkurs"][$knumber]["fach"]."\n"; }
$value = preg_replace('/[\n\r]/', '/', $value);
$zeile[] = $key."\t".$value."\n";
}
$dateinamevollerpfad = $_SERVER["DOCUMENT_ROOT"].'untis/rstkxzw3/zfweo87/'.$schuljahr['pfad'].'/'.$halbjahr['kurz'].'roh/'.$_POST["kuerzel"];
if (file_exists($dateinamevollerpfad.'.txt')) { $dateinamevollerpfad = $dateinamevollerpfad.'.'.date("Y-m-d-H-i-s"); }
$dateinamevollerpfad = $dateinamevollerpfad.'.txt';
$datei = fopen($dateinamevollerpfad,"w");                // und so abspeichern
fwrite($datei, implode($zeile));
fclose($datei);
// Und zur Info zeigen, was abgespeichert wurde:

$translate = array ("kuerzel" => "Lehrer", "korrekturkurs" => "Klasse/Kurs", "häufigkeit" => "Häufigkeit", "dauer" => "Dauer", "schreiber" => "Anzahl Schreiber", "bemerkung" => "Bemerkung", "andereeingabe" => "andere Eingabe",
					"nicht" => "(ggf. Bemerkung falscher Kurs)", "klassenleitung_janein" => "Klassenleitung", 'abi_erst' => 'Vorabi + Abi-Erstkorrektur', 'abi_zweit' => 'Abi-Zweitkorrektur',
					"klassenleitung_klasse" => "Klassenleitung Klasse", "klassenleitung_nr" => "1./2. Klassenl.", "klassenleitung_aufteilung" => "Aufteilung", "kommentar" => "ggf. zusätzl. Kommentar",
					'abi_mue_A4_fach_und_kursart' => 'mündl. Abi (A4)', 'abi_A4_mue_bloecke' => 'Prüfungsblöcke', 'abi_mue_bemerkung' => 'Bemerkung');
echo 'Diese Eingaben wurden gespeichert:'."\n";
$vorherige_knumber = null;
foreach ($_POST as $key => $value) {  // ALLE übergebenen Formulardaten durchlaufen
$knumber = -1;
// feldart und ggf. Nummer aus key extrahieren: ("dauer4" -> "dauer", "4")
preg_match_all('/\d+$/', $key, $matches, PREG_OFFSET_CAPTURE);
$gefundene_zahlen = $matches[0];
if (!empty($gefundene_zahlen)) {
	$letzte_gefundene_zahl = $gefundene_zahlen[(count($gefundene_zahlen) - 1)];
	$feldart = substr($key, 0, $letzte_gefundene_zahl[1]) ;
	$knumber = $letzte_gefundene_zahl[0];
}
else { $feldart = $key; }

// alt; if (ctype_digit(substr($key, -1))) { $feldart = substr($key, 0, -1); $knumber = substr($key, -1); } else { $feldart = $key; }

// alt: if (strpos("(kuerzel|korrekturkurs|klassenleitung_janein|klassenleitung_klasse|kommentar|abi_erst|abi_zweit|abi_mue_A4_fach_und_kursart)", $feldart))
if ($vorherige_knumber !== $knumber) {
	if ($vorherige_knumber !== null) { echo '</div>'."\n"; }
	echo "\n".'<div class="rahmen">';
}
// zwischen Klassenleitung und Kommentar wechselt keine Zahl, daher zusätzlih noch folgender Fall:
if (strpos("(kommentar)", $feldart)) { echo '</div>'."\n"; echo "\n".'<div class="rahmen">'; }
// if ($feldart == "häufigkeit") { echo '<div class="readonly">Klasse/Kurs: '.$lehrer[$lnumber]["korrekturkurs"][$knumber]["klasse"]." ".$lehrer[$lnumber]["korrekturkurs"][$knumber]["fach"]."</div>\n"; }
echo '<div class="readonly">'.strtr($feldart, $translate).": ";
echo $value."</div>\n";
// alt: if (strpos("(kuerzel|nicht|abi_mue_bem|klassenleitung_klasse|klassenleitung_janein|klassenleitung_aufteilung|kommentar)", $feldart)) => jetzt nach oben in die erste Bedingung

$vorherige_knumber = $knumber;
} // Formulardaten durchlaufen Ende
echo '</div>'."\n";

echo '  <a href="../index.php">Halbjahr wechseln</a>
		<a href="index.php?logout=1">Logout</a>			';
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