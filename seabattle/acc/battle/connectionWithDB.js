import {requestToDB} from "../prepareConnection.js";

const opponentField = document.querySelector(".opponent-field > .ships");
// Получаем элемент таймера по его id
const timerElement = document.getElementById('timer');
let initialTimeInSeconds = 30;
let timerInterval; // Переменная для хранения интервала таймера

// Функция для обновления таймера
function updateTimer() {
    if (initialTimeInSeconds <= 0) {
        clearInterval(timerInterval);
        timerElement.textContent = 'Час вийшов!';
        // Запуск таймера заново
        startTimer();
        document.querySelector(".opponent-nick").classList.add("highlite");
        document.querySelector(".mine-nick").classList.remove("highlite");
        document.querySelector(".opponent-field").setAttribute('disabled', '');
        document.querySelector(".skip span").innerHTML =
            (parseInt(document.querySelector(".skip span").innerText) - 1).toString();
    } else {
        initialTimeInSeconds--;
        const seconds = initialTimeInSeconds % 60;
        timerElement.textContent = seconds + ' сек';
    }
}

// Функция для запуска таймера
function startTimer() {
    initialTimeInSeconds = 30; // Устанавливаем начальное время
    clearInterval(timerInterval); // Очищаем предыдущий интервал, если он существует
    timerInterval = setInterval(updateTimer, 1000); // Запускаем таймер с интервалом 1 секунда
}

startTimer();
export let turnCounter = 1;

export function checkCoordinateInJSON(jsonString, targetCoordinate) {
    try {
        const data = JSON.parse(jsonString);

        for (const key in data) {
            if (data.hasOwnProperty(key) && data[key].coords) {
                if (data[key].coords.includes(targetCoordinate)) {
                    return true;
                }
            }
        }
        return false;
    } catch (error) {
        return 'Ошибка при разборе JSON.';
    }
}

function getSurroundingCoordinates(coords) {
    let surroundingCoords = [];

    let shipElement = document.createElement('div');
    const parentElement = document.querySelector('.opponent-field .ships');
    shipElement.className = 'ship';

    if (coords.length === 4) {
        shipElement.classList.add('fourdeck');
    } else if (coords.length === 3) {
        shipElement.classList.add('tripledeck');
    } else if (coords.length === 2) {
        shipElement.classList.add('doubledeck');
    } else {
        shipElement.classList.add('singledeck');
    }

    // Устанавливаем координаты и ориентацию корабля
    shipElement.style.top = ((parseInt(coords[0].slice(1))-1) * 25).toString() + "px";
    shipElement.style.left = (((coords[0][0]).charCodeAt(0) - 97) * 25).toString() + "px";

    for (const coord of coords) {
        const [col, row] = coord.split('');
        const colCode = col.charCodeAt(0);
        const rowNumber = parseInt(row);

        for (let i = colCode - 1; i <= colCode + 1; i++) {
            for (let j = rowNumber - 1; j <= rowNumber + 1; j++) {
                if (i >= 97 && i <= 106 && j >= 1 && j <= 10) {
                    // Преобразуем обратно в формат "a1" и добавляем в результат
                    const newCoord = String.fromCharCode(i) + j;
                    if (!coords.includes(newCoord)) {
                        surroundingCoords.push(newCoord);
                    }
                }
            }
        }
    }

    surroundingCoords = Array.from(new Set(surroundingCoords));
    for(let deck of surroundingCoords) {
        const newChildElement = document.createElement('div');
        newChildElement.classList.add("miss");
        newChildElement.style.top = ((parseInt(deck.slice(1)) - 1) * 25).toString() + "px";
        newChildElement.style.left = (((deck[0]).charCodeAt(0) - 97) * 25).toString() + "px";
        parentElement.appendChild(newChildElement);
    }
    parentElement.appendChild(shipElement);

}

opponentField.addEventListener("click", function (e) {
    if ((e.target.tagName === "DIV" ||
            e.target.classList.contains("ships")) &&
        !e.target.classList.contains("miss") &&
        !e.target.classList.contains("hit")
    ) {
        const rect = opponentField.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const cellX = Math.floor(x / 25);
        const cellY = Math.floor(y / 25);

        const coordinate = String.fromCharCode(97 + cellX) + (cellY + 1);

        requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/acc/battle/server.php",
            {
                messageId: 11,
                messageType: "shotRequest",
                createDate: new Date(),
                request: coordinate
            }).then(data => {
            if (data.messageType === "shotResponse" && data.response === 1 && initialTimeInSeconds > 0) {
                initialTimeInSeconds = 30;
                console.log(`I hit ${coordinate}`);
                const newChildElement = document.createElement('div');
                newChildElement.classList.add("hit");
                newChildElement.style.top = (cellY * 25).toString() + "px";
                newChildElement.style.left = (cellX * 25).toString() + "px";
                const parentElement = document.querySelector('.opponent-field .ships');
                parentElement.appendChild(newChildElement);
            } else if (data.messageType === "shotResponse" && data.response === 2 && initialTimeInSeconds > 0) {
                initialTimeInSeconds = 30;
                console.log(`I kill ${coordinate}`);
                const newChildElement = document.createElement('div');
                newChildElement.classList.add("hit");
                newChildElement.style.top = (cellY * 25).toString() + "px";
                newChildElement.style.left = (cellX * 25).toString() + "px";
                const parentElement = document.querySelector('.opponent-field .ships');
                parentElement.appendChild(newChildElement);
                getSurroundingCoordinates(data.ships);
            } else if (data.messageType === "shotResponse" && data.response === 0) {
                initialTimeInSeconds = 30;
                document.querySelector(".opponent-nick").classList.add("highlight");
                document.querySelector(".mine-nick").classList.remove("highlight");
                document.querySelector(".mine-turn").innerHTML = "";
                document.querySelector(".opponent-turn").innerHTML = "Хід";
                console.log(`I miss ${coordinate}`);
                const newChildElement = document.createElement('div');
                newChildElement.classList.add("miss");
                newChildElement.style.top = (cellY * 25).toString() + "px";
                newChildElement.style.left = (cellX * 25).toString() + "px";
                const parentElement = document.querySelector('.opponent-field .ships');
                parentElement.appendChild(newChildElement);

                document.querySelector(".game-search-spinner").style.visibility = "visible";
                requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/acc/battle/server.php",
                    {
                        messageId: 11,
                        messageType: "shotRequest",
                        createDate: new Date(),
                        request: 0,
                        turn: turnCounter
                    }).then(data => {
                    turnCounter++;
                    let shipsCoordinates = localStorage.getItem("shipCoords");

                    if (checkCoordinateInJSON(shipsCoordinates, data.request)) {
                        initialTimeInSeconds = 30;
                        document.querySelector(".mine-turn").innerHTML = "Хід";
                        document.querySelector(".opponent-turn").innerHTML = "";
                        document.querySelector(".mine-nick").classList.add("highlight");
                        document.querySelector(".opponent-nick").classList.remove("highlight");
                        const newChildElement = document.createElement('div');
                        newChildElement.classList.add("hit");
                        newChildElement.style.top = ((parseInt(data.request.slice(1)) - 1) * 25).toString() + "px";
                        console.log(newChildElement.style.top)
                        newChildElement.style.left = (((data.request[0]).charCodeAt(0) - 97) * 25).toString() + "px";
                        const parentElement = document.querySelector('.mine-field .ships');
                        parentElement.appendChild(newChildElement);
                        document.querySelector(".game-search-spinner").style.visibility = "hidden";
                        requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/acc/battle/server.php",
                            {
                                messageId: 11,
                                messageType: "shotResponse",
                                createDate: new Date(),
                                coordinate: data.request,
                                response: 1
                            }).then(data => {
                            console.log(data)
                            requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/acc/battle/server.php",
                                {
                                    messageId: 11,
                                    messageType: "shotRequest",
                                    createDate: new Date(),
                                    request: 0,
                                    turn: turnCounter
                                });
                        });
                    } else {
                        initialTimeInSeconds = 30;
                        document.querySelector(".game-search-spinner").style.visibility = "hidden";
                        document.querySelector(".mine-nick").classList.add("highlight");
                        document.querySelector(".opponent-nick").classList.remove("highlight");
                        document.querySelector(".mine-turn").innerHTML = "Хід";
                        document.querySelector(".opponent-turn").innerHTML = "";
                        const newChildElement = document.createElement('div');
                        newChildElement.classList.add("miss");
                        newChildElement.style.top = ((parseInt(data.request.slice(1)) - 1) * 25).toString() + "px";
                        newChildElement.style.left = (((data.request[0]).charCodeAt(0) - 97) * 25).toString() + "px";
                        const parentElement = document.querySelector('.mine-field .ships');
                        parentElement.appendChild(newChildElement);

                        requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/acc/battle/server.php",
                            {
                                messageId: 11,
                                messageType: "shotResponse",
                                createDate: new Date(),
                                coordinate: data.request,
                                response: 0
                            }).then(data => {
                            console.log(data)
                        });
                    }
                });
            }
        });
    }
});
