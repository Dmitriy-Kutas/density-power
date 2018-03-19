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
$articles = dbQuery("SELECT `article_id` as 'articleId', DATE(`article_time`) as 'article_time', `article_text` FROM `bot_articles` WHERE `project_id` = '$projectId' ORDER BY `article_time` ASC");
$wordsTotal = dbQuery("SELECT SUM(`w`.`word_count`) as 'total' FROM `bot_articles` `a`, `bot_words` `w` WHERE `w`.`article_id` = `a`.`article_id` AND `a`.`project_id` = '$projectId'", true);
$words = getWords($projectId);

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
	var words = <?php echo json_encode($words); ?>;

	var excludedWords = <?php echo json_encode($excludedWords); ?>;
	var includedPhrases = <?php echo json_encode($phrases); ?>;
	var config = <?php echo json_encode($configByKey); ?>;
</script>
<div class="container-fluid" ng-controller="DensityController" ng-init='init()'>
	<div class="row">
		<div class="col-md-12">
			<p class="lead">Viewing density for project - <strong>{{selectedProject.projectName}}</strong></p>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="form-inline">
                <div class="form-group date input-daterange">
				    <span>Beginning date &nbsp;&nbsp;&nbsp;: </span>
				    <input type="text" class="form-control from-date input-sm" ng-model="reportData.initDate"/>
				</div>
			</div>
		</div>
	</div><br>
	<div class="row">
		<div class="col-md-12">
			<div class="form-inline">
				<div class="form-group">
                    <span>Count of articles&nbsp;&nbsp;:</span>
				    <input type="text" class="form-control input-sm" ng-model="countOfArticle" />
				</div>
				<div class="form-group">
					<button type="button" class="btn btn-primary btn-sm" ng-click="submitAnalysis()">
						<span class="glyphicon glyphicon-calendar"></span> Analysis
					</button>
				</div>
			</div>
		</div>
	</div><br>
	<div class="row">
		<div class="col-md-12">
			<p class="bg-info add-padding">
				<strong>Total count of words: </strong>{{wordsTotal}}<br/>
				<strong>Average count of words per article: </strong>{{average}}
			</p>
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
						<input type="radio" name="keyword" ng-model="isChecked" ng-value="word.text" ng-click="changeActiveWord(word)"/>
						{{word.text}}
					</label>
				</div>
				<div class="col-sm-3 col-xs-6 text-center">{{word.total}}</div>
				<div class="col-sm-3 col-xs-6">
					<label>
						<input type="radio" name="keyword" ng-model="isChecked" ng-value="topWords[$index*2+1].text" ng-click="changeActiveWord(topWords[$index*2+1])"/>
						{{topWords[$index*2+1].text}}
					</label>
				</div>
				<div class="col-sm-3 col-xs-6 text-center">{{topWords[$index*2+1].total}}</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="warnNotExistArticles">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Error</h4>
			</div>
			<div class="modal-body">
				<p>These is no any article for analysis! <br/> Please input the beginning date, again.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
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
	});
</script>