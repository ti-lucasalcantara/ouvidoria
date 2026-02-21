<?php
namespace App\Libraries;
class C_string {
    private $vetorAcentos = array("´", "`", "~", "^", "¨", '\'', "\"", "\\", "&", "º", "ª", "°");
    private $vetorLetrasAcentosMinusculas = array("á", "à", "ã", "â", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "õ", "ô", "ö", "ú", "ù", "û", "ü", "ç");
    private $vetorLetrasAcentosMaiusculas = array("Á", "À", "Ã", "Â", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Õ", "Ô", "Ö", "Ú", "Ù", "Û", "Ü", "Ç");
    private $vetorLetrasMinusculas = array("a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i", "o", "o", "o", "o", "o", "u", "u", "u", "u", "c");
    public function acentos($texto) {
        return str_replace($this -> vetorAcentos, "", $texto);
    }
    public function acentuacao($texto) {
        $texto = str_replace($this -> vetorLetrasAcentosMinusculas, $this -> vetorLetrasMinusculas, $texto);
        return str_replace($this -> vetorLetrasAcentosMaiusculas, $this -> vetorLetrasMinusculas, $texto);
    }
    public function geral($string) {
        if (mb_detect_encoding($string, 'auto', true) == 'UTF-8') {
            return strtr($string, get_html_translation_table(HTML_ENTITIES, ENT_QUOTES, 'UTF-8'));
        }elseif (mb_detect_encoding($string, 'auto', true) == 'ASCII') {
            return strtr(html_entity_decode($string), get_html_translation_table(HTML_ENTITIES, ENT_QUOTES, 'ISO-8859-1'));
        }else{
            return mb_detect_encoding($string, 'auto', true);
        }
    }
    public function iso($string) {
        return html_entity_decode(htmlentities($this -> geral($string), ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_HTML401 | ENT_XML1 | ENT_XHTML | ENT_HTML5, 'ISO-8859-1', false), ENT_QUOTES | ENT_HTML401 | ENT_XML1 | ENT_XHTML | ENT_HTML5, 'ISO-8859-1');
    }
    public function iso_utf($string) {
        return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
    }
    public function utf($string) {
        return html_entity_decode(htmlentities($this -> geral($string), ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_HTML401 | ENT_XML1 | ENT_XHTML | ENT_HTML5, 'UTF-8', false), ENT_QUOTES | ENT_HTML401 | ENT_XML1 | ENT_XHTML | ENT_HTML5, 'UTF-8');
    }
    public function utf_iso($string) {
        return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
    }
    public function javascript($string) {
        return json_encode($string, JSON_FORCE_OBJECT);
    }
    public function puro($string) {
        $string = trim(str_replace('\r\n', '', $string));
        $string = trim(str_replace('\r', '', $string));
        $string = trim(str_replace('\n', '', $string));
        $string = trim($this -> acentuacao($string));
        $string = trim($this -> acentos($string));
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        return preg_replace(array('/[^A-Za-z0-9 \-]/'), array('', ''), $string);
    }
    public function __destruct() {}
}
?>