<?php

declare(strict_types = 1);

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace KdybyCodingStandard\Sniffs\Strings;

use PHP_CodeSniffer_File;

class ValidDoubleQuotedStringSniff implements \PHP_CodeSniffer_Sniff
{

	public const CODE_INVALID_ESCAPE_SEQUENCE = 'InvalidEscapeSequence';

	/**
	 * @var bool
	 */
	public $strict = FALSE;

	/**
	 * @return int[]
	 */
	public function register(): array
	{
		return [
			T_ENCAPSED_AND_WHITESPACE,
			T_CONSTANT_ENCAPSED_STRING,
		];
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
		$tokens = $phpcsFile->getTokens();

		$token = $tokens[$stackPtr];
		$prev = array_key_exists($stackPtr - 1, $tokens) ? $tokens[$stackPtr - 1] : NULL;

		if (($token['code'] === T_ENCAPSED_AND_WHITESPACE && ($prev['code'] !== T_START_HEREDOC || !strpos($prev['content'], "'")))
			|| ($token['code'] === T_CONSTANT_ENCAPSED_STRING && $token['content'][0] === '"')
		) {
			$pattern = $this->strict
				? '#^([^\\\\]|\\\\[\\\\nrtvef$"x0-7])*+#'
				: '#^([^\\\\]|\\\\[\\\\nrtvefx0-7\W])*+#';

			if (preg_match($pattern, $token['content'], $m) && $token['content'] !== $m[0]) {
				$phpcsFile->addErrorOnLine(
					'Invalid escape sequence "%seq" in double quoted string',
					$token['line'],
					self::CODE_INVALID_ESCAPE_SEQUENCE,
					[
						'seq' => substr($token['content'], strlen($m[0]), 2),
					]
				);
			}
		}
	}

}
