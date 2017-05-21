<?php

declare(strict_types = 1);

namespace KdybyCodingStandard\Sniffs\Files;

class ControlCharactersSniffTest extends \SlevomatCodingStandard\Sniffs\TestCase
{

	public function testValid(): void
	{
		$this->assertNoSniffErrorInFile($this->checkFile(__DIR__ . '/data/helloWorld.php'));
	}

	public function testInvalid(): void
	{
		$report = $this->checkFile(__DIR__ . '/data/containsControlCharacters.php');

		$this->assertSame(1, $report->getErrorCount());
		$this->assertSniffError($report, 3, ControlCharactersSniff::CODE_CONTAINS_CONTROL_CHARACTERS);
	}

}
