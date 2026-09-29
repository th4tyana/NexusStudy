<?php
declare(strict_types=1);

/**
 * FileUploadValidator — validação de uploads com defesa em profundidade.
 *
 * Camadas aplicadas a TODO arquivo enviado (nenhuma confia no navegador):
 *   1. Status do upload PHP + is_uploaded_file() (arquivo veio mesmo de um POST HTTP)
 *   2. Tamanho (não vazio e dentro do limite do perfil)
 *   3. Nome: sem byte nulo, sem extensão perigosa embutida (ex.: "x.php.pdf", "x.exe.pdf")
 *   4. Allowlist de extensões por perfil (image | document)
 *   5. MIME real via finfo (lê o conteúdo; NUNCA usa $_FILES['type'], que é do cliente)
 *   6. Content-Sniffing: assinatura (magic bytes) e estrutura interna coerentes com a extensão
 *   7. Conteúdo ativo/malicioso: código PHP embutido, JavaScript/Launch em PDF, macros em Office
 *   8. Armazenamento: nome aleatório + extensão definida pelo SERVIDOR (nunca pelo cliente)
 *
 * Observação: Google Docs / Google Sheets nativos não existem como arquivo; ao baixar,
 * o Google exporta para DOCX / XLSX / PDF — por isso esses são os formatos aceitos.
 */
final class FileUploadValidator
{
    public const PROFILE_IMAGE    = 'image';
    public const PROFILE_DOCUMENT = 'document';

    private const MIME_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    private const MIME_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /** Allowlist: extensão => MIMEs aceitos (detectados pelo servidor). */
    private const PROFILES = [
        self::PROFILE_IMAGE => [
            'max_size' => 5 * 1024 * 1024,
            'label'    => 'imagens JPEG, PNG, GIF ou WEBP',
            'types'    => [
                'jpg'  => ['image/jpeg'],
                'jpeg' => ['image/jpeg'],
                'png'  => ['image/png'],
                'gif'  => ['image/gif'],
                'webp' => ['image/webp'],
            ],
        ],
        self::PROFILE_DOCUMENT => [
            'max_size' => 10 * 1024 * 1024,
            'label'    => 'PDF, Word (.docx) ou Excel (.xlsx)',
            'types'    => [
                'pdf'  => ['application/pdf'],
                // 'application/zip' é fallback de libmagic antigo; a estrutura interna é validada logo depois.
                'docx' => [self::MIME_DOCX, 'application/zip'],
                'xlsx' => [self::MIME_XLSX, 'application/zip'],
            ],
        ],
    ];

    /** Extensões que nunca podem aparecer DEPOIS do nome base (bloqueia "arquivo.exe.pdf"). */
    private const BLOCKED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'pht', 'inc',
        'exe', 'dll', 'msi', 'bat', 'cmd', 'com', 'scr', 'cpl', 'lnk', 'hta',
        'js', 'jse', 'vbs', 'vbe', 'wsf', 'ps1', 'sh', 'jar', 'py', 'pl', 'cgi',
        'html', 'htm', 'svg', 'xml',
        'docm', 'xlsm', 'pptm', 'dotm', 'xltm', 'xlam',
        'htaccess',
    ];

    /** Mapa extensão => constante IMAGETYPE_* devolvida por getimagesize(). */
    private const IMAGE_TYPES = [
        'jpg'  => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'png'  => IMAGETYPE_PNG,
        'gif'  => IMAGETYPE_GIF,
        'webp' => IMAGETYPE_WEBP,
    ];

    /** Limites anti zip-bomb para DOCX/XLSX. */
    private const ZIP_MAX_ENTRIES           = 2000;
    private const ZIP_MAX_UNCOMPRESSED_SIZE = 100 * 1024 * 1024;

    /**
     * Valida um item de $_FILES e o move para $destDir com nome aleatório.
     *
     * @return array{ok:bool,error:?string,ext:?string,mime:?string,file_name:?string}
     */
    public static function storeUpload(array $file, string $profile, string $destDir, string $prefix = ''): array
    {
        $result = self::validateUpload($file, $profile);
        if (!$result['ok']) {
            return $result;
        }

        if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            return self::fail('Não foi possível criar o diretório de upload.');
        }

        $fileName    = sprintf('%s%s.%s', $prefix, bin2hex(random_bytes(16)), $result['ext']);
        $destination = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
            return self::fail('Erro ao salvar o arquivo enviado.');
        }

        @chmod($destination, 0644);
        $result['file_name'] = $fileName;

        return $result;
    }

    /**
     * Camada 1: valida o item de $_FILES (erro do PHP, formato do array, origem HTTP).
     *
     * @return array{ok:bool,error:?string,ext:?string,mime:?string,file_name:?string}
     */
    public static function validateUpload(array $file, string $profile): array
    {
        foreach (['name', 'tmp_name', 'error'] as $key) {
            // Bloqueia "media_file[]" (arrays) que causariam TypeError/bypass.
            if (!isset($file[$key]) || is_array($file[$key])) {
                return self::fail('Envio de arquivo inválido.');
            }
        }

        $error = (int) $file['error'];
        if ($error !== UPLOAD_ERR_OK) {
            return self::fail(self::uploadErrorMessage($error));
        }

        $tmp = (string) $file['tmp_name'];
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return self::fail('Envio de arquivo inválido.');
        }

        return self::validateFile($tmp, (string) $file['name'], $profile);
    }

    /**
     * Camadas 2 a 7: valida um arquivo em disco (testável sem servidor HTTP).
     *
     * @return array{ok:bool,error:?string,ext:?string,mime:?string,file_name:?string}
     */
    public static function validateFile(string $path, string $originalName, string $profile): array
    {
        $cfg = self::PROFILES[$profile] ?? null;
        if ($cfg === null) {
            throw new InvalidArgumentException('Perfil de upload desconhecido: ' . $profile);
        }
        $label = $cfg['label'];

        // 2. Tamanho
        $size = is_file($path) ? filesize($path) : false;
        if ($size === false || $size === 0) {
            return self::fail('O arquivo enviado está vazio ou é ilegível.');
        }
        if ($size > $cfg['max_size']) {
            return self::fail(sprintf('O arquivo deve ter no máximo %d MB.', intdiv($cfg['max_size'], 1024 * 1024)));
        }

        // 3. Nome (byte nulo / dupla extensão perigosa)
        if (str_contains($originalName, "\0")) {
            return self::fail('Nome de arquivo inválido.');
        }
        $parts = explode('.', strtolower(basename(str_replace('\\', '/', $originalName))));
        if (count($parts) < 2) {
            return self::fail("Somente {$label} são permitidos.");
        }
        foreach (array_slice($parts, 1) as $piece) {
            if (in_array($piece, self::BLOCKED_EXTENSIONS, true)) {
                return self::fail('Tipo de arquivo não permitido por motivos de segurança.');
            }
        }

        // 4. Allowlist de extensão
        $ext = end($parts);
        if (!isset($cfg['types'][$ext])) {
            return self::fail("Somente {$label} são permitidos.");
        }

        // 5. MIME real (conteúdo, não o Content-Type enviado pelo cliente)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($path);
        if ($mime === false || !in_array($mime, $cfg['types'][$ext], true)) {
            return self::fail("O conteúdo do arquivo não corresponde à extensão .{$ext}. Somente {$label} são permitidos.");
        }

        // 6 e 7. Content-Sniffing + conteúdo ativo, por família de formato
        $canonicalExt = $ext === 'jpeg' ? 'jpg' : $ext;
        $problem = match ($canonicalExt) {
            'jpg', 'png', 'gif', 'webp' => self::inspectImage($path, $ext),
            'pdf'                       => self::inspectPdf($path),
            'docx'                      => self::inspectOoxml($path, 'word/document.xml'),
            'xlsx'                      => self::inspectOoxml($path, 'xl/workbook.xml'),
            default                     => 'Tipo de arquivo não suportado.',
        };
        if ($problem !== null) {
            return self::fail($problem);
        }

        return ['ok' => true, 'error' => null, 'ext' => $canonicalExt, 'mime' => $mime, 'file_name' => null];
    }

    private static function inspectImage(string $path, string $ext): ?string
    {
        // Content-sniffing: getimagesize() valida cabeçalho e dimensões reais da imagem.
        $info = @getimagesize($path);
        if ($info === false || ($info[0] ?? 0) < 1 || ($info[1] ?? 0) < 1) {
            return 'O arquivo não é uma imagem válida.';
        }
        if (($info[2] ?? null) !== self::IMAGE_TYPES[$ext]) {
            return 'O conteúdo da imagem não corresponde à extensão informada.';
        }
        // Polyglot (imagem válida com código PHP embutido).
        if (self::containsPhpTag($path)) {
            return 'Arquivo rejeitado: código executável detectado no conteúdo.';
        }
        return null;
    }

    private static function inspectPdf(string $path): ?string
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return 'Não foi possível ler o arquivo.';
        }
        $head = (string) fread($handle, 8);
        fclose($handle);

        // Assinatura obrigatoriamente no byte 0 (impede polyglots com lixo no início).
        if (!str_starts_with($head, '%PDF-')) {
            return 'O arquivo não é um PDF válido.';
        }

        $content = (string) file_get_contents($path);
        if (str_contains($content, '<?php')) {
            return 'Arquivo rejeitado: código executável detectado no conteúdo.';
        }
        // Conteúdo ativo (melhor esforço; ver limitações na documentação).
        if (preg_match('#/(JavaScript|Launch|EmbeddedFile)\b#', $content) === 1) {
            return 'PDF rejeitado: contém scripts, execução de programas ou arquivos embutidos.';
        }
        return null;
    }

    private static function inspectOoxml(string $path, string $requiredEntry): ?string
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            return 'O arquivo não é um documento Office válido.';
        }

        try {
            if ($zip->numFiles < 1 || $zip->numFiles > self::ZIP_MAX_ENTRIES) {
                return 'Documento Office rejeitado: estrutura interna suspeita.';
            }

            $total        = 0;
            $hasContent   = false;
            $hasRequired  = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if ($stat === false) {
                    return 'Documento Office corrompido.';
                }
                $name  = (string) $stat['name'];
                $lower = strtolower($name);
                $total += (int) $stat['size'];

                if ($total > self::ZIP_MAX_UNCOMPRESSED_SIZE) {
                    return 'Documento Office rejeitado: tamanho descompactado excessivo.';
                }
                if (str_contains($name, '../') || str_starts_with($name, '/')) {
                    return 'Documento Office rejeitado: caminho interno inválido.';
                }
                // Macros e executáveis embutidos.
                if (str_contains($lower, 'vbaproject.bin')
                    || preg_match('#\.(exe|dll|bat|cmd|scr|js|vbs|ps1|jar|php)$#', $lower) === 1) {
                    return 'Documento rejeitado: contém macros ou arquivos executáveis embutidos.';
                }
                if ($name === '[Content_Types].xml') {
                    $hasContent = true;
                    $types = (string) $zip->getFromIndex($i);
                    // docm/xlsm renomeado para .docx/.xlsx
                    if (stripos($types, 'macroEnabled') !== false) {
                        return 'Documento rejeitado: arquivo habilitado para macros.';
                    }
                }
                if ($name === $requiredEntry) {
                    $hasRequired = true;
                }
            }

            if (!$hasContent || !$hasRequired) {
                return 'O arquivo não é um documento Office válido.';
            }
            return null;
        } finally {
            $zip->close();
        }
    }

    private static function containsPhpTag(string $path): bool
    {
        $content = file_get_contents($path);
        return $content !== false && str_contains($content, '<?php');
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o tamanho máximo permitido.',
            UPLOAD_ERR_PARTIAL                        => 'O envio foi interrompido. Tente novamente.',
            UPLOAD_ERR_NO_FILE                        => 'Nenhum arquivo foi enviado.',
            default                                   => 'Falha no upload do arquivo. Tente novamente.',
        };
    }

    /** @return array{ok:bool,error:?string,ext:?string,mime:?string,file_name:?string} */
    private static function fail(string $message): array
    {
        return ['ok' => false, 'error' => $message, 'ext' => null, 'mime' => null, 'file_name' => null];
    }
}
