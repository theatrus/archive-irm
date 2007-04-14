<html>
<head>
<title><? echo $this->title ?></title>
</head>

<body id="main" onload="document.login.name.focus()">
<link href="<? print $this->stylesheet ?>" rel="stylesheet" type="text/css">
<div class="nav"><? print $this->content_nav ?></div>
<div id="login"><? print $this->content_menu ?></div>
<div id="centercontent"><? print $this->content_main ?></div>

</body>
</html>
