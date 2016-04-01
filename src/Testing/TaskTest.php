<?php
/**
 * @see zan/test/Foundation/Coroutine/TaskDemoTest.php
 */
namespace Zan\Framework\Testing;

use Zan\Framework\Foundation\Core\Event;
use Zan\Framework\Foundation\Core\EventChain;
use Zan\Framework\Foundation\Coroutine\Task;

class TaskTest extends UnitTest
{
    public static $isInitialized = false;
    
    protected $taskMethodPattern = '/^task.+/i';
    protected $taskCounter = 0;
    protected $coroutines = [];

    public function testTasksWork()
    {
        $this->initTask();

        $this->taskCounter++;
        EventChain::before('test_task_num_' . $this->taskCounter, 'test_task_done');

        $this->scanTasks(); 
        $taskCoroutine = $this->runTest();
        Task::execute($taskCoroutine);
    }
    
    protected function scanTasks()
    {
        $ref = new \ReflectionClass($this);
        $methods = $ref->getMethods(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $method->getName();
            if (!preg_match($this->taskMethodPattern, $methodName)) {
                continue;
            }

            $coroutine = $this->$methodName();
            $this->coroutines[] = $coroutine;
        } 
    }

    protected function initTask()
    {
        if (TaskTest::$isInitialized) {
            return false;
        }
        TaskTest::$isInitialized = true;
        
        Event::bind('test_task_done', function () {
            swoole_event_exit();
        });
    }
    
    protected function runTest()
    {
        yield parallel($this->coroutines);
        Event::fire('test_task_done');
    }

}





