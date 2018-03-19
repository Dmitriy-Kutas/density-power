angular.module("theBot", [ 'googlechart'])
.controller('ProjectsController', function($scope){
	var projectList = this;
	projectList.projects = [];
	projectList.editForm = {
		"type" : 0,
		"projectName" : "",
		"projectId" : 0
	};
	projectList.deleteForm = {
		"projectName" : "",
		"projectId" : 0
	};
	projectList.selectForm = {
		"projectId" : 0	
	};
	
	projectList.selectProject = function(project)
	{
		projectList.selectForm.projectId = project.projectId;
		setTimeout(function(){
			$("#selectProject").submit();
		}, 1);
	};
	
	projectList.submitEditProject = function(){
		$("#editProject button[type=submit]").button("loading");
		$.post("/ajax/editProject.php", projectList.editForm, function(response){
			$("#editProject button[type=submit]").button("reset");
			if (response["error"])
			{
				alert(response["error"]);
			}
			else
			{
				if (projectList.editForm.type == 0)
				{
					projectList.insertProject({"projectName":projectList.editForm.projectName, "projectId" : response["project_id"]});
				}
				else
				{
					projectList.editProject({"projectName":projectList.editForm.projectName, "projectId" : projectList.editForm.projectId});
				}
				
				$scope.$apply();
				$('#editProject').modal('hide');
			}
		}, "json")
	};
	
	projectList.openEditProject = function(project)
	{
		if (project)
		{
			projectList.editForm.type = "1";
			projectList.editForm.projectName = project.projectName;
			projectList.editForm.projectId = project.projectId;
		}
		else
		{
			projectList.editForm.type = "0";
			projectList.editForm.projectName = "";
			projectList.editForm.projectId = 0;
		}
	};
	
	projectList.insertProject = function(project)
	{
		var inserted = false;
		for (var i = 0; i < projectList.projects.length; i++)
		{
			if (projectList.projects[i].projectName.localeCompare(project.projectName) >= 0)
			{
				projectList.projects.splice(i,0,project);
				inserted = true;
				break;
			}
		}
		
		if (!inserted)
		{
			projectList.projects.push(project);
		}
	};
	
	projectList.editProject = function(project)
	{
		projectList.deleteProject(project);
		projectList.insertProject(project);
	};
	
	projectList.deleteProject = function(project)
	{
		for (var i = 0; i < projectList.projects.length; i++)
		{
			if (projectList.projects[i].projectId == project.projectId)
			{
				projectList.projects.splice(i,1);
				break;
			}
		}
	};
	
	projectList.openDeleteProject = function(project)
	{
		projectList.deleteForm.projectName = project.projectName;
		projectList.deleteForm.projectId = project.projectId;
	};
	
	projectList.submitDeleteProject = function()
	{
		$("#deleteProject button[type=submit]").button('loading');
		$.post("/ajax/deleteProject.php", projectList.deleteForm, function(response){
			$("#deleteProject button[type=submit]").button('reset');
			if (response["error"])
			{
				alert(response["error"]);
			}
			else
			{
				projectList.deleteProject(projectList.deleteForm);
			}
			
			$scope.$apply();
			$('#deleteProject').modal('hide');
		}, "json");
	};
}).controller('ReportController', ['$scope', '$timeout', '$http', function($scope, $timeout, $http){
	$scope.selectedProject = {};
	$scope.articles = [];
	// $scope.wordsTotal = {};
	$scope.topWords = [];
	$scope.dateRange = {};
	$scope.chartData = {};
	$scope.loading = 0;
	$scope.reportData = {};
	$scope.excludedWords = {};
	
	$scope.chart = 
	{
		"type": "AreaChart",
		"cssStyle": "height:400px; width:100%;",
		"data": {
			"cols": [],
	        "rows": []
		},
		"options": {
			"title": "Words Trends",
			"fill": 20,
			"displayExactValues": true,
			"vAxis": {
				"title": "Word Popularity (%)",
				"gridlines": {
					"count": -1
				},
				"minValue" : 0,
				"format" : "percent"
			},
			"hAxis": {
				"title": "Number of Articles",
				// "format" : "dd/MM/y",
				"gridlines" : {
					"count" : -1
				}
			},
			"fontName" : '"Helvetica Neue",Helvetica,Arial,sans-serif',
			/*
			"explorer" : {
				"actions" : ['dragToZoom', 'rightClickToReset']
			},*/
			"animation" : {
				"duration" : 500,
				"easing" : "out",
				"startup" : true
			},
			"interpolateNulls" : true
		},
		"formatters": {},
		"displayed": true
	};
	
	$scope.init = function()
	{
		$scope.selectedProject = selectedProject;
		$scope.articles = articles;
		// $scope.wordsTotal = wordsTotal["total"] || 0;
		$scope.topWords = [];
		
		$debug = $scope;
		
		if (window.config)
		{
			if (config["init_date"])
			{
				$scope.reportData.initDate = parseInt(config["init_date"], 10)*1000;
			}
			else
			{
				var initDate = new Date(Date.now() - 30*24*60*60*1000);
				if ($scope.articles.length > 0)
				{
					initDate = new Date(parseInt($scope.articles[0].article_time, 10)*1000 - 24*60*60*1000);
				}
				$scope.reportData.initDate = initDate.getTime();
			}
			
			if (config["end_date"])
			{
				$scope.reportData.endDate = parseInt(config["end_date"], 10)*1000;
			}
			else
			{
				var endDate = new Date();
				$scope.reportData.endDate = endDate.getTime();
			}
			
			$scope.reportData.articleLength = parseInt(config["article_length"] || 300, 10);
			$scope.reportData.articlePerDay = parseInt(config["article_per_day"] || 3, 10);
		}
		
		if (window.excludedWords)
		{
			for (var i = 0; i < excludedWords.length; i++)
			{
				$scope.excludedWords[excludedWords[i].text] = 1;
			}
		}
		
		if (window.includedPhrases)
		{
			$scope.includedPhrases = [];
			for (var i = 0; i < includedPhrases.length; i++)
			{
				$scope.includedPhrases.push(includedPhrases[i].text);
			}
		}
		
		$scope.dateRange.initDate = new Date($scope.reportData.initDate);
		$scope.dateRange.initDateString = $scope.dateRange.initDate.getDate()+"/"+($scope.dateRange.initDate.getMonth()+1)+"/"+$scope.dateRange.initDate.getFullYear();
		$scope.dateRange.endDate = new Date($scope.reportData.endDate);
		$scope.dateRange.endDateString = $scope.dateRange.endDate.getDate()+"/"+($scope.dateRange.endDate.getMonth()+1)+"/"+$scope.dateRange.endDate.getFullYear();
		
		$(".input-daterange .from-date").data("datepicker").setDate($scope.dateRange.initDateString);
		$(".input-daterange .to-date").data("datepicker").setDate($scope.dateRange.endDateString);
		
		$scope.submitDateRange()
	};
	
	$scope.isEmptyChart = function()
	{
		return Object.keys($scope.chartData).length == 0;
	};
	
	$scope.wordsTotal = function()
	{
		var count = 0;
		for (var i = 0; i < $scope.topWords.length; i++)
		{
			count += $scope.topWords[i].total;
		}
		
		return count;
	};
	
	$scope.submitDateRange = function()
	{
		var from = $scope.dateRange.initDateString.split("/");
		$scope.dateRange.initDate = new Date(from[2], parseInt(from[1], 10)-1, from[0]);
		
		var to = $scope.dateRange.endDateString.split("/");
		$scope.dateRange.endDate = new Date(to[2], parseInt(to[1], 10)-1, to[0]);
		
		/*
		var activeWords = [];
		for (var i = 0; i < topWords.length; i++)
		{
			if (topWords[i].active)
			{
				if (topWords[i]["phrase_text"])
				{
					activeWords.push({"phrase_text" : topWords[i]["phrase_text"]});
				}
				else
				{
					activeWords.push({"word_text" : topWords[i]["word_text"]});
				}
			}
		}
		*/
		
		var post = {
			"timezone" : $scope.dateRange.initDate.getTimezoneOffset(),
			"initDate" : $scope.dateRange.initDate.getTime()/1000,
			"endDate" : $scope.dateRange.endDate.getTime()/1000, 
			"projectId" : $scope.selectedProject.projectId
			// "activeWords" : JSON.stringify(activeWords)
		};
		$scope.loading++;
		$.post("/ajax/getDataFromWordsList.php", post, function(response){
			$scope.loading--;
			if (response["error"])
			{
				alert(response["error"]);
				return;
			}
			$scope.articles = response["data"];
			$scope.countActiveWords();
			$scope.drawActiveWords();
			//$scope.chartData = response["data"];
			//$scope.drawActiveWords();
			$scope.$apply();
		}, "json");
		
		$scope.reportData.initDate = $scope.dateRange.initDate.getTime();
		$scope.reportData.endDate = $scope.dateRange.endDate.getTime();
	};
	
	$scope.getDataFromWord = function(text, isPhrase, callback)
	{
		$scope.loading++;
		$.post("/ajax/getDataFromWord.php", {
			"text" : text,
			"isPhrase" : isPhrase,
			"projectId" : $scope.selectedProject.projectId,
			"timezone" : $scope.dateRange.initDate.getTimezoneOffset(),
			"initDate" : $scope.dateRange.initDate.getTime()/1000,
			"endDate" : $scope.dateRange.endDate.getTime()/1000
		}, 
		function(response) {
			$scope.loading--;
			if (response["error"])
			{
				alert(response["error"]);
				return;
			}
			else
			{
				$scope.chartData[text] = response["data"];
				if (typeof (callback) == "function")
				{
					callback();
				}
			}
		}, "json");
	};
	
	$scope.changeActiveWords = function(word)
	{
		var text = word.text;
		
		if (word.active && !$scope.reportData.activeWords[text])
		{
			$scope.reportData.activeWords[text] = 1;
		}
		else if (!word.active && $scope.reportData.activeWords[text])
		{
			delete $scope.reportData.activeWords[text];
		}
		
		if ($scope.saveActiveTimeout)
		{
			$timeout.cancel($scope.saveActiveTimeout);
		}
		
		$scope.saveActiveTimeout = $timeout(function(){
			$http.post("/ajax/saveActiveWords.php", $.param({
				"activeWords" : JSON.stringify($scope.reportData.activeWords),
				"projectId" : $scope.selectedProject.projectId
			}), {headers: {'Content-type' : 'application/x-www-form-urlencoded'}});
		}, 500);
		
		/*
		if (word.active && !$scope.chartData[text])
		{
			var isPhrase = word.phrase_text != null;
			$scope.getDataFromWord(text, isPhrase, function(){
				$scope.drawActiveWords();
				$scope.$apply();
			});
		}
		else
		{
			$scope.drawActiveWords();		
		}
		*/
		
		$scope.drawActiveWords();
	};
	
	$scope.resetChart = function()
	{
		$scope.chart.data.cols = [];
		$scope.chart.data.rows = [];
	};
	
	$scope.countActiveWords = function()
	{
		$scope.chartData = [];
		$scope.topWords = [];
		$scope.reportData.activeWords = {};
		var topWordsPosition = {};
		var count = 0;
		var pointIndex = 0;
		var point = {};
		var totalWordsPerDay = $scope.reportData.articleLength*$scope.reportData.articlePerDay;
		var wordRegEx = /\b[\w-'\u2019]+\b/gm;
		var computedArticle = "";
		for (var i = 0; i < $scope.articles.length; i++)
		{
			var text = $scope.articles[i]["article_text"];
			text.replace(wordRegEx, function(match)
			{
				computedArticle += match+" ";
				match = match.toLowerCase();
				match = match.replace("/['\u2019]s/g", "");
				if (point[match])
				{
					point[match][0] += 1/totalWordsPerDay;
					point[match][1]++;
				}
				else
				{
					point[match] = [1/totalWordsPerDay, 1];
				}
				
				if (!$scope.excludedWords[match])
				{
					if (typeof topWordsPosition[match] != "undefined")
					{
						$scope.topWords[topWordsPosition[match]].total++;
					}
					else
					{
						topWordsPosition[match] = $scope.topWords.length; 
						$scope.topWords.push({"total" : 1, "isPhrase" : false, "text" : match});
					}
				}
				
				count++;
				if (count == $scope.reportData.articleLength*$scope.reportData.articlePerDay)
				{
					//counting included phrases
					for (var j = 0; j < $scope.includedPhrases.length; j++)
					{
						var phraseRegEx = new RegExp($scope.includedPhrases[j], "gmi");
						computedArticle.replace(phraseRegEx, function(match){
							match = match.toLowerCase();
							if (point[match])
							{
								point[match][0] += 1/totalWordsPerDay;
								point[match][1]++;
							}
							else
							{
								point[match] = [1/totalWordsPerDay, 1];
							}
							
							if (topWordsPosition[match])
							{
								$scope.topWords[topWordsPosition[match]].total++;
							}
							else
							{
								topWordsPosition[match] = $scope.topWords.length; 
								$scope.topWords.push({"total" : 1, "isPhrase" : true, "text" : match});
							}
						});
					}
					
					$scope.chartData.push(point);
					point = {};
					count = 0;
					computedArticle = "";
				}
				// console.log(count+" "+match);
			});			
		}
		
		$scope.topWords.sort(function(a,b){
			if (a.total < b.total) return 1;
			if (a.total > b.total) return -1;
			return 0;
		});
		
		var selectFirstFive = true;
		
		if (window.config && config["active_words"])
		{
			for (var i = 0; i < $scope.topWords.length; i++)
			{
				var word = $scope.topWords[i];
				if (config["active_words"][word["text"]])
				{
					word.active = true;
					selectFirstFive = false;
					$scope.reportData.activeWords[word["text"]] = 1;
				}
			}
		}
		
		if (selectFirstFive)
		{
			for (var i = 0; i < $scope.topWords.length && i < 5; i++)
			{
				var word = $scope.topWords[i];
				word.active = true;
				$scope.reportData.activeWords[word["text"]] = 1;
			}
		}
		
		//console.dir($scope.chartData);
	};
	
	$scope.drawActiveWords = function()
	{
		$scope.resetChart();
		
		/*
		$scope.chart.data.cols.push({
			"id" : "date",
			"type" : "date",
			"label" : "Date"
		});
		*/
		
		$scope.chart.data.cols.push({
			"id" : "articles",
			"type" : "string",
			"label" : "Number of Articles"
		});
		
		var i;
		for (i = 0; i < $scope.topWords.length; i++)
		{
			if ($scope.topWords[i].active)
			{
				var word = $scope.topWords[i]["text"];
				$scope.chart.data.cols.push({
					"id" : word,
					"label" : word,
					"type" : "number"
				});
			}
		}
		
		for (i = 0; i < $scope.chartData.length; i++)
		{
			var point = $scope.chartData[i];
			var newRow = {"c" : []};
			newRow.c.push({"v" : (i+1)*$scope.reportData.articlePerDay});
			for (var j = 0; j < $scope.topWords.length; j++)
			{
				if ($scope.topWords[j].active)
				{
					var word = $scope.topWords[j]["text"];
					if (point[word])
					{
						newRow.c.push({"v" : point[word][0].toFixed(3), "f" : point[word][1]+" counted"});
					}
					else
					{
						newRow.c.push({"v" : 0, "f" : "0 counted"});
					}
				}
			}
			
			$scope.chart.data.rows.push(newRow);
		}
		/*
		var nDays = Math.floor(($scope.dateRange.endDate.getTime() - $scope.dateRange.initDate.getTime())/(24*60*60*1000));
		for (i = 0; i <= nDays; i++)
		{
			var date = new Date($scope.dateRange.initDate.getTime() + i*24*60*60*1000);
			var newRow = {"c" : []};
			newRow.c.push({"v" : date});
			
			var value;
			for (var j = 0; j < topWords.length; j++)
			{
				if (topWords[j].active)
				{
					var word = topWords[j]["word_text"] || topWords[j]["phrase_text"];
					if ($scope.chartData[word] && $scope.chartData[word][i])
					{
						if (typeof $scope.chartData[word][i][0] == "string")
						{
							$scope.chartData[word][i][0] = parseInt($scope.chartData[word][i][0], 10);
							$scope.chartData[word][i][1] = parseInt($scope.chartData[word][i][1], 10);
						}
						value = $scope.chartData[word][i][0]/$scope.chartData[word][i][1];
						newRow.c.push({"v" : value.toFixed(3), "f" : $scope.chartData[word][i][0]+" of "+$scope.chartData[word][i][1]+" counted"});
					}
					else
					{
						newRow.c.push({"v" : null});
					}
				}
			}
			
			$scope.chart.data.rows.push(newRow);
		}
		*/
	};
	
}]).filter("evenArray", function(){
	return function(input, test){
		var newArray = [];
		for (var i = 0; i < input.length; i+=2)
		{
			newArray.push(input[i]);
		}
		return newArray;
	};
}).controller('DensityController', ['$scope', '$timeout', '$http', function($scope, $timeout, $http){
	$scope.selectedProject = {};
	$scope.articles = [];
	$scope.totalArticles = [];
	$scope.dateRange = {};
	$scope.chartData = [];
	$scope.reportData = {};
	$scope.average = 0;
	$scope.wordsTotal = 0;
	$scope.articlesTotal = 0;
	$scope.countOfArticle = 0;
	$scope.keyword = "";
	$scope.words = [];
	$scope.topWords = [];
	
	$scope.chart = 
	{
		"type": "ColumnChart",
		"cssStyle": "height:400px; width:100%;",
		"data": {
			"cols": [],
	        "rows": []
		},
		"options": {
			"title": "Words Density",
			
			"fontName" : '"Helvetica Neue",Helvetica,Arial,sans-serif',
			
			"animation" : {
				"duration" : 500,
				"easing" : "out",
				"startup" : true
			},
			"interpolateNulls" : true
		},
		"formatters": {},
		"displayed": true
	};
	
	$scope.init = function()
	{
		$scope.selectedProject = selectedProject;
		$scope.totalArticles = articles;
		$scope.words = words;
		$scope.topWords = [];
		$scope.excludedWords = {};
		$scope.isFirstLoad = true;

		$debug = $scope;

		if (window.config)
		{
			$scope.reportData.articleLength = parseInt(config["article_length"] || 300, 10);
			$scope.reportData.articlePerDay = parseInt(config["article_per_day"] || 3, 10);
		}
		
		if (window.excludedWords)
		{
			for (var i = 0; i < excludedWords.length; i++)
			{
				$scope.excludedWords[excludedWords[i].text] = 1;
			}
		}
		
		if (window.includedPhrases)
		{
			$scope.includedPhrases = [];
			for (var i = 0; i < includedPhrases.length; i++)
			{
				$scope.includedPhrases.push(includedPhrases[i].text);
			}
		}
		
		if(window.config.density_option == undefined) {
			var initDate = new Date().toISOString().slice(0,10);
			if ($scope.totalArticles.length > 0)
			{
				initDate = $scope.totalArticles[0].article_time;
			}
			$scope.keyword = $scope.words[0].word_text;
			$scope.isChecked = $scope.words[0].word_text;
			$scope.reportData.initDate = initDate.substring(8, 10) + "/" + initDate.substring(5, 7) + "/" + initDate.substring(0, 4);
		} else {
			$scope.reportData.initDate = window.config.density_option.beginning_date;
			$scope.countOfArticle = window.config.density_option.count_of_articles;
			$scope.keyword = window.config.density_option.keyword;
			$scope.isChecked = window.config.density_option.keyword;
		}
		$(".input-daterange .from-date").data("datepicker").setDate($scope.reportData.initDate);
				
		$scope.submitAnalysis();
	};
	
	$scope.isEmptyChart = function()
	{
		return $scope.articles.length == 0;
		//return false;
	};

	$scope.changeActiveWord = function(currentWord) {
		$scope.keyword = currentWord.text;
		$scope.isChecked = currentWord.text;
		$scope.isFirstLoad = false;
		$scope.submitAnalysis();
	};

	$scope.submitAnalysis = function()
	{	
		var from = $scope.reportData.initDate.split("/");
		from = from[2] + "-" + from[1] + "-" + from[0];
		
		var post = {
			"initDate" : from,
			"endDate" : from, 
			"projectId" : $scope.selectedProject.projectId,
			"kind" : "density"
		};

		$.post("/ajax/getDataFromWordsList.php", post, function(response){

			if (response["error"])
			{
				alert(response["error"]);
				return;
			}
			$scope.articles = response["data"];
			if($scope.articles.length == 0) {
				$("#warnNotExistArticles").modal("show");
				$scope.resetChart();
				$scope.topWords = [];
				$scope.average = 0;
				$scope.wordsTotal = 0;
				$scope.articlesTotal = 0;
				$scope.$apply();
				return;
			} else {
				if($scope.countOfArticle == 0) {
					$scope.countOfArticle = $scope.articles.length;
				}

				$scope.countWords();

				if(window.config.density_option == undefined && $scope.topWords.length > 0 && $scope.isFirstLoad == true) {
					$scope.keyword = $scope.topWords[0].text;
					$scope.isChecked = $scope.topWords[0].text;
				}

				$scope.chartData = [];
				
				var wholeText = "";
				for(var i = 0; i < $scope.articles.length; i++) {
					wholeText += " " + $scope.articles[i]["article_text"];
				}
				
				$scope.wordList = [];
				var wordRegEx = /\b[\w-'\u2019]+\b/gm;
				wholeText.replace(wordRegEx, function(match) {
					match = match.toLowerCase();
					match = match.replace("/['\u2019]s/g", "");
					$scope.wordList.push({
						'text' : match
					});
				});

				$scope.wordsTotal = $scope.wordList.length;
				$scope.average = Math.round($scope.wordsTotal / $scope.countOfArticle);

				var keywordCount = 0;
				for(var i = 0; i < $scope.wordList.length; i++) {
					if($scope.keyword == $scope.wordList[i].text) {
						keywordCount++;
					}
					if(((i+1) % $scope.average) == 0) {
						$scope.chartData.push({
							'total' : keywordCount
						});
						keywordCount = 0;
					}
				}
				$scope.saveConfig();
				$scope.drawDensity();
				$scope.$apply();
			}
			
		}, "json");
		
	};

	$scope.saveConfig = function() {
		if ($scope.saveActiveTimeout)
		{
			$timeout.cancel($scope.saveActiveTimeout);
		}
		
		$scope.saveActiveTimeout = $timeout(function(){
			$http.post("/ajax/saveDensityConfig.php", $.param({
				"density_option" : '{"beginning_date": "' + $scope.reportData.initDate + '", "count_of_articles": "' + $scope.countOfArticle + '", "keyword": "' + $scope.keyword + '"}',
				"projectId" : $scope.selectedProject.projectId
			}), {headers: {'Content-type' : 'application/x-www-form-urlencoded'}});
		}, 500);
	};

	$scope.resetChart = function()
	{
		$scope.chart.data.cols = [];
		$scope.chart.data.rows = [];
	};

	$scope.drawDensity = function()
	{
		$scope.resetChart();
		
		$scope.chart.data.cols = [
			{id: "t", label: "Word Counts", type: "string"},
			{id: "d", label: $scope.keyword, type: "number"}
		];
		for(var i = 0; i < $scope.chartData.length; i++) {
			$scope.chart.data.rows.push({
				c: [
					{v: $scope.chartData[i].total + " counted" },
					{v: $scope.chartData[i].total / $scope.average}
				]
			})
		}
		
	};

	$scope.countWords = function()
	{
		$scope.topWords = [];
		var topWordsPosition = {};
		var count = 0;
		var pointIndex = 0;
		var point = {};
		var totalWordsPerDay = $scope.reportData.articleLength*$scope.reportData.articlePerDay;
		var wordRegEx = /\b[\w-'\u2019]+\b/gm;
		var computedArticle = "";
		for (var i = 0; i < $scope.articles.length; i++)
		{
			var text = $scope.articles[i]["article_text"];
			text.replace(wordRegEx, function(match)
			{
				computedArticle += match+" ";
				match = match.toLowerCase();
				match = match.replace("/['\u2019]s/g", "");
								
				if (!$scope.excludedWords[match])
				{
					if (typeof topWordsPosition[match] != "undefined")
					{
						$scope.topWords[topWordsPosition[match]].total++;
					}
					else
					{
						topWordsPosition[match] = $scope.topWords.length; 
						$scope.topWords.push({"total" : 1, "isPhrase" : false, "text" : match});
					}
				}
				
				count++;
				if (count == $scope.reportData.articleLength*$scope.reportData.articlePerDay)
				{
					//counting included phrases
					for (var j = 0; j < $scope.includedPhrases.length; j++)
					{
						var phraseRegEx = new RegExp($scope.includedPhrases[j], "gmi");
						computedArticle.replace(phraseRegEx, function(match){
							match = match.toLowerCase();
													
							if (topWordsPosition[match])
							{
								$scope.topWords[topWordsPosition[match]].total++;
							}
							else
							{
								topWordsPosition[match] = $scope.topWords.length; 
								$scope.topWords.push({"total" : 1, "isPhrase" : true, "text" : match});
							}
						});
					}
									
					count = 0;
					computedArticle = "";
				}
			});			
		}
		
		$scope.topWords.sort(function(a,b){
			if (a.total < b.total) return 1;
			if (a.total > b.total) return -1;
			return 0;
		});
		
	};
	
}]).filter("evenArray", function(){
	return function(input, test){
		var newArray = [];
		for (var i = 0; i < input.length; i+=2)
		{
			newArray.push(input[i]);
		}
		return newArray;
	};
}).controller('ExcludedWordsController', function($scope){
	$scope.excludedWords = [{"excludedWordId" : 1, "excludedWordText" : "aeofnaoefiaef oaefi"}];
	$scope.deleteForm = {};
	$scope.createForm = {};
	
	$scope.addExcludedWord = function()
	{
		$("#createExcludedWord button[type=submit]").button("loading");
		$.post("/ajax/addExcludedWord.php", $scope.createForm, function(response){
			$("#createExcludedWord button[type=submit]").button("reset");
			if (response["error"])
			{
				alert(response["error"]);
				return;
			}
			
			var inserted = false;
			var newExcluded = {"excludedWordId": response["excluded_word_id"], "excludedWordText": $scope.createForm.excludedWordText}; 
			for (var i = 0; i < $scope.excludedWords.length; i++)
			{
				if ($scope.excludedWords[i].excludedWordText.localeCompare(newExcluded.excludedWordText) >= 0)
				{
					$scope.excludedWords.splice(i,0,newExcluded);
					inserted = true;
					break;
				}
			}
			
			if (!inserted)
			{
				$scope.excludedWords.push(newExcluded);
			}
			
			$scope.createForm.excludedWordText = "";
			$scope.$apply();
			$("#createExcludedWord").modal("hide");
		}, "json");
	};
	
	$scope.openDeleteExcludedWord = function(excludedWord)
	{
		$scope.deleteForm.excludedWordId = excludedWord.excludedWordId;
		$scope.deleteForm.excludedWordText = excludedWord.excludedWordText;
	};
	
	$scope.deleteExcludedWord = function()
	{
		$("#deleteExcludedWord button[type=submit]").button("loading");
		$.post("/ajax/removeExcludedWord.php", $scope.deleteForm, function(response){
			$("#deleteExcludedWord button[type=submit]").button("reset");
			if (response["error"])
			{
				alert(response["error"]);
				return;
			}
			
			for (var i = 0; i < $scope.excludedWords.length; i++)
			{
				if ($scope.excludedWords[i].excludedWordId == $scope.deleteForm.excludedWordId)
				{
					$scope.excludedWords.splice(i,1);
					break;
				}
			}
			$("#deleteExcludedWord").modal("hide");
			$scope.$apply();
		}, "json");
	}
}).controller("PhrasesController", function($scope){
	$scope.phrases = [];
	$scope.createForm = {};
	$scope.deleteForm = {"phrase" : {}};
	
	$scope.addPhrase = function()
	{
		$("#addPhrase button[type=submit]").button("loading");
		$.post("/ajax/addPhrase.php", $scope.createForm, function(response){
			$("#addPhrase button[type=submit]").button("reset");
			if (response["error"])
			{
				alert(response["error"]);
				return;
			}
			
			var inserted = false;
			var newPhrase = {"phraseId" : response["phrase_id"], "phraseText" : $scope.createForm.phraseText};
			for(var i = 0; i < $scope.phrases.length; i++)
			{
				if ($scope.phrases[i].phraseText.localeCompare(newPhrase.phraseText) >= 0)
				{
					$scope.phrases.splice(i,0,newPhrase);
					inserted = true;
					break;
				}
			}
			
			if (!inserted)
			{
				$scope.phrases.push(newPhrase);
			}
			
			$scope.createForm.phraseText = "";
			$scope.$apply();
			$("#addPhrase").modal("hide");
		}, "json");
	};
	
	$scope.removePhrase = function($form)
	{
		$("#removePhrase button[type=submit]").button("loading");
		$.post("/ajax/removePhrase.php", $scope.deleteForm.phrase, function(response){
			$("#removePhrase button[type=submit]").button("reset");
			if (response["error"])
			{
				alert(response["error"]);
				return;
			}
			
			for(var i = 0; i < $scope.phrases.length; i++)
			{
				if ($scope.phrases[i].phraseId == $scope.deleteForm.phrase.phraseId)
				{
					$scope.phrases.splice(i,1);
					break;
				}
			}
			
			$scope.deleteForm.phrase = {};
			$scope.$apply();
			$("#removePhrase").modal("hide");
		}, "json");
	};
}).controller("ArticlesController", ['$scope', '$sce', function($scope, $sce){
	$scope.selectedProject = {};
	$scope.articles = [];
	$scope.editForm = {
		"type": 1,
		"projectId": 0,
		"article": {}
	};
	$scope.deleteForm = {};
	
	$scope.initArticles = function()
	{
		var articles = window._articles;
		$scope.selectedProject = window._selectedProject;
		
		for (var i = 0; i < articles.length; i++)
		{
			var article = articles[i];
			article.articleTime = parseInt(article.articleTime, 10);
			article.articleId = parseInt(article.articleId, 10);
			$scope.articles.push(article);
		}
	};
	
	$scope.openEditArticle = function(article)
	{
		var $modal = $("#editArticle");
		if (article) //edit article
		{
			$modal.find(".modal-title").html("Edit Article");
			$modal.find(".modal-footer .btn-primary").html("Edit Article");
			$modal.find("button[type=submit]").data("loading-text", "Updating...");
			$scope.editForm.type = 1;
			$scope.editForm.article.articleId = article.articleId;
			$scope.editForm.article.articleText = article.articleText;
			$scope.editForm.article.articleUrl = article.articleUrl;
			var date = new Date(article.articleTime);
			$scope.editForm.article.articleTime = date.getDate()+"/"+(date.getMonth()+1)+"/"+date.getFullYear();
		}
		else //create article
		{
			$modal.find(".modal-title").html("Add New Article");
			$modal.find(".modal-footer .btn-primary").html("Add Article");
			$modal.find("button[type=submit]").data("loading-text", "Adding...");
			$scope.editForm.type = 0;
			$scope.editForm.article.articleText = "";
			$scope.editForm.article.articleUrl = "";
			$scope.editForm.article.articleTime = "";
		}
		
		$scope.editForm.projectId = $scope.selectedProject.projectId;
	};
	
	$scope.editArticle = function()
	{
		if ($scope.editForm.article.articleTime != "")
		{
			//dd mm yyyy
			var date = $scope.editForm.article.articleTime.split("/");
			$scope.editForm.article.articleTime = (new Date(date[1]+"/"+date[0]+"/"+date[2])).getTime();
		}
		else
		{
			$scope.editForm.article.articleTime = Date.now();
		}
		
		if ($scope.editForm.article.articleUrl.length == 0)
		{
			//do nothing
		}
		else if ($scope.editForm.article.articleUrl.indexOf("http://") < 0 && 
			$scope.editForm.article.articleUrl.indexOf("https://") < 0)
		{
			$scope.editForm.article.articleUrl = "http://"+$scope.editForm.article.articleUrl;
		}
		
		$scope.editForm.timezone = (new Date()).getTimezoneOffset();
		$("#editArticle button[type=submit]").button("loading");
		$.post("/ajax/editArticle.php", $scope.editForm, function(response){
			$("#editArticle button[type=submit]").button("reset");
			if (response["error"])
			{
				alert(response["error"]);
				return;
			}
			
			if ($scope.editForm.type == 0)
			{
				$scope.editForm.article.articleId = response["article_id"];
			}
			else
			{
				$scope.removeArticleFromList($scope.editForm.article.articleId);
			}
			
			$scope.addArticleToList($scope.editForm.article);
			$scope.editForm.article = {};
			$scope.$apply();
			$('[data-toggle="tooltip"]').tooltip();
			$("#editArticle").modal("hide");
		}, "json");
	};
	
	$scope.removeArticle = function()
	{
		$("#removeArticle button[type=submit]").button('loading');
		$.post("/ajax/removeArticle.php", $scope.deleteForm, function(response){
			$("#removeArticle button[type=submit]").button('reset');
			if (response["error"])
			{
				alert(response["error"]);
				return;
			}
			
			$scope.removeArticleFromList($scope.deleteForm.articleId);
			$scope.deleteForm = {};
			$scope.$apply();
			$("#removeArticle").modal("hide");
		}, "json");
	};
	
	$scope.addArticleToList = function(article)
	{
		var inserted = false;
		for (var i = 0; i < $scope.articles.length; i++)
		{
			if ($scope.articles[i].articleTime < article.articleTime)
			{
				$scope.articles.splice(i,0,article);
				inserted = true;
				break;
			}
			else if ($scope.articles[i].articleId < article.articleId)
			{
				$scope.articles.splice(i,0,article);
				inserted = true;
				break;
			}
		}
		
		if (!inserted)
		{
			$scope.articles.push(article);
		}
	};
	
	$scope.removeArticleFromList = function(articleId)
	{
		for (var i = 0; i < $scope.articles.length; i++)
		{
			if ($scope.articles[i].articleId == articleId)
			{
				$scope.articles.splice(i,1);
				break;
			}
		}
	};
	
	$scope.excerpt = function(text)
	{
		text = text.replace(/\n/g, "<br/>");
		if (text.length > 256)
		{
			text = text.substr(0,254)+"...";
		}
		return $sce.trustAsHtml(text);
	};
	
	$scope.timestampToDate = function(timestamp)
	{
		var date = new Date(timestamp);
		return date.getDate()+"/"+(date.getMonth()+1)+"/"+date.getFullYear();
	};
}]);

var $debug;