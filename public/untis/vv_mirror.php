<?php

$request = array_merge ($_GET, $_POST);

// nur mit Passwort geht es weiter:
if (!https_is_on() || !isset($request['vv_password']) || !password_verify($request['vv_password'], '$2y$10$hxpghx0DICyrpnfpMrk1feV5uuQmDp/YRmYkwLXyunsW8uk2thH12'))	{
	echo 'unauthorized or not accessed via https';
	exit();
}

// Passwort OK:
else {
	
	if (isset($request['vv_get_filelist'])) {
	
		$dir_content = array();
		foreach (glob_recursive('./*') as $fullname)	$dir_content[$fullname] = array (	'type' => is_dir($fullname) ? 'directoy' : 'file',
																							'date' => filemtime($fullname) );
		echo json_encode($dir_content);
		
	}
	
	else if (isset($request['vv_upload_file'])) {
		
		// upload entgegennehmen:
		$list = array();
		$error = array();
		$success = false;
		if (isset($_FILES['uploaded_file'])) {
			$file = $_FILES['uploaded_file'];
			move_uploaded_file($file['tmp_name'], './latest_update.zip');
			$zip = new ZipArchive;	
			if ($zip->open('./latest_update.zip') === TRUE && deleteDirectory('./temp_upload') === true && $zip->extractTo('./temp_upload') === true) {
				$zip->close();
				$list = json_decode(file_get_contents('./temp_upload/list.json'));
				foreach ($list as $new_file) {
					$temp_name = './temp_upload/'.substr($new_file, 2);
					if (!rename($temp_name, $new_file)) $error[] = $new_file;
				}
				// rename('./temp_upload/list.json', './latest_upload_list.json'); => nicht nötig, die zip-Datei ist ja eh noch da, da kann man noch alles vom letzten Upload sehen!
				deleteDirectory('./temp_upload');
				if (count($error) === 0)	$success = true;
			}
		}
		
		$response = array ( 'success'	=> $success,
							'request'	=> $request,
							'files'		=> $_FILES,
							'list'		=> $list,
							'error'		=> $error		);
		
		echo json_encode($response);
		
	}
}


// Funktionen:
function glob_recursive($pattern, $flags = 0) { // Does not support flag GLOB_BRACE!

	$files = glob($pattern, $flags);

	foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
		$files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
     }

	return $files;

}

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

function https_is_on() {
  return
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || $_SERVER['SERVER_PORT'] == 443;
}