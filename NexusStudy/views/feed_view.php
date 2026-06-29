<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EduConnect — Feed</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f1f5f9; }
  .nav-link { transition: color .15s; }
  .nav-link:hover { color: #2563eb; }
  .card { background:#fff; border-radius:16px; border:1px solid #e2e8f0; }
  .btn-primary { background:#2563eb; color:#fff; font-weight:600; border-radius:10px; padding:.55rem 1.1rem; font-size:.8rem; transition:background .15s; }
  .btn-primary:hover { background:#1d4ed8; }
  .btn-ghost  { color:#475569; font-weight:500; border-radius:10px; padding:.55rem 1rem; font-size:.8rem; transition:background .15s; }
  .btn-ghost:hover  { background:#f1f5f9; }
  .btn-danger { color:#dc2626; font-weight:500; border-radius:10px; padding:.55rem 1rem; font-size:.8rem; transition:background .15s; }
  .btn-danger:hover { background:#fef2f2; }
  textarea:focus, input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
  .badge-inst { background:#eff6ff; color:#2563eb; font-size:.65rem; padding:.15rem .5rem; border-radius:99px; font-weight:700; letter-spacing:.03em; }
  .badge-verified { color:#2563eb; }
  .modal-overlay { position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:50;display:flex;align-items:center;justify-content:center; }
</style>
</head>
<body>

<!-- ========== NAVBAR ========== -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-40">
  <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between gap-4">

    <!-- Logo -->
    <a href="index.php?action=feed" class="flex items-center gap-2 flex-shrink-0">
      <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0121 12c0 3.866-4.03 7-9 7s-9-3.134-9-7c0-.539.078-1.06.227-1.562L12 14z"/>
      </svg>
      <span class="font-bold text-slate-900 hidden sm:inline">EduConnect</span>
    </a>

    <!-- Nav -->
    <nav class="flex items-center gap-1">
      <a href="index.php?action=feed" class="nav-link flex flex-col items-center px-3 py-1 text-blue-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span class="text-[10px] font-semibold">Feed</span>
      </a>
      <a href="index.php?action=profile" class="nav-link flex flex-col items-center px-3 py-1 text-slate-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <span class="text-[10px]">Perfil</span>
      </a>
      <!-- Premium -->
      <button onclick="openPremiumModal()"
        class="nav-link flex flex-col items-center px-3 py-1 text-amber-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
        <span class="text-[10px]">Premium</span>
      </button>
      <a href="index.php?action=logout" class="nav-link flex flex-col items-center px-3 py-1 text-slate-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        <span class="text-[10px]">Sair</span>
      </a>
    </nav>

  </div>
</header>

<!-- ========== FLASH ========== -->
<?php if (!empty($flash)): ?>
  <div id="flash-msg"
    class="max-w-5xl mx-auto px-4 pt-4">
    <div class="<?= $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800' ?> border rounded-xl px-4 py-3 text-sm flex items-center justify-between">
      <span><?= htmlspecialchars($flash['msg']) ?></span>
      <button onclick="document.getElementById('flash-msg').remove()" class="ml-4 text-lg leading-none opacity-50 hover:opacity-100">&times;</button>
    </div>
  </div>
<?php endif; ?>

<!-- ========== LAYOUT PRINCIPAL ========== -->
<main class="max-w-5xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6">

  <!-- COL ESQUERDA: Feed -->
  <div class="space-y-5">

    <!-- Busca global -->
    <div class="card p-4">
      <form method="GET" action="index.php?action=search_global" class="flex gap-2">
        <div class="flex-1 relative">
          <input type="text" id="global-search" name="q" value="<?= htmlspecialchars($searchQuery ?? '') ?>"
            placeholder="Buscar pessoas ou instituições"
            class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 transition">
          <div id="search-results" class="absolute z-20 left-0 right-0 mt-1 hidden rounded-xl border border-slate-200 bg-white shadow-lg max-h-60 overflow-auto"></div>
        </div>
        <button type="submit" class="btn-primary">Buscar</button>
      </form>

      <?php if (!empty($searchQuery)): ?>
        <div class="mt-3">
          <?php if (!empty($searchResults)): ?>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Resultados</p>
            <div class="space-y-2">
              <?php foreach ($searchResults as $result): ?>
                <?php $profileAction = ($result['result_type'] ?? 'user') === 'institution' ? 'institution_profile' : 'user_profile'; ?>
                <a href="index.php?action=<?= $profileAction ?>&id=<?= (int)$result['id'] ?>"
                  class="flex items-center gap-3 rounded-xl border border-slate-200 p-2 hover:bg-slate-50 transition">
                  <?php if (!empty($result['avatar_url'])): ?>
                    <img src="<?= htmlspecialchars($result['avatar_url']) ?>"
                      class="w-10 h-10 rounded-full object-cover border border-slate-200" alt="Avatar">
                  <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">
                      <?= strtoupper(substr($result['name'] ?? 'U', 0, 1)) ?>
                    </div>
                  <?php endif; ?>
                  <div class="min-w-0">
                    <div class="text-sm font-semibold text-slate-800 truncate"><?= htmlspecialchars($result['name'] ?? '') ?></div>
                    <div class="text-xs text-slate-400">
                      <?= ($result['result_type'] ?? 'user') === 'institution' ? 'Instituição' : 'Usuário' ?>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-sm text-slate-500 mt-2">Nenhum resultado encontrado para "<?= htmlspecialchars($searchQuery) ?>".</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Caixa de nova publicação -->
    <div class="card p-5">
      <div class="flex items-center gap-3 mb-3">
        <?php if (!empty($currentUser['avatar_url'])): ?>
          <img src="<?= htmlspecialchars($currentUser['avatar_url']) ?>"
            class="w-10 h-10 rounded-full object-cover border border-slate-200" alt="">
        <?php else: ?>
          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">
            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
          </div>
        <?php endif; ?>
        <button onclick="document.getElementById('new-post-form').classList.toggle('hidden')"
          class="flex-1 text-left bg-slate-100 hover:bg-slate-200 text-slate-500 text-sm rounded-full px-4 py-2.5 transition">
          O que você quer compartilhar, <?= htmlspecialchars((string)($currentUser['name'] ?? 'usuário')) ?>?
        </button>
      </div>

      <!-- Formulário expandível -->
      <form id="new-post-form" method="POST" action="index.php?action=post_create" enctype="multipart/form-data" class="hidden border-t border-slate-100 pt-4 space-y-3">
        <input type="hidden" name="redirect_to" value="feed">
        <textarea name="content" rows="3" placeholder="Escreva sua publicação..." required
          class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-slate-50 resize-none transition"></textarea>
        <div>
          <label class="block text-xs font-semibold text-slate-400 mb-1 uppercase tracking-wide">Imagem (opcional)</label>
          <input type="file" name="media_file" accept="image/*"
            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 transition" />
          <p class="text-xs text-slate-400 mt-2">A imagem será enviada diretamente e exibida junto à publicação.</p>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="document.getElementById('new-post-form').classList.add('hidden')"
            class="btn-ghost">Cancelar</button>
          <button type="submit" class="btn-primary">Publicar</button>
        </div>
      </form>
    </div>

    <!-- Posts -->
    <?php if (empty($posts)): ?>
      <div class="card p-12 text-center text-slate-400 text-sm">
        Nenhuma publicação ainda. Seja o primeiro a compartilhar algo!
      </div>
    <?php endif; ?>

    <?php foreach ($posts as $post):
      $isAuthor  = ((int)$post['author_id'] === (int)($_SESSION['user_id'] ?? 0));
      $isInst    = ($_SESSION['user_type'] ?? '') === 'institution';
      $canEdit   = $isAuthor || $isInst;
      $postTime  = date('d/m/Y H:i', strtotime($post['created_at']));
    ?>
    <div class="card overflow-hidden" id="post-<?= $post['id'] ?>">

      <!-- Cabeçalho do post -->
      <div class="p-5 pb-3 flex items-start justify-between gap-3">
          <?php if ($post['author_type'] === 'institution'): ?>
            <a href="index.php?action=institution_profile&id=<?= (int)$post['author_id'] ?>"
              class="flex items-center gap-3 hover:text-blue-600 transition">
          <?php else: ?>
            <div class="flex items-center gap-3">
          <?php endif; ?>

          <?php if (!empty($post['author_avatar'])): ?>
            <img src="<?= htmlspecialchars($post['author_avatar']) ?>"
              class="w-10 h-10 rounded-full object-cover border border-slate-200 flex-shrink-0" alt="Avatar do autor">
          <?php else: ?>
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm flex-shrink-0">
              <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
            </div>
          <?php endif; ?>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-semibold text-slate-900 text-sm"><?= htmlspecialchars($post['author_name']) ?></span>
              <?php if ($post['author_type'] === 'institution'): ?>
                <span class="badge-inst">Instituição</span>
                <svg class="w-3.5 h-3.5 badge-verified" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              <?php endif; ?>
            </div>
            <span class="text-xs text-slate-400"><?= $postTime ?></span>
          </div>

          <?php if ($post['author_type'] === 'institution'): ?>
            </a>
          <?php else: ?>
            </div>
          <?php endif; ?>

        <!-- Ações (editar / excluir) -->
        <?php if ($canEdit): ?>
          <div class="flex items-center gap-1 flex-shrink-0">
            <button onclick="openEditModal(<?= $post['id'] ?>, <?= htmlspecialchars(json_encode($post['content'])) ?>, <?= htmlspecialchars(json_encode($post['media_url'] ?? '')) ?>)"
              class="btn-ghost p-2 rounded-lg" title="Editar">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>
            <a href="index.php?action=post_delete&id=<?= $post['id'] ?>"
              onclick="return confirm('Confirma a exclusão desta publicação?')"
              class="btn-danger p-2 rounded-lg" title="Excluir">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Conteúdo -->
      <div class="px-5 pb-3">
        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
      </div>

      <!-- Mídia -->
      <?php if (!empty($post['media_url'])): ?>
        <img src="<?= htmlspecialchars($post['media_url']) ?>"
          class="w-full max-h-80 object-cover" alt="Mídia da publicação"
          onerror="this.style.display='none'">
      <?php endif; ?>

      <!-- Contadores -->
      <div class="px-5 py-2.5 flex items-center gap-4 text-xs text-slate-400 border-t border-slate-100">
        <span id="like-count-<?= $post['id'] ?>"><?= $post['like_count'] ?> curtida<?= $post['like_count'] != 1 ? 's' : '' ?></span>
        <span><?= count($post['comments']) ?> comentário<?= count($post['comments']) != 1 ? 's' : '' ?></span>
      </div>

      <!-- Botões de ação -->
      <div class="px-5 py-2 flex items-center gap-1 border-t border-slate-100">
        <button id="like-btn-<?= $post['id'] ?>"
          onclick="toggleLike(<?= $post['id'] ?>)"
          class="flex items-center gap-1.5 btn-ghost <?= $post['liked_by_me'] ? 'text-blue-600 font-semibold' : '' ?>">
          <svg class="w-4 h-4" fill="<?= $post['liked_by_me'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
          </svg>
          Curtir
        </button>
        <button onclick="toggleComments(<?= $post['id'] ?>)"
          class="flex items-center gap-1.5 btn-ghost">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
          Comentar
        </button>
      </div>

      <!-- Seção de comentários (colapsada por padrão) -->
      <div id="comments-<?= $post['id'] ?>" class="hidden border-t border-slate-100 bg-slate-50">
        <!-- Comentários existentes -->
        <div class="px-5 pt-3 space-y-3" id="comment-list-<?= $post['id'] ?>">
          <?php foreach ($post['comments'] as $comment): ?>
            <div class="flex gap-2.5">
              <?php if (!empty($comment['author_avatar'])): ?>
                <img src="<?= htmlspecialchars($comment['author_avatar']) ?>"
                  class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
              <?php else: ?>
                <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-[10px] font-bold flex-shrink-0">
                  <?= strtoupper(substr($comment['author_name'], 0, 1)) ?>
                </div>
              <?php endif; ?>
              <div class="bg-white rounded-xl px-3 py-2 text-xs flex-1 border border-slate-200">
                <span class="font-semibold text-slate-800"><?= htmlspecialchars($comment['author_name']) ?></span>
                <p class="text-slate-600 mt-0.5"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Formulário novo comentário -->
        <form method="POST" action="index.php?action=comment_create" class="px-5 py-3 flex gap-2.5 items-start">
          <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
          <?php if (!empty($currentUser['avatar_url'])): ?>
            <img src="<?= htmlspecialchars($currentUser['avatar_url']) ?>"
              class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
          <?php else: ?>
            <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-[10px] font-bold flex-shrink-0">
              <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
            </div>
          <?php endif; ?>
          <div class="flex-1 flex gap-2">
            <input type="text" name="content" placeholder="Adicione um comentário..." required
              class="flex-1 border border-slate-200 rounded-full px-3 py-1.5 text-xs bg-white transition">
            <button type="submit" class="btn-primary py-1.5 px-3 text-xs">Enviar</button>
          </div>
        </form>
      </div>

    </div><!-- /post -->
    <?php endforeach; ?>

  </div><!-- /col-left -->

  <!-- COL DIREITA: Sidebar -->
  <aside class="space-y-5 hidden lg:block">

    <!-- Card do usuário logado -->
    <div class="card overflow-hidden">
      <div class="h-16 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
      <div class="px-5 pb-5 -mt-8">
        <?php if (!empty($currentUser['avatar_url'])): ?>
          <img src="<?= htmlspecialchars($currentUser['avatar_url']) ?>"
            class="w-16 h-16 rounded-full object-cover border-4 border-white mb-2" alt="">
        <?php else: ?>
          <div class="w-16 h-16 rounded-full bg-white border-4 border-white shadow flex items-center justify-center text-blue-600 font-bold text-xl mb-2">
            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
          </div>
        <?php endif; ?>
        <p class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($currentUser['name'] ?? '') ?></p>
        <?php if ($currentUser['user_type'] === 'institution'): ?>
          <span class="badge-inst">Instituição</span>
        <?php else: ?>
          <span class="text-xs text-slate-400"><?= htmlspecialchars($currentUser['extra_info'] ?? 'Estudante') ?></span>
        <?php endif; ?>
        <?php if (!empty($currentUser['bio'])): ?>
          <p class="text-xs text-slate-500 mt-2 leading-relaxed"><?= htmlspecialchars($currentUser['bio']) ?></p>
        <?php endif; ?>
        <a href="index.php?action=edit_profile"
          class="mt-3 block text-center border border-slate-300 hover:border-blue-500 text-slate-600 hover:text-blue-600 text-xs font-semibold py-2 rounded-xl transition">
          Editar perfil
        </a>
      </div>
    </div>

    <!-- Premium -->
    <div class="card p-5 bg-gradient-to-br from-amber-50 to-orange-50 border-amber-200">
      <div class="flex items-center gap-2 mb-2">
        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
        <span class="font-bold text-amber-800 text-sm">EduConnect Premium</span>
      </div>
      <p class="text-xs text-amber-700 leading-relaxed mb-3">
        Destaque suas publicações para estudantes de toda a plataforma e amplie o alcance da sua instituição.
      </p>
      <button onclick="openPremiumModal()"
        class="w-full bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold py-2.5 rounded-xl transition">
        Conhecer planos
      </button>
    </div>

    <!-- Moderação (apenas instituições) -->
    <?php if (($_SESSION['user_type'] ?? '') === 'institution'): ?>
      <div class="card p-5 border-indigo-100 bg-indigo-50">
        <div class="flex items-center gap-2 mb-2">
          <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
          <span class="font-bold text-indigo-800 text-sm">Poder de Moderacao</span>
        </div>
        <p class="text-xs text-indigo-600 leading-relaxed">
          Como instituicao, voce pode editar ou remover qualquer publicacao do feed (RN02).
        </p>
      </div>
    <?php endif; ?>

    <!-- Rodapé -->
    <p class="text-xs text-slate-400 text-center px-2">
      EduConnect &copy; <?= date('Y') ?><br>
      Projeto Academico — Arquitetura MVC + PHP
    </p>
  </aside>

</main>

<!-- ========== MODAL: EDITAR POST ========== -->
<div id="modal-edit" class="modal-overlay hidden" onclick="if(event.target===this) closeEditModal()">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-900">Editar publicacao</h3>
      <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-700 text-2xl leading-none">&times;</button>
    </div>
    <form method="POST" action="index.php?action=post_update" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="post_id" id="edit-post-id">
      <input type="hidden" name="existing_media_url" id="edit-existing-media" value="">
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Conteudo</label>
        <textarea name="content" id="edit-content" rows="5" required
          class="w-full border border-slate-200 rounded-xl p-3 text-sm bg-slate-50 resize-none transition"></textarea>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Substituir imagem (opcional)</label>
        <input type="file" name="media_file" id="edit-media" accept="image/*"
          class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 transition" />
        <p class="text-xs text-slate-400 mt-2">Envie uma nova imagem para atualizar esta publicação.</p>
      </div>
      <div class="flex gap-2 justify-end pt-1">
        <button type="button" onclick="closeEditModal()" class="btn-ghost">Cancelar</button>
        <button type="submit" class="btn-primary">Salvar alteracoes</button>
      </div>
    </form>
  </div>
</div>

<!-- ========== MODAL: PREMIUM ========== -->
<div id="modal-premium" class="modal-overlay hidden" onclick="if(event.target===this) closePremiumModal()">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-8 text-center">
    <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
      <svg class="w-8 h-8 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
      </svg>
    </div>
    <h3 class="font-bold text-slate-900 text-lg mb-2">EduConnect Premium</h3>
    <p class="text-slate-500 text-sm mb-6 leading-relaxed">
      Este recurso ainda nao esta disponivel.<br>
      Em breve, instituicoes poderao adquirir pacotes de destaque para ampliar seu alcance na plataforma (RN05).
    </p>
    <button onclick="closePremiumModal()"
      class="w-full bg-slate-900 hover:bg-slate-700 text-white font-semibold py-3 rounded-xl text-sm transition">
      Entendido
    </button>
  </div>
</div>

<script>
  // ----- Modais -----
  function openEditModal(id, content, mediaUrl) {
    document.getElementById('edit-post-id').value       = id;
    document.getElementById('edit-content').value       = content;
    document.getElementById('edit-existing-media').value = mediaUrl || '';
    document.getElementById('edit-media').value          = '';
    document.getElementById('modal-edit').classList.remove('hidden');
  }
  function closeEditModal() {
    document.getElementById('modal-edit').classList.add('hidden');
  }
  function openPremiumModal() {
    document.getElementById('modal-premium').classList.remove('hidden');
  }
  function closePremiumModal() {
    document.getElementById('modal-premium').classList.add('hidden');
  }

  // ----- Comentários: toggle -----
  function toggleComments(postId) {
    const el = document.getElementById('comments-' + postId);
    el.classList.toggle('hidden');
  }

  // ----- Like: AJAX -----
  async function toggleLike(postId) {
    const res   = await fetch('index.php?action=like_toggle', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'post_id=' + postId
    });
    const data  = await res.json();
    const btn   = document.getElementById('like-btn-' + postId);
    const count = document.getElementById('like-count-' + postId);

    const plural = data.count !== 1 ? 's' : '';
    count.textContent = data.count + ' curtida' + plural;

    const svg = btn.querySelector('svg');
    if (data.action === 'added') {
      btn.classList.add('text-blue-600', 'font-semibold');
      svg.setAttribute('fill', 'currentColor');
    } else {
      btn.classList.remove('text-blue-600', 'font-semibold');
      svg.setAttribute('fill', 'none');
    }
  }

  // ----- Auto-ocultar flash após 4s -----
  setTimeout(() => {
    const f = document.getElementById('flash-msg');
    if (f) f.remove();
  }, 4000);
</script>
<script>
  const searchInput = document.getElementById('global-search');
  const searchResults = document.getElementById('search-results');

  if (searchInput && searchResults) {
    searchInput.addEventListener('input', function () {
      const term = this.value.trim();
      if (term.length < 2) {
        searchResults.innerHTML = '';
        searchResults.classList.add('hidden');
        return;
      }

      fetch('index.php?action=search_autocomplete&q=' + encodeURIComponent(term))
        .then(response => response.json())
        .then(data => {
          if (!data.length) {
            searchResults.innerHTML = '<div class="p-3 text-sm text-slate-500">Nenhum resultado.</div>';
            searchResults.classList.remove('hidden');
            return;
          }

          searchResults.innerHTML = data.map(item => {
            const profileAction = item.result_type === 'institution' ? 'institution_profile' : 'user_profile';
            const avatar = item.avatar_url
              ? `<img src="${item.avatar_url}" class="w-9 h-9 rounded-full object-cover" alt="Avatar">`
              : `<div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">${(item.name || 'U').charAt(0).toUpperCase()}</div>`;
            return `<a href="index.php?action=${profileAction}&id=${item.id}" class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50">${avatar}<span class="text-sm text-slate-700">${item.name}</span></a>`;
          }).join('');
          searchResults.classList.remove('hidden');
        });
    });

    document.addEventListener('click', function (event) {
      if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
        searchResults.classList.add('hidden');
      }
    });
  }
</script>
</body>
</html>
