<?php


$prop = new DBConnectionProperties();
$prop->driver = "MySQLi";
// $prop->driver = "PDOMySQL";

$prop->database="mmshop";
$prop->user="mmshop1";
$prop->pass="U%N6bvA:hhRT9rnL";
$prop->host="localhost";
$prop->port="3306";
$prop->is_pdo = false;


$prop->setConnectionName("default");
DBConnections::addConnection($prop);


// $prop1 = new DBConnectionProperties();
// $prop1->driver = "MySQLi";
// 
// $prop1->database="demo";
// $prop1->user="demo1";
// $prop1->pass="d3m0d3m0";
// $prop1->host="localhost";
// $prop1->port="3306";
// 
// 
// 
// $prop1->setConnectionName("mysqli_conn");
// 
// DBConnections::addConnection($prop1);




?>