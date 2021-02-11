<?php
namespace Starbug\Queue;

use Exception;

/**
 * A simple interface for a queue.
 */
class QueueManager implements QueueManagerInterface {
  protected $queues;
  protected $workers;
  public function __construct(QueueFactoryInterface $queues, WorkerFactoryInterface $workers) {
    $this->queues = $queues;
    $this->workers = $workers;
  }
  /**
   * @see WorkerFactoryInterface::get
   */
  public function getWorker($name): WorkerInterface {
    return $this->workers->get($name);
  }
  /**
   * @see QueueFactoryInterface::get
   */
  public function getQueue($name = "default"): QueueInterface {
    return $this->queues->get($name);
  }
  /**
   * @see QueueFactoryInterface::get
   */
  public function getQueueForWorker($worker): QueueInterface {
    return $this->queues->getQueueForWorker($worker);
  }

  /**
   * {@inheritdoc}
   */
  public function put($worker, $data = []): TaskInterface {
    return $this->getQueueForWorker($worker)->put($worker, $data);
  }
  /**
   * {@inheritdoc}
   */
  public function processQueue($name = "default") {
    $queue = $this->getQueue($name);
    if ($task = $queue->reserve()) {
      $this->processTask($task);
    }
  }
  /**
   * {@inheritdoc}
   */
  public function processTask(TaskInterface $task) {
    $worker = $this->getWorker($task->getWorker());
    $queue = $this->getQueue($task->getQueue());
    try {
      $worker->process($task, $queue);
    } catch (Exception $e) {
      $queue->fail($task);
      throw new Exception("Exception while processing queue '".$task->getQueue()."'", 0, $e);
    }
  }
}
