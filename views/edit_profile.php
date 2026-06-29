<?php declare(strict_types=1); ?>
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
      <a href="index.php?action=profile" class="btn-ghost text-sm">Ir para meu perfil</a>
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

  <!-- Preview do avatar atual removido -->

  <!-- Formulario -->
  <form method="POST" action="index.php?action=profile_update" enctype="multipart/form-data" class="space-y-5">

    <!-- Preview do avatar atual -->
    <div class="card p-6">
      <h2 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wide">Foto de Perfil Atual</h2>
      <label for="avatar-file-input" class="flex items-center gap-5 cursor-pointer group">
        <div class="relative">
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
            <input type="file" name="avatar_file" id="avatar-file-input" accept="image/*"
              class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
            <span class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[10px] font-semibold uppercase tracking-wide z-10">Alterar</span>
        </div>
        <div>
          <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($currentUser['name'] ?? '') ?></p>
          <p class="text-xs text-slate-400 mt-0.5">
            <?= $currentUser['user_type'] === 'institution' ? 'Conta Instituicao' : 'Conta Estudante' ?>
          </p>
          <p class="text-xs text-blue-500 mt-2">Clique no círculo para trocar a foto</p>
        </div>
      </label>
      <div class="mt-4">
        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Ou use um link direto</label>
        <input type="url" name="avatar_url" id="avatar-url-input"
          value="<?= htmlspecialchars($currentUser['avatar_url'] ?? '') ?>"
          placeholder="https://exemplo.com/sua-foto.jpg"
          oninput="previewAvatar(this.value)"
          class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 transition">
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
          <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">E-mail <span class="text-red-500">*</span></label>
          <input type="email" name="email" required
            value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>"
            placeholder="seu@email.com"
            class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm bg-slate-50 transition">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Nova senha</label>
          <input type="password" name="password" autocomplete="new-password"
            placeholder="Deixe em branco para manter a senha atual"
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

  <div class="card p-6 bg-amber-50 border-amber-200">
    <h2 class="text-sm font-bold text-slate-700 mb-4 uppercase tracking-wide">Acoes da conta</h2>
    <p class="text-sm text-slate-600 mb-4">Para excluir sua conta, a acao removerá todos os posts, likes e comentarios associados.</p>
    <form method="POST" action="index.php?action=profile_delete" onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.')">
      <button type="submit" class="w-full text-left text-red-600 border border-red-200 hover:bg-red-50 rounded-xl px-4 py-3 text-sm font-semibold transition">
        Excluir minha conta
      </button>
    </form>
  </div>
</main>

<script>
  function previewAvatar(url) {
    const container = document.getElementById('avatar-preview-container');
    const img       = document.getElementById('avatar-preview-img');

    if (!url || url.trim() === '') {
      if (container) container.classList.add('hidden');
      return;
    }
    if (img) {
      img.src = url;
      img.onload  = () => container && container.classList.remove('hidden');
      img.onerror = () => container && container.classList.add('hidden');
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('avatar-file-input');
    const urlInput = document.getElementById('avatar-url-input');
    const container = document.getElementById('avatar-preview-container');
    const img = document.getElementById('avatar-preview-img');

    if (fileInput) {
      fileInput.addEventListener('change', function () {
        const [file] = this.files || [];
        const avatarPreview = document.getElementById('avatar-preview');
        const avatarPlaceholder = document.getElementById('avatar-placeholder');

        if (!file) {
          if (!urlInput || urlInput.value.trim() === '') {
            container && container.classList.add('hidden');
          }
          return;
        }

        const objectUrl = URL.createObjectURL(file);

        if (avatarPreview) {
          avatarPreview.src = objectUrl;
          avatarPreview.onload = () => {
            container && container.classList.remove('hidden');
            URL.revokeObjectURL(objectUrl);
          };
          avatarPreview.onerror = () => {
            container && container.classList.add('hidden');
            URL.revokeObjectURL(objectUrl);
          };
        } else if (avatarPlaceholder) {
          avatarPlaceholder.classList.add('hidden');
          const newImg = document.createElement('img');
          newImg.id = 'avatar-preview';
          newImg.src = objectUrl;
          newImg.className = 'w-20 h-20 rounded-full object-cover border-4 border-slate-200';
          newImg.alt = 'Avatar selecionado';
          avatarPlaceholder.parentNode.insertBefore(newImg, avatarPlaceholder.nextSibling);
          avatarPlaceholder.remove();
          container && container.classList.remove('hidden');
        }
      });
    }
  });
</script>
</body>
</html>
