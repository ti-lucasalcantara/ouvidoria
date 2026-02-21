<?php

if ( ! function_exists('getMessageSucess') ){
    function getMessageSucess(  $alert = 'toast', $message = [] ) {
       
        return [
            'show_'.$alert => true,
            'type' => $message['type'] ?? 'success',
            'title' =>  $message['title'] ?? 'Tudo certo!',
            'text' =>  $message['text'] ?? 'Operação realizada com sucesso.',
            'timer' =>  $message['timer'] ?? 5 * 1000,
        ];
    }
}

;

if ( ! function_exists('getMessageFail') ){
    function getMessageFail( $alert = 'toast', $message = [] ) {
        // register log
        return [
            'show_'.$alert => true,
            'type' => $message['type'] ?? 'danger',
            'title' =>  $message['title'] ?? 'Erro Inesperado',
            'text' =>  $message['text'] ?? 'Ocorreu um problema ao processar a sua solicitação.',
            'timer' =>  $message['timer'] ?? 5 * 1000,
        ];
    }
}
