<?php
namespace Starbug\Queue;

/**
 * A simple interface for a queue.
 */
interface QueueManagerInterface {
  /**
   * Put a job into a queue.
   *
   * @param string $worker The class name of the worker.
   * @param array $data The job data.
   */
  public function put($worker, $data = []): TaskInterface;
  /**
   * Process jobs in a queue.
   *
   * @param string $queue The name of the queue.
   */
  public function processQueue($queue = "default");

  /**
   * Process a specific task.
   *
   * @param TaskInterface $task The task to process.
   */
  public function processTask(TaskInterface $task);
}
