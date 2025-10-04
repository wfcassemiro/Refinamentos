// DIAGNÓSTICO FINAL - Execute no console do site v.translators101.com
console.log("🔍 === DIAGNÓSTICO FINAL DO PROBLEMA ===");

// 1. Testar a requisição diretamente
async function testeRequisicaoCompleta() {
    const testId = '115faa0d55024b9b9670b82c4c7f9ad4';
    const url = `manage_announcements.php?id=${testId}`;
    
    console.log(`🌐 Testando URL: ${url}`);
    
    try {
        const response = await fetch(url);
        console.log(`📡 Status: ${response.status} ${response.statusText}`);
        console.log(`📋 Headers:`, [...response.headers.entries()]);
        
        if (response.ok) {
            const data = await response.json();
            console.log(`📊 Dados recebidos:`, data);
            return data;
        } else {
            const errorText = await response.text();
            console.log(`❌ Erro texto:`, errorText);
            return null;
        }
    } catch (error) {
        console.log(`💥 Erro fetch:`, error);
        return null;
    }
}

// 2. Testar função editLecture step by step
function testeEditLectureDetalhado(lectureId) {
    console.log(`🧪 === TESTE DETALHADO editLecture ===`);
    console.log(`📝 ID recebido: ${lectureId}`);
    
    try {
        // Step 1: Verificar modal title
        console.log(`Step 1: Definindo título do modal...`);
        const modalTitleElement = document.getElementById('modalTitle');
        console.log(`- Elemento modalTitle:`, modalTitleElement);
        if (modalTitleElement) {
            modalTitleElement.textContent = 'Editar Palestra - TESTE';
            console.log(`✅ Título definido: ${modalTitleElement.textContent}`);
        } else {
            console.log(`❌ Elemento modalTitle não encontrado!`);
            return;
        }
        
        // Step 2: Verificar lectureId input
        console.log(`Step 2: Definindo lectureId...`);
        const lectureIdElement = document.getElementById('lectureId');
        console.log(`- Elemento lectureId:`, lectureIdElement);
        if (lectureIdElement) {
            lectureIdElement.value = lectureId;
            console.log(`✅ LectureId definido: ${lectureIdElement.value}`);
        } else {
            console.log(`❌ Elemento lectureId não encontrado!`);
            return;
        }
        
        // Step 3: Verificar se é palestra padrão
        console.log(`Step 3: Verificando tipo de palestra...`);
        if (lectureId.startsWith('default-')) {
            console.log(`⚡ Palestra padrão detectada`);
            if (typeof getDefaultLectureData === 'function') {
                const lectureData = getDefaultLectureData(lectureId);
                console.log(`📋 Dados padrão:`, lectureData);
                if (typeof populateLectureForm === 'function') {
                    populateLectureForm(lectureData);
                    console.log(`✅ Formulário populado`);
                } else {
                    console.log(`❌ Função populateLectureForm não encontrada`);
                }
            } else {
                console.log(`❌ Função getDefaultLectureData não encontrada`);
            }
            
            // Mostrar modal
            const modal = document.getElementById('lectureModal');
            if (modal) {
                modal.style.display = 'flex';
                console.log(`✅ Modal exibido para palestra padrão`);
            } else {
                console.log(`❌ Elemento lectureModal não encontrado`);
            }
            return;
        }
        
        // Step 4: Buscar dados da API
        console.log(`Step 4: Fazendo requisição para API...`);
        testeRequisicaoCompleta().then(data => {
            if (data) {
                console.log(`✅ Dados recebidos da API`);
                if (typeof populateLectureForm === 'function') {
                    populateLectureForm(data);
                    console.log(`✅ Formulário populado com dados da API`);
                } else {
                    console.log(`❌ Função populateLectureForm não encontrada`);
                }
                
                const modal = document.getElementById('lectureModal');
                if (modal) {
                    modal.style.display = 'flex';
                    console.log(`✅ Modal exibido com dados da API`);
                } else {
                    console.log(`❌ Elemento lectureModal não encontrado`);
                }
            } else {
                console.log(`❌ Falha ao obter dados da API`);
            }
        });
        
    } catch (error) {
        console.log(`💥 Erro no teste:`, error);
    }
}

// 3. Verificar se há conflitos de CSS que possam estar escondendo o modal
function verificarModalCSS() {
    console.log(`🎨 === VERIFICANDO CSS DO MODAL ===`);
    
    const modal = document.getElementById('lectureModal');
    if (modal) {
        const styles = window.getComputedStyle(modal);
        console.log(`📋 Estilos do modal:`, {
            display: styles.display,
            visibility: styles.visibility,
            opacity: styles.opacity,
            zIndex: styles.zIndex,
            position: styles.position,
            top: styles.top,
            left: styles.left,
            width: styles.width,
            height: styles.height
        });
        
        if (styles.display === 'none') {
            console.log(`⚠️ Modal está com display: none`);
        }
        
        if (styles.visibility === 'hidden') {
            console.log(`⚠️ Modal está com visibility: hidden`);
        }
        
        if (styles.opacity === '0') {
            console.log(`⚠️ Modal está com opacity: 0`);
        }
        
    } else {
        console.log(`❌ Modal não encontrado no DOM`);
    }
}

// 4. Verificar todos os event listeners nos botões
function verificarEventListeners() {
    console.log(`🖱️ === VERIFICANDO EVENT LISTENERS ===`);
    
    const editButtons = document.querySelectorAll('[onclick*="editLecture"]');
    console.log(`📋 Encontrados ${editButtons.length} botões com editLecture`);
    
    editButtons.forEach((btn, index) => {
        console.log(`Botão ${index + 1}:`);
        console.log(`- onclick: ${btn.getAttribute('onclick')}`);
        console.log(`- disabled: ${btn.disabled}`);
        console.log(`- hidden: ${btn.hidden}`);
        console.log(`- style.display: ${btn.style.display}`);
        console.log(`- Visível:`, btn.offsetWidth > 0 && btn.offsetHeight > 0);
    });
}

// 5. Executar todos os testes
console.log(`🚀 Executando diagnóstico completo...`);

console.log(`\n1️⃣ Testando requisição...`);
testeRequisicaoCompleta();

console.log(`\n2️⃣ Verificando CSS...`);
verificarModalCSS();

console.log(`\n3️⃣ Verificando botões...`);
verificarEventListeners();

console.log(`\n🎯 Para testar a função editLecture:`);
console.log(`testeEditLectureDetalhado('115faa0d55024b9b9670b82c4c7f9ad4')`);

console.log(`\n✅ Diagnóstico configurado!`);