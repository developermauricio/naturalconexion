var servicioUrlFirmada = "https://apis.coordinadora.com/puntos-drop/security/recursos/firmados";

function consumoServicio() {
  var xmlhttp = new XMLHttpRequest();
  var key = coordinadoraShippingSettings.droopApiKey;
  if (!key) {
    console.log("¡No hay una api KEY para el Droop de Coordinadora!");
    return false;
  }
  xmlhttp.open("GET", servicioUrlFirmada, true);
  xmlhttp.setRequestHeader("x-api-key", key);
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
      var urlResultado = xmlhttp.responseText;
      var scriptDroop = document.createElement("script");
      scriptDroop.setAttribute("src", urlResultado);
      document.body.appendChild(scriptDroop);
    }
  };
  xmlhttp.send();
}

window.onload = function () {
  if (coordinadoraShippingSettings.droopEnabled === "no") {
    return;
  }
  consumoServicio();
  const widgetOut = document.querySelector("puntos-drop");
  widgetOut.addEventListener("objetoDroopSalida", (event) => {
    if (event.detail.flagRetorno) {
      document.getElementById("billing_address_1").value = event.detail.direccion;
      jQuery("#billing_state").val(event.detail.daneDepartamento);
      jQuery("#billing_state").trigger("change");
      let cityOptions = [];
      jQuery("#billing_city > option").each(function () {
        cityOptions.push(this.value);
      });
      if (cityOptions.length > 0) {
        const cityIndex = cityOptions.map((option) => option.substr(-9, 8)).indexOf(event.detail.daneCiudad);
        if (cityIndex !== -1) {
          const citySelected = cityOptions[cityIndex];
          jQuery("#billing_city").val(citySelected);
          jQuery("#billing_city").trigger("change");
        }
      }
    } else {
      document.getElementById("billing_address_1").value = "";
    }
  });
};
