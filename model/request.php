<?php
include_once("../resource/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($_POST['module'] == 'request') {
		if ($_POST['event'] == 'check_itemno') {
			$message = check_itemno($_POST);
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
		elseif ($_POST['event'] == 'export') {
			$message = export($_POST);
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
	$target = $content['target'];
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
	elseif (empty($target)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($target) > 20) {
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
			$sql3 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$target'");
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
						$sql5 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$target' AND ITEMNO='$itemno' AND ACTCODE=1");
						if ($sql4 == false) {
							$warning = 'Unreceivable warehouse item ' . query_name($itemno);
							break;
						}
						elseif ($sql5 == false) {
							$warning = 'Unfound warehouse item ' . query_name($itemno);
							break;
						}
						else {
							$fetch5 = mysql_fetch_array($sql5);
							if ($fetch5['REQUEST'] == 0) {
								$warning = 'Unrequestable item ' . query_name($itemno);
								break;
							}
						}
					}
				}
				if ($warning == '') {
					date_default_timezone_set('Asia/Taipei');
					$date = date("Y-m-d H:i:s");
					$requestno = get_requestno();
					mysql_query("INSERT INTO REQUEST (REQUESTNO, REQUESTER, TARGET, MEMO, CREATETIME, UPDATETIME) VALUES ('$requestno', '$whouseno', '$target', '$memo', '$date', '$date')");
					for ($i = 1; $i < 10; $i++) {
						$itemno = 'itemno'.$i;
						$itemno = $content[$itemno];
						$itemamt = 'itemamt'.$i;
						$itemamt = $content[$itemamt];
						mysql_query("INSERT INTO REQUESTITEM (REQUESTNO, ITEMNO, AMT) VALUES ('$requestno', '$itemno', '$itemamt')");
					}
					update_requestno();
					return array('message' => 'Success', 'requestno' => $requestno);
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
			$content = '<table><tr><td>請求編號</td><td>請求方</td><td>目標方</td><td>狀態</td><td>建立時間</td><td>操作</td></tr>';
			$sql3 = mysql_query("SELECT * FROM REQUEST WHERE REQUESTER='$whouseno' AND STATE='B'");
			$sql4 = mysql_query("SELECT * FROM REQUEST WHERE TARGET='$whouseno' AND STATE='A'");
			while ($fetch3 = mysql_fetch_array($sql3)) {
				$content .= '<tr><td>'.$fetch3['REQUESTNO'].'</td><td>'.$fetch3['REQUESTER'].'</td><td>'.$fetch3['TARGET'].'</td><td>已接受</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="query(\''.$fetch3['REQUESTNO'].'\')">查看</button></td></tr></table>';
			}
			while ($fetch4 = mysql_fetch_array($sql4)) {
				$content .= '<tr><td>'.$fetch3['REQUESTNO'].'</td><td>'.$fetch3['REQUESTER'].'</td><td>'.$fetch3['TARGET'].'</td><td>待確認</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="query(\''.$fetch3['REQUESTNO'].'\')">查看</button></td></tr></table>';
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
	$target = $content['target'];
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
	elseif (empty($target)) {
		return 'Empty warehouse number';
	}
	elseif (strlen($target) > 20) {
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
			$sql2 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$target'");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				return 'Unfound warehouse';
			}
			else {
				for ($i = 1; $i < 10; $i++) {
					$itemno = 'itemno'.$i;
					if (!empty($content[$itemno])) {
						$sql3 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno' AND ACTCODE=1");
						$sql4 = mysql_query("SELECT * FROM WHOUSEITEM WHERE WHOUSENO='$target' AND ITEMNO='$itemno' AND ACTCODE=1");
						if ($sql3 == false || mysql_num_rows($sql3) == 0) {
							return 'Unreceivable warehouse item ' . query_name($itemno);
							break;
						}
						elseif ($sql4 == false || mysql_num_rows($sql4) == 0) {
							return 'Unfound warehouse item ' . query_name($itemno);
							break;
						}
						else {
							$fetch4 = mysql_fetch_array($sql4);
							if ($fetch4['REQUEST'] == 0) {
								return 'Unrequestable item ' . query_name($itemno);
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
	$requestno = $content['requestno'];
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
			$content = '<table><tr><td>請求編號</td><td>請求方</td><td>目標方</td><td>狀態</td><td>建立時間</td><td>操作</td></tr>';
			$sql3 = mysql_query("SELECT * FROM REQUEST WHERE REQUESTNO='$requestno'");
			$fetch3 = mysql_fetch_array($sql3);
			if ($fetch3['STATE'] == 'B' && $fetch3['REQUESTNO'] == $whouseno) {
				$content .= '<tr><td>'.$fetch3['REQUESTNO'].'</td><td>'.$fetch3['REQUESTER'].'</td><td>'.$fetch3['TARGET'].'</td><td>已接受</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="check(\''.$fetch3['REQUESTNO'].'\')">確認</button></td></tr>';
			}
			elseif ($fetch3['STATE'] == 'A' && $fetch3['TARGET'] == $whouseno) {
				$content .= '<tr><td>'.$fetch3['REQUESTNO'].'</td><td>'.$fetch3['REQUESTER'].'</td><td>'.$fetch3['TARGET'].'</td><td>待確認</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="accept(\''.$fetch3['REQUESTNO'].'\')">接收</button><button onclick="reject(\''.$fetch3['REQUESTNO'].'\')">拒絕</button></td></tr>';
			}
			$content .= '</table><h3>請求內容</h3><table><tr><td>請求編號</td><td>物料名稱</td><td>運送數量</td></tr>';
			$sql4 = mysql_query("SELECT * FROM REQUESTITEM WHERE REQUESTNO='$requestno'");
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
	$requestno = $content['requestno'];
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
			$sql3 = mysql_query("SELECT * FROM REQUEST WHERE REQUESTNO='$requestno'");
			$fetch3 = mysql_fetch_array($sql3);
			$minuswhouse = $fetch3['TARGET'];
			if ($fetch3['STATE'] != 'A') {
				return 'Wrong state';
			}
			elseif ($whouseno != $minuswhouse) {
				return 'Unmatch warehouse authority';
			}
			else {
				$sql4 = mysql_query("SELECT * FROM REQUESTITEM WHERE REQUESTNO='$requestno'");
				while ($fetch4 = mysql_fetch_array($sql4)) {
					$itemno = $fetch4['ITEMNO'];
					$amt = $fetch4['AMT'];
					$sql5 = mysql_query("SELECT AMT FROM WHOUSEITEM WHERE WHOUSENO='$whouseno' AND ITEMNO='$itemno'");
					$fetch5 = mysql_fetch_array($sql5);
					if ($fetch5['AMT'] < $amt) {
						return 'Not enough ' . query_name($itemno);
						break;
					}
					else {
						mysql_query("UPDATE WHOUSEITEM SET AMT=AMT-'$amt' WHERE WHOUSENO='$minuswhouse' AND ITEMNO='$itemno'");
						mysql_query("UPDATE WHOUSE SET WHOUSEAMT=WHOUSEAMT-'$amt' WHERE WHOUSENO='$minuswhouse'");
					}
				}
				mysql_query("UPDATE REQUEST SET STATE='B' WHERE REQUESTNO='$requestno'");
				return 'Success';
			}
		}
	}
}

function reject($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$requestno = $content['requestno'];
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
			$sql3 = mysql_query("SELECT * FROM REQUEST WHERE REQUESTNO='$requestno'");
			$fetch3 = mysql_fetch_array($sql3);
			$minuswhouse = $fetch3['TARGET'];
			if ($fetch3['STATE'] != 'A') {
				return 'Wrong state';
			}
			elseif ($whouseno != $minuswhouse) {
				return 'Unmatch warehouse authority';
			}
			else {
				mysql_query("UPDATE REQUEST SET STATE='C' WHERE REQUESTNO='$requestno'");
				return 'Success';
			}
		}
	}
}

function check($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$requestno = $content['requestno'];
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
			$sql3 = mysql_query("SELECT * FROM REQUEST WHERE REQUESTNO='$requestno'");
			$fetch3 = mysql_fetch_array($sql3);
			$addwhouse = $fetch3['REQUESTER'];
			if ($fetch3['STATE'] != 'B') {
				return 'Wrong state';
			}
			elseif ($whouseno != $addwhouse) {
				return 'Unmatch warehouse authority';
			}
			else {
				$sql4 = mysql_query("SELECT * FROM REQUESTITEM WHERE REQUESTNO='$requestno'");
				while ($fetch4 = mysql_fetch_array($sql4)) {
					$itemno = $fetch4['ITEMNO'];
					$amt = $fetch4['AMT'];
					mysql_query("UPDATE WHOUSEITEM SET AMT=AMT+'$amt' WHERE WHOUSENO='$addwhouse' AND ITEMNO='$itemno'");
					mysql_query("UPDATE WHOUSE SET WHOUSEAMT=WHOUSEAMT+'$amt' WHERE WHOUSENO='$addwhouse'");
				}
				mysql_query("UPDATE REQUEST SET STATE='D' WHERE REQUESTNO='$requestno'");
				return 'Success';
			}
		}
	}
}

function search($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$requestno = $content['requestno'];
	$requester = $content['requester'];
	$target = $content['target'];
	$whouse = $content['whouse'];
	$item = $content['item'];
	$createtimestart = $content['createtimestart'];
	$createtimeend = $content['createtimeend'];
	$closetimestart = $content['closetimestart'];
	$closetimeend = $content['closetimeend'];
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
		elseif (empty($whouseno) && $fetch1['AUTHORITY'] != 'A') {
			return 'No authority';
		}
		else {
			if (!empty($whouseno)) {
				$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
				$fetch2 = mysql_fetch_array($sql2);
				if (!in_array($fetch2['AUTHORITY'], array('A', 'B', 'C'))) {
					return 'No authority';
				}
				else {
					$sql3 = "SELECT * FROM REQUEST WHERE (REQUESTER='$whouseno' || TARGET='$whouseno')";
				}
			}
			else {
				if ($fetch1['AUTHORITY'] != 'A') {
					return 'No authority';
				}
				else {
					$sql3 = "SELECT * FROM REQUEST WHERE 1";
				}
			}
			if (!empty($requestno)) {
				$sql3 .= " AND REQUESTNO='$requestno'";
			}
			if (!empty($requester)) {
				$sql3 .= " AND REQUESTER='$requester'";
			}
			if (!empty($target)) {
				$sql3 .= " AND TARGET='$target'";
			}
			if (!empty($whouse)) {
				$sql3 .= " AND (REQUESTER='$whouse' || TARGET='$whouse')";
			}
			if (!empty($item)) {
				$sql3 .= " AND REQUESTNO IN (SELECT DISTINCT REQUESTNO FROM REQUESTITEM WHERE ITEMNO='$item')";
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
				$content = '<table><tr><td>請求編號</td><td>請求方</td><td>目標方</td><td>狀態</td><td>建立時間</td><td>操作</td></tr>';
				while ($fetch3 = mysql_fetch_array($sql3)) {
					$content .= '<tr><td>'.$fetch3['REQUESTNO'].'</td><td>'.$fetch3['REQUESTER'].'</td><td>'.$fetch3['TARGET'].'</td><td>'.transform_state($fetch3['STATE']).'</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="view(\''.$fetch3['REQUESTNO'].'\')">查看</button></td></tr></table>';
				}
				$content .= '</table>';
				return array('message' => 'Success', 'content' => $content);
			}
		}
	}
}

function export($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$requestnostart = $content['requestnostart'];
	$requestnoend = $content['requestnoend'];
	$requester = $content['requester'];
	$target = $content['target'];
	$whouse = $content['whouse'];
	$item = $content['item'];
	$createtimestart = $content['createtimestart'];
	$createtimeend = $content['createtimeend'];
	$closetimestart = $content['closetimestart'];
	$closetimeend = $content['closetimeend'];
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
		elseif (empty($whouseno) && $fetch1['AUTHORITY'] != 'A') {
			return 'No authority';
		}
		else {
			if (!empty($whouseno)) {
				$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
				$fetch2 = mysql_fetch_array($sql2);
				if (!in_array($fetch2['AUTHORITY'], array('A', 'B', 'C'))) {
					return 'No authority';
				}
				else {
					$sql3 = "SELECT * FROM REQUEST WHERE (REQUESTER='$whouseno' || TARGET='$whouseno')";
				}
			}
			else {
				if ($fetch1['AUTHORITY'] != 'A') {
					return 'No authority';
				}
				else {
					$sql3 = "SELECT * FROM REQUEST WHERE 1";
				}
			}
			if (!empty($requestnostart)) {
				$sql3 .= " AND REQUESTNO>='$requestnostart'";
			}
			if (!empty($requestnoend)) {
				$sql3 .= " AND REQUESTNO<='$requestnoend'";
			}
			if (!empty($requester)) {
				$sql3 .= " AND REQUESTER='$requester'";
			}
			if (!empty($target)) {
				$sql3 .= " AND TARGET='$target'";
			}
			if (!empty($whouse)) {
				$sql3 .= " AND (REQUESTER='$whouse' || TARGET='$whouse')";
			}
			if (!empty($item)) {
				$sql3 .= " AND REQUESTNO IN (SELECT DISTINCT REQUESTNO FROM REQUESTITEM WHERE ITEMNO='$item')";
			}
			if (!empty($createtimestart)) {
				$sql3 .= " AND CREATETIME>='$createtimestart'";
			}
			if (!empty($createtimeend)) {
				$sql3 .= " AND CREATETIME<='$createtimeend'";
			}
			if (!empty($closetimestart)) {
				$sql3 .= " AND STATE='E' AND UPDATETIME>='$closetimestart'";
			}
			if (!empty($closetimeend)) {
				$sql3 .= " AND STATE='E' AND UPDATETIME<='$closetimeend'";
			}
			$sql3 = mysql_query($sql3);
			if ($sql3 == false || mysql_num_rows($sql3) == 0) {
				return 'No data';
			}
			else {
				/*
				$content = '<table><tr><td>請求編號</td><td>請求方</td><td>目標方</td><td>狀態</td><td>建立時間</td><td>操作</td></tr>';
				while ($fetch3 = mysql_fetch_array($sql3)) {
					$content .= '<tr><td>'.$fetch3['REQUESTNO'].'</td><td>'.$fetch3['REQUESTER'].'</td><td>'.$fetch3['TARGET'].'</td><td>'.transform_state($fetch3['STATE']).'</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="view(\''.$fetch3['REQUESTNO'].'\')">查看</button></td></tr></table>';
				}
				$content .= '</table><button onclick="export_request()">確定輸出</button>';
				*/
				return 'Success';
			}
		}
	}
}

function export_search($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$requestnostart = $content['requestnostart'];
	$requestnoend = $content['requestnoend'];
	$requester = $content['requester'];
	$target = $content['target'];
	$whouse = $content['whouse'];
	$item = $content['item'];
	$createtimestart = $content['createtimestart'];
	$createtimeend = $content['createtimeend'];
	$closetimestart = $content['closetimestart'];
	$closetimeend = $content['closetimeend'];
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
		elseif (empty($whouseno) && $fetch1['AUTHORITY'] != 'A') {
			return 'No authority';
		}
		else {
			if (!empty($whouseno)) {
				$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'");
				$fetch2 = mysql_fetch_array($sql2);
				if (!in_array($fetch2['AUTHORITY'], array('A', 'B', 'C'))) {
					return 'No authority';
				}
				else {
					$sql3 = "SELECT * FROM REQUEST WHERE (REQUESTER='$whouseno' || TARGET='$whouseno')";
				}
			}
			else {
				if ($fetch1['AUTHORITY'] != 'A') {
					return 'No authority';
				}
				else {
					$sql3 = "SELECT * FROM REQUEST WHERE 1";
				}
			}
			if (!empty($requestnostart)) {
				$sql3 .= " AND REQUESTNO>='$requestnostart'";
			}
			if (!empty($requestnoend)) {
				$sql3 .= " AND REQUESTNO<='$requestnoend'";
			}
			if (!empty($requester)) {
				$sql3 .= " AND REQUESTER='$requester'";
			}
			if (!empty($target)) {
				$sql3 .= " AND TARGET='$target'";
			}
			if (!empty($whouse)) {
				$sql3 .= " AND (REQUESTER='$whouse' || TARGET='$whouse')";
			}
			if (!empty($item)) {
				$sql3 .= " AND REQUESTNO IN (SELECT DISTINCT REQUESTNO FROM REQUESTITEM WHERE ITEMNO='$item')";
			}
			if (!empty($createtimestart)) {
				$sql3 .= " AND CREATETIME>='$createtimestart'";
			}
			if (!empty($createtimeend)) {
				$sql3 .= " AND CREATETIME<='$createtimeend'";
			}
			if (!empty($closetimestart)) {
				$sql3 .= " AND STATE='E' AND UPDATETIME>='$closetimestart'";
			}
			if (!empty($closetimeend)) {
				$sql3 .= " AND STATE='E' AND UPDATETIME<='$closetimeend'";
			}
			$sql3 = mysql_query($sql3);
			if ($sql3 == false || mysql_num_rows($sql3) == 0) {
				return 'No data';
			}
			else {
				$content = '<table><tr><td>請求編號</td><td>請求方</td><td>目標方</td><td>狀態</td><td>建立時間</td><td>操作</td></tr>';
				while ($fetch3 = mysql_fetch_array($sql3)) {
					$content .= '<tr><td>'.$fetch3['REQUESTNO'].'</td><td>'.$fetch3['REQUESTER'].'</td><td>'.$fetch3['TARGET'].'</td><td>'.transform_state($fetch3['STATE']).'</td><td>'.$fetch3['CREATETIME'].'</td><td><button onclick="view(\''.$fetch3['REQUESTNO'].'\')">查看</button></td></tr></table>';
				}
				$content .= '</table><button onclick="export_request()">確定輸出</button>';
				return array('message' => 'Success', 'content' => $content);
			}
		}
	}
}

function view($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whouseno = $content['whouseno'];
	$requestno = $content['requestno'];
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
			$content = '<table><tr><td>請求編號</td><td>請求方</td><td>目標方</td><td>狀態</td><td>建立時間</td></tr>';
			$sql3 = mysql_query("SELECT * FROM REQUEST WHERE REQUESTNO='$requestno'");
			$fetch3 = mysql_fetch_array($sql3);
			$content .= '<tr><td>'.$fetch3['REQUESTNO'].'</td><td>'.$fetch3['REQUESTER'].'</td><td>'.$fetch3['TARGET'].'</td><td>'.transform_state($fetch3['STATE']).'</td><td>'.$fetch3['CREATETIME'].'</td></tr>';
			$content .= '</table><h3>請求內容</h3><table><tr><td>物料編號</td><td>物料名稱</td><td>運送數量</td></tr>';
			$sql4 = mysql_query("SELECT * FROM REQUESTITEM WHERE REQUESTNO='$requestno'");
			while ($fetch4 = mysql_fetch_array($sql4)) {
				$content .= '<tr><td>'.$fetch4['ITEMNO'].'</td><td>'.query_name($fetch4['ITEMNO']).'</td><td>'.$fetch4['AMT'].'</td></tr>';
			}
			$content .= '</table>';
			return array('message' => 'Success', 'content' => $content);
		}
	}
}

function get_requestno() {
	$sql = mysql_query("SELECT NEXT_REQUESTNO FROM SYSTEM");
	$fetch = mysql_fetch_array($sql);
	return $fetch['NEXT_REQUESTNO'];
}

function update_requestno() {
	$sql = mysql_query("SELECT NEXT_REQUESTNO FROM SYSTEM");
	$fetch = mysql_fetch_array($sql);
	$requestno = $fetch['NEXT_REQUESTNO'];
	$requestno = substr($requestno, 1) + 1;
	$requestno = 'R' . $requestno;
	mysql_query("UPDATE SYSTEM SET NEXT_REQUESTNO='$requestno'");
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