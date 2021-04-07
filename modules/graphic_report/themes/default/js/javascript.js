$(document).ready( function () {
  console.log(noData);
  if (noData == 0) {
      console.log('pasa');
      var chart=buildChart(chartType);
  } else {
      window.chart.innerText = noData;
  }
});

function buildChart(chartType) {
  if (  
        window.myChart1 !== undefined
        &&
        window.myChart1 !== null
        ) {
        window.myChart1.reset();
        window.myChart1.destroy();
    }
  var ctx = document.getElementById("myChart").getContext('2d');
  var ctx2 = document.getElementById("myChart2").getContext('2d');
  if (chartType == "extension") {
  window.myChart1 = new Chart(ctx, {
    plugins: [ChartDataLabels],
    type: 'pie',
    data: {
      labels: chartLabels.split(","),
      datasets: [
        {
        backgroundColor: ["#522b76", "#3498db"],
        data: chartData.split(","),
        },
      ],
    },
    options: {
        plugins: {
            datalabels: {
                formatter: function(value, context) {
                    var sum = context.dataset.data.reduce((a, b) => {
          	        return Number(a) + Number(b);
                    }, 0);
                    var percent = value / sum * 100;
                    percent = percent.toFixed(2); // make a nice string
                    return value + '\n (' + percent + '%)';
                },
                anchor: 'end',
                align: 'start',
                clamp: true,
                color: '#d9d9d9',
                textAlign: 'center',
            },
        },
        title: {
            display: true,
            text: chartTitle,
        },
        responsive: true, // Instruct chart js to respond nicely.
    },
  });
  return myChart1;
  }
  if (chartType == "queue") {
    window.myChart1 = new Chart(ctx, {
    plugins: [ChartDataLabels],
    type: 'bar',
    data: {
      labels: chartLabels.split(","),
      datasets: [
        {
        backgroundColor: "#522b76",
        data: chartData.split(","),
        },
      ],
    },
    options: {
        plugins: {
            datalabels: {
                formatter: function(value, context) {
                    var sum = context.dataset.data.reduce((a, b) => {
                        return Number(a) + Number(b);
                    }, 0);
                    var percent = value / sum * 100;
                    percent = percent.toFixed(2); // make a nice string
                    return value + '\n (' + percent + '%)';
                },
                anchor: 'start',
                align: 'end',
                clamp: true,
                color: '#d9d9d9',
                textAlign: 'center',
            },
        },
        legend: {
            display: false,
        },
       title: {
            display: true,
            text: chartTitle,
        },
    responsive: true, // Instruct chart js to respond nicely.
    },
  });
  return myChart1;
  }
    if (chartType == "trunk") {
    window.myChart1 = new Chart(ctx, {
    plugins: [ChartDataLabels],
    type: 'pie',
    data: {
      labels: chartLabels.split(","),
      datasets: [
        {
        backgroundColor: ["#522b76", "#3498db"],
        data: chartData.split(","),
        },
      ],
    },
    options: {
        plugins: {
            datalabels: {
                formatter: function(value, context) {
                    var sum = context.dataset.data.reduce((a, b) => {
                        return Number(a) + Number(b);
                    }, 0);
                    var percent = value / sum * 100;
                    percent = percent.toFixed(2); // make a nice string
                    hours = formatMinutes(value);
                    return hours + '\n (' + percent + '%)';
                },
                anchor: 'end',
                align: 'start',
                clamp: true,
                color: '#d9d9d9',
                textAlign: 'center',
            },
        },
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {

                    let label = data.labels[tooltipItem.index];
                    let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                    hours = formatMinutes(value);
                    return ' ' + label + ': ' + hours;

                }
            }
        },
        legend: {
            display: true,
        },
       title: {
            display: true,
            text: chartTitle,
        },
    responsive: true, // Instruct chart js to respond nicely.
    },
  });
  window.myChart2 = new Chart(ctx2, {
    plugins: [ChartDataLabels],
    type: 'pie',
    data: {
      labels: chartLabels.split(","),
      datasets: [
        {
        backgroundColor: ["#522b76", "#3498db"],
        data: chartData2.split(","),
        },
      ],
    },
    options: {
        plugins: {
            datalabels: {
                formatter: function(value, context) {
                    var sum = context.dataset.data.reduce((a, b) => {
                        return Number(a) + Number(b);
                    }, 0);
                    var percent = value / sum * 100;
                    percent = percent.toFixed(2); // make a nice string
                    return value + '\n (' + percent + '%)';
                },
                anchor: 'end',
                align: 'start',
                clamp: true,
                color: '#d9d9d9',
                textAlign: 'center',
            },
        },
        legend: {
            display: true,
        },
       title: {
            display: true,
            text: chartTitle2,
        },
    responsive: true, // Instruct chart js to respond nicely.
    },
  });
  window.myChart1.render();;
  window.myChart2.render();
  return;
  }
} 

function downloadPDF2() {
        var newCanvas = document.querySelector('#myChart');
         var newCanvas2 = document.querySelector('#myChart2');
        //var newCanvasImg = newCanvas.toDataURL("image/jpeg", 1.0);
        var data = newCanvas.toDataURL();
        var data2 = newCanvas2.toDataURL();
        var docDefinition = {
            pageOrientation: 'landscape',
            pageSize: 'A4',
            content: [
                {text: 'Issabel', headlineLevel: 1},
                 " ",
                {
                 image: data,
                 width: 750
                },
                {
                 image: data2,
                 width: 750
                }
                ]
         };
         pdfMake.createPdf(docDefinition).download("gr-reports.pdf");
}

function formatMinutes(value) {
    var hours = Math.floor(value / 3600);
    hours = hours.toString();
    if (hours.length == 1) {
        hours = '0' + hours;
    }
    value %= 3600;
    var minutes = Math.floor(value / 60);
    minutes = minutes.toString();
    if (minutes.length == 1) {
        minutes = '0' + minutes;
    }
    var seconds = value % 60;
    seconds = seconds.toString();
    if (seconds.length == 1) {
        seconds = '0' + seconds;
    }
    return hours + ':' + minutes + ':' + seconds;
}
