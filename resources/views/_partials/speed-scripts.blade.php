<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    var container = document.querySelector("[data-container='speeds']");
    var activity = container.querySelector('activity');

    function getChartData() {
        var labels = [];
        var speeds = [];
        var hr = [];

        activity.querySelectorAll('speed').forEach(coord => {
            labels.push(new Date(coord.dataset.time).toTimeString().split(' ')[0]);
            speeds.push(coord.dataset.speed);
            hr.push(coord.dataset.hr);
        });

        return {labels, speeds, hr};
    }

    var {labels, speeds, hr} = getChartData();

    var datasets = [
        {
            type: 'line',
            label: 'Snelheid',
            data: speeds,
            yAxisID: 'speed',
            borderColor: '#36a2eb',
            backgroundColor: '#36a2eb',
            borderWidth: 1,
            tension: 1,
            fill: true,
            order: 10,
        }
    ];

    var scales = {
        speed: {
            type: 'linear',
            display: true,
            position: 'left',
        },
    };

    if (!!hr.filter(n => n).length) {
        datasets.push({
            label: 'Hartslag',
            data: hr,
            yAxisID: 'hr',
            borderColor: '#ff6384',
            backgroundColor: '#ff6384',
            borderWidth: 2,
            tension: 2,
            order: 5
        });

        scales.hr = {
            type: 'linear',
            display: true,
            position: 'right',
            grid: {
                drawOnChartArea: false,
            },
        };
    }

    const ctx = document.getElementById('speed-chart');
    const data = {
        labels: labels,
        datasets: datasets,
    };

    function handleHover(event, elements) {
        var event = new CustomEvent("speed.hover", {
            detail: elements.map(e => e.index),
        });

        container.dispatchEvent(event);
    }

    const crosshair_plugin = {
        id: 'corsair',
        defaults: {
            width: 1,
            color: '#FF4949',
            dash: [3, 3],
        },
        afterInit: (chart, args, opts) => {
            chart.corsair = {
                x: 0,
                y: 0,
            }
        },
        afterEvent: (chart, args) => {
            const {inChartArea} = args
            const {type,x,y} = args.event

            chart.corsair = {x, y, draw: inChartArea}
            chart.draw()
        },
        beforeDatasetsDraw: (chart, args, opts) => {
            const {ctx} = chart
            const {top, bottom, left, right} = chart.chartArea
            const {x, y, draw} = chart.corsair
            if (!draw) return

            ctx.save()

            ctx.beginPath()
            ctx.lineWidth = opts.width
            ctx.strokeStyle = opts.color
            ctx.setLineDash(opts.dash)
            ctx.moveTo(x, bottom)
            ctx.lineTo(x, top)
            ctx.moveTo(left, y)
            ctx.lineTo(right, y)
            ctx.stroke()

            ctx.restore()
        }
    }

    new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            datasets: {
                line: {
                    pointRadius: 0
                }
            },
            stacked: false,
            scales: scales,
            plugins: {
                corsair: {
                    color: 'black',
                }
            },
            onHover: handleHover,
        },
        plugins: [crosshair_plugin],
    });
</script>
