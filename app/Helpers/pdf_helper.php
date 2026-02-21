<?php

use Spipu\Html2Pdf\Html2Pdf;

if (!function_exists('gerarPDF')) {
    function gerarPDF(
        $html = '',
        $fileName = 'relatorio.pdf',
        $output_mode = 'F',
        $fullPath = '',
        $header_pdf = [
            'orientation' => 'P',
            'format' => 'A4',
            'lang' => 'pt',
            'unicode' => true,
            'encoding' => 'UTF-8',
            'margins' => [5, 5, 5, 8],
            'pdfa' => false
        ]
    ) {
        try {
            // Para output direto no navegador, não precisamos criar diretório
            if ($output_mode === 'F') {
                if (!is_dir($fullPath)) {
                    if (!mkdir($fullPath, 0775, true)) {
                        return json_encode(getMessageFail('toast', ['text' => 'Falha ao criar pasta: ' . $fullPath]));
                    }
                }
            }

            $html = str_replace("./assets/", FCPATH . "assets/", $html);

            $pdf = new Html2Pdf(
                $header_pdf['orientation'],
                $header_pdf['format'],
                $header_pdf['lang'],
                $header_pdf['unicode'],
                $header_pdf['encoding'],
                $header_pdf['margins'],
                $header_pdf['pdfa']
            );

            // Se usar fontes personalizadas
            if (file_exists(FCPATH . 'assets/fonts/LibreBarcode39Text-Regular.php')) {
                $pdf->addFont('LibreBarcode39', '', 'assets/fonts/LibreBarcode39Text-Regular.php');
            }

            $pdf->writeHTML($html);

            // Modo "I" (inline) ou "D" (download) não precisam de caminho completo
            if ($output_mode === 'I' || $output_mode === 'D') {
                $pdf->output($fileName, $output_mode); // 'I' = inline, 'D' = download
                exit; // Finaliza para não retornar HTML depois
            }

            // Modo salvar em arquivo (F)
            $output_file = $fullPath . $fileName;
            $pdf->output($output_file, 'F');
            return $output_file;

        } catch (\Throwable $th) {
            throw $th;
        }
    }
}



if ( ! function_exists('mergePDF') ){
    function mergePDF( $input_files=array(), $output_file=''  ) {
       
        try {
            if ( PHP_OS == 'WINNT' ) {
                $command = 'gswin64c';
            }else{
                $command = 'gs';
            }
    
            $gsCommand = $command.' -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile=' . escapeshellarg($output_file);
    
            // Adiciona cada arquivo PDF ao comando
            foreach ($input_files as $file) {
                $gsCommand .= ' ' . escapeshellarg($file);
            }
    
            exec($gsCommand, $output, $return_var);
    
            // 0 - sem erros
            // 1 - com erros
            return !$return_var;

        } catch (\Throwable $th) {
           throw $th;
        }
    }
}
