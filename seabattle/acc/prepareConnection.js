export function sendSquadron(squadron) {
    let outputObj = {};

    for (const key in squadron) {
        const ship = squadron[key];
        const coords = ship.arrDecks.map(deck => String.fromCharCode(97 + deck[1]) + (deck[0] + 1));
        const shipStart = coords[0];
        let orientation = "";
        if (ship.kx === -1) {
            orientation = "up";
        } else if (ship.kx === 1) {
            orientation = "down";
        } else if (ship.ky === -1) {
            orientation = "left";
        } else if (ship.ky === 1) {
            orientation = "right";
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

    console.log(JSON.parse(localStorage.getItem("shipCoords")));

    requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/index.php",
        {
            messageId: 9,
            messageType: "requestIsUsersInQueue",
            createDate: new Date(),
            login: localStorage.getItem("login"),
            continueSearch: true,
            shipCoordinates: JSON.parse(localStorage.getItem("shipCoords"))
        }).then(data => {
        if (data.messageType === "gameCreateInfo" || data.messageType === "gameConnectInfo") {
            data.shipCoordinates = JSON.parse(localStorage.getItem("shipCoords"));
            localStorage.setItem("gameInfo", JSON.stringify(data));
            window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/acc/battle";
            document.querySelector(".in-game").style.visibility = "visible";
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

export async function requestToDB(url, requestData, recursion = false) {
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
            throw error;
        });
}