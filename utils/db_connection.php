<?php

	/**
     * Connetti al database
     */

	require_once __DIR__ . '/settings.php';
	require_once __DIR__ . '/../lib/dotenv/vendor/autoload.php';

	$env = Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();

	global $__con;
	$__con = new mysqli(
		$_ENV['DB_HOST'],
		$_ENV['DB_USER'],
		$_ENV['DB_PASS'],
		$_ENV['DB_NAME']
	);

	if ($__con->connect_error) {
		die("Connection failed: " . $__con->connect_error);
	}
	mysqli_set_charset($__con, 'utf8');


?>