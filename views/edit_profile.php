<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EduConnect — Editar Perfil</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f1f5f9; }
  input:focus, textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
  .card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; }
  .btn-primary { background:#2563eb; color:#fff; font-weight:600; border-radius:10px; padding:.65rem 1.4rem; font-size:.85rem; transition:background .15s; }
  .btn-primary:hover { background:#1d4ed8; }
  .btn-ghost { color:#475569; font-weight:500; border-radius:10px; padding:.65rem 1.2rem; font-size:.85rem; transition:background .15s; }
  .btn-ghost:hover { background:#f1f5f9; }
</style>
</head>
<body>

<!-- NAVBAR -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-40">
  <div class="max-w-3xl mx-auto px-4 h-14 flex items-center justify-between gap-4">
    <a href="index.php?action=feed" class="flex items-center gap-2">
      <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0121 12c0 3.866-4.03 7-9 7s-9-3.134-9-7c0-.539.078-1.06.227-1.562L12 14z"/>
      </svg>
      <span class="font-bold text-slate-900">EduConnect</span>
    </a>
    <div class="flex items-center gap-2">
      <a href="index.php?action=feed" class="btn-ghost text-sm">Voltar ao feed</a>
      <a href="index.php?action=logout" class="text-xs text-slate-400 hover:text-slate-700 px-2">Sair</a>
    </div>
  </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-8 space-y-6">

  <!-- Flash -->
  <?php if (!empty($_SESSION['flash'])): 
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
  ?>
    <div class="<?= $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800' ?> border rounded-xl px-4 py-3 text-sm">
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <!-- Cabecalho da pagina -->
  <div class="card p-6">
    <div class="flex items-center gap-3 mb-1">
      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
      </svg>
      <h1 class="text-xl font-bold text-slate-900">Editar Perfil</h1>
    </div>
    <p class="text-sm text-slate-500 ml-9">Mantenha suas informacoes atualizadas para que a comunidade te conheca melhor.</p>
  </div>

  <!-- Preview do avatar atual -->
  <div class="card p-6">
    <h2 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wide">Foto de Perfil Atual</h2>
    <div class="flex items-center gap-5">
      <?php if (!empty($currentUser['avatar_url'])): ?>
        <img id="avatar-preview"
          src="<?= htmlspecialchars($currentUser['avatar_url']) ?>"
          class="w-20 h-20 rounded-full object-cover border-4 border-slate-200"
          alt="Avatar atual"
          onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($currentUser['name']) ?>&background=dbeafe&color=2563eb&size=80'">
      <?php else: ?>
        <div id="avatar-placeholder"
          class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-2xl border-4 border-slate-200">
          <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
        </div>
      <?php endif; ?>
      <div>
        <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($currentUser['name'] ?? '') ?></p>
        <p class="text-xs text-slate-400 mt-0.5">
          <?= $currentUser['user_type'] === 'institution' ? 'Conta Instituicao' : 'Conta Estudante' ?>
        </p>
        <p class="text-xs text-blue-500 mt-2">Cole uma URL de imagem abaixo para alterar</p>
      </div>
    </div>
  </div>

  <!-- Formulario -->
  <form method="POST" action="index.php?action=profile_update" class="space-y-5">

    <!-- Nome -->
    <div class="card p-6">
      <h2 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wide">Informacoes Basicas</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">
            Nome completo / Razao social <span class="text-red-500">*</span>
          </label>
          <input type="text" name="name" required
            value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>"
            placeholder="Seu nome completo ou razao social"
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 transition">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Bio / Descricao</label>
          <textarea name="bio" rows="4"
            placeholder="Escreva um pouco sobre voce, seus interesses academicos ou sobre a sua instituicao..."
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 resize-none transition"><?= htmlspecialchars($currentUser['bio'] ?? '') ?></textarea>
          <p class="text-xs text-slate-400 mt-1">Maximo recomendado: 280 caracteres</p>
        </div>
      </div>
    </div>

    <!-- Informacoes adicionais -->
    <div class="card p-6">
      <h2 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wide">
        <?= $currentUser['user_type'] === 'institution' ? 'Dados Institucionais' : 'Dados Academicos' ?>
      </h2>
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">
          <?= $currentUser['user_type'] === 'institution' ? 'CNPJ' : 'Curso atual' ?>
        </label>
        <input type="text" name="extra_info"
          value="<?= htmlspecialchars($currentUser['extra_info'] ?? '') ?>"
          placeholder="<?= $currentUser['user_type'] === 'institution' ? 'XX.XXX.XXX/0001-XX' : 'Ex.: Tecnico em Desenvolvimento de Sistemas' ?>"
          class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 transition">
      </div>
    </div>

    <!-- Avatar URL -->
    <div class="card p-6">
      <h2 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wide">Foto de Perfil</h2>
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">URL da foto (link direto para imagem)</label>
        <input type="url" name="avatar_url" id="avatar-url-input"
          value="<?= htmlspecialchars($currentUser['avatar_url'] ?? '') ?>"
          placeholder="https://exemplo.com/sua-foto.jpg"
          oninput="previewAvatar(this.value)"
          class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 transition">
        <p class="text-xs text-slate-400 mt-1.5">
          Sugestao: use uma foto do <a href="https://unsplash.com" target="_blank" class="text-blue-500 hover:underline">Unsplash</a> ou o link direto da sua foto do GitHub.
        </p>
      </div>

      <!-- Preview dinamico -->
      <div id="avatar-preview-container" class="mt-4 <?= empty($currentUser['avatar_url']) ? 'hidden' : '' ?>">
        <p class="text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Preview</p>
        <img id="avatar-preview-img"
          src="<?= htmlspecialchars($currentUser['avatar_url'] ?? '') ?>"
          class="w-16 h-16 rounded-full object-cover border-2 border-blue-200"
          alt="Preview"
          onerror="document.getElementById('avatar-preview-container').classList.add('hidden')">
      </div>
    </div>

    <!-- Acoes -->
    <div class="flex items-center justify-between gap-3 pb-8">
      <a href="index.php?action=feed" class="btn-ghost">Cancelar</a>
      <button type="submit" class="btn-primary flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Salvar alteracoes
      </button>
    </div>

  </form>
</main>

<script>
  function previewAvatar(url) {
    const container = document.getElementById('avatar-preview-container');
    const img       = document.getElementById('avatar-preview-img');

    if (!url || url.trim() === '') {
      container.classList.add('hidden');
      return;
    }
    img.src = url;
    img.onload  = () => container.classList.remove('hidden');
    img.onerror = () => container.classList.add('hidden');
  }
</script>
</body>
</html>
