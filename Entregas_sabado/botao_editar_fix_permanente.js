/**
 * FIX PERMANENTE - Botões de Editar Palestras
 * Adicione este arquivo ao seu site ou inclua o código no arquivo JavaScript principal
 */

// Aguardar o DOM estar carregado
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Aplicando fix permanente dos botões de editar...');
    
    // Aplicar fix imediatamente
    aplicarFixBotoes();
    
    // Aplicar fix novamente após mudanças no DOM (caso seja necessário)
    const observer = new MutationObserver(function(mutations) {
        let needsFix = false;
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' || mutation.type === 'attributes') {
                // Verificar se novos botões foram adicionados
                const newButtons = document.querySelectorAll('[onclick*="editLecture"]:not([data-fixed])');
                if (newButtons.length > 0) {
                    needsFix = true;
                }
            }
        });
        
        if (needsFix) {
            setTimeout(aplicarFixBotoes, 100); // Pequeno delay para DOM se estabilizar
        }
    });
    
    // Observar mudanças no documento
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['onclick']
    });
});

// Função principal de correção
function aplicarFixBotoes() {
    const editButtons = document.querySelectorAll('[onclick*="editLecture"]:not([data-fixed])');
    
    if (editButtons.length === 0) return;
    
    console.log(`🔄 Corrigindo ${editButtons.length} botões de editar...`);
    
    editButtons.forEach((button, index) => {
        // Extrair ID do onclick
        const onclickAttr = button.getAttribute('onclick');
        const match = onclickAttr ? onclickAttr.match(/editLecture\(['"]([^'"]+)['"]\)/) : null;
        
        if (match) {
            const lectureId = match[1];
            
            // Remover onclick antigo
            button.removeAttribute('onclick');
            
            // Adicionar novo onclick funcional
            button.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log(`🚀 Editando palestra - ID: ${lectureId}`);
                editLectureFixed(lectureId);
                return false;
            };
            
            // Marcar como corrigido
            button.setAttribute('data-fixed', 'true');
            button.title = button.title || `Editar palestra - ID: ${lectureId}`;
            
            console.log(`✅ Botão ${index + 1} corrigido (ID: ${lectureId})`);
        }
    });
}

// Função de edição corrigida
function editLectureFixed(lectureId) {
    console.log(`🎯 Editando palestra: ${lectureId}`);
    
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
        console.log('📋 Carregando palestra padrão...');
        
        if (typeof getDefaultLectureData === 'function') {
            const lectureData = getDefaultLectureData(lectureId);
            if (typeof populateLectureForm === 'function') {
                populateLectureForm(lectureData);
            } else {
                popularFormularioManual(lectureData);
            }
        }
        
        exibirModal();
        return;
    }
    
    // Buscar dados da API
    console.log('🌐 Buscando dados da API...');
    
    fetch(`manage_announcements.php?id=${lectureId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('📊 Dados recebidos:', data);
            
            // Popular o formulário
            if (typeof populateLectureForm === 'function') {
                populateLectureForm(data);
            } else {
                popularFormularioManual(data);
            }
            
            exibirModal();
        })
        .catch(error => {
            console.error('❌ Erro ao carregar dados:', error);
            alert('Erro ao carregar dados da palestra: ' + error.message);
        });
}

// Função para popular formulário manualmente
function popularFormularioManual(data) {
    const campos = {
        'lectureTitle': data.title,
        'lectureSpeaker': data.speaker,
        'lectureDate': data.lecture_date,
        'lectureTime': data.lecture_time,
        'lectureSummary': data.description
    };
    
    Object.entries(campos).forEach(([id, valor]) => {
        const elemento = document.getElementById(id);
        if (elemento && valor) {
            elemento.value = valor;
        }
    });
    
    console.log('📝 Formulário populado manualmente');
}

// Função para exibir modal
function exibirModal() {
    const modal = document.getElementById('lectureModal');
    if (modal) {
        modal.style.display = 'flex';
        console.log('✅ Modal exibido');
        
        // Garantir que o modal esteja visível
        modal.style.visibility = 'visible';
        modal.style.opacity = '1';
        modal.style.zIndex = '9999';
        
        // Focar no primeiro campo do formulário
        const firstInput = modal.querySelector('input[type="text"], textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    } else {
        console.error('❌ Modal não encontrado');
    }
}

// Disponibilizar funções globalmente para compatibilidade
window.editLectureFixed = editLectureFixed;
window.aplicarFixBotoes = aplicarFixBotoes;

// Auto-aplicar em páginas que já estão carregadas
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', aplicarFixBotoes);
} else {
    aplicarFixBotoes();
}