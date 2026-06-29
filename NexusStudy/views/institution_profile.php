<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EduConnect — Perfil da Instituição</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f1f5f9; }
  .card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; }
  .btn-ghost { color:#475569; font-weight:500; border-radius:10px; padding:.65rem 1.2rem; font-size:.85rem; transition:background .15s; }
  .btn-ghost:hover { background:#f1f5f9; }
  .badge { display:inline-flex; align-items:center; gap:.35rem; padding:.35rem .75rem; border-radius:999px; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
  .badge-institution { background:#eff6ff; color:#2563eb; }
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
      <a href="index.php?action=feed" class="btn-ghost text-sm">Feed</a>
      <a href="index.php?action=profile" class="btn-ghost text-sm">Meu perfil</a>
      <a href="index.php?action=logout" class="text-xs text-slate-400 hover:text-slate-700 px-2">Sair</a>
    </div>
  </div>
</header>

<main class="max-w-5xl mx-auto px-4 py-8 space-y-6">
  <section class="card p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="flex items-center gap-4">
        <?php if (!empty($institution['avatar_url'])): ?>
          <img src="<?= htmlspecialchars($institution['avatar_url']) ?>" class="w-20 h-20 rounded-full object-cover border border-slate-200" alt="Avatar da instituicao">
        <?php else: ?>
          <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-2xl border border-slate-200">
            <?= strtoupper(substr($institution['name'] ?? 'I', 0, 1)) ?>
          </div>
        <?php endif; ?>
        <div>
          <h1 class="text-2xl font-bold text-slate-900"><?= htmlspecialchars($institution['name'] ?? '') ?></h1>
          <div class="mt-3 flex flex-wrap gap-2">
            <span class="badge badge-institution">Instituição</span>
            <?php if (!empty($institution['extra_info'])): ?>
              <span class="badge bg-slate-100 text-slate-700"><?= htmlspecialchars($institution['extra_info']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="text-right">
        <p class="text-xs text-slate-400">E-mail institucional</p>
        <p class="text-sm font-semibold text-slate-800"><?= htmlspecialchars($institution['email'] ?? '') ?></p>
      </div>
    </div>
    <p class="text-sm text-slate-500 mt-4"><?= nl2br(htmlspecialchars($institution['bio'] ?? 'Sem descricao cadastrada no perfil.')) ?></p>
  </section>

  <section class="space-y-4">
    <div>
      <h2 class="text-xl font-bold text-slate-900">Publicações da instituição</h2>
      <p class="text-sm text-slate-500">Veja todas as postagens publicadas por esta entidade.</p>
    </div>

    <?php if (empty($posts)): ?>
      <div class="card p-8 text-center text-slate-400 text-sm">
        Esta instituição ainda não publicou nada.
      </div>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
      <article class="post-card overflow-hidden">
        <div class="p-5 border-b border-slate-100">
          <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-semibold text-slate-900">Publicado em <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></h3>
            <span class="text-xs text-slate-400">Comentários: <?= (int)($post['comment_count'] ?? 0) ?></span>
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
</body>
</html>
