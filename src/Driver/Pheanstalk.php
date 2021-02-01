<?php
namespace Starbug\Queue\Driver;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Job;
use Pheanstalk\JobId;
use Pheanstalk\Pheanstalk;
use Starbug\Queue\QueueInterface;
use Starbug\Queue\Task;
use Starbug\Queue\TaskInterface;

/**
 * A Pheanstalk queue implementation.
 */
class PheanstalkQueue implements QueueInterface {
  /**
   * The name of the queue.
   *
   * @var string
   */
  protected $name;
  /**
   * Pheanstalk client
   *
   * @var Pheanstalk
   */
  protected $queue;

  public function __construct($name, $prefix = "", ?Pheanstalk $queue = null) {
    $this->name = $name;
    $this->prefix = $prefix;
    $this->queue = $queue ?? Pheanstalk::create("localhost");
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
    $task = [
      "worker" => $worker,
      "data" => $data
    ];
    $job = $this->queue->useTube($this->prefix.$this->name)->put(json_encode($task));
    return new Task($job->getId(), $worker, $this->name, $data);
  }
  /**
   * {@inheritdoc}
   */
  public function reserve(): TaskInterface {
    return $this->jobToTask($this->queue->watch($this->prefix.$this->name)->reserve());
  }
  /**
   * {@inheritdoc}
   */
  public function release(TaskInterface $task) {
    $this->queue->release($this->taskToJob($task));
  }
  /**
   * {@inheritdoc}
   */
  public function remove(TaskInterface $task) {
    $this->queue->delete($this->taskToJob($task));
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
    $this->queue->bury($this->taskToJob($task));
  }
  /**
   * {@inheritdoc}
   */
  public function count() {
    return $this->queue->statsTube($this->prefix.$this->name)["current-jobs-ready"];
  }

  /**
   * Helper to convert from Pheanstalk\Job to Starbug\Queue\Task
   *
   * @param Job $job The pheanstalk job.
   *
   * @return TaskInterface The converted task.
   */
  protected function jobToTask(Job $job): TaskInterface {
    $decoded = json_decode($job->getData(), true);
    return new Task($job->getId(), $decoded["worker"], $this->name, $decoded["data"]);
  }
  /**
   * Helper to convert from Starbug\Queue\Task to Pheastalk\JobIdInterface
   *
   * @param TaskInterface $task The task.
   *
   * @return JobIdInterface The converted Pheanstalk job reference.
   */
  protected function taskToJob(TaskInterface $task): JobIdInterface {
    return new JobId($task->getId());
  }
}
