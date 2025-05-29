<script>
    function updateTimeAndDate() {
        const now = new Date();
        const berlinTime = new Date(now.toLocaleString("en-US", {
            timeZone: "Europe/Berlin"
        }));
        const time = berlinTime.toLocaleTimeString('de-DE', {
            hour: '2-digit',
            minute: '2-digit'
        });
        const date = berlinTime.toLocaleDateString('de-DE', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });

        document.getElementById('current-time').textContent = time;
        document.getElementById('current-date').textContent = date;
    }

    setInterval(updateTimeAndDate, 60000);
    updateTimeAndDate();
</script>