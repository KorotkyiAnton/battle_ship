export function sendSquadron(squadron) {
    let outputObj = {};
    outputObj.messageId = 10;
    outputObj.messageType = "allShipCoordsToDB";
    outputObj.createDate = new Date();

    for (const key in squadron) {
        const ship = squadron[key];
        const coords = ship.arrDecks.map(deck => String.fromCharCode(97 + deck[1]) + (deck[0] + 1));
        const shipStart = coords[0];
        let orientation = "";
        if (ship.kx === -1) {
            orientation = "north";
        } else if (ship.kx === 1) {
            orientation = "south";
        } else if (ship.ky === -1) {
            orientation = "west";
        } else if (ship.ky === 1) {
            orientation = "east";
        }

        outputObj[key] = {
            coords,
            hits: ship.hits,
            shipStart,
            orientation
        };
    }

    localStorage.setItem("shipCoords", JSON.stringify(outputObj));
    return JSON.stringify(outputObj)
}

let countdownTimer;
let timerSeconds = 90;
export let isTimerRunning = false;

export function startTimer(timerElement, otherElementsToDisable, readyButton, squadron) {
    isTimerRunning = true;
    disableOtherElements(otherElementsToDisable, true);

    requestToDB("http://localhost:63342/battle_ship/seabattle/acc/server.php",
        {
        messageId: 9,
        messageType: "requestIsUsersInQueue",
        createDate: new Date(),
        id: Math.random() * 100,
        firstTurn: Math.random() * 100,
        ships: squadron
    }).then(data => {
        if(data.messageType === "gameCreateInfo" || data.messageType === "gameConnectInfo") {
            localStorage.setItem("gameInfo", JSON.stringify(data));
            //window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/acc/battle";
            window.location.href = "http://localhost:63342/battle_ship/seabattle/acc/battle/";
        }
    });

    countdownTimer = setInterval(function () {
        timerSeconds--;
        timerElement.textContent = `${timerSeconds} сек`;

        if (timerSeconds <= 0) {
            clearInterval(countdownTimer);
            timerElement.textContent = 'Опонента не знайдено';
            isTimerRunning = false;
            disableOtherElements(otherElementsToDisable, false);
            readyButton.value = 'Готовий';
        }
    }, 1000);
}

export function stopTimer(timerElement, otherElementsToDisable, readyButton) {
    clearInterval(countdownTimer);
    isTimerRunning = false;
    timerSeconds = 90;
    timerElement.textContent = `${timerSeconds} сек`;
    disableOtherElements(otherElementsToDisable, false);
    readyButton.value = 'Готовий';
}

function disableOtherElements(elements, disable) {
    elements.forEach(function (element) {
        if (disable) {
            element.setAttribute('disabled', '');
            document.querySelector(".game-search-spinner").style.visibility = "visible";
        } else {
            element.removeAttribute('disabled');
            document.querySelector(".game-search-spinner").style.visibility = "hidden";
        }
    });
}

export function requestToDB(url, requestData) {
    const requestBody = JSON.stringify(requestData);

    const requestOptions = {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: requestBody
    };

    return fetch(url, requestOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error("Ошибка HTTP: " + response.status);
            }
            return response.json();
        })
        .catch(error => {
            console.error("Произошла ошибка:", error);
            throw error;
        });
}