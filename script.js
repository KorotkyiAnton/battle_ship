function showStatus(statusBad, statusNormal, statusGood) {
    document.querySelector(".status-bad").style.display = statusBad;
    document.querySelector(".status-normal").style.display = statusNormal;
    document.querySelector(".status-good").style.display = statusGood;
}

function validateLogin(login) {
    let textStatus = document.querySelector(".status-text");
    const startButton = document.querySelector(".big-purple-button");
    textStatus.innerHTML = "";
    let isError = false;

    if (login.length < 3 || login.length > 10) {
        showStatus("block", "none", "none");
        textStatus.innerHTML += "Нажаль, помилка - дозволена довжина нікнейму від 3 до 10 символів;<br>";
        isError = true;
    }

    if (!/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ\-'_]+$/.test(login)) {
        showStatus("block", "none", "none");
        textStatus.innerHTML += "На жаль, помилка - нікнейм може містити літери (zZ-яЯ),цифри (0-9), спецсимволи (Word space, -, ', _);<br>";
        isError = true;
    }

    if (!/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ]/.test(login[0])) {
        showStatus("block", "none", "none");
        textStatus.innerHTML += "Нікнейм повинен починатися з літер чи цифр;<br>";
        isError = true;
    }

    if (!/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ]/.test(login[login.length-1])) {
        showStatus("block", "none", "none");
        textStatus.innerHTML += "Нікнейм повинен закінчуватися на літеру чи цифру;<br>";
        isError = true;
    }

    if (!isError) {
        showStatus("none", "none", "block");
        textStatus.innerHTML = "";
        startButton.removeAttribute('disabled');
    }
}

window.onload = function () {
    const startButton = document.querySelector(".big-purple-button");
    startButton.setAttribute('disabled', '');
    const nicknameInput = document.querySelector(".login-field");
    let timeout;
    showStatus("none", "none", "none");

    nicknameInput.oninput = () => {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
                validateLogin(nicknameInput.value.trim());
            },
            1000);
    };
    nicknameInput.onchange = () => {
        showStatus("none", "block", "none");
        validateLogin(nicknameInput.value.trim());
    };
};