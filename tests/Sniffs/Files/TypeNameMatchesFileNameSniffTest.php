<?php

declare(strict_types = 1);

namespace KdybyCodingStandard\Sniffs\Files;

class TypeNameMatchesFileNameSniffTest extends \SlevomatCodingStandard\Sniffs\TestCase
{

	public function testError(): void
	{
		$report = $this->checkFile(__DIR__ . '/data/project1_psr4/rootNamespace/boo.php', [
			'rootNamespaces' => ['tests/Sniffs/Files/data/project1_psr4/rootNamespace' => 'RootNamespace'],
		]);

		$this->assertSame(1, $report->getErrorCount());
		$this->assertSniffError($report, 5, TypeNameMatchesFileNameSniff::CODE_NO_MATCH_BETWEEN_TYPE_NAME_AND_FILE_NAME);

		$report = $this->checkFile(__DIR__ . '/data/project1_psr4/rootNamespace/coo/Foo.php', [
			'rootNamespaces' => ['tests/Sniffs/Files/data/project1_psr4/rootNamespace' => 'RootNamespace'],
		]);

		$this->assertSame(1, $report->getErrorCount());
		$this->assertSniffError($report, 5, TypeNameMatchesFileNameSniff::CODE_NO_MATCH_BETWEEN_TYPE_NAME_AND_FILE_NAME);
	}

	public function testNoError(): void
	{
		$this->assertNoSniffErrorInFile($this->checkFile(__DIR__ . '/data/project1_psr4/rootNamespace/Foo.php', [
			'rootNamespaces' => ['tests/Sniffs/Files/data/project1_psr4/rootNamespace' => 'RootNamespace'],
		]));
	}

	public function testSkippedDir(): void
	{
		$this->assertNoSniffErrorInFile($this->checkFile(__DIR__ . '/data/project1_psr4/rootNamespace/skippedDir/Bar.php', [
			'rootNamespaces' => ['tests/Sniffs/Files/data/project1_psr4/rootNamespace' => 'RootNamespace'],
			'skipDirs' => ['skippedDir'],
		]));
	}

	public function testIgnoredNamespace(): void
	{
		$this->assertNoSniffErrorInFile($this->checkFile(__DIR__ . '/data/project1_psr4/ignoredNamespace.php', [
			'ignoredNamespaces' => ['IgnoredNamespace'],
		]));
	}

	public function testNoNamespace(): void
	{
		$this->assertNoSniffErrorInFile($this->checkFile(__DIR__ . '/data/project1_psr4/noNamespace.php'));
		$this->assertNoSniffErrorInFile($this->checkFile(__DIR__ . '/data/project1_psr4/rootNamespace/exceptions.php'));
	}

	public function testExceptionsFileDeep(): void
	{
		$this->assertNoSniffErrorInFile($this->checkFile(__DIR__ . '/data/project1_psr4/rootNamespace/Nemam/exceptions.php'));
	}

	public function testExceptionsFileError(): void
	{
		$report = $this->checkFile(__DIR__ . '/data/project1_psr4/rootNamespace/Nemam/Boo/exceptions.php');

		$this->assertSame(2, $report->getErrorCount());
		$this->assertSniffError($report, 5, TypeNameMatchesFileNameSniff::CODE_NO_MATCH_BETWEEN_EXCEPTION_NAMESPACE_AND_FILE_NAME);
		$this->assertSniffError($report, 10, TypeNameMatchesFileNameSniff::CODE_NO_MATCH_BETWEEN_EXCEPTION_NAMESPACE_AND_FILE_NAME);
	}

	public function testPsr0Error(): void
	{
		$report = $this->checkFile(__DIR__ . '/data/project2_psr0/tests/FakeTests/Annotations/files/mocks.php');

		$this->assertSame(2, $report->getErrorCount());
		$this->assertSniffError($report, 5, TypeNameMatchesFileNameSniff::CODE_NO_MATCH_BETWEEN_TYPE_NAME_AND_FILE_NAME);
		$this->assertSniffError($report, 10, TypeNameMatchesFileNameSniff::CODE_NO_MATCH_BETWEEN_TYPE_NAME_AND_FILE_NAME);
	}

	public function testPsr0Valid(): void
	{
		$this->assertNoSniffErrorInFile($this->checkFile(__DIR__ . '/data/project2_psr0/tests/FakeTests/Annotations/ExtensionTest.phpt', [
			'extensions' => ['php', 'phpt'],
		]));
	}

	public function testPsr0BrokenAutoloading(): void
	{
		$report1 = $this->checkFile(__DIR__ . '/data/project3_broken_autoloading/tests/FakeTests/Annotations/ExtensionTest.phpt', [
			'extensions' => ['php', 'phpt'],
		]);
		$this->assertSame(1, $report1->getErrorCount());
		$this->assertSniffError($report1, 5, TypeNameMatchesFileNameSniff::CODE_BROKEN_AUTOLOADING);

		$report2 = $this->checkFile(__DIR__ . '/data/project3_broken_autoloading/tests/FakeTests/Annotations/files/mocks.php');
		$this->assertSame(2, $report2->getErrorCount());
		$this->assertSniffError($report2, 5, TypeNameMatchesFileNameSniff::CODE_BROKEN_AUTOLOADING);
		$this->assertSniffError($report2, 10, TypeNameMatchesFileNameSniff::CODE_BROKEN_AUTOLOADING);
	}

}
