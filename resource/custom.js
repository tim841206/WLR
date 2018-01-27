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
	var reg = "^\\d+$";
	var result = reg.test(Number(text));
	if (result == false) {
		alert('請輸入非負整數');
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