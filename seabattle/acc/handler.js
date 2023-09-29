import {requestToDB} from "./prepareConnection.js";

window.onload = () => {
    if(localStorage.getItem("login")===null) {
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/";
    } else {
        document.querySelector(".nickname").innerHTML = "";
        document.querySelector(".nickname").innerHTML = localStorage.getItem("login");
    }

    function exitFromPreparePage() {
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/";
        requestToDB("https://fmc2.avmg.com.ua/study/korotkyi/warship/index.php",
            {
                messageId: 9,
                messageType: "exitFromPage",
                createDate: new Date(),
                login: localStorage.getItem("login"),
            }).then(data => {});
        localStorage.removeItem("login");
        localStorage.removeItem("shipCoords");
    }

    document.querySelector(".previous-page").addEventListener("click", exitFromPreparePage);
    document.querySelector(".close").addEventListener("click", exitFromPreparePage);
}

window.onbeforeunload = () => {
    localStorage.removeItem("mine-field");
    localStorage.removeItem("opponent-field");
}