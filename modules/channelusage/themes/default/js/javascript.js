$(document).ready( function () {
  var chart=buildChart();
});

function buildChart() {
  if (  
        window.myChart1 !== undefined
        &&
        window.myChart1 !== null
        ) {
        window.myChart1.destroy();
    }
  var ctx = document.getElementById("myChart").getContext('2d');
  window.myChart1 = new Chart(ctx, {
    type: 'line',
    data: {
      labels: hours,
      datasets: [
        {
        label: 'Total',
        data: total,
        borderColor: "#522b76",
        backgroundColor: "#522b76",
        fill: false,
        },
        {
        label: 'DAHDI',
        data: dahdi,
        borderColor: "#0000ff",
        backgroundColor: "#0000ff",
        fill: false,
        },
        {
        label: 'SIP',
        data: sip,
        borderColor: "#00cc66",
        backgroundColor: "#00cc66",
        fill: false,
        },
        {
        label: 'IAX',
        data: iax,
        borderColor: "#ff9900",
        backgroundColor: "#ff9900",
        fill: false,
        },
        {
        label: 'H323',
        data: h323,
        borderColor: "#ff99bb",
        backgroundColor: "#ff99bb",
        fill: false,
        },
        {
        label: 'Local',
        data: local,
        borderColor: "#ffff00",
        backgroundColor: "#ffff00",
        fill: false,
        },
      ],
    },
    options: {
        scales: {
          xAxes: [{
          type: 'time',
          time: {
            timezone: localtz,
            unit: 'minute',
            tooltipFormat: 'lll',
            }
          }]
       }, 
    scaleShowValues: true,
    responsive: true, // Instruct chart js to respond nicely.
    maintainAspectRatio: false, // Add to prevent default behavior of full-width/height 
    },
  });
  return myChart1;
} 

function downloadPDF2() {
        var newCanvas = document.querySelector('#myChart');
        var newCanvasImg = newCanvas.toDataURL("image/jpeg", 1.0);
        var data = newCanvas.toDataURL();
        var docDefinition = {
            pageOrientation: 'landscape',
            pageSize: 'A4',
            content: [
                {text: 'Issabel Channel Usage', headlineLevel: 1},
                 " ",
                {
                 image: data,
                 width: 750
                }]
         };
         pdfMake.createPdf(docDefinition).download("channel-usage.pdf");
}

