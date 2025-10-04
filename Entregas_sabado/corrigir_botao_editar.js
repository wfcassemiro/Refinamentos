// SCRIPT PARA CORRIGIR O BOTÃO DE EDITAR
// Execute este código no console do site v.translators101.com

console.log("🔧 === CORRIGINDO BOTÕES DE EDITAR ===");

// 1. Primeiro, vamos interceptar todos os cliques nos botões de editar
function interceptarBotoesEditar() {
    console.log("📋 Interceptando botões de editar...");
    
    // Encontrar todos os botões com editLecture
    const editButtons = document.querySelectorAll('[onclick*="editLecture"]');
    console.log(`🎯 Encontrados ${editButtons.length} botões de editar`);
    
    editButtons.forEach((button, index) => {
        console.log(`🔍 Verificando botão ${index + 1}:`);
        console.log(`- onclick original: ${button.getAttribute('onclick')}`);
        
        // Remover o onclick original que pode estar com problema
        const onclickOriginal = button.getAttribute('onclick');
        
        // Extrair o ID do onclick
        const match = onclickOriginal.match(/editLecture\(['"]([^'"]+)['"]\)/);
        if (match) {
            const lectureId = match[1];
            console.log(`- ID extraído: ${lectureId}`);
            
            // Remover onclick original
            button.removeAttribute('onclick');
            
            // Adicionar novo event listener que sabemos que funciona
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log(`🚀 Clique interceptado para ID: ${lectureId}`);
                
                // Usar a função que sabemos que funciona
                testeEditLectureDetalhado(lectureId);
                
                return false;
            });
            
            console.log(`✅ Botão ${index + 1} corrigido com novo event listener`);
            
            // Adicionar indicação visual de que foi corrigido
            button.style.border = '2px solid #00ff00';
            button.title = `Botão corrigido - ID: ${lectureId}`;
            
        } else {
            console.log(`❌ Não foi possível extrair ID do botão ${index + 1}`);
        }
    });
}

// 2. Função para testar um botão específico
function testarBotaoEspecifico(index = 0) {
    const editButtons = document.querySelectorAll('[onclick*="editLecture"]');
    if (editButtons[index]) {
        console.log(`🧪 Testando botão ${index + 1} por click programático...`);
        editButtons[index].click();
    } else {
        console.log(`❌ Botão ${index + 1} não encontrado`);
    }
}

// 3. Função para adicionar logs em todas as funções relacionadas
function adicionarLogsDebug() {
    console.log("📝 Adicionando logs de debug...");
    
    // Interceptar a função editLecture original se existir
    if (typeof editLecture !== 'undefined') {
        const editLectureOriginal = editLecture;
        
        window.editLecture = function(lectureId) {
            console.log(`🎯 editLecture chamada com ID: ${lectureId}`);
            console.log(`📞 Argumentos:`, arguments);
            console.log(`🕒 Timestamp:`, new Date().toISOString());
            
            try {
                return editLectureOriginal.apply(this, arguments);
            } catch (error) {
                console.error(`💥 Erro na editLecture original:`, error);
                console.log(`🔄 Tentando função de backup...`);
                testeEditLectureDetalhado(lectureId);
            }
        };
        
        console.log("✅ Função editLecture interceptada com logs");
    } else {
        console.log("❌ Função editLecture não encontrada");
    }
    
    // Interceptar populateLectureForm se existir
    if (typeof populateLectureForm !== 'undefined') {
        const populateLectureFormOriginal = populateLectureForm;
        
        window.populateLectureForm = function(data) {
            console.log(`📝 populateLectureForm chamada com:`, data);
            return populateLectureFormOriginal.apply(this, arguments);
        };
        
        console.log("✅ Função populateLectureForm interceptada com logs");
    }
}

// 4. Função para verificar se há JavaScript bloqueando
function verificarBloqueios() {
    console.log("🚫 Verificando possíveis bloqueios...");
    
    // Verificar se há event listeners que podem estar interferindo
    const body = document.body;
    const events = getEventListeners ? getEventListeners(body) : 'Função getEventListeners não disponível';
    console.log("📋 Event listeners no body:", events);
    
    // Verificar se há overlays invisíveis
    const elementsAtClick = document.elementsFromPoint(window.innerWidth / 2, window.innerHeight / 2);
    console.log("🎯 Elementos no centro da tela:", elementsAtClick);
    
    // Verificar se há modais abertos que podem estar bloqueando
    const modals = document.querySelectorAll('[id*="modal"], [class*="modal"]');
    console.log(`🖼️ Modais encontrados: ${modals.length}`);
    modals.forEach((modal, index) => {
        const styles = window.getComputedStyle(modal);
        if (styles.display !== 'none') {
            console.log(`⚠️ Modal ${index + 1} pode estar bloqueando:`, modal);
        }
    });
}

// 5. Executar todas as correções
console.log("🚀 Iniciando correção completa...");

console.log("\n1️⃣ Adicionando logs...");
adicionarLogsDebug();

console.log("\n2️⃣ Verificando bloqueios...");
verificarBloqueios();

console.log("\n3️⃣ Interceptando botões...");
interceptarBotoesEditar();

console.log("\n🎯 BOTÕES CORRIGIDOS!");
console.log("📝 Os botões agora têm uma borda verde e devem funcionar.");
console.log("🧪 Para testar manualmente: testarBotaoEspecifico(0)");

// Função global para facilitar testes
window.testarBotaoEspecifico = testarBotaoEspecifico;
window.testeEditLectureDetalhado = testeEditLectureDetalhado;

console.log("✅ Correção aplicada com sucesso!");