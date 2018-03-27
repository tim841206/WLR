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

function export_search($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whousenostart = $content['whousenostart'];
	$whousenoend = $content['whousenoend'];
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
			$sql2 = "SELECT * FROM WHOUSEITEM WHERE ACTCODE=1";
			if (!empty($whousenostart)) {
				$sql2 .= "AND WHOUSENO>='$whousenostart'";
			}
			if (!empty($whousenoend)) {
				$sql2 .= "AND WHOUSENO<='$whousenoend'";
			}
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
				$content = '<table><tr><td>倉庫編號</td><td>倉庫名稱</td><td>物料編號</td><td>物料名稱</td><td>存量</td><td>運送權</td><td>請求權</td><td>最後修改時間</td><td>備註</td></tr>';
				while ($fetch2 = mysql_fetch_array($sql2)) {
					$content .= '<tr><td>'.$fetch2['WHOUSENO'].'</td><td>'.query_whousenm($fetch2['WHOUSENO']).'</td><td>'.$fetch2['ITEMNO'].'</td><td>'.query_itemnm($fetch2['ITEMNO']).'</td><td>'.$fetch2['AMT'].'</td><td>'.$fetch2['LOGISTIC'].'</td><td>'.$fetch2['REQUEST'].'</td><td>'.$fetch2['UPDATETIME'].'</td><td>'.$fetch2['MEMO'].'</td></tr>';
				}
				$content .= '</table><button onclick="export_whouseitem()">確定輸出</button>';
				return array('message' => 'Success', 'content' => $content);
			}
		}
	}
}

function export($content) {
	$account = $content['account'];
	$token = $content['token'];
	$whousenostart = $content['whousenostart'];
	$whousenoend = $content['whousenoend'];
	$itemnostart = $content['itemnostart'];
	$itemnoend = $content['itemnoend'];
	$sql1 = mysql_query("SELECT * FROM USER WHERE ACCOUNT='$account' AND ACTCODE=1");
	if (!include_once("../resource/TCPDF/tcpdf.php")) {
		return 'Unable to load export tool';
	}
	elseif (empty($account)) {
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
			$sql2 = "SELECT * FROM WHOUSEITEM WHERE ACTCODE=1";
			if (!empty($whousenostart)) {
				$sql2 .= "AND WHOUSENO>='$whousenostart'";
			}
			if (!empty($whousenoend)) {
				$sql2 .= "AND WHOUSENO<='$whousenoend'";
			}
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
				download_whouseitem_pdf($sql2);
				download_whouseitem_xls($sql2);
				return 'Success';
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

function query_whousenm($whouseno) {
	$sql = mysql_query("SELECT WHOUSENM FROM WHOUSE WHERE WHOUSENO='$whouseno'");
	$fetch = mysql_fetch_array($sql);
	return $fetch['WHOUSENM'];
}

function query_itemnm($itemno) {
	$sql = mysql_query("SELECT ITEMNM FROM ITEM WHERE ITEMNO='$itemno'");
	$fetch = mysql_fetch_array($sql);
	return $fetch['ITEMNM'];
}

function download_whouseitem_pdf($resource) {
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		require_once(dirname(__FILE__).'/lang/eng.php');
		$pdf->setLanguageArray($l);
	}
	$pdf->AddPage();
	$pdf->SetFont('cid0jp', 'B', 20);
	$pdf->Write(0, '倉庫物料', '', 0, 'C', true, 0, false, false, 0);
	$pdf->SetFont('cid0jp', '', 12);

	$tbl = '<table><tr><td>倉庫編號</td><td>倉庫名稱</td><td>物料編號</td><td>物料名稱</td><td>存量</td><td>運送權</td><td>請求權</td><td>最後修改時間</td><td>備註</td></tr>';
	while ($fetch = mysql_fetch_array($resource)) {
		$tbl .= '<tr><td>'.$fetch['WHOUSENO'].'</td><td>'.query_whousenm($fetch['WHOUSENO']).'</td><td>'.$fetch['ITEMNO'].'</td><td>'.query_itemnm($fetch['ITEMNO']).'</td><td>'.$fetch['AMT'].'</td><td>'.$fetch['LOGISTIC'].'</td><td>'.$fetch['REQUEST'].'</td><td>'.$fetch['UPDATETIME'].'</td><td>'.$fetch['MEMO'].'</td></tr>';
	}
	$tbl .= '</table>';
	$pdf->writeHTML($tbl, true, false, false, false, '');
	ob_end_clean();
	$pdf->Output('whouseitem.pdf', 'D');
}

function download_whouseitem_xls($resource) {
	$fp = fopen("倉庫物料.xls", "w");
	$content = "倉庫編號 \t倉庫名稱 \t物料編號 \t物料名稱 \t存量 \t運送權 \t請求權 \t最後修改時間 \t備註\n";
	while ($fetch = mysql_fetch_array($resource)) {
		$content .= $fetch['WHOUSENO']."\t".query_whousenm($fetch['WHOUSENO'])."\t".$fetch['ITEMNO']."\t".query_itemnm($fetch['ITEMNO'])."\t".$fetch['AMT']."\t".$fetch['LOGISTIC']."\t".$fetch['REQUEST']."\t".$fetch['UPDATETIME']."\t".$fetch['MEMO']."\n";
	}
	if (fputs($fp, mb_convert_encoding($content, "Big5", "UTF-8"))) {
		fclose($fp);
		return 0;
	}
	else {
		fclose($fp);
		return 1;
	}
}