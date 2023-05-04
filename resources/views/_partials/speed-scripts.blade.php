<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    var activity = document.querySelectorAll("[data-container='speeds'] activity")[0];

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
            label: 'Snelheid',
            data: speeds,
            yAxisID: 'speed',
            backgroundColor: '#36a2eb',
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
            backgroundColor: '#ff6384',
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
        datasets: datasets
    };

    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            stacked: false,
            scales: scales
        },
    });
</script>
