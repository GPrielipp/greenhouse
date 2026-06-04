<?php

function load_env(path) {
	$file = fopen("$path/.env", 'r');
	if (! $data) return false;

	while(($line = fgets($file)) {
		$pieces = explode("=", $line);
		$_ENV[$pieces[0]] = $pieces[1];
	}

	fclose($file);

	return true;
}

?>
