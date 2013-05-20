<?php
//database driver

include('../config.php');
$conn = NULL;
$MAX_BAD_WORD_LENGTH = 255;
$MIN_COMMENTS_LENGTH = 10;
$PREVENT_URLS_IN_COMMENTS = TRUE;
$ERROR_MSG_URLS_NOT_ALLOWED = "URLs are not allowed in comments.";
$ENABLE_EMAIL_FIELD = TRUE;
$ENABLE_URL_FIELD = TRUE;
$ENABLE_COMMENT_FIELD = TRUE;
$MIN_SECONDS_BETWEEN_POSTS = 120;
$ERROR_MSG_FLOOD_DETECTED = "You are attempting to post too frequently.";
$READ_ONLY_MODE = FALSE;
$MAX_WORD_LENGTH = 40;
$ERROR_MSG_MAX_WORD_LENGTH = "You attempted to use a word that was too long.";
$MIN_POST_DELAY = 5;
$MAX_POST_DELAY = 7200;
$ERROR_MSG_MIN_DELAY_STRING = "You tried to post too quickly.";
$ERROR_MSG_MAX_DELAY_STRING = "You waited too long to post.";
$MODERATION_ENABLED = FALSE;

function database_open(){
	global $conn;
	global $current_set;
	$conn = new mysqli($DB['host'], $DB['user'], $DB['pwd'], $DB['db']);
	return $conn;
}

function database_close(){
	global $conn;
	$conn->close($conn);
}

function database_forward($forward_count){
	global $conn;
	global $current_set;
	$current_set = $current_set+intval($forward_count);
}

function database_next(){
	global $conn;
	global $current_set;
	$current_set+=1;
	if (!is_null($conn->connect_errno))
		return false;
	$result = $conn->query("SELECT * FROM `wish` LIMIT 1 OFFSET `$current_set`");
	if (!$result)
		return false;
	$record = $result->fetch_assoc();
	$record = array_map('rawurldecode', $record);
	$record = array_map('htmlspecialchars', $record);
	!isset($record['approved']) && $record['approved'] = 'true';
	
	return $record;
}

function database_entries_action($idArray, $banip=false, $action){
	global $conn;
	$conn = database_open();
	if (!is_null($conn->connect_errno))
		die('Error connect database');
	foreach ($idArray as $id){
		
		$result = $conn->query("SELECT * FROM `wish` WHERE id=$id");
		$record = $result->fetch_array();
		
		$record = $result->array_map('rawurldecode', $record);
		if ($banip){
			if (isset($record['ipaddress'])&&!is_null($record['ipaddress'])&&!empty($record['ipaddress']))
				$ipaddress = $record['ipaddress'];
			if (!is_banned($ipaddress))
				ban_add($ipaddress);	
		}
		if ($action === 'delete'){
			$conn->query("DELETE * FROM `wish` WHERE id = $id ");
			
		}
		if ($action === 'approve')
			$conn->query("UPDATE `wish` SET approved='true' WHERE ip=$id");
	}	
}

function database_validate() {
	
}