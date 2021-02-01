<?php
namespace Starbug\Queue;

interface QueueFactoryInterface {
  /**
   * Get a specific queue by name.
   *
   * @param string $name The name of the queue.
   *
   * @return QueueInterface
   */
  public function get($name = "default"): QueueInterface;
  /**
   * Get the queue for a specific worker.
   *
   * @param string $worker The class name of the worker.
   *
   * @return QueueInterface
   */
  public function getQueueForWorker($worker): QueueInterface;
}
