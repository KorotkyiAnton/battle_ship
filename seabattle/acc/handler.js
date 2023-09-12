window.onload = () => {
    if(localStorage.getItem("login")===null) {
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/";
    } else {
        document.querySelector(".nickname").innerHTML = "";
        document.querySelector(".nickname").innerHTML = localStorage.getItem("login");
    }

    function exitFromPreparePage() {
        window.location.href = "https://fmc2.avmg.com.ua/study/korotkyi/warship/seabattle/";
        localStorage.removeItem("login");
    }

    document.querySelector(".previous-page").addEventListener("click", exitFromPreparePage);
    document.querySelector(".close").addEventListener("click", exitFromPreparePage);
}