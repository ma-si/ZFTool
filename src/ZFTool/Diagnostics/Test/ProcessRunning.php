<?php
namespace ZFTool\Diagnostics\Test;

use ZFTool\Diagnostics\Exception\InvalidArgumentException;
use ZFTool\Diagnostics\Result\Failure;
use ZFTool\Diagnostics\Result\Success;

/**
 * Check if system service is running.
 */
class ProcessRunning extends AbstractTest implements TestInterface
{
    /**
     * @var string|null
     */
    private $processName;
    
    /**
     * @var integer|null
     */
    private $pid;
    
    /**
     * @var string
     */
    private $os;

    /**
     * @param string|int $processNameOrPid   Name or ID of the process to find.
     * @throws ZFTool\Diagnostics\Exception\InvalidArgumentException
     */
    public function __construct($processNameOrPid)
    {
        if (empty($processNameOrPid)) {
            throw new InvalidArgumentException(
                'Wrong argument provided for ProcessRunning check - ' .
                'expected a process name (string) or pid (positive number).'
            );
        }

        if (
            !is_numeric($processNameOrPid) && !is_scalar($processNameOrPid)
            || (is_numeric($processNameOrPid) && $processNameOrPid < 0)
        ) {
            throw new InvalidArgumentException(sprintf(
                'Wrong argument provided for ProcessRunning check - ' .
                'expected a process name (string) or pid (positive number) but got %s',
                gettype($processNameOrPid).(is_numeric($processNameOrPid) ? ' '
                    .(int)$processNameOrPid : '').'.'
            ));
        }

        if (is_numeric($processNameOrPid)) {
            $this->pid = $processNameOrPid;
        } else {
            $this->processName = $processNameOrPid;
        }
        
        // TODO: make more OS agnostic - regexp maybe
        $os = php_uname('s');
        switch ($os) {
            case 'Windows NT':
                $this->os = 'windows';
                break;
            default:
                $this->os = 'linux';
                break;
        }
    }

    /**
     * Run system services check and return a Success if the service were found,
     * Failure if not.
     *
     * @return Failure|Success
     */
    public function run()
    {
        if ($this->pid) {
            return $this->checkPid();
        } else {
            return $this->checkProcessName();
        }
    }
    
    private function checkPid()
    {
        if($this->os=='windows') {
            exec('tasklist /svc /fi "pid eq ' . (int)$this->pid . '" | find /i "'
                . (int)$this->pid . '"', $output, $return);
        } else {
            exec('ps -p ' . (int)$this->pid, $output, $return);
        }

        if ($return == 1) {
            return new Failure(
                sprintf('Process with PID %s is not currently running.', $this->pid)
            );
        }
        
        return new Success(sprintf('Process with PID %s is running.', $this->pid));
    }
    
    private function checkProcessName()
    {
        if($this->os=='windows') {
            exec('sc query ' . escapeshellarg($this->processName)
                . ' | find "STATE" | find "RUNNING"', $output, $return);
        } else {
            exec('ps -ef | grep ' . escapeshellarg($this->processName)
                . ' | grep -v grep', $output, $return);
        }

        if ($return == 1) {
            return new Failure(
                sprintf(
                    'Could not find any running process containing "%s"',
                    $this->processName
                )
            );
        }
        
        return new Success(sprintf('Process %s is running.', $this->processName));
    }
}
