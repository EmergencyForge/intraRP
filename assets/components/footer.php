<!-- Footer für das Intranet-System -->
<footer class="footer mt-auto py-3 text-light">
    <div class="container">
        <div class="row d-flex align-items-end justify-content-between">
            <div class="col-md-4">
                <h5><?php echo SYSTEM_NAME ?></h5>
                <p class="small">Verwaltungsportal der <?php echo RP_ORGTYPE . " " . SERVER_CITY ?></p>
            </div>
            <div class="col-md-4 text-center">
                <p class="small">&copy; <?php echo date("Y") ?> <a href="https://intrarp.de">intraRP</a> | Alle Rechte vorbehalten</p>
            </div>
            <div class="col-md-4 text-end">
                <p class="small">Version <?php echo SYSTEM_VERSION ?? "1.0" ?></p>
                <p><a href="https://github.com/intraRP/intraRP/issues" target="_blank">Fehler melden</a></p>
            </div>
        </div>
    </div>
</footer>