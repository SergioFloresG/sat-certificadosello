# mrgenis/sat-certificadosello


[![Latest Version](https://img.shields.io/github/release/SergioFloresG/sat-certificadosello.svg?style=flat)](https://github.com/SergioFloresG/sat-certificadosello/releases)
[![Build Status](https://travis-ci.org/SergioFloresG/sat-certificadosello.svg?branch=master&style=for-the-badge)](https://travis-ci.org/SergioFloresG/sat-certificadosello)

Clases para manejar los archivos CER y KEY del SAT para generar el sello de un CFDI

## Instalar
Puedes instalar este paquete via composer.

```bash
composer require mrgenis/sat-certificadosello
```

## Usar

### Validar CER y KEY
Se generar los archivos PEM para CER y KEY, y el archivo PFX 

```php
/// Dirección del archivo CER
$cer = $this->path('testing_pm.cer');
/// Dirección del archivo KEY
$key = $this->path('testing_pm.key');
/// Contraseña del KEY
/// (solo se require la primera vez para generar los PEM)
$pas = '12345678a';

$storage = StorageCerKey::create($cer, $key, $pas);
/// Se valida que el CER y KEY sean pareja.
$storage->matchCerKey();
```

### Certificar CFDI
Inyectar en un CFDI 3.3 el NoCertificado, Certificado y Sello. Posterior a la primera validación de los archivos 
CER y KEY (existen los archivos PEM).

```php
/// Dirección del archivo CER
$cer = $this->path('testing_pm.cer');
/// Dirección del archivo KEY
$key = $this->path('testing_pm.key');

$storage = StorageCerKey::create($cer, $key);
$cerSello = new CertificadoSello33($storage);
/// Se obtiene la cedena (string) del xml con NoCertificado, Certificado y Sello. 
$xml = $cerSello->injectInformation($xml);
```


# Licencia
MIT License (MIT). Ver [archivo de licencia](https://github.com/SergioFloresG/sat-certificadosello/blob/HEAD/LICENSE) para mas información.