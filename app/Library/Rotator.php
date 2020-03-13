<?php
namespace App\Library;

trait Rotator
{
    private array $rows;

    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public function getRow()
    {
        return $this->getRows()[0] ?? null;
    }

    public function getRowsCount(): int
    {
        return count($this->rows);
    }

    public function skip(): void
    {
        unset($this->rows[0]);
        $this->rows = array_values($this->rows);
    }
}
