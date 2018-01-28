<?php
include_once("../resource/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($_POST['module'] == 'whouse') {
		if ($_POST['event'] == 'check_itemno') {
			$message = check_itemno($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'check_itemamt') {
			$message = check_itemamt($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'create') {
			$message = create($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'accept') {
			$message = check_empty($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'reject') {
			$message = check_exist($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'check') {
			$message = recover($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'query') {
			$message = recover($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		else {
			echo json_encode(array('message' => 'Invalid event called'));
    		return;
		}
	}
	else {
		echo json_encode(array('message' => 'Invalid module called'));
    	return;
	}
}

else {
	echo json_encode(array('message' => 'Invalid request method'));
    return;
}

function create($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$whousenm = $content['whousenm'];
	$whousedescription = $content['whousedescription'];
	$whousememo = $content['whousememo'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif (empty($whouseno)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($whouseno) > 20) {
		return 'Warehouse number exceed length limit';
	}
	elseif (empty($whousenm)) {
		return 'Empty warehouse name';
	}
	elseif (strlen($whousenm) > 50) {
		return 'Warehouse name exceed length limit';
	}
	elseif (strlen($whousedescription) > 200) {
		return 'Warehouse description exceed length limit';
	}
	elseif (strlen($whousememo) > 200) {
		return 'Warehouse memo exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			date_default_timezone_set('Asia/Taipei');
			$date = date("Y-m-d H:i:s");
			$sql2 = "INSERT INTO WHOUSE (WHOUSENO, WHOUSENM, DESCRIPTION, MEMO, CREATETIME, UPDATETIME) VALUES ($whouseno, $whousenm, $whousedescription, $whousememo, $date, $date)";
			if (mysql_query($sql2)) {
				return 'Success';
			}
			else {
				return 'Database operation error';
			}
		}
	}
}

function modify($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$whousenm = $content['whousenm'];
	$whousedescription = $content['whousedescription'];
	$whousememo = $content['whousememo'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif (empty($whouseno)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($whouseno) > 20) {
		return 'Warehouse number exceed length limit';
	}
	elseif (empty($whousenm)) {
		return 'Empty warehouse name';
	}
	elseif (strlen($whousenm) > 50) {
		return 'Warehouse name exceed length limit';
	}
	elseif (strlen($whousedescription) > 200) {
		return 'Warehouse description exceed length limit';
	}
	elseif (strlen($whousememo) > 200) {
		return 'Warehouse memo exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			date_default_timezone_set('Asia/Taipei');
			$date = date("Y-m-d H:i:s");
			$sql2 = "UPDATE WHOUSE SET WHOUSENM=$whousenm, DESCRIPTION=$whousedescription, MEMO=$whousememo, UPDATETIME=$date WHERE WHOUSENO=$whouseno";
			if (mysql_query($sql2)) {
				return 'Success';
			}
			else {
				return 'Database operation error';
			}
		}
	}
}

function delete($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif (empty($whouseno)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($whouseno) > 20) {
		return 'Warehouse number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSE WHERE WHOUSENO=$whouseno AND ACTCODE=1");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse';
			}
			else {
				date_default_timezone_set('Asia/Taipei');
				$date = date("Y-m-d H:i:s");
				$sql3 = "UPDATE WHOUSE SET ACTCODE=0, UPDATETIME=$date WHERE WHOUSENO=$whouseno";
				if (mysql_query($sql3)) {
					return 'Success';
				}
				else {
					return 'Database operation error';
				}
			}
		}
	}
}

function query($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif (empty($whouseno)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($whouseno) > 20) {
		return 'Warehouse number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSE WHERE WHOUSENO=$whouseno AND ACTCODE=1");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse';
			}
			else {
				$fetch2 = mysql_fetch_array($sql2);
				return array('message' => 'Success', 'whousenm' => $fetch2['whousenm'], 'description' => $fetch2['description'], 'memo' => $fetch2['memo']);
			}
		}
	}
}

function check_empty($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif (empty($whouseno)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($whouseno) > 20) {
		return 'Warehouse number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSE WHERE WHOUSENO=$whouseno");
			if ($sql2 != false && mysql_num_rows($sql2) > 0) {
				return 'Occupied warehouse';
			}
			else {
				return 'ok';
			}
		}
	}
}

function check_exist($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif (empty($whouseno)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($whouseno) > 20) {
		return 'Warehouse number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSE WHERE WHOUSENO=$whouseno AND ACTCODE=1");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse';
			}
			else {
				return 'ok';
			}
		}
	}
}

function check_delete($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif (empty($whouseno)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($whouseno) > 20) {
		return 'Warehouse number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSE WHERE WHOUSENO=$whouseno AND ACTCODE=0");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse';
			}
			else {
				$fetch2 = mysql_fetch_array($sql2);
				return array('message' => 'Success', 'whousenm' => $fetch2['whousenm'], 'description' => $fetch2['description'], 'memo' => $fetch2['memo']);
			}
		}
	}
}

function recover($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif (empty($whouseno)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($whouseno) > 20) {
		return 'Warehouse number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSE WHERE WHOUSENO=$whouseno AND ACTCODE=0");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse';
			}
			else {
				date_default_timezone_set('Asia/Taipei');
				$date = date("Y-m-d H:i:s");
				$sql3 = "UPDATE WHOUSE SET ACTCODE=1, UPDATETIME=$date WHERE WHOUSENO=$whouseno";
				if (mysql_query($sql3)) {
					return 'Success';
				}
				else {
					return 'Database operation error';
				}
			}
		}
	}
}