<script>
    const inputElements = document.querySelectorAll('.edivi__input-check');

    function toggleInputChecked(inputElement) {
        if (inputElement.tagName === 'SELECT') {
            const selectedOption = inputElement.querySelector('option:checked');
            if (selectedOption && !selectedOption.disabled) {
                inputElement.classList.add('edivi__input-checked');
            } else {
                inputElement.classList.remove('edivi__input-checked');
            }
        } else {
            if (inputElement.value.trim() === '') {
                inputElement.classList.remove('edivi__input-checked');
            } else {
                inputElement.classList.add('edivi__input-checked');
            }
        }

        const groupContainer = inputElement.closest('.edivi__box');
        const groupHeading = groupContainer ? groupContainer.querySelector('h5.edivi__group-check') : null;

        if (groupHeading) {
            inputElement.style.borderLeft = '0';
        } else {
            inputElement.style.borderLeft = '';
        }
    }

    function checkGroupStatus() {
        const groupHeadings = document.querySelectorAll('h5.edivi__group-check');

        groupHeadings.forEach(groupHeading => {
            const groupContainer = groupHeading.closest('.edivi__box');
            if (!groupContainer) return;

            const groupInputs = groupContainer.querySelectorAll('.edivi__input-check');

            let allFilled = true;
            groupInputs.forEach(input => {
                if (input.tagName === 'SELECT') {
                    const selectedOption = input.querySelector('option:checked');
                    if (!selectedOption || selectedOption.disabled) {
                        allFilled = false;
                    }
                } else if (input.value.trim() === '') {
                    allFilled = false;
                }

                input.style.borderLeft = '0';
            });

            if (allFilled) {
                groupHeading.classList.add('edivi__group-checked');
            } else {
                groupHeading.classList.remove('edivi__group-checked');
            }
        });
    }

    inputElements.forEach(inputElement => {
        toggleInputChecked(inputElement);
        inputElement.addEventListener('input', () => {
            toggleInputChecked(inputElement);
            checkGroupStatus();
        });
    });

    document.addEventListener('DOMContentLoaded', checkGroupStatus);
</script>