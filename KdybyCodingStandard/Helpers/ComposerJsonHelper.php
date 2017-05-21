<?php

declare(strict_types = 1);

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace KdybyCodingStandard\Helpers;

use SlevomatCodingStandard\Helpers\NamespaceHelper;

class ComposerJsonHelper
{

	public static function findClosestComposerJsonPath(string $phpFile): ?string
	{
		$cwd = realpath(getcwd());
		$dir = realpath($phpFile);
		do {
			$parent = dirname($dir);
			if ($parent === $dir) {
				return NULL; // filesystem root
			}

			$composerFile = $parent . DIRECTORY_SEPARATOR . 'composer.json';
			if (is_file($composerFile)) {
				return $composerFile;
			}
			$dir = $parent;

		} while ($parent !== $cwd); // do not escape working directory
	}

	/**
	 * @param string $composerFile
	 * @return string[][]
	 */
	public static function loadPsrAutoLoaderDefinition(string $composerFile): array
	{
		$composer = json_decode(file_get_contents($composerFile), TRUE);

		$def = [];
		if (array_key_exists('autoload', $composer)) {
			$def = array_merge_recursive($def, $composer['autoload']);
		}
		if (array_key_exists('autoload-dev', $composer)) {
			$def = array_merge_recursive($def, $composer['autoload-dev']);
		}

		return $def;
	}

	/**
	 * @return string[] path(string) => namespace
	 */
	public static function getComposerAutoLoadingPairs(string $phpFile): array
	{
		$composerFile = self::findClosestComposerJsonPath($phpFile);
		if ($composerFile === NULL) {
			return [];
		}

		$prefix = self::getComposerAutoLoadDirectoriesPrefix($composerFile, getcwd());

		$autoLoading = self::loadPsrAutoLoaderDefinition($composerFile);
		$rootNamespaces = [];
		foreach ($autoLoading['psr-0'] ?? [] as $namespace => $paths) {
			$pathsList = is_array($paths) ? $paths : [$paths];
			foreach ($pathsList as $path) {
				$normalizedNamespace = self::normalizeNamespace($namespace);
				$namespaceDirectory = str_replace(NamespaceHelper::NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, $normalizedNamespace);
				$rootNamespaces[$prefix . trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $namespaceDirectory] = $normalizedNamespace;
			}
		}
		foreach ($autoLoading['psr-4'] ?? [] as $namespace => $paths) {
			$pathsList = is_array($paths) ? $paths : [$paths];
			foreach ($pathsList as $path) {
				$rootNamespaces[$prefix . trim($path, DIRECTORY_SEPARATOR)] = self::normalizeNamespace($namespace);
			}
		}

		return $rootNamespaces;
	}

	public static function getComposerAutoLoadDirectoriesPrefix(string $composerFile, string $cwd): string
	{
		$projectDir = realpath(dirname($composerFile));
		$cwd = realpath($cwd);
		if ($projectDir === $cwd) {
			return '';
		}

		$prefix = '';
		do {
			$prefix = basename($projectDir) . DIRECTORY_SEPARATOR . $prefix;
			$projectDir = dirname($projectDir);
		} while ($projectDir !== $cwd);

		return $prefix;
	}

	private static function normalizeNamespace(string $namespace): string
	{
		return rtrim(NamespaceHelper::normalizeToCanonicalName($namespace), NamespaceHelper::NAMESPACE_SEPARATOR);
	}

}
