<?php
namespace Starbug\Queue;

use Psr\Container\ContainerInterface;

class WorkerFactory implements WorkerFactoryInterface {
  /**
   * PSR-11 Container.
   *
   * @var ContainerInterface
   */
  protected $container;
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }
  public function get($className): WorkerInterface {
    return $this->container->get($className);
  }
}
