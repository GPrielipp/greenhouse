<?php

function load_env($path) {
	$file = fopen("$path/.env", 'r');
	if (! $file) return false;

	while(($line = fgets($file)) !== false) {
		$pieces = explode("=", $line);
		$_ENV[$pieces[0]] = $pieces[1];
	}

	fclose($file);

	return true;
}

?>
