<?php

require_once(__DIR__.'/../utils/utils.php');

$method = $_SERVER['REQUEST_METHOD'];

if($method !== 'POST') {
	error("Incorrect method '$method'. Expected 'POST'");
}

require_once(__DIR__.'/../utils/db.php');

# get the data from the message body
$post_data = file_get_contents('php://input');
$json_data = json_decode($post_data, true);

if(!isset($json_data['readings']) || !isset($json_data['refresh_rate']) || !isset($json_data['mac_addr'])){
	error("Missing crucial data. Received '" . $post_data . "'.");
}

$sensor_id = put('sensor_table', [
	'readings' => [json_encode($json_data['readings']), PDO::PARAM_STR],
	'refresh_rate' => [$json_data['refresh_rate'], PDO::PARAM_INT],
	'mac_addr' => [$json_data['mac_addr'], PDO::PARAM_STR],
]);

echo $sensor_id;

?>
