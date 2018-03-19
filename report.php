<?php
if (!is_numeric($_SESSION["selected-project"]))
{
	die("error");
}

$projectId = $_SESSION["selected-project"];
$p = dbQuery("SELECT `project_name` as 'projectName', `project_id` as 'projectId' FROM `bot_projects` WHERE `project_id` = '$projectId'", true);
if ($p == null)
{
?>
<script type="text/javascript">
	window.location = "<?php echo HOMEURL; ?>/projects?warning=1";
</script>
<?php
	exit();
}
$articles = dbQuery("SELECT `article_id` as 'articleId', UNIX_TIMESTAMP(`article_time`) as 'article_time' FROM `bot_articles` WHERE `project_id` = '$projectId' ORDER BY `article_time` ASC");
$wordsTotal = dbQuery("SELECT SUM(`w`.`word_count`) as 'total' FROM `bot_articles` `a`, `bot_words` `w` WHERE `w`.`article_id` = `a`.`article_id` AND `a`.`project_id` = '$projectId'", true);
// $topWords = dbQuery("SELECT SUM(`w`.`word_count`) as 'total', `w`.`word_text`, `p`.`phrase_text` FROM `bot_words` `w` LEFT JOIN `bot_phrases` `p` ON (`p`.`phrase_id` = `w`.`phrase_id`), `bot_articles` `a` WHERE `a`.`project_id` = '$projectId' AND `a`.`article_id` = `w`.`article_id` GROUP BY `w`.`word_text`, `w`.`phrase_id` ORDER BY `total` DESC");
$excludedWords = dbQuery("SELECT `excluded_word_text` as 'text' FROM `bot_excluded_words` WHERE 1 ORDER BY `excluded_word_text` ASC");
$phrases = dbQuery("SELECT `phrase_text` as 'text' FROM `bot_phrases` WHERE 1 ORDER BY `phrase_text` ASC");
$config = dbQuery("SELECT `config_key`, `config_value` FROM `bot_config` WHERE `project_id` = '$projectId'");
$configByKey = array();
foreach($config as $c)
{
	if (is_numeric($c["config_value"]))
	{
		$configByKey[$c["config_key"]] = 0+$c["config_value"];
	}
	else if (strpos($c["config_value"], "{") !== false)
	{
		$configByKey[$c["config_key"]] = json_decode($c["config_value"], true);
	}
	else
	{
		$configByKey[$c["config_key"]] = $c["config_value"];
	}
}

$lines = explode(PHP_EOL, file_get_contents(ABSPATH."/config.txt"));
foreach($lines as $line)
{
	if ($line == "")
	{
		continue;
	}

	if (strpos($line, "#") === 0)
	{
		continue;
	}

	$val = explode("=", $line);
	switch($val[0])
	{
		case "article_per_day":
		case "article_length":
			$configByKey[$val[0]] = $val[1];
			break;
	}
}

?>
<script type="text/javascript">
	var selectedProject = <?php echo json_encode($p); ?>;
	var articles = <?php echo json_encode($articles); ?>;
	var wordsTotal = <?php echo json_encode($wordsTotal); ?>;
	<?php // var topWords = <?php echo json_encode($topWords); ?>
	var excludedWords = <?php echo json_encode($excludedWords); ?>;
	var includedPhrases = <?php echo json_encode($phrases); ?>;
	var config = <?php echo json_encode($configByKey); ?>;
</script>
<div class="container-fluid" ng-controller="ReportController" ng-init='init()'>
	<div class="row">
		<div class="col-md-12">
			<p class="lead">Viewing report for project {{selectedProject.projectName}}</p>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<p class="bg-info add-padding">
				<strong>Number of actual articles in date range: </strong>{{articles.length}}<br/>
				<strong>Number of bot articles: </strong>{{chartData.length*reportData.articlePerDay}}<br/>
				<strong>Number of counted words and included phrases: </strong>{{wordsTotal()}}
			</p>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<p>Start by selecting a date range:</p>
			<div class="form-inline">
				<div class="form-group date input-daterange">
				    <input type="text" class="form-control from-date input-sm" ng-model="dateRange.initDateString" />
				    <span class="add-on" style="height: 30px; ">to</span>
				    <input type="text" class="form-control to-date input-sm" ng-model="dateRange.endDateString"/>
				</div>
				<div class="form-group">
					<button type="button" class="btn btn-primary btn-sm" ng-click="submitDateRange()"><span class="glyphicon glyphicon-calendar"></span> Submit Date</button>
				</div>
			</div>
		</div>
	</div>
	<div class="row" ng-show="!isEmptyChart()">
		<div class="col-md-12" id="word-count-chart">
			<div google-chart chart="chart" style="{{chart.cssStyle}}" ></div>
			<div class="loading-div" ng-show="loading > 0">
				<div class="three-quarters-loader"></div> Loading data...
			</div>
		</div>
	</div>
	<div class="row" ng-show="isEmptyChart()">
		<div class="col-md-12">
			<div class="no-chart bg-warning" >
				<p class="text-center">No data to show chart yet.</p>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 grid-table table-striped top-words">
			<div class="row table-header">
				<div class="col-sm-3 col-xs-6">Word or Phrase</div>
				<div class="col-sm-3 col-xs-6 text-center">Count</div>
				<div class="col-sm-3 hidden-xs ">Word or Phrase</div>
				<div class="col-sm-3 hidden-xs text-center">Count</div>
			</div>
			<div class="row" ng-show="!topWords.length">
				<div class="col-sm-12">
					<p class="vertical-padding">No words counted yet.</p>
				</div>
			</div>
			<div class="row table-body" ng-repeat="word in topWords | evenArray">
				<div class="col-sm-3 col-xs-6">
					<label>
						<input type="checkbox" ng-model="word.active" ng-change="changeActiveWords(word);"/>
						{{word.text}}
					</label>
				</div>
				<div class="col-sm-3 col-xs-6 text-center">{{word.total}}</div>
				<div class="col-sm-3 col-xs-6">
					<label>
						<input type="checkbox" ng-model="topWords[$index*2+1].active" ng-change="changeActiveWords(topWords[$index*2+1]);"/>
						{{topWords[$index*2+1].text}}
					</label>
				</div>
				<div class="col-sm-3 col-xs-6 text-center">{{topWords[$index*2+1].total}}</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" >
	$(".input-daterange").datepicker({
		autoclose: true,
		format : "dd/mm/yyyy",
		todayBtn : "linked",
		endDate : 'now',
		orientation : "top",
	}).on("hide", function(e){
		if (e.date)
		{
			if ($(e.target).is(".from-date"))
			{
				$(".input-daterange .to-date").focus().data("datepicker").setStartDate(e.date);
			}
			else if ($(e.target).is(".to-date"))
			{
				$(".input-daterange .from-date").data("datepicker").setEndDate(e.date);
			}
		}
	});
</script>