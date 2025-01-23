<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTaskCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $taskData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($taskData)
    {
        $this->taskData = $taskData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Task::create($this->taskData);
    }
}
