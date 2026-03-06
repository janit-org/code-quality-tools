<?php

declare(strict_types=1);

namespace Janit\CodeQualityBundle;

use Janit\CodeQualityBundle\DependencyInjection\JanitCodeQualityExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JanitCodeQualityBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new JanitCodeQualityExtension();
        }

        return $this->extension;
    }
}
