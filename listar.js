window.currentFilter = "";
let currentPage = 1;
const itemsPerPage = 10;

function calcularIdade(dataNascimento) {
  if (!dataNascimento) return "";
  try {
    const [dia, mes, ano] = dataNascimento.split("/").map(Number);
    const hoje = new Date(); // Usa a data atual do sistema
    const nascimento = new Date(ano, mes - 1, dia);
    let idade = hoje.getFullYear() - nascimento.getFullYear();
    const mesDiff = hoje.getMonth() - nascimento.getMonth();
    if (mesDiff < 0 || (mesDiff === 0 && hoje.getDate() < nascimento.getDate())) {
      idade--;
    }
    return idade >= 0 ? idade : "";
  } catch (e) {
    console.error("Erro ao calcular idade:", e);
    return "";
  }
}

function listarMembros(filtro = "") {
  console.log("Listar membros chamado com filtro:", filtro);
  window.currentFilter = filtro;
  const container = document.getElementById("lista-membros");
  const pagination = document.getElementById("pagination");
  const paginationInfo = document.getElementById("pagination-info");

  if (!container || !pagination || !paginationInfo) {
    console.error("Elementos DOM não encontrados");
    Swal.fire({
      icon: 'error',
      title: 'Erro!',
      text: 'Elementos necessários não encontrados.',
      confirmButtonText: 'OK'
    });
    return;
  }

  container.innerHTML = "Carregando...";
  pagination.innerHTML = "";
  paginationInfo.innerHTML = "";

  fetch("listar.php")
    .then(res => {
      if (!res.ok) {
        return res.text().then(text => {
          throw new Error(`Erro HTTP ${res.status}: ${text}`);
        });
      }
      return res.text();
    })
    .then(text => {
      try {
        const data = JSON.parse(text);
        return data;
      } catch (e) {
        console.error("Resposta não é JSON válido:", text);
        throw new Error(`Erro ao parsear JSON: ${e.message}\nResposta: ${text}`);
      }
    })
    .then(membros => {
      container.innerHTML = "";
      const membrosFiltrados = membros.filter(m => m.nome.toLowerCase().includes(filtro.toLowerCase()));

      if (membrosFiltrados.length === 0) {
        container.innerHTML = "<p>Nenhum membro encontrado.</p>";
        paginationInfo.innerHTML = "Mostrando 0 de 0 membros";
        return;
      }

      const totalItems = membrosFiltrados.length;
      const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
      console.log("Total items:", totalItems, "Total pages:", totalPages);

      if (currentPage > totalPages || !Number.isFinite(currentPage)) {
        currentPage = 1;
      }

      const startIndex = (currentPage - 1) * itemsPerPage;
      const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
      const membrosPagina = membrosFiltrados.slice(startIndex, endIndex);

      paginationInfo.innerHTML = `Mostrando ${startIndex + 1}-${endIndex} de ${totalItems} membros`;

      const tabela = document.createElement("table");
      tabela.classList.add("table", "table-striped", "table-hover");
      tabela.innerHTML = `
        <thead>
          <tr>
            <th>Nome</th><th>Telefone</th><th>Bairro</th><th>Batizado</th>
            <th>Músico</th><th>Instrumento</th><th>Atuação</th><th>Organista</th><th>Cargo</th><th>Data de Nascimento</th><th>Idade</th><th>Ações</th>
          </tr>
        </thead>
      `;

      const tbody = document.createElement("tbody");
      membrosPagina.forEach((membro) => {
        const idade = calcularIdade(membro.data_nascimento);
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${membro.nome || ''}</td>
          <td>${membro.telefone || ''}</td>
          <td>${membro.bairro || ''}</td>
          <td>${membro.batizado || ''}</td>
          <td>${membro.musico || ''}</td>
          <td>${membro.instrumento || ''}</td>
          <td>${membro.atuacao || ''}</td>
          <td>${membro.organista || ''}</td>
          <td>${membro.cargo || ''}</td>
          <td>${membro.data_nascimento || ''}</td>
          <td>${idade}</td>
          <td>
            <button class="btn btn-sm btn-outline-primary me-1" onclick='abrirEditarModal(${JSON.stringify(membro)})' title="Editar">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick='abrirExcluirModal(${membro.id}, "${membro.nome}")' title="Excluir">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        `;
        tbody.appendChild(tr);
      });

      tabela.appendChild(tbody);
      container.appendChild(tabela);
      renderPagination(totalPages);
    })
    .catch(err => {
      console.error("Erro ao listar:", err);
      Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: 'Erro ao carregar membros: ' + err.message,
        confirmButtonText: 'OK',
        html: `<pre style="text-align: left; font-size: 12px;">${err.message}</pre>`
      });
      container.innerHTML = "<p>Erro ao carregar membros.</p>";
      paginationInfo.innerHTML = "Erro ao carregar membros";
    });
}

function renderPagination(totalPages) {
  console.log("Renderizando paginação para", totalPages, "páginas, currentPage:", currentPage);
  const pagination = document.getElementById("pagination");
  if (!pagination || !Number.isFinite(totalPages) || totalPages < 1) {
    console.warn("Paginação inválida, totalPages:", totalPages);
    return;
  }

  pagination.innerHTML = "";
  const firstLi = document.createElement("li");
  firstLi.classList.add("page-item");
  if (currentPage === 1) firstLi.classList.add("disabled");
  firstLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(1)">Primeira</a>`;
  pagination.appendChild(firstLi);

  const prevLi = document.createElement("li");
  prevLi.classList.add("page-item");
  if (currentPage === 1) prevLi.classList.add("disabled");
  prevLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${currentPage - 1})">Anterior</a>`;
  pagination.appendChild(prevLi);

  const maxButtons = 5;
  let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
  let endPage = Math.min(totalPages, startPage + maxButtons - 1);
  if (endPage - startPage + 1 < maxButtons) {
    startPage = Math.max(1, endPage - maxButtons + 1);
  }

  for (let i = startPage; i <= endPage; i++) {
    const pageLi = document.createElement("li");
    pageLi.classList.add("page-item");
    if (i === currentPage) pageLi.classList.add("active");
    pageLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>`;
    pagination.appendChild(pageLi);
  }

  const nextLi = document.createElement("li");
  nextLi.classList.add("page-item");
  if (currentPage === totalPages) nextLi.classList.add("disabled");
  nextLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${currentPage + 1})">Próxima</a>`;
  pagination.appendChild(nextLi);

  const lastLi = document.createElement("li");
  lastLi.classList.add("page-item");
  if (currentPage === totalPages) lastLi.classList.add("disabled");
  lastLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${totalPages})">Última</a>`;
  pagination.appendChild(lastLi);
}

function goToPage(page) {
  console.log("Navegando para página:", page);
  if (page >= 1 && Number.isFinite(page)) {
    currentPage = page;
    listarMembros(window.currentFilter);
  }
}

function exibirInstrumentosEditar(valor) {
  console.log("Exibir instrumentos editar:", valor);
  const show = valor === "Sim";
  const instrumentoContainer = document.getElementById("editarInstrumentoContainer");
  const musicoAtuacao = document.getElementById("editarMusicoAtuacao");
  if (instrumentoContainer && musicoAtuacao) {
    instrumentoContainer.style.display = show ? "block" : "none";
    musicoAtuacao.style.display = show ? "block" : "none";
    if (!show) {
      const editarInstrumento = document.getElementById("editarInstrumento");
      const editarAtuacao = document.getElementById("editarAtuacao");
      if (editarInstrumento) editarInstrumento.value = "";
      if (editarAtuacao) editarAtuacao.value = "";
    }
  }
}

function abrirEditarModal(membro) {
  console.log("Abrindo modal de edição para membro:", membro.id);
  const editarId = document.getElementById("editarId");
  if (!editarId) return;

  editarId.value = membro.id || '';
  document.getElementById("editarNome").value = membro.nome || '';
  document.getElementById("editarTelefone").value = membro.telefone || '';
  document.getElementById("editarEndereco").value = membro.endereco || '';
  document.getElementById("editarCep").value = membro.cep || '';
  document.getElementById("editarNumero").value = membro.numero || '';
  document.getElementById("editarBairro").value = membro.bairro || '';
  document.getElementById("editarSexo").value = membro.sexo || '';
  document.getElementById("editarBatizado").value = membro.batizado || '';
  document.getElementById("editarMusico").value = membro.musico || '';
  document.getElementById("editarInstrumento").value = membro.instrumento_id || '';
  document.getElementById("editarAtuacao").value = membro.atuacao_id || '';
  document.getElementById("editarOrganista").value = membro.organista || '';
  document.getElementById("editarCargo").value = membro.cargo_id || '';
  document.getElementById("editarDataNascimento").value = membro.data_nascimento ? 
  membro.data_nascimento.split('/').reverse().join('-') : '';

  exibirInstrumentosEditar(membro.musico || 'Não');

  const modal = new bootstrap.Modal(document.getElementById('editarModal'));
  modal.show();
}

function salvarEdicao() {
  console.log("Salvando edição de membro");
  const formData = new FormData(document.getElementById("formularioEditar"));
  const musico = formData.get("musico");

  if (musico !== "Sim") {
    formData.delete("instrumento");
    formData.delete("atuacao");
  }

  fetch("editar.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.text())
    .then(msg => {
      if (msg.trim() === "ok") {
        Swal.fire({
          icon: 'success',
          title: 'Sucesso!',
          text: 'Membro atualizado com sucesso!',
          confirmButtonText: 'OK'
        }).then(() => {
          document.getElementById("formularioEditar").reset();
          bootstrap.Modal.getInstance(document.getElementById('editarModal')).hide();
          listarMembros(window.currentFilter);
        });
      } else {
        throw new Error(msg);
      }
    })
    .catch(err => {
      console.error("Erro ao editar:", err);
      Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: 'Erro ao atualizar membro: ' + err.message,
        confirmButtonText: 'OK'
      });
    });
}

function abrirExcluirModal(id, nome) {
  console.log("Abrindo modal de exclusão para membro:", id);
  const excluirId = document.getElementById("excluirId");
  const excluirNome = document.getElementById("excluirNome");
  if (!excluirId || !excluirNome) return;

  excluirId.value = id;
  excluirNome.textContent = nome;

  const modal = new bootstrap.Modal(document.getElementById('excluirModal'));
  modal.show();
}

function excluirMembro() {
  console.log("Excluindo membro");
  const excluirId = document.getElementById("excluirId");
  if (!excluirId) return;

  const id = excluirId.value;

  fetch("excluir.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}`
  })
    .then(res => res.text())
    .then(msg => {
      if (msg.trim() === "ok") {
        Swal.fire({
          icon: 'success',
          title: 'Sucesso!',
          text: 'Membro excluído com sucesso!',
          confirmButtonText: 'OK'
        }).then(() => {
          bootstrap.Modal.getInstance(document.getElementById('excluirModal')).hide();
          fetch("listar.php")
            .then(res => res.json())
            .then(membros => {
              const membrosFiltrados = membros.filter(m => m.nome.toLowerCase().includes(window.currentFilter.toLowerCase()));
              const totalPages = Math.ceil(membrosFiltrados.length / itemsPerPage) || 1;
              if (currentPage > totalPages) {
                currentPage = totalPages;
              }
              listarMembros(window.currentFilter);
            });
        });
      } else {
        throw new Error(msg);
      }
    })
    .catch(err => {
      console.error("Erro ao excluir:", err);
      Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: 'Erro ao excluir membro: ' + err.message,
        confirmButtonText: 'OK'
      });
    });
}

document.addEventListener('DOMContentLoaded', () => {
  console.log("DOMContentLoaded disparado em listar.js");
  const btnListaMembros = document.getElementById("btn-lista-membros");
  if (btnListaMembros) {
    btnListaMembros.addEventListener("click", () => {
      console.log("Botão Lista de Membros clicado");
      listarMembros(window.currentFilter);
    });
  } else {
    console.warn("Botão btn-lista-membros não encontrado");
  }

  listarMembros();
});