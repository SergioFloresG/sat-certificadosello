<?php
/**
 * Created by PhpStorm.
 * User: Sergio Flores Genis
 * Date: 2017-12-08T09:50
 */

namespace MrGenis\Sat;


class CerKeyTest extends \PHPUnit_Framework_TestCase
{

    public function test01StorageString()
    {
        $cer = $this->path('testing_pm.cer');
        $key = $this->path('testing_pm.key');
        $pas = '12345678a';

        $storage = StorageCerKey::create($cer, $key, $pas);
        $this->assertFileExists($storage->getCerPemFile(), "PEM del archivo CER no existe");
        $this->assertFileExists($storage->getKeyPemFile(), "PEM del archivo KEY no existe");
        $this->assertFileExists($storage->getPfkFile(), "Archivo PFK no existe");
    }

    public function test02SelloCertificado(){
        $xml = $this->path('testing_pm.xml');
        $cer = $this->path('testing_pm.cer');
        $key = $this->path('testing_pm.key');
        $pas = '12345678a';

        $storage = StorageCerKey::create($cer, $key, $pas);

        $cerSello = new CertificadoSello33($storage);
        $xml = $cerSello->injectInformation($xml);

        $this->assertNotEmpty($xml, "No se genero el XML sellado");
    }

    public function path($file = null)
    {
        return realpath(__DIR__) . ($file ? '/' . $file : $file);
    }

}