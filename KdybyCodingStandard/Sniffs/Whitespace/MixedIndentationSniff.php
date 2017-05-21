<?php

declare(strict_types = 1);

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace KdybyCodingStandard\Sniffs\Whitespace;

use PHP_CodeSniffer_File;

class MixedIndentationSniff implements \PHP_CodeSniffer_Sniff
{

	public const CODE_UNEXPECTED_SPACE = 'UnexpectedSpace';
	public const CODE_UNEXPECTED_TAB = 'UnexpectedTab';

	/**
	 * Should tabs be used for indenting?
	 *
	 * @var bool
	 */
	public $tabIndent = TRUE;

	/**
	 * @var mixed[]
	 */
	private static $whitespaceTokens = [
		T_WHITESPACE,
		T_DOC_COMMENT_WHITESPACE,
	];

	/**
	 * @return int[]
	 */
	public function register(): array
	{
		return self::$whitespaceTokens;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param \PHP_CodeSniffer_File $phpcsFile
	 * @param int $stackPtr
	 * @return int|NULL
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr): ?int
	{
		$tokens = $phpcsFile->getTokens();
		$token = $tokens[$stackPtr];

		if (!self::isNewline($token, $phpcsFile->eolChar) || !array_key_exists($stackPtr + 1, $tokens) || self::isNewline($tokens[$stackPtr + 1], $phpcsFile->eolChar)) {
			// skip if token is not newline
			// and in case there are more consecutive newlines, we care only about the last one
			return NULL;
		}

		// the next token should be indentation otherwise we don't care
		$stackPtr++;
		if (!self::isIndentation($tokens[$stackPtr], $phpcsFile->eolChar)) {
			return $stackPtr; // ignore
		}
		$indent = self::getOriginalContent($tokens[$stackPtr]);
		if ($indent === '') {
			return $stackPtr; // ignore
		}

		$nextPtr = $stackPtr + 1;
		if (array_key_exists($nextPtr, $tokens) && self::isPhpDocStarColumn($tokens[$nextPtr])) {
			// one space is required before phpdoc, once it's opened
			if (substr($indent, -1) !== ' ') {
				$phpcsFile->addErrorOnLine(
					'Unexpected tab before phpdoc',
					$tokens[$nextPtr]['line'],
					self::CODE_UNEXPECTED_TAB
				);
				return $nextPtr;
			}

			$indent = substr($indent, 0, -1);
		}

		if (substr_count($indent, $this->tabIndent ? ' ' : "\t") !== 0) {
			$phpcsFile->addErrorOnLine(
				$this->tabIndent ? 'Unexpected space' : 'Unexpected tab',
				$tokens[$stackPtr]['line'],
				$this->tabIndent ? self::CODE_UNEXPECTED_SPACE : self::CODE_UNEXPECTED_TAB
			);
		}

		return $stackPtr;
	}

	/**
	 * @param mixed[] $token
	 * @return string
	 */
	private static function getOriginalContent(array $token): string
	{
		return (array_key_exists('orig_content', $token) && $token['orig_content'] !== $token['content'])
			? $token['orig_content']
			: $token['content'];
	}

	/**
	 * @param mixed[] $token
	 * @param string $eol
	 * @return bool
	 */
	private static function isIndentation(array $token, string $eol): bool
	{
		return in_array($token['code'], self::$whitespaceTokens, TRUE)
			&& self::getOriginalContent($token) !== $eol;
	}

	/**
	 * @param mixed[] $token
	 * @param string $eol
	 * @return bool
	 */
	private static function isNewline(array $token, string $eol): bool
	{
		return in_array($token['code'], self::$whitespaceTokens, TRUE)
			&& self::getOriginalContent($token) === $eol;
	}

	/**
	 * @param mixed[] $token
	 * @return bool
	 */
	private static function isPhpDocStarColumn(array $token): bool
	{
		return in_array($token['code'], [T_DOC_COMMENT_STAR, T_DOC_COMMENT_CLOSE_TAG], TRUE);
	}

}
