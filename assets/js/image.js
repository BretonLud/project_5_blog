let imageCounter = document.querySelectorAll('.picture-upload').length;
let imageCanBeDeleted = document.querySelectorAll('.picture-can-be-deleted')

const btn = document.querySelector('.btn-add-image');

btn.addEventListener('click', () => {
    addImageUploadFields();
});

function removeImageUploadFields(colDiv) {
    colDiv.remove()
}

imageCanBeDeleted.forEach(element => {
    const btn = removeButton(element);
    const cardDiv = element.querySelector('.card');

    cardDiv.appendChild(btn);
})

function addImageUploadFields() {

    const colDiv = createDiv(['col', 'mb-3']);
    const cardDiv = createDiv(['form-group', 'picture-upload', 'mb-3', 'card', 'p-3']);
    const nameDiv = createDiv(['mb-3']);
    const headerDiv = createDiv(['form-switch', 'mb-3', 'form-check']);
    const removeBtn = removeButton(colDiv);
    const pictureLabel = createLabel('name', 'Image :', ['form-label'])
    const pictureInput = createInput('file', imageCounter, 'name', ['form-control'], true);

    const headerLabel = createLabel('header', "Image d'en-tête", ['form-check-label']);
    const headerInput = createInput('checkbox', imageCounter, 'header', ['form-check-input'])

    colDiv.appendChild(cardDiv);
    cardDiv.appendChild(nameDiv);
    cardDiv.appendChild(headerDiv);
    cardDiv.appendChild(removeBtn);
    nameDiv.appendChild(pictureLabel)
    nameDiv.appendChild(pictureInput)
    headerDiv.appendChild(headerInput)
    headerDiv.appendChild(headerLabel)

    document.getElementById('pictureUploadContainer').append(colDiv);

    imageCounter++;
}

function createDiv(arrayClasses) {

    const div = document.createElement('div');
    addClasses(div, arrayClasses)

    return div
}

function removeButton(colDiv) {
    const btn = document.createElement('button');
    btn.classList.add('btn', 'btn-danger')
    btn.textContent = 'Supprimer';

    btn.addEventListener('click', () => {
        removeImageUploadFields(colDiv);
    });

    return btn
}

function createInput(type, imageCounter, name, arrayClasses, required = false) {
    const input = document.createElement("input");
    addClasses(input, arrayClasses)
    input.setAttribute("type", type);
    input.setAttribute("id", name + imageCounter);

    if (type === 'file') {
        // If input type is file, don't include the name in name attribute
        input.setAttribute("name", "pictures[" + imageCounter + "]");
    } else {
        input.setAttribute("name", "pictures[" + imageCounter + "][" + name + "]");
    }

    if (required) {
        input.setAttribute("required", true);
    }

    return input
}

function createLabel(id, text, arrayClasses) {
    const label = document.createElement("label");
    label.setAttribute("for", id + imageCounter);
    label.textContent = text;
    addClasses(label, arrayClasses);

    return label
}

function addClasses(element, arrayClasses) {
    arrayClasses.forEach(string => {
        element.classList.add(string);
    })
}
