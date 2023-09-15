export function sendSquadron(squadron) {
    let outputObj = {};
    outputObj.messageId = 10;
    outputObj.messageType = "allShipCoordsToDB";
    outputObj.createDate = new Date();

    for (const key in squadron) {
        const ship = squadron[key];
        const coords = ship.arrDecks.map(deck => String.fromCharCode(97 + deck[1]) + (deck[0] + 1));
        const shipStart = coords[0];
        const orientation = ship.kx === 1 ? "vertical" : "horizontal";

        outputObj[key] = {
            coords,
            hits: ship.hits,
            shipStart,
            orientation
        };
    }

    localStorage.setItem("shipCoords", JSON.stringify(outputObj));
    window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/acc/battle";
}