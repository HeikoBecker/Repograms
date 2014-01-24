function example() {

	    $.ajax({
		    url: 'https://api.github.com/repositories',
	          complete: function(xhr) {
			        var url = xhr.responseJSON;
				var number = Math.floor(Math.random()*xhr.responseJSON.length);
				var descr = url[number].description;
				url = url[number].html_url;
				$("<p style=\"text-align:center;\">"+descr+"</p>").appendTo(document.getElementById('description'));
		(document.getElementById('repourl')).value = url+'.git';

				        }
	        });
	      }
