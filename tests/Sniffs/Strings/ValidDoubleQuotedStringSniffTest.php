<?php

declare(strict_types = 1);

namespace KdybyCodingStandard\Sniffs\Strings;

class ValidDoubleQuotedStringSniffTest extends \SlevomatCodingStandard\Sniffs\TestCase
{

	public function testValid(): void
	{
		$this->assertNoSniffErrorInFile($this->checkFile(__DIR__ . '/data/valid.php'));
	}

	public function testInvalid(): void
	{
		$report = $this->checkFile(__DIR__ . '/data/escapingSequencesInStrings.php');

		$this->assertSame(4, $report->getErrorCount());
		$this->assertSniffError($report, 11, ValidDoubleQuotedStringSniff::CODE_INVALID_ESCAPE_SEQUENCE);
		$this->assertSniffError($report, 13, ValidDoubleQuotedStringSniff::CODE_INVALID_ESCAPE_SEQUENCE);
		$this->assertSniffError($report, 40, ValidDoubleQuotedStringSniff::CODE_INVALID_ESCAPE_SEQUENCE);
		$this->assertSniffError($report, 41, ValidDoubleQuotedStringSniff::CODE_INVALID_ESCAPE_SEQUENCE);
	}

	public function testInvalidStrictly(): void
	{
		$report = $this->checkFile(__DIR__ . '/data/escapingSequencesInStrings.php', [
			'strict' => TRUE,
		]);

		$this->assertSame(7, $report->getErrorCount());
		$this->assertSniffError($report, 11, ValidDoubleQuotedStringSniff::CODE_INVALID_ESCAPE_SEQUENCE);
		$this->assertSniffError($report, 13, ValidDoubleQuotedStringSniff::CODE_INVALID_ESCAPE_SEQUENCE);
		$this->assertSniffError($report, 29, ValidDoubleQuotedStringSniff::CODE_INVALID_ESCAPE_SEQUENCE);
		$this->assertSniffError($report, 30, ValidDoubleQuotedStringSniff::CODE_INVALID_ESCAPE_SEQUENCE);
		$this->assertSniffError($report, 31, ValidDoubleQuotedStringSniff::CODE_INVALID_ESCAPE_SEQUENCE);
		$this->assertSniffError($report, 40, ValidDoubleQuotedStringSniff::CODE_INVALID_ESCAPE_SEQUENCE);
		$this->assertSniffError($report, 41, ValidDoubleQuotedStringSniff::CODE_INVALID_ESCAPE_SEQUENCE);
	}

}
