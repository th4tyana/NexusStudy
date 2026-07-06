<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ALTI — Entrar ou Cadastrar</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: 'Segoe UI', system-ui, sans-serif; }
  .bg-grid {
    background: #2B59FF;
    background-image:
      radial-gradient(circle at top right, rgba(255,255,255,.14), transparent 18%),
      linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px);
    background-size: 100% 100%, 24px 24px, 24px 24px;
  }
  .tab-button { min-width: 120px; }
  .tab-button.active { background: #fff; color: #111827; box-shadow: 0 10px 25px rgba(15,23,42,.08); }
  .tab-button.inactive { background: #f8fafc; color: #64748b; }
  .account-card { transition: all .2s ease; }
  .account-card.active { border-color: #2563eb; background: rgba(37,99,235,.08); }
  .form-control:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
  .divider-line::before,
  .divider-line::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e2e8f0;
  }
</style>
</head>
<body class="min-h-screen bg-slate-100">
  <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

    <aside class="hidden lg:flex flex-col justify-between px-16 py-12 relative overflow-hidden bg-grid text-white">
      <div class="absolute -right-24 top-20 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
      <div class="absolute left-10 top-40 h-48 w-48 rounded-full bg-white/5 blur-3xl"></div>
      <div class="relative z-10 max-w-lg">
        <div class="flex items-center gap-3 mb-8">
          
          <svg width="0" height="0" style="position: absolute;">
  <defs>
    <clipPath id="squircleClip" clipPathUnits="objectBoundingBox">
      <path d="M 0,0.5 C 0,0 0,0 0.5,0 S 1,0 1,0.5 1,1 0.5,1 0,1 0,0.5"></path>
    </clipPath>
  </defs>
</svg>

<div class="relative">
  <div class="absolute inset-0 bg-black/20 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl"></div>

  <div class="relative flex items-center p-2">
    <div class="relative group flex items-center">
      
      <div class="absolute left-full top-1/2 -translate-y-1/2 ml-3 invisible opacity-0 scale-95 group-hover:visible group-hover:opacity-100 group-hover:scale-100 transition-all duration-300 ease-out bg-slate-900/90 backdrop-blur-md border border-white/10 px-4 py-2.5 rounded-xl flex flex-row gap-x-5 shadow-2xl z-50">
        
        <a href="https://linkedin.com/in/seu-perfil-1" target="_blank" class="text-sm font-medium text-gray-300 hover:text-white pb-0.5 border-b-2 border-transparent hover:border-blue-400 transition-all duration-200 whitespace-nowrap">
          Thatyana
        </a>
        
        <a href="https://www.linkedin.com/in/nycolas-g-31a2a53b2/" target="_blank" class="text-sm font-medium text-gray-300 hover:text-white pb-0.5 border-b-2 border-transparent hover:border-blue-400 transition-all duration-200 whitespace-nowrap">
          Nycolas
        </a>
        
        <a href="https://linkedin.com/in/seu-perfil-3" target="_blank" class="text-sm font-medium text-gray-300 hover:text-white pb-0.5 border-b-2 border-transparent hover:border-blue-400 transition-all duration-200 whitespace-nowrap">
          Ana
        </a>
        
        <a href="https://linkedin.com/in/seu-perfil-4" target="_blank" class="text-sm font-medium text-gray-300 hover:text-white pb-0.5 border-b-2 border-transparent hover:border-blue-400 transition-all duration-200 whitespace-nowrap">
          Jacke
        </a>
        
      </div>

      <div
        style="clip-path: url(#squircleClip)"
        class="w-14 h-14 bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl flex items-center justify-center shadow-lg border border-blue-500/50 cursor-pointer transform transition-all duration-300 ease-out hover:scale-110 hover:shadow-2xl"
      >
        <svg
          viewBox="0 0 24 24"
          fill="currentColor"
          class="h-8 w-8 text-white"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"
          ></path>
        </svg>
      </div>

    </div>
  </div>
</div>

          <span class="text-base uppercase tracking-[0.35em] text-white/80"> ⬅ EQUIPE</span>
        </div>

        <p class="text-xs uppercase tracking-[0.35em] text-white/70 mb-4">ALTI</p>
        <h1 class="text-5xl font-extrabold leading-tight mb-6">Conecte talentos.<br>Acelere carreiras.<br><span class="text-white/80">Transformando organizações.</span></h1>
        <p class="text-sm text-white/80 max-w-md">Uma plataforma onde estudantes constroem portfólios reais e instituições encontram os próximos talentos do mercado.</p>
      </div>

      <div class="relative z-10 mt-12 grid grid-cols-3 gap-4 text-white/80 text-sm">
        <div class="flex items-center gap-3 rounded-3xl bg-white/5 p-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M23 21v-2a4 4 0 00-3-3.87" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div>
            <p class="text-xl font-semibold"></p>
            <p class="uppercase tracking-[0.25em] text-xs">Estudantes</p>
          </div>
        </div>
        <div class="flex items-center gap-3 rounded-3xl bg-white/5 p-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 21V8a2 2 0 012-2h14a2 2 0 012 2v13" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 21V11h8v10" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div>
            <p class="text-xl font-semibold"></p>
            <p class="uppercase tracking-[0.25em] text-xs">Instituições</p>
          </div>
        </div>
        <div class="flex items-center gap-3 rounded-3xl bg-white/5 p-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          </div>
          <div>
            <p class="text-xl font-semibold"></p>
            <p class="uppercase tracking-[0.25em] text-xs">Empregabilidade</p>
          </div>
        </div>
      </div>
    </aside>

    <main class="flex items-center justify-center p-8">
      <div class="w-full max-w-xl">
        <div class="bg-white rounded-[32px] shadow-[0_24px_80px_rgba(15,23,42,0.08)] overflow-hidden">
          <div class="p-5 bg-slate-50">
            <div class="flex items-center justify-center gap-2 bg-slate-100 rounded-full p-1">
              <button id="tab-login" type="button" onclick="switchTab('login')" class="tab-button active rounded-full px-6 py-3 text-sm font-semibold">Entrar</button>
              <button id="tab-register" type="button" onclick="switchTab('register')" class="tab-button inactive rounded-full px-6 py-3 text-sm font-semibold">Cadastrar</button>
            </div>
          </div>

          <div class="p-8">
            <div class="mb-8">
              <h2 id="form-title" class="text-3xl font-semibold text-slate-900">Bem-vindo de volta</h2>
              <p id="form-subtitle" class="mt-3 text-sm text-slate-500">Acesse sua conta na rede ALTI</p>
            </div>

            <?php if (!empty($error)): ?>
              <div class="mb-4 rounded-3xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <?= htmlspecialchars($error) ?>
              </div>
            <?php endif; ?>

            <form id="form-login" method="POST" action="index.php?action=do_login" class="space-y-5">
              <div>
                <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-[0.18em]">E-mail</label>
                <input id="login-email" type="email" name="email" placeholder="seu@email.com" required
                  value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                  class="form-control w-full rounded-[32px] border border-slate-200 bg-white px-5 py-4 text-sm text-slate-900">
              </div>

              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="text-xs font-semibold text-slate-500 uppercase tracking-[0.18em]">Senha</label>
                  <a href="#" class="text-sm text-blue-600 hover:text-blue-700">Esqueci minha senha</a>
                </div>
                <div class="relative">
                  <input id="login-password" type="password" name="password" placeholder="••••••••" required
                    class="form-control w-full rounded-[32px] border border-slate-200 bg-white px-5 py-4 pr-12 text-sm text-slate-900">
                  <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" onclick="togglePassword('login-password', this)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </button>
                </div>
              </div>

              <button type="submit" class="w-full rounded-[36px] bg-gradient-to-r from-[#2563eb] to-[#1d4ed8] py-4 text-sm font-semibold text-white shadow-lg shadow-blue-200/40 transition hover:brightness-110">Entrar →</button>

              <div class="divider-line flex items-center justify-center gap-3 text-xs text-slate-400">
                <span></span>
                <span>ou</span>
                <span></span>
              </div>

              <a href="index.php?action=google_login" class="w-full inline-flex items-center justify-center gap-2 rounded-[32px] border border-slate-200 bg-white py-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <svg class="h-5 w-5" viewBox="0 0 533.5 544.3" xmlns="http://www.w3.org/2000/svg">
                  <path fill="#4285F4" d="M533.5 278.4c0-17.2-1.5-34.1-4.4-50.4H272v95.5h146.9c-6.3 34-25.3 62.8-54 82.1v68.1h87.3c51.1-47 80.3-116.4 80.3-195.3z"/>
                  <path fill="#34A853" d="M272 544.3c72.9 0 134.2-24.2 178.9-65.7l-87.3-68.1c-24.3 16.3-55.5 26-91.5 26-70.4 0-130-47.3-151.4-110.9H32.9v69.5C77.5 489.6 169.7 544.3 272 544.3z"/>
                  <path fill="#FBBC05" d="M120.6 325.1c-6.1-18.1-9.6-37.4-9.6-57.1s3.5-39 9.6-57.1V141.4H32.9C11.7 189.7 0 244.1 0 272s11.7 82.3 32.9 130.6l87.7-77.5z"/>
                  <path fill="#EA4335" d="M272 108.9c39.7 0 75.4 13.7 103.5 40.6l77.6-77.6C406.2 24.2 344.9 0 272 0 169.7 0 77.5 54.7 32.9 141.4l87.7 69.5C142 156.2 201.6 108.9 272 108.9z"/>
                </svg>
                Continuar com Google
              </a>

              <p class="text-center text-xs text-slate-400 pt-4">Ainda não tem conta? <button type="button" onclick="switchTab('register')" class="text-blue-600 font-semibold">Cadastre-se grátis</button></p>
            </form>

            <form id="form-register" method="POST" action="index.php?action=do_register" class="space-y-5 hidden">
              <input type="hidden" name="user_type" value="student">
              <div class="mb-6">
                <label class="block text-xs font-semibold text-slate-500 mb-3 uppercase tracking-[0.18em]">Tipo de conta</label>
                <div class="grid grid-cols-2 gap-3">
                  <button type="button" id="card-student" onclick="selectAccountType('student')" class="account-card relative rounded-3xl border-2 border-blue-600 bg-blue-50 p-5 text-left active">
                    <span class="indicator absolute right-4 top-4 h-2.5 w-2.5 rounded-full bg-blue-600"></span>
                    <div class="flex items-center justify-center h-11 w-11 rounded-2xl bg-white shadow-sm mb-3">
                      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-slate-900">Estudante</p>
                      <p class="text-xs text-slate-500 mt-1">Portfólio & oportunidades</p>
                    </div>
                  </button>
                  <button type="button" id="card-institution" onclick="selectAccountType('institution')" class="account-card relative rounded-3xl border-2 border-slate-200 bg-white p-5 text-left">
                    <span class="indicator absolute right-4 top-4 h-2.5 w-2.5 rounded-full bg-blue-600"></span>
                    <div class="flex items-center justify-center h-11 w-11 rounded-2xl bg-slate-100 shadow-sm mb-3">
                      <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-slate-900">Instituição</p>
                      <p class="text-xs text-slate-500 mt-1">Gerencie e recrute talentos</p>
                    </div>
                  </button>
                </div>
              </div>
              <div>
                <label id="name-label" class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-[0.18em]">Nome completo</label>
                <input id="name-input" type="text" name="name" placeholder="João Silva" required class="form-control w-full rounded-[32px] border border-slate-200 bg-white px-5 py-4 text-sm text-slate-900">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-[0.18em]">E-mail</label>
                <input id="register-email" type="email" name="email" placeholder="seu@email.com" required class="form-control w-full rounded-[32px] border border-slate-200 bg-white px-5 py-4 text-sm text-slate-900">
              </div>
              <div class="relative">
                <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-[0.18em]">Senha</label>
                <input id="reg-password" type="password" name="password" placeholder="Mínimo 6 caracteres" required class="form-control w-full rounded-[32px] border border-slate-200 bg-white px-5 py-4 text-sm text-slate-900 pr-12">
                <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" onclick="togglePassword('reg-password', this)">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
              </div>
              <div id="extra-field">
                <label id="extra-label" class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-[0.18em]">Curso atual</label>
                <input id="extra-input" type="text" name="extra_info" placeholder="Ex.: Técnico em Desenvolvimento de Sistemas" class="form-control w-full rounded-[32px] border border-slate-200 bg-white px-5 py-4 text-sm text-slate-900">
                <p id="extra-help" class="mt-2 text-xs text-slate-400">Informe seu curso atual para receber ofertas de vagas e parcerias.</p>
              </div>
              <p class="text-xs text-slate-400">Ao continuar, você aceita os Termos de Uso e a Política de Privacidade de ALTI.</p>
              <button type="submit" class="w-full rounded-[36px] bg-gradient-to-r from-[#2563eb] to-[#1d4ed8] py-4 text-sm font-semibold text-white shadow-lg shadow-blue-200/40 transition hover:brightness-110">Criar minha conta</button>
              <p class="text-center text-xs text-slate-400 pt-4">Já tem conta? <button type="button" onclick="switchTab('login')" class="text-blue-600 font-semibold">Entrar</button></p>
            </form>
          </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">ALTI  2025 a 2026 — Projeto SA - SENAI</p>
      </div>
    </main>
  </div>

  <script>
    const urlAction = new URLSearchParams(window.location.search).get('action');
    if (urlAction === 'register') switchTab('register');
    selectAccountType('student');

    function switchTab(tab) {
      const fLogin = document.getElementById('form-login');
      const fReg = document.getElementById('form-register');
      const tLogin = document.getElementById('tab-login');
      const tReg = document.getElementById('tab-register');
      const title = document.getElementById('form-title');
      const subtitle = document.getElementById('form-subtitle');

      if (tab === 'login') {
        fLogin.classList.remove('hidden');
        fReg.classList.add('hidden');
        tLogin.classList.add('active');
        tLogin.classList.remove('inactive');
        tReg.classList.add('inactive');
        tReg.classList.remove('active');
        title.textContent = 'Bem-vindo de volta';
        subtitle.textContent = 'Acesse sua conta na rede Nexo Edu.';
      } else {
        fLogin.classList.add('hidden');
        fReg.classList.remove('hidden');
        tLogin.classList.add('inactive');
        tLogin.classList.remove('active');
        tReg.classList.add('active');
        tReg.classList.remove('inactive');
        title.textContent = 'Criar conta';
        subtitle.textContent = 'Junte-se à maior rede educacional corporativa.';
      }
    }

    function selectAccountType(type) {
      const cards = {
        student: document.getElementById('card-student'),
        institution: document.getElementById('card-institution')
      };
      const hiddenInput = document.querySelector('#form-register input[name="user_type"]');
      const nameLabel = document.getElementById('name-label');
      const nameInput = document.getElementById('name-input');
      const extraLabel = document.getElementById('extra-label');
      const extraInput = document.getElementById('extra-input');
      const extraHelp = document.getElementById('extra-help');

      if (hiddenInput) {
        hiddenInput.value = type;
      }

      Object.values(cards).forEach(card => {
        if (!card) return;
        card.classList.remove('active', 'border-blue-600', 'bg-blue-50');
        card.classList.add('border-slate-200', 'bg-white');
      });

      const activeCard = cards[type];
      if (activeCard) {
        activeCard.classList.add('active', 'border-blue-600', 'bg-blue-50');
      }

      if (type === 'institution') {
        nameLabel.textContent = 'Nome da instituição';
        nameInput.placeholder = 'Ex.: Universidade Federal';
        extraLabel.textContent = 'CNPJ';
        extraInput.placeholder = 'XX.XXX.XXX/0001-XX';
        extraHelp.textContent = 'Informe o CNPJ da sua instituição para validar o cadastro corporativo.';
      } else {
        nameLabel.textContent = 'Nome completo';
        nameInput.placeholder = 'João Silva';
        extraLabel.textContent = 'Curso atual';
        extraInput.placeholder = 'Ex.: Técnico em Desenvolvimento de Sistemas';
        extraHelp.textContent = 'Informe seu curso atual para receber ofertas de vagas e parcerias.';
      }
    }

    function togglePassword(fieldId, btn) {
      const input = document.getElementById(fieldId);
      if (!input) return;
      if (input.type === 'password') {
        input.type = 'text';
        btn.classList.add('text-slate-600');
      } else {
        input.type = 'password';
        btn.classList.remove('text-slate-600');
      }
    }
  </script>
</body>
</html>
