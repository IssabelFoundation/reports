<script>
//var lang = "{$LANG}";
var chartLabels = "{$chartLabels}";
var chartData = "{$chartData}";
var chartData2 = "{$chartData2}";
var chartType = "{$Chart}";
var chartTitle = "{$chartTitle}";
var chartTitle2 = "{$chartTitle2}";
var noData = "{$noData}";
</script>

<div class="chart-container" id=chart align=center>
  <canvas id="myChart" height=60></canvas>
</div>
<div class="chart-container" id=chart2 align=center>
  <canvas id="myChart2" height=60></canvas>
</div>

<div class="text-right">
  <button type="button" class="btn btn-link" id="download-pdf2" onclick="downloadPDF2()">
    <span class="glyphicon glyphicon-stats" aria-hidden="true"></span>
    PDF
  </button>
</div>
