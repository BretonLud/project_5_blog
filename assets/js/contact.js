const form = document.querySelector('form');
const button = document.querySelector('.btn-submit-contact');

button.addEventListener('click', () => {

    const data = new FormData(form)

    if (!validateForm(form)) {
        return;
    }

    fetch(form.action, {
        method: form.getAttribute('method'),
        body: data,
    }).then(response => {
        return response.json();
    })
        .then(async response => {
            if (response.success) {
                const successMessage = "L'email a bien été envoyé. Vous allez être redirigé vers la page d'accueil dans 5 secondes.";

                form.style.display = "none";

                let successContainer = document.getElementById('success-container');
                if (!successContainer) {
                    successContainer = document.createElement('div');
                    successContainer.id = "success-container";
                    successContainer.classList.add('mt-3')
                    form.parentNode.insertBefore(successContainer, form.nextSibling);
                }

                successContainer.innerText = successMessage;

                let counter = 5;
                const intervalId = setInterval(() => {
                    counter--;
                    if (counter === 0) {
                        clearInterval(intervalId);
                        window.location.href = "/";
                    } else {
                        successContainer.innerText = `L'email a bien été envoyé. Vous allez être redirigé vers la page d'accueil dans ${counter} secondes.`;
                    }
                }, 1000);
            }

            if (response.error && typeof response.error === "string") {
                console.log(typeof response.error)
                let errorContainer = document.getElementById('error-container');
                if (!errorContainer) {
                    errorContainer = document.createElement('div');
                    errorContainer.id = "error-container";
                    form.parentNode.insertBefore(errorContainer, form);
                }

                errorContainer.innerText = response.error
            }

            if (response.error && typeof response.error === "object") {
                for (let key in response.error) {
                    if (response.error.hasOwnProperty(key)) {
                        let error = response.error[key];

                        let field = document.querySelector(`input[name=${key}], textarea[name=${key}]`);

                        if (field) {

                            let errorContainer = field.parentNode.querySelector('.error');
                            if (!errorContainer) {
                                errorContainer = document.createElement('div');
                                errorContainer.style.color = "red";
                                errorContainer.classList.add('error');
                                field.parentNode.appendChild(errorContainer);
                            }
                            errorContainer.innerText = error;
                        }
                    }
                }
            }

        }).catch(error => {
        console.error('There has been a problem with your fetch operation:', error);
    });
})

const validators = {
    'fullname': {
        validator: value => value.trim() !== '',
        message: 'Veuillez entrer votre nom complet.'
    },
    'email': {
        validator: value => /^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/.test(value),
        message: "Le format d'e-mail est invalide. Veuillez le saisir comme 'nom@example.com'."
    },
    'phone': {
        validator: value => value === '' || /^\+?(\d){8,15}$/.test(value),
        message: 'Veuillez entrer un numéro de téléphone valide. Par exemple, \'+33123456789\' ou \'0123456789\''
    },
    'subject': {
        validator: value => value.trim() !== '',
        message: 'Veuillez entrer le sujet.'
    },
    'message': {
        validator: value => value.trim() !== '',
        message: 'Veuillez entrer le message.'
    },
}

function validateForm(form) {
    const data = new FormData(form);
    let isValid = true;

    for (let name of data.keys()) {
        const input = form.elements[name];
        const validator = validators[name];

        const errorMessage = document.getElementById(name + 'Error');

        if (validator && !validator.validator(input.value)) {
            input.classList.add('is-invalid');
            let text = validator.message;

            if (errorMessage) {
                errorMessage.textContent = text;
            } else {
                const errorElement = document.createElement('div');
                errorElement.id = name + 'Error';
                errorElement.style.color = 'red';
                errorElement.textContent = text;
                input.parentNode.insertBefore(errorElement, input.nextSibling);
            }

            isValid = false;
        } else {
            if (errorMessage) {
                errorMessage.textContent = '';
            }

            input.classList.remove('is-invalid');
        }
    }

    return isValid;
}