<?php namespace Potsky\LaravelLocalizationHelpers\Factory;

interface TranslatorInterface
{
	public function translate( $word , $toLang , $fromLang = null );
}