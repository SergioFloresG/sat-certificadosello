<?php
/**
 * Created by PhpStorm.
 * User: Sergio Flores Genis
 * Date: 2017-12-08T09:50
 */

namespace MrGenis\Sat;


class CerKeyTest extends \PHPUnit_Framework_TestCase
{

    /** @var StorageCerKey */
    private $storage;

    protected function setUp()
    {
        $cer = $this->path('testing_pm.cer');
        $key = $this->path('testing_pm.key');
        $pas = '12345678a';

        $this->storage = StorageCerKey::create($cer, $key, $pas);
    }


    public function test_00_march_cer_key()
    {
        $this->assertTrue($this->storage->matchCerKey(), 'Cer y Key no son compatibles');
        unlink($this->storage->getCerPemFile());
    }

    public function test_01_make_cerpem()
    {
        $this->assertTrue($this->storage->make_cerpem(), 'No se logro crear el archivo PEM del certificado');
    }

    public function test_02_make_keypem() {
        $this->assertTrue($this->storage->make_keypem(), 'No se logro crear el archivo PEM de la llave');
    }

    public function test01StorageString()
    {

        $this->assertFileExists($this->storage->getCerPemFile(), "PEM del archivo CER no existe");
        $this->assertFileExists($this->storage->getKeyPemFile(), "PEM del archivo KEY no existe");
        $this->assertFileExists($this->storage->getPfkFile(), "Archivo PFK no existe");
    }

    public function test02SelloCertificado()
    {
        $xml = $this->path('testing_pm.xml');

        $cerSello = new CertificadoSello33($this->storage);
        $xml = $cerSello->injectInformation($xml);

        $this->assertNotEmpty($xml, "No se genero el XML sellado");
    }

    public function path($file = null)
    {
        return realpath(__DIR__) . ($file ? '/' . $file : $file);
    }

}