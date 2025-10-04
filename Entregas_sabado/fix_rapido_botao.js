// FIX RÁPIDO - Execute no console para corrigir imediatamente
console.log("⚡ === FIX RÁPIDO DO BOTÃO ===");

// Função que sabemos que funciona (baseada no teste bem-sucedido)
function editLectureFixed(lectureId) {
    console.log(`🎯 editLectureFixed chamada para: ${lectureId}`);
    
    // Definir título do modal
    const modalTitle = document.getElementById('modalTitle');
    if (modalTitle) {
        modalTitle.textContent = 'Editar Palestra';
    }
    
    // Definir ID no formulário
    const lectureIdInput = document.getElementById('lectureId');
    if (lectureIdInput) {
        lectureIdInput.value = lectureId;
    }
    
    // Se for palestra padrão
    if (lectureId.startsWith('default-')) {
        if (typeof getDefaultLectureData === 'function') {
            const lectureData = getDefaultLectureData(lectureId);
            if (typeof populateLectureForm === 'function') {
                populateLectureForm(lectureData);
            }
        }
        
        const modal = document.getElementById('lectureModal');
        if (modal) {
            modal.style.display = 'flex';
        }
        return;
    }
    
    // Buscar dados da API
    fetch(`manage_announcements.php?id=${lectureId}`)
        .then(response => {
            if (!response.ok) throw new Error('Falha ao carregar dados da palestra.');
            return response.json();
        })
        .then(data => {
            console.log('📊 Dados recebidos:', data);
            
            // Popular o formulário manualmente se necessário
            if (typeof populateLectureForm === 'function') {
                populateLectureForm(data);
            } else {
                // Popular manualmente
                const elements = {
                    'lectureTitle': data.title,
                    'lectureSpeaker': data.speaker,
                    'lectureDate': data.lecture_date,
                    'lectureTime': data.lecture_time,
                    'lectureSummary': data.description
                };
                
                Object.entries(elements).forEach(([id, value]) => {
                    const element = document.getElementById(id);
                    if (element && value) {
                        element.value = value;
                    }
                });
            }
            
            // Exibir modal
            const modal = document.getElementById('lectureModal');
            if (modal) {
                modal.style.display = 'flex';
                console.log('✅ Modal exibido!');
            }
        })
        .catch(error => {
            console.error('❌ Erro:', error);
            alert('Erro ao carregar dados da palestra: ' + error.message);
        });
}

// Substituir todos os botões com a função corrigida
const editButtons = document.querySelectorAll('[onclick*="editLecture"]');
console.log(`🔄 Corrigindo ${editButtons.length} botões...`);

editButtons.forEach((button, index) => {
    // Extrair ID do onclick
    const onclickAttr = button.getAttribute('onclick');
    const match = onclickAttr.match(/editLecture\(['"]([^'"]+)['"]\)/);
    
    if (match) {
        const lectureId = match[1];
        
        // Remover onclick antigo
        button.removeAttribute('onclick');
        
        // Adicionar novo onclick
        button.onclick = function(e) {
            e.preventDefault();
            console.log(`🚀 Botão ${index + 1} clicado - ID: ${lectureId}`);
            editLectureFixed(lectureId);
            return false;
        };
        
        // Indicação visual
        button.style.boxShadow = '0 0 5px #00ff00';
        button.title = `Corrigido - ID: ${lectureId}`;
        
        console.log(`✅ Botão ${index + 1} corrigido`);
    }
});

// Disponibilizar função globalmente para testes
window.editLectureFixed = editLectureFixed;

console.log("🎉 CORREÇÃO APLICADA!");
console.log("📝 Todos os botões agora devem funcionar com a versão corrigida.");
console.log("🧪 Teste manual: editLectureFixed('115faa0d55024b9b9670b82c4c7f9ad4')");

// Testar automaticamente o primeiro botão
setTimeout(() => {
    if (editButtons.length > 0) {
        console.log("🧪 Testando primeiro botão automaticamente em 2 segundos...");
        editButtons[0].click();
    }
}, 2000);