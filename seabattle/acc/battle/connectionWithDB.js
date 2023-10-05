import {requestToDB} from "../prepareConnection.js";

const opponentField = document.querySelector(".opponent-field > .ships");
// Получаем элемент таймера по его id
const timerElement = document.getElementById('timer');
//let initialTimeInSeconds = 30;
let timerInterval; // Переменная для хранения интервала таймера

// Функция для обновления таймера
function updateTimer() {
    if (parseInt(localStorage.getItem("initialTimeInSeconds")) <= 0) {
        clearInterval(timerInterval);
        timerElement.textContent = 'Час вийшов!';
        startTimer(false);
    } else {
        localStorage.setItem("initialTimeInSeconds", localStorage.getItem("initialTimeInSeconds") - 1);
        const gameInfo = JSON.parse(localStorage.getItem("gameInfo"));
        const yourTurn = JSON.parse(localStorage.getItem("yourTurn"));
        console.log(typeof yourTurn)
        if (localStorage.getItem("initialTimeInSeconds") === "1" && yourTurn === true) {
            console.log("afk")
            handlerOfYourTurn(null, null, "afk", gameInfo);
        }

        const seconds = localStorage.getItem("initialTimeInSeconds") % 60;
        timerElement.textContent = seconds + ' сек';
    }
}

// Функция для запуска таймера
function startTimer(yourTurn) {
    if (yourTurn === true) {
        document.querySelector(".mine-turn").innerHTML = "Хід";
        document.querySelector(".opponent-turn").innerHTML = "";
        document.querySelector(".mine-nick").classList.add("highlight");
        document.querySelector(".opponent-nick").classList.remove("highlight");
        document.querySelector(".game-search-spinner").style.visibility = "hidden";
    } else if (yourTurn === false) {
        document.querySelector(".opponent-turn").innerHTML = "Хід";
        document.querySelector(".mine-turn").innerHTML = "";
        document.querySelector(".opponent-nick").classList.add("highlight");
        document.querySelector(".mine-nick").classList.remove("highlight");
        document.querySelector(".game-search-spinner").style.visibility = "visible";
    }
    if(!(JSON.parse(localStorage.getItem("reload")))) {
        localStorage.setItem("initialTimeInSeconds", 30); // Устанавливаем начальное время
    }
    clearInterval(timerInterval); // Очищаем предыдущий интервал, если он существует
    timerInterval = setInterval(updateTimer, 1000); // Запускаем таймер с интервалом 1 секунда
}

function getSurroundingCoordinates(coords, shotResponse, selector) {
    let surroundingCoords = [];

    let shipElement = document.createElement('div');
    shotResponse = parseInt(shotResponse);
    const parentElement = document.querySelector(selector);
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
    shipElement.style.top = ((parseInt(coords[0].slice(1)) - 1) * 25).toString() + "px";
    shipElement.style.left = (((coords[0][0]).charCodeAt(0) - 97) * 25).toString() + "px";

    if (shotResponse === 21) {
        shipElement.classList.add('right');
    } else if (shotResponse === 22) {
        shipElement.classList.add('down');
    } else if (shotResponse === 23) {
        shipElement.classList.add('left');
    } else if (shotResponse === 24) {
        shipElement.classList.add('up');
        shipElement.style.top = ((parseInt(coords[0].slice(1)) - 2 + coords.length) * 25).toString() + "px";
    }

    for (const coord of coords) {
        const col = coord[0];
        const row = coord.slice(1);
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
    for (let deck of surroundingCoords) {
        const newChildElement = document.createElement('div');
        newChildElement.classList.add("miss");
        newChildElement.style.top = ((parseInt(deck.slice(1)) - 1) * 25).toString() + "px";
        newChildElement.style.left = (((deck[0]).charCodeAt(0) - 97) * 25).toString() + "px";
        parentElement.appendChild(newChildElement);
    }
    if (selector === ".opponent-field .ships") {
        parentElement.appendChild(shipElement);
    }
}

opponentField.addEventListener("click", clickOnField);

const gameInfo = JSON.parse(localStorage.getItem("gameInfo"));
const yourTurn = JSON.parse(localStorage.getItem("yourTurn"));
if (localStorage.getItem("mine-field") && localStorage.getItem("opponent-field")) {
    document.querySelector(".mine-field .ships").innerHTML = JSON.parse(localStorage.getItem("mine-field"));
    document.querySelector(".opponent-field .ships").innerHTML = JSON.parse(localStorage.getItem("opponent-field"));
}

console.log(typeof yourTurn)
if (yourTurn === true) {
    startTimer(true);
} else if (yourTurn === false) {
    startTimer(false);
    handlerOfOpponentTurn(gameInfo);
}

function clickOnField(e) {
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
        handlerOfYourTurn(cellY, cellX, coordinate, gameInfo)
    }
}

function handlerOfOpponentTurn(gameInfo) {
    const login = localStorage.getItem("login");
    localStorage.setItem("reload", false);
    localStorage.setItem("mine-field", JSON.stringify(document.querySelector(".mine-field .ships").innerHTML));
    localStorage.setItem("opponent-field", JSON.stringify(document.querySelector(".opponent-field .ships").innerHTML));

    //ToDo:
    requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/index.php",
        {
            messageId: 15,
            messageType: "shotResponseCoords",
            createDate: new Date(),
            gameId: gameInfo.game_id,
            login: login,
            opponent: gameInfo.opponent_login
        }).then(data => {
        if (data.winner !== "") {
            localStorage.setItem("isWinner", JSON.stringify(data.winner === localStorage.getItem("login")));
            localStorage.setItem("mine-field", JSON.stringify(document.querySelector(".mine-field .ships").innerHTML));
            localStorage.setItem("opponent-field", JSON.stringify(document.querySelector(".opponent-field .ships").innerHTML));
            window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/acc/result-battle/";
        }
        if (data.messageType === "shotResponseCoords" && data.yourTurn === 1) {
            localStorage.setItem("yourTurn", true);
            if (data.shotCoords !== "afk") {
                const newChildElement = document.createElement('div');
                newChildElement.classList.add("miss");
                newChildElement.style.top = ((parseInt(data.shotCoords.slice(1)) - 1) * 25).toString() + "px";
                newChildElement.style.left = (((data.shotCoords[0]).charCodeAt(0) - 97) * 25).toString() + "px";
                const parentElement = document.querySelector('.mine-field .ships');
                parentElement.appendChild(newChildElement);
            } else {
                const opponentSkip = document.querySelector(".opponent-skip");
                opponentSkip.innerHTML = (parseInt(opponentSkip.innerText) - 1).toString();
            }
            startTimer(true);
        } else if (data.messageType === "shotResponseCoords" && data.yourTurn === 0) {
            localStorage.setItem("yourTurn", false);
            const newChildElement = document.createElement('div');
            newChildElement.classList.add("hit");
            newChildElement.style.top = ((parseInt(data.shotCoords.slice(1)) - 1) * 25).toString() + "px";
            newChildElement.style.left = (((data.shotCoords[0]).charCodeAt(0) - 97) * 25).toString() + "px";
            const parentElement = document.querySelector('.mine-field .ships');
            parentElement.appendChild(newChildElement);
            if (parseInt(data.shotResponse / 10) === 2) {
                const newChildElement = document.createElement('div');
                newChildElement.classList.add("hit");
                newChildElement.style.top = ((parseInt(data.shotCoords.slice(1)) - 1) * 25).toString() + "px";
                newChildElement.style.left = (((data.shotCoords[0]).charCodeAt(0) - 97) * 25).toString() + "px";
                const parentElement = document.querySelector('.mine-field .ships');
                parentElement.appendChild(newChildElement);
                getSurroundingCoordinates(data.ships, data.shotResponse, '.mine-field .ships');
            }
            startTimer(false);
            handlerOfOpponentTurn(gameInfo);
        }
    });
}

function handlerOfYourTurn(cellY, cellX, coordinate, gameInfo) {
    localStorage.setItem("reload", false);
    localStorage.setItem("mine-field", JSON.stringify(document.querySelector(".mine-field .ships").innerHTML));
    localStorage.setItem("opponent-field", JSON.stringify(document.querySelector(".opponent-field .ships").innerHTML));
    const login = localStorage.getItem("login");

    //ToDO:
    requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/index.php",
        {
            messageId: 13,
            messageType: "shotRequestCoords",
            createDate: new Date(),
            shotCoords: coordinate,
            gameId: gameInfo.game_id,
            login: login,
            opponent: gameInfo.opponent_login
        }).then(data => {
        if (data.winner !== "") {
            localStorage.setItem("isWinner", JSON.stringify(data.winner === localStorage.getItem("login")));
            localStorage.setItem("mine-field", JSON.stringify(document.querySelector(".mine-field .ships").innerHTML));
            localStorage.setItem("opponent-field", JSON.stringify(document.querySelector(".opponent-field .ships").innerHTML));
            window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/acc/result-battle/";
        }
        if (data.messageType === "shotResponseCoords" && data.yourTurn === 1) {
            localStorage.setItem("yourTurn", true);
            const newChildElement = document.createElement('div');
            newChildElement.classList.add("hit");
            newChildElement.style.top = (cellY * 25).toString() + "px";
            newChildElement.style.left = (cellX * 25).toString() + "px";
            const parentElement = document.querySelector('.opponent-field .ships');
            parentElement.appendChild(newChildElement);
            if (parseInt(data.shotResponse / 10) === 2) {
                const newChildElement = document.createElement('div');
                newChildElement.classList.add("hit");
                newChildElement.style.top = (cellY * 25).toString() + "px";
                newChildElement.style.left = (cellX * 25).toString() + "px";
                const parentElement = document.querySelector('.opponent-field .ships');
                parentElement.appendChild(newChildElement);
                console.log(data.ships, data.shotResponse);
                getSurroundingCoordinates(data.ships, data.shotResponse, '.opponent-field .ships');
            }
            startTimer(true);
        } else if (data.messageType === "shotResponseCoords" && data.yourTurn === 0) {
            localStorage.setItem("yourTurn", false);
            const newChildElement = document.createElement('div');
            newChildElement.classList.add("miss");
            newChildElement.style.top = (cellY * 25).toString() + "px";
            newChildElement.style.left = (cellX * 25).toString() + "px";
            const parentElement = document.querySelector('.opponent-field .ships');
            if (cellX !== null || cellY !== null) {
                parentElement.appendChild(newChildElement);
            } else {
                const mineSkip = document.querySelector(".mine-skip");
                mineSkip.innerHTML = (parseInt(mineSkip.innerText) - 1).toString();
            }
            startTimer(false);
            handlerOfOpponentTurn(gameInfo);
        }
    });
}
