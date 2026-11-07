<?php 

$conn=new mysqli('localhost','root','','vanni_campus_connect');
if($conn->connect_error){
    die("Connection failed:".$conn->connect_error);

}
echo "Database connected successfully.";

?>



