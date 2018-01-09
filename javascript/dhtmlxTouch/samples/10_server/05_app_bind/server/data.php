<?php
$connector_path = "../../../../connectors/";
require_once($connector_path."data_connector.php");
require_once($connector_path."db_sqlite.php");

if (!$db = sqlite_open('db', 0777, $sqliteerror)) {
	die($sqliteerror);
}
$data = new JSONDataConnector($db,"SQLite");
$data->render_table("tblusers", "id", "name,age,gender,country,city,phone,email");
?>