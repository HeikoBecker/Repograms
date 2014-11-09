var repogramsDirectives = angular.module('repogramsDirectives',[]);

repogramsDirectives.directive('ngRenderblock', function(){
        return {
          restrict: 'E',
          scope: {
                commitMsg: "@commitMsg",
                commitID: "@commitId",
                bgColor: "@color",
                width: "=width",
                url: "@url"
          },
          template: '<div class="customBlock" ng-click="popModal()" tooltip-html-unsafe="{{tooltip}}" tooltip-popup-delay="200" style="background-color: {{bgColor}}; width: {{width}};"></div>',
          controller: ['$scope', '$modal', function($scope, $modal) {
            // 40 is the length of commitID
            $scope.msg = $scope.commitMsg.length > 40 ? $scope.commitMsg.substring(0, 39) + '…'
                                                      : $scope.commitMsg;
            $scope.commitURL = $scope.url.replace(/\.git$|$/, "/commit/" + $scope.commitID);
            $scope.commitHash = $scope.commitID.substring(0, 8);
            $scope.tooltip = '<p class="commitMessage"><code>' + $scope.commitHash + '</code> <span>' + $scope.msg + '</span></p><p class="text-muted">Click for details</p>';
            $scope.popModal = function() {
              $modal.open({
                scope: $scope,
                template: '<div class="modal-header"><h3 class="modal-title"><code>{{commitID}}</code></h3></div><div class="modal-body commitDetails"><p><a href="{{commitURL}}">{{commitMsg}}</a></p></div><div class="modal-footer"><button class="btn btn-primary" ng-click="dismiss()">OK</button></div>',
                controller: ['$scope', '$modalInstance', function($scope, $modalInstance) { $scope.dismiss = $modalInstance.dismiss; }]
              });
            };
          }]
        };
});

repogramsDirectives.directive('rgRenderMetric', ['$interpolate' ,'reposService', 'blenService', 'metricSelectionService', 'blenSelectionService', 'zoomService', function($interpolate, reposService, blenService, metricSelectionService, blenSelectionService, zoomService) {
  return {

    restrict: 'E',
    template: '<div class="renderMetric"><div class="individualMetric" style="width:100%; overflow: auto; white-space: nowrap;">' +
      '<div style="width:100%; padding: 1px; overflow: visible; white-space: nowrap;">' +
      '</div></div>',
    link: function($scope, element, attrs) {
      // set up directive
      $scope.reposService = reposService;
      $scope.repo = reposService.getRepoArr()[$scope.$parent.$index];
      $scope.totalChurn = reposService.getTotalChurnArr()[$scope.$parent.$index];
      $scope.blenService = blenService;
      $scope.metricSelectionService = metricSelectionService;
      $scope.blenSelectionService = blenSelectionService;
      $scope.currentZoom = zoomService.getSelectedZoom();

      // template string for individual blocks
      var templateBlock = '<div class="customBlock" ng-click="popModal()" tooltip-html-unsafe="{{tooltip}}" tooltip-popup-delay="200" style="background-color: red; width: {{width}};"></div>';
      var templateBlockString = $interpolate(templateBlock);


      // get any metric to do the initial setup with it
      var firstSelectedMetric = metricSelectionService.getAllMetrics()[0];
      // insert individual commit blocks with the correct size into container
      var currentBlockLengthMode = blenSelectionService.getSelectedBlenMod().id;
      var commitBlocks = "";
      for( var i = 0; i < $scope.repo.metricData[firstSelectedMetric.id].length; i++) {
       var churn = $scope.repo.metricData.churn[i];
       var context = {width: (blenService.getWidth(currentBlockLengthMode, churn, $scope.totalChurn, $scope.currentZoom))};
       commitBlocks += templateBlockString(context);
      }
      element.find("#individualMetric").append(commitBlocks);



      // for each metric that should be displayed
      angular.forEach(metricSelectionService.getAllMetrics(), function(value, key) {
        // create copy of container if needed (more than one metric)
        // and set colour according to metric values
    });
    }
  };
}]);


repogramsDirectives.directive('ngRendermetric', function(){
        return {
	    restrict: 'E',
	    scope:{},
	    template: '<div class="renderMetric" ng-repeat="metric in selectedMetrics"><div style="width:100%; overflow: auto; white-space: nowrap;">' +
	    '<div style="width:100%; padding: 1px; overflow: visible; white-space: nowrap;">' +
	    '<ng-renderblock ng-repeat="style in styles[metric.id][blenMod().id]"  commit-msg={{repo.metricData.commit_msgs[$index]}} commit-id={{repo.metricData.checksums[$index]}} url={{repo.url}} color={{style.color}} width=style.width.string></ng-renderblock>' +
  '</div></div>',
	    controller: ['$scope','reposService', 'blenService', 'metricSelectionService', 'blenSelectionService', 'zoomService', function($scope, reposService, blenService, metricSelectionService, blenSelectionService, zoomService, $sce){
		//TODO: Add every metricvalue
                $scope.reposService = reposService;
                $scope.blenService = blenService;
                $scope.metricSelectionService = metricSelectionService;
                $scope.blenSelectionService = blenSelectionService;
                $scope.selectedMetrics = metricSelectionService.getSelectedMetrics();
                $scope.repo = reposService.getRepoArr()[$scope.$parent.$index];
                $scope.zoomService = zoomService;
                $scope.blenMod = blenSelectionService.getSelectedBlenMod;
                $scope.currentZoom = zoomService.getSelectedZoom();
                $scope.totalChurn = reposService.getTotalChurnArr()[$scope.$parent.$index];
                $scope.styles = {};
                angular.forEach(metricSelectionService.getAllMetrics(), function(value, key) {
                	$scope.styles[value.id] = [];
                	var currentValueIDStyle = $scope.styles[value.id];

                	angular.forEach(blenSelectionService.getAllBlenMods(), function(bValue, bKey) {
                		currentValueIDStyle[bValue.id] = [];
                		var currentModIDStyle = currentValueIDStyle[bValue.id];
                		
	                	for( var i = 0; i < $scope.repo.metricData[value.id].length; i++){
	                		var churn = $scope.repo.metricData.churn[i];
	                		var x = {
	                				color: reposService.mapToColor(value.id, $scope.repo.metricData[value.id][i]),
	                				width: (blenService.getWidth(bValue.id, churn, $scope.totalChurn, $scope.currentZoom))
	                		};
	                		currentModIDStyle.push(x);
	                	}
                	});
                });
                $scope.$on('zoomChange', function (evnt, newZoom){
                	angular.forEach(metricSelectionService.getAllMetrics(), function(value, key) {
                		angular.forEach(blenSelectionService.getAllBlenMods(), function(bValue, bKey) {
	                		for( var i = 0; i < $scope.repo.metricData[value.id].length; i++){
	                			var oldWidth = $scope.styles[value.id][bValue.id][i].width;
	                			oldWidth.zoom = newZoom.num;
	                			var newWidth = blenService.updateString(oldWidth);
	                			$scope.styles[value.id][bValue.id][i].width = newWidth;
	                		}
                		});
                	});
                });
                // the mapper might change when a new repo is added, and the
                // maxvalue increases
                $scope.$on('mapperChange', function (evnt, metricID, newMapper) {
                	angular.forEach(blenSelectionService.getAllBlenMods(), function(bValue, bKey) {
                		
                		for( var i = 0; i < $scope.repo.metricData[metricID].length; i++){
                			$scope.styles[metricID][bValue.id][i].color = newMapper.map($scope.repo.metricData[metricID][i]);
                		}
                	});
                });
            }]
};});

repogramsDirectives.directive('ngLegend', function(){ return {
	restrict: 'E',
	scope: {},
	template: '<div class="panel panel-success">' +
		  '<div class="panel-heading">'+
		  '<h3 class="panel-title">Legend</h3>'+
		  '</div>' +
                  '<div class="panel-body" ng-repeat="metric in selectedMetrics">'+
                  '<p><strong>{{metric.label}}</strong> <span>{{metric.description}}</span></p>'+
		  '<ul class="list-inline">' +
                  '<li ng-repeat="style in styles[metric.id]"><span class="customBlock" style="background-color: {{style.color}};"></span> {{style.lowerBound}}-{{style.upperBound}}</li>' +
                  '</ul></div></div>',
	controller: ['$scope', 'reposService', 'metricSelectionService', function($scope, reposService, metricSelectionService){
          $scope.reposService = reposService;
          $scope.metricSelectionService = metricSelectionService;
          $scope.selectedMetrics = metricSelectionService.getSelectedMetrics();
          $scope.styles = {};
          angular.forEach(metricSelectionService.getAllMetrics(), function(value, index) {
            $scope.styles[value.id] = [];
          });

          $scope.$on("mapperChange", function(evnt, metricID, newMapper) {
            console.assert(angular.isDefined(newMapper), "new mapper is not defined!");
            var mappingInfo = newMapper.getMappingInfo();
            for (var i=0; i < mappingInfo.length; i++) {
              $scope.styles[metricID][i] = {
                color: mappingInfo[i].color,
            width: "10px",
            lowerBound: mappingInfo[i].lowerBound,
            upperBound: mappingInfo[i].upperBound
              };
            }
          }, true);
	}]
};});
