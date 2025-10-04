<header class="glass-header">
  <div class="logo">
    <a href="/">
    <img src="/images/logo-translators101.png" alt="Translators101" style="height: 40px;">
    </a>
  </div>

  <nav class="desktop-nav">
    <ul>
    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
    <!-- Menu superior para usuários LOGADOS: apenas Perfil, Contato e Sair -->
    <li><a href="/perfil.php"><i class="fa-solid fa-user-circle"></i> Perfil</a></li>
    <li><a href="/contato.php"><i class="fa-solid fa-envelope"></i> Contato</a></li>
    <li><a href="/logout_confirm.php"><i class="fa-solid fa-sign-out-alt"></i><span>Sair</span></a></li>
    <?php else: ?>
    <!-- Menu superior para usuários NÃO LOGADOS: Planos, FAQ e Contato -->
    <li><a href="/#:~:text=Escolha%20seu%20plano%20e%20comece%20hoje%20mesmo"><i class="fa-solid fa-briefcase"></i><span>Planos</span></a></li>
    <li><a href="/faq.php"><i class="fa-solid fa-circle-question"></i> FAQ</a></li>
    <li><a href="/contato.php"><i class="fa-solid fa-envelope"></i> Contato</a></li>
    <li><a href="/login.php"><i class="fa-solid fa-key"></i> Login</a></li>
    <li><a href="/registro.php"><i class="fa-solid fa-user-plus"></i> Cadastro</a></li>
    <?php endif; ?>
    </ul>
  </nav>

  <div class="mobile-menu-toggle">
    <i class="fa-solid fa-bars"></i>
  </div>
</header>