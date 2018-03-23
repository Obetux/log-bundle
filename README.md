<p align="center">
    <a href="https://symfony.com" target="_blank">
        <img width=500 height=200 src="https://stcloudfront.qubit.tv/assets/public/qubit/qubit-ar/prod/images/logo-qubit-azul.svg">
    </a>
</p>

[Qubit\LogBundle][3] es un bundle que se integra a [Monolog][1] y genera servicios publicos para agregar funcionalidad a los logs.

Requerimientos
--------------
**No hace falta instalarlos porque ya lo requiere a traves de composer.json**

* [Monolog][1]
* [Qubit\UtilsBundle][2]

Instalación
-----------

* Editar composer para agregar el servidor SATIS de Qubit:

```json
...
"repositories": [
    {
        "type": "composer",
        "url": "https://repo-manager.qubit.tv/"
    },
],
...
```

* Requerimos el [Qubit\LogBundle][3] dejando que composer elija la versión

```bash
$ composer require qubit/log-bundle
```

* Agregar los bundles al AppKernel

```php
// app/AppKernel.php

use Symfony\Component\HttpKernel\Kernel;

...
class AppKernel extends Kernel
{
    ...

    public function registerBundles()
    {
        ...

        $bundles = [
            ...
            new Qubit\Bundle\UtilsBundle\UtilsBundle(),
            new Qubit\Bundle\LogBundle\LogBundle(),
            ...
        ];

        ...
    }

    ...
}
```


Funcionalidades
---------------

* Un formatter especifico de Qubit.
* TrackingCodeProcessor a todos los logs manejados por monolog.
* IntrospectionProcessor (provisto por [Monolog][1] - hecho servicio por [Qubit\UtilsBundle][2]) a todos los logs manejados por mnolog. Agregandol línea, archivo, clase y función desde donde fue llamado el log.

Configuración
-------------

Para utilizar el formatter provisto, debemos editar la configuración de Monolog, especificamente al handler que queramos aplicarle el formatter.

Ejemplo para la aplicación standar de Symfony 2.x|3.x

```yaml
# app/config/config_dev.yml|config_prod.yml
monolog:
    handlers:
        main:
            ...
            formatter: qubit.line.formatter
        ...
```

Uso
---
Utilizando monolog de la manera normal:

```php
// src/FooBundle/Controller/FooController.php

$context = array();
$this->get('logger')->info('Probando logs', $context);
```

Generara la siguiente entrada de log (utilizando el formatter):
```
[2014-08-13 22:30:09] Duration: [ 9 ] - app.INFO - 26aa0a23 : Request recibida [ [] ] [ {"duration_time":9,"tracking_code":"26aa0a23","file":"/src/FooBundle/Listener/LogRequestListener.php","line":32,"class":"FooBundle\\Listener\\LogRequestListener","function":"onKernelRequest"} ]
[2014-08-13 22:30:12] Duration: [ 12 ] - app.INFO - 26aa0a23 : Probando logs [] [ {"duration_time":12,"tracking_code":"26aa0a23","file":"/src/FooBundle/Controller/FooController.php","line":29,"class":"FooBundle\\Controller\\FooController","function":"functionalLoggerAction"} ]
[2014-08-13 22:30:18] Duration: [ 18 ] - app.INFO - 26aa0a23 : Request terminada [ [] ] [ {"duration_time":18,"tracking_code":"26aa0a23","file":"/FooBundle/Listener/LogRequestListener.php","line":44,"class":"FooBundle\\Listener\\LogRequestListener","function":"onKernelResponse"} ]
```

[1]: https://github.com/Seldaek/monolog
[2]: http://git.qubit.tv:8888/Qubit/UtilsBundle
[3]: http://git.qubit.tv:8888/Qubit/LogBundle
