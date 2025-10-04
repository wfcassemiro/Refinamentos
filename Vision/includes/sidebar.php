<aside class="glass-sidebar collapsed" id="sidebar">
  <nav class="sidebar-nav">
    <ul>
    <?php
      $role = $_SESSION['user_role'] ?? null; // pega role se logado
      $current_page = basename($_SERVER['PHP_SELF'], '.php');
      $current_uri = $_SERVER['REQUEST_URI'];

      function isActive($page, $current_page, $current_uri = '') {
          if ($page === $current_page) return 'active';
          if ($page === 'home' && ($current_page === 'index' || $current_uri === '/')) return 'active';
          if ($page === 'admin' && strpos($current_uri, '/admin/') !== false) return 'active';
          if ($page === 'dash' && strpos($current_uri, '/dash-t101/') !== false) return 'active';
          if ($page === 'live' && strpos($current_uri, '/live-stream/') !== false) return 'active';
          return '';
      }
    ?>

    <?php if (!function_exists('isLoggedIn') || !isLoggedIn()): ?>
      <!-- Não logado -->
      <li><a href="/" class="<?php echo isActive('home', $current_page, $current_uri); ?>">
          <i class="fa-solid fa-house"></i><span>Início</span>
      </a></li>
      <li><a href="/#:~:text=de%20R%24%202%2C00-,por,-dia!" class="<?php echo isActive('planos', $current_page); ?>">
          <i class="fa-solid fa-briefcase"></i><span>Planos</span>
      </a></li>
      <li><a href="/faq.php" class="<?php echo isActive('faq', $current_page); ?>">
          <i class="fa-solid fa-circle-question"></i><span>FAQ</span>
      </a></li>
      <li><a href="/glossarios.php" class="<?php echo isActive('glossarios', $current_page); ?>">
          <i class="fa-solid fa-book"></i><span>Glossários</span>
      </a></li>
      <li><a href="/login.php" class="<?php echo isActive('login', $current_page); ?>">
          <i class="fa-solid fa-key"></i><span>Login</span>
      </a></li>
      <li><a href="/registro.php" class="<?php echo isActive('registro', $current_page); ?>">
          <i class="fa-solid fa-pen-to-square"></i><span>Cadastro</span>
      </a></li>

    <?php elseif ($role === 'free'): ?>
      <!-- Usuário Free logado -->
      <li><a href="/" class="<?php echo isActive('home', $current_page, $current_uri); ?>">
          <i class="fa-solid fa-house"></i><span>Início</span>
      </a></li>
      <li><a href="/#:~:text=de%20R%24%202%2C00-,por,-dia!" class="<?php echo isActive('planos', $current_page); ?>">
          <i class="fa-solid fa-briefcase"></i><span>Planos</span>
      </a></li>
      <li><a href="/faq.php" class="<?php echo isActive('faq', $current_page); ?>">
          <i class="fa-solid fa-circle-question"></i><span>FAQ</span>
      </a></li>
      <li><a href="/glossarios.php" class="<?php echo isActive('glossarios', $current_page); ?>">
          <i class="fa-solid fa-book"></i><span>Glossários</span>
      </a></li>

    <?php elseif ($role === 'subscriber'): ?>
      <!-- Assinante -->
      <li><a href="/videoteca.php" class="<?php echo isActive('videoteca', $current_page); ?>">
          <i class="fa-solid fa-film"></i><span>Videoteca</span>
      </a></li>
      <li><a href="/dash-t101/" class="<?php echo isActive('dash', $current_page, $current_uri); ?>">
          <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
      </a></li>
      <li><a href="/glossarios.php" class="<?php echo isActive('glossarios', $current_page); ?>">
          <i class="fa-solid fa-book"></i><span>Glossários</span>
      </a></li>
      <li><a href="/live-stream/index.php" class="<?php echo isActive('live', $current_page, $current_uri); ?>">
          <i class="fa-solid fa-broadcast-tower"></i><span>Ao Vivo</span>
      </a></li>

    <?php elseif ($role === 'admin'): ?>
      <!-- Administrador -->
      <li><a href="/videoteca.php" class="<?php echo isActive('videoteca', $current_page); ?>">
          <i class="fa-solid fa-film"></i><span>Videoteca</span>
      </a></li>
      <li><a href="/dash-t101/" class="<?php echo isActive('dash', $current_page, $current_uri); ?>">
          <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
      </a></li>
      <li><a href="/glossarios.php" class="<?php echo isActive('glossarios', $current_page); ?>">
          <i class="fa-solid fa-book"></i><span>Glossários</span>
      </a></li>
      <li><a href="/live-stream/index.php" class="<?php echo isActive('live', $current_page, $current_uri); ?>">
          <i class="fa-solid fa-broadcast-tower"></i><span>Ao Vivo</span>
      </a></li>
      <li><a href="/admin/" class="<?php echo isActive('admin', $current_page, $current_uri); ?>">
          <i class="fa-solid fa-crown"></i><span>Administração</span>
      </a></li>
    <?php endif; ?>
    </ul>
  </nav>
</aside>
<style>
/* Estilos para o item ativo do sidebar */
.sidebar-nav a.active {
    background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(255, 215, 0, 0.1)) !important;
    border-left: 4px solid var(--accent-gold) !important;
    color: var(--accent-gold) !important;
    font-weight: 600 !important;
    transform: translateX(5px) !important;
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3) !important;
}

.sidebar-nav a.active i {
    color: var(--accent-gold) !important;
    text-shadow: 0 0 10px rgba(255, 215, 0, 0.5) !important;
}

.sidebar-nav a.active span {
    color: var(--accent-gold) !important;
}

/* Animação suave para transições */
.sidebar-nav a {
    transition: all 0.3s ease !important;
}

/* Efeito hover melhorado para itens não ativos */
.sidebar-nav a:not(.active):hover {
    background: rgba(255, 255, 255, 0.1) !important;
    transform: translateX(3px) !important;
}
</style>