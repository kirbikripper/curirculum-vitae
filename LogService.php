<?php

namespace App\Services\Development;

use Illuminate\Support\Arr;

class LogService
{
    private const PAGE_ROWS = 10;

    public function getLogsFilesNames(): array
    {
        $files = array_reverse(glob(storage_path('logs/*.log')));
        usort($files, function (string $firstFile, string $secondFile) {
            return filemtime($firstFile) < filemtime($secondFile);
        });

        return array_map(function (string $filePath) {
            return pathinfo($filePath, PATHINFO_FILENAME);
        }, $files);
    }

    public function getLogs(string $name, ?int $page = 1): array
    {
        $fileName = storage_path("logs/{$name}.log");
        if (!file_exists($fileName)) {
            return [];
        }

        $fileHandle = fopen($fileName, "r");

        if($fileHandle === false) {
            return [];
        }

        $offset = -1;
        $logs = [];
        for ($element = 0; $element < $page * self::PAGE_ROWS; $element++) {
            $logElement = $this->getSingleLogElement($fileHandle, $offset);
            if (empty($logElement)) {
                break;
            }

            $offset -= mb_strlen($logElement);

            if ($element < ($page - 1) * self::PAGE_ROWS) {
                continue;
            }

            $logs[] = $this->parseLogElement($logElement);
        }

        fclose($fileHandle);

        return $logs;
    }

    private function getSingleLogElement($fileHandle, int $offset): string
    {
        $lines = [];
        $currentLine = '';

        while (-1 !== fseek($fileHandle, $offset, SEEK_END)) {
            $char = fgetc($fileHandle);
            $offset--;

            if ($char !== PHP_EOL) {
                $currentLine = $char . $currentLine;

                continue;
            }

            if ($this->validateLogElementStart($currentLine)) {
                break;
            }

            $lines[] = $currentLine;
            $currentLine = '';
        }

        $lines[] = $currentLine;

        return implode(PHP_EOL, array_reverse($lines));
    }

    private function parseLogElement(string $logElement): array
    {
        $pattern = "/^\[(?<date>.*)\]\s(?<env>\w+)\.(?<type>\w+): (?<message>.*)/s";

        preg_match($pattern, $logElement, $matches);

        return Arr::only($matches, ['date', 'env', 'type', 'message']);
    }

    private function validateLogElementStart(string $line): bool
    {
        preg_match('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}]/', $line, $matches);

        return !empty($matches);
    }
}
