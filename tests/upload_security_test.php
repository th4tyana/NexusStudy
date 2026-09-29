<?php
declare(strict_types=1);

/**
 * Testes do FileUploadValidator (sem PHPUnit, sem banco e sem servidor web).
 * Uso:  php tests/upload_security_test.php
 * Saída: tabela PASS/FAIL e código de saída 1 se algum teste falhar.
 */

require_once __DIR__ . '/../services/FileUploadValidator.php';

$tmpDir = sys_get_temp_dir() . '/nexus_upload_tests_' . bin2hex(random_bytes(4));
mkdir($tmpDir, 0700, true);

$passed = 0;
$failed = 0;

/** Cria um arquivo de fixture e devolve o caminho. */
function fixture(string $dir, string $name, string $content): string
{
    $path = $dir . '/' . $name;
    file_put_contents($path, $content);
    return $path;
}

/** Cria um ZIP (docx/xlsx) em disco com as entradas informadas. */
function makeZip(string $dir, string $name, array $entries): string
{
    $path = $dir . '/' . $name;
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    foreach ($entries as $entry => $data) {
        $zip->addFromString($entry, $data);
    }
    $zip->close();
    return $path;
}

function check(string $label, bool $expectOk, array $result): void
{
    global $passed, $failed;
    $ok = $result['ok'] === $expectOk;
    $ok ? $passed++ : $failed++;
    printf(
        "[%s] %-62s esperado=%-8s obtido=%-8s %s\n",
        $ok ? 'PASS' : 'FAIL',
        $label,
        $expectOk ? 'ACEITO' : 'REJEITADO',
        $result['ok'] ? 'ACEITO' : 'REJEITADO',
        $result['ok'] ? '' : '(' . $result['error'] . ')'
    );
}

$V   = 'FileUploadValidator';
$DOC = FileUploadValidator::PROFILE_DOCUMENT;
$IMG = FileUploadValidator::PROFILE_IMAGE;

// ---------- Fixtures legítimas ----------
$pdf  = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Count 0/Kids[]>>endobj\n"
      . "trailer<</Root 1 0 R>>\n%%EOF\n";
$png  = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
$gif  = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
$webp = base64_decode('UklGRhoAAABXRUJQVlA4TA0AAAAvAAAAEAcQERGIiP4HAA==');
$jpg  = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');

$docxOk = ['[Content_Types].xml' => '<Types><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>',
           '_rels/.rels' => '<Relationships/>', 'word/document.xml' => '<w:document/>'];
$xlsxOk = ['[Content_Types].xml' => '<Types><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/></Types>',
           '_rels/.rels' => '<Relationships/>', 'xl/workbook.xml' => '<workbook/>'];

echo "=== ARQUIVOS LEGÍTIMOS (devem ser ACEITOS) ===\n";
check('PDF válido (relatorio.pdf)',            true, $V::validateFile(fixture($tmpDir, 'a.pdf', $pdf), 'relatorio.pdf', $DOC));
check('PDF com extensão maiúscula (EDITAL.PDF)', true, $V::validateFile(fixture($tmpDir, 'b.pdf', $pdf), 'EDITAL.PDF', $DOC));
check('DOCX válido (exportado do Google Docs)', true, $V::validateFile(makeZip($tmpDir, 'a.docx', $docxOk), 'guia.docx', $DOC));
check('XLSX válido (exportado do Google Sheets)', true, $V::validateFile(makeZip($tmpDir, 'a.xlsx', $xlsxOk), 'notas.xlsx', $DOC));
check('JPG válido',  true, $V::validateFile(fixture($tmpDir, 'a.jpg',  $jpg),  'foto.jpg',  $IMG));
check('PNG válido',  true, $V::validateFile(fixture($tmpDir, 'a.png',  $png),  'foto.png',  $IMG));
check('GIF válido',  true, $V::validateFile(fixture($tmpDir, 'a.gif',  $gif),  'foto.gif',  $IMG));
check('WEBP válido', true, $V::validateFile(fixture($tmpDir, 'a.webp', $webp), 'foto.webp', $IMG));

$realPdf = __DIR__ . '/../uploads/edital_9326199b82fb5d53.pdf';
if (is_file($realPdf)) {
    check('PDF real do projeto (uploads/edital_...pdf)', true, $V::validateFile($realPdf, 'edital.pdf', $DOC));
}

echo "\n=== ARQUIVOS MALICIOSOS / INVÁLIDOS (devem ser REJEITADOS) ===\n";
$exe = "MZ\x90\x00\x03\x00\x00\x00\x04\x00\x00\x00\xff\xff\x00\x00" . str_repeat("\x00", 200) . "This program cannot be run in DOS mode.";
check('.exe verdadeiro (virus.exe) no perfil documento', false, $V::validateFile(fixture($tmpDir, 'v1', $exe), 'virus.exe', $DOC));
check('.exe renomeado para .pdf (MIME real = executável)', false, $V::validateFile(fixture($tmpDir, 'v2', $exe), 'boleto.pdf', $DOC));
check('.exe renomeado para .jpg no perfil imagem',        false, $V::validateFile(fixture($tmpDir, 'v3', $exe), 'foto.jpg', $IMG));
check('.exe renomeado para .docx',                        false, $V::validateFile(fixture($tmpDir, 'v4', $exe), 'guia.docx', $DOC));
check('Dupla extensão perigosa (shell.php.pdf)',          false, $V::validateFile(fixture($tmpDir, 'v5', $pdf), 'shell.php.pdf', $DOC));
check('Dupla extensão perigosa (setup.exe.pdf)',          false, $V::validateFile(fixture($tmpDir, 'v6', $pdf), 'setup.exe.pdf', $DOC));
check('Script PHP (shell.php)',                           false, $V::validateFile(fixture($tmpDir, 'v7', '<?php system($_GET["c"]); ?>'), 'shell.php', $DOC));
check('Script PHP disfarçado de .pdf',                    false, $V::validateFile(fixture($tmpDir, 'v8', '<?php system($_GET["c"]); ?>'), 'nota.pdf', $DOC));
check('HTML/XSS disfarçado de .pdf',                      false, $V::validateFile(fixture($tmpDir, 'v9', '<html><script>alert(1)</script></html>'), 'aviso.pdf', $DOC));
check('SVG com script disfarçado de .png',                false, $V::validateFile(fixture($tmpDir, 'v10', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'), 'logo.png', $IMG));
check('SVG legítimo (extensão .svg não permitida)',       false, $V::validateFile(fixture($tmpDir, 'v11', '<svg xmlns="http://www.w3.org/2000/svg"/>'), 'logo.svg', $IMG));
check('PDF com /JavaScript embutido',                     false, $V::validateFile(fixture($tmpDir, 'v12', $pdf . "\n3 0 obj<</S/JavaScript/JS(app.alert(1))>>endobj\n"), 'edital.pdf', $DOC));
check('PDF com /Launch (executa programa)',               false, $V::validateFile(fixture($tmpDir, 'v13', $pdf . "\n4 0 obj<</S/Launch/F(cmd.exe)>>endobj\n"), 'edital.pdf', $DOC));
check('PDF com lixo antes da assinatura %PDF-',           false, $V::validateFile(fixture($tmpDir, 'v14', "GARBAGE\n" . $pdf), 'edital.pdf', $DOC));
check('PDF polyglot com <?php embutido',                  false, $V::validateFile(fixture($tmpDir, 'v15', $pdf . "\n<?php system(\$_GET['c']); ?>"), 'edital.pdf', $DOC));
check('GIF polyglot (GIF89a + <?php)',                    false, $V::validateFile(fixture($tmpDir, 'v16', "GIF89a<?php system(\$_GET['c']); ?>"), 'foto.gif', $IMG));
check('PNG real com <?php anexado ao final',              false, $V::validateFile(fixture($tmpDir, 'v17', $png . '<?php system($_GET["c"]); ?>'), 'foto.png', $IMG));
check('Imagem PNG com extensão .jpg (mismatch)',          false, $V::validateFile(fixture($tmpDir, 'v18', $png), 'foto.jpg', $IMG));
check('DOCM (macros) renomeado para .docx',               false, $V::validateFile(makeZip($tmpDir, 'v19.zip', $docxOk + ['word/vbaProject.bin' => 'MACRO']), 'guia.docx', $DOC));
check('DOCX com Content-Type macroEnabled',               false, $V::validateFile(makeZip($tmpDir, 'v20.zip', ['[Content_Types].xml' => '<Types><Override ContentType="application/vnd.ms-word.document.macroEnabled.main+xml"/></Types>', 'word/document.xml' => '<w/>']), 'guia.docx', $DOC));
check('ZIP qualquer renomeado para .docx (sem estrutura)', false, $V::validateFile(makeZip($tmpDir, 'v21.zip', ['a.txt' => 'oi']), 'guia.docx', $DOC));
check('XLSX com executável embutido (.exe)',              false, $V::validateFile(makeZip($tmpDir, 'v22.zip', $xlsxOk + ['xl/embeddings/x.exe' => 'MZ']), 'notas.xlsx', $DOC));
check('Extensão .xlsm (macros) bloqueada',                false, $V::validateFile(makeZip($tmpDir, 'v23.zip', $xlsxOk), 'notas.xlsm', $DOC));
check('ZIP bomb (~120 MB descompactado em um .docx)',     false, $V::validateFile(makeZip($tmpDir, 'v24.zip', $docxOk + ['word/bomb.bin' => str_repeat("\0", 120 * 1024 * 1024)]), 'guia.docx', $DOC));
check('Arquivo vazio (0 bytes)',                          false, $V::validateFile(fixture($tmpDir, 'v25', ''), 'vazio.pdf', $DOC));
check('PDF acima de 10 MB',                               false, $V::validateFile(fixture($tmpDir, 'v26', $pdf . str_repeat('A', 10 * 1024 * 1024 + 1)), 'grande.pdf', $DOC));
check('Imagem acima de 5 MB',                             false, $V::validateFile(fixture($tmpDir, 'v27', $png . str_repeat("\0", 5 * 1024 * 1024 + 1)), 'grande.png', $IMG));
check('Byte nulo no nome (relatorio.pdf\\0.exe)',         false, $V::validateFile(fixture($tmpDir, 'v28', $pdf), "relatorio.pdf\0.exe", $DOC));
check('Nome sem extensão',                                false, $V::validateFile(fixture($tmpDir, 'v29', $pdf), 'relatorio', $DOC));
check('Imagem enviada no campo de documento (.jpg)',      false, $V::validateFile(fixture($tmpDir, 'v30', $jpg), 'foto.jpg', $DOC));
check('DOCX enviado no campo de imagem',                  false, $V::validateFile(makeZip($tmpDir, 'v31.zip', $docxOk), 'guia.docx', $IMG));
check('Caminho no nome (../../evil.pdf) tratado por basename', true, $V::validateFile(fixture($tmpDir, 'v32', $pdf), '../../evil.pdf', $DOC));

echo "\n=== CAMADA DE TRANSPORTE (validateUpload) ===\n";
check('Fora de upload HTTP (is_uploaded_file = false)',   false, $V::validateUpload(['name' => 'a.pdf', 'tmp_name' => $tmpDir . '/a.pdf', 'error' => UPLOAD_ERR_OK], $DOC));
check('Campo enviado como array (media_file[])',          false, $V::validateUpload(['name' => ['a.pdf'], 'tmp_name' => ['x'], 'error' => [0]], $DOC));
check('Erro de upload do PHP (arquivo maior que ini)',    false, $V::validateUpload(['name' => 'a.pdf', 'tmp_name' => '', 'error' => UPLOAD_ERR_INI_SIZE], $DOC));

// ---------- Limpeza ----------
foreach (glob($tmpDir . '/*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

printf("\nResumo: %d aprovados, %d reprovados, %d no total.\n", $passed, $failed, $passed + $failed);
exit($failed > 0 ? 1 : 0);
