let startGame = false;
let isHandlerPlacement = false;

const getElement = id => document.getElementById(id);
const getCoordinates = el => {
    const coords = el.getBoundingClientRect();
    return {
        left: coords.left + window.pageXOffset,
        right: coords.right + window.pageXOffset,
        top: coords.top + window.pageYOffset,
        bottom: coords.bottom + window.pageYOffset
    };
};

const humanfield = getElement('ships');

function sendSquadron(squadron) {
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
}

class Field {
    static FIELD_SIDE = 250;
    static SHIP_SIDE = 25;
    static SHIP_DATA = {
        fourdeck: [1, 4],
        tripledeck: [2, 3],
        doubledeck: [3, 2],
        singledeck: [4, 1]
    };

    constructor(field) {
        this.field = field;
        this.squadron = {};
        this.matrix = [];
        let {left, right, top, bottom} = getCoordinates(this.field);
        this.fieldLeft = left;
        this.fieldRight = right;
        this.fieldTop = top;
        this.fieldBottom = bottom;
    }

    static createMatrix() {
        return [...Array(10)].map(() => Array(10).fill(0));
    }

    static getRandom = n => Math.floor(Math.random() * (n + 1));

    cleanField() {
        while (this.field.firstChild) {
            this.field.removeChild(this.field.firstChild);
        }
        this.squadron = {};
        this.matrix = Field.createMatrix();
    }

    randomLocationShips() {
        for (let type in Field.SHIP_DATA) {
            let count = Field.SHIP_DATA[type][0];
            let decks = Field.SHIP_DATA[type][1];
            for (let i = 0; i < count; i++) {
                let options = this.getCoordsDecks(decks);
                options.decks = decks;
                options.shipname = type + String(i + 1);
                const ship = new Ships(this, options);
                ship.createShip();
            }
        }
    }

    getCoordsDecks(decks) {
        let kx = Field.getRandom(1), ky = (kx === 0) ? 1 : 0,
            x, y;

        if (kx === 0) {
            x = Field.getRandom(9);
            y = Field.getRandom(10 - decks);
        } else {
            x = Field.getRandom(10 - decks);
            y = Field.getRandom(9);
        }

        const obj = {x, y, kx, ky}
        const result = this.checkLocationShip(obj, decks);
        if (!result) return this.getCoordsDecks(decks);
        return obj;
    }

    checkLocationShip(obj, decks) {
        let {x, y, kx, ky, fromX, toX, fromY, toY} = obj;
        kx = Math.abs(kx);
        ky = Math.abs(ky);

        fromX = (x === 0) ? x : x - 1;
        if (x + kx * decks === 10 && kx === 1) toX = x + kx * decks;
        else if (x + kx * decks < 10 && kx === 1) toX = x + kx * decks + 1;
        else if (x === 9 && kx === 0) toX = x + 1;
        else if (x < 9 && kx === 0) toX = x + 2;

        fromY = (y === 0) ? y : y - 1;
        if (y + ky * decks === 10 && ky === 1) toY = y + ky * decks;
        else if (y + ky * decks < 10 && ky === 1) toY = y + ky * decks + 1;
        else if (y === 9 && ky === 0) toY = y + 1;
        else if (y < 9 && ky === 0) toY = y + 2;
        console.log(String.fromCharCode(fromY + 97) + (fromX + 1), toX, String.fromCharCode(toY + 96))

        if (toX === undefined || toY === undefined || fromX < 0 || fromY < 0) return false;

        return this.matrix.slice(fromX, toX)
            .filter(arr => arr.slice(fromY, toY).includes(1))
            .length <= 0;

    }
}

class Ships {
    constructor(self, {x, y, kx, ky, decks, shipname}) {
        this.player = human;
        this.field = self.field;
        this.shipname = shipname;
        this.decks = decks;
        this.x = x;
        this.y = y;
        this.kx = kx;
        this.ky = ky;
        this.hits = 0;
        this.arrDecks = [];
    }

    static showShip(self, shipname, x, y, kx, ky, length) {
        const div = document.createElement('div');
        const classname = shipname.slice(0, -1);
        let dirClass = '';

        // Добавляем классы для сторон поворота корабля
        if (kx === -1 && ky === 0) {
            dirClass = ' north';
            x += length - 1;
        } else if (kx === 0 && ky === -1) {
            dirClass = ' west';
        } else if (kx === 1 && ky === 0) {
            dirClass = ' south';
        }

        div.setAttribute('id', shipname);
        div.className = `ship ${classname} ${dirClass}`;
        div.style.cssText = `left:${y * Field.SHIP_SIDE}px; top:${x * Field.SHIP_SIDE}px;`;
        self.field.appendChild(div);
    }

    createShip() {
        let {player, field, shipname, decks, x, y, kx, ky, hits, arrDecks, k = 0} = this;

        while (k < decks) {
            let kxForMatrix = Math.abs(kx);
            let kyForMatrix = Math.abs(ky);
            let i = x + k * kxForMatrix, j = y + k * kyForMatrix;

            player.matrix[i][j] = 1;
            arrDecks.push([i, j]);
            k++;
        }

        player.squadron[shipname] = {arrDecks, hits, x, y, kx, ky};
        if (player === human) {
            Ships.showShip(human, shipname, x, y, kx, ky, arrDecks.length);
            if (Object.keys(player.squadron).length === 10) {
                console.log(player.squadron)
                let readyButton = document.querySelector(".ready");
                readyButton.removeAttribute('disabled');
                readyButton.addEventListener("click", function () {
                    sendSquadron(player.squadron);
                });
            }
        }
    }
}

class Placement {
    static FRAME_COORDS = getCoordinates(humanfield);

    constructor() {
        this.dragObject = {};
        this.pressed = false;
    }

    static getShipName = el => el.getAttribute('id');
    static getCloneDecks = el => {
        const type = Placement.getShipName(el).slice(0, -1);
        return Field.SHIP_DATA[type][1];
    }

    setObserver() {
        if (isHandlerPlacement) return;
        document.addEventListener('mousedown', this.onMouseDown.bind(this));
        document.addEventListener('mousemove', this.onMouseMove.bind(this));
        document.addEventListener('mouseup', this.onMouseUp.bind(this));
        humanfield.addEventListener('contextmenu', this.rotationShip.bind(this));
        isHandlerPlacement = true;
    }

    onMouseDown(e) {
        if (e.which !== 1 || startGame) return;

        const el = e.target.closest('.ship');
        if (!el) return;

        this.pressed = true;

        this.dragObject = {
            el,
            parent: el.parentElement,
            next: el.nextElementSibling,
            downX: e.pageX,
            downY: e.pageY,
            left: el.offsetLeft,
            top: el.offsetTop,
            kx: 0,
            ky: 1
        };

        if (el.parentElement === humanfield) {
            const name = Placement.getShipName(el);
            this.dragObject.kx = human.squadron[name].kx;
            this.dragObject.ky = human.squadron[name].ky;
        }
    }

    onMouseMove(e) {
        if (!this.pressed || !this.dragObject.el) return;

        let {left, right, top, bottom} = getCoordinates(this.dragObject.el);

        if (!this.clone) {
            this.decks = Placement.getCloneDecks(this.dragObject.el);
            this.clone = this.creatClone({left, right, top, bottom}) || null;
            if (!this.clone) return;

            this.shiftX = this.dragObject.downX - left;
            this.shiftY = this.dragObject.downY - top;
            this.clone.style.zIndex = '1000';
            document.body.appendChild(this.clone);

            this.removeShipFromSquadron(this.clone);
        }

        let currentLeft = Math.round(e.pageX - this.shiftX),
            currentTop = Math.round(e.pageY - this.shiftY);
        this.clone.style.left = `${currentLeft}px`;
        this.clone.style.top = `${currentTop}px`;

        if (left >= Placement.FRAME_COORDS.left - 14 && right <= Placement.FRAME_COORDS.right + 14 && top >= Placement.FRAME_COORDS.top - 14 && bottom <= Placement.FRAME_COORDS.bottom + 14) {
            this.clone.classList.remove('unsuccess');
            this.clone.classList.add('success');

            const {x, y} = this.getCoordsCloneInMatrix({left, right, top, bottom});
            const obj = {
                x,
                y,
                kx: this.dragObject.kx,
                ky: this.dragObject.ky
            };

            const result = human.checkLocationShip(obj, this.decks);
            if (!result) {
                this.clone.classList.remove('success');
                this.clone.classList.add('unsuccess');
            }
        } else {
            this.clone.classList.remove('success');
            this.clone.classList.add('unsuccess');
        }
    }

    onMouseUp(e) {
        this.pressed = false;
        if (!this.clone) return;

        if (this.clone.classList.contains('unsuccess')) {
            this.clone.classList.remove('unsuccess');
            this.clone.rollback();
        } else {
            this.createShipAfterMoving();
        }
        this.removeClone();
    }

    rotationShip(e) {
        e.preventDefault();
        if (e.which !== 3 || startGame) return;

        const el = e.target.closest('.ship');
        const name = Placement.getShipName(el);

        if (human.squadron[name].decks === 1) return;
        const decks = human.squadron[name].arrDecks.length;

        const directions = [
            {kx: 0, ky: 1, x: human.squadron[name].x + decks - 1, y: human.squadron[name].y},  // Изначальное положение корабля
            {kx: 1, ky: 0, x: human.squadron[name].x, y: human.squadron[name].y},  // Поворот на 90 градусов
            {kx: 0, ky: -1, x: human.squadron[name].x, y: human.squadron[name].y - decks + 1}, // Поворот на 180 градусов
            {kx: -1, ky: 0, x: human.squadron[name].x - decks + 1, y: human.squadron[name].y + decks - 1}  // Поворот на 270 градусов
        ];

        const rollBack = [
            {kx: 0, ky: 1, x: human.squadron[name].x, y: human.squadron[name].y},
            {kx: 1, ky: 0, x: human.squadron[name].x, y: human.squadron[name].y},
            {kx: 0, ky: -1, x: human.squadron[name].x, y: human.squadron[name].y},
            {kx: -1, ky: 0, x: human.squadron[name].x, y: human.squadron[name].y}
        ];

        let obj = {
            x: human.squadron[name].x,
            y: human.squadron[name].y,
            kx: human.squadron[name].kx,
            ky: human.squadron[name].ky
        };

        // Находим индекс текущего направления корабля в массиве направлений
        const currentIndex = directions.findIndex(dir => dir.kx === obj.kx && dir.ky === obj.ky);

        // Вычисляем индекс следующего направления после поворота
        const nextIndex = (currentIndex + 1) % directions.length;


        // Обновляем координаты и направление корабля
        obj.kx = directions[nextIndex].kx;
        obj.ky = directions[nextIndex].ky;
        obj.x = directions[nextIndex].x;
        obj.y = directions[nextIndex].y;

        this.removeShipFromSquadron(el);
        human.field.removeChild(el);

        const result = human.checkLocationShip(obj, decks);

        if (!result) {
            console.log(currentIndex)
            obj.kx = rollBack[currentIndex].kx;
            obj.ky = rollBack[currentIndex].ky;
            obj.x = rollBack[currentIndex].x;
            obj.y = rollBack[currentIndex].y;
        }

        obj.shipname = name;
        obj.decks = decks;

        const ship = new Ships(human, obj);
        ship.createShip();

        if (!result) {
            const el = getElement(name);
            el.classList.add('unsuccess');
            setTimeout(() => {
                el.classList.remove('unsuccess')
            }, 750);
        }
    }

    creatClone() {
        const clone = this.dragObject.el;
        const oldPosition = this.dragObject;

        clone.rollback = () => {
            if (oldPosition.parent === humanfield) {
                clone.style.left = `${oldPosition.left}px`;
                clone.style.top = `${oldPosition.top}px`;
                clone.style.zIndex = '';
                oldPosition.parent.insertBefore(clone, oldPosition.next);
                this.createShipAfterMoving();
            } else {
                clone.removeAttribute('style');
                oldPosition.parent.insertBefore(clone, oldPosition.next);
            }
        };
        return clone;
    }

    removeClone() {
        delete this.clone;
        this.dragObject = {};
    }

    createShipAfterMoving() {
        const coords = getCoordinates(this.clone);
        let {left, top, x, y} = this.getCoordsCloneInMatrix(coords);
        this.clone.style.left = `${left}px`;
        this.clone.style.top = `${top}px`;
        humanfield.appendChild(this.clone);
        this.clone.classList.remove('success');

        const options = {
            shipname: Placement.getShipName(this.clone),
            x,
            y,
            kx: this.dragObject.kx,
            ky: this.dragObject.ky,
            decks: this.decks
        };

        const ship = new Ships(human, options);
        ship.createShip();
        humanfield.removeChild(this.clone);
    }

    getCoordsCloneInMatrix({left, right, top, bottom} = coords) {
        let computedLeft = left - Placement.FRAME_COORDS.left,
            computedRight = right - Placement.FRAME_COORDS.left,
            computedTop = top - Placement.FRAME_COORDS.top,
            computedBottom = bottom - Placement.FRAME_COORDS.top;

        const obj = {};

        let ft = (computedTop < 0) ? 0 : (computedBottom > Field.FIELD_SIDE) ? Field.FIELD_SIDE - Field.SHIP_SIDE : computedTop;
        let fl = (computedLeft < 0) ? 0 : (computedRight > Field.FIELD_SIDE) ? Field.FIELD_SIDE - Field.SHIP_SIDE * this.decks : computedLeft;

        obj.top = Math.round(ft / Field.SHIP_SIDE) * Field.SHIP_SIDE;
        obj.left = Math.round(fl / Field.SHIP_SIDE) * Field.SHIP_SIDE;
        obj.x = obj.top / Field.SHIP_SIDE;
        obj.y = obj.left / Field.SHIP_SIDE;

        return obj;
    }

    removeShipFromSquadron(el) {
        const name = Placement.getShipName(el);
        if (!human.squadron[name]) return;

        const arr = human.squadron[name].arrDecks;
        for (let coords of arr) {
            const [x, y] = coords;
            human.matrix[x][y] = 0;
        }
        delete human.squadron[name];
    }
}


const shipsCollection = getElement('docks');
const initialShips = document.querySelector('.initial-ships');


const human = new Field(humanfield);

window.addEventListener("load", function () {
    human.cleanField();
    let initialShipsClone = '';
    initialShipsClone = initialShips.cloneNode(true);
    shipsCollection.appendChild(initialShipsClone);
    initialShipsClone.hidden = false;
    const placement = new Placement();
    placement.setObserver();
})

document.querySelector(".random").addEventListener('click', function (e) {
    human.cleanField();

    shipsCollection.innerHTML = "";
    human.randomLocationShips();

    const placement = new Placement();
    placement.setObserver();
});

document.querySelector(".clear-field").addEventListener('click', function (e) {
    human.cleanField();

    if (shipsCollection.children.length > 0) {
        shipsCollection.removeChild(shipsCollection.lastChild);
    }
    shipsCollection.hidden = false;
    let initialShipsClone = '';
    initialShipsClone = initialShips.cloneNode(true);
    shipsCollection.appendChild(initialShipsClone);
    initialShipsClone.hidden = false;

    const placement = new Placement();
    placement.setObserver();
});
