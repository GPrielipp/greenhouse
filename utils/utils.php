<?php

function error($msg) {
	echo json_encode(['error' => $msg]);
	die;
}

?>
