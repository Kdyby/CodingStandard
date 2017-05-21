<?php

declare(strict_types = 1);

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace KdybyCodingStandard\Sniffs\Files;

use Nette\Utils\Strings;
use PHP_CodeSniffer_File;

class Utf8Sniff implements \PHP_CodeSniffer_Sniff
{

	public const CODE_CONTAINS_INVALID_CHARACTERS = 'ContainsInvalidCharacters';

	/**
	 * @return int[]
	 */
	public function register(): array
	{
		return [T_OPEN_TAG];
	}

	/**
	 * @see https://github.com/nette/code-checker/blob/3cd542ddbdf38f54ec445da17c4004256ef5a181/src/code-checker.php
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param \PHP_CodeSniffer_File $phpcsFile
	 * @param int $stackPtr
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr): void
	{
		$fileName = $phpcsFile->getFilename();
		$lines = preg_split('~(\\r\\n|\\r|\\n)~', file_get_contents($fileName));

		foreach ($lines as $i => $line) {
			if (!Strings::checkEncoding($line)) {
				$phpcsFile->addErrorOnLine('Not valid UTF-8 file', $i + 1, self::CODE_CONTAINS_INVALID_CHARACTERS);
			}
		}
	}

}
