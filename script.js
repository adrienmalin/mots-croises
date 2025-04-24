async function sha256(text) {
    const encoder = new TextEncoder();
    const data = encoder.encode(text);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

const inputs = Array.from(grilleForm.querySelectorAll('input[type="text"]'));
let largeur = Number(colonnes.value);
let nb_cases = inputs.length;
let index = 0;

inputs.forEach(input => {
    input.index = index++;

    input.onfocus = function (event) {
        input.select();
    };

    input.onkeydown = function (event) {
        switch (event.key) {
            case 'ArrowUp':
                inputs[(input.index - largeur + nb_cases) % nb_cases].focus();
                break;
            case 'ArrowDown':
                inputs[(input.index + largeur) % nb_cases].focus();
                break;
            case 'ArrowLeft':
                inputs[(input.index - 1 + nb_cases) % nb_cases].focus();
                break;
            case 'ArrowRight':
                inputs[(input.index + 1) % nb_cases].focus();
                break;
        }
    };

    input.oninput = function (event) {
        this.value = this.value.toUpperCase();
        if (!input.checkValidity()) {
            input.value = '';
        }
        if (inputs.every(input => input.value.length == 1) && grilleForm.checkValidity()) {
            sha256(inputs.map(input => input.value).join('')).then(hash => {
                if (hash == solution_hashee.value) {
                    if (confirm('Bravo ! \nUne nouvelle partie ?')) {
                        grilleForm.submit();
                    }
                }
            });
        }
    };
});
