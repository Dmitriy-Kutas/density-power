<?php
if (empty($_SESSION["selected-project"]))
{
	?>
<script type="text/javascript">
	window.location = "<?php echo HOMEURL; ?>/projects?warning=1";
</script>
	<?php
	exit();
}

$selectedProjectId = $_SESSION["selected-project"];
$selectedProject = dbQuery("SELECT `project_id` as 'projectId', `project_name` as 'projectName' FROM `bot_projects` WHERE `project_id` = '$selectedProjectId'", true);
$articles = dbQuery("SELECT `article_id` as 'articleId', `project_id` as 'projectId', UNIX_TIMESTAMP(`article_time`)*1000 as 'articleTime', `article_url` as 'articleUrl', `article_text` as 'articleText' FROM `bot_articles` WHERE `project_id` = '$selectedProjectId' ORDER BY `article_time` ASC, `article_id` ASC");
?>
<script type="text/javascript">
	var _articles = <?php echo json_encode($articles); ?>; 
	var _selectedProject = <?php echo json_encode($selectedProject); ?>;
</script>
<div class="container-fluid" ng-controller="ArticlesController" ng-init='initArticles(window._articles);'>
	<div class="row">
		<div class="col-md-12">
			<p class="lead">Below is the listing of all articles from <strong>{{selectedProject.projectName}}</strong>. Use this page to insert a new article, edit or remove articles.</p>
			<p>For each new article, words will be counted. If you edit an article, words will be re-counted. If you remove an article, counted words will be also removed.</p>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			&nbsp;
		</div>
	</div>
	<div class="row margin-15">
		<div class="col-md-12">
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editArticle" ng-click="openEditArticle();"><span class="glyphicon glyphicon-plus" aria-hide="true"></span> Add Article</button>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th colspan="2">Article</th>
						<th class="text-center">URL</th>
						<th class="text-center">Date</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="article in articles">
						<td class="article-index text-center align-middle">{{$index+1}}</td>
						<td class="article-text col-sm-7" ng-bind-html="excerpt(article.articleText)"></td>
						<td class="col-sm-1 align-middle text-center" ng-if="article.articleUrl.length > 0"><a href="{{article.articleUrl}}" data-toggle="tooltip" data-original-title="{{article.articleUrl}}" data-placement="bottom" target="_blank" >link</a></td>
						<td class="col-sm-1 align-middle text-center" ng-if="article.articleUrl.length == 0">-- no link --</td>
						<td class="col-sm-1 align-middle text-center">{{timestampToDate(article.articleTime)}}</td>
						<td class="col-sm-2 col-xs-4 align-middle">
							<button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editArticle" ng-click="openEditArticle(article);"><span class="glyphicon glyphicon-pencil"></span> Edit</button>
							<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#removeArticle" ng-click="deleteForm.articleId = article.articleId"><span class="glyphicon glyphicon-trash"></span> Remove</button>
						</td>
					</tr>
					<tr ng-show="!articles.length">
						<td colspan="4">There are no articles yet.</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="modal fade" id="editArticle">
		<div class="modal-dialog">
			<div class="modal-content">
				<form ng-submit="editArticle()">
					<input type="hidden" name="edit-type" ng-value="editForm.type" />
					<input type="hidden" name="project-id" ng-value="selectedProject.projectId" />
					<input type="hidden" name="article-id" ng-value="editForm.article.articleId" />
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Add New Article</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="article-url" class="control-label">Article URL:</label>
							<input type="text" class="form-control" name="article-url" ng-model="editForm.article.articleUrl" placeholder="http://" />
						</div>
						<div class="form-group">
							<label for="article-time" class="control-label">Article Publish Date <small>(optional)</small>:</label>
							<div class="input-group date">
							    <input type="text" data-provide="datepicker" data-date-format="dd/mm/yyyy" data-date-autoclose="true" data-date-today-btn="linked" data-date-orientation="top auto" class="form-control datepicker" name="article-time" ng-model="editForm.article.articleTime" placeholder="<?php echo date("d/m/Y"); ?>" />
							    <div class="input-group-addon"><span class="glyphicon glyphicon-th" aria-hidden="true"></span></div>
							</div>
						</div>
						<div class="form-group">
							<label for="article-text" class="control-label">Article:</label>
							<textarea required="true" class="form-control" name="article-text" ng-model="editForm.article.articleText"></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary" data-loading-text="Adding...">Add Article</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="modal fade" id="removeArticle">
		<div class="modal-dialog">
			<div class="modal-content">
				<form ng-submit="removeArticle()">
					<input type="hidden" ng-value="selectedProject.projectId" name="project-id" />
					<input type="hidden" ng-value="deleteForm.articleId" name="article-id" />
					<div class="modal-header">
						<h4 class="modal-title">Remove Article</h4>
					</div>
					<div class="modal-body">
						<p>Are you sure you want to delete this article and all its counted words?</p>
						<p class="bg-danger add-padding">This action cannot be reverted.</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-danger" data-loading-text="Removing...">Remove</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(".modal").scroll(function(){
		$("input.datepicker").datepicker("place");
	});

	$(function(){
		$('[data-toggle="tooltip"]').tooltip();
	});
</script>