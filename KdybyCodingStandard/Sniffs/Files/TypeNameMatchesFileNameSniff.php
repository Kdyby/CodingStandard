<?php

declare(strict_types = 1);

namespace KdybyCodingStandard\Sniffs\Files;

use KdybyCodingStandard\Helpers\ComposerJsonHelper;
use PHP_CodeSniffer_File;
use SlevomatCodingStandard\Helpers\ClassHelper;
use SlevomatCodingStandard\Helpers\NamespaceHelper;
use SlevomatCodingStandard\Helpers\SniffSettingsHelper;
use SlevomatCodingStandard\Helpers\StringHelper;
use SlevomatCodingStandard\Helpers\SuppressHelper;
use SlevomatCodingStandard\Sniffs\Files\FilepathNamespaceExtractor;

class TypeNameMatchesFileNameSniff implements \PHP_CodeSniffer_Sniff
{

	public const NAME = 'KdybyCodingStandard.Files.TypeNameMatchesFileName';
	public const CODE_NO_MATCH_BETWEEN_TYPE_NAME_AND_FILE_NAME = 'NoMatchBetweenTypeNameAndFileName';
	public const CODE_NO_MATCH_BETWEEN_EXCEPTION_NAMESPACE_AND_FILE_NAME = 'NoMatchBetweenExceptionNamespaceAndFileName';
	public const CODE_BROKEN_AUTOLOADING = 'BrokenAutoloading';

	/** @var string[] index(integer) => extension */
	public $extensions = ['php'];

	/** @var string[] index(integer) => extension */
	private $normalizedExtensions;

	/** @var bool */
	public $allowExceptionsFile = TRUE;

	/** @var string[] path(string) => namespace */
	public $rootNamespaces = [];

	/** @var string[] path(string) => namespace */
	private $normalizedRootNamespaces;

	/** @var string[] */
	public $skipDirs = [];

	/** @var string[] */
	private $normalizedSkipDirs;

	/** @var string[] */
	public $ignoredNamespaces = [];

	/** @var string[] */
	private $normalizedIgnoredNamespaces;

	/**
	 * @return int[]
	 */
	public function register(): array
	{
		return [
			T_CLASS,
			T_INTERFACE,
			T_TRAIT,
		];
	}

	/**
	 * @return string[] path(string) => namespace
	 */
	private function getRootNamespaces(): array
	{
		if ($this->normalizedRootNamespaces === NULL) {
			$this->normalizedRootNamespaces = SniffSettingsHelper::normalizeAssociativeArray($this->rootNamespaces);
		}

		return $this->normalizedRootNamespaces;
	}

	/**
	 * @return string[]
	 */
	private function getSkipDirs(): array
	{
		if ($this->normalizedSkipDirs === NULL) {
			$this->normalizedSkipDirs = SniffSettingsHelper::normalizeArray($this->skipDirs);
		}

		return $this->normalizedSkipDirs;
	}

	/**
	 * @return string[]
	 */
	private function getIgnoredNamespaces(): array
	{
		if ($this->normalizedIgnoredNamespaces === NULL) {
			$this->normalizedIgnoredNamespaces = SniffSettingsHelper::normalizeArray($this->ignoredNamespaces);
		}

		return $this->normalizedIgnoredNamespaces;
	}

	/**
	 * @return string[] index(integer) => extension
	 */
	private function getExtensions(): array
	{
		if ($this->normalizedExtensions === NULL) {
			$this->normalizedExtensions = SniffSettingsHelper::normalizeArray($this->extensions);
		}

		return $this->normalizedExtensions;
	}

	/**
	 * @param string[] $additionalRootNamespaces path(string) => namespace
	 * @return \SlevomatCodingStandard\Sniffs\Files\FilepathNamespaceExtractor
	 */
	private function getNamespaceExtractor(array $additionalRootNamespaces): FilepathNamespaceExtractor
	{
		return new FilepathNamespaceExtractor(
			$this->getRootNamespaces() + SniffSettingsHelper::normalizeAssociativeArray($additionalRootNamespaces),
			$this->getSkipDirs(),
			$this->getExtensions()
		);
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param \PHP_CodeSniffer_File $phpcsFile
	 * @param int $typePointer
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $typePointer): void
	{
		$tokens = $phpcsFile->getTokens();
		$namePointer = (int) $phpcsFile->findNext(T_STRING, $typePointer + 1);

		$namespacePointer = $phpcsFile->findPrevious(T_NAMESPACE, $typePointer - 1);
		if ($namespacePointer === FALSE) {
			// Skip types without a namespace
			return;
		}

		$typeName = NamespaceHelper::normalizeToCanonicalName(ClassHelper::getFullyQualifiedName($phpcsFile, $typePointer));

		foreach ($this->getIgnoredNamespaces() as $ignoredNamespace) {
			if (StringHelper::startsWith($typeName, $ignoredNamespace . NamespaceHelper::NAMESPACE_SEPARATOR)) {
				return;
			}
		}

		$fileExtension = pathinfo($phpcsFile->getFilename(), PATHINFO_EXTENSION);
		if (!in_array($fileExtension, $this->getExtensions(), TRUE)) {
			return; // ignore
		}

		if (SuppressHelper::isSniffSuppressed($phpcsFile, $typePointer, self::NAME)) {
			return;
		}

		$additionalRootNamespaces = ComposerJsonHelper::getComposerAutoLoadingPairs($phpcsFile->getFilename());

		$expectedTypeName = $this
			->getNamespaceExtractor($additionalRootNamespaces)
			->getTypeNameFromProjectPath($phpcsFile->getFilename());

		if ($expectedTypeName === NULL) {
			$phpcsFile->addError(
				sprintf(
					'The sniff is unable to guess the expected type name for %s %s',
					$tokens[$typePointer]['content'],
					$typeName
				),
				$namePointer,
				self::CODE_BROKEN_AUTOLOADING
			);

			return;
		}

		if ($this->allowExceptionsFile && $this->isInExceptionsFile($phpcsFile) && $this->isProbablyException($typeName)) {
			$expectedExceptionsNamespace = substr($expectedTypeName, 0, -11); // strip "\exceptions"
			$exceptionNamespace = substr($typeName, 0, strrpos($typeName, '\\'));

			if ($expectedExceptionsNamespace !== $exceptionNamespace) {
				$phpcsFile->addError(
					sprintf(
						'Namespace %s does not match expected %s.',
						$exceptionNamespace,
						$expectedExceptionsNamespace
					),
					$namePointer,
					self::CODE_NO_MATCH_BETWEEN_EXCEPTION_NAMESPACE_AND_FILE_NAME
				);
			}

			return;
		}

		if ($typeName !== $expectedTypeName) {
			$phpcsFile->addError(
				sprintf(
					'%s name %s does not match filepath and is expected to be %s.',
					ucfirst($tokens[$typePointer]['content']),
					$typeName,
					$expectedTypeName
				),
				$namePointer,
				self::CODE_NO_MATCH_BETWEEN_TYPE_NAME_AND_FILE_NAME
			);
		}
	}

	private function isProbablyException(string $typeName): bool
	{
		return substr($typeName, -9) === 'Exception';
	}

	private function isInExceptionsFile(PHP_CodeSniffer_File $phpcsFile): bool
	{
		return basename($phpcsFile->getFilename()) === 'exceptions.php';
	}

}
