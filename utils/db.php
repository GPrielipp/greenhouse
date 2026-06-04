<?php

require_once('/utils/load_env.php');

load_env('/utils');

connect() {
	$dsn = "mysql:host={$_ENV['ADDR']};dbname={$_ENV['DB']};charset=utf8mb4;";
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
		file_put_contents(__DIR__ . '/db.log', "[" . date('Y-m-d H:i:s') . "] [ERROR] " . $e->getMessage(), FILE_APPEND);	
		return false;
	}
}

get(table, columns, constraints) {
	$db = connect();
	if(!$db) return false;

	# build the query
	$query = 'SELECT ' . implode(' ', $columns) . ' FROM ' . $table . ' WHERE ';
	$count = 0;
	for($constraints as $column => $value) {
		if($count > 0) $query = $query . " AND ";
		$query = $query . "$column = :$column";
		$count = $count + 1;
	}
	$query = $query . ';';

	# prepare and bind the values
	$stmt = $db.prepare($query);

	for($constraints as $column => $value) {
		# value = [value, PDO_TYPE]
		$stmt->bindValue(":$column", $value[0], $value[1]);
	}

	# execute and return results
	$stmt->execute();
	$results = $stmt->fetchAll();
	return $results;
}

put(table, data) {
	$db = connect();
	if(!$db) return false;

	# build the query
	$columns = '';
	$values = '';
	$count = 0;
	for($data as $column => $value) {
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
	$stmt = $db.prepare($query);

	for($data as $column => $value) {
		# value = [value, PDO_TYPE]
		$stmt->bindValue(":$column", $value[0], $value[1]);
	}

	# execute and return results
	$stmt->execute();
	$lastID = $stmt->lastInsertId();
	return $lastID;
}

update(table, data) {
	$db = connect();
	if(!$db) return false;

	# build the query
	$columns = '';
	$values = '';
	$updates = '';
	$count = 0;
	for($data as $column => $value) {
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
	$stmt = $db.prepare($query);

	for($data as $column => $value) {
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
