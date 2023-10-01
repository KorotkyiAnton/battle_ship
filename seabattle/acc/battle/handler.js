import {requestToDB, startTimer} from "../prepareConnection.js";

window.onload = () => {
    setInterval(
        //ToDo:
        function () {
            requestToDB("http://localhost/alpha-battle/",
                {
                    messageId: 20,
                    messageType: "lastUpdate",
                    createDate: new Date(),
                    login: localStorage.getItem("login"),
                    opponent: JSON.parse(localStorage.getItem("gameInfo")).opponent_login,
                }).then(data => {
                console.log(data.userOnline)
                if (data.userOnline >= 3 && data.userOnline < 90) {
                    document.querySelector(".opponent-lose-control").style.visibility = "visible";
                    localStorage.setItem("initialTimeInSeconds", 30);
                } else if (data.userOnline >= 90) {
                    requestToDB("http://localhost/alpha-battle/",
                        {
                            messageId: 21,
                            messageType: "updateWinner",
                            createDate: new Date(),
                            login: localStorage.getItem("login"),
                            gameId: JSON.parse(localStorage.getItem("gameInfo")).game_id,
                        }).then(data => {
                    });
                } else {
                    document.querySelector(".opponent-lose-control").style.visibility = "hidden";
                }
            })
        },
        1000);

    setInterval(
        //ToDo:
        function () {
            requestToDB("http://localhost/alpha-battle/",
                {
                    messageId: 22,
                    messageType: "getWinner",
                    createDate: new Date(),
                    gameId: JSON.parse(localStorage.getItem("gameInfo")).game_id,
                    login: localStorage.getItem("login"),
                    opponent: JSON.parse(localStorage.getItem("gameInfo")).opponent_login,
                }).then(data => {
                console.log(data.winner)
                if (data.winner !== "") {
                    localStorage.setItem("isWinner", JSON.stringify(data.winner === localStorage.getItem("login")));
                    localStorage.setItem("mine-field", JSON.stringify(document.querySelector(".mine-field .ships").innerHTML));
                    localStorage.setItem("opponent-field", JSON.stringify(document.querySelector(".opponent-field .ships").innerHTML));
                    window.location.href = "http://localhost/alpha-battle/seabattle/acc/result-battle/";
                }
            })
        },
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

        //ToDO:
        requestToDB("http://localhost/alpha-battle/",
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
        //ToDo:
        requestToDB("http://localhost/alpha-battle/",
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

window.onbeforeunload = () => {
    localStorage.setItem("reload", true);
}