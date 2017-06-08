<?php

namespace Ocrend\Kernel\Router;

interface RouterInterface {
    public function setRoute(string $index, string $rule);
    public function getRoute(string $index);
    public function getController();
    public function getMethod();
    public function getId(bool $with_rules);
}