{*$catResults | print_r*}
<div class="row justify-content-center align-items-center py-3">
<div class="col-lg-5 col-sm-12 col-12">	
        {foreach $catResults as $cr}
           <h5 class="">{$cr.cat_name}</h5>
            <div class="text-muted small">{$cr.result}</div>                   
        {/foreach}
    </div>
<div class="col-lg-5 col-sm-12 col-12">	
        <div id="canvas-holder" style="width:100%">
            <canvas id="chart-area" /></div>
	    </div>    
</div>

<script>
    var randomScalingFactor = function() {
        return Math.round(Math.random() * 100);
    };
    
    var getProc = function(point,all) {
        return Math.round(point / all * 100);
    };
    
    var config = {
        type: 'pie',
        data: {
            datasets: [{
                data: [
                    {foreach $catResults as $cr}
                        getProc({$cr.cat_point},{$test_point}),
                    {/foreach}
                ],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(255, 159, 64)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(54, 162, 235)',
                    'rgb(153, 102, 255)',
                    'rgb(201, 203, 207)',
                ],
                label: 'Dataset 1'
            }],
            labels: [
                {foreach $catResults as $cr}
                    "{$cr.cat_name} {$cr.cat_point}{if $test.type == 1} из {$cr.max_point}{/if}",
                {/foreach}
            ]
        },
        options: {
            responsive: true
        }
    };

    {if !$check_ajax}
        onload_chart = function() {
    {/if}
        var ctx = document.getElementById("chart-area").getContext("2d");
        window.myPie = new Chart(ctx, config);
    {if !$check_ajax}
        };
        _init.push(onload_chart);
    {/if}
</script>