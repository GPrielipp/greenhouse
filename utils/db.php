<?php

require_once(__DIR__ . '/load_env.php');

load_env(__DIR__);

function connect() {
	$dsn = "mysql:host={$_ENV['ADDR']};dbname={$_ENV['DB']};";
	error_log("[DEBUG] dsn = '$dsn'");
	$opts = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];

	try {
		$pdo = new PDO($dsn, $_ENV['USER'], $_ENV['PASSWORD'], $opts);
		return $pdo;
	} catch (\PDOException $e) {
		// log error message
		file_put_contents('/var/log/db/db.log', "[" . date('Y-m-d H:i:s') . "] [ERROR] " . $e->getMessage() . "\n", FILE_APPEND);	
		return false;
	}
}

function get($table, $columns, $constraints) {
	$db = connect();
	if(!$db) return false;

	# build the query
	$query = 'SELECT ' . implode(' ', $columns) . ' FROM ' . $table . ' WHERE ';
	$count = 0;
	foreach($constraints as $column => $value) {
		if($count > 0) $query = $query . " AND ";
		$query = $query . "$column = :$column";
		$count = $count + 1;
	}
	$query = $query . ';';

	# prepare and bind the values
	$stmt = $db->prepare($query);

	foreach($constraints as $column => $value) {
		# value = [value, PDO_TYPE]
		$stmt->bindValue(":$column", $value[0], $value[1]);
	}

	# execute and return results
	$stmt->execute();
	$results = $stmt->fetchAll();
	return $results;
}

function put($table, $data) {
	$db = connect();
	if(!$db) return false;

	# build the query
	$columns = '';
	$values = '';
	$count = 0;
	foreach($data as $column => $value) {
		if($count > 0) {
			$columns = $columns . ',';
			$values = $values . ',';
		}
		$columns = $columns . $column;
		$values = $values . ":$column";
		$count = $count + 1;
	}
	$query = "INSERT INTO $table ($columns) VALUES ($values);";

	# prepare and bind the values
	if($db === null) error_log('[!!! ERROR !!!] PDO is null');
	
	$stmt = $db->prepare($query);

	foreach($data as $column => $value) {
		# value = [value, PDO_TYPE]
		$stmt->bindValue(":$column", $value[0], $value[1]);
	}

	# execute and return results
	$stmt->execute();
	$lastID = $db->lastInsertId();
	return $lastID;
}

function update($table, $data) {
	$db = connect();
	if(!$db) return false;

	# build the query
	$columns = '';
	$values = '';
	$updates = '';
	$count = 0;
	foreach($data as $column => $value) {
		if($count > 0) {
			$columns = $columns . ',';
			$values = $values . ',';
			$updates = $updates . ',';
		}
		$columns = $columns . $column;
		$values = $values . ":$column";
		$updates = $updates . "$column=:update_$column";
		$count = $count + 1;
	}
	$query = "INSERT INTO $table ($columns) VALUES ($values) ON DUPLICATE KEY UPDATE $updates;";

	# prepare and bind the values
	$stmt = $db->prepare($query);

	foreach($data as $column => $value) {
		# value = [value, PDO_TYPE]
		$stmt->bindValue(":$column", $value[0], $value[1]);
		$stmt->bindValue(":update_$column", $value[0], $value[1]);
	}

	# execute and return results
	$stmt->execute();
	$lastID = $stmt->lastInsertId();
	return $lastID;
}

?>
