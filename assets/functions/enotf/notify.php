<div id="toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"></div>
<script>
    $(document).ready(function() {
        const enr = <?= json_encode($enr) ?>;
        const inputElements = $("form[name='form'] input:not([readonly]):not([disabled]), form[name='form'] select:not([readonly]):not([disabled]), form[name='form'] textarea:not([readonly]):not([disabled])");
        const activeRequests = {};

        inputElements.each(function() {
            $(this).data('original-value', $(this).val());
        });

        function updateNavFillStates(data) {
            const zeroIsValid = [
                'patsex', 'awsicherung_neu', 'b_symptome',
                'b_auskult', 'b_beatmung', 'c_kreislauf', 'c_ekg',
                'd_bewusstsein', 'd_ex_1', 'd_pupillenw_1', 'd_pupillenw_2',
                'd_lichtreakt_1', 'd_lichtreakt_2', 'd_gcs_1', 'd_gcs_2', 'd_gcs_3', 'v_muster_k', 'v_muster_t',
                'v_muster_a', 'v_muster_al', 'v_muster_bl', 'v_muster_w', 'transportziel'
            ];

            $("#edivi__nidanav a[data-requires]").each(function() {
                const $link = $(this);
                const requiresRaw = $link[0].dataset.requires;

                if (!requiresRaw) return;

                const groups = requiresRaw.split(",");

                const isFilled = groups.every(group => {
                    const options = group.split("|").map(key => key.trim());

                    return options.some(field => {
                        const val = data[field];
                        return (
                            val !== null &&
                            typeof val !== "undefined" &&
                            (val !== "" && (val !== 0 || zeroIsValid.includes(field)))
                        );
                    });
                });

                $link.toggleClass("edivi__nidanav-filled", isFilled);
            });
        }



        window.__dynamicDaten = <?= json_encode($daten) ?>;
        updateNavFillStates(window.__dynamicDaten);


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

        $('input.btn-check[type="checkbox"]').on('change', function() {
            const clicked = $(this);
            const clickedId = clicked.attr('id');
            const base = clickedId.split('_')[0];
            const group = $('input.btn-check[type="checkbox"]').filter(function() {
                return $(this).attr('id')?.startsWith(base + '_');
            });

            group.each(function() {
                const $box = $(this);
                if ($box[0] !== clicked[0]) {
                    if ($box.is(':checked')) {
                        $box.prop('checked', false).trigger('change');
                    }
                }
            });

            clicked.trigger('blur');
        });

        inputElements.off('change blur').on('change blur', function(e) {
            const $this = $(this);
            const fieldName = $this.attr('name');
            let currentValue;

            if ($this.is(':radio')) {
                currentValue = $('input[name="' + fieldName + '"]:checked').val();
            } else if ($this.is(':checkbox')) {
                currentValue = $this.is(':checked') ? 1 : 0;
            } else {
                currentValue = $this.val();
            }

            const originalValue = $this.data('original-value');

            if ($this.is(':radio')) {
                const originalGroupValue = $('input[name="' + fieldName + '"]').filter(function() {
                    return $(this).data('original-value') == $(this).val();
                }).val();
                if (currentValue == originalGroupValue) return;
            } else {
                if (!$this.hasClass('btn-check') && currentValue == originalValue) return;
            }

            if (!activeRequests[fieldName]) {
                activeRequests[fieldName] = true;

                let labelText = $('label[for="' + $this.attr('id') + '"]').text().trim();
                if (!labelText) {
                    const firstInput = $('input[name="' + fieldName + '"]').first();
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

                        window.__dynamicDaten[fieldName] = currentValue;

                        updateNavFillStates(window.__dynamicDaten);
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