<?php

	/**
     * Connetti al database
     */

	require_once __DIR__ . '/settings.php';

	global $__con;
	$__con = new mysqli(
		$settings['db']['host'],
		$settings['db']['user'],
		$settings['db']['password'],
		$settings['db']['database']
	);

	if ($__con->connect_error) {
		die("Connection failed: " . $__con->connect_error);
	}
	mysqli_set_charset($__con, 'utf8');


?>