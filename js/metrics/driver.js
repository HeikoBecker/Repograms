angular.module('repogramsModule').factory('metricsRunner', ['commitModularity', 'commitMsgLength', 'commitLangCompl', 'mostEditFile', function (commitModularity, commitMsgLength, commitLangCompl, mostEditFile) {
  return {
    runMetricsAsync: function (data, onComplete) {
      async.parallel({
        checksums: function (callback) {
          callback(null, data.checksums);
        },
        commit_msgs: function (callback) {
          callback(null, data.commit_messages);
        },
        commit_lang_complexity: function (callback) {
          if (data.precomputed === true) {
            callback(null, data.commit_lang_compl);
          } else {
            async.map(data.files, function (item, transformer) {
              transformer(/*err=*/null, commitLangCompl.run(item));
            }, callback);
          }
        },
        branch_complexity: function (callback) {
          callback(null, data.bcomplexities);
        },
        commit_message_length: function (callback) {
          if (data.precomputed === true) {
            callback(null, data.commit_message_length);
          } else {
            async.map(data.commit_messages, function (item, transformer) {
              transformer(/*err=*/null, commitMsgLength.run(item));
            }, callback);
          }
        },
        branch_usage: function (callback) {
          callback(null, data.associated_branches);
        },
        commit_modularity: function (callback) {
          if (data.precomputed === true) {
            callback(null, data.commit_modularity);
          } else {
            async.map(data.files, function (item, transformer) {
              transformer(/*err=*/null, commitModularity.run(item));
            }, callback);
          }
        },
        most_edited_file: function (callback) {
          if (data.precomputed === true) {
            callback(null, data.most_edit_file);
          } else {
            callback(null, mostEditFile.run(_.zip(data.churns, data.files)));
          }
        },
        churn: function (callback) {
          callback(null, data.churns);
        },
        pom_files: function (callback) {
            if (data.precomputed === true) {
                callback(null, data.pom_files);
            } else {
                // TODO do we want to implement a client-side version? Or are we skipping client-side from now on?
                callback("Error: The POM files metric does not support client-side computation.");
            }
        },
        files_modified: function (callback){
		if(data.precomputed === true) {
			callback(null, data.files_modified);
		} else {
			callback("Error: The Files Modified metric does not support client-side computation.");
		}
	},
        merge_indicator: function (callback) {
            if (data.precomputed === true) {
                callback(null, data.merge_indicator);
            } else {
                // TODO do we want to implement a client-side version? Or are we skipping client-side from now on?
                callback("Error: The Merge Indicator metric does not support client-side computation.");
            }
        },
        author_experience: function (callback) {
            if (data.precomputed === true) {
                callback(null, data.author_experience);
            } else {
                // TODO do we want to implement a client-side version? Or are we skipping client-side from now on?
                callback("Error: The Author Experience metric does not support client-side computation.");
            }
        },
        commit_author: function (callback) {
            if (data.precomputed === true) {
                callback(null, data.commit_author);
            } else {
                // TODO do we want to implement a client-side version? Or are we skipping client-side from now on?
                callback("Error: The Commit Author metric does not support client-side computation.");
            }
        },
        commit_age: function (callback) {
            if (data.precomputed === true) {
                callback(null, data.commit_age);
            } else {
                // TODO do we want to implement a client-side version? Or are we skipping client-side from now on?
                callback("Error: The Commit Age metric does not support client-side computation.");
            }
        }
      }, function (err, results) {
        onComplete(results);
      });
    }
  };
}]);
