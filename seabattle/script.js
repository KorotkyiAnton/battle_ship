let textStatus = document.querySelector(".status-text");
let popupErr = document.querySelector(".popup-err");

function showStatus(statusBad, statusNormal, statusGood) {
    document.querySelector(".status-bad").style.display = statusBad;
    document.querySelector(".status-normal").style.display = statusNormal;
    document.querySelector(".status-good").style.display = statusGood;

    const startButton = document.querySelector(".big-purple-button");
    if(statusBad === "block") {
        startButton.setAttribute('disabled', "");
    }
}

function checkLoginUnique(login) {
    // Создаем объект с данными для запроса
    const requestData = {
        messageId: 1,
        messageType: "isLoginUnique",
        createDate: new Date(),
        isUnique: false,
        login: login
    };

    const requestBody = JSON.stringify(requestData);

    const url = "https://fmc2.avmg.com.ua/study/korotkyi/warship/server.php";

    const requestOptions = {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: requestBody
    };

    fetch(url, requestOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error("Ошибка HTTP: " + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (!(localStorage.getItem("login") === null) && localStorage.getItem("login") === data.login) {
                if(data.status === 0 || data.status=== 1) {
                    window.location.href = "acc";
                } else if(data.status === 2) {
                    window.location.href = "acc/battle";
                }
            } else if (!data.isUnique) {
                textStatus.style.visibility = "visible";
                showStatus("block", "none", "none");
                textStatus.innerHTML = "";
                popupErr.innerHTML = "";
                textStatus.innerHTML += "На жаль, помилка - данний нікнейм вже зайнятий, спробуйте іншій.<br>";
                popupErr.innerHTML += "На жаль, помилка - данний нікнейм вже зайнятий, спробуйте іншій.<br>";
            } else {
                localStorage.setItem("login", data.login)
                window.location.href = "acc";
            }
        })
        .catch(error => {
            console.error("Произошла ошибка:", error);
            throw error;
        });
}

function validateLogin(login) {
    textStatus.style.visibility = "visible";
    const startButton = document.querySelector(".big-purple-button");
    textStatus.innerHTML = "";
    popupErr.innerHTML = "";
    let isError = false;

    if (login.length < 3 || login.length > 10) {
        showStatus("block", "none", "none");
        textStatus.innerHTML += "Нажаль, помилка - дозволена довжина нікнейму від 3 до 10 символів;<br>";
        popupErr.innerHTML += "Нажаль, помилка - дозволена довжина нікнейму від 3 до 10 символів;<br>";
        isError = true;
    }

    if (!/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ\-'_]+$/.test(login)) {
        showStatus("block", "none", "none");
        textStatus.innerHTML += "На жаль, помилка - нікнейм може містити літери (zZ-яЯ),цифри (0-9), спецсимволи (Word space, -, ', _);<br>";
        popupErr.innerHTML += "На жаль, помилка - нікнейм може містити літери (zZ-яЯ),цифри (0-9), спецсимволи (Word space, -, ', _);<br>";
        isError = true;
    }

    if (!/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ]/.test(login[0])) {
        showStatus("block", "none", "none");
        textStatus.innerHTML += "Нікнейм повинен починатися з літер чи цифр;<br>";
        popupErr.innerHTML += "Нікнейм повинен починатися з літер чи цифр;<br>";
        isError = true;
    }

    if (!/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ]/.test(login[login.length - 1])) {
        showStatus("block", "none", "none");
        textStatus.innerHTML += "Нікнейм повинен закінчуватися на літеру чи цифру;<br>";
        popupErr.innerHTML += "Нікнейм повинен закінчуватися на літеру чи цифру;<br>";
        isError = true;
    }

    if (!isError) {
        showStatus("none", "none", "block");
        textStatus.innerHTML = "";
        textStatus.style.visibility = "hidden";
        startButton.removeAttribute('disabled');
    }
}

window.onload = function () {
    const startButton = document.querySelector(".big-purple-button");
    startButton.setAttribute('disabled', '');
    const nicknameInput = document.querySelector(".login-field");
    let timeout;

    textStatus.style.visibility = "hidden";

    showStatus("none", "none", "none");

    startButton.onclick = () => {
        checkLoginUnique(nicknameInput.value.trim())
    }

    nicknameInput.oninput = () => {
        showStatus("none", "block", "none");
        clearTimeout(timeout);
        timeout = setTimeout(function () {
                validateLogin(nicknameInput.value.trim());
            },
            1000);
    };
    nicknameInput.onchange = () => {
        validateLogin(nicknameInput.value.trim());
    };
};