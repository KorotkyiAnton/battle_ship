let draggedImage = null;
let lastDraggedImage = null;

function drag(event) {
    draggedImage = event.target;
}

function allowDrop(event) {
    event.preventDefault();
}

function drop(event) {
    event.preventDefault();
    const battleField = document.querySelector(".cells");

    if (draggedImage) {
        const x = event.clientX - battleField.getBoundingClientRect().left;
        const y = event.clientY - battleField.getBoundingClientRect().top;
        console.log(draggedImage.classList)

        if ((x > 25 && y > 25 && (
                draggedImage.id.contains("ship-1") ||
                draggedImage.classList.contains("ship-2") ||
                draggedImage.classList.contains("ship-3") ||
                draggedImage.classList.contains("ship-4"))) ||
            (x > 25 && y > 25 && x < 250 && (
                draggedImage.classList.contains("ship-5") ||
                draggedImage.classList.contains("ship-6") ||
                draggedImage.classList.contains("ship-7"))) ||
            (x > 25 && y > 25 && x < 225 && (
                draggedImage.classList.contains("ship-8") ||
                draggedImage.classList.contains("ship-9"))) ||
            (x > 25 && y > 25 && x < 200 &&
                draggedImage.classList.contains("ship-10"))) {
            // Знаходимо ближчі менші кратні координати 25 (ширина зображення)
            const newX = Math.floor(x / 25) * 25;
            const newY = Math.floor(y / 25) * 25;
            console.log((newX - 25) / 25 + 1, (newY - 25) / 25 + 1)

            draggedImage.style.position = "absolute";
            draggedImage.style.left = newX + 'px';
            draggedImage.style.top = newY + 'px';

            // Перемістити зображення в новий контейнер
            battleField.appendChild(draggedImage);
            lastDraggedImage = draggedImage; // Зберігаємо останню перенесену картинку
            draggedImage = null;
        }
    }
}

function rotateImage() {
    if (lastDraggedImage) {
        const currentRotation = parseInt(lastDraggedImage.style.transform.replace('rotate(', '').replace('deg)', '')) || 0;
        const newRotation = currentRotation + 90;
        lastDraggedImage.style.transform = `rotate(${newRotation}deg)`;
    }
}