
<script>
var hours = {$hoursJSON};
//var lang = "{$LANG}";
var total = {$totalJSON};
var dahdi = {$dahdiJSON};
var sip = {$sipJSON};
var iax = {$iaxJSON};
var h323 = {$h323JSON};
var local = {$localJSON};
var localtz = "{$timezone}";
</script>

<div class="chart-container" id=chart>
  <canvas id="myChart" height=420></canvas>
</div>

<div class="text-right">
  <button type="button" class="btn btn-link" id="download-pdf2" onclick="downloadPDF2()">
    <span class="glyphicon glyphicon-stats" aria-hidden="true"></span>
    PDF
  </button>
</div>
