<?php

namespace MrGenis\Sat;

/**
 * Created by PhpStorm.
 * User: Sergio Flores Genis
 * Date: 2017-12-11T16:11
 */

class CertificadoSello33
{

    /** @var StorageCerKey manejador de certificados */
    private $certificados;

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * CertificadoSello33 constructor.
     *
     * @param StorageCerKey $certificados
     *
     * @throws \Exception los archivos cer y key no son pareja. {@see StorageCerKey::matchCerKey}
     */
    public function __construct(StorageCerKey $certificados)
    {
        $certificados->matchCerKey();
        $this->certificados = $certificados;
    }

    public function getNoCertificado()
    {
        return $this->certificados->noCertificado();
    }

    public function getSello($cadena_original)
    {
        $pkkey = openssl_get_privatekey(file_get_contents($this->certificados->getKeyPemFile()));
        openssl_sign($cadena_original, $crypttext, $pkkey, OPENSSL_ALGO_SHA256);
        openssl_free_key($pkkey);
        return base64_encode($crypttext);
    }

    public function getCertificado()
    {
        $lines = preg_grep('(BEGIN|END)',
            explode("\n", file_get_contents($this->certificados->getCerPemFile())),
            PREG_GREP_INVERT);
        return implode('', $lines);
    }

    /**
     * @param string|\SimpleXMLElement|\DOMDocument $xml (string: ruta del archivo o xml)
     *
     * @return string el xml en texto
     */
    public function injectInformation($xml)
    {
        $dom_xml = new \DOMDocument();
        if ($xml instanceof \DOMDocument) {
            $dom_xml = $xml;
        }
        else if ($xml instanceof \SimpleXMLElement) {
            $dom_xml->loadXML($xml->asXML());
        }
        else if (file_exists($xml)) {
            $dom_xml->load($xml);
        }
        else {
            $dom_xml->loadXML($xml);
        }

        $certificadono = $this->getNoCertificado();
        $dom_xml->documentElement->setAttribute('NoCertificado', $certificadono);

        $cadenaoriginal = CadenaOriginal33::cadenaOriginal($dom_xml);
        $sello = $this->getSello($cadenaoriginal);
        $certificado = $this->getCertificado();

        $dom_xml->documentElement->setAttribute('Sello', $sello);
        $dom_xml->documentElement->setAttribute('Certificado', $certificado);

        $dom_xml->preserveWhiteSpace = false;
        $dom_xml->formatOutput = true;
        $result = $dom_xml->saveXML();
        unset($dom_xml);
        return $result;
    }


}