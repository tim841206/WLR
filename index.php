<?php
if (isset($_POST['module'])) {
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if ($_POST['module'] == 'whouse') {
			if (in_array($_POST['event'], array('create', 'modify', 'delete', 'query', 'check_empty', 'check_exist', 'check_delete', 'recover'))) {
				$id = array('account' => $_COOKIE['account'], 'token' => $_COOKIE['token']);
				$post = array_merge($id, $_POST);
				echo curl_post($post, $_POST['module']);
			}
			else {
				echo json_encode(array('message' => 'Invalid event called'));
			}
		}
		elseif ($_POST['module'] == 'item') {
			if (in_array($_POST['event'], array('create', 'modify', 'delete', 'query', 'check_empty', 'check_delete', 'recover'))) {
				$id = array('account' => $_COOKIE['account'], 'token' => $_COOKIE['token']);
				$post = array_merge($id, $_POST);
				echo curl_post($post, $_POST['module']);
			}
			else {
				echo json_encode(array('message' => 'Invalid event called'));
			}
		}
		elseif ($_POST['module'] == 'whouseitem') {
			if (in_array($_POST['event'], array('create', 'modify', 'delete', 'query', 'check_item_empty', 'check_whouse_exist', 'check_item_exist', 'check_whouse_delete', 'check_item_delete'))) {
				$id = array('account' => $_COOKIE['account'], 'token' => $_COOKIE['token']);
				$post = array_merge($id, $_POST);
				echo curl_post($post, $_POST['module']);
			}
			else {
				echo json_encode(array('message' => 'Invalid event called'));
			}
		}
		elseif ($_POST['module'] == 'logistic') {
			if (in_array($_POST['event'], array('create', 'accept', 'reject', 'check', 'search'))) {
				$id = array('account' => $_COOKIE['account'], 'token' => $_COOKIE['token'], 'whouseno' => $_COOKIE['whouseno']);
				$post = array_merge($id, $_POST);
				echo curl_post($post, $_POST['module']);
			}
			else {
				echo json_encode(array('message' => 'Invalid event called'));
			}
		}
		elseif ($_POST['module'] == 'request') {
			if (in_array($_POST['event'], array('create', 'accept', 'reject', 'check', 'search'))) {
				$id = array('account' => $_COOKIE['account'], 'token' => $_COOKIE['token']);
				$post = array_merge($id, $_POST);
				echo curl_post($post, $_POST['module']);
			}
			else {
				echo json_encode(array('message' => 'Invalid event called'));
			}
		}
		elseif ($_POST['module'] == 'user') {
			if ($_POST['event'] == 'login') {
				$return = json_decode(curl_post($_POST, $_POST['module']), true);
				if ($return['message'] == 'Success') {
					setcookie('account', $_POST['account']);
					setcookie('token', $return['token']);
					if ($return['authority'] == 'A') {
						echo json_encode(array('message' => $return['message'], 'content' => 'index.html'));
					}
					elseif ($return['authority'] == 'B') {
						echo json_encode(array('message' => $return['message'], 'content' => 'index.html'));
					}
					elseif ($return['authority'] == 'C') {
						echo json_encode(array('message' => $return['message'], 'content' => 'viewer.html'));
					}
				}
				else {
					echo json_encode(array('message' => $return['message']));
				}
			}
			elseif ($_POST['event'] == 'logout') {
				$id = array('account' => $_COOKIE['account']);
				$post = array_merge($id, $_POST);
				$return = json_decode(curl_post($post, $_POST['module']), true);
				if ($return['message'] == 'Success') {
					unset($_COOKIE['account']);
					unset($_COOKIE['token']);
					echo json_encode(array('message' => $return['message']));
				}
				else {
					echo json_encode(array('message' => $return['message']));
				}
			}
			elseif ($_POST['event'] == 'logon') {
				echo curl_post($_POST, $_POST['module']);
			}
			elseif (in_array($_POST['event'], array('change_password', 'search_account', 'search_auth', 'view', 'notice', 'auth', 'release'))) {
				$id = array('account' => $_COOKIE['account'], 'token' => $_COOKIE['token']);
				$post = array_merge($id, $_POST);
				echo curl_post($post, $_POST['module']);
			}
			else {
				echo json_encode(array('message' => 'Invalid event called'));
			}
		}
		else {
			echo json_encode(array('message' => 'Invalid module called'));
		}
	}
	else {
		return 'Invalid request method';
	}
}

elseif (isset($_COOKIE['account']) && isset($_COOKIE['token'])) {
	$return = json_decode(curl_post(array('module' => 'user', 'event' => 'get_auth', 'account' => $_COOKIE['account'], 'token' => $_COOKIE['token']), 'user'), true);
	if ($return['message'] == 'Success') {
		if ($return['authority'] == 'A') {
			include_once("view/index.html");
		}
		elseif ($return['authority'] == 'B') {
			include_once("view/index.html");
		}
		elseif ($return['authority'] == 'C') {
			include_once("view/viewer.html");
		}
	}
	else {
		unset($_COOKIE['account']);
		unset($_COOKIE['token']);
		include_once("view/user/login.html");
	}
}
else {
	include_once("view/user/login.html");
}

function curl_post($post, $module) {
	$ch = curl_init();
	$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http";
	curl_setopt($ch, CURLOPT_URL, $protocol.'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']).'/model/'.$module.'.php');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}