<?php

if (!function_exists('generarSlug')) {
    function generarSlug($texto) {
        $remplazos = [
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'Á'=>'a', 'É'=>'e', 'Í'=>'i', 'Ó'=>'o', 'Ú'=>'u',
            'ñ'=>'n', 'Ñ'=>'n', 'ç'=>'c', 'Ç'=>'c'
        ];
        $texto = strtr($texto, $remplazos);

        $texto = mb_strtolower($texto, 'UTF-8');

        $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);

        $texto = trim($texto, '-');

        $texto = preg_replace('/-+/', '-', $texto);

        return $texto;
    }
}
