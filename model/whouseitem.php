<?php
include_once("../resource/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($_POST['module'] == 'whouseitem') {
		if ($_POST['event'] == 'create') {
			$message = create($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'modify') {
			$message = modify($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'delete') {
			$message = delete($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'query') {
			$message = query($_POST);
			if (is_array($message)) {
				echo json_encode($message);
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'check_item_empty') {
			$message = check_item_empty($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'check_whouse_exist') {
			$message = check_whouse_exist($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'check_item_exist') {
			$message = check_item_exist($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'check_whouse_delete') {
			$message = check_whouse_delete($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'check_item_delete') {
			$message = check_item_delete($_POST);
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
	$itemno = $content['itemno'];
	$whouseitemamt = $content['whouseitemamt'];
	$logistic = $content['logistic'];
	$request = $content['request'];
	$whouseitemmemo = $content['whouseitemmemo'];
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
	elseif (empty($itemno)) {
		return 'Empty item number';
	}
	elseif (strlen($itemno) > 20) {
		return 'Item number exceed length limit';
	}
	elseif (!empty($whouseitemamt) && $whouseitemamt < 0) {
		return 'Wrong amount format';
	}
	elseif ($logistic != 0 && $logistic != 1) {
		return 'Wrong logistic format';
	}
	elseif ($request != 0 && $request != 1) {
		return 'Wrong request format';
	}
	elseif (strlen($whouseitemmemo) > 200) {
		return 'Warehouse memo exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno'");
			if ($sql2 != false && mysql_num_rows($sql2) > 0) {
				return 'Occupied warehouse item';
			}
			else {
				date_default_timezone_set('Asia/Taipei');
				$date = date("Y-m-d H:i:s");
				$sql3 = "INSERT INTO WHOUSEITEM (WHOUSENO, ITEMNO, AMT, LOGISTIC, REQUEST, MEMO, CREATETIME, UPDATETIME) VALUES ('$whouseno', '$itemno', '$whouseitemamt', '$logistic', '$request', '$whouseitemmemo', '$date', '$date')";
				if (mysql_query($sql3)) {
					mysql_query("UPDATE WHOUSE SET WHOUSEAMT=WHOUSEAMT+'$whouseitemamt' WHERE WHOUSENO='$whouseno'");
					mysql_query("UPDATE ITEM SET ITEMAMT=ITEMAMT+'$whouseitemamt' WHERE ITEMNO='$itemno'");
					return 'Success';
				}
				else {
					return 'Database operation error';
				}
			}
		}
	}
}

function modify($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$itemno = $content['itemno'];
	$whouseitemamt = $content['whouseitemamt'];
	$logistic = $content['logistic'];
	$request = $content['request'];
	$whouseitemmemo = $content['whouseitemmemo'];
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
	elseif (empty($itemno)) {
		return 'Empty item number';
	}
	elseif (strlen($itemno) > 20) {
		return 'Item number exceed length limit';
	}
	elseif (empty($whouseitemamt)) {
		return 'Empty warehouse item amount';
	}
	elseif ($whouseitemamt < 0) {
		return 'Wrong amount format';
	}
	elseif ($logistic != 0 && $logistic != 1) {
		return 'Wrong logistic format';
	}
	elseif ($request != 0 && $request != 1) {
		return 'Wrong request format';
	}
	elseif (strlen($whouseitemmemo) > 200) {
		return 'Warehouse memo exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno' AND ACTCODE=1");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse item';
			}
			else {
				date_default_timezone_set('Asia/Taipei');
				$date = date("Y-m-d H:i:s");
				$sql3 = "UPDATE WHOUSEITEM SET AMT='$whouseitemamt', LOGISTIC='$logistic', REQUEST='$request', MEMO='$whouseitemmemo', UPDATETIME='$date' WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno'";
				$sql4 = mysql_query("SELECT AMT FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno'");
				$fetch4 = mysql_fetch_array($sql4);
				if (mysql_query($sql3)) {
					$amount = $fetch4['AMT'];
					mysql_query("UPDATE WHOUSE SET WHOUSEAMT=WHOUSEAMT-'$amount'+'$whouseitemamt' WHERE WHOUSENO='$whouseno'");
					mysql_query("UPDATE ITEM SET ITEMAMT=ITEMAMT-'$amount'+'$whouseitemamt' WHERE ITEMNO='$itemno'");
					return 'Success';
				}
				else {
					return 'Database operation error';
				}
			}
		}
	}
}

function delete($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$itemno = $content['itemno'];
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
	elseif (empty($itemno)) {
		return 'Empty item number';
	}
	elseif (strlen($itemno) > 20) {
		return 'Item number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno' AND ACTCODE=1");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse item';
			}
			else {
				date_default_timezone_set('Asia/Taipei');
				$date = date("Y-m-d H:i:s");
				$sql3 = "UPDATE WHOUSEAMT SET AMT=0, ACTCODE=0, UPDATETIME='$date' WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno'";
				$sql4 = mysql_query("SELECT AMT FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno'");
				$fetch4 = mysql_fetch_array($sql4);
				if (mysql_query($sql3)) {
					$amount = $fetch4['AMT'];
					mysql_query("UPDATE WHOUSE SET WHOUSEAMT=WHOUSEAMT-'$amount' WHERE WHOUSENO='$whouseno'");
					mysql_query("UPDATE ITEM SET ITEMAMT=ITEMAMT-'$amount' WHERE ITEMNO='$itemno'");
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
	$itemno = $content['itemno'];
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
	elseif (empty($itemno)) {
		return 'Empty item number';
	}
	elseif (strlen($itemno) > 20) {
		return 'Item number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno' AND ACTCODE=1");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse item';
			}
			else {
				$fetch2 = mysql_fetch_array($sql2);
				return array('message' => 'Success', 'amt' => $fetch2['amt'], 'logistic' => $fetch2['logistic'], 'request' => $fetch2['request'], 'memo' => $fetch2['memo']);
			}
		}
	}
}

function check_item_empty($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$itemno = $content['itemno'];
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
	elseif (empty($itemno)) {
		return 'Empty item number';
	}
	elseif (strlen($itemno) > 20) {
		return 'Item number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno'");
			if ($sql2 != false && mysql_num_rows($sql2) > 0) {
				return 'Occupied warehouse item';
			}
			else {
				return 'ok';
			}
		}
	}
}

function check_whouse_exist($content) {
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
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ACTCODE=1");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse';
			}
			else {
				return 'ok';
			}
		}
	}
}

function check_item_exist($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$itemno = $content['itemno'];
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
	elseif (empty($itemno)) {
		return 'Empty item number';
	}
	elseif (strlen($itemno) > 20) {
		return 'Item number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno' AND ACTCODE=1");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse item';
			}
			else {
				return 'ok';
			}
		}
	}
}

function check_whouse_delete($content) {
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
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ACTCODE=0");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse';
			}
			else {
				return 'ok';
			}
		}
	}
}

function check_item_delete($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$itemno = $content['itemno'];
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
	elseif (empty($itemno)) {
		return 'Empty item number';
	}
	elseif (strlen($itemno) > 20) {
		return 'Item number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno' AND ACTCODE=0");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse item';
			}
			else {
				return 'ok';
			}
		}
	}
}
