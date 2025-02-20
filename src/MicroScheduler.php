<?php

namespace Alesinicio\MicroScheduler;

use Exception;
use Psr\Log\LoggerInterface;

class MicroScheduler {
	/* @var array<SchedulerTask> $tasks */
	private array $tasks = [];

	public function __construct(
		private readonly LoggerInterface $logger,
	) {}
	public function addTask(SchedulerTask $task) : self {
		$this->tasks[] = $task;
		return $this;
	}
	public function loop($intervalMicroseconds = 10_000) : never {
		while (true) {
			$now = microtime(true);
			foreach ($this->tasks as $key => $task) {
				try {
					if (!$task->shouldRun($now)) continue;

					$task->execute();
					$task->reschedule($now);
					if ($task->isOneOff) {
						unset($this->tasks[$key]);
					}
				} catch (Exception $e) {
					$this->logger->error('Exception on scheduler', ['exception' => $e->getMessage()]);
					$task->reschedule($now);
				}
			}
			usleep($intervalMicroseconds);
		}
	}
}