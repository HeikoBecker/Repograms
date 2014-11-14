angular.module('repogramsModule').factory('commitLangCompl', ['fileInfo', function (fileInfo) {
  /*
   * Small hack to get a fitting map function as the map function is an extension
   * of the javaScript standard and not implemented in every version.
   */
  if (!Array.prototype.map) {
    Array.prototype.map = function (fun /*, thisp*/) {
      var len = this.length;
      if (typeof fun != "function")
        throw new TypeError();

      var res = new Array(len);
      var thisp = arguments[1];
      for (var i = 0; i < len; i++) {
        if (i in this)
          res[i] = fun.call(thisp, this[i], i, this);
      }

      return res;
    };
  }

  /*
   * Function that validates if a given file extension is in the set of wanted
   * extensions, that has been specified by Ivan.
   */
  function isValidEnding(entry) {
    "use strict";
    var pos = fileInfo.ENDINGS.indexOf(entry);
    return pos;
  }

  function isValidFile(entry) {
    var pos = fileInfo.NAMES.indexOf(entry);
    return pos;
  }


  /*
   * Return for a list of changed files an integer containing the number of
   * changed files.
   */
  function getMetric(fileList, data) {
    var mem = {};

    fileList.forEach(function (entry) {
      // remove everything before the last /
      var realEntry = "";
      var fileName = entry.split("/").pop();
      var position = isValidFile(fileName);
      if (position !== -1) {
        realEntry = fileInfo.TYPES[position];
      } else {
        var fileEnding = fileName.split(".").pop();
        position = isValidEnding(fileEnding);
        if (position !== -1) {
          realEntry = fileInfo.TYPE[position];
        } else {
          realEntry = "Other";
        }
      }
      realEntry in mem ? mem[realEntry] += 1 : mem[realEntry] = 1;
    });
    return Object.keys(mem).length;
  }

  return {
    "run": getMetric
  };
}]);
