<?php
namespace Starbug\Queue;

class Task implements TaskInterface {
  protected $id;
  protected $worker;
  protected $data;
  public function __construct($id, $worker, $queue, $data = []) {
    $this->id = $id;
    $this->worker = $worker;
    $this->queue = $queue;
    $this->data = $data;
  }
  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->id;
  }
  /**
   * {@inheritdoc}
   */
  public function getWorker() {
    return $this->worker;
  }
  /**
   * {@inheritdoc}
   */
  public function getQueue() {
    return $this->queue;
  }
  /**
   * {@inheritdoc}
   */
  public function getData() {
    return $this->data;
  }
}
