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
	$sql = "SELECT * FROM user ORDER BY first_name";
	if ( $check_out_of_office ) {
		$sql = "SELECT * FROM user WHERE out_of_office=0 ORDER BY first_name";
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
	return $user_id;
}
function get_next_user($conn, $work_id) {
	$current = get_curent_user($conn, $work_id); 
	$users = get_users($conn, true);
	$value=99999999;
	$user_id = 0;
	foreach ($users as $user) {
		if ( $user[0] != $current ) {
			if ( $user[$work_id] < $value ) {
				$value = $user[$work_id];
				$user_id = $user[0]; 
			}
		}

	}
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

	$jump_count = $user_info['overjumps_sum'];
	$jump_count++;
	$sql = "UPDATE user SET overjumps_sum"."=".$jump_count." WHERE id=".$user_id;
	$result = $conn->query($sql);	

	$desc = $jump_type.'_overjump';
	$timedate = date("Y-m-d H:i:s");
	$sql = "INSERT INTO work_log (Description, Date, User_ID) VALUES ('".$desc."','".$timedate."','".$user_id."')";
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
	$username = strtolower($user_info['username']);
	shell_exec('./reminder.py '.$remind_type.' '.$username);

	$desc = $remind_type.'_remind';
	$timedate = date("Y-m-d H:i:s");
	$sql = "INSERT INTO work_log (Description, Date, User_ID) VALUES ('".$desc."','".$timedate."','".$user_id."')";
	$result = $conn->query($sql);

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

	$desc = $done_type.'_done';
	$timedate = date("Y-m-d H:i:s");
	$sql = "INSERT INTO work_log (Description, Date, User_ID) VALUES ('".$desc."','".$timedate."','".$user_id."')";
	$result = $conn->query($sql);
}


if (isset($_POST['action'])) {

	switch($_POST['action']) {
		case ("muell_jump"):
			overjump($conn, 'muell');
			break;
		case ("pfand_jump"):
			overjump($conn, 'pfand');			
			break;
		case ("geschirr_ein_jump"):
			overjump($conn, 'ein');			
			break;
		case ("geschirr_aus_jump"):
			overjump($conn, 'aus');			
			break;
		case ("entkalken_jump"):
			overjump($conn, 'kaffee');			
			break;

		case ("muell_remind"):
			remind($conn, 'muell');
			break;
		case ("pfand_remind"):
			remind($conn, 'pfand');			
			break;
		case ("geschirr_ein_remind"):
			remind($conn, 'ein');			
			break;
		case ("geschirr_aus_remind"):
			remind($conn, 'aus');			
			break;
		case ("entkalken_remind"):
			remind($conn, 'kaffee');			
			break;

		case ("muell_done"):
			done($conn, 'muell');
			break;
		case ("pfand_done"):
			done($conn, 'pfand');			
			break;
		case ("geschirr_ein_done"):
			done($conn, 'ein');			
			break;
		case ("geschirr_aus_done"):
			done($conn, 'aus');			
			break;
		case ("entkalken_done"):
			done($conn, 'kaffee');			
			break;


	}
	header("Refresh:2");
	echo 'Aktion wurde ausgeführt.<br><br>';
	print_r($_POST);
	exit();
}




?>





<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="60">
    <title>ProtonetOfficeClean</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">

	<style type="text/css">
		div { border:0px solid #888; }
		#s1 { width:1023px; top:287px; position:absolute;}
		#news { width:1024px; height:150; }

	</style>
	<meta charset="utf-8">

<script type="application/javascript" src="js/fastklick.js"></script> 
<script type="application/javascript">
	window.addEventListener('load', function () {
		FastClick.attach(document.body);
	}, false);
</script> 
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

    <li class="active">
		<a href="index.php">Overview<span class="sr-only">(current)</span></a>
	</li>
        <li><a href="anwesenheit.php">Presence</a></li>
        <li><a href="userview.php">Points</a></li>
        <li><a href="logging.php">Log</a></li>
        <!--<li><a href="siegertreppe.php">Hall of Jumper</a></li>-->
   		
      </ul>
 </div>

</nav>

<br><br>

<div id="news" align="center" valign="center" style="position:relative;top:20px;height:554px;" >
	<?php include ("news.php"); ?>

</div>

<?php

$muell_id = get_curent_user($conn, 1);
$pfand_id = get_curent_user($conn, 2);
$ein_id = get_curent_user($conn, 3);
$aus_id = get_curent_user($conn, 4);
$entkalken_id = get_curent_user($conn, 5);

$muell_user_info = get_user_info($conn, $muell_id);
$pfand_user_info = get_user_info($conn, $pfand_id);
$aus_user_info = get_user_info($conn, $aus_id);
$ein_user_info = get_user_info($conn, $ein_id);
$entkalken_user_info = get_user_info($conn, $entkalken_id);

$next_muell_id = get_next_user($conn, 1);
$next_pfand_id = get_next_user($conn, 2);
$next_ein_id = get_next_user($conn, 3);
$next_aus_id = get_next_user($conn, 4);
$next_entkalken_id = get_next_user($conn, 5);

$next_muell_user_info = get_user_info($conn, $next_muell_id);
$next_pfand_user_info = get_user_info($conn, $next_pfand_id);
$next_aus_user_info = get_user_info($conn, $next_aus_id);
$next_ein_user_info = get_user_info($conn, $next_ein_id);
$next_entkalken_user_info = get_user_info($conn, $next_entkalken_id);

$user_muell = $muell_user_info['first_name'].' '.$muell_user_info['last_name'];
$user_pfand = $pfand_user_info['first_name'].' '.$pfand_user_info['last_name'];
$user_aus = $aus_user_info['first_name'].' '.$aus_user_info['last_name'];
$user_ein = $ein_user_info['first_name'].' '.$ein_user_info['last_name'];
$user_entkalken = $entkalken_user_info['first_name'].' '.$entkalken_user_info['last_name'];

echo '
<div id="s1" align="center" valign="center">
<table  class="table table-bordered table-condensed">
<colgroup>
        <col width="204">
        <col width="204">
        <col width="204">
        <col width="204">
        <col width="204">
</colgroup>


<tr>
	<td style="font-size:200%;color:#ffffff;" align="center" bgcolor="#000000"><b>Trash</b></td>
	<td style="font-size:200%;color:#ffffff;" align="center" bgcolor="#000000"><b>Pledge</b></td>
	<td style="font-size:200%;color:#ffffff;" align="center" bgcolor="#000000"><b>Put Out</b></td>
	<td style="font-size:200%;color:#ffffff;" align="center" bgcolor="#000000"><b>Put In</b></td>
	<td style="font-size:200%;color:#ffffff;" align="center" bgcolor="#000000"><b>Paper Trash</b></td>
</tr>

<tr>
	<td>'.avatar($conn, $muell_id).'</td>
	<td>'.avatar($conn, $pfand_id).'</td>
	<td>'.avatar($conn, $aus_id).'</td>
	<td>'.avatar($conn, $ein_id).'</td>
	<td>'.avatar($conn, $entkalken_id).'</td>
</tr>
<tr>
	<td style="color:#ffffff;" align="center" bgcolor="#000000"><b>'.$user_muell.'</b></td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000"><b>'.$user_pfand.'</b></td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000"><b>'.$user_aus.'</b></td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000"><b>'.$user_ein.'</b></td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000"><b>'.$user_entkalken.'</b></td>
</tr>
<tr>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="value" value="muell">
			<input type="hidden" name="action" value="muell_remind">
			<button type="submit" class="btn btn-l btn-danger" style="width:200px"><b>Remind</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="value" value="pfand">
			<input type="hidden" name="action" value="pfand_remind">
			<button type="submit" class="btn btn-l btn-danger" style="width:200px"><b>Remind</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="value" value="geschirr_aus">
			<input type="hidden" name="action" value="geschirr_aus_remind">
			<button type="submit" class="btn btn-l btn-danger" style="width:200px"><b>Remind</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="value" value="geschirr_ein">
			<input type="hidden" name="action" value="geschirr_ein_remind">
			<button type="submit" class="btn btn-l btn-danger" style="width:200px"><b>Remind</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="value" value="entkalken">
			<input type="hidden" name="action" value="entkalken_remind">
			<button type="submit" class="btn btn-l btn-danger" style="width:200px"><b>Remind</b></button>
		</form>
	</td>
</tr>
<tr>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="action" value="muell_done">
			<button type="submit" class="btn btn-l btn-warning" style="width:200px"><b>Done</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="action" value="pfand_done">
			<button type="submit" class="btn btn-l btn-warning" style="width:200px"><b>Done</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="action" value="geschirr_aus_done">
			<button type="submit" class="btn btn-l btn-warning" style="width:200px"><b>Done</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="action" value="geschirr_ein_done">
			<button type="submit" class="btn btn-l btn-warning" style="width:200px"><b>Done</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="action" value="entkalken_done">
			<button type="submit" class="btn btn-l btn-warning" style="width:200px"><b>Done</b></button>
		</form>
	</td>
</tr>

<tr>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="action" value="muell_jump">
			<button type="submit" class="btn btn-l btn-info" style="width:200px"><b>Jump</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="action" value="pfand_jump">
			<button type="submit" class="btn btn-l btn-info" style="width:200px"><b>Jump</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="action" value="geschirr_aus_jump">
			<button type="submit" class="btn btn-l btn-info" style="width:200px"><b>Jump</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="action" value="geschirr_ein_jump">
			<button type="submit" class="btn btn-l btn-info" style="width:200px"><b>Jump</b></button>
		</form>
	</td>
	<td style="color:#ffffff;" align="center" bgcolor="#000000">
		<form action="index.php" method="post">
			<input type="hidden" name="action" value="entkalken_jump">
			<button type="submit" class="btn btn-l btn-info" style="width:200px"><b>Jump</b></button>
		</form>
	</td>
</tr>

</table>    
</div> 
';


$next_muell_avatar = '<img src="avatar/'.$next_muell_user_info['email'].'.jpg" width="50px">';
echo '<div style="position:absolute; top:475px; left:141px;">'.$next_muell_avatar.'</div>';

$next_pfand_avatar = '<img src="avatar/'.$next_pfand_user_info['email'].'.jpg" width="50px">';
echo '<div style="position:absolute; top:475px; left:348px;">'.$next_pfand_avatar.'</div>';

$next_aus_avatar = '<img src="avatar/'.$next_aus_user_info['email'].'.jpg" width="50px">';
echo '<div style="position:absolute; top:475px; left:556px;">'.$next_aus_avatar.'</div>';

$next_ein_avatar = '<img src="avatar/'.$next_ein_user_info['email'].'.jpg" width="50px">';
echo '<div style="position:absolute; top:475px; left:764px;">'.$next_ein_avatar.'</div>';

$next_kaffee_avatar = '<img src="avatar/'.$next_entkalken_user_info['email'].'.jpg" width="50px">';
echo '<div style="position:absolute; top:475px; left:972px;">'.$next_kaffee_avatar.'</div>';


?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
  </body>
</html>
