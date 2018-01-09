<?php

// create sqlite database connection
if (!$db = sqlite_open('db', 0777, $sqliteerror)) {
	die($sqliteerror);
}

// take option from $_GET or set default action "get"
$action = isset($_GET['action']) ? $_GET['action'] : "get";

// select function by action
switch ($action) {
	case 'get':
		echo get($db);
		break;
	case 'insert':
		echo add($db);
		break;
	case 'delete':
		echo delete($db);
		break;
	case 'update':
		echo update($db);
		break;
}

// returns list of users records as json
function get($db) {
	// takes all records from database
	$result = sqlite_query($db, 'SELECT * FROM tblusers');
	$users = Array();
	while ($user = sqlite_fetch_array($result))
		$users[] = $user;
	// generate json
	$json = Array();
	for ($i = 0; $i < count($users); $i++) {
		$rec = "{";
		$rec .= 'id: "'.$users[$i]['id'].'",';
		$rec .= 'name: "'.$users[$i]['name'].'",';
		$rec .= 'age: "'.$users[$i]['age'].'",';
		$rec .= 'gender: "'.$users[$i]['gender'].'",';
		$rec .= 'country: "'.$users[$i]['country'].'",';
		$rec .= 'city: "'.$users[$i]['city'].'",';
		$rec .= 'phone: "'.$users[$i]['phone'].'",';
		$rec .= 'email: "'.$users[$i]['email'].'",';
		$rec .= "}";
		$json[] = $rec;
	}
	$json = '['.implode(',', $json).']';
	return $json;
}

// add user to database
function add($db) {
	// takes data from POST array
	$name = $_POST['name'];
	$age = $_POST['age'];
	$gender = $_POST['gender'];
	$country = $_POST['country'];
	$city = $_POST['city'];
	$phone = $_POST['phone'];
	$email = $_POST['email'];
	// add to database
	$query = "INSERT INTO 'tblusers' VALUES(null, '{$name}','{$age}','{$country}','{$city}','{$gender}','{$phone}','{$email}')";
	$result = sqlite_query($query, $db);
	if ($result) return sqlite_last_insert_rowid($db);
	return "false";
}

// delete user record from database
function delete($db) {
	if (isset($_POST['id'])) {
		$result = sqlite_query("DELETE FROM tblusers WHERE id='{$_POST['id']}'", $db);
		if ($result)
			return "true";
	}
	return "false";
}

// update user record in database
function update($db) {
	if (isset($_POST['id'])) {
		// takes data from POST array
		$id = $_POST['id'];
		$name = $_POST['name'];
		$age = $_POST['age'];
		$gender = $_POST['gender'];
		$country = $_POST['country'];
		$city = $_POST['city'];
		$phone = $_POST['phone'];
		$email = $_POST['email'];
		// update query
		$query = "UPDATE tblusers SET name='{$name}', age='{$age}', gender='{$gender}', country='{$country}', city='{$city}', phone='{$phone}', email='{$email}' WHERE id='{$id}'";
		$result = sqlite_query($query, $db);
		if($result)
		    return "true";
	}
	return "false";
}

/*require_once("../../connector/data_connector.php");
require_once("../../connector/db_sqlite.php");

if ($db = sqlite_open('db', 0666, $sqliteerror)) {
    sqlite_query($db, 'CREATE TABLE "tblusers" ("id" INTEGER PRIMARY KEY  NOT NULL , "name" VARCHAR(60), "age" VARCHAR(3), "country" VARCHAR(60), "city" VARCHAR(60), "gender" VARCHAR(6), "phone" VARCHAR(60), "email" VARCHAR(60))');
}
else
	 die($sqliteerror);
$data = new JSONDataConnector($db,"SQLite");
$data->render_table("tblusers", "id", "name,age,country,city,phone,gender,email");*/
?>