<?php
namespace Pylesos;

class Scheduler
{
    private array $options;

    const SCHEDULER_TIMES = 'SCHEDULER_TIMES';

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function run(Callable $callback, ?Callable $exceptionCallback)
    {
        date_default_timezone_set('Europe/Minsk');
        $optionsHours = $this->getFilteredHours($this->options[self::SCHEDULER_TIMES] ?? '');
        if (!$this->isTimeToRun($optionsHours)) {
            throw new Exception('No time to run, wait for ' . $this->getNextHour($optionsHours) . ':00');
        }

        try {
            $this->checkForSingleInstance($callback, $this->getRunScriptDirectoryName() . '.lock');
        } catch (Exception $e) {
            if ($exceptionCallback) {
                $exceptionCallback($e);
            } else {
                throw $e;
            }
        }
    }

    private function getNextHour(array $optionsHours): ?string
    {
        $currentHour = intval((new \DateTime())->format('H'));
        foreach ($optionsHours as $hour) {
            if ($hour > $currentHour) {
                return $this->formatHour($hour);
            }
        }

        return $this->formatHour($optionsHours[0]);
    }

    private function isTimeToRun(array $optionsHours): bool
    {
        $currentHour = intval((new \DateTime())->format('H'));

        return in_array($currentHour, $optionsHours) || !$optionsHours;
    }

    private function checkForSingleInstance($callback, $lockFile)
    {
        $lockFile = fopen($lockFile, 'c');
        $gotLock = flock($lockFile, LOCK_EX | LOCK_NB, $wouldBlock);
        if ($lockFile === false || (!$gotLock && !$wouldBlock)) {
            throw new Exception(
                "Unexpected error opening or locking lock file. Perhaps you " .
                "don't  have permission to write to the lock file or its " .
                "containing directory?"
            );
        } elseif (!$gotLock && $wouldBlock) {
            throw new Exception("Another instance is already running; terminating.");
        }

        // Lock acquired; let's write our PID to the lock file for the convenience
        // of humans who may wish to terminate the script.
        ftruncate($lockFile, 0);
        fwrite($lockFile, getmypid() . PHP_EOL);
        try {
            $callback();
        } catch (Exception $e) {
            ftruncate($lockFile, 0);
            flock($lockFile, LOCK_UN);

            throw $e;
        };

        // All done; we blank the PID file and explicitly release the lock
        // (although this should be unnecessary) before terminating.
        ftruncate($lockFile, 0);
        flock($lockFile, LOCK_UN);
    }

    private function getFilteredHours(string $times): array
    {
        $withoutEmpty = array_filter(explode(PHP_EOL, $times));
        $filtered = [];
        foreach ($withoutEmpty as $time) {
            if (is_numeric(trim($time))) {
                $filtered[] = intval($time);
            }
        }

        sort($filtered);

        return $filtered;
    }

    private function formatHour(int $hour): string
    {
        if ($hour < 10) {
            return '0' . $hour;
        }

        return $hour;
    }

    private function getRunScriptDirectoryName(): string
    {
        $stack = debug_backtrace();
        $firstFrame = $stack[count($stack) - 1];
        $initialFile = $firstFrame['file'];
        $initialFileParts = explode('/', $initialFile);

        return $initialFileParts[count($initialFileParts) - 2];
    }
}