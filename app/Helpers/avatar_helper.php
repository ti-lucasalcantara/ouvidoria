<?php

if ( ! function_exists('render_avatar') ){
    function renderAvatar(): string
    {
        $usuario = session('usuario_logado');
        $nomeCompleto = remove_acentos(trim($usuario['nome'] ?? ''));
        
        if (!$nomeCompleto) {
            $iniciais = '?';
        } else {
            $partes = explode(' ', $nomeCompleto);
            $primeira = strtoupper(substr($partes[0], 0, 1));
            $ultima = strtoupper(substr(end($partes), 0, 1));
            $iniciais = $primeira . $ultima;
        }

        return <<<HTML
        <div class="avatar rounded-circle d-flex align-items-center justify-content-center" 
            style="width: 40px; height: 40px; background-color: #e2e6ea; color: #0d6efd; font-weight: bold;">
            {$iniciais}
        </div>
    HTML;
    }
}
