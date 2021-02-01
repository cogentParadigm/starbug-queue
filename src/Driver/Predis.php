<?php
namespace Starbug\Queue\Driver;

use Predis\Client;
use Starbug\Queue\QueueInterface;
use Starbug\Queue\Task;
use Starbug\Queue\TaskInterface;

/**
 * A Predis queue implementation.
 */
class Predis implements QueueInterface {
  /**
   * The name of the queue.
   *
   * @var string
   */
  protected $name;
  /**
   * Redis client
   *
   * @var Client
   */
  protected $redis;
  public function __construct($name, ?Client $redis) {
    $this->name = $name;
    $this->redis = $redis ?? new Client();
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
    $this->redis->set($this->name.":worker:".$id, $worker);
    $this->redis->hmset($this->name.":item:".$id, $data);
    $this->redis->rpush($this->name, $id);
    return new Task($id, $worker, $this->name, $data);
  }
  /**
   * {@inheritdoc}
   */
  public function reserve(): TaskInterface {
    $id = $this->redis->blmove($this->name, $this->name.":processing", "LEFT", "RIGHT", 0);
    $data = $this->redis->hgetall($this->name.":item:".$id);
    $worker = $this->redis->get($this->name.":worker:".$id);
    return new Task($id, $worker, $this->name, $data);
  }
  /**
   * {@inheritdoc}
   */
  public function release(TaskInterface $task) {
    $this->redis->lrem($this->name.":processing", $task->getId());
    $this->redis->rpush($this->name, $task->getId());
  }
  /**
   * {@inheritdoc}
   */
  public function remove(TaskInterface $task) {
    $this->redis->lrem($this->name, $task->getId());
    $this->redis->lrem($this->name.":processing", $task->getId());
    $this->redis->del($this->name.":item:".$task->getId());
    $this->redis->del($this->name.":worker:".$task->getId());
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
    $this->redis->lrem($this->name.":processing", $task->getId());
    $this->redis->rpush($this->name.":failed", $task->getId());
  }
  /**
   * {@inheritdoc}
   */
  public function count() {
    return $this->redis->llen($this->name);
  }
}
