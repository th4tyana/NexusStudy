<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EduConnect — Entrar</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: 'Segoe UI', system-ui, sans-serif; }
  .tab-active { border-bottom: 2px solid #2563eb; color: #2563eb; font-weight: 600; }
  .tab-inactive { border-bottom: 2px solid transparent; color: #64748b; }
  input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
</style>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4">

<div class="w-full max-w-md">

  <!-- Logotipo -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center gap-2 mb-2">
      <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0121 12c0 3.866-4.03 7-9 7s-9-3.134-9-7c0-.539.078-1.06.227-1.562L12 14z"/>
      </svg>
      <span class="text-2xl font-bold text-slate-900">EduConnect</span>
    </div>
    <p class="text-sm text-slate-500">Rede acadêmica para estudantes e instituições</p>
  </div>

  <!-- Card principal -->
  <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

    <!-- Abas Login / Cadastro -->
    <div class="flex border-b border-slate-200">
      <button id="tab-login" onclick="switchTab('login')"
        class="flex-1 py-4 text-sm transition tab-active">
        Entrar
      </button>
      <button id="tab-register" onclick="switchTab('register')"
        class="flex-1 py-4 text-sm transition tab-inactive">
        Criar conta
      </button>
    </div>

    <div class="p-7">

      <?php if (!empty($error)): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- FORMULÁRIO LOGIN -->
      <form id="form-login" method="POST" action="index.php?action=do_login" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">E-mail</label>
          <input type="email" name="email" placeholder="seu@email.com" required
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 transition">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Senha</label>
          <input type="password" name="password" placeholder="••••••••" required
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 transition">
        </div>
        <button type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl text-sm transition">
          Entrar na plataforma
        </button>
        <p class="text-center text-xs text-slate-400 pt-1">
          Acesso demo: <span class="font-mono text-slate-600">gabriel@educonnect.com</span> / <span class="font-mono text-slate-600">Senha@123</span>
        </p>
      </form>

      <!-- FORMULÁRIO CADASTRO -->
      <form id="form-register" method="POST" action="index.php?action=do_register" class="space-y-4 hidden">

        <!-- Seleção de tipo de conta -->
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wide">Tipo de conta</label>
          <div class="grid grid-cols-2 gap-3">
            <label class="cursor-pointer">
              <input type="radio" name="user_type" value="student" class="sr-only peer" checked
                onchange="toggleExtraField('student')">
              <div class="peer-checked:border-blue-500 peer-checked:bg-blue-50 border-2 border-slate-200 rounded-xl p-3 text-center transition">
                <svg class="w-6 h-6 mx-auto mb-1 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-xs font-semibold text-slate-700">Estudante</span>
              </div>
            </label>
            <label class="cursor-pointer">
              <input type="radio" name="user_type" value="institution" class="sr-only peer"
                onchange="toggleExtraField('institution')">
              <div class="peer-checked:border-blue-500 peer-checked:bg-blue-50 border-2 border-slate-200 rounded-xl p-3 text-center transition">
                <svg class="w-6 h-6 mx-auto mb-1 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="text-xs font-semibold text-slate-700">Instituição</span>
              </div>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Nome completo / Razão social</label>
          <input type="text" name="name" placeholder="Seu nome ou nome da instituição" required
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 transition">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">E-mail</label>
          <input type="email" name="email" placeholder="seu@email.com" required
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 transition">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Senha</label>
          <input type="password" name="password" placeholder="Mínimo 6 caracteres" required
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 transition">
        </div>

        <!-- Campo dinâmico -->
        <div id="extra-field">
          <label id="extra-label" class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Curso atual</label>
          <input type="text" name="extra_info" id="extra-input" placeholder="Ex.: Técnico em Desenvolvimento de Sistemas"
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 transition">
        </div>

        <button type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl text-sm transition">
          Criar minha conta
        </button>
      </form>

    </div>
  </div>

  <p class="text-center text-xs text-slate-400 mt-6">
    EduConnect &copy; <?= date('Y') ?> — Projeto Acadêmico
  </p>
</div>

<script>
  // Determina aba inicial baseado na action da URL ou erro de registro
  const urlAction = new URLSearchParams(window.location.search).get('action');
  if (urlAction === 'register') switchTab('register');

  function switchTab(tab) {
    const fLogin  = document.getElementById('form-login');
    const fReg    = document.getElementById('form-register');
    const tLogin  = document.getElementById('tab-login');
    const tReg    = document.getElementById('tab-register');

    if (tab === 'login') {
      fLogin.classList.remove('hidden');
      fReg.classList.add('hidden');
      tLogin.className = 'flex-1 py-4 text-sm transition tab-active';
      tReg.className   = 'flex-1 py-4 text-sm transition tab-inactive';
    } else {
      fLogin.classList.add('hidden');
      fReg.classList.remove('hidden');
      tLogin.className = 'flex-1 py-4 text-sm transition tab-inactive';
      tReg.className   = 'flex-1 py-4 text-sm transition tab-active';
    }
  }

  function toggleExtraField(type) {
    const label = document.getElementById('extra-label');
    const input = document.getElementById('extra-input');
    if (type === 'institution') {
      label.textContent   = 'CNPJ';
      input.placeholder   = 'XX.XXX.XXX/0001-XX';
    } else {
      label.textContent   = 'Curso atual';
      input.placeholder   = 'Ex.: Técnico em Desenvolvimento de Sistemas';
    }
  }
</script>
</body>
</html>
