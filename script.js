function exibirInstrumentos(valor) {
  console.log("Exibir instrumentos:", valor);
  const show = valor === "Sim";
  const instrumentoContainer = document.getElementById("instrumentoContainer");
  const musicoAtuacao = document.getElementById("musicoAtuacao");
  if (instrumentoContainer && musicoAtuacao) {
    instrumentoContainer.style.display = show ? "block" : "none";
    musicoAtuacao.style.display = show ? "block" : "none";
    if (!show) {
      const instrumento = document.getElementById("instrumento");
      const atuacao = document.getElementById("atuacao");
      if (instrumento) instrumento.value = "";
      if (atuacao) atuacao.value = "";
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  console.log("DOMContentLoaded disparado em script.js");
  const formulario = document.getElementById("formulario");

  if (formulario) {
    formulario.addEventListener("submit", (e) => {
      e.preventDefault();
      console.log("Formulário submetido");

      const formData = new FormData(formulario);
      const musico = formData.get("musico");

      if (musico !== "Sim") {
        formData.delete("instrumento");
        formData.delete("atuacao");
      }

      fetch("cadastrar.php", {
        method: "POST",
        body: formData
      })
        .then(res => res.text())
        .then(msg => {
          if (msg.trim() === "ok") {
            Swal.fire({
              icon: 'success',
              title: 'Sucesso!',
              text: 'Membro cadastrado com sucesso!',
              confirmButtonText: 'OK'
            }).then(() => {
              formulario.reset();
              exibirInstrumentos("");
            });
          } else {
            throw new Error(msg);
          }
        })
        .catch(err => {
          console.error("Erro ao cadastrar:", err);
          Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao cadastrar membro: ' + err.message,
            confirmButtonText: 'OK'
          });
        });
    });
  } else {
    console.warn("Formulário não encontrado");
  }
});