<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('speed-chart');
    const labels = ['a', 'b', 'c'];
    const data = {
        labels: labels,
        datasets: [{
            label: 'My First Dataset',
            data: [1, 2, 3],
            fill: false,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    };

    new Chart(ctx, {
        type: 'bar',
        data: data,

    });
</script>
