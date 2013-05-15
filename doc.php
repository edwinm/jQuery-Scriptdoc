<?php
if ($_GET['ver'])
	$ver = $_GET['ver'];
else {
	echo "Need an version";
	exit;
}
if ($_GET['file'])
	$file = $_GET['file'];
else {
	echo "Need an filename";
	exit;
}

exec('./rmdocs.sh');

function pageHeader($title) {
	global $ver;
	$title_lower = strtolower($title);
$headerTpl = <<< _EOT
<html>
<head>
<title>$title - jQuery</title>
<!-- jQuery $ver -->
<meta name="generator" content="Code by Edwin Martin edwin@bitstorm.org">
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript">
$(function() {
	$("h4").click(function() {
		$(this).next().slideToggle();
		$(this).toggleClass("selected");
	});
});
</script>
<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<h1><a href="http://jquery.com/" target="_blank">jQuery website</a><img src="hat.png" width="24" height="16"> jQuery documentation</h1>
<div id="menu" class="$title_lower">
<a href="contents.html" class="contents">contents</a>
<a href="index.html" class="index">index</a>
</div>

<div class="body">
<h2>$title</h2>
_EOT;

$footer = <<< _EOT
</div>
</body>
</html>
_EOT;

	return $headerTpl;
}

$dom = domxml_open_file($file);
if (!$dom) {
	echo "Couln't read XML-file.";
	die;
}

// Test meerdere examples: $.index

$methods = $dom->get_elements_by_tagname("method");

$functions = array();
$files = array();
$index = array();
$oldFn = "";
$oldCat = "";
foreach ($methods as $m) {
	$fn = $m->get_attribute('name');
	$ret = $m->get_attribute('type');
	$cat = $m->get_attribute('cat');

	$desc = "";
	$child = $m->first_child();
	if (true) {
		while ($child) {
			if ($child->type == 1 && $child->tagname() == "desc") {
				$desc = $child->get_content();
				break;
			}
			$child = $child->next_sibling();
		}
	}
	$paramObjs = $m->get_elements_by_tagname('params');
	$params = array();
	foreach($paramObjs as $paramObj) {
		$descObj = $paramObj->get_elements_by_tagname('desc');
		if (count($descObj) >= 1)
			$parmDesc = $descObj[0]->get_content();
		else
			$parmDesc = "";
		$params[] = array(
			"type" => $paramObj->get_attribute('type'),
			"name" => $paramObj->get_attribute('name'),
			"desc" => $parmDesc
		);
	}

	if ($oldCat != $cat) {
		$kop = "<h2>$cat</h2>\n";
		$oldCat = $cat;
	} else {
		$kop = "";
	}

	if ($oldFn != $fn) {
		$id = " id='$fn'";
		$oldFn = $fn;
	} else {
		$id = "";
	}

	$out = "";
	$func = "";
	$func .= "<b>$fn</b>(";
	$sep = "";
	foreach ($params as $p) {
		$type = $p['type'];
		$name = $p['name'];
		$func .= "$sep$type <b>$name</b>";
		$sep = ", ";
	}
	$func .= ") : $ret";

	$out .= "<h3 class='func'$id>$func</h3>\n";

	if ($params) {
		$out .= "<table cellpadding='0' cellspacing='0' border='0'>\n";
		foreach ($params as $p) {
			$type = $p['type'];
			$name = $p['name'];
			$parmDesc = $p['desc'];
			$out .= "<tr><th width='15%'>$name</th><td width='15%'>$type</td><td>$parmDesc</td></tr>\n";
		}
		$out .= "</table>\n";
	}

	$out .= paragraph($desc);

	$examples = $m->get_elements_by_tagname('examples');
	if (count($examples) >= 1) {
		if (count($examples) == 1)
			$out .= "<h4>Example</h4>\n";
		else
			$out .= "<h4>Examples</h4>\n";
		$out .= "<div class='examples'>\n";

		foreach($examples as $example) {
			$out .= "<div class='example'>\n";
			//$example = $examples[0];
			$exDescN = $example->get_elements_by_tagname('desc');
			if (count($exDescN) >= 1) {
				$exDesc = $exDescN[0]->get_content();
				$out .= "<p>$exDesc</p>\n";
			}
			$exDescN = $example->get_elements_by_tagname('before');
			if (count($exDescN) >= 1) {
				$exDesc = htmlentities($exDescN[0]->get_content());
				$out .= "<h5>Before</h5>\n<pre>$exDesc</pre>\n";
			}
			$exDescN = $example->get_elements_by_tagname('code');
			if (count($exDescN) >= 1) {
				$exDesc = htmlentities($exDescN[0]->get_content());
				$out .= "<h5>Code</h5>\n<pre>$exDesc</pre>\n";
			}
			$exDescN = $example->get_elements_by_tagname('result');
			if (count($exDescN) >= 1) {
				$exDesc = htmlentities($exDescN[0]->get_content());
				$out .= "<h5>Result</h5>\n<pre>$exDesc</pre>\n";
			}
			$out .= "</div>\n";
		}

		$out .= "</div>\n";
	}

	$filename = str_replace('/', '_', strtolower($cat)).".html";
	$href = $filename."#".urlencode($fn);

	$method = array(
		"href" => $href,
		"fn" => $fn,
		"func" => $func,
		"html" => $out
	);

	if ($id)
		$functions[] = $method;

	list($main, $sub) = split("/", $cat);

	$files[$main][$sub]["file"] = $filename;
	$files[$main][$sub]["main"] = $main;
	$files[$main][$sub]["sub"] = $sub;
	$files[$main][$sub]["name"] = $cat;
	$files[$main][$sub]["methods"][] = $method;
}

usort($functions, fnCmp);
//echo "<pre>";
//print_r($files);

$xmlf = fopen("out/index.xml", "w");
$xmlHeader = <<< _EOT
<?xml version="1.0" encoding="UTF-8"?>
<?NLS TYPE="org.eclipse.help.toc"?>

<toc label="jQuery Reference" link_to="toc.xml#reference">
	<topic label="jQuery Index"  href="libraries_docs/jquery/docs/index.html">

_EOT;
fwrite($xmlf, $xmlHeader);

$listf = fopen("out/contents.html", "w");
fwrite($listf, pageHeader("Contents"));
fwrite($listf, "<ul>\n");
$dept = false;
$first = true;
foreach ($files as $mainfile) {
	foreach ($mainfile as $subfile) {
		$filename = $subfile["file"];
		$name = $subfile["name"];
		$main = $subfile["main"];
		$sub = $subfile["sub"];

		// This only works when each main cat. has an empty sub cat.
		if ($sub) {
			if (!$dept) { // Start of subcats
				fwrite($listf, "<ul>\n");
				$dept = true;
			}
			$title = $sub;
			$title2 = "$main / $sub";
			fwrite($xmlf, "\t\t\t<topic label=\"$title\"  href=\"libraries_docs/jquery/docs/$filename\"/>\n");
		} else {
			if ($dept) { // End of subcats
				fwrite($listf, "</ul>\n");
				$dept = false;
			}
			if (!$first)
				fwrite($xmlf, "\t\t</topic>\n");
			$title = $main;
			$title2 = $main;
			fwrite($xmlf, "\t\t<topic label=\"$title\"  href=\"libraries_docs/jquery/docs/$filename\">\n");
		}


		fwrite($listf, "<li><a href='$filename'>$title</a></li>");

		$catf = fopen("out/$filename", "w");
		fwrite($catf, pageHeader($title2));
		foreach ($subfile["methods"] as $method) {
			fwrite($catf, $method["html"]);
		}
		fwrite($catf, $footer);
		fclose($catf);
		$first = false;
	}
}
fwrite($listf, "</ul>\n");
fwrite($listf, $footer);
fclose($listf);

$list2f = fopen("out/index.html", "w");
fwrite($list2f, pageHeader("Index"));
foreach ($functions as $fn) {
	$href = $fn["href"];
	$name = $fn["fn"];
	fwrite($list2f, "<a class='index' href='$href'>$name</a><br>\n");
}
fwrite($list2f, $footer);
fclose($list2f);

fwrite($xmlf, "\t\t</topic>\n\t</topic>\n</toc>");
fclose($xmlf);

// Now create zip-file

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"jquery-$ver-docs.zip\";" );

passthru('./makezipfile.sh');


function fnCmp($a, $b) {
   if ($a['fn'] == $b['fn']) {
       return 0;
   }
   return ($a['fn'] < $b['fn']) ? -1 : 1;
}

function paragraph($s) {
	return "<p>".ereg_replace("\n", " ", ereg_replace("\n\n", "</p><p>", $s))."</p>";
}

?>

