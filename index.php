<html>
<head>
<title>jQuery</title>
</head>

<body>

<h1>jQuery doc</h1>

<p><a href="http://jquery.com/">jquery.com</a></p>
<p><a href="http://jquery.com/api/">jquery API</a></p>

<form action="post.php" method="post" enctype="multipart/form-data">

jQuery version:
<input type="text" name="version" value="<?php echo $_GET['version'] ?>">
<hr>
XML-documentation file:
<input type="file" name="file">
<input type="submit" name="action" value="upload">
<hr>
<input type="submit" name="action" value="generate scriptdoc">
<hr>
<input type="submit" name="action" value="generate html doc">
<br>
<a href="out/">view docs</a>

</form>

</body>
</html>

