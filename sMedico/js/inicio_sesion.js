function validar(){
    let username = document.getElementById("username").value;
    let password = document.getElementById("password").value;

    console.log(username);
    if (username=="ejemplo" && password=="sismed"){
        window.location.href = ("opciones.html");
    }
    else{
        alert("Usuario o contraseña incorrectos");
        }
}