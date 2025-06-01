function exportarMembros(formato) {
    console.log(`Iniciando exportação para ${formato}`);
    const exportarModal = bootstrap.Modal.getInstance(document.getElementById('exportarModal'));
    if (!exportarModal) {
      console.error("Modal de exportação não encontrado");
      Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: 'Modal de exportação não encontrado.',
        confirmButtonText: 'OK'
      });
      return;
    }
  
    exportarModal.hide();
  
    const url = formato === 'pdf' ? 'exportar_pdf.php' : 'exportar_excel.php';
    console.log("Enviando requisição para:", url);
    const formData = new FormData();
    formData.append('filtro', window.currentFilter || '');
  
    Swal.fire({
      title: 'Exportando...',
      text: 'Aguarde enquanto o arquivo é gerado.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });
  
    fetch(url, {
      method: 'POST',
      body: formData
    })
      .then(res => {
        console.log("Resposta recebida:", res.status, res.statusText);
        if (!res.ok) {
          return res.text().then(text => { throw new Error(`Erro HTTP ${res.status}: ${text}`); });
        }
        return res.blob();
      })
      .then(blob => {
        console.log("Blob recebido, tamanho:", blob.size);
        if (blob.size === 0) {
          throw new Error("Arquivo vazio retornado");
        }
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = formato === 'pdf' ? 'membros.pdf' : 'membros.xlsx';
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
  
        Swal.fire({
          icon: 'success',
          title: 'Sucesso!',
          text: `Arquivo ${formato.toUpperCase()} exportado com sucesso!`,
          confirmButtonText: 'OK'
        });
      })
      .catch(err => {
        console.error("Erro ao exportar:", err);
        Swal.fire({
          icon: 'error',
          title: 'Erro!',
          text: 'Erro ao exportar: ' + err.message,
          confirmButtonText: 'OK'
        });
      });
  }
  
  function bindExportEvents() {
    console.log("Vinculando eventos de exportação");
    const btnExportarPDF = document.getElementById("exportar-pdf");
    const btnExportarExcel = document.getElementById("exportar-excel");
  
    if (btnExportarPDF) {
      btnExportarPDF.removeEventListener("click", handleExportPDF);
      btnExportarPDF.addEventListener("click", handleExportPDF);
      console.log("Evento de clique vinculado ao botão exportar-pdf");
    } else {
      console.warn("Botão exportar-pdf não encontrado no DOM");
    }
  
    if (btnExportarExcel) {
      btnExportarExcel.removeEventListener("click", handleExportExcel);
      btnExportarExcel.addEventListener("click", handleExportExcel);
      console.log("Evento de clique vinculado ao botão exportar-excel");
    } else {
      console.warn("Botão exportar-excel não encontrado no DOM");
    }
  }
  
  function handleExportPDF() {
    console.log("Botão Exportar PDF clicado");
    exportarMembros('pdf');
  }
  
  function handleExportExcel() {
    console.log("Botão Exportar Excel clicado");
    exportarMembros('excel');
  }
  
  document.addEventListener('DOMContentLoaded', () => {
    console.log("DOMContentLoaded disparado em export.js");
    bindExportEvents();
  });
  
  document.getElementById('exportarModal')?.addEventListener('shown.bs.modal', () => {
    console.log("Modal de exportação mostrado, re-vinculando eventos");
    bindExportEvents();
  });