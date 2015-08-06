<?php
header("Content-Type: text/html; charset=utf-8");
$time = date(' H:i');

$servername = "localhost";
$username = "root";
$password = "bbbbbb";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
$conn->query("use Office");


function get_users($conn, $check_out_of_office=false) { 
	$sql = "SELECT * FROM user";
	if ( $check_out_of_office ) {
		$sql = "SELECT * FROM user WHERE out_of_office=0";
	}
	$result = $conn->query($sql);
	$return_array = array();
	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
			$data = array();
			array_push($data, $row['id']);
			array_push($data, $row['count_muell']);
			array_push($data, $row['count_pfand']);
			array_push($data, $row['count_ein']);
			array_push($data, $row['count_aus']);
			array_push($data, $row['count_kaffee']);
			array_push($data, $row['first_name']);
			array_push($data, $row['last_name']);
			array_push($data, $row['email']);
			array_push($data, $row['out_of_office']);
			array_push($return_array, $data);	
	    }
	} 
	return $return_array;	
}
function get_users_in_order($conn) { 
	$sql = "SELECT * FROM user ORDER BY first_name";

	$result = $conn->query($sql);
	$return_array = array();
	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
			$data = array();
			array_push($data, $row['id']);
			array_push($data, $row['count_muell']);
			array_push($data, $row['count_pfand']);
			array_push($data, $row['count_ein']);
			array_push($data, $row['count_aus']);
			array_push($data, $row['count_kaffee']);
			array_push($data, $row['first_name']);
			array_push($data, $row['last_name']);
			array_push($data, $row['email']);
			array_push($data, $row['out_of_office']);
			array_push($return_array, $data);	
	    }
	} 
	return $return_array;	
}
function get_user_info($conn, $id) { 
	$sql = "SELECT * FROM user WHERE id=".$id;
	$result = $conn->query($sql);
	$return_array = array();
	if ($result->num_rows > 0) {
	    $row = $result->fetch_assoc();
	} 
	return $row;	
}
function get_works($conn) { 
	$sql = "SELECT * FROM work";
	$result = $conn->query($sql);
	$return_array = array();
	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
			$data = array();
			array_push($data, $row['id']);
			array_push($data, $row['name']);
			array_push($data, $row['show_text']);
			array_push($return_array, $data);	
	    }
	} 
	return $return_array;	
}
function get_curent_user($conn, $work_id) { 
	$users = get_users($conn, true);
	$value=99999999;
	$user_id = 0;
	foreach ($users as $user) {
		if ( $user[$work_id] < $value ) {
			$value = $user[$work_id];
			$user_id = $user[0]; 
		}
	}
	return "30";
	return $user_id;
}
function avatar($conn, $id) { 
	$sql = "SELECT * FROM user WHERE id=".$id;
	$result = $conn->query($sql);
	$return_array = array();
	if ($result->num_rows > 0) {
	    $row = $result->fetch_assoc();
	    return '<img src="avatar/'.$row['email'].'.jpg" width="202px">';		
	} 
	return false;
}
function overjump($conn, $jump_type) { 
	switch($jump_type) {
		case ("muell"):
			$work_id = 1;
			break;

		case ("pfand"):
			$work_id = 2;			
			break;

		case ("ein"):
			$work_id = 3;	
			break;

		case ("aus"):
			$work_id = 4;		
			break;

		case ("kaffee"):
			$work_id = 5;		
			break;
	}
	$user_id = get_curent_user($conn, $work_id);
	$user_info = get_user_info($conn, $user_id);
	$jumps = $user_info['overjump_'.$jump_type];
	$jumps++;
	$sql = "UPDATE user SET overjump_".$jump_type."=".$jumps." WHERE id=".$user_id;
	$result = $conn->query($sql);	
	$count = $user_info['count_'.$jump_type];
	$count++;
	$sql = "UPDATE user SET count_".$jump_type."=".$count." WHERE id=".$user_id;
	$result = $conn->query($sql);	
}
function remind($conn, $remind_type) { 
	switch($remind_type) {
		case ("muell"):
			$work_id = 1;
			break;

		case ("pfand"):
			$work_id = 2;			
			break;

		case ("ein"):
			$work_id = 3;	
			break;

		case ("aus"):
			$work_id = 4;		
			break;

		case ("kaffee"):
			$work_id = 5;		
			break;
	}
	$user_id = get_curent_user($conn, $work_id);
	$user_info = get_user_info($conn, $user_id);
	$username = strtolower($user_info['first_name'].'.'.$user_info['last_name']);
	shell_exec('./reminder.py '.$remind_type.' '.$username);
}
function done($conn, $done_type) { 
	switch($done_type) {
		case ("muell"):
			$work_id = 1;
			break;

		case ("pfand"):
			$work_id = 2;			
			break;

		case ("ein"):
			$work_id = 3;	
			break;

		case ("aus"):
			$work_id = 4;		
			break;

		case ("kaffee"):
			$work_id = 5;		
			break;
	}
	$user_id = get_curent_user($conn, $work_id);
	$user_info = get_user_info($conn, $user_id);
	$count = $user_info['count_'.$done_type];
	$count++;
	$sql = "UPDATE user SET count_".$done_type."=".$count." WHERE id=".$user_id;
	$result = $conn->query($sql);	
}

$users = get_users($conn);

if ( $_POST['action'] == 'out_of_office' ) {
	$user_id = $_POST['user'];
	$value = intval($_POST['out_of_office']);
	if ( $value == 1 ) {
		$kind = 'anw';
		$value = 0;
	} else { 
		$kind = 'abw';
		$value = 1; 
	}
	
	$desc = $kind;
	$timedate = date("Y-m-d H:i:s");
	$sql = "INSERT INTO work_log (Description, Date, User_ID) VALUES ('".$desc."','".$timedate."','".$user_id."')";
	$result = $conn->query($sql);

	$sql = "UPDATE user SET out_of_office = ".$value." WHERE id = ".$user_id.";";
	$conn->query($sql);
	header('Location: abwesend.php');
}



?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="60">
    <title>ProtonetOfficeClean</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">

	<style type="text/css">
		div { border:0px solid #888; }
		#s1 { width:1024; }
		#news { width:768px; height:300; }

    
	</style>
<script type="application/javascript" src="fastklick.js"></script> 
<script type="application/javascript">
	window.addEventListener('load', function () {
		FastClick.attach(document.body);
	}, false);
</script> 


	<meta charset="utf-8">
  </head>
  <body>

       

<nav class="navbar navbar-inverse navbar-fixed-top">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->

 <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
		<li><span style="color:white;font-size:250%"><b><?php echo $time ?>&ensp;</b></span></li>
    	<li><a href="index.php">Übersicht<span class="sr-only">(current)</span></a></li>
    	<li><a href="anwesend.php">Anwesend</a></li>
    	<li  class="active"><a href="abwesend.php">Abwesend<span class="sr-only">(current)</span></a></li>
        <!-- <li><a href="whattodo.php">Was muss ich tun?</a></li> -->
        <li><a href="userview.php">Punkte</a></li>
        <li><a href="logging.php">Log</a></li>
      </ul>
 </div>

</nav>

<br><br>


<div id="news" align="center" valign="center" style="position:relative;top:20px;height:10px;" >
</div>

<div id="s1" align="center" valign="center">

     	
<table  class="table table-bordered table-condensed">
<colgroup>
	<col>
	<col width="60">
</colgroup>


<?php
	$users = get_users_in_order($conn);

	foreach ($users as $user) {
		if ($user[9] == 1) {
			$out_of_office_part = '<td align="center"><form name="user_'.$user[0].'" action="abwesend.php" method="post">
			<input type="hidden" name="action" value="out_of_office">
			<input type="hidden" name="user" value="'.$user[0].'">
			<input type="hidden" name="out_of_office" value="'.$user[9].'">
			<button type="submit" class="btn btn-xs btn-success"><b>auf "anwesend" setzen</b></button>
			</form></td>';
			$name_part = '<td align="right"><b>'.$job.' '.$user[6].' '.$user[7].'</b></td>';
			echo '<tr'.$_tr.'>';
			echo $name_part;
			echo $out_of_office_part;
			echo '</tr>';
		}
	}
	

echo '
</table>    
</div> 
';

?>




   
    
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
  </body>
</html>
