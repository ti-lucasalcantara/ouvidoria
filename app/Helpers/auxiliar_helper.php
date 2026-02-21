<?php

if ( ! function_exists('remove_acentos') ){
    function remove_acentos($string) {
        return preg_replace(
            array("/[áàâãä]/u", "/[éèêë]/u", "/[íìîï]/u", "/[óòôõö]/u", "/[úùûü]/u", "/[ç]/u"),
            array("a", "e", "i", "o", "u", "c"),
            mb_strtolower($string)
        );
    }
}


if (!function_exists('getNomeSobrenome')) {
    function getNomeSobrenome($string) {
        $partes = explode(' ', trim($string));
        if (count($partes) === 0) {
            return '';
        }
        if (count($partes) === 1) {
            return $partes[0];
        }
        return $partes[0] . ' ' . end($partes);
    }
}

/**
 * Remove/ajusta caracteres inválidos (recursivo).
 * Não muda números/boolean/null. Só mexe em string.
 */
if (!function_exists('sanearUtf8Recursivo')) {
    function sanearUtf8Recursivo($data){
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = sanearUtf8Recursivo($v);
            }
            return $data;
        }

        if (is_object($data)) {
            foreach ($data as $k => $v) {
                $data->$k = sanearUtf8Recursivo($v);
            }
            return $data;
        }

        if (!is_string($data)) {
            return $data;
        }

        // Se já estiver ok em UTF-8, mantém
        if (@preg_match('//u', $data)) {
            return $data;
        }

        // Tenta converter "qualquer coisa" pra UTF-8 ignorando o que for inválido
        // (iconv costuma resolver 99% desses casos)
        $fix = @iconv('UTF-8', 'UTF-8//IGNORE', $data);

        // fallback: se iconv falhar, remove bytes inválidos
        if ($fix === false || $fix === null) {
            $fix = preg_replace('/[^\x00-\x7F\xC2-\xF4][\x80-\xBF]*/', '', $data);
        }

        return $fix ?? '';
    }
}
