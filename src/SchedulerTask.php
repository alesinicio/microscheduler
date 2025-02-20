<?php

namespace Alesinicio\MicroScheduler;

use Closure;

class SchedulerTask {
	public function __construct(
		public readonly string   $name,
		private readonly Closure $callback,
		private readonly int     $intervalMicroseconds = 0,
		private float            $nextExecution = 0,
		public readonly bool     $isOneOff = false,

	) {}
	public function reschedule(float $baseTime) : void {
		$this->nextExecution = $baseTime + ($this->intervalMicroseconds / 1_000_000);
	}
	public function shouldRun(float $baseTime) : bool {
		return $this->nextExecution <= $baseTime;
	}
	public function execute() : void {
		($this->callback)();
	}
}