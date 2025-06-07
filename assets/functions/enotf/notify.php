<div id="toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"></div>
<script>
    $(document).ready(function() {
        const inputElements = $("form[name='form'] input:not([readonly]):not([disabled]), form[name='form'] select:not([readonly]):not([disabled]), form[name='form'] textarea:not([readonly]):not([disabled])");

        inputElements.each(function() {
            $(this).data('original-value', $(this).val());
        });

        const activeRequests = {};

        function showToast(message, type = 'success') {
            var bgColor = (type === 'success') ? '#28a745' : '#dc3545';
            var toast = $('<div></div>').text(message).css({
                'background-color': bgColor,
                'color': '#fff',
                'padding': '10px 20px',
                'margin-top': '10px',
                'border-radius': '5px',
                'box-shadow': '0 0 10px rgba(0,0,0,0.3)',
                'font-family': 'Arial, sans-serif',
                'font-size': '14px',
                'opacity': '0.95'
            });
            $('#toast-container').append(toast);
            setTimeout(function() {
                toast.fadeOut(500, function() {
                    $(this).remove();
                });
            }, 4000);
        }

        inputElements.off('change blur').on('change blur', function(e) {
            var $this = $(this);
            var fieldName = $this.attr('name');
            var enr = <?= json_encode($enr) ?>;
            var currentValue;

            if ($this.is(':radio')) {
                currentValue = $('input[name="' + fieldName + '"]:checked').val();
            } else if ($this.is(':checkbox')) {
                currentValue = $this.is(':checked') ? 1 : 0;
            } else {
                currentValue = $this.val();
            }

            var originalValue = $this.data('original-value');

            if ($this.is(':radio')) {
                var originalGroupValue = $('input[name="' + fieldName + '"]').filter(function() {
                    return $(this).data('original-value') == $(this).val();
                }).val();
                if (currentValue == originalGroupValue) {
                    return;
                }
            } else {
                if (currentValue == originalValue) {
                    return;
                }
            }

            if (!activeRequests[fieldName]) {
                activeRequests[fieldName] = true;

                var labelText = $('label[for="' + $this.attr('id') + '"]').text().trim();
                if (!labelText) {
                    var firstInput = $('input[name="' + fieldName + '"]').first();
                    labelText = $('label[for="' + firstInput.attr('id') + '"]').text().trim();
                }
                if (!labelText) {
                    labelText = fieldName;
                }

                $.ajax({
                    url: '/assets/functions/save_fields.php',
                    type: 'POST',
                    data: {
                        enr: enr,
                        field: fieldName,
                        value: currentValue
                    },
                    success: function(response) {
                        showToast("✔️ Feld '" + labelText + "' gespeichert.", 'success');
                        $('input[name="' + fieldName + '"]').data('original-value', currentValue);
                        $this.data('original-value', currentValue);
                    },
                    error: function() {
                        showToast("❌ Fehler beim Speichern von '" + labelText + "'", 'error');
                    },
                    complete: function() {
                        activeRequests[fieldName] = false;
                    }
                });
            }
        });


        $('#final').on('click', function(e) {
            e.preventDefault();
            var enr = <?= json_encode($enr) ?>;

            const plausibilityContent = document.getElementById('plausibility');
            if (plausibilityContent && plausibilityContent.innerText.trim().length > 0) {
                showToast("❌ Abschluss nicht möglich: Plausibilitätsprüfung nicht bestanden!", 'error');
                return;
            }

            const pfname = <?= json_encode($daten['pfname']) ?>;
            if (!pfname || pfname.trim() === "") {
                showToast("❌ Kein Protokollant angegeben!", 'error');
                return;
            }

            $(this).prop('disabled', true);

            $.ajax({
                url: '/assets/functions/save_fields.php',
                type: 'POST',
                data: {
                    enr: enr,
                    field: 'freigeber',
                    value: pfname
                },
                success: function(response) {
                    if (response.includes("erfolgreich")) {
                        window.location.href = "/enotf/prot/index.php?enr=" + enr;
                    } else {
                        showToast("❌ " + response, 'error');
                        $('#final').prop('disabled', false);
                    }
                },
                error: function() {
                    showToast("❌ Fehler beim Abschließen.", 'error');
                    $('#final').prop('disabled', false);
                }
            });
        });
    });
</script>