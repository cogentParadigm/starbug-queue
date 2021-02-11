<?php
namespace Starbug\Queue;

use Countable;

/**
 * A simple interface for a queue.
 */
interface QueueInterface extends Countable {
  /**
   * Get the name of the queue.
   *
   * @return string The queue name.
   */
  public function getName();
  /**
   * Put a job into the queue.
   *
   * @param string $worker The full class name of the worker.
   * @param array $data The task data.
   *
   * @return static this instance.
   */
  public function put($worker, $data): TaskInterface;
  /**
   * Reserve a task from the queue.
   *
   * @return TaskInterface The task.
   */
  public function reserve(): ?TaskInterface;
  /**
   * Release a reserved job back into the queue.
   *
   * @param TaskInterface $item The job.
   */
  public function release(TaskInterface $task);
  /**
   * Remove a task from the queue.
   *
   * @param TaskInterface $task The task.
   */
  public function remove(TaskInterface $task);
  /**
   * Complete a task.
   *
   * @param TaskInterface $task The task.
   */
  public function complete(TaskInterface $task);
  /**
   * Fail a task.
   *
   * @param TaskInterface $task The task.
   */
  public function fail(TaskInterface $task);
}
