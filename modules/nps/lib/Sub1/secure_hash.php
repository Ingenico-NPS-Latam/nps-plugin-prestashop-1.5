<?php

function add_secure_hash($psp_parameters,$secret_code)
{
    if(!isset($psp_parameters['psp_Transactions']))
    {
        $psp_parameters_orig = $psp_parameters;
        ksort($psp_parameters);
        //echo "<pre>";
        //print_r($psp_parameters);
        //echo implode('',$psp_parameters).$secret_code."<br>";
        //echo md5(implode('',$psp_parameters).$secret_code)."<br>";
        //exit;
        $secure_hash = md5(implode('',$psp_parameters).$secret_code);
        $psp_parameters_orig['psp_SecureHash'] = $secure_hash;
    }
    else
    {
        $psp_parameters_orig = $psp_parameters;
        $aux = $psp_parameters['psp_Transactions'];
        unset($psp_parameters['psp_Transactions']);
        
        ksort($psp_parameters);    
        $secure_hash = md5(implode('',$psp_parameters).$secret_code);
        $psp_parameters_orig['psp_SecureHash'] = $secure_hash;
        
        $psp_parameters_orig['psp_Transactions'] = $aux;
    }
    
    return $psp_parameters_orig;        
}
