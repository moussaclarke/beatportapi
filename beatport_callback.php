<?php
	$credentials = array();
	foreach ($_GET as $key => $value) { 
		$credentials[$key] = $value;
	}

	if (!empty($credentials)) print json_encode($credentials);
?>