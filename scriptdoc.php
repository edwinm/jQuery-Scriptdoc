<?php
header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=\"jquery-$ver.sdoc\";" );

$file = $_GET['file'];
$ver = $_GET['ver'];
?>
/**
 * @namespace jQuery-<?php echo $ver ?>

 *
 * Scriptdoc-file for jQuery <?php echo $ver ?>.
 *
 * Created: <?php echo gmstrftime ("%b %d, %Y %T %Z", time ()); ?>

 *
 * Courtesy of Edwin Martin <edwin@bitstorm.org>
 *
 */

<?php

//$dom = domxml_open_file("jquery-$ver-docs-xml.xml");
$dom = domxml_open_file($file);
if (!$dom) {
	echo "Couln't read XML-file.";
	die;
}


$methods = $dom->get_elements_by_tagname("method");

$ma = array();

foreach ($methods as $m) {
	$fn = $m->get_attribute('name');
	$ret = $m->get_attribute('type');
//	$descObjs = $m->get_elements_by_tagname('desc');
//	if (count($descObjs) >= 1)
//		$desc = $descObjs[0]->get_content();
//	else
//		$desc = "";
	$desc = $m->get_attribute('short');

	$paramObjs = $m->get_elements_by_tagname('params');
	$params = array();
	foreach($paramObjs as $paramObj) {
		$params[] = array(
			"type" => $paramObj->get_attribute('type'),
			"name" => $paramObj->get_attribute('name')
		);
	}

	$ma[$fn][] = array(
		"fn" => $fn,
		"desc" => $desc,
		"ret" => $ret,
		"desc" => $desc,
		"params" => $params
	);
}

//print_r($ma);

foreach ($ma as $m) {
		$fn = $m[0]['fn'];
		if (substr($fn, 0, 2) == '$.' && $ma[substr($fn, 2)])
			;//continue;
		$desc = $m[0]['desc'];
		$params = $m[0]['params'];
		$ret = $m[0]['ret'];
		$overcount = count($m);
		echo "/**\n";
		if ($desc)
			echo " * ".formatText($desc)."\n";
		if ($overcount > 1) {
			echo " * <br>\n";
			echo " * <br><b>Alternatives</b><br>\n";
			echo " * <br>\n";
			for ($i = 1; $i < $overcount; $i++) {
				echo " * <b>$fn</b>(";
				$oParams = $m[$i]['params'];
				$sep = "";
				foreach ($oParams as $p) {
					$type = $p['type'];
					$name = $p['name'];
					echo "$sep<b>$name</b>: $type";
					$sep = ", ";
				}
				echo ") : ".$m[$i]['ret']."<br>\n";
				echo " * <br>\n";
				echo " * ".$m[$i]['desc']."<br>\n * <br>\n";
			}
		}
		echo " * @id jQuery.$fn\n";
		if (substr($fn, 0, 2) == '$.')
			echo " * @alias $fn\n";
		else
			echo " * @alias jQuery.prototype.$fn\n";
		foreach ($params as $p) {
			$type = $p['type'];
			$name = $p['name'];
			echo " * @param \{$type} $name\n";
		}
		if ($ret)
			echo " * @return \{$ret}\n";
		echo " */\n\n";
}

function formatText($text) {
	return $text;
	//return preg_replace('/\n\n/g', '<br><br>', $text);
}
?>