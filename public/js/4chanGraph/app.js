$(document).ready(function() {
	var drawGraph = function(result) {
		var options = {
			chart : {
				renderTo: 'graphContainer'
			},
			title: {
				text: ''
			},
			yAxis: {
				title: {
					text: 'Number of posts'
				}
			},
			xAxis: {},
			series: []
		};

		if (!$.isEmptyObject(result)) {
			var xAxis  = [];
			var column = {type: 'column', name: 'post count', data: []};
			$.each(result.boards, function(board, data) {
				xAxis.push(board);
			});

			options.xAxis.categories = xAxis;

			$.each(result.boards, function(board, data) {
				column.data.push(parseInt(data.total.number));

				var series = data.posts.map(function(point) {
					return [Date.parse(point.date), parseInt(point.count)];
				});

				//options.series.push({type: 'spline', name: board, data: series});
			});

			options.series.push(column);
		}
		
		new Highcharts.Chart(options);
	};


	$('.boardToggle').click(function() {
		$(this).toggleClass('active');
		return false;
	});

	$('#graph').click(function() {
		var data = {};

		now  = new Date();
		then = new Date();
		// Default to one day ago
		then.setDate(then.getDate() - 1);

		data.from = then.toJSON();
		data.to =  now.toJSON();

		// Build boards array
		var boards = [];
		$('.boardToggle.active').each(function(index, elem) {
			boards.push(elem.id);
		});

		// Convert to comma seperated list
		if (boards.length > 0) {
			data.boards = boards.reduce(function(prev, curr, index) {
				return prev + ',' + curr;
			});

			$.ajax({
				url: '/4chan-graph',
				data: data,
				success: drawGraph,
				dataType: 'json'
			});
		}
	});

});
