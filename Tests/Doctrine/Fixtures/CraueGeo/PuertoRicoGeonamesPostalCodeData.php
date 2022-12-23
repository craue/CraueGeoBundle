<?php

namespace Craue\GeoBundle\Tests\Doctrine\Fixtures\CraueGeo;

use Craue\GeoBundle\Doctrine\Fixtures\GeonamesPostalCodeData;
use Doctrine\Persistence\ObjectManager;

class PuertoRicoGeonamesPostalCodeData extends GeonamesPostalCodeData {

    /**
     * {@inheritDoc}
     */
    final public function load(ObjectManager $manager): void
    {
        $this->clearPostalCodesTable($manager);

        $zip = new \ZipArchive();
        $res = $zip->open(__DIR__.'/PR.zip');
        if ($res === true) {
            $zip->extractTo(__DIR__);
            $zip->close();
            $this->addEntries($manager, __DIR__.'/PR.txt');
            unlink(__DIR__.'/PR.txt');
            unlink(__DIR__.'/readme.txt');
        }
    }
}
