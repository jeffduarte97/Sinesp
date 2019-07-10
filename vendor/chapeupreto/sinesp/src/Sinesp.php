<?php

namespace Sinesp;

class Sinesp
{
    private $secret = '#8.1.0#0KnlVSWHxOih3zKXBWlo';
    private $url = 'https://cidadao.sinesp.gov.br/sinesp-cidadao/mobile/consultar-placa/v5';
    private $proxy = '';
    private $placa = '';
    private $response = '';
    private $chave = '7EG2J3EwyvX:APA91bEkhRGzt6DQVBIsQqottKLhxgvd4_4SSCqVrytApUKIpTLl4gjDkjD4HapUwTWxRVyzZ6lVMwaiOj1giREfHcHigLHbD0HpVeevY_pGFQ9ZrEQM-8fdTszdrCvbUjZ9SNhWq34j';
    private $id = '7EG2J3EwyvX';
    private $dados = [];

    /**
     * Time (in seconds) to wait for a response
     * @var int
     */
    private $timeout = 0;

    public function buscar($placa, array $proxy = [])
    {
        if ($proxy) {
            $this->proxy($proxy['ip'], $proxy['porta']);
        }

        $this->setUp($placa);
        $this->exec();

        return $this;
    }

    public function dados()
    {
        return $this->dados;
    }

    public function proxy($ip, $porta)
    {
        $this->proxy = $ip . ':' . $porta;
    }

    /**
     * Set a timeout for request(s) that will be made
     * @param  int  $seconds How much seconds to wait
     * @return self
     */
    public function timeout($seconds)
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function __get($name)
    {
        return array_key_exists($name, $this->dados) ? $this->dados[$name] : '';
    }

    public function existe()
    {
        return array_key_exists('codigoRetorno', $this->dados) && $this->dados['codigoRetorno'] != '3';
    }

    private function exec()
    {
        $this->verificarRequisitos();
        $this->obterResposta();
        $this->tratarResposta();
    }

    public function str($size){
        $basic = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $return= "";
        for($count= 0; $size > $count; $count++){
            
            $return.= $basic[rand(0, strlen($basic) - 1)];
        }
        return $return;
    }

    public function emp()
    {
        $sr = 'X-subtype=905942954488&sender=905942954488&X-app_ver=49&X-osv=23&X-cliv=fiid-12451000&X-gmsv=17785018&X-appid='.$this->str(11).'&X-scope=*&X-gmp_app_id=1%253A905942954488%253Aandroid%253Ad9d949bd7721de40&X-app_ver_name=4.7.4&app=br.gov.sinesp.cidadao.android&device=3580873862227064803&app_ver=49&info=szkyZ1yvKxIbENW7sZq6nvlyrqNTeRY&gcm_ver=17785018&plat=0&cert=daf1d792d60867c52e39c238d9f178c42f35dd98&target_ver=26';
        $inf = <<<EOX
        %s
        EOX;
        return sprintf($inf, $sr);
    }

    public function a4lJIhgYU54()
    {
        $ch = curl_init('https://android.clients.google.com/c2dm/register3');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->emp());                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: AidLogin 3580873862227064803:6185646517745801705'));                         
        $result = curl_exec($ch);
        curl_close($ch);
        $this->chave = str_replace('token=', '', $result);
        $this->id = explode(':', $this->chave)[0];
    }

    public function obterResposta()
    {
        $xml = $this->xml();
        $headers = [
            'User-Agent: ksoap2-android/2.6.0+',
            'SOAPAction: ',
            'Content-Type: text/xml;charset=utf-8',
            'Accept-Encoding: gzip',
            'Authorization: Token '.$this->chave,
            'Content-Length: 606',
            'Host: cidadao.sinesp.gov.br',
            'Connection: Keep-Alive'. strlen($xml),
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $this->url);

        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $this->response = curl_exec($ch);
        curl_close($ch);
    }

    private function tratarResposta()
    {
        if (!$this->response) {
            throw new \Exception('O servidor retornou nenhuma resposta!');
        }

        $response = str_ireplace(['soap:', 'ns2:'], '', $this->response);

        $this->dados = (array) simplexml_load_string($response)->Body->getStatusResponse->return;

        while (true) {
            if ((intval($this->dados["codigoRetorno"])) == 8){
                $this->a4lJIhgYU54();
                $this->obterResposta();
            }
            elseif ((intval($this->dados["codigoRetorno"])) == 0){
                break;
            }
        }
    }

    private function verificarRequisitos()
    {
        if (!function_exists('curl_init')) {
            throw new \Exception('Incapaz de processar. PHP requer biblioteca cURL');
        }

        if (!function_exists('simplexml_load_string')) {
            throw new \Exception('Incapaz de processar. PHP requer biblioteca libxml');
        }

        return;
    }

    private function setUp($placa)
    {
        if (!$this->validar($placa)) {
            throw new \Exception('Placa do veiculo nao especificada ou em formato invalido!');
        }

        $this->placa = $this->ajustar($placa);
    }

    private function token()
    {
        return hash_hmac('sha1', $this->placa, $this->placa . $this->secret);
    }

    private function xml()
    {
        $xml = <<<EOX
<v:Envelope xmlns:i="http://www.w3.org/2001/XMLSchema-instance" xmlns:d="http://www.w3.org/2001/XMLSchema" xmlns:c="http://schemas.xmlsoap.org/soap/encoding/" xmlns:v="http://schemas.xmlsoap.org/soap/envelope/"><v:Header><b>samsung GT-I9192</b><c>ANDROID</c><d>8.1.0</d><e>4.7.4</e><f>172.17.99.15</f><g>%s</g><h>%s</h><i>%s</i><k></k><l>%s</l><m>8797e74f0d6eb7b1ff3dc114d4aa12d3</m><n>%s</n></v:Header><v:Body><n0:getStatus xmlns:n0="http://soap.ws.placa.service.sinesp.serpro.gov.br/"><a>%s</a></n0:getStatus></v:Body></v:Envelope>
EOX;
        return sprintf($xml, $this->token(), $this->latitude(), $this->longitude(), strftime('%Y-%m-%d %H:%M:%S'), $this->id, $this->placa);
    }

    private function validar($placa)
    {
        return preg_match('/^[a-z]{3}-?\d[a-z0-9]{2}\d$/i', trim($placa));
    }

    private function ajustar($placa)
    {
        return str_replace('-', '', trim($placa));
    }

    private function latitude()
    {
        return '-38.5' . rand(100000, 999999);
    }

    private function longitude()
    {
        return '-3.7' . rand(100000, 999999);
    }
}
?>