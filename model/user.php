<?php
include_once("../resource/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($_POST['module'] == 'user') {
		if ($_POST['event'] == 'login') {
			$message = login($_POST);
			if (is_array($message)) {
				echo json_encode(array('message' => $message['message'], 'token' => $message['token'], 'authority' => $message['authority']));
				return;
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

		
		elseif ($_POST['event'] == 'search_account') {
			$message = search_account($_POST['account'], $_POST['token'], $_POST['index']);
			if (is_array($message)) {
				echo json_encode(array('message' => $message['message'], 'content' => $message['content']));
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'search_auth') {
			$message = search_auth($_POST['account'], $_POST['token'], $_POST['auth']);
			if (is_array($message)) {
				echo json_encode(array('message' => $message['message'], 'content' => $message['content']));
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'view') {
			$message = view($_POST['account'], $_POST['token']);
			if (is_array($message)) {
				echo json_encode(array('message' => $message['message'], 'content' => $message['content']));
				return;
			}
			else {
				echo json_encode(array('message' => $message));
				return;
			}
		}
		elseif ($_POST['event'] == 'notice') {
			$message = notice($_POST['account'], $_POST['token']);
			if (is_array($message)) {
				echo json_encode(array('message' => $message['message'], 'content' => $message['content']));
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
			$sql2 = "UPDATE USER SET TOKEN='$token', LASTLOGINDATE='$date' WHERE ACCOUNT='$account'";
			if (mysql_query($sql2)) {
				return array('message' => 'Success', 'token' => $token, 'authority' => $fetch1['AUTHORITY']);
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
		$sql2 = "INSERT INTO USER (ACCOUNT, PASSWORD, USERNM, PHONE, EMAIL, CREATEDATE, ACTCODE) VALUES ('$account', '$password', '$name', '$phone', '$email', '$date', 2)";
		if (mysql_query($sql2)) {
			return 'Success';
		}
		else {
			return 'Database operation error';
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
			$sql3 = "UPDATE USER SET ACTCODE=1 WHERE ACCOUNT='$index'";
			if (mysql_query($sql3)) {
				$sql4 = mysql_query("SELECT WHOUSENO FROM WHOUSE");
				while ($fetch4 = mysql_fetch_array($sql4)) {
					$whouseno = $fetch4['WHOUSENO'];
					mysql_query("INSERT INTO USERWHOUSE (ACCOUNT, WHOUSENO) VALUES ($target, $whouseno)");
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
			$sql3 = "DELETE FROM USER WHERE ACCOUNT='$index'";
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
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE='1'");
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
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE='1'");
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



function search_account($account, $token, $index) {
	$sql1 = mysql_query("SELECT * FROM MEMBERMAS WHERE ACCOUNT='$account' AND ACTCODE='1'");
	$sql2 = mysql_query("SELECT * FROM MEMBERMAS WHERE ACCOUNT='$index' AND ACTCODE='1'");
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
				$content = '<table><tr><th>使用者帳號</th><th>使用者名稱</th><th>帳號建立日期</th><th>帳號最後登入日期</th><th>權限</th></tr><tr><td>'.$fetch2['ACCOUNT'].'</td><td>'.$fetch2['NAME'].'</td><td>'.$fetch2['CREATEDATE'].'</td><td>'.$fetch2['ONLINEDATE'].'</td><td>'.transfer($fetch2['AUTHORITY']).'</td></tr></table>';
				return array('message' => 'Success', 'content' => $content);
			}
		}
	}
}

function search_auth($account, $token, $auth) {
	$sql1 = mysql_query("SELECT * FROM MEMBERMAS WHERE ACCOUNT='$account' AND ACTCODE='1'");
	if (empty($account)) {
		return 'Empty account';
	}
	elseif (empty($token)) {
		return 'Empty token';
	}
	elseif (empty($auth)) {
		return 'Empty auth';
	}
	elseif (!in_array($auth, array('A', 'B', 'C', 'D', 'E', 'I'))) {
		return 'Wrong auth format';
	}
	elseif ($sql1 == false) {
		return 'Unregistered account';
	}
	else {
		$fetch1 = mysql_fetch_array($sql1);
		if ($fetch1['TOKEN'] != md5($account.$token)) {
			return 'Wrong token';
		}
		elseif ($fetch1['AUTHORITY'] == 'B' && $auth != 'B') {
			return 'No authority';
		}
		elseif ($fetch1['AUTHORITY'] == 'C' && $auth != 'C') {
			return 'No authority';
		}
		elseif ($fetch1['AUTHORITY'] == 'D' && $auth != 'D') {
			return 'No authority';
		}
		elseif ($fetch1['AUTHORITY'] == 'I' && $auth != 'I') {
			return 'No authority';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM MEMBERMAS WHERE AUTHORITY='$auth' AND ACTCODE='1'");
			if ($sql2 == false) {
				$content = '查無資料';
			}
			else {
				$content = '<table><tr><th>使用者帳號</th><th>使用者名稱</th><th>帳號建立日期</th><th>帳號最後登入日期</th><th>權限</th></tr>';
				while ($fetch2 = mysql_fetch_array($sql2)) {
					$content .= '<tr><td>'.$fetch2['ACCOUNT'].'</td><td>'.$fetch2['NAME'].'</td><td>'.$fetch2['CREATEDATE'].'</td><td>'.$fetch2['ONLINEDATE'].'</td><td>'.transfer($fetch2['AUTHORITY']).'</td></tr>';
				}
				$content .= '</table>';
			}
			return array('message' => 'Success', 'content' => $content);
		}
	}
}

function view($account, $token) {
	$sql1 = mysql_query("SELECT * FROM MEMBERMAS WHERE ACCOUNT='$account' AND ACTCODE='1'");
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
		if ($fetch1['TOKEN'] != md5($account.$token)) {
			return 'Wrong token';
		}
		else {
			$authority = $fetch1['AUTHORITY'];
			$sql2 = ($authority == 'A') ? mysql_query("SELECT * FROM MEMBERMAS WHERE ACTCODE='1' ORDER BY ONLINEDATE DESC") : mysql_query("SELECT * FROM MEMBERMAS WHERE AUTHORITY='$authority' AND ACTCODE='1' ORDER BY ONLINEDATE DESC");
			if ($sql2 == false) {
				$content = '查無資料';
			}
			else {
				$content = '<table><tr><th>使用者帳號</th><th>使用者名稱</th><th>帳號建立日期</th><th>帳號最後登入日期</th><th>權限</th></tr>';
				while ($fetch2 = mysql_fetch_array($sql2)) {
					$content .= '<tr><td>'.$fetch2['ACCOUNT'].'</td><td>'.$fetch2['NAME'].'</td><td>'.$fetch2['CREATEDATE'].'</td><td>'.$fetch2['ONLINEDATE'].'</td><td>'.$fetch2['AUTHORITY'].'</td></tr>';
				}
				$content .= '</table>';
			}
			return array('message' => 'Success', 'content' => $content);
		}
	}
}

function notice($account, $token) {
	$sql1 = mysql_query("SELECT * FROM MEMBERMAS WHERE ACCOUNT='$account' AND ACTCODE='1'");
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
		if ($fetch1['TOKEN'] != md5($account.$token)) {
			return 'Wrong token';
		}
		elseif ($fetch1['AUTHORITY'] != 'A' || $account != 'trisoap') {
			return 'No notice';
		}
		else {
			$sql2 = mysql_query("SELECT * FROM MEMBERMAS WHERE ACTCODE='2' ORDER BY ONLINEDATE ASC");
			if (mysql_num_rows($sql2) == 0) {
				return 'No notice';
			}
			else {
				$content = '';
				while ($fetch2 = mysql_fetch_array($sql2)) {
					$content .= $fetch2['NAME'] . ' 請求獲得 ' . transfer($fetch2['AUTHORITY']) . ' 的授權。<button onclick="auth(\''.$fetch2['ACCOUNT'].'\')">授權</button><button onclick="release(\''.$fetch2['ACCOUNT'].'\')">拒絕</button><br>';
				}
				return array('message' => 'Success', 'content' => $content);
			}
		}
	}
}

function transfer($authority) {
	if ($authority == 'A') return '總部';
	elseif ($authority == 'B') return '北投';
	elseif ($authority == 'C') return '後山埤';
	elseif ($authority == 'D') return '台東';
	elseif ($authority == 'E') return '實習';
	elseif ($authority == 'I') return '宜蘭';
}