var options = {
	chart : {
		renderTo: 'graphContainer',
		defaultSeriesType: 'bar',
	},
	series: [{
		name: 'Jane',
		data: [1, 0, 4]
	}]
};

$(document).ready(function() {
	chart = new Highcharts.Chart(options);
});
