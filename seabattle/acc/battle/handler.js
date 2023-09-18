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
}