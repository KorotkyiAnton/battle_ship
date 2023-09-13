window.onload = () => {
    if(localStorage.getItem("login")===null) {
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/";
    } else {
        document.querySelector(".mine-nick").innerHTML = "";
        document.querySelector(".mine-nick").innerHTML = localStorage.getItem("login");
    }

    function exitFromPreparePage() {
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/";
        localStorage.removeItem("login");
        localStorage.removeItem("shipCoords");
    }

    function previousPage() {
        history.back();
    }

    document.querySelector(".previous-page").addEventListener("click", previousPage);
    document.querySelector(".close").addEventListener("click", exitFromPreparePage);

    // Получаем элемент таймера по его id
    const timerElement = document.getElementById('timer');

// Устанавливаем начальное значение времени (в секундах)
    let initialTimeInSeconds = 30;

// Функция для обновления таймера
    function updateTimer() {
        // Если время истекло, останавливаем таймер
        if (initialTimeInSeconds <= 0) {
            clearInterval(timerInterval);
            timerElement.textContent = 'Час вийшов!';
            timerElement.textContent = '30 сек';
            document.querySelector(".opponent-nick").classList.add("highlite");
            document.querySelector(".mine-nick").classList.remove("highlite");
            document.querySelector(".opponent-field").setAttribute('disabled', '');
            document.querySelector(".skip span").innerHTML =
                (parseInt(document.querySelector(".skip span").innerText)-1).toString();
        } else {
            // В противном случае, уменьшаем время на 1 секунду
            initialTimeInSeconds--;
            // Преобразуем время в формат "мм:сс" и обновляем текст на странице
            const minutes = Math.floor(initialTimeInSeconds / 60);
            const seconds = initialTimeInSeconds % 60;
            timerElement.textContent = seconds + ' сек';
        }
    }

// Обновляем таймер каждую секунду
    let timerInterval = setInterval(updateTimer, 1000);
}