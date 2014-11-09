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

repogramsDirectives.directive('rgRenderMetric', ['$interpolate', '$compile', '$modal', 'reposService', 'blenService', 'metricSelectionService', 'blenSelectionService', 'zoomService', function($interpolate, $compile, $modal, reposService, blenService, metricSelectionService, blenSelectionService, zoomService) {
  return {

    restrict: 'E',
    scope: {},
    template: '<div class="renderMetric"><div style="width:100%; overflow: auto; white-space: nowrap;">' +
      '<div class="individualMetric" style="width:100%; padding: 1px; overflow: visible; white-space: nowrap;">' +
      '</div></div></div>',
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
      $scope.popModal = function() {};
      var templateBlock = '<div class="customBlock" ng-click="popModal(\'{{commitID}}\', \'{{commitURL}}\', \'{{commitMsg}}\')" tooltip-html-unsafe=\'{{tooltip}}\' tooltip-popup-delay="200" style="background-color: red; width: {{width}};"></div>';
      var templateBlockString = $interpolate(templateBlock);


      // get any metric to do the initial setup with it
      var firstSelectedMetric = metricSelectionService.getAllMetrics()[0];
      // insert individual commit blocks with the correct size into container
      var currentBlockLengthMode = blenSelectionService.getSelectedBlenMod().id;
      var commitBlocks = "";
      var repoURL = $scope.repo.url;
      for( var i = 0; i < $scope.repo.metricData[firstSelectedMetric.id].length; i++) {
       var commitMsg = $scope.repo.metricData.commit_msgs[i];
       var msg = commitMsg.length > 40 ? commitMsg.substring(0, 39) + '…'
                                                 : commitMsg;
       var commitID = $scope.repo.metricData.checksums[i];
       var commitURL = repoURL.replace(/\.git$|$/, "/commit/" + commitID);
       var commitHash = commitID.substring(0, 8);
       var tooltip = '<p class=\"commitMessage\"><code>' + commitHash + '</code> <span>' + msg + '</span></p><p class=\"text-muted\">Click for details</p>';
       var churn = $scope.repo.metricData.churn[i];
       var context = {
         width: blenService.getWidth(currentBlockLengthMode, churn, $scope.totalChurn, $scope.currentZoom).string,
         tooltip: tooltip,
         commitID: commitID,
         commitURL: commitURL,
         commitMsg: msg
       };
       commitBlocks += templateBlockString(context);
      }
      var content = $compile(commitBlocks)($scope);
      var innerMost =  element.children().children().children();
      innerMost.html(commitBlocks);


      /* TODO: create copy of container if needed (more than one metric) (not
       * necessary currently, because there will only ever be one metric
       * selected; in the advanced version this will change) */

      function updateColors(metricID) {
        // iterate over all commit blocks and
        innerMost.children().css("background-color", function(index) {
          // set colour according to metric values
          return reposService.mapToColor(metricID, $scope.repo.metricData[metricID][index]);
        });
      }

      function updateWidth(currentBlockLengthMode) {
        // iterate over all commit blocks and
        innerMost.children().css("width", function(index) {
          // set width according to current mode
          var churn = $scope.repo.metricData.churn[index];
          return blenService.getWidth(currentBlockLengthMode, churn, $scope.totalChurn, $scope.currentZoom).string;
        });
      }


      // set colors for each metric that should be displayed
      angular.forEach(metricSelectionService.getSelectedMetrics(), function(value, key) {
        updateColors(value.id);
      });

      // register watches to trigger recomputations

      // the mapper might change when a new repo is added, and the
      // maxvalue increases
      $scope.$on('mapperChange', function (evnt, metricID, newMapper) {
        // TODO: support multiple metrics
        if (metricID === metricSelectionService.getSelectedMetrics()[0].id) {
          // only update visible metrics
          updateColors(metricID);
        }
      });

      $scope.oldZoom = zoomService.getSelectedZoom().num;

      $scope.$on('zoomChange', _.debounce(function (evnt, newZoom){
        // TODO: this function would be much cleaner if we had oldZoom in the
        // event
        // TODO: WARNING: this function does not call updateString for performance
        // reasons; this could lead to incorrect results
        // NOTE: the debounce is really important, else we get a terrible
        // performance
        if ($scope.oldZoom !== newZoom.num) {
          if ($scope.blenSelectionService.getSelectedBlenMod().id === "3_constant") {
            var scalingFactor = newZoom.num/$scope.oldZoom;
            $scope.oldZoom = newZoom.num;
            innerMost.children().css("width", function(index, oldWidth) {
              // remove unit, multiply with scaling factor and add px again
              ret = parseInt(oldWidth.slice(0,oldWidth.length-2))*scalingFactor+"px";
              return ret;
            });
            $scope.$apply(); // code above never throws
          }
        } 
      }, 1000));

      $scope.$watchCollection("metricSelectionService.getSelectedMetrics()", function(newVal) {
        console.log("newval", newVal);
        angular.forEach(newVal, function(value, key) {
          updateColors(value.id);
        });
      });

      $scope.$watch("blenSelectionService.getSelectedBlenMod()", function(newVal) {
        updateWidth(newVal.id);
      });
    }
  };
}]);

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
