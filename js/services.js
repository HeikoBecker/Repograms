var repogramsServices = angular.module('repogramsServices', []);

repogramsServices.service('reposService', ["$rootScope", "metricSelectionService", function ($rootScope, metricSelectionService) {
  var RepoArr = [];
  var totalChurnArr = [];
  var mappers = {};
  var allMetrics = metricSelectionService.getAllMetrics();
  var maxVal = {};
  var maxChurn = 0;

  var initializeMappers = function () {
    for (var i = 0; i < allMetrics.length; i++) {
      // initialize with dummy mapper
      var metric = allMetrics[i].id;
      mappers[metric] = mapperFactory.createMapper(0, metric);
      maxVal[metric] = -1;
    }
  };
  initializeMappers();

  /**
   * Update the max value per metrix, if needed.
   */
  var updateMetricMax = function (metric, metricMaxVal, forceUpdate) {
    if (metric == "branches_used" || metric == "commit_author") {
      mappers[metric] = mapperFactory.createMapper(null, metric);
      $rootScope.$broadcast("mapperChange", metric, mappers[metric]);
    } else if (metricMaxVal > maxVal[metric] || forceUpdate) {
      maxVal[metric] = metricMaxVal;
      mappers[metric] = mapperFactory.createMapper(maxVal[metric], metric);
      $rootScope.$broadcast("mapperChange", metric, mappers[metric]);
    }
  };

  return {
    getRepoArr: function () {
      return RepoArr;
    },
    getTotalChurnArr: function () {
      return totalChurnArr;
    },
    getMaxChurn: function () {
      return maxChurn;
    },
    addRepo: function (repoJSON) {
      RepoArr.push(repoJSON);
      $rootScope.$broadcast("reposChange", RepoArr);
      for (var metric in mappers) {
        var metricMaxVal = arrayMax(repoJSON.metricData[metric]);
        updateMetricMax(metric, metricMaxVal, false);
      }

      /**
       * totalChurn is necessary to calculate the proportional size of blocks
       * all churns are summed up and stored per repo
       */
      var totalChurn = 0;

      for (var i = 0; i < repoJSON.metricData.churns.length; i++) {
        totalChurn += repoJSON.metricData.churns[i];
      }
      totalChurnArr.push(totalChurn);
      maxChurn = arrayMax(totalChurnArr);
      $rootScope.$broadcast("maxChurnChange", maxChurn);
    },
    removeRepo: function (place) {
      console.assert(place >= 0, "");
      console.assert(place < RepoArr.length, "");
      RepoArr.splice(place, 1);
      $rootScope.$broadcast("reposChange", RepoArr);

      var totalChurn = totalChurnArr[place];
      totalChurnArr.splice(place, 1);
      if (totalChurn >= maxChurn) {
        maxChurn = arrayMax(totalChurnArr);
        $rootScope.$broadcast("maxChurnChange", maxChurn);
      }

      if (RepoArr.length == 0) {
        initializeMappers();
        return;
      }

      for (var metric in mappers) {
        var metricMaxVals = [];
        for (var repoIndex = 0; repoIndex < RepoArr.length; repoIndex++) {
          var repoJSON = RepoArr[repoIndex];
          metricMaxVals.push(arrayMax(repoJSON.metricData[metric]));
        }
        var metricMaxVal = arrayMax(metricMaxVals);
        updateMetricMax(metric, metricMaxVal, true);
      }
    },
    moveRepoUp: function (place) {
      if (place == 0)
        return;
      var tmp = RepoArr[place];
      RepoArr[place] = RepoArr[place - 1];
      RepoArr[place - 1] = tmp;
      $rootScope.$broadcast("reposChange", RepoArr);
    },
    moveRepoDown: function (place) {
      if (place == RepoArr.length - 1)
        return;
      var tmp = RepoArr[place];
      RepoArr[place] = RepoArr[place + 1];
      RepoArr[place + 1] = tmp;
      $rootScope.$broadcast("reposChange", RepoArr);
    },
    mapToColor: function (metric, value) {
      console.assert(typeof metric === "string", "metric must be the name of a metric");
      console.assert(typeof mappers[metric] !== "undefined", "mapper is not initialized");
      return mappers[metric].map(value);
    },
    getMapper: function (metric) {
      return mappers[metric];
    }
  };
}]);

repogramsServices.service('metricSelectionService', ['$rootScope', function ($rootScope) {
  var allMetrics = [
    {
      id: "commit_localization",
      label: "Commit Localization",
      icon: "sun-o",
      description: "Fraction of the number of unique project directories containing files modified by the commit.",
      long_description: "Metric value of 1 means that all the modified files in a commit are in a single directory. Metric value of 0 means <em>all</em> the project directories contain a file modified by the commit."
    },
    {
      id: "commit_message_length",
      label: "Commit Message Length",
      icon: "circle",
      description: "The number of words in a commit log message.",
      long_description: null
    },
    {
      id: "languages_in_a_commit",
      label: "Languages in a Commit",
      icon: "certificate",
      description: "The number of unique programming languages used in a commit based on filenames.",
      long_description: null
    },
    {
      id: "branches_used",
      label: "Branches Used",
      icon: "plus-circle",
      description: "Each branch is associated with a unique color. A commit is colored according to the branch it belongs to.",
      long_description: null
    },
    {
      id: "most_edited_file",
      label: "Most Edited File",
      icon: "dot-circle-o",
      description: "The number of times that the most edited file in a commit has been previously modified.",
      long_description: null
    },
    {
      id: "number_of_branches",
      label: "Number of Branches",
      icon: "cog",
      description: "The number of branches that are concurrently active at a commit point.",
      long_description: null
    },
    {
      id: "pom_files",
      label: "POM files",
      icon: "codepen",
      description: "The number of POM files changed in every commit.",
      long_description: null
    },
    {
      id: "commit_author",
      label: "Commit Author",
      icon: "user",
      description: "Each commit author is associated wih a unique color. A commit block is colored according to its author.",
      long_description: null
    },
    {
      id: "commit_age",
      label: "Commit Age",
      icon: "clock-o",
      description: "Elapsed time between a commit and its parent commit.",
      long_description: "For merge commits we consider the elapsed time between a commit and its youngest parent commit."
    },
    {
      id: "files_modified",
      label: "Files Modified",
      icon: "file",
      description: "The number of files modified in a particular commit, includes new and deleted files.",
      long_description: null
    },
    {
      id: "merge_indicator",
      label: "Merge Indicator",
      icon: "code-fork",
      description: "Displays the number of parents involved in a commit",
      long_description: "2 or more parents denote a merge commit."
    },
    {
      id: "author_experience",
      label: "Author Experience",
      icon: "user-times",
      description: "The number of commits a contributor has previously made to the repository",
      long_description: null
    }
  ];
  var selectedMetrics = [];
  var isMetricsFirst = true;

  return {
    getSelectedMetrics: function () {
      return selectedMetrics;
    },
    swapMetric: function (metric) {
      var position = selectedMetrics.indexOf(metric);
      if (position == -1) {
        metric.selected = true;
        selectedMetrics.push(metric);
      } else {
        metric.selected = false;
        selectedMetrics.splice(position, 1);
      }
      $rootScope.$broadcast('selectedMetricsChange');
    },
    getAllMetrics: function () {
      return allMetrics;
    },
    clear: function () {
      for (var i = 0; i < selectedMetrics.length; i++) {
        selectedMetrics[i].selected = false;
      }
      selectedMetrics.length = 0;
      $rootScope.$broadcast('selectedMetricsChange');
    },
    isMetricsFirst: function () {
      return isMetricsFirst;
    },
    setIsMetricsFirst: function (value) {
      isMetricsFirst = value;
      $rootScope.$broadcast('multiMetricModeChange');
    }
  };
}]);

/**
 * calculates block length for given mode and churn
 */

repogramsServices.service('blenService', function () {
  var getModFunction = {
    "1_constant": function (churn, totalChurn, maxChurn, noOfCommits, zoom) {
      return {value: (5), divisor: 1, zoom: zoom.num, unit: "px"}
    },
    "2_churn": function (churn, totalChurn, maxChurn, noOfCommits, zoom) {
      return {value: (churn * 100), divisor: maxChurn, zoom: zoom.num, unit: "%"}
    },
    "3_fill": function (churn, totalChurn, maxChurn, noOfCommits, zoom) {
      return {value: (churn * 100), divisor: totalChurn, zoom: zoom.num, unit: "%"}
    }
  };
  var calculateWidth = function (width) {
    width.string = "" + ((width.value / width.divisor) * width.zoom) + width.unit;
    return width;
  };
  return {
    getWidth: function (mode, churn, totalChurn, maxChurn, noOfCommits, zoom) {
      var width = getModFunction[mode](churn, totalChurn, maxChurn, noOfCommits, zoom);
      return calculateWidth(width);
    }
  };
});

repogramsServices.service('blenSelectionService', ["$rootScope", function ($rootScope) {
  var allBlenMods = [
    {
      id: "1_constant",
      label: "Fixed",
      icon: "th",
      description: "All blocks have constant width."
    },
    {
      id: "2_churn",
      label: "Lines changed (comparable btw. projects)",
      icon: "align-left",
      description: "Block width represents number of lines changed in a commit. Project commit histories are scaled <em>uniformly</em> using the same factor (comparable between projects)."
    },
    {
      id: "3_fill",
      label: "Lines changed (incomparable btw. projects)",
      icon: "align-justify",
      description: "Block width represents number of lines changed in a commit. Project commit histories are scaled <em>independently</em> (incomparable between projects)."
    }
  ];
  this.selectedBlenMod = allBlenMods[2];
  this.selectedBlenMod.selected = true;
  var outer = this;

  return {
    getSelectedBlenMod: function () {
      return outer.selectedBlenMod;
    },
    setBlenMod: function (blen) {
      for (var i = 0; i < allBlenMods.length; i++) {
        allBlenMods[i].selected = allBlenMods[i] == blen;
      }
      outer.selectedBlenMod = blen;
      $rootScope.$broadcast('blenModChange');
    },
    getAllBlenMods: function () {
      return allBlenMods;
    }
  };
}]);

repogramsServices.service('zoomService', ["$rootScope", function ($rootScope) {
  var selectedZoom = {num: 1};

  return {
    getSelectedZoom: function () {
      return selectedZoom;
    },
    setZoom: function (zoom) {
      $rootScope.$broadcast("zoomChange", zoom);
      selectedZoom = zoom;
    }
  };
}]);

