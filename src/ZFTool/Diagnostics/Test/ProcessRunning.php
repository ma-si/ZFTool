<?php
namespace ZFTool\Diagnostics\Test;

use ZFTool\Diagnostics\Exception\InvalidArgumentException;
use ZFTool\Diagnostics\Result\Failure;
use ZFTool\Diagnostics\Result\Success;

/**
 * Check if system service is running.
 *
 * @package ZFTool\Diagnostics\Test
 */
class ProcessRunning extends AbstractTest implements TestInterface
{
    /**
     * @var string
     */
    private $processName;
    private $pid;
    private $os;

    /**
     * @param string|int $processNameOrPid   Name or ID of the process to find.
     * @throws \InvalidArgumentException
     */
    public function __construct($processNameOrPid)
    {
        if (empty($processNameOrPid)) {
            throw new InvalidArgumentException(sprintf(
                'Wrong argument provided for ProcessRunning check - ' .
                'expected a process name (string) or pid (positive number).',
                gettype($processNameOrPid)
            ));
        }

        if (!is_numeric($processNameOrPid) && !is_scalar($processNameOrPid)) {
            throw new InvalidArgumentException(sprintf(
                'Wrong argument provided for ProcessRunning check - ' .
                'expected a process name (string) or pid (positive number) but got %s',
                gettype($processNameOrPid)
            ));
        }

        if (is_numeric($processNameOrPid)) {
            if ((int)$processNameOrPid < 0) {
                throw new InvalidArgumentException(sprintf(
                    'Wrong argument provided for ProcessRunning check - ' .
                    'expected pid to be a positive number but got %s',
                    (int)$processNameOrPid
                ));
            }
            $this->pid = (int)$processNameOrPid;
        } else {
            $this->processName = $processNameOrPid;
        }
        
        // TODO: make more OS agnostic - regexp for specific strings like NT maybe
        exec('uname', $output, $return);
        switch ($return) {
            case 'MINGW32_NT-6.2':
                $this->os = 'windows';
                break;
            default:
                $this->os = 'linux';
                break;
        }
    }

    /**
     * Run system services check and return a Success if the service were found, Failure if not.
     *
     * @return Failure|Success
     */
    public function run()
    {
        // TODO: make more OS agnostic
        if ($this->pid) {
            if($this->os=='windows') {
                exec('tasklist /svc /fi "pid eq ' . (int)$this->pid . '" | find /i "' . (int)$this->pid . '"', $output, $return);
            }
            else {
                exec('ps -p ' . (int)$this->pid, $output, $return);
            }
            
            if ($return == 1) {
                return new Failure(sprintf('Process with PID %s is not currently running.', $this->pid));
            }
            else
            {
                return new Success(sprintf('Process with PID %s is running.', $this->pid));
            }
        } else {
            if($this->os=='windows') {
                exec('sc query ' . escapeshellarg($this->processName) . ' | find "STATE" | find "RUNNING"', $output, $return);
            }
            else {
                exec('ps -ef | grep ' . escapeshellarg($this->processName) . ' | grep -v grep', $output, $return);
            }

            if ($return == 1) {
                return new Failure(sprintf('Could not find any running process containing "%s"', $this->processName));
            }
            else
            {
                return new Success(sprintf('Process %s is running.', $this->processName));
            }
        }
    }
}
