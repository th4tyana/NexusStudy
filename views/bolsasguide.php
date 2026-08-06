<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ALTI — Guia de Estudos para Bolsas</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f8fafc; }
    .card { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; }
  </style>
</head>
<body>

<!-- ========== NAVBAR ========== -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-40">
  <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between gap-4">
    <a href="index.php?action=feed" class="flex items-center gap-2">
      <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0121 12c0 3.866-4.03 7-9 7s-9-3.134-9-7c0-.539.078-1.06.227-1.562L12 14z"/>
      </svg>
      <span class="font-bold text-slate-900 hidden sm:inline">ALTI</span>
    </a>

    <nav class="flex items-center gap-2">
      <a href="index.php?action=feed" class="flex flex-col items-center px-3 py-1 text-slate-500 hover:text-blue-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span class="text-[10px]">Feed</span>
      </a>
      <a href="index.php?action=bolsas_guide" class="flex flex-col items-center px-3 py-1 text-blue-600 font-semibold">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0121 12c0 3.866-4.03 7-9 7s-9-3.134-9-7c0-.539.078-1.06.227-1.562L12 14z"/>
        </svg>
        <span class="text-[10px]">Guias & Bolsas</span>
      </a>
      <a href="index.php?action=profile" class="flex flex-col items-center px-3 py-1 text-slate-500 hover:text-blue-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <span class="text-[10px]">Perfil</span>
      </a>
    </nav>
  </div>
</header>

<main class="max-w-5xl mx-auto px-4 py-6 space-y-5">

  <!-- BANNER SUPERIOR -->
  <div class="bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-2xl p-6 shadow-sm">
    <h1 class="text-2xl font-bold mb-1">Guia de Estudos para Bolsas</h1>
    <p class="text-sm opacity-90 leading-relaxed max-w-2xl">
      Saiba exatamente o que será avaliado no processo seletivo para o seu curso desejado. Filtre pelo seu curso e baixe o material de apoio.
    </p>
  </div>

  <!-- PAINEL DE FILTROS DA BUSCA -->
  <div class="card p-5">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="md:col-span-2">
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">QUAL CURSO VOCÊ DESEJA?</label>
        <div class="relative">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input type="text" id="filter-search" placeholder="Ex: Engenharia..." onkeyup="applyFilters()"
            class="w-full border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:border-blue-500 transition">
        </div>
      </div>
      <div>
        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">MODALIDADE</label>
        <select id="filter-modalidade" onchange="applyFilters()"
          class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm bg-slate-50 focus:bg-white focus:outline-none focus:border-blue-500 transition">
          <option value="Todas">Todas</option>
          <option value="SiSU / ENEM">SiSU / ENEM</option>
          <option value="Vestibular Próprio">Vestibular Próprio</option>
          <option value="Histórico Escolar">Histórico Escolar</option>
          <option value="Transferência">Transferência</option>
        </select>
      </div>
    </div>
  </div>

  <!-- LISTA DOS CARDS DE GUIA DE ESTUDOS -->
  <div id="guides-container" class="space-y-4">

    <?php if (empty($guides)): ?>
      <div class="card p-12 text-center text-slate-400 text-sm">
        Nenhum guia de estudos publicado no momento.
      </div>
    <?php endif; ?>

    <?php foreach ($guides as $guide): 
      $courseName = $guide['course_name'] ?? 'Curso sem Nome';
      $entryType  = $guide['entry_type'] ?? 'Vestibular';
      $weights    = is_array($guide['weights'] ?? null) ? $guide['weights'] : json_decode($guide['weights'] ?? '[]', true);
      $pdfUrl     = $guide['pdf_url'] ?? '#';
    ?>
      <div class="card p-6 guide-card transition hover:shadow-md" 
           data-course="<?= strtolower(htmlspecialchars($courseName)) ?>" 
           data-entry="<?= htmlspecialchars($entryType) ?>">

        <div class="grid grid-cols-1 md:grid-cols-[1fr_210px] gap-6 items-start">
          
          <!-- COLUNA ESQUERDA: Informações do Curso -->
          <div class="space-y-3">
            <div class="flex items-center gap-3 flex-wrap">
              <h2 class="text-xl font-bold text-slate-900"><?= htmlspecialchars($courseName) ?></h2>
              <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                </svg>
                Ingresso via <?= htmlspecialchars($entryType) ?>
              </span>
            </div>

            <!-- QUADRO "O QUE VOCÊ PRECISA FOCAR" -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-1.5">
              <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">O QUE VOCÊ PRECISA FOCAR:</span>
              <p class="text-sm text-slate-700 leading-relaxed">
                <?php if (!empty($weights)): ?>
                  Para este curso, o peso maior da sua nota será nas áreas de 
                  <strong class="text-slate-900 font-bold"><?= implode(', ', array_map('htmlspecialchars', $weights)) ?></strong>.
                <?php endif; ?>
                <?= nl2br(htmlspecialchars($guide['content'])) ?>
              </p>
            </div>
          </div>

          <!-- COLUNA DIREITA: Status & Downloads -->
          <div class="md:border-l md:border-slate-100 md:pl-6 space-y-3 flex flex-col justify-between h-full">
            <div>
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block text-center md:text-right mb-1">Status</span>
              <div class="flex items-center justify-center md:justify-end gap-1.5 text-xs font-bold text-emerald-600">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Inscrições Abertas
              </div>
            </div>

            <div class="space-y-2 pt-2">
              <?php if (!empty($pdfUrl) && $pdfUrl !== '#'): ?>
                <a href="<?= htmlspecialchars($pdfUrl) ?>" target="_blank" download
                   class="w-full bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 font-semibold py-2 px-3 rounded-lg text-xs flex items-center justify-center gap-2 transition border border-slate-200">
                  <svg class="w-4 h-4 text-red-500 group-hover:text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                  </svg>
                  Baixar Edital
                </a>
              <?php endif; ?>

              <a href="<?= htmlspecialchars($pdfUrl) ?>" target="_blank"
                 class="w-full bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 font-semibold py-2 px-3 rounded-lg text-xs flex items-center justify-center gap-2 transition border border-slate-200">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Material de Estudo
              </a>
            </div>
          </div>

        </div>
      </div>
    <?php endforeach; ?>

  </div>
</main>

<script>
  function applyFilters() {
    const searchVal = document.getElementById('filter-search').value.toLowerCase().trim();
    const modalidadeVal = document.getElementById('filter-modalidade').value;
    const cards = document.querySelectorAll('.guide-card');

    cards.forEach(card => {
      const course = card.getAttribute('data-course');
      const entry  = card.getAttribute('data-entry');

      const matchSearch     = !searchVal || course.includes(searchVal);
      const matchModalidade = (modalidadeVal === 'Todas') || (entry === modalidadeVal);

      if (matchSearch && matchModalidade) {
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  }
</script>
</body>
</html>