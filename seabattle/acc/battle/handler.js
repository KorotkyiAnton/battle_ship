import {requestToDB} from "../prepareConnection.js";

window.onload = () => {
    setInterval(
        function (){requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/index.php",
            {
                messageId: 20,
                messageType: "lastUpdate",
                createDate: new Date(),
                login: localStorage.getItem("login"),
            }).then(data => {
        })},
        1000);

    if (localStorage.getItem("login") === null) {
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/";
    } else {
        document.querySelector(".mine-nick").innerHTML = "";
        document.querySelector(".mine-nick").innerHTML = localStorage.getItem("login");
        document.querySelector(".opponent-nick").innerHTML = JSON.parse(localStorage.getItem("gameInfo")).opponent_login;
    }

    function exitFromBattlePage() {
        const gameInfo = JSON.parse(localStorage.getItem("gameInfo"));

        requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/index.php",
            {
                messageId: 9,
                messageType: "userCancelPage",
                createDate: new Date(),
                login: gameInfo.opponent_login,
                gameId: gameInfo.game_id
            }).then(data => {
        });
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/";
        localStorage.removeItem("login");
        localStorage.removeItem("shipCoords");
    }

    function previousPage() {
        const gameInfo = JSON.parse(localStorage.getItem("gameInfo"));
        history.back();
        requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/index.php",
            {
                messageId: 9,
                messageType: "userEnterPreviousPage",
                createDate: new Date(),
                login: gameInfo.opponent_login,
                gameId: gameInfo.game_id
            }).then(data => {
        });
    }

    const confirmationOverlay = document.getElementById('confirmationOverlay');
    const confirmButton = document.getElementById('confirmButton');
    const cancelButton = document.getElementById('cancelButton');

    // Обработчик события клика по кнопке .close
    document.querySelector('.close').addEventListener('click', function () {
        confirmationOverlay.style.display = 'flex'; // Показываем окно
    });

    // Обработчик события клика по кнопке "Так"
    confirmButton.addEventListener('click', function () {
        exitFromBattlePage();
        confirmationOverlay.style.display = 'none'; // Скрываем окно
    });

    // Обработчик события клика по кнопке "Ні"
    cancelButton.addEventListener('click', function () {
        confirmationOverlay.style.display = 'none'; // Скрываем окно
    });

    document.querySelector(".previous-page").addEventListener("click", previousPage);
}