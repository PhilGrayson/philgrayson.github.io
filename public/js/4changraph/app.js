$(document).ready(function() {
  $('[name="from"]').datepicker();
  $('[name="to"]').datepicker();

  var drawGraph = function(result) {
    var options = {
      chart : {
        renderTo: 'graphContainer',
        defaultSeriesType: 'spline',
      },
      yAxis: {
        title: {
          text: 'Number of posts'
        }
      },
      xAxis: {
        title: {
          text: 'Date'
        },
        type: 'datetime'
      },
      series: []
    };

    if (!$.isEmptyObject(result)) {
      $.each(result.boards, function(index, board) {
        var series = result.data[board].map(function(point) {
          return [Date.parse(point.date), parseInt(point.count)];
        });

        options.series.push({name: board, data: series});
      });
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
    then.setDate(then.getDate() - 8);

    data.from = then.toJSON();
    data.to =  now.toJSON();

    var boards = [];
    $('.boardToggle.active').each(function(index, elem) {
      boards.push(elem.id);
    });

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
