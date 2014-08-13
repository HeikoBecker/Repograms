<?php
require_once('header.php');
?>

<html ng-app="repogramsModule">
	<head>
		<!-- include Angular.js -->
		<script type="text/javascript" src="/js/angular.min.js"></script>
		<!-- Sanitize Module for HTML injections -->
		<script type="text/javascript" src="/js/sanitize.js"></script>
		<!-- include the app controllers -->
		<script type="text/javascript" src="/js/app.js"></script>

		<title>RepoGrams</title>
	</head>
	<body>
	<div class="container">
		<!-- First block for the selection box and zoom slider -->
		<div class="row">
		<div ng-controller="RepogramsConfig">
			<!-- Dropdown Menu for Metric Selection -->
			<select  ng-model="metricId" 
			ng-options="metric.metricId as metric.name for metric in metrics">
			</select>
			<!-- TODO: Add slider maybe: https://prajwalkman.github.io/angular-slider/ -->
		</div>
		</div>

		<!-- Main Block with the Repo name and Metric render -->
                <div ng-controller="RepogramsRender" class="container-fluid">
                        <div class="row">
                          <div class="col-md-8" style="outline: 1px solid red;">
                            <div class="repo" ng-repeat="repo in repos">
                              <h3>{{repo.name}}</h3>
                              <ng-rendermetric></ng-rendermetric>
                            </div>
                          </div>
                          <div class="col-md-4" style="outline: 1px solid black;">
                            <ng-legend></ng-legend>
                          </div>
                        </div>
                </div>
		<br><br>
		<div class="row">
		<!-- last block for import box -->
		<div ng-controller="RepogramsImporter" class=row>
                  <div class="col-md-12">
			<input type="text" ng-model="importURL"/>
			<button ng-click="importRepo()">{{ImportButtonText}}</button>
                  </div>
		</div>
		</div>
	</div>

</body>
</html>