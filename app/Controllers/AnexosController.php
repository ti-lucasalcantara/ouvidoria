<?php

namespace App\Controllers;

use App\Models\ManifestacaoModel;
use App\Models\ManifestacaoAnexoModel;
use App\Models\RespostaOuvidorModel;
use App\Models\RespostaOuvidorAnexoModel;

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

    /**
     * Abre anexo de resposta ao ouvidor no navegador.
     * Rota: /ouvidoria/anexos/resposta/abrir/{id}
     */
    public function respostaAbrir(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!$usuario) {
            return $this->response->setStatusCode(403)->setBody('Acesso negado');
        }

        $anexoModel = model(RespostaOuvidorAnexoModel::class);
        $anexo = $anexoModel->find($id);
        if (!$anexo) {
            return $this->response->setStatusCode(404)->setBody('Anexo não encontrado');
        }

        $respostaModel = model(RespostaOuvidorModel::class);
        $resposta = $respostaModel->find($anexo['resposta_ouvidor_id']);
        if (!$resposta) {
            return $this->response->setStatusCode(404)->setBody('Resposta não encontrada');
        }

        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($resposta['manifestacao_id']);
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
     * Download de anexo de resposta ao ouvidor.
     * Rota: /ouvidoria/anexos/resposta/download/{id}
     */
    public function respostaDownload(int $id)
    {
        $usuario = obterUsuarioLogado();
        if (!$usuario) {
            return $this->response->setStatusCode(403)->setBody('Acesso negado');
        }

        $anexoModel = model(RespostaOuvidorAnexoModel::class);
        $anexo = $anexoModel->find($id);
        if (!$anexo) {
            return $this->response->setStatusCode(404)->setBody('Anexo não encontrado');
        }

        $respostaModel = model(RespostaOuvidorModel::class);
        $resposta = $respostaModel->find($anexo['resposta_ouvidor_id']);
        if (!$resposta) {
            return $this->response->setStatusCode(404)->setBody('Resposta não encontrada');
        }

        $manifestacaoModel = model(ManifestacaoModel::class);
        $manifestacao = $manifestacaoModel->find($resposta['manifestacao_id']);
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
