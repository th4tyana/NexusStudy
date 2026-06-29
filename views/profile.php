<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EduConnect — Meu Perfil</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f1f5f9; }
  .card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; }
  .btn-primary { background:#2563eb; color:#fff; font-weight:600; border-radius:10px; padding:.65rem 1.4rem; font-size:.85rem; transition:background .15s; }
  .btn-primary:hover { background:#1d4ed8; }
  .btn-ghost { color:#475569; font-weight:500; border-radius:10px; padding:.65rem 1.2rem; font-size:.85rem; transition:background .15s; }
  .btn-ghost:hover { background:#f1f5f9; }
  .badge { display:inline-flex; align-items:center; gap:.35rem; padding:.35rem .75rem; border-radius:999px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
  .badge-institution { background:#eff6ff; color:#2563eb; }
  .badge-student { background:#e2e8f0; color:#475569; }
  .post-card { background:#fff; border-radius:16px; border:1px solid #e2e8f0; }
</style>
</head>
<body>
<header class="bg-white border-b border-slate-200 sticky top-0 z-40">
  <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between gap-4">
    <a href="index.php?action=feed" class="flex items-center gap-2 flex-shrink-0">
      <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0121 12c0 3.866-4.03 7-9 7s-9-3.134-9-7c0-.539.078-1.06.227-1.562L12 14z"/>
      </svg>
      <span class="font-bold text-slate-900 hidden sm:inline">EduConnect</span>
    </a>
    <div class="flex items-center gap-2">
      <a href="index.php?action=edit_profile" class="btn-ghost text-sm">Editar perfil</a>
      <a href="index.php?action=feed" class="btn-ghost text-sm">Feed</a>
      <a href="index.php?action=logout" class="text-xs text-slate-400 hover:text-slate-700 px-2">Sair</a>
    </div>
  </div>
</header>

<main class="max-w-5xl mx-auto px-4 py-8 space-y-6">
  <?php if (!empty($_SESSION['flash'])): 
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
  ?>
    <div class="<?= $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800' ?> border rounded-xl px-4 py-3 text-sm">
      <?= htmlspecialchars($flash['msg']) ?>
    </div>
  <?php endif; ?>

  <section class="card p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="flex items-center gap-4">
        <?php if (!empty($currentUser['avatar_url'])): ?>
          <img src="<?= htmlspecialchars($currentUser['avatar_url']) ?>" class="w-20 h-20 rounded-full object-cover border border-slate-200" alt="Avatar do perfil">
        <?php else: ?>
          <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-2xl border border-slate-200">
            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
          </div>
        <?php endif; ?>
        <div>
          <h1 class="text-2xl font-bold text-slate-900"><?= htmlspecialchars($currentUser['name'] ?? '') ?></h1>
          <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($currentUser['bio'] ?? 'Sem bio definida ainda.') ?></p>
          <div class="mt-3 flex flex-wrap gap-2">
            <span class="badge <?= $currentUser['user_type'] === 'institution' ? 'badge-institution' : 'badge-student' ?>">
              <?= $currentUser['user_type'] === 'institution' ? 'Instituição' : 'Estudante' ?>
            </span>
            <?php if (!empty($currentUser['extra_info'])): ?>
              <span class="badge bg-slate-100 text-slate-700"><?= htmlspecialchars($currentUser['extra_info']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="text-right space-y-2">
        <p class="text-xs text-slate-400">E-mail</p>
        <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
        <?php if ((int)($_SESSION['user_id'] ?? 0) !== (int)$currentUser['id']): ?>
          <button id="follow-btn" data-target-id="<?= (int)$currentUser['id'] ?>" class="btn-primary">
            <?= $followModel->isFollowing((int)($_SESSION['user_id'] ?? 0), (int)$currentUser['id']) ? 'Seguindo' : 'Seguir' ?>
          </button>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="card p-5">
    <div class="flex flex-wrap gap-4 text-sm">
      <button type="button" onclick="toggleList('followers')" class="text-left rounded-xl border border-slate-200 px-3 py-2 hover:bg-slate-50">
        <div class="font-semibold text-slate-900"><?= (int)$followModel->countFollowers((int)$currentUser['id']) ?> seguidores</div>
        <div class="text-slate-500">Ver seguidores</div>
      </button>
      <button type="button" onclick="toggleList('following')" class="text-left rounded-xl border border-slate-200 px-3 py-2 hover:bg-slate-50">
        <div class="font-semibold text-slate-900"><?= (int)$followModel->countFollowing((int)$currentUser['id']) ?> seguindo</div>
        <div class="text-slate-500">Ver seguindo</div>
      </button>
    </div>
    <div id="follow-list" class="mt-4 hidden rounded-xl border border-slate-200 bg-slate-50 p-3"></div>
  </section>

  <section class="space-y-4">
    <div class="flex items-center justify-between gap-4">
      <div>
        <h2 class="text-xl font-bold text-slate-900">Suas Publicações</h2>
        <p class="text-sm text-slate-500">Acompanhe apenas as postagens feitas por você.</p>
      </div>
      <button type="button" onclick="toggleNewPostForm()" class="btn-primary" id="new-post-button">
        Nova publicação
      </button>
    </div>

    <form id="new-post-form" method="POST" action="index.php?action=post_create" enctype="multipart/form-data" class="hidden card border-slate-200 p-5 space-y-3">
      <input type="hidden" name="redirect_to" value="profile">
      <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg">
          <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="flex-1 space-y-3">
          <textarea name="content" rows="4" required placeholder="O que você quer compartilhar hoje?"
            class="w-full border border-slate-200 rounded-2xl p-4 text-sm bg-slate-50 resize-none transition"></textarea>
          <input type="file" name="media_file" accept="image/*"
            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 transition" />
          <p class="text-xs text-slate-400 mt-2">A imagem será enviada diretamente e exibida junto à publicação.</p>
          <div class="flex justify-between items-center gap-3">
            <button type="button" onclick="toggleNewPostForm()" class="btn-ghost">Cancelar</button>
            <button type="submit" class="btn-primary">Publicar</button>
          </div>
        </div>
      </div>
    </form>

    <?php if (empty($posts)): ?>
      <div class="card p-8 text-center text-slate-400 text-sm">
        Você ainda não publicou nada. Crie uma nova publicação para aparecer aqui.
      </div>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
      <article class="post-card overflow-hidden">
        <div class="p-5 border-b border-slate-100">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h3 class="text-lg font-semibold text-slate-900">Publicação em <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></h3>
              <p class="text-xs text-slate-400 mt-1">Curtidas: <?= (int)$post['like_count'] ?> — Comentários: <?= (int)($post['comment_count'] ?? 0) ?></p>
            </div>
            <div class="flex items-center gap-2">
              <a href="index.php?action=post_edit&id=<?= (int)$post['id'] ?>" class="btn-ghost text-xs">Editar</a>
              <a href="index.php?action=post_delete&id=<?= (int)$post['id'] ?>" class="btn-danger text-xs"
                 onclick="return confirm('Excluir esta publicação?');">Excluir</a>
            </div>
          </div>
        </div>
        <div class="p-5">
          <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
          <?php if (!empty($post['media_url'])): ?>
            <img src="<?= htmlspecialchars($post['media_url']) ?>" class="w-full mt-4 rounded-2xl object-cover max-h-96" alt="Mídia da publicação" onerror="this.style.display='none'">
          <?php endif; ?>

          <?php if (!empty($post['comments'])): ?>
            <div class="mt-6 space-y-3 bg-slate-50 rounded-2xl border border-slate-200 p-4">
              <h4 class="text-sm font-semibold text-slate-900">Comentários</h4>
              <?php foreach ($post['comments'] as $comment): ?>
                <div class="rounded-xl bg-white border border-slate-200 p-3">
                  <p class="text-xs text-slate-500 mb-1"><?= htmlspecialchars($comment['author_name']) ?></p>
                  <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
</main>
<script>
  function toggleNewPostForm() {
    const form = document.getElementById('new-post-form');
    const button = document.getElementById('new-post-button');
    form.classList.toggle('hidden');
    if (form.classList.contains('hidden')) {
      button.textContent = 'Nova publicação';
    } else {
      button.textContent = 'Fechar';
    }
  }

  function toggleList(type) {
    const list = document.getElementById('follow-list');
    if (!list) return;
    const userId = <?= (int)$currentUser['id'] ?>;
    const url = `index.php?action=follow_list&type=${type}&id=${userId}`;
    fetch(url)
      .then(response => response.text())
      .then(html => {
        list.innerHTML = html;
        list.classList.toggle('hidden', html.trim() === '');
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    const followBtn = document.getElementById('follow-btn');
    if (followBtn) {
      followBtn.addEventListener('click', function () {
        const targetId = this.dataset.targetId;
        fetch('index.php?action=toggle_follow', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'target_id=' + encodeURIComponent(targetId)
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              this.textContent = data.following ? 'Seguindo' : 'Seguir';
            }
          });
      });
    }
  });
</script>
</body>
</html>
