<?php
// RCCService SOAP client (ported from zyphie main/classes.php, gragrastudios\zypher\RCCServiceSoap08)
// Talks to RCCService.exe listening on RCC_IP:RCC_PORT.

class RCCServiceSoap08 {
    public $ip;
    public $port;
    public $url;
    public $renderFix;

    function __construct($ip = "127.0.0.1", $port = 65435, $url = "roblox.com", $renderFix = true) {
        $this->ip = $ip;
        $this->port = $port;
        $this->url = $url;
        $this->renderFix = $renderFix;
    }

    function requestUrl($url, $xml) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [ "Content-Type: text/xml" ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!is_string($response)) {
            return false;
        }

        $result = str_replace(
            [ "<ns1:value>", "</ns1:value>", "</ns1:OpenJobResult>", "<ns1:OpenJobResult>", "<ns1:type>", "</ns1:type>", "<ns1:table>", "</ns1:table>", "</ns1:OpenJobResult>", "</ns1:OpenJobResponse>", "</SOAP-ENV:Body>", "</SOAP-ENV:Envelope>" ],
            "",
            strstr(
                str_replace(
                    [ "LUA_TSTRING", "LUA_TNUMBER", "LUA_TBOOLEAN", "LUA_TTABLE" ],
                    "",
                    $response
                ),
                "<ns1:value>"
            )
        );

        // FIX FOR SOME RENDERS!
        if($this->renderFix) {
            $position = strpos($result, "<ns1:LuaValue>");
            if($position !== false)
                $result = substr($result, 0, $position);
        }

        return $result;
    }

    function execScript($script = 'print("Hello World!")', $jobId = "helloworld", $jobExpiration = 0.1) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:ns2="http://'.$this->url.'/RCCServiceSoap" xmlns:ns1="http://'.$this->url.'/" xmlns:ns3="http://'.$this->url.'/RCCServiceSoap12">
            <SOAP-ENV:Body>
                <ns1:OpenJob>
                    <ns1:job>
                        <ns1:id>'.$jobId.'</ns1:id>
                        <ns1:expirationInSeconds>'.$jobExpiration.'</ns1:expirationInSeconds>
                        <ns1:category>1</ns1:category>
                        <ns1:cores>321</ns1:cores>
                    </ns1:job>
                    <ns1:script>
                        <ns1:name>Script</ns1:name>
                        <ns1:script>
                            '.htmlspecialchars($script, ENT_XML1 | ENT_COMPAT, 'UTF-8').'
                        </ns1:script>
                    </ns1:script>
                </ns1:OpenJob>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        return $this->requestUrl("http://".$this->ip.":".$this->port, $xml);
    }

    function closeJob($jobId = "helloworld") {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:ns2="http://'.$this->url.'/RCCServiceSoap" xmlns:ns1="http://'.$this->url.'/" xmlns:ns3="http://'.$this->url.'/RCCServiceSoap12">
            <SOAP-ENV:Body>
                <ns1:CloseJob>
                    <ns1:job>
                        <ns1:id>'.$jobId.'</ns1:id>
                        <ns1:expirationInSeconds>0</ns1:expirationInSeconds>
                    </ns1:job>
                </ns1:CloseJob>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        return $this->requestUrl("http://".$this->ip.":".$this->port, $xml);
    }

    function getAllJobs() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:ns2="http://'.$this->url.'/RCCServiceSoap" xmlns:ns1="http://'.$this->url.'/" xmlns:ns3="http://'.$this->url.'/RCCServiceSoap12">
            <SOAP-ENV:Body>
                <ns1:GetAllJobs/>
            </SOAP-ENV:Body>
        </SOAP-ENV:Envelope>';

        $ch = curl_init("http://".$this->ip.":".$this->port);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [ "Content-Type: text/xml" ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        curl_close($ch);

        if (!is_string($result)) {
            return '';
        }

        $clean = str_replace([ "LUA_TSTRING", "LUA_TNUMBER", "LUA_TBOOLEAN", "LUA_TTABLE" ], "", $result);

        if (preg_match('/<ns1:GetAllJobsResult>(.*?)<\/ns1:GetAllJobsResult>/s', $clean, $m)) {
            $tags = preg_replace('/<[^>]+>/', ' ', $m[1]);
            return preg_replace('/\s+/', ' ', trim($tags));
        }

        return $clean;
    }

    function helloWorld() {
        return $this->execScript('print("Hello World!")', "helloworld", 0.1);
    }
}