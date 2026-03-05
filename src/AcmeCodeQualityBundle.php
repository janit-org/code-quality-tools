<?php

declare(strict_types=1);

namespace Acme\CodeQualityBundle;

use Acme\CodeQualityBundle\DependencyInjection\AcmeCodeQualityExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmeCodeQualityBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new AcmeCodeQualityExtension();
        }

        return $this->extension;
    }
}
