<?php

// If all db constants are configured it tries to log the api call
if(DB_HOST != '' && DB_NAME != '' && DB_USER != '' && DB_PASSWORD != ''){

	try{
		// Make the db connection
		$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//Create table api_calls if it doesn't exist
		$db->exec("CREATE TABLE IF NOT EXISTS `api_calls` (
					`id` integer AUTO_INCREMENT NOT NULL,
					`http_referer` varchar(255) NOT NULL,
					`query_string` varchar(255),
					`browser` varchar(255),
					`ip` varchar(15),
					`time_stamp` varchar(255),
					PRIMARY KEY (`id`)) 
					CHARACTER SET utf8 COLLATE utf8_general_ci;") or die(print_r($db->errorInfo(), true));

		//Prepare the insert statement to log the call
		$stmt = $db->prepare("INSERT INTO api_calls (http_referer, query_string, browser, ip, time_stamp) VALUES (?,?,?,?,?);");

		//Execute the insert statement with the corresponding server variables
		$stmt->execute(array($_SERVER['REMOTE_ADDR'], $_SERVER['QUERY_STRING'], $_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR'], $_SERVER['REQUEST_TIME']));

	}catch(PDOException $e){
		//If there's been any problem when creating the db connection, catch the exception and display the error.
		echo "ERROR: " . $e->getMessage();
	}

}


?>