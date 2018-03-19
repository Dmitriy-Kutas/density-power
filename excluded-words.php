<?php
$excludedWords = dbQuery("SELECT `excluded_word_id` as 'excludedWordId', `excluded_word_text` as 'excludedWordText' FROM `bot_excluded_words` WHERE 1 ORDER BY `excluded_word_text` ASC");
?>
<div class="container-fluid" ng-controller="ExcludedWordsController" ng-init='excludedWords = <?php echo json_encode($excludedWords); ?>;'>
	<div class="row">
		<div class="col-md-12">
			<p class="lead">Use this page to manage excluded words. Excluded words won't be counted on articles.</p>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			&nbsp;
		</div>
	</div>
	<div class="row margin-15">
		<div class="col-md-12">
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createExcludedWord" ><span class="glyphicon glyphicon-plus" aria-hide="true"></span> Add Excluded Word</button>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-8 col-md-9">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>Excluded Word</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="excludedWord in excludedWords" data-excluded-word-id="{{excludedWord.excludedWordId}}">
						<td class="text">{{excludedWord.excludedWordText}}</td>
						<td class="actions">
							<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteExcludedWord" ng-click="openDeleteExcludedWord(excludedWord);"><span class="glyphicon glyphicon-trash"></span> Remove</button>
						</td>
					</tr>
					<tr ng-show="!excludedWords.length">
						<td colspan="2">There are no excluded words yet.</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="modal fade" id="createExcludedWord">
		<div class="modal-dialog">
			<div class="modal-content">
				<form ng-submit="addExcludedWord()">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Add Excluded Word</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="excluded-word" class="control-label">Excluded Word Name:</label>
							<input type="text" required="true" class="form-control" name="excluded-word" ng-model="createForm.excludedWordText" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary" data-loading-text="Adding...">Add Excluded Word</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="modal fade" id="deleteExcludedWord">
		<div class="modal-dialog">
			<div class="modal-content">
				<form ng-submit="deleteExcludedWord()">
					<input type="hidden" ng-value="deleteForm.excludedWordId" name="excluded-word-id" />
					<div class="modal-header">
						<h4 class="modal-title">Remove Excluded Word</h4>
					</div>
					<div class="modal-body">
						<p>Are you sure you want to delete '{{deleteForm.excludedWordText}}' from the excluded words?</p>
						<!-- <p class="bg-warning add-padding">All articles that have already been parsed won't receive additional counts from this word.</p> -->
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-danger" data-loading-text="Removing...">Remove Excluded Word</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>