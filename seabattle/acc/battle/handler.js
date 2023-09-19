window.onload = () => {
    if(localStorage.getItem("login")===null) {
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/";
    } else {
        document.querySelector(".mine-nick").innerHTML = "";
        document.querySelector(".mine-nick").innerHTML = localStorage.getItem("login");
    }

    function exitFromBattlePage() {
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/";
        localStorage.removeItem("login");
        localStorage.removeItem("shipCoords");
    }

    function previousPage() {
        history.back();
    }

    const confirmationOverlay = document.getElementById('confirmationOverlay');
    const confirmButton = document.getElementById('confirmButton');
    const cancelButton = document.getElementById('cancelButton');

    // Обработчик события клика по кнопке .close
    document.querySelector('.close').addEventListener('click', function() {
        confirmationOverlay.style.display = 'flex'; // Показываем окно
    });

    // Обработчик события клика по кнопке "Так"
    confirmButton.addEventListener('click', function() {
        exitFromBattlePage();
        confirmationOverlay.style.display = 'none'; // Скрываем окно
    });

    // Обработчик события клика по кнопке "Ні"
    cancelButton.addEventListener('click', function() {
        confirmationOverlay.style.display = 'none'; // Скрываем окно
    });

    document.querySelector(".previous-page").addEventListener("click", previousPage);
}