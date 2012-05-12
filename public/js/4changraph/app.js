$(document).ready(function() {
  $('[name="from"]').datepicker();
  $('[name="to"]').datepicker();

  var drawGraph = function(result) {
    var options = {
      chart : {
        renderTo: 'graphContainer',
        defaultSeriesType: 'spline',
      },
      title: {
        text: null
      },
      plotOptions: {
        spline: {
          marker: {
            enabled: false
          }
        }
      },
      yAxis: [],
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
        var series = board.posts.map(function(point) {
          return [Date.parse(point.date), parseInt(point.count)];
        });

        options.series.push({name: index, data: series});
        options.yAxis.push({
          title: {
            text:null
          },
          labels: {
            enabled: false
          },
          gridLineWidth: 0
        });
      });
    }

    var count = options.series.length;
    $.each(options.series, function(index, series) {
      (function(index, series) {
        if (index < count) {
          series.yAxis = index;
        }
      }(index + 1, series));
    });

    new Highcharts.Chart(options);
  };


  $('.boardToggle').click(function() {
    $(this).toggleClass('active');
    return false;
  });

  $('#graph').click(function() {
    var data = {};

    now = new Date();
    if ($('[name="to"]').val().length > 0) {
      now  = new Date($('[name="to"]').val());
    }

    then = new Date();
    then.setDate(then.getDate() - 8);
    if ($('[name="from"]').val().length > 0) {
      then = new Date($('[name="from"]').val());
    }

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
