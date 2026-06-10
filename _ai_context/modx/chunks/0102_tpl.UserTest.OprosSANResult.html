<div id="chartContainer" style="height: 300px; width: 100%;"></div>
<br>
<script>
    var c_color = {
        0:"#4F81BC",
        1:"#C0504E",
        2:"#4fbc5c",
        3:"#e0e13b",
        4:"#3be1c1",
        };
    var options = {
        exportEnabled: true,
        animationEnabled: true,
        title:{
            text: "{$test.name}"
        },
        subtitles: [{
            text: "График тестов"
        }],
        axisX: {
            title: "Даты"
        },
        {foreach $catResults as $k=>$cr}
        axisY{$k}: {
            title: "{$cr.cat_name}",
            titleFontColor: c_color[{$k}],
            lineColor: c_color[{$k}],
            labelFontColor: c_color[{$k}],
            tickColor: c_color[{$k}],
            includeZero: false
        },
        {/foreach}
        toolTip: {
            shared: true
        },
        legend: {
            cursor: "pointer",
            itemclick: toggleDataSeries
        },
        data: [
        {foreach $catResults as $k=>$cr}
        {
            type: "spline",
            name: "{$cr.cat_name}",
            showInLegend: true,
            xValueFormatString: "D MMM YYYY H:m",
            yValueFormatString: "#,##0.#",
            dataPoints: [
                {foreach $cat_history as $d=>$ch}
                    { x: new Date('{$d}'),  y: {if !$ch[$cr.category_id]}0{else}{$ch[$cr.category_id]}{/if} },
                {/foreach}
            ]
        },
        {/foreach}
        ]
    };
    
    
    {if !$check_ajax}
        onload_chart1 = function() {
    {/if}
        $("#chartContainer").CanvasJSChart(options);
    {if !$check_ajax}
        };
        _init.push(onload_chart1);
    {/if}
    function toggleDataSeries(e) {
        if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
            e.dataSeries.visible = false;
        } else {
            e.dataSeries.visible = true;
        }
        e.chart.render();
    }
</script>