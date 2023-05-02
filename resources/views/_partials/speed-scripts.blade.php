<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    var activity = document.querySelectorAll("[data-container='speeds'] activity")[0];

    var labels = [];
    var speeds = [];

    activity.querySelectorAll("speed").forEach(coord => {
        labels.push(new Date(coord.dataset.time).toTimeString().split(' ')[0]);
        speeds.push(coord.dataset.speed);
    });

    console.log(labels, speeds)

    const ctx = document.getElementById('speed-chart');
    const data = {
        labels: labels,
        datasets: [{
            label: 'Snelheid',
            data: speeds,
            fill: true,
        }]
    };

    new Chart(ctx, {
        type: 'bar',
        data: data,

    });
</script>
