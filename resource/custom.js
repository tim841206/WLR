function time() {
	today = new Date();
	Year = today.getYear() + 1900;
	Month = today.getMonth()+1 <= 9 ? "0"+(today.getMonth()+1) : today.getMonth()+1;
	Day = today.getDate() <= 9 ? "0"+today.getDate() : today.getDate();
	Hour = today.getHours() <= 9 ? "0"+today.getHours() : today.getHours();
	Minute = today.getMinutes() <= 9 ? "0"+today.getMinutes() : today.getMinutes();
	Second = today.getSeconds() <= 9 ? "0"+today.getSeconds() : today.getSeconds();
	document.getElementById("now").innerHTML = Year + "-" + Month + "-" + Day + " " + Hour + ":" + Minute + ":" + Second;
	setTimeout("time()", 1000);
}

function check_50(text) {
	if (text.length > 50) {
		alert('長度超過上限');
	}
}

function check_200(text) {
	if (text.length > 200) {
		alert('長度超過上限');
	}
}

function check_nonnegative(text) {
	if (Number(text) < 0) {
		alert('請輸入非負數');
	}
}

function login() {
	var request = new XMLHttpRequest();
	request.open("POST", "index.php");
	var account = document.getElementById("account").value;
	var password = document.getElementById("password").value;
	var data = "module=user&event=login&account=" + account + "&password=" + password;
	request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	request.send(data);
	request.onreadystatechange = function() {
		if (request.readyState === 4 && request.status === 200) {
			var data = JSON.parse(request.responseText);
			if (data.message == 'Success') {
				location.assign('index.php');
			}
			else {
				alert(data.message);
			}
		}
	}
}

function logon() {
	var request = new XMLHttpRequest();
	request.open("POST", "index.php");
	var account = document.getElementById("account").value;
	var password = document.getElementById("password").value;
	var password2 = document.getElementById("password2").value;
	var name = document.getElementById("name").value;
	var phone = document.getElementById("phone").value;
	var email = document.getElementById("email").value;
	var data = "module=user&event=logon&account=" + account + "&password=" + password + "&password2=" + password2 + "&name=" + name + "&phone=" + phone + "&email=" + email;
	request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	request.send(data);
	request.onreadystatechange = function() {
		if (request.readyState === 4 && request.status === 200) {
			var data = JSON.parse(request.responseText);
			if (data.message == 'Success') {
				alert("註冊成功，請通知管理員授權");
			}
			else {
				alert(data.message);
			}
		}
	}
}

function logout() {
	var request = new XMLHttpRequest();
	request.open("POST", "index.php");
	var data = "module=user&event=logout";
	request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	request.send(data);
	request.onreadystatechange = function() {
		if (request.readyState === 4 && request.status === 200) {
			var data = JSON.parse(request.responseText);
			if (data.message == 'Success') {
				location.assign('index.php');
			}
			else {
				alert(data.message);
			}
		}
	}
}

function create_logistic() {

}

function accept_logistic() {

}

function reject_logistic() {

}

function check_logistic() {

}

function search_logistic() {

}

function create_request() {

}

function accept_request() {

}

function reject_request() {

}

function check_request() {

}

function search_request() {

}