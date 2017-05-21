<?php

declare(strict_types = 1);

namespace KdybyCodingStandard\Sniffs\Files;

class Utf8SniffTest extends \SlevomatCodingStandard\Sniffs\TestCase
{

	public function testValid(): void
	{
		$this->assertNoSniffErrorInFile($this->checkFile(__DIR__ . '/data/helloWorld.php'));
	}

	public function testInvalid(): void
	{
		$report = $this->checkFile(__DIR__ . '/data/invalidUtf8Characters.php');

		$this->assertSame(1, $report->getErrorCount());
		$this->assertSniffError($report, 3, Utf8Sniff::CODE_CONTAINS_INVALID_CHARACTERS);
	}

}
