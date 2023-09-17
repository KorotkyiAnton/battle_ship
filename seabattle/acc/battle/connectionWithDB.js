import {requestToDB} from "../connectionWithDB.js";

const opponentField = document.querySelector(".opponent-field > .ships");

opponentField.addEventListener("click", function (e) {
    if(e.target.tagName === "DIV" || e.target.classList.includes("ships")) {
        const rect = opponentField.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const cellX = Math.floor(x / 25);
        const cellY = Math.floor(y / 25);

        const coordinate = String.fromCharCode(97 + cellX) + (cellY+1);
        console.log(coordinate)

        requestToDB("http://localhost:63342/battle_ship/seabattle/acc/battle/server.php",
            {
            messageId: 11,
            messageType: "shotRequest",
            createDate: new Date(),
            request: coordinate
        }).then(data => {
            if(data.messageType === "shotResponse") {
                console.log(data.response)
            }
        });
    }
})