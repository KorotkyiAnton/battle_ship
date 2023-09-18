import {requestToDB} from "../prepareConnection.js";

const opponentField = document.querySelector(".opponent-field > .ships");
// Получаем элемент таймера по его id
const timerElement = document.getElementById('timer');
// Устанавливаем начальное значение времени (в секундах)
let initialTimeInSeconds = 30;

// Функция для обновления таймера
function updateTimer() {
    // Если время истекло, останавливаем таймер
    if (initialTimeInSeconds <= 0) {
        clearInterval(timerInterval);
        timerElement.textContent = 'Час вийшов!';
        timerElement.textContent = '30 сек';
        document.querySelector(".opponent-nick").classList.add("highlite");
        document.querySelector(".mine-nick").classList.remove("highlite");
        document.querySelector(".opponent-field").setAttribute('disabled', '');
        document.querySelector(".skip span").innerHTML =
            (parseInt(document.querySelector(".skip span").innerText) - 1).toString();
    } else {
        // В противном случае, уменьшаем время на 1 секунду
        initialTimeInSeconds--;
        // Преобразуем время в формат "мм:сс" и обновляем текст на странице
        const minutes = Math.floor(initialTimeInSeconds / 60);
        const seconds = initialTimeInSeconds % 60;
        timerElement.textContent = seconds + ' сек';
    }
}

// Обновляем таймер каждую секунду
let timerInterval = setInterval(updateTimer, 1000);
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

opponentField.addEventListener("click", function (e) {
    if ((e.target.tagName === "DIV" ||
            e.target.classList.contains("ships")) &&
        !e.target.classList.contains("miss") &&
        !e.target.classList.contains("hit")
    ) {
        initialTimeInSeconds = 30;
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
            if (data.messageType === "shotResponse" && data.response === 1) {
                console.log(`I hit ${coordinate}`);
                const newChildElement = document.createElement('div');
                newChildElement.classList.add("hit");
                newChildElement.style.top = (cellY * 25).toString() + "px";
                newChildElement.style.left = (cellX * 25).toString() + "px";
                const parentElement = document.querySelector('.opponent-field .ships');
                parentElement.appendChild(newChildElement);
            } else if (data.messageType === "shotResponse" && data.response === 2) {
                console.log(`I kill ${coordinate}`);
                const newChildElement = document.createElement('div');
                newChildElement.classList.add("hit");
                newChildElement.style.top = (cellY * 25).toString() + "px";
                newChildElement.style.left = (cellX * 25).toString() + "px";
                const parentElement = document.querySelector('.opponent-field .ships');
                parentElement.appendChild(newChildElement);
            } else if (data.messageType === "shotResponse" && data.response === 0) {
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
