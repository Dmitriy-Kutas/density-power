<?php
if (!empty($_POST["select-project"]))
{
	$_SESSION["selected-project"] = $_POST["select-project"];
	?>
<script type="text/javascript">
	window.location = "<?php echo HOMEURL; ?>";
</script>
	<?php
	exit();
}

$projects = dbQuery("SELECT `project_name` as 'projectName', `project_id` as 'projectId' FROM `bot_projects` WHERE 1 ORDER BY `project_name` ASC");
?>
<div class="container-fluid" ng-controller="ProjectsController as projectList" ng-init='projectList.projects = <?php echo json_encode($projects); ?>'>
	<div class="row">
		<div class="col-md-12">
			<p class="lead">Use this page to select the active project, create new projects or delete one.</p>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			&nbsp;
		</div>
	</div>
	<div class="row margin-15">
		<div class="col-md-12">
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editProject" data-option="create" ng-click="projectList.openEditProject();"><span class="glyphicon glyphicon-plus" aria-hide="true"></span> Create Project</button>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-8 col-md-9">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>Project Name</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="project in projectList.projects" data-project-id="{{project.projectId}}">
						<td class="project-name">{{project.projectName}}</td>
						<td class="actions">
							<button type="button" class="btn btn-sm btn-success" ng-click="projectList.selectProject(project);"><span class="glyphicon glyphicon-hand-up"></span> Select</button>
							<button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editProject" data-option="edit" ng-click="projectList.openEditProject(project);"><span class="glyphicon glyphicon-pencil"></span> Edit</button>
							<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteProject" ng-click="projectList.openDeleteProject(project);"><span class="glyphicon glyphicon-trash"></span> Delete</button>
						</td>
					</tr>
					<tr ng-show="!projectList.projects.length">
						<td colspan="2">There are no projects yet.</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="modal fade" id="editProject">
		<div class="modal-dialog">
			<div class="modal-content">
				<form ng-submit="projectList.submitEditProject()">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Create Project</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="project-name" class="control-label">Project Name:</label>
							<input type="text" required="true" class="form-control" name="project-name" ng-model="projectList.editForm.projectName" />
							<input type="hidden" name="edit-type" ng-value="projectList.editForm.type" />
							<input type="hidden" name="project-id" ng-value="projectList.editForm.projectId" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary" data-loading-text="Creating...">Create Project</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="modal fade" id="deleteProject">
		<div class="modal-dialog">
			<div class="modal-content">
				<form ng-submit="projectList.submitDeleteProject()">
					<input type="hidden" ng-value="projectList.deleteForm.projectId" name="project-id" />
					<div class="modal-header">
						<h4 class="modal-title">Delete Project</h4>
					</div>
					<div class="modal-body">
						<p>Are you sure you want to delete '{{projectList.deleteForm.projectName}}' and all its counted words?</p>
						<p class="bg-danger">This action cannot be reverted.</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-danger" data-loading-text="Deleting...">Delete Project</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<form style="display: none;" method="post" action="" id="selectProject">
		<input type="hidden" ng-value="projectList.selectForm.projectId" name="select-project" />
	</form>
</div>
<?php 
if (!empty($_GET["warning"]))
{
?>
<div class="modal fade" id="warnSelectProject">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">First Select a Project</h4>
			</div>
			<div class="modal-body">
				<p>In order to see the reports or add new articles, first you need to select one of the projects you previously added.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$("#warnSelectProject").modal("show");
});
</script>
<?php 
}
?>
<script type="text/javascript">
	$("#editProject").on("show.bs.modal", function(e){
		var $button = $(e.relatedTarget);
		var $modal = $(this);
		if ($button.data("option") == "create")
		{
			$modal.find(".modal-title").html("Create New Project");
			$modal.find(".modal-footer .btn-primary").html("Create Project");
			$modal.find("button[type=submit]").data("loading-text", "Creating...");
		}
		else
		{
			$modal.find(".modal-title").html("Edit Project");
			$modal.find(".modal-footer .btn-primary").html("Edit Project");
			$modal.find("button[type=submit]").data("loading-text", "Editing...");
		}
	});
</script>