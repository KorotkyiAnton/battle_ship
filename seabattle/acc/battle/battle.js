export function placeShipsOnField(shipData) {
    let mineField = document.querySelector('.mine-field .ships');
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
            shipElement.style.top = ((parseInt(shipInfo.shipStart.slice(1))-1) * 25).toString() + "px";
            shipElement.style.left = (((shipInfo.shipStart[0]).charCodeAt(0) - 97) * 25).toString() + "px";
            //console.log(shipInfo.orientation)
            if (shipInfo.orientation === 'left') {
                shipElement.classList.add('left');
            } else if (shipInfo.orientation === 'right') {
                shipElement.classList.add('right');
            } else if (shipInfo.orientation === 'up') {
                shipElement.classList.add('up');
                shipElement.style.top = ((parseInt(shipInfo.shipStart.slice(1))) * 25).toString() + "px";
            } else if (shipInfo.orientation === 'down') {
                shipElement.classList.add('down');
            }
            //console.log(shipElement.style.top)

            // Добавляем корабль на поле mine-field
            mineField.appendChild(shipElement);
        }
    }
}

placeShipsOnField(JSON.parse(localStorage.getItem("shipCoords")));