<?php

namespace App\Libraries;

class FtpDownloader
{
    protected $conn;
    protected string $baseDir;
    protected bool $connected = false;

    public function __construct()
    {
        $host    = env('FTP_HOST');
        $port    = (int) (env('FTP_PORT') ?? 21);
        $user    = env('FTP_USER');
        $pass    = env('FTP_PASS');
        $ssl     = filter_var(env('FTP_SSL') ?? false, FILTER_VALIDATE_BOOL);
        $passive = filter_var(env('FTP_PASSIVE') ?? true, FILTER_VALIDATE_BOOL);
        $this->baseDir = rtrim((string) env('FTP_BASEDIR'), '/');

        if (!$host || !$user || !$pass) {
            throw new \RuntimeException('Config FTP ausente no .env');
        }

        $this->conn = $ssl ? @ftp_ssl_connect($host, $port, 15)
                           : @ftp_connect($host, $port, 15);

        if (!$this->conn) {
            throw new \RuntimeException('Não foi possível conectar ao FTP');
        }

        if (!@ftp_login($this->conn, $user, $pass)) {
            throw new \RuntimeException('Login FTP falhou');
        }

        // Modo passivo geralmente necessário atrás de firewall/NAT
        @ftp_pasv($this->conn, $passive);

        // Se baseDir informado, tenta entrar
        if ($this->baseDir !== '' && $this->baseDir !== '/' && !@ftp_chdir($this->conn, $this->baseDir)) {
            throw new \RuntimeException('Diretório base do FTP inválido: ' . $this->baseDir);
        }

        $this->connected = true;
    }

    public function __destruct()
    {
        if ($this->connected && $this->conn) {
            @ftp_close($this->conn);
        }
    }

    private function remotePath(string $file): string
    {
        // Previne voltar diretório
        $file = ltrim($file, '/');
        return ($this->baseDir ? $this->baseDir . '/' : '') . $file;
    }

    public function exists(string $file): bool
    {
        $size = @ftp_size($this->conn, $this->remotePath($file));
        return $size !== -1 && $size !== false;
    }

    /**
     * Baixa o arquivo para um caminho temporário dentro de writable/ftp_cache
     * Retorna o caminho local.
     */
    public function downloadToTemp(string $file, ?string $localName = null): string
    {
        $remote = $this->remotePath($file);

        $cacheDir = FCPATH . 'temp';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        // Nome padrão: o basename do remoto
        $finalName = $localName ?: basename($file);

        // Sanitiza para evitar path traversal e caracteres problemáticos
        $finalName = basename($finalName);
        $finalName = preg_replace('/[^\w\-. ]+/u', '_', $finalName); // mantém letras, números, _ - . espaço

        // Se quiser forçar extensão do remoto quando não vier no nome
        $remoteExt = pathinfo($file, PATHINFO_EXTENSION);
        $finalExt  = pathinfo($finalName, PATHINFO_EXTENSION);
        if ($remoteExt && !$finalExt) {
            $finalName .= '.' . $remoteExt;
        }

        $tmp = rtrim($cacheDir, '/\\') . DIRECTORY_SEPARATOR . $finalName;

        // Evita sobrescrever: se existir, acrescenta sufixo (1), (2)...
        if (file_exists($tmp)) {
            $base = pathinfo($finalName, PATHINFO_FILENAME);
            $ext  = pathinfo($finalName, PATHINFO_EXTENSION);
            $i = 1;
            do {
                $candidate = $base . " ($i)" . ($ext ? "." . $ext : "");
                $tmp = rtrim($cacheDir, '/\\') . DIRECTORY_SEPARATOR . $candidate;
                $i++;
            } while (file_exists($tmp));
        }

        $fp = fopen($tmp, 'w+b');
        if (!$fp) {
            throw new \RuntimeException('Não foi possível criar o temp local');
        }

        $ok = @ftp_fget($this->conn, $fp, $remote, FTP_BINARY);
        fclose($fp);

        if (!$ok) {
            @unlink($tmp);
            throw new \RuntimeException('Falha ao baixar do FTP: ' . $file);
        }

        return $tmp;
    }

    
}
