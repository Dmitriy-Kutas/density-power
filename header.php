<?php
?>
<!DOCTYPE html>
<html lang="en" ng-app="theBot">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>CATCHER 22</title>
		<script type="text/javascript" src="/js/jquery-2.1.4.min.js"></script>
		<script type="text/javascript" src="/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="/js/angular.min.js"></script>
		<script type="text/javascript" src="/js/ng-google-chart.min.js"></script>
		<link href="/css/bootstrap.min.css" rel="stylesheet">
		<?php 
		if ($page == "articles" || $page == "" || $page == "density-power")
		{
		?>
		<link href="/css/bootstrap-datepicker.min.css" rel="stylesheet" />
		<script type="text/javascript" src="/js/bootstrap-datepicker.min.js"></script>
		<?php
		}
		?>
		<link href="/css/three-quarters.css" rel="stylesheet" />
		<link href="/css/style.css" rel="stylesheet" />
		<script type="text/javascript" src="/js/thebot.js"></script>
	</head>
<body>
	<nav class="navbar navbar-default navbar-inverse navbar-static-top">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span> 
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/projects"><img src="/logo.png" /></a>
			</div>
			<div class="collapse navbar-collapse" id="navbar-collapse">
				<ul class="nav navbar-nav">
					<?php if (!empty($_SESSION["selected-project"])) { ?>
					<li class="<?php if ($page == "") echo "active"; ?>"><a href="/" >Reports</a></li>
					<?php } ?>
					<li class="<?php if ($page == "projects") echo "active"; ?>"><a href="/projects" >Projects</a></li>
					<li class="<?php if ($page == "articles") echo "active"; ?>"><a href="/articles" >Articles</a></li>
					<li class="<?php if ($page == "phrases") echo "active"; ?>"><a href="/phrases">Phrases</a></li>
					<li class="<?php if ($page == "excluded-words") echo "active"; ?>"><a href="/excluded-words">Excluded Words</a></li>
					<li class="<?php if ($page == "density-power") echo "active"; ?>"><a href="/density-power">Density Power</a></li>
				</ul>
			</div>
		</div>
	</nav>
