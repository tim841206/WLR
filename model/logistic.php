<?php
include_once("../resource/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($_POST['module'] == 'logistic') {
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
			if (is_array($message)) {
				echo json_encode($message);
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'waiting') {
			$message = waiting($_POST);
			if (is_array($message)) {
				echo json_encode($message);
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'accept') {
			$message = accept($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'reject') {
			$message = reject($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'check') {
			$message = check($_POST);
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
		elseif ($_POST['event'] == 'search') {
			$message = search($_POST);
			if (is_array($message)) {
				echo json_encode($message);
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'view') {
			$message = view($_POST);
			if (is_array($message)) {
				echo json_encode($message);
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
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
	$receiver = $content['receiver'];
	$memo = $content['memo'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif (empty($receiver)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($receiver) > 20) {
		return 'Warehouse number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		$fetch2 = mysql_fetch_array($sql2);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch2['AUTHORITY'] != 'A' && $fetch2['AUTHORITY'] != 'B') {
			return 'No authority';
		}
		else {
			$sql3 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$receiver'");
			if ($sql3 == false || mysql_num_rows($sql3) == 0) {
				return 'Unfound warehouse';
			}
			else {
				$warning = '';
				for ($i = 1; $i < 10; $i++) {
					$itemno = 'itemno'.$i;
					$itemamt = 'itemamt'.$i;
					if (!empty($content[$itemno])) {
						$sql4 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno' AND ACTCODE=1");
						$sql5 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$receiver' AND ITEMNO='$itemno' AND ACTCODE=1");
						if ($sql4 == false || mysql_num_rows($sql4) == 0) {
							$warning = 'Unfound warehouse item' . $i;
							break;
						}
						elseif ($sql5 == false || mysql_num_rows($sql5) == 0) {
							$warning = 'Unreceivable warehouse item' . $i;
							break;
						}
						else {
							$fetch4 = mysql_fetch_array($sql4);
							if ($fetch4['LOGISTIC'] == 0) {
								$warning = 'Undeliverable item' . $i;
								break;
							}
							elseif ($fetch4['AMT'] < $content[$itemamt]) {
								$warning = 'Not enough item' . $i;
								break;
							}
						}
					}
				}
				if ($warning == '') {
					date_default_timezone_set('Asia/Taipei');
					$date = date("Y-m-d H:i:s");
					$logisticno = get_logisticno();
					mysql_query("INSERT INTO LOGISTIC (LOGISTICNO, SENDER, RECEIVER, MEMO, CREATETIME, UPDATETIME) VALUES ('$logisticno', '$whouseno', '$receiver', '$memo', '$date', '$date')");
					for ($i = 1; $i < 10; $i++) {
						$itemno = 'itemno'.$i;
						$itemno = $content[$itemno];
						$itemamt = 'itemamt'.$i;
						$itemamt = $content[$itemamt];
						mysql_query("INSERT INTO LOGISTICITEM (LOGISTICNO, ITEMNO, AMT) VALUES ('$logisticno', '$itemno', '$itemamt')");
						mysql_query("UPDATE WHOUSEITEM SET AMT=AMT-'$itemamt' WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno'");
						mysql_query("UPDATE WHOUSE SET WHOUSEAMT=WHOUSEAMT-'$itemamt' WHERE WHOUSENO='$whouseno'");
					}
					update_logisticno();
					return array('message' => 'Success', 'logisticno' => $logisticno);
				}
				else {
					return $warning;
				}
			}
		}
	}
}

function waiting($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
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
		$fetch2 = mysql_fetch_array($sql2);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch2['AUTHORITY'] != 'A' && $fetch2['AUTHORITY'] != 'B') {
			return 'No authority';
		}
		else {
			$content = '<table><tr><td>物流編號</td><td>運送方</td><td>接收方</td><td>狀態</td><td>建立時間</td><td>操作</td></tr>';
			$sql3 = mysql_query("SELECT * FROM LOGISTIC WHERE SENDER='$whouseno' AND STATE='C'");
			$sql4 = mysql_query("SELECT * FROM LOGISTIC WHERE RECEIVER='$whouseno' AND STATE='A'");
			while ($fetch3 = mysql_fetch_array($sql3)) {
				$content .= '<tr><td>'.$fetch3['LOGISTICNO'].'</td><td>'.$fetch3['SENDER'].'</td><td>'.$fetch3['RECEIVER'].'</td><td>已拒絕</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="query(\''.$fetch3['LOGISTICNO'].'\')">查看</button></td></tr></table>';
			}
			while ($fetch4 = mysql_fetch_array($sql4)) {
				$content .= '<tr><td>'.$fetch3['LOGISTICNO'].'</td><td>'.$fetch3['SENDER'].'</td><td>'.$fetch3['RECEIVER'].'</td><td>待確認</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="query(\''.$fetch3['LOGISTICNO'].'\')">查看</button></td></tr></table>';
			}
			$content .= '</table>';
			return array('message' => 'Success', 'content' => $content);
		}
	}
}

function check_itemno($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$receiver = $content['receiver'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif (empty($receiver)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($receiver) > 20) {
		return 'Warehouse number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		$fetch2 = mysql_fetch_array($sql2);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch2['AUTHORITY'] != 'A' && $fetch2['AUTHORITY'] != 'B') {
			return 'No authority';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$receiver'");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse';
			}
			else {
				for ($i = 1; $i < 10; $i++) {
					$itemno = 'itemno'.$i;
					if (!empty($content[$itemno])) {
						$sql3 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno' AND ACTCODE=1");
						$sql4 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$receiver' AND ITEMNO='$itemno' AND ACTCODE=1");
						if ($sql3 == false || mysql_num_rows($sql3) == 0) {
							return 'Unfound warehouse item' . $i;
							break;
						}
						elseif ($sql4 == false || mysql_num_rows($sql4) == 0) {
							return 'Unreceivable warehouse item' . $i;
							break;
						}
						else {
							$fetch3 = mysql_fetch_array($sql3);
							if ($fetch3['LOGISTIC'] == 0) {
								return 'Undeliverable item' . $i;
								break;
							}
						}
					}
				}
				return 'ok';
			}
		}
	}
}

function check_itemamt($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$receiver = $content['receiver'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif (empty($receiver)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($receiver) > 20) {
		return 'Warehouse number exceed length limit';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		$fetch2 = mysql_fetch_array($sql2);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch2['AUTHORITY'] != 'A' && $fetch2['AUTHORITY'] != 'B') {
			return 'No authority';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$receiver'");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse';
			}
			else {
				for ($i = 1; $i < 10; $i++) {
					$itemno = 'itemno'.$i;
					$itemamt = 'itemamt'.$i;
					if (!empty($content[$itemno])) {
						$sql3 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno' AND ACTCODE=1");
						if ($sql3 == false) {
							return 'Unfound warehouse item' . $i;
							break;
						}
						else {
							$fetch3 = mysql_fetch_array($sql3);
							if ($fetch3['LOGISTIC'] == 0) {
								return 'Undeliverable item' . $i;
								break;
							}
							elseif ($fetch3['AMT'] < $itemamt) {
								return 'Not enough item' . $i;
								break;
							}
						}
					}
				}
				return 'ok';
			}
		}
	}
}

function query($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$logisticno = $content['logisticno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
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
		$fetch2 = mysql_fetch_array($sql2);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch2['AUTHORITY'] != 'A' && $fetch2['AUTHORITY'] != 'B') {
			return 'No authority';
		}
		else {
			$content = '<table><tr><td>物流編號</td><td>運送方</td><td>接收方</td><td>狀態</td><td>建立時間</td><td>操作</td></tr>';
			$sql3 = mysql_query("SELECT * FROM LOGISTIC WHERE LOGISTICNO='$logisticno'");
			$fetch3 = mysql_fetch_array($sql3);
			if ($fetch3['STATE'] == 'C' && $fetch3['SENDER'] == $whouseno) {
				$content .= '<tr><td>'.$fetch3['LOGISTICNO'].'</td><td>'.$fetch3['SENDER'].'</td><td>'.$fetch3['RECEIVER'].'</td><td>已拒絕</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="check(\''.$fetch3['LOGISTICNO'].'\')">確認</button></td></tr>';
			}
			elseif ($fetch3['STATE'] == 'A' && $fetch3['RECEIVER'] == $whouseno) {
				$content .= '<tr><td>'.$fetch3['LOGISTICNO'].'</td><td>'.$fetch3['SENDER'].'</td><td>'.$fetch3['RECEIVER'].'</td><td>待確認</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="accept(\''.$fetch3['LOGISTICNO'].'\')">接收</button><button onclick="reject(\''.$fetch3['LOGISTICNO'].'\')">拒絕</button></td></tr>';
			}
			$content .= '</table><h3>物流內容</h3><table><tr><td>物料編號</td><td>物料名稱</td><td>運送數量</td></tr>';
			$sql4 = mysql_query("SELECT * FROM LOGISTICITEM WHERE LOGISTICNO='$logisticno'");
			while ($fetch4 = mysql_fetch_array($sql4)) {
				$content .= '<tr><td>'.$fetch4['ITEMNO'].'</td><td>'.query_name($fetch4['ITEMNO']).'</td><td>'.$fetch4['AMT'].'</td></tr>';
			}
			$content .= '</table>';
			return array('message' => 'Success', 'content' => $content);
		}
	}
}

function accept($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$logisticno = $content['logisticno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
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
		$fetch2 = mysql_fetch_array($sql2);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch2['AUTHORITY'] != 'A' && $fetch2['AUTHORITY'] != 'B') {
			return 'No authority';
		}
		else {
			$sql3 = mysql_query("SELECT * FROM LOGISTIC WHERE LOGISTICNO='$logisticno'");
			$fetch3 = mysql_fetch_array($sql3);
			$addwhouse = $fetch3['RECEIVER'];
			if ($fetch3['STATE'] != 'A') {
				return 'Wrong state';
			}
			elseif ($whouseno != $addwhouse) {
				return 'Unmatch warehouse authority';
			}
			else {
				$sql4 = mysql_query("SELECT * FROM LOGISTICITEM WHERE LOGISTICNO='$logisticno'");
				while ($fetch4 = mysql_fetch_array($sql4)) {
					$itemno = $fetch4['ITEMNO'];
					$amt = $fetch4['AMT'];
					mysql_query("UPDATE WHOUSEITEM SET AMT=AMT+'$amt' WHERE WHOUSENO='$addwhouse' AND ITEMNO='$itemno'");
					mysql_query("UPDATE WHOUSE SET WHOUSEAMT=WHOUSEAMT+'$amt' WHERE WHOUSENO='$addwhouse'");
				}
				mysql_query("UPDATE LOGISTIC SET STATE='B' WHERE LOGISTICNO='$logisticno'");
				return 'Success';
			}
		}
	}
}

function reject($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$logisticno = $content['logisticno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
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
		$fetch2 = mysql_fetch_array($sql2);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch2['AUTHORITY'] != 'A' && $fetch2['AUTHORITY'] != 'B') {
			return 'No authority';
		}
		else {
			$sql3 = mysql_query("SELECT * FROM LOGISTIC WHERE LOGISTICNO='$logisticno'");
			$fetch3 = mysql_fetch_array($sql3);
			$addwhouse = $fetch3['RECEIVER'];
			if ($fetch3['STATE'] != 'A') {
				return 'Wrong state';
			}
			elseif ($whouseno != $addwhouse) {
				return 'Unmatch warehouse authority';
			}
			else {
				mysql_query("UPDATE LOGISTIC SET STATE='C' WHERE LOGISTICNO='$logisticno'");
				return 'Success';
			}
		}
	}
}

function check($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$logisticno = $content['logisticno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
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
		$fetch2 = mysql_fetch_array($sql2);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif ($fetch2['AUTHORITY'] != 'A' && $fetch2['AUTHORITY'] != 'B') {
			return 'No authority';
		}
		else {
			$sql3 = mysql_query("SELECT * FROM LOGISTIC WHERE LOGISTICNO='$logisticno'");
			$fetch3 = mysql_fetch_array($sql3);
			$addwhouse = $fetch3['SENDER'];
			if ($fetch3['STATE'] != 'C') {
				return 'Wrong state';
			}
			elseif ($whouseno != $addwhouse) {
				return 'Unmatch warehouse authority';
			}
			else {
				$sql4 = mysql_query("SELECT * FROM LOGISTICITEM WHERE LOGISTICNO='$logisticno'");
				while ($fetch4 = mysql_fetch_array($sql4)) {
					$itemno = $fetch4['ITEMNO'];
					$amt = $fetch4['AMT'];
					mysql_query("UPDATE WHOUSEITEM SET AMT=AMT+'$amt' WHERE WHOUSENO='$addwhouse' AND ITEMNO='$itemno'");
					mysql_query("UPDATE WHOUSE SET WHOUSEAMT=WHOUSEAMT+'$amt' WHERE WHOUSENO='$addwhouse'");
				}
				mysql_query("UPDATE LOGISTIC SET STATE='D' WHERE LOGISTICNO='$logisticno'");
				return 'Success';
			}
		}
	}
}

function search($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$logisticno = $content['logisticno'];
	$sender = $content['sender'];
	$receiver = $content['receiver'];
	$whouse = $content['whouse'];
	$item = $content['item'];
	$createtimestart = $content['createtimestart'];
	$createtimeend = $content['createtimeend'];
	$closetimestart = $content['closetimestart'];
	$closetimeend = $content['closetimeend'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
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
		$fetch2 = mysql_fetch_array($sql2);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif (!in_array($fetch2['AUTHORITY'], array('A', 'B', 'C'))) {
			return 'No authority';
		}
		else {
			$sql3 = "SELECT * FROM LOGISTIC WHERE 1";
			if (!empty($logisticno)) {
				$sql3 .= " AND LOGISTICNO='$logisticno'";
			}
			if (!empty($sender)) {
				$sql3 .= " AND SENDER='$sender'";
			}
			if (!empty($receiver)) {
				$sql3 .= " AND RECEIVER='$receiver'";
			}
			if (!empty($whouse)) {
				$sql3 .= " AND (SENDER='$whouse' || RECEIVER='$whouse')";
			}
			if (!empty($item)) {
				$sql3 .= " AND LOGISTICNO IN (SELECT DISTINCT LOGISTICNO FROM LOGISTICITEM WHERE ITEMNO='$item')";
			}
			if (!empty($createtimestart)) {
				$sql3 .= " AND CREATETIME>'$createtimestart'";
			}
			if (!empty($createtimeend)) {
				$sql3 .= " AND CREATETIME<'$createtimeend'";
			}
			if (!empty($closetimestart)) {
				$sql3 .= " AND STATE='E' AND UPDATETIME>'$closetimestart'";
			}
			if (!empty($closetimeend)) {
				$sql3 .= " AND STATE='E' AND UPDATETIME<'$closetimeend'";
			}
			$sql3 = mysql_query($sql3);
			if ($sql3 == false || mysql_num_rows($sql3) == 0) {
				return 'No data';
			}
			else {
				$content = '<table><tr><td>物流編號</td><td>運送方</td><td>接收方</td><td>狀態</td><td>建立時間</td><td>操作</td></tr>';
				while ($fetch3 = mysql_fetch_array($sql3)) {
					$content .= '<tr><td>'.$fetch3['LOGISTICNO'].'</td><td>'.$fetch3['SENDER'].'</td><td>'.$fetch3['RECEIVER'].'</td><td>'.transform_state($fetch3['STATE']).'</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="view(\''.$fetch3['LOGISTICNO'].'\')">查看</button></td></tr></table>';
				}
				$content .= '</table>';
				return array('message' => 'Success', 'content' => $content);
			}
		}
	}
}

function view($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$logisticno = $content['logisticno'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
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
		$fetch2 = mysql_fetch_array($sql2);
		if ($fetch1['TOKEN'] != $token) {
			return 'Wrong token';
		}
		elseif (!in_array($fetch2['AUTHORITY'], array('A', 'B', 'C'))) {
			return 'No authority';
		}
		else {
			$content = '<table><tr><td>物流編號</td><td>運送方</td><td>接收方</td><td>狀態</td><td>建立時間</td></tr>';
			$sql3 = mysql_query("SELECT * FROM LOGISTIC WHERE LOGISTICNO='$logisticno'");
			$fetch3 = mysql_fetch_array($sql3);
			$content .= '<tr><td>'.$fetch3['LOGISTICNO'].'</td><td>'.$fetch3['SENDER'].'</td><td>'.$fetch3['RECEIVER'].'</td><td>'.transform_state($fetch3['STATE']).'</td><td>'.$fetch3['CREATETIME'].'</td></tr>';
			$content .= '</table><h3>物流內容</h3><table><tr><td>物料編號</td><td>物料名稱</td><td>運送數量</td></tr>';
			$sql4 = mysql_query("SELECT * FROM LOGISTICITEM WHERE LOGISTICNO='$logisticno'");
			while ($fetch4 = mysql_fetch_array($sql4)) {
				$content .= '<tr><td>'.$fetch4['ITEMNO'].'</td><td>'.query_name($fetch4['ITEMNO']).'</td><td>'.$fetch4['AMT'].'</td></tr>';
			}
			$content .= '</table>';
			return array('message' => 'Success', 'content' => $content);
		}
	}
}

function get_logisticno() {
	$sql = mysql_query("SELECT NEXT_LOGISTICNO FROM SYSTEM");
	$fetch = mysql_fetch_array($sql);
	return $fetch['NEXT_LOGISTICNO'];
}

function update_logisticno() {
	$sql = mysql_query("SELECT NEXT_LOGISTICNO FROM SYSTEM");
	$fetch = mysql_fetch_array($sql);
	$logisticno = $fetch['NEXT_LOGISTICNO'];
	$logisticno = substr($logisticno, 1) + 1;
	$logisticno = 'L' . $logisticno;
	mysql_query("UPDATE SYSTEM SET NEXT_LOGISTICNO='$logisticno'");
}

function query_name($itemno) {
	$sql = mysql_query("SELECT ITEMNM FROM ITEM WHERE ITEMNO='$itemno'");
	$fetch = mysql_fetch_array($sql);
	return $fetch['ITEMNM'];
}

function transform_state($state) {
	if ($state == 'A') return '待處理';
	elseif ($state == 'B') return '已接受';
	elseif ($state == 'C') return '已拒絕';
	elseif ($state == 'D') return '已確認';
	elseif ($state == 'E') return '已結束';
}