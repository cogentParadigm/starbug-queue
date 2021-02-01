<?php
namespace Starbug\Queue;

/**
 * An implementation of QueueFactoryInterface.
 */
class QueueFactory implements QueueFactoryInterface {
  protected $queues = [];
  protected $factories = [];
  protected $workers = [];
  /**
   * Add a queue to the factory.
   *
   * @param string $name The name of the queue.
   * @param callable $factory callable which should return QueueInterface.
   *
   * @return void
   */
  public function addQueue(string $name, callable $factory) {
    $this->factories[$name] = $factory;
  }
  /**
   * Sets a specific queue for a given worker.
   *
   * @param string $worker The class name of the worker.
   * @param string $queueName The name of the queue.
   *
   * @return void
   */
  public function setWorkerQueue(string $worker, string $queueName) {
    $this->workers[$worker] = $queueName;
  }
  /**
   * {@inheritdoc}
   */
  public function get($name = "default"): QueueInterface {
    if (!isset($this->queues[$name])) {
      $this->queues[$name] = $this->factories[$name]($name);
    }
    return $this->queues[$name];
  }
  /**
   * {@inheritdoc}
   */
  public function getQueueForWorker($worker): QueueInterface {
    return $this->get($this->workers[$worker] ?? "default");
  }
}
