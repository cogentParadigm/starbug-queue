<?php
namespace Starbug\Queue;

interface WorkerInterface {
  public function process(TaskInterface $task, QueueInterface $queue);
}
