<?php
namespace Starbug\Queue;

interface TaskInterface {
  /**
   * Get the task ID.
   */
  public function getId();
  /**
   * Get the class name of the worker.
   */
  public function getWorker();
  /**
   * Get the name of the queue.
   */
  public function getQueue();
  /**
   * Get the task data.
   */
  public function getData();
}
