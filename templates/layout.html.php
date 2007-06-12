<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><? echo $this->title ?></title>
<link href="<? print $this->stylesheet ?>" rel="stylesheet" type="text/css" />
</head>
<body id="main" onload="document.login.name.focus()">
<div class="nav"><? print $this->content_nav ?></div>
<div id="login"><? print $this->content_menu ?></div>
<div id="centerlogincontent"><? print $this->content_main ?></div>

</body>
</html>
