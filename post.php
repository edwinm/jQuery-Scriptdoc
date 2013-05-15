<?php
$action = $_POST['action'];
$version = $_POST['version'];
$file = $_FILES['file'];

if (!$version) {
	echo "No version entered";
	exit();
}

//echo "<pre>";
//print_r($file);
//echo "</pre>\n";

$filename = "xml/jquery-$version.xml";

if ($file['tmp_name']) {
	move_uploaded_file($file['tmp_name'], $filename);
}

switch($action) {
	case "generate scriptdoc":
		header("Location: /jquery/scriptdoc.php?file=$filename&ver=$version");
		break;
	case "generate html doc":
		header("Location: /jquery/doc.php?file=$filename&ver=$version");
		break;
	default:
		header("Location: /jquery/");
}


?>