<?php
if (isset($_GET['module']) && isset($_GET['operate'])) {
	if ($_GET['module'] == 'whouse') {
		if (in_array($_GET['operate'], array('create', 'modify', 'delete', 'recover', 'search', 'export'))) {
			include_once('view/whouse/'.$_GET['operate'].'_whouse.html');
		}
		else {
			find_current();
		}
	}
	elseif ($_GET['module'] == 'item') {
		if (in_array($_GET['operate'], array('create', 'modify', 'delete', 'recover', 'search', 'export'))) {
			include_once('view/item/'.$_GET['operate'].'_item.html');
		}
		else {
			find_current();
		}
	}
	elseif ($_GET['module'] == 'whouseitem') {
		if (in_array($_GET['operate'], array('create', 'modify', 'delete', 'recover', 'search', 'export'))) {
			include_once('view/whouseitem/'.$_GET['operate'].'_whouseitem.html');
		}
		else {
			find_current();
		}
	}
	elseif ($_GET['module'] == 'logistic') {
		if (in_array($_GET['operate'], array('create', 'waiting', 'search', 'export'))) {
			include_once('view/logistic/'.$_GET['operate'].'_logistic.html');
		}
		else {
			find_current();
		}
	}
	elseif ($_GET['module'] == 'request') {
		if (in_array($_GET['operate'], array('create', 'waiting', 'search', 'export'))) {
			include_once('view/request/'.$_GET['operate'].'_request.html');
		}
		else {
			find_current();
		}
	}
	elseif ($_GET['module'] == 'user') {
		if (in_array($_GET['operate'], array('change_whouse', 'change_password', 'change_authority'))) {
			include_once('view/user/'.$_GET['operate'].'.html');
		}
		else {
			find_current();
		}
	}
	else {
		find_current();
	}
}
elseif (isset($_POST['module'])) {
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
			if (in_array($_POST['event'], array('create', 'waiting', 'accept', 'reject', 'check', 'query', 'search', 'export', 'check_itemno', 'check_itemamt', 'view'))) {
				$id = array('account' => $_COOKIE['account'], 'token' => $_COOKIE['token'], 'whouseno' => $_COOKIE['whouseno']);
				$post = array_merge($id, $_POST);
				echo curl_post($post, $_POST['module']);
			}
			else {
				echo json_encode(array('message' => 'Invalid event called'));
			}
		}
		elseif ($_POST['module'] == 'request') {
			if (in_array($_POST['event'], array('create', 'waiting', 'accept', 'reject', 'check', 'query', 'search', 'export', 'check_itemno', 'view'))) {
				$id = array('account' => $_COOKIE['account'], 'token' => $_COOKIE['token'], 'whouseno' => $_COOKIE['whouseno']);
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
					if (isset($return['whouseno']) && !empty($return['whouseno'])) {
						setcookie('whouseno', $return['whouseno']);
					}
				}
				echo json_encode(array('message' => $return['message']));
			}
			elseif ($_POST['event'] == 'logout') {
				$id = array('account' => $_COOKIE['account']);
				$post = array_merge($id, $_POST);
				$return = json_decode(curl_post($post, $_POST['module']), true);
				if ($return['message'] == 'Success') {
					unset($_COOKIE['account']);
					unset($_COOKIE['token']);
					unset($_COOKIE['whouseno']);
				}
				echo json_encode(array('message' => $return['message']));
			}
			elseif ($_POST['event'] == 'change_whouse') {
				$id = array('account' => $_COOKIE['account'], 'token' => $_COOKIE['token']);
				$post = array_merge($id, $_POST);
				$return = json_decode(curl_post($post, $_POST['module']), true);
				if ($return['message'] == 'Success') {
					setcookie('whouseno', $_POST['whouseno']);
				}
				echo json_encode(array('message' => $return['message']));
			}
			elseif ($_POST['event'] == 'logon') {
				echo curl_post($_POST, $_POST['module']);
			}
			elseif ($_POST['event'] == 'current_whouse') {
				if (isset($_COOKIE['whouseno']) && !empty($_COOKIE['whouseno'])) {
					$id = array('account' => $_COOKIE['account'], 'token' => $_COOKIE['token']);
					$post = array_merge($id, array('module' => 'whouse', 'event' => 'query'));
					$return = json_decode(curl_post($post, 'whouse'), true);
					if ($return['message'] == 'Success') {
						echo json_encode(array('message' => 'Success', 'content' => '操作中的倉庫：'.$return['whousenm']));
					}
					else {
						unset($_COOKIE['whouseno']);
						echo json_encode(array('message' => $return['message']));
					}
				}
				else {
					echo json_encode(array('message' => 'Success', 'content' => '操作中的倉庫：無操作中的倉庫'));
				}
			}
			elseif ($_POST['event'] == 'available') {
				$id = array('account' => $_COOKIE['account'], 'token' => $_COOKIE['token']);
				$post = array_merge($id, $_POST);
				$return = json_decode(curl_post($post, $_POST['module']), true);
				$whouseno = (isset($_COOKIE['whouseno']) && !empty($_COOKIE['whouseno'])) ? $_COOKIE['whouseno'] : '';
				echo json_encode(array_merge($return, array('whouseno' => $whouseno)));
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
	find_current();
}

else {
	include_once("view/user/entry.html");
}

function find_current() {
	$return = json_decode(curl_post(array('module' => 'user', 'event' => 'get_auth', 'account' => $_COOKIE['account'], 'token' => $_COOKIE['token']), 'user'), true);
	if ($return['message'] == 'Success') {
		if ($return['authority'] == 'A') {
			include_once("view/index.html");
		}
		elseif ($return['authority'] == 'B') {
			if (isset($_COOKIE['whouseno']) && !empty($_COOKIE['whouseno'])) {
				include_once("view/index.html");
			}
			else {
				include_once("view/user/change_whouse.html");
			}
		}
		elseif ($return['authority'] == 'C') {
			include_once("view/viewer.html");
		}
	}
	else {
		unset($_COOKIE['account']);
		unset($_COOKIE['token']);
		unset($_COOKIE['whouseno']);
		include_once("view/user/entry.html");
	}
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