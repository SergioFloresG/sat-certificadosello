<?php
/**
 * Created by PhpStorm.
 * User: Sergio Flores Genis
 * Date: 2017-12-11T16:08
 */

namespace MrGenis\Sat;

class StorageCerKey
{

    /** @var  string direccion del archivo <code>*.cer</code> */
    private $cer_file;
    /** @var  string direccion del archivo <code>*.cer.pem</code> */
    private $cer_pem_file;
    /** @var string direccion del archivo <code>*.key</code> */
    private $key_file;
    /** @var  string direccion del archivo <code>*.key.pem</code> */
    private $key_pem_file;
    /** @var string direccion del archivo <code>*.pfx</code> */
    private $pfk_file;
    /** @var  string constraseña de archivo <code>*.key</code> */
    private $key_password;


    /**
     * GeneratePem constructor.
     *
     * @param string $cer_file direccion al archivo CER
     * @param string $key_file direccion al archivo KEY
     * @param string $pass     clave para archivo KEY
     *
     * @throws \RuntimeException
     */
    protected function __construct($cer_file, $key_file, $pass = null)
    {
        if (!file_exists($cer_file)) throw new \RuntimeException("El archivo de certificado no existe");
        $this->cer_file = $cer_file;
        $this->cer_pem_file = $cer_file . '.pem';

        if (!file_exists($key_file)) throw new \RuntimeException("El archivo de llave no existe");
        $this->key_file = $key_file;
        $this->key_pem_file = $key_file . '.pem';
        $this->pfk_file = $key_file . '.pfx';
        $this->key_password = $pass;


        $this->__cerpem_exist();
        $this->__keypem_exist();
        $this->__pfk_exist();
    }

    /**
     * @param string $cer  archivo CER
     * @param string $key  archivo KEY
     * @param string $pass clave para archivo KEY
     *
     * @return static
     */
    public static function create($cer, $key, $pass = null)
    {
        return new static($cer, $key, $pass);
    }


    /**
     * Valida que el archivo <i>CER</i> y <i>KEY</i> sean pareja.
     *
     * @return bool <b>TRUE</b>: cuando el cer y el key son pareja, en caso contrario false
     */
    public function matchCerKey()
    {
        static $command_cer = "openssl x509 -noout -modulus -in %s 2>&1";
        static $command_key = "openssl rsa -noout -modulus -in %s 2>&1";

        exec(sprintf($command_cer, $this->getCerPemFile()), $outcer, $intrcer);
        exec(sprintf($command_key, $this->getKeyPemFile()), $outkey, $intrkey);

        return (($intrcer === 0) && ($intrkey === 0) && ($outcer[0] === $outkey[0]));
    }

    /**
     * Obtiene el numero del certificado
     *
     * @return string numero del certificado
     */
    public function noCertificado()
    {
        static $command_cer = "openssl x509 -in %s -noout -serial 2>&1";
        exec(sprintf($command_cer, $this->getCerPemFile()), $outcer, $intrcer);
        $serial = '';
        if (strpos($outcer[0], 'serial=') !== false) {
            $outcer = str_replace('serial=', '', $outcer[0]);
            for ($i = 0; $i < strlen($outcer); $i++) if ($i % 2 != 0) {
                $serial .= $outcer[ $i ];
            }
        }
        unset($outcer, $intrcer);
        return $serial;
    }



    /// region GETTERS
    /// @format:off

    /**
     * @return string
     */
    public function getCerFile(): string
    {
        return $this->cer_file;
    }

    /**
     * @return string
     */
    public function getCerPemFile(): string
    {
        return $this->cer_pem_file;
    }

    /**
     * @return string
     */
    public function getKeyFile(): string
    {
        return $this->key_file;
    }

    /**
     * @return string
     */
    public function getKeyPemFile(): string
    {
        return $this->key_pem_file;
    }

    /**
     * @return string
     */
    public function getPfkFile(): string{
        return $this->pfk_file;
    }

    /// @format:on
    /// endregion

    /// region Validacion de existencia de los archivos originales y su generacion de archivos PEM
    /// format:off

    /**
     * Genera el archivo PEM de la llave
     *
     * @return bool
     */
    protected function makeKEY2PEM()
    {
        static $command = "openssl pkcs8 -inform DER -in %s -out %s -passin pass:%s 2>&1";
        $key = $this->getKeyFile();
        $pem = $this->getKeyPemFile();

        if (!$this->key_password) return false;

        try {


            exec(sprintf($command,
                escapeshellarg($key),
                escapeshellarg($pem),
                escapeshellarg($this->key_password)),
                $output, $intr);


            return ($intr === 0);
        } catch (\Exception $e) {
            // Nada
        }
        return false;
    }

    /**
     * Genera el archivo PEM del certificado.
     *
     * @return bool
     */
    protected function makeCER2PEM()
    {
        static $command = "openssl x509 -inform DER -outform PEM -in %s -pubkey -out %s 2>&1";
        $cer = $this->getCerFile();
        $pem = $this->getCerPemFile();
        if (!file_exists($cer)) return false;
        try {


            exec(sprintf($command,
                escapeshellarg($cer),
                escapeshellarg($pem)),
                $output, $intr);


            return ($intr === 0);
        } catch (\Exception $e) {
            // Nada
        }
        return false;
    }

    /**
     * Genera el archivo PFX del KEY
     *
     * @return bool
     */
    protected function makePFX()
    {

        static $command = "echo 4xBbCfSj | openssl pkcs12 -export -inkey %s -in %s -out %s -passout pass:%s 2>&1";

        try {


            exec(sprintf($command,
                escapeshellarg($this->getKeyPemFile()),
                escapeshellarg($this->getCerPemFile()),
                escapeshellarg($this->getPfkFile()),
                escapeshellarg($this->key_password)),
                $out, $intr);


            return ($intr === 0);
        } catch (\Exception $e) {
            // Nada
        }
        return false;

    }

    private function __cerpem_exist()
    {
        if (!file_exists($this->getCerPemFile()) && !$this->makeCER2PEM()) {
            throw new \RuntimeException("No se logro generar el archivo PEM del certificado");
        }
    }

    private function __keypem_exist()
    {
        if (!file_exists($this->getKeyPemFile()) && !$this->makeKEY2PEM()) {
            throw new \RuntimeException("No se logo generar el archivo PEM de la llave");
        }
    }

    private function __pfk_exist()
    {
        if (!file_exists($this->getPfkFile()) && !$this->makePFX()) {
            throw new \RuntimeException('No se logro generar el archivo PFX');
        }
    }

    /// format:on
    /// endregion
}