
function getval(sel)
{
    alert(sel.value);
}

function emailChecked() {
var checkBox = document.getElementById("05");
var text = document.getElementById("email-text");

    if (checkBox.checked == true){
        text.style.display = "block";
    } else {
        text.style.display = "none";
    }
} 
