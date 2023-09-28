window.onload = () => {
    const nicks = document.querySelectorAll(".loc-nick");
    const nick = localStorage.getItem("login");
    for (let i = 0; i < nicks.length; i++) {
        nicks[i].innerHTML = nick+", ";
    }
    document.querySelector(".mine-nick").innerHTML = localStorage.getItem("login");
    document.querySelector(".opponent-nick").innerHTML = JSON.parse(localStorage.getItem("gameInfo")).opponent_login;
    console.log(localStorage.getItem("isWinner"))
    if(localStorage.getItem("isWinner")!==null && localStorage.getItem("isWinner") === "true") {
        document.querySelector(".u-win").style.display = "flex";
    } else if(localStorage.getItem("isWinner")!==null && localStorage.getItem("isWinner")==="false") {
        document.querySelector(".u-lose").style.display = "flex";
    }
}