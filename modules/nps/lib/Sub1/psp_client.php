<?php
/**********************************************************************************
/*            Componente de comunicación con Webserice PSP - NuSoap             *
/*                                                                                *
/* Descripción: Conjunto de funciones para manejo de comunicaciones y mensajeria  *
/*                                                                                *
/* Autor          : Juan Manuel Rebull                                            *
/* Fecha Creación : 12/06/2007                                                    *
/* Observaciones  :                                                               *
/*                                                                                *
/**********************************************************************************/

// 2007/09/10    Juan Manuel   Creacion
// 2007/09/19    Juan Manuel   Se ordeno/modifico librerias e includes
// 2009/12/11    Juan Manuel   Se incorporo manejo de excepciones
// 2011/07/14    Juan Manuel   Se modifico la estructura a modelo de objetos

require_once(__DIR__.'/../NuSoap/nusoap.php');
require_once(__DIR__.'/../NuSoap/nusoap/lib/class.wsdlcache.php');

class PSP_Client {

  protected $debug = false;
  protected $show_request = false;
  protected $show_response = false;

  protected $ws_url = '';
  protected $connect_timeout = 5;
  protected $execute_timeout = 40;

  protected $method = '';
  protected $params = array();

  protected $secret_key = '';
  
  protected $cache_dir = '';
  protected $cache_lifetime = 0;

  public function setDebug($value)
  {
    $this->debug = (boolean) $value;
  }

  public function setPrintRequest($value)
  {
    $this->show_request = (boolean) $value;
  }

  public function setPrintResponse($value)
  {
    $this->show_response = (boolean) $value;
  }

  public function setUrl($value)
  {
    $this->ws_url = $value;
  }

  public function setConnectTimeout($value)
  {
    $this->connect_timeout = $value;
  }

  public function setExecuteTimeout($value)
  {
    $this->execute_timeout = $value;
  }

  public function setMethodName($value)
  {
    $this->method = $value;
  }

  public function setMethodParams($value)
  {
    $this->params = $this->cleanArray($value);
  }
  
  function cleanArray(&$array)
  {
    if(is_array($array))
    {
      foreach($array as $key=>&$arrayElement)
      {
        if(is_array($arrayElement))
        {
          $this->cleanArray($arrayElement);
        }
        else
        {
          if($arrayElement === NULL || trim($arrayElement) === '')
          {
              unset($array[$key]);
          }
        }
      }
        
      return $array;  
    }
    throw new Exception("::cleanArray has recieved non array parameter");
  }    

  public function setSecretKey($value)
  {
    $this->secret_key = $value;
  }

  public function setWsdlCache($cache_dir, $cache_lifetime=0)
  {
    $this->cache_dir = $cache_dir;
    $this->cache_lifetime = $cache_lifetime;
  }

  public function send()
  {
    $this->validMandatoryParams();

    if ($this->cache_dir != ''){
      $wsdl = $this->getWsdlCache();
    }
    else{
      $wsdl = $this->ws_url;
    }

    $client = new nusoap_client($wsdl, true, '', '', '', '', $this->connect_timeout, $this->execute_timeout);
    $client->decode_utf8 = false;
    $client->soap_defencoding = 'UTF-8';

    $err = $client->getError();
    if ($err)
    {
      if ($this->debug)
      {
        echo '<h3>Constructor error</h3><pre>' . $err . '</pre>';
        echo '<h3>Debug</h3><pre>' . htmlspecialchars($client->debug_str,ENT_QUOTES) . '</pre>';
      }

      throw new Exception("Error de conexion: Constructor Error - {$err}", '1000');
    }

    $result = $client->call($this->method, $this->generateParams(), '', '', false, true);

    // Analizo respuesta.
    if ($client->fault)
    {
      if ($this->debug)
      {
        echo '<h3>Fault</h3><pre>'. var_export($result,true) . '</pre>';
        echo '<h3>Debug</h3><pre>' . htmlspecialchars($client->debug_str,ENT_QUOTES) . '</pre>';
      }

      throw new Exception('Error interno en el Webservice: Fault', '1001');
    }
    else
    {
      $err = $client->getError();
      if ($err)
      {
        if ($this->debug)
        {
          echo '<h3>Error</h3><pre>' . $err . '</pre>';
          echo '<h3>Debug</h3><pre>' . htmlspecialchars($client->debug_str,ENT_QUOTES) . '</pre>';
        }

        throw new Exception("Error interno en el Webservice: Error - {$err}", '1002');
      }
    }

    // Debug
    if ($this->show_request)
      echo '<h3>Request</h3><pre>' . htmlspecialchars($client->request,ENT_QUOTES) . '</pre>';

    if ($this->show_response)
      echo '<h3>Response</h3><pre>' . htmlspecialchars($client->response,ENT_QUOTES) . '</pre>';
    
    if ($this->debug)
      echo '<h3>Debug</h3><pre>' . htmlspecialchars($client->debug_str,ENT_QUOTES) . '</pre>';

    return $result;
  }  
  
  protected function getWsdlCache()
  {
    $this->validCacheDir();
    
    $cache = new nusoap_wsdlcache($this->cache_dir, $this->cache_lifetime);
    $wsdl = $cache->get($this->ws_url);
    
    if (is_null($wsdl))
    {
      $wsdl = new wsdl($this->ws_url, '', '', '', '', $this->connect_timeout, $this->execute_timeout);
      $err = $wsdl->getError();
      if ($err)
      {
        if ($this->debug)
        {
          echo '<h3>WSDL Constructor error</h3><pre>' . $err . '</pre>';
          echo '<h3>Debug</h3><pre>' . htmlspecialchars($wsdl->getDebug(), ENT_QUOTES) . '</pre>';
        }
        
        throw new Exception("Error de conexion: Constructor Error - {$err}", '1000');
      }
      $cache->put($wsdl);
    }
    else
    {
      $wsdl->debug_str = '';
      $wsdl->debug('Retrieved from cache');
    }
    
    return $wsdl;
  }

  protected function validCacheDir()
  {
    if (!is_dir($this->cache_dir)){
      if(!mkdir($this->cache_dir, 0777)){
        throw new Exception("Imposible crear el directorio de cache {$this->cache_dir}", '1006');
      }
    }
 
    if (file_put_contents($this->cache_dir.'touch', '0') === false){
      throw new Exception("Imposible escribir en {$this->cache_dir}", '1007');
    }
    else{
      if (is_file($this->cache_dir.'touch'))
        unlink ($this->cache_dir.'touch');
    }
  }
  
  protected function validMandatoryParams()
  {
    if ($this->ws_url == '')
      throw new Exception("Url no definida", '1003');

    if ($this->method == '')
      throw new Exception("MethodName no definido", '1004');

    if (count($this->params) == 0)
      throw new Exception("MethodParams no definido", '1005');
  }

  protected function generateParams()
  {
    if ($this->secret_key != '')
      return array('Requerimiento' => $this->addSecureHash($this->params,$this->secret_key));
    else
      return array('Requerimiento' => $this->params);
  }

  protected function addSecureHash($psp_parameters,$secret_code)
  {
    $psp_parameters_orig = $psp_parameters;
    
    $temp_psp_parameters = array();
    foreach($psp_parameters as $k => $psp_parameter) {
      if(is_array($psp_parameter)) {
        $temp_psp_parameters[$k] = $psp_parameter;
        unset($psp_parameters[$k]);
      }
    }
    
    ksort($psp_parameters);
    $secure_hash = md5(implode('',$psp_parameters).$secret_code);
    $psp_parameters_orig['psp_SecureHash'] = $secure_hash;
    
    foreach($temp_psp_parameters as $k => $temp_psp_parameter) {
      $psp_parameters_orig[$k] = $temp_psp_parameter;
    }
    
    return $psp_parameters_orig;
  }
}
