export function placeShipsOnField(shipData) {
    let mineField = document.querySelector('.mine-field');
    for (let shipId in shipData) {
        if (shipId !== 'messageId' && shipId !== 'messageType' && shipId !== 'createDate') {
            let shipInfo = shipData[shipId];
            let shipElement = document.createElement('div');
            shipElement.className = 'ship';

            // Устанавливаем класс для корабля в зависимости от его размера
            if (shipInfo.coords.length === 4) {
                shipElement.classList.add('fourdeck');
            } else if (shipInfo.coords.length === 3) {
                shipElement.classList.add('tripledeck');
            } else if (shipInfo.coords.length === 2) {
                shipElement.classList.add('doubledeck');
            } else {
                shipElement.classList.add('singledeck');
            }
            shipElement.classList.add(shipId);

            // Устанавливаем координаты и ориентацию корабля
            shipElement.style.top = (shipInfo.shipStart.slice(1) * 25).toString() + "px";
            shipElement.style.left = (((shipInfo.shipStart[0]).charCodeAt(0) - 96) * 25).toString() + "px";
            if (shipInfo.orientation === 'vertical') {
                shipElement.classList.add('vertical');
            }
            console.log(shipElement)

            // Добавляем корабль на поле mine-field
            mineField.appendChild(shipElement);
        }
    }
}

placeShipsOnField(JSON.parse(localStorage.getItem("shipCoords")));