<?php
include_once("../resource/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($_POST['module'] == 'item') {
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
		elseif ($_POST['event'] == 'check_empty') {
			$message = check_empty($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'check_delete') {
			$message = check_delete($_POST);
			if (is_array($message)) {
				echo json_encode($message);
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'recover') {
			$message = recover($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'export_search') {
			$message = export_search($_POST);
			if (is_array($message)) {
				echo json_encode($message);
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'export') {
			$message = export($_POST);
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
	$itemno = $content['itemno'];
	$itemnm = $content['itemnm'];
	$itemdescription = $content['itemdescription'];
	$itemmemo = $content['itemmemo'];
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
	elseif (empty($itemno)) {
		return 'Empty item number';
	}
	elseif (strlen($itemno) > 20) {
		return 'Item number exceed length limit';
	}
	elseif (empty($itemnm)) {
		return 'Empty item name';
	}
	elseif (strlen($itemnm) > 50) {
		return 'Item name exceed length limit';
	}
	elseif (strlen($itemdescription) > 200) {
		return 'Item description exceed length limit';
	}
	elseif (strlen($itemmemo) > 200) {
		return 'Item memo exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch1['AUTHORITY'] != 'A') {
			return 'No authority';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM ITEM WHERE ITEMNO='$itemno'");
			if ($sql2 != false && mysql_num_rows($sql2) > 0) {
				return 'Occupied item';
			}
			else {
				date_default_timezone_set('Asia/Taipei');
				$date = date("Y-m-d H:i:s");
				$sql3 = "INSERT INTO ITEM (ITEMNO, ITEMNM, DESCRIPTION, MEMO, CREATETIME, UPDATETIME) VALUES ('$itemno', '$itemnm', '$itemdescription', '$itemmemo', '$date', '$date')";
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

function modify($content) {
	$account = $content['account'];
	$token = $content['token'];
	$itemno = $content['itemno'];
	$itemnm = $content['itemnm'];
	$itemdescription = $content['itemdescription'];
	$itemmemo = $content['itemmemo'];
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
	elseif (empty($itemno)) {
		return 'Empty item number';
	}
	elseif (strlen($itemno) > 20) {
		return 'Item number exceed length limit';
	}
	elseif (empty($itemnm)) {
		return 'Empty item name';
	}
	elseif (strlen($itemnm) > 50) {
		return 'Item name exceed length limit';
	}
	elseif (strlen($itemdescription) > 200) {
		return 'Item description exceed length limit';
	}
	elseif (strlen($itemmemo) > 200) {
		return 'Item memo exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch1['AUTHORITY'] != 'A') {
			return 'No authority';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM ITEM WHERE ITEMNO='$itemno' AND ACTCODE=1");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound item';
			}
			else {
				date_default_timezone_set('Asia/Taipei');
				$date = date("Y-m-d H:i:s");
				$sql3 = "UPDATE ITEM SET ITEMNM='$itemnm', DESCRIPTION='$itemdescription', MEMO='$itemmemo', UPDATETIME='$date' WHERE ITEMNO='$itemno'";
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

function delete($content) {
	$account = $content['account'];
	$token = $content['token'];
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
		elseif ($fetch1['AUTHORITY'] != 'A') {
			return 'No authority';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM ITEM WHERE ITEMNO='$itemno' AND ACTCODE=1");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound item';
			}
			else {
				date_default_timezone_set('Asia/Taipei');
				$date = date("Y-m-d H:i:s");
				$sql3 = "UPDATE ITEM SET ACTCODE=0, UPDATETIME='$date' WHERE ITEMNO='$itemno'";
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
		return 'Empty item number';
	}
	elseif (strlen($whouseno) > 20) {
		return 'Item number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch1['AUTHORITY'] != 'A' && $fetch1['AUTHORITY'] != 'B') {
			return 'No authority';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM ITEM WHERE ITEMNO='$itemno' AND ACTCODE=1");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound item';
			}
			else {
				$fetch2 = mysql_fetch_array($sql2);
				return array('message' => 'Success', 'itemnm' => $fetch2['itemnm'], 'description' => $fetch2['description'], 'memo' => $fetch2['memo']);
			}
		}
	}
}

function export_search($content) {
	$account = $content['account'];
	$token = $content['token'];
	$itemnostart = $content['itemnostart'];
	$itemnoend = $content['itemnoend'];
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
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch1['AUTHORITY'] != 'A' && $fetch1['AUTHORITY'] != 'B') {
			return 'No authority';
		}
		else {
			$sql2 = "SELECT * FROM ITEM WHERE ACTCODE=1";
			if (!empty($itemnostart)) {
				$sql2 .= "AND ITEMNO>='$itemnostart'";
			}
			if (!empty($itemnoend)) {
				$sql2 .= "AND ITEMNO<='$itemnoend'";
			}
			$sql2 = mysql_query($sql2);
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'No data';
			}
			else {
				$content = '<table><tr><td>物料編號</td><td>物料名稱</td><td>物料存量</td><td>物料概述</td><td>建立時間</td><td>最後修改時間</td><td>備註</td></tr>';
				while ($fetch2 = mysql_fetch_array($sql2)) {
					$content .= '<tr><td>'.$fetch2['ITEMNO'].'</td><td>'.$fetch2['ITEMNM'].'</td><td>'.$fetch2['ITEMAMT'].'</td><td>'.$fetch2['DESCRIPTION'].'</td><td>'.$fetch2['CREATETIME'].'</td><td>'.$fetch2['UPDATETIME'].'</td><td>'.$fetch2['MEMO'].'</td></tr></table>';
				}
				$content .= '</table><button onclick="export_item()">確定輸出</button>';
				return array('message' => 'Success', 'content' => $content);
			}
		}
	}
}

function export($content) {
	$account = $content['account'];
	$token = $content['token'];
	$itemnostart = $content['itemnostart'];
	$itemnoend = $content['itemnoend'];
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
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch1['AUTHORITY'] != 'A' && $fetch1['AUTHORITY'] != 'B') {
			return 'No authority';
		}
		else {
			$sql2 = "SELECT * FROM ITEM WHERE ACTCODE=1";
			if (!empty($itemnostart)) {
				$sql2 .= "AND ITEMNO>='$itemnostart'";
			}
			if (!empty($itemnoend)) {
				$sql2 .= "AND ITEMNO<='$itemnoend'";
			}
			$sql2 = mysql_query($sql2);
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'No data';
			}
			else {
				/*
				$content = '<table><tr><td>物料編號</td><td>物料名稱</td><td>物料存量</td><td>物料概述</td><td>建立時間</td><td>最後修改時間</td><td>備註</td></tr>';
				while ($fetch2 = mysql_fetch_array($sql2)) {
					$content .= '<tr><td>'.$fetch2['ITEMNO'].'</td><td>'.$fetch2['ITEMNM'].'</td><td>'.$fetch2['ITEMAMT'].'</td><td>'.$fetch2['DESCRIPTION'].'</td><td>'.$fetch2['CREATETIME'].'</td><td>'.$fetch2['UPDATETIME'].'</td><td>'.$fetch2['MEMO'].'</td></tr></table>';
				}
				$content .= '</table>';
				*/
				return 'Success';
			}
		}
	}
}

function check_empty($content) {
	$account = $content['account'];
	$token = $content['token'];
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
			$sql2 = mysql_query("SELECT * FROM ITEM WHERE ITEMNO='$itemno'");
			if ($sql2 != false && mysql_num_rows($sql2) > 0) {
				return 'Occupied item';
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
		return 'Empty item number';
	}
	elseif (strlen($whouseno) > 20) {
		return 'Item number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM ITEM WHERE ITEMNO='$itemno' AND ACTCODE=0");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound item';
			}
			else {
				$fetch2 = mysql_fetch_array($sql2);
				return array('message' => 'Success', 'itemnm' => $fetch2['itemnm'], 'description' => $fetch2['description'], 'memo' => $fetch2['memo']);
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
		return 'Empty item number';
	}
	elseif (strlen($whouseno) > 20) {
		return 'Item number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch1['AUTHORITY'] != 'A') {
			return 'No authority';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM ITEM WHERE ITEMNO='$itemno' AND ACTCODE=0");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound item';
			}
			else {
				date_default_timezone_set('Asia/Taipei');
				$date = date("Y-m-d H:i:s");
				$sql3 = "UPDATE WHOUSE SET ACTCODE=1, UPDATETIME='$date' WHERE ITEMNO='$itemno'";
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