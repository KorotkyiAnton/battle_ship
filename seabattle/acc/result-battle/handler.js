import {requestToDB} from "../prepareConnection.js";
import {placeShipsOnField} from "../battle/battle.js";

window.onload = () => {
    const nicks = document.querySelectorAll(".loc-nick");
    const nick = localStorage.getItem("login");
    for (let i = 0; i < nicks.length; i++) {
        nicks[i].innerHTML = nick + ", ";
    }
    document.querySelector(".mine-nick").innerHTML = localStorage.getItem("login");
    document.querySelector(".opponent-nick").innerHTML = JSON.parse(localStorage.getItem("gameInfo")).opponent_login;
    console.log(localStorage.getItem("isWinner"))
    if (localStorage.getItem("isWinner") !== null && localStorage.getItem("isWinner") === "true") {
        document.querySelector(".u-win").style.display = "flex";
    } else if (localStorage.getItem("isWinner") !== null && localStorage.getItem("isWinner") === "false") {
        document.querySelector(".u-lose").style.display = "flex";
    }

    document.querySelector(".mine-field .ships").innerHTML = JSON.parse(localStorage.getItem("mine-field"));
    document.querySelector(".opponent-field .ships").innerHTML = JSON.parse(localStorage.getItem("opponent-field"));
    localStorage.removeItem("mine-field");
    localStorage.removeItem("opponent-field");

    console.log(JSON.parse(localStorage.getItem("gameInfo")).opponent_login)
    requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/index.php",
        {
            messageId: 11,
            messageType: "localShipStoreEmpty",
            createDate: new Date(),
            login: JSON.parse(localStorage.getItem("gameInfo")).opponent_login,
        }).then(data => {
        console.log(data.shipCoordinates);
        placeShipsOnField(data.shipCoordinates, '.opponent-field');
    });

    function exitFromPreparePage() {
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/";
        localStorage.removeItem("login");
        localStorage.removeItem("shipCoords");
    }

    function playAgain() {
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/acc";
        localStorage.removeItem("shipCoords");
    }

    document.querySelector(".exit-after-game").addEventListener("click", exitFromPreparePage);
    document.querySelector(".play-again").addEventListener("click", playAgain);
}