<?php
namespace Starbug\Queue\Driver;

use Starbug\Queue\QueueInterface;
use Starbug\Queue\Task;
use Starbug\Queue\TaskInterface;

/**
 * A local array queue implementation.
 */
class LocalArray implements QueueInterface {
  /**
   * The name of the queue.
   *
   * @var string
   */
  protected $name;
  /**
   * Task lists.
   *
   * @var array
   */
  protected $ready = [];
  protected $processing = [];
  protected $failed = [];
  public function __construct($name) {
    $this->name = $name;
  }
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }
  /**
   * {@inheritdoc}
   */
  public function put($worker, $data): TaskInterface {
    $id = bin2hex(random_bytes(16));
    return $this->ready[$id] = new Task($id, $worker, $this->name, $data);
  }
  /**
   * {@inheritdoc}
   */
  public function reserve(): TaskInterface {
    $task = array_shift($this->ready);
    return $this->processing[$task->getId()] = $task;
  }
  /**
   * {@inheritdoc}
   */
  public function release(TaskInterface $task) {
    unset($this->processing[$task->getId()]);
    $this->ready[$task->getId()] = $task;
  }
  /**
   * {@inheritdoc}
   */
  public function remove(TaskInterface $task) {
    unset($this->ready[$task->getId()]);
    unset($this->processing[$task->getId()]);
  }
  /**
   * {@inheritdoc}
   */
  public function complete(TaskInterface $task) {
    $this->remove($task);
  }
  /**
   * {@inheritdoc}
   */
  public function fail(TaskInterface $task) {
    unset($this->processing[$task->getId()]);
    $this->failed[$task->getId()] = $task;
  }
  /**
   * {@inheritdoc}
   */
  public function count() {
    return $this->redis->llen($this->name);
  }
}
