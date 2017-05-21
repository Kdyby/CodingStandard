<?php

declare(strict_types = 1);

namespace KdybyCodingStandard\Sniffs\Whitespace;

class MixedIndentationSniffTest extends \SlevomatCodingStandard\Sniffs\TestCase
{

	public function testValid(): void
	{
		$this->assertNoSniffErrorInFile($this->checkFile(__FILE__));
	}

	public function testInvalidSpaces(): void
	{
		$report = $this->checkFile(__DIR__ . '/data/mixedIndentation.php');

		$this->assertSame(10, $report->getErrorCount());
		$this->assertSniffError($report, 7, MixedIndentationSniff::CODE_UNEXPECTED_SPACE);
		$this->assertSniffError($report, 9, MixedIndentationSniff::CODE_UNEXPECTED_SPACE);
		$this->assertSniffError($report, 28, MixedIndentationSniff::CODE_UNEXPECTED_TAB);
		$this->assertSniffError($report, 34, MixedIndentationSniff::CODE_UNEXPECTED_SPACE);
		$this->assertSniffError($report, 43, MixedIndentationSniff::CODE_UNEXPECTED_SPACE);
		$this->assertSniffError($report, 49, MixedIndentationSniff::CODE_UNEXPECTED_SPACE);
		$this->assertSniffError($report, 55, MixedIndentationSniff::CODE_UNEXPECTED_SPACE);
		$this->assertSniffError($report, 62, MixedIndentationSniff::CODE_UNEXPECTED_TAB);
		$this->assertSniffError($report, 71, MixedIndentationSniff::CODE_UNEXPECTED_TAB);
		$this->assertSniffError($report, 77, MixedIndentationSniff::CODE_UNEXPECTED_SPACE);
	}

	public function testInvalidTabs(): void
	{
		$report = $this->checkFile(__DIR__ . '/data/mixedIndentation.php', [
			'tabIndent' => FALSE,
		]);

		$this->assertSame(42, $report->getErrorCount());
		$this->assertSniffError($report, 5, MixedIndentationSniff::CODE_UNEXPECTED_TAB);
		$this->assertSniffError($report, 7, MixedIndentationSniff::CODE_UNEXPECTED_TAB);
	}

}
