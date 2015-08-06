<?php
$time = date(' H:i');

$office_id = 2;

$servername = "localhost";
$username = "root";
$password = "bbbbbb";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
$conn->query("use Office");


function get_offices($conn) { 
	$sql = "SELECT * FROM office";
	$result = $conn->query($sql);
	$return_array = array();
	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
			$data = array();
			array_push($data, $row['id']);
			array_push($data, $row['office']);
			array_push($return_array, $data);	
	    }
	} 
	return $return_array;	
}

function get_users($conn) { 
	$sql = "SELECT * FROM user order by first_name";
	$result = $conn->query($sql);
	$return_array = array();
	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
			$data = array();
			array_push($data, $row['id']);
			array_push($data, $row['first_name']);
			array_push($data, $row['last_name']);
			array_push($data, $row['email']);
			array_push($data, $row['office_id']);
			array_push($data, $row['out_of_office']);
			array_push($return_array, $data);	
	    }
	} 
	return $return_array;	
}

function get_log($conn) { 
	$sql = "SELECT * FROM work_log WHERE Valid=1 order by ID desc";
	$result = $conn->query($sql);
	$return_array = array();
	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
			$data = array();
			array_push($data, $row['ID']);
			array_push($data, $row['Description']);
			array_push($data, $row['Date']);
			array_push($data, $row['User_ID']);
			array_push($return_array, $data);	
	    }
	} 
	return $return_array;	
}

function user_office_work($conn, $user_id) { 
	$sql = "SELECT * FROM office_work where user_id = ".$user_id;
	$result = $conn->query($sql);
	$return_array = array();
	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
			$data = array();
			array_push($data, $row['description']);
			array_push($return_array, $data);	
	    }
	} 
	return $return_array;	
}

$offices = get_offices($conn);
$users = get_users($conn);

function work_part($value, $kind_of_work, $id, $office_id) {
	$part = '<td align="center"></td>';
	if ( $value == 1 ) {
		
$template = '<form action="index.php" method="post">
<input type="hidden" name="value" value="'.$id.'">
<input type="hidden" name="action" value="'.$kind_of_work.'">
<button type="submit" class="btn btn-xs btn-warning"><b>&emsp;&emsp;&emsp;X&emsp;&emsp;&emsp;</b></button>
</form>';		
		$part = '<td align="center"><b>'.$template.'</b></td>';
	}
	return $part;
}

function last_user_id($conn) {
	$sql = "SELECT count(id) FROM user";
	$result = $conn->query($sql);
	$return = $result->fetch_assoc();
	return intval($return['count(id)']);
}

function is_user_out_of_office($conn, $id) { 
	$sql = "SELECT * FROM user where id = ".$id;
	$result = $conn->query($sql);
	$return_array = array();
	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
			return $row['out_of_office'];
	    }
	} 
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
		#s1 { width:768px; }
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

    	<li><a href="index.php">Overview<span class="sr-only">(current)</span></a></li>
                <li><a href="anwesenheit.php">Presence</a></li>
    	<!--<li><a href="whattodo.php">Was muss ich tun?</a></li>-->
    	<li><a href="userview.php">Points</a></li>
    	<li  class="active"><a href="logging.php">Log<span class="sr-only">(current)</span></a></li>
        <!--<li><a href="siegertreppe.php">Hall of Jumper</a></li>-->
      </ul>
 </div>

</nav>

<br><br>

<div id="news" align="center" valign="center" style="position:relative;top:20px;height:10px;" >
</div>
<div id="s1" align="center" valign="center">

     	
<table class="table table-bordered table-condensed">
<colgroup>
	<col>
	<col width="60">
</colgroup>


<?php

	
echo '
</table>    
</div> 
';


	foreach (get_log($conn) as $log_entry) {

		$sql = "SELECT first_name, last_name FROM user WHERE id = ".$log_entry[3];
		$result = $conn->query($sql);
		$result = $result->fetch_assoc();
		$name = $result['first_name'].' '.$result['last_name'];

		$word = 'have done';
		$color = 'black';

		if ( $log_entry[1] == 'muell_done' ) {
			$what = 'Trash';
			$color = 'green';
		}
		if ( $log_entry[1] == 'aus_done' ) {
			$what = 'Put Out';
			$color = 'green';
		}
		if ( $log_entry[1] == 'ein_done' ) {
			$what = 'Put In';
			$color = 'green';
		}
		if ( $log_entry[1] == 'pfand_done' ) {
			$what = 'Pledge';
			$color = 'green';
		}
		if ( $log_entry[1] == 'kaffee_done' ) {
			$what = 'Paper Trash';
			$color = 'green';
		}

		if ( $log_entry[1] == 'anw' ) {
			$what = 'present';
			$word = 'is now';
			$color = 'grey';
		}
		if ( $log_entry[1] == 'abw' ) {
			$what = 'not present';
			$word = 'is now';
			$color = 'grey';
		}

		if ( $log_entry[1] == 'muell_remind' ) {
			$what = 'Trash';
			$word = 'is reminded for';
			$color = 'red';
		}

		if ( $log_entry[1] == 'pfand_remind' ) {
			$what = 'Pledge';
			$word = 'is reminded for';
			$color = 'red';
		}

		if ( $log_entry[1] == 'aus_remind' ) {
			$what = 'Put Out';
			$word = 'is reminded for';
			$color = 'red';
		}

		if ( $log_entry[1] == 'ein_remind' ) {
			$what = 'Put In';
			$word = 'is reminded for';
			$color = 'red';
		}

		if ( $log_entry[1] == 'kaffee_remind' ) {
			$what = 'Paper Trash';
			$word = 'is reminded for';
			$color = 'red';
		}



		if ( $log_entry[1] == 'muell_overjump' ) {
			$what = 'Trash';
			$word = 'have overjumped';
			$color = 'blue';
		}

		if ( $log_entry[1] == 'pfand_overjump' ) {
			$what = 'Pledge';
			$word = 'have overjumped';
			$color = 'blue';
		}

		if ( $log_entry[1] == 'aus_overjump' ) {
			$what = 'Put Out';
			$word = 'have overjumped';
			$color = 'blue';
		}

		if ( $log_entry[1] == 'ein_overjump' ) {
			$what = 'Put In';
			$word = 'have overjumped';
			$color = 'blue';
		}

		if ( $log_entry[1] == 'kaffee_overjump' ) {
			$what = 'Paper Trash';
			$word = 'have overjumped';
			$color = 'blue';
		}



		$message = '<span style="color:'.$color.';"><b>['.$log_entry[2].'] '.$name.'</b> '.$word.' <b>'.$what.'</b>.</span>';
		echo $message.'<br>';
	}

?>




   
    
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
  </body>
</html>
