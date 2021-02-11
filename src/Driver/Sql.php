<?php
namespace Starbug\Queue\Driver;

use Starbug\Core\DatabaseInterface;
use Starbug\Queue\QueueInterface;
use Starbug\Queue\Task;
use Starbug\Queue\TaskInterface;

/**
 * An SQL queue implementation.
 */
class Sql implements QueueInterface {
  /**
   * The name of the queue.
   *
   * @var string
   */
  protected $name;
  /**
   * Database client
   *
   * @var DatabaseInterface
   */
  protected $db;
  protected $tableName;
  public function __construct($name, DatabaseInterface $db, $tableName = "queues") {
    $this->name = $name;
    $this->db = $db;
    $this->tableName = $tableName;
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
    $task = $this->store([
      "queue" => $this->name,
      "worker" => $worker,
      "data" => json_encode($data),
      "status" => "ready"
    ]);
    return new Task($task["id"], $worker, $this->name, $data);
  }
  /**
   * {@inheritdoc}
   */
  public function reserve(): ?TaskInterface {
    if ($task = $this->query()) {
      $decoded = json_decode($task["data"], true);
      $this->store(["id" => $task["id"], "status" => "processing"]);
      return new Task($task["id"], $task["worker"], $this->name, $decoded);
    }
    return null;
  }
  /**
   * {@inheritdoc}
   */
  public function release(TaskInterface $task) {
    $this->store(["id" => $task->getId(), "status" => "ready"]);
  }
  /**
   * {@inheritdoc}
   */
  public function remove(TaskInterface $task) {
    $this->db->query($this->tableName)->condition("id", $task->getId())->delete();
  }
  /**
   * {@inheritdoc}
   */
  public function complete(TaskInterface $task) {
    $this->store(["id" => $task->getId(), "status" => "completed"]);
  }
  /**
   * {@inheritdoc}
   */
  public function fail(TaskInterface $task) {
    $this->store(["id" => $task->getId(), "status" => "failed"]);
  }
  /**
   * {@inheritdoc}
   */
  public function count() {
    return $this->db->query($this->tableName)
      ->condition("queue", $this->name)
      ->count();
  }
  /**
   * Helper to write to the queue.
   *
   * @param array $data The data to write.
   *
   * @return array The same data with id added if a record was inserted.
   */
  protected function store(array $data) {
    $this->db->store($this->tableName, $data);
    if (empty($data["id"])) {
      $data["id"] = $this->db->getInsertId($this->tableName);
    }
    return $data;
  }
  /**
   * Helper to read from the queue.
   *
   * @return void
   */
  protected function query() {
    return $this->db->query($this->tableName)
      ->condition("queue", $this->name)
      ->condition("status", "ready")
      ->sort("position")
      ->forUpdate()
      ->one();
  }
}
