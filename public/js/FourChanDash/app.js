$(document).ready(function() {

  var commaFormat = function(nStr) {
    nStr += '';
    var x = nStr.split('.');
    var x1 = x[0];
    var x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;

    while (rgx.test(x1)) {
      x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }

    return x1 + x2;
  };

  $('[name="from"], [name="to"]').datepicker({dateFormat: 'yy-mm-dd'});

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
      tooltip: {
        formatter: function() {
          var date = new Date(this.x);
          return date.toDateString() + '<br />' +
                 '<b>/' + this.series.name  + '/</b> : ' + commaFormat(this.y);
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
          return [Date.parse(point.date.date), parseInt(point.number)];
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
    then.setDate(then.getDate() - 7);
    if ($('[name="from"]').val().length > 0) {
      then = new Date($('[name="from"]').val());
    }

    data.from = then.toDateString();
    data.to =  now.toDateString();

    var boards = [];
    $('.boardToggle.active').each(function(index, elem) {
      boards.push(elem.id);
    });

    if (boards.length > 0) {
      data.boards = boards.reduce(function(prev, curr, index) {
        return prev + ',' + curr;
      });

      $.ajax({
        url: 'search',
        data: data,
        success: drawGraph,
        dataType: 'json'
      });
    }
  });

});
