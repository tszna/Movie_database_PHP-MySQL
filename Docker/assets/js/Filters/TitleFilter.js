class TitleFilter extends InputFilter {
	constructor(params) {
		super(params);
	}

	render() {
		return '' +
			'<div class="filter__item">' +
				'<input type="'+this.type+'" id="'+this.name+'" name="'+this.name+'" class="form__input main__subchildren filter" placeholder="'+this.placeholder+'" />' +
				'<div class="" id="' + this.name + '-results" style="display: none;">' +
					'<div class="lists results"></div>' +
				'</div>' +
			'</div>';
	}

	searchTitleRow(id, title) {
		return '<a href="#" class="hoverable" style="width: 100%" onclick="TitleFilter.oneFilm(\'http://localhost:2300/film/'+id+'/\');"><span class="py-1 px-2">'+title+'</span></a>';
	}

	static oneFilm(url) {
		window.location.href = url;
	}
	
	initSearch(url) {
		document.querySelector('#'+this.name).value = '';

		let instance = this;
		let input = document.querySelector('#' + this.name);

		input.onkeyup = function() {
			let inputValue = this.value;

			if (inputValue.length === 0) {
				$('#' + this.name + '-results').hide();
				return;
			}

			$.ajax({
				url: url,
				type: 'POST',
				async: true,
				data: {
					"value": inputValue
				}
			})
				.done(function(response) {
					if (response.records.length === 0) {
						$('#' + input.name + '-results').hide();
						return;
					}

					$('#title-results').show();
					let height = Math.min(150, 24 * Object.keys(response.records).length);
					$('#' + input.name + '-results').css('height', height+'px')

					let resultsBox = $('#' + input.name + '-results .results');
					resultsBox.html('');

					for (let id in response.records) {
						let title = response.records[id];
						resultsBox.append(instance.searchTitleRow(id, title));
					}
				})
				.fail(function(response) {
					console.error(response);
					alert(response.responseJSON.error);
				});
		};

		input.onfocusout = function() {
			$('#' + instance.name + '-results').hide(400);
		};

		input.onfocus = function() {
			if ($('#' + instance.name + '-results .results').html().trim().length > 0) {
				$('#' + instance.name + '-results').show();
			} else {
				$('#' + instance.name + '-results').hide();
			};
		}
	}
}
