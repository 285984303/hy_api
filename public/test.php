<?php
$dbh = new PDO('mysql:host=111.230.215.100:10044;dbname=justclub', 'root', 'cnqqazMjust2Wsk');  
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
$dbh->exec('set names utf8'); 
/*Ìí¼Ó*/
//$sql = "INSERT INTO `user` SET `login`=:login AND `password`=:password"; 
$sql = "INSERT INTO `user` (`user_name` ,`password`)VALUES (:login, :password)";  $stmt = $dbh->prepare($sql);  $stmt->execute(array(':login'=>'kevin2',':password'=>''));  
echo $dbh->lastinsertid();  
/*ÐÞ¸Ä*/
// $sql = "UPDATE `user` SET `password`=:password WHERE `user_id`=:userId";  
// $stmt = $dbh->prepare($sql);  
// $stmt->execute(array(':userId'=>'7', ':password'=>'4607e782c4d86fd5364d7e4508bb10d9'));  
// echo $stmt->rowCount(); 
/*É¾³ý*/
// $sql = "DELETE FROM `user` WHERE `user_name` LIKE 'rick'"; //kevin%  
// $stmt = $dbh->prepare($sql);  
// $stmt->execute();  
// echo $stmt->rowCount();  
/*²éÑ¯*/
$login = 'rick%';  
$sql = "SELECT * FROM `user` WHERE `user_name` LIKE :login";  
$stmt = $dbh->prepare($sql);  
$stmt->execute(array(':login'=>$login));  
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){     
 //print_r($row);
 echo json_encode($row);  
}  
print_r( $stmt->fetchAll(PDO::FETCH_ASSOC)); 
?>