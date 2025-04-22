// Script per gestire la selezione dell'immagine
let items = document.querySelectorAll('.dropdown-item');
for (let i = 0; i < items.length; i++) 
    items[i].addEventListener('click', function () {
        let imageValue = this.getAttribute('data-value');
        document.getElementById('immagine_default').value = imageValue;
        // Cambia il testo del pulsante
        let dropbtn = document.querySelector('.dropbtn');
        dropbtn.innerHTML = '<img src="default_profiles/' + imageValue + '" alt="' + imageValue + '" class="avatar-image"> ' + this.innerText;
    });
