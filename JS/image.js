document.addEventListener('DOMContentLoaded', function () {
    let items = document.querySelectorAll('.dropdown-item');
    let inputHidden = document.getElementById('immagine_default');
    let preview = document.getElementById('preview-img');

    for (let i = 0; i < items.length; i++) {
        items[i].addEventListener('click', function () {
            let valore = items[i].getAttribute('data-value');
            inputHidden.value = valore;
            preview.innerHTML = '<img src="default_profiles/' + valore + '" style="width:60px; height:60px; border-radius:50%;">';
        });
    }
});
