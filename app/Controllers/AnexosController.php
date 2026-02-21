<?php

namespace App\Controllers;

use App\Models\ManifestacaoModel;
use App\Models\ManifestacaoAnexoModel;

/**
 * Controller de Anexos.
 * Download seguro com verificação de autorização.
 */
class AnexosController extends BaseController
{
    protected $helpers = ['ouvidoria'];

    /**
     * Download de anexo. Rota: /ouvidoria/anexos/download/{id}
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
