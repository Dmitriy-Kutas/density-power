<?php
$phrases = dbQuery("SELECT `phrase_id` as 'phraseId', `phrase_text` as 'phraseText' FROM `bot_phrases` WHERE 1 ORDER BY `phrase_text` ASC");
?>
<div class="container-fluid" ng-controller="PhrasesController" ng-init='phrases = <?php echo json_encode($phrases); ?>;'>
	<div class="row">
		<div class="col-md-12">
			<p class="lead">Use this page to manage included phrases. Included Phrases will be counted from articles just like a regular word.</p>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			&nbsp;
		</div>
	</div>
	<div class="row margin-15">
		<div class="col-md-12">
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPhrase" ><span class="glyphicon glyphicon-plus" aria-hide="true"></span> Add New Phrase</button>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-8 col-md-9">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>Included Phrase</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="phrase in phrases">
						<td class="text">{{phrase.phraseText}}</td>
						<td class="actions">
							<button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#removePhrase" ng-click="deleteForm.phrase = phrase;"><span class="glyphicon glyphicon-trash"></span> Remove</button>
						</td>
					</tr>
					<tr ng-show="!phrases.length">
						<td colspan="2">There are no included phrases yet.</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="modal fade" id="addPhrase">
		<div class="modal-dialog">
			<div class="modal-content">
				<form ng-submit="addPhrase()">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Add Included Phrase</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="phrase-text" class="control-label">Included Phrase:</label>
							<input type="text" required="true" class="form-control" name="phrase-text" ng-model="createForm.phraseText" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary" data-loading-text="Adding...">Add Included Phrase</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="modal fade" id="removePhrase">
		<div class="modal-dialog">
			<div class="modal-content">
				<form ng-submit="removePhrase()">
					<input type="hidden" ng-value="deleteForm.phrase.phraseId" name="phrase-id" />
					<div class="modal-header">
						<h4 class="modal-title">Remove Included Phrase</h4>
					</div>
					<div class="modal-body">
						<p>Are you sure you want to delete '{{deleteForm.phrase.phraseText}}' from the included phrases?</p>
						<p class="bg-danger add-padding">This action cannot be reverted.</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-danger" data-loading-text="Removing...">Remove Included Phrase</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>