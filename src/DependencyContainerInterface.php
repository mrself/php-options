<?php declare(strict_types=1);

namespace Mrself\Options;

interface DependencyContainerInterface
{
    public function get(string $name);

    public function has(string $name);

    public function getParameter(string $name);
}