<?php

declare(strict_types=1);

namespace OCR\Utility\Http\Response;

use OCR\Exception\Exception;

class OcrSpaceResponseParser extends JsonResponseParser
{
    protected function validateResponseData(array $data): bool
    {
        $exitCode = $data['OCRExitCode'];
        if (!is_integer($exitCode)) {
            throw new Exception();
        }

        $success = (
            $exitCode === 1
                ||
            $exitCode === 2
        );

        return $success;
    }

    protected function parseResponseData(array $data): string
    {
        $results = $data['ParsedResults'] ?? null;
        if (!is_array($results)) {
            throw new Exception();
        }

        foreach ($results as &$result) {
            if (!is_array($result)) {
                throw new Exception();
            }

            $result = $this->parseResult($result);
        }

        /** @var string[] $results */
        $result = implode("\n", $results);

        return $result;
    }

    /**
     * @param mixed[] $data
     */
    private function parseResult(array $data): string
    {
        $result = $data['ParsedText'] ?? null;
        if (!is_string($result)) {
            throw new Exception();
        }

        return $result;
    }
}
