<?php

namespace App\Controllers;

use App\Models\ManifestacaoModel;
use App\Models\ManifestacaoAnexoModel;

/**
 * Controller de Anexos.
 * Download seguro e abertura no navegador (inline) com verificação de autorização.
 */
class AnexosController extends BaseController
{
    protected $helpers = ['ouvidoria'];

    /**
     * Abre o anexo no navegador (inline). PDF e imagens abrem na aba; outros podem baixar.
     * Rota: /ouvidoria/anexos/abrir/{id}
     */
    public function abrir(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!$usuario) {
            return $this->response->setStatusCode(403)->setBody('Acesso negado');
        }

        $anexoModel = model(ManifestacaoAnexoModel::class);
        $anexo = $anexoModel->find($id);
        if (!$anexo) {
            return $this->response->setStatusCode(404)->setBody('Anexo não encontrado');
        }

        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($anexo['manifestacao_id']);

        if (!$manifestacao || !service('authorization')->podeDownloadAnexo($usuario, $manifestacao)) {
            return $this->response->setStatusCode(403)->setBody('Acesso negado');
        }

        $caminhoCompleto = WRITEPATH . $anexo['caminho_arquivo'];
        if (!is_file($caminhoCompleto)) {
            return $this->response->setStatusCode(404)->setBody('Arquivo não encontrado');
        }

        $mime = $anexo['mime'] ?? 'application/octet-stream';
        $nome = $anexo['nome_original'] ?? 'anexo';

        $this->response->setHeader('Content-Type', $mime);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . str_replace('"', '\\"', $nome) . '"');
        $this->response->setHeader('Cache-Control', 'private, max-age=3600');
        $this->response->setBody(file_get_contents($caminhoCompleto));

        return $this->response;
    }

    /**
     * Download do anexo (força salvamento). Rota: /ouvidoria/anexos/download/{id}
     */
    public function download(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!$usuario) {
            return $this->response->setStatusCode(403)->setBody('Acesso negado');
        }

        $anexoModel = model(ManifestacaoAnexoModel::class);
        $anexo = $anexoModel->find($id);
        if (!$anexo) {
            return $this->response->setStatusCode(404)->setBody('Anexo não encontrado');
        }

        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($anexo['manifestacao_id']);

        if (!$manifestacao || !service('authorization')->podeDownloadAnexo($usuario, $manifestacao)) {
            return $this->response->setStatusCode(403)->setBody('Acesso negado');
        }

        $caminhoCompleto = WRITEPATH . $anexo['caminho_arquivo'];
        if (!is_file($caminhoCompleto)) {
            return $this->response->setStatusCode(404)->setBody('Arquivo não encontrado');
        }

        return $this->response->download($caminhoCompleto, $anexo['nome_original']);
    }
}
