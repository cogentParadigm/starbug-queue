<?php
namespace Starbug\Queue;

interface WorkerFactoryInterface {
  public function get($worker): WorkerInterface;
}
