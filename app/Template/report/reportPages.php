<?php
	switch (isset($_GET['report']) ? $_GET['report'] : '') {
		case '1' :
			include 'report1.php';
			break;
		case '2' :
			include 'report2.php';
			break;
		case '3' :
		case '4' :
		case '5' :
		case '7' :
			if((isset($_GET['repType']) ? $_GET['repType'] : '') == 0){
				include 'summary.php';
				break;
			}else{
				include 'detailed.php';
				break;
			}
		case '6' :
			include 'report6.php';
			break;
		case '8' :
			include 'report8.php';
			break;
		case '9' :
			include 'report9.php';
			break;
	}
?>