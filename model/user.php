<?php
include_once("../resource/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($_POST['module'] == 'user') {
		if ($_POST['event'] == 'login') {
			$message = login($_POST);
			if (is_array($message)) {
				if (isset($message['whouseno'])) {
					echo json_encode(array('message' => $message['message'], 'token' => $message['token'], 'whouseno' => $message['whouseno']));
					return;
				}
				else {
					echo json_encode(array('message' => $message['message'], 'token' => $message['token']));
					return;
				}
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'logout') {
			$message = logout($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'logon') {
			$message = logon($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'auth') {
			$message = auth($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'release') {
			$message = release($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'change_password') {
			$message = change_password($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'get_auth') {
			$message = get_auth($_POST);
			if (is_array($message)) {
				echo json_encode(array('message' => $message['message'], 'authority' => $message['authority']));
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'available') {
			$message = available($_POST);
			if (is_array($message)) {
				echo json_encode($message);
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'change_whouse') {
			$message = change_whouse($_POST);
			echo json_encode(array('message' => $message));
			return;
		}
		elseif ($_POST['event'] == 'query_verify') {
			$message = query_verify($_POST);
			if (is_array($message)) {
				echo json_encode($message);
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'query_authorize') {
			$message = query_authorize($_POST);
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

function encrypt($text) {
	return substr(md5(substr(md5($text), 6, 12)), 6, 12);
}

function get_token() {
	$str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$code = '';
	for ($i = 0; $i < 12; $i++) {
		$code .= $str[mt_rand(0, strlen($str)-1)];
	}
	return $code;
}

function query_name($whouseno) {
	$sql = mysql_query("SELECT WHOUSENM FROM WHOUSE WHERE WHOUSENO='$whouseno'");
	$fetch = mysql_fetch_array($sql);
	return $fetch['WHOUSENM'];
}

function check_equal($std, $auth) {
	if ($std == $auth) { return ' selected'; }
	else { return ''; }
}

function login($content) {
	$account = $content['account'];
	$password = $content['password'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account'");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($password)) {
		return 'Empty password';
	}
	elseif (mysql_num_rows($sql1) == 0) {
		return 'Unregistered account';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['ACTCODE'] == 2) {
			return 'Unauthorized account';
		}
		elseif (encrypt($password) != $fetch1['PASSWORD']) {
			return 'Wrong password';
		}
		else {
			$token = get_token();
			date_default_timezone_set('Asia/Taipei');
			$date = date("Y-m-d H:i:s");
			$sql2 = "UPDATE USER SET TOKEN='$token', LASTLOGINTIME='$date' WHERE ACCOUNT='$account'";
			if (mysql_query($sql2)) {
				$sql3 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND AUTHORITY!='C'");
				if ($sql3 != false && mysql_num_rows($sql3) == 1) {
					$fetch3 = mysql_fetch_array($sql3);
					return array('message' => 'Success', 'token' => $token, 'whouseno' => $fetch3['WHOUSENO']);
				}
				else {
					return array('message' => 'Success', 'token' => $token);
				}
			}
			else {
				return 'Database operation error';
			}
		}
	}
}

function logout($content) {
	$account = $content['account'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE='1'");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	else {
		$sql2 = "UPDATE USER SET TOKEN='' WHERE ACCOUNT='$account'";
		if (mysql_query($sql2)) {
			return 'Success';
		}
		else {
			return 'Database operation error';
		}
	}
}

function logon($content) {
	$account = $content['account'];
	$password = $content['password'];
	$password2 = $content['password2'];
	$usernm = $content['usernm'];
	$phone = $content['phone'];
	$email = $content['email'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account'");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($usernm)) {
		return 'Empty name';
	}
	elseif (strlen($usernm) > 50) {
		return 'User name exceed length limit';
	}
	elseif (empty($password)) {
		return 'Empty password';
	}
	elseif (empty($password2)) {
		return 'Empty verify password';
	}
	elseif (mysql_num_rows($sql1) > 0) {
		return 'Registered account';
	}
	elseif ((strlen($password) > 20) || (strlen($password2) > 20)) {
		return 'Password exceed length limit';
	}
	elseif ($password != $password2) {
		return 'Unequal password';
	}
	elseif (!ctype_alnum($password) || !ctype_alnum($password2)) {
		return 'Wrong password format';
	}
	else {
		date_default_timezone_set('Asia/Taipei');
		$date = date("Y-m-d H:i:s");
		$password = encrypt($password);
		$sql2 = "INSERT INTO USER (ACCOUNT, PASSWORD, USERNM, PHONE, EMAIL, CREATETIME, ACTCODE) VALUES ('$account', '$password', '$name', '$phone', '$email', '$date', 2)";
		if (mysql_query($sql2)) {
			return 'Success';
		}
		else {
			return 'Database operation error';
		}
	}
}

function query_verify($content) {
	$account = $content['account'];
	$token = $content['token'];
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
		elseif ($fetch1['AUTHORITY'] != 'A') {
			return 'No authority';
		}
		else {
			$content = '';
			$sql2 = mysql_query("SELECT * FROM USER WHERE ACTCODE=2");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				$content = '<p>無等待核可之帳號</p>';
			}
			else {
				$content = '<table><tr><td>使用者帳號</td><td>使用者姓名</td><td>使用者電話</td><td>使用者信箱</td><td>註冊時間</td><td>操作</td></tr>';
				while ($fetch2 = mysql_fetch_array($sql2)) {
					$content .= '<tr><td>'.$fetch2['ACCOUNT'].'</td><td>'.$fetch2['USERNM'].'</td><td>'.$fetch2['PHONE'].'</td><td>'.$fetch2['EMAIL'].'</td><td>'.$fetch2['CREATETIME'].'</td><td><button onclick="auth(\''.$fetch2['ACCOUNT'].'\')">接受</button><button onclick="release(\''.$fetch2['ACCOUNT'].'\')">拒絕</button></td></tr>';
				}
				$content .= '</table>';
			}
			$content .= '<button onclick="location.assign(\'index.php\')">返回首頁</button>';
			return array('message' => 'Success', 'content' => $content);
		}
	}
}

function query_authorize($content) {
	$account = $content['account'];
	$token = $content['token'];
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
		elseif ($fetch1['AUTHORITY'] != 'A') {
			return 'No authority';
		}
		else {
			$content = '';
			$sql2 = mysql_query("SELECT * FROM USER WHERE ACTCODE=1 AND ACCOUNT!='$account' ORDER BY AUTHORITY, CREATETIME, ACCOUNT");
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				$content = '<p>無可授權之帳號</p>';
			}
			else {
				$content = '<table><tr><td>使用者帳號</td><td>使用者姓名</td><td>使用者電話</td><td>使用者信箱</td><td>註冊時間</td><td>最後登入時間</td><td>操作</td></tr>';
				while ($fetch2 = mysql_fetch_array($sql2)) {
					$content .= '<tr><td>'.$fetch2['ACCOUNT'].'</td><td>'.$fetch2['USERNM'].'</td><td>'.$fetch2['PHONE'].'</td><td>'.$fetch2['EMAIL'].'</td><td>'.$fetch2['CREATETIME'].'</td><td>'.$fetch2['LASTLOGINTIME'].'</td><td><button onclick="view(\''.$fetch2['ACCOUNT'].'\')">查看</button></td></tr>';
				}
				$content .= '</table>';
			}
			$content .= '<button onclick="location.assign(\'index.php\')">返回首頁</button>';
			return array('message' => 'Success', 'content' => $content);
		}
	}
}

function auth($content) {
	$account = $content['account'];
	$token = $content['token'];
	$target = $content['target'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$target' AND ACTCODE=2");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif (empty($target)) {
		return 'Empty target';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif ($sql2 == false) {
		return 'Unregistered target';
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
			$sql3 = "UPDATE USER SET ACTCODE=1 WHERE ACCOUNT='$target'";
			if (mysql_query($sql3)) {
				$sql4 = mysql_query("SELECT WHOUSENO FROM WHOUSE");
				while ($fetch4 = mysql_fetch_array($sql4)) {
					$whouseno = $fetch4['WHOUSENO'];
					mysql_query("INSERT INTO USERWHOUSE (ACCOUNT, WHOUSENO) VALUES ('$target', '$whouseno')");
				}
				return 'Success';
			}
			else {
				return 'Database operation error';
			}
		}
	}
}

function release($content) {
	$account = $content['account'];
	$token = $content['token'];
	$target = $content['target'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$target' AND ACTCODE=2");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif (empty($target)) {
		return 'Empty target';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif ($sql2 == false) {
		return 'Unregistered target';
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
			$sql3 = "DELETE FROM USER WHERE ACCOUNT='$target'";
			if (mysql_query($sql3)) {
				return 'Success';
			}
			else {
				return 'Database operation error';
			}
		}
	}
}

function change_password($content) {
	$account = $content['account'];
	$token = $content['token'];
	$password = $content['password'];
	$password_new = $content['password_new'];
	$password_new2 = $content['password_new2'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif (empty($password)) {
		return 'Empty original password';
	}
	elseif (empty($password_new)) {
		return 'Empty password';
	}
	elseif (empty($password_new2)) {
		return 'Empty verify password';
	}
	elseif ($password_new != $password_new2) {
		return 'Unequal password';
	}
	elseif ((strlen($password_new) > 20) || (strlen($password_new2) > 20)) {
		return 'Password exceed length limit';
	}
	elseif (!ctype_alnum($password_new) || !ctype_alnum($password_new2)) {
		return 'Wrong password format';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != md5($account.$token)) {
			return 'Wrong token';
		}
		elseif ($fetch1['PASSWORD'] != encrypt($password)) {
			return 'Wrong password';
		}
		else {
			date_default_timezone_set('Asia/Taipei');
			$date = date("Y-m-d H:i:s");
			$password_new = encrypt($password_new);
			$sql2 = "UPDATE USER SET PASSWORD='$password_new' WHERE ACCOUNT='$account'";
			if (mysql_query($sql2)) {
				return 'Success';
			}
			else {
				return 'Database operation error';
			}
		}
	}
}

function get_auth($content) {
	$account = $content['account'];
	$token = $content['token'];
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
		else {
			return array('message' => 'Success', 'authority' => $fetch1['AUTHORITY']);
		}
	}
}

function available($content) {
	$account = $content['account'];
	$token = $content['token'];
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
		else {
			$content = '<h3>請選擇欲操作之倉庫</h3>';
			$sql2 = mysql_query("SELECT * FROM USERWHOUSE WHERE ACCOUNT='$account' AND AUTHORITY!='C'");
			if ($fetch1['AUTHORITY'] == 'A') {
				$content .= '<tr><td colspan="4"><button onclick="change_whouse(\'\')">直接進入</button></td></tr>';
			}
			if ($sql2 == false || mysql_num_rows($sql2) == 0) {
				$content .= '<h3>無可操作之倉庫</h3>';
			}
			else {
				if ($fetch1['AUTHORITY'] == 'A') {
					$content .= '<tr><td colspan="4"><button onclick="change_whouse(\'\')">直接進入</button></td></tr>';
				}
				$content .= '<tr><td>倉庫編號</td><td>倉庫名稱</td><td>最後操作日期</td><td>操作</td></tr></table>';
				while ($fetch2 = mysql_fetch_array($sql2)) {
					$content .= '<tr><td>'.$fetch2['WHOUSENO'].'</td><td>'.query_name($fetch2['WHOUSENO']).'</td><td>'.$fetch2['LASTUSETIME'].'</td><td><button onclick="change_whouse(\''.$fetch2['WHOUSENO'].'\')"></button></td></tr>';
				}
				$content .= '</table>';
			}
			return array('message' => 'Success', 'content' => $content);
		}
	}
}

function change_whouse($content) {
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
		elseif ($fetch1['AUTHORITY'] == 'A' && empty($whouseno)) {
			return 'Success';
		}
		else {
			if (empty($whouseno)) {
				return 'Empty warehouse number';
			}
			elseif (strlen($whouseno) > 20) {
				return 'Warehouse number exceed length limit';
			}
			elseif ($fetch2['AUTHORITY'] != 'A' && $fetch2['AUTHORITY'] != 'B') {
				return 'No authority';
			}
			else {
				date_default_timezone_set('Asia/Taipei');
				$date = date("Y-m-d H:i:s");
				$sql3 = "UPDATE USERWHOUSE SET LASTUSETIME='$date' WHERE ACCOUNT='$account' AND WHOUSENO='$whouseno'";
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

function view($content) {
	$account = $content['account'];
	$token = $content['token'];
	$target = $content['target'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$target' AND ACTCODE=1");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif (empty($target)) {
		return 'Empty target';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif ($sql2 == false) {
		return 'Unregistered target';
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
			$fetch2 = mysql_fetch_array($sql2);
			$content = '<table><tr><td>使用者帳號</td><td>'.$fetch2['ACCOUNT'].'</td></tr><tr><td>使用者名稱</td><td>'.$fetch2['USERNM'].'</td></tr><tr><td>使用者電話</td><td>'.$fetch2['PHONE'].'</td></tr><tr><td>使用者信箱</td><td>'.$fetch2['EMAIL'].'</td></tr><tr><td>註冊時間</td><td>'.$fetch2['CREATETIME'].'</td></tr><tr><td>使用者權限</td><td><select id="authority"><option value="A"'.check_equal('A', $fetch2['AUTHORITY']).'>可授權</option><option value="B"'.check_equal('B', $fetch2['AUTHORITY']).'>可使用</option><option value="C"'.check_equal('C', $fetch2['AUTHORITY']).'>無權限</option></select></td></tr><tr><td>操作</td><td><button onclick="change_authority(\''.$fetch2['ACCOUNT'].'\')">確定更改</button></td></tr></table>';
			return array('message' => 'Success', 'content' => $content);
		}
	}
}



function search_account($account, $token, $index) {
	$sql1 = mysql_query("SELECT * FROM MEMBERMAS WHERE ACCOUNT='$account' AND ACTCODE=1");
	$sql2 = mysql_query("SELECT * FROM MEMBERMAS WHERE ACCOUNT='$index' AND ACTCODE=1");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif (empty($index)) {
		return 'Empty target';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	elseif ($sql2 == false) {
		return 'Unregistered target';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != md5($account.$token)) {
			return 'Wrong token';
		}
		elseif ($fetch1['AUTHORITY'] != 'A') {
			return 'No authority';
		}
		else {
			$fetch2 = mysql_fetch_array($sql2);
			if ($fetch1['AUTHORITY'] == 'B' && $fetch2['AUTHORITY'] != 'B') {
				return 'No authority';
			}
			elseif ($fetch1['AUTHORITY'] == 'C' && $fetch2['AUTHORITY'] != 'C') {
				return 'No authority';
			}
			elseif ($fetch1['AUTHORITY'] == 'D' && $fetch2['AUTHORITY'] != 'D') {
				return 'No authority';
			}
			elseif ($fetch1['AUTHORITY'] == 'I' && $fetch2['AUTHORITY'] != 'I') {
				return 'No authority';
			}
			else { 
				$content = '<table><tr><th>使用者帳號</th><th>使用者名稱</th><th>帳號建立日期</th><th>帳號最後登入日期</th><th>權限</th></tr><tr><td>'.$fetch2['ACCOUNT'].'</td><td>'.$fetch2['NAME'].'</td><td>'.$fetch2['CREATETIME'].'</td><td>'.$fetch2['ONLINEDATE'].'</td><td>'.transfer($fetch2['AUTHORITY']).'</td></tr></table>';
				return array('message' => 'Success', 'content' => $content);
			}
		}
	}
}