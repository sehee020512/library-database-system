<?php

function dbconnect($host, $id, $pass, $db){
	$connect=mysqli_connect($host, $id, $pass, $db);
	if ($connect == false){
		die('Database Not Connected :'.mysqli_error());
	} return $connect;
}

?>