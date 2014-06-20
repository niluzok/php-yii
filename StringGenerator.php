<?php

/**
 * Класс для генерации строки по шаблону.
 * В шаблон могут входить метки вида {placemark}, которые будут заменены определенными значениями
 * Так же в шаблоне могут быть необязательные чисти заключенные в треугольные скобки <>, если для метки
 * внутри необязательной части нет значения, то вся необязательная часть отбразывается.
 */
class StringGenerator extends CComponent
{
	/** @var Array Массив соответствий меток и значений, на которые они будут заменены */
	private $_substitutions;
	/** @var string Шаблон для генерации строки */
	private $_template;
	/** @var string Строка после генерации */
	private $_string;
	/** @var string Строка для результатов промежуточной обработки */
	private $_tempString;

	public function __construct($template = null)
	{
		if($template)
			$this->template = $template;

		$this->substitutions = array();
	}

	/**
	 * Устанавливает шаблон
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		$this->_template = $template;
		$this->_string = null;
		$this->_tempString = null;
	}

	/**
	 * Добавляем метку и значение, на которое ее необходимо заменить
	 * @param string $placeholder Метка вида {placeholder}, может состоять из букв, цифр и тире
	 * @param string $value       Строка на которую надо будет заменить метку
	 */
	public function setSubstitution($placeholder, $value)
	{
		$this->_substitutions[$placeholder] = (string)$value;
	}

	/**
	 * Функция для массового добавления меток и замен
	 * @param array $substitutions Массив соответствий меток и значения, на которые ихнужно заменить
	 */
	public function setSubstitutions(array $substitutions)
	{
		foreach ($substitutions as $placeholder => $value) {
			$this->setSubstitution($placeholder, $value);
		}
	}

	/**
	 * Очищает массив замен
	 */
	public function clearSubstitutions()
	{
		$this->_substitutions = null;
	}

	/**
	 * Производит замены меток на значения
	 */
	protected function makeSubstitutions()
	{
		$this->_tempString = strtr($this->_template, $this->_substitutions);
	}

	/**
	 * Удаляет необязательные части в которых остались не замененные метки
	 */
	protected function removeEmptyOptionalParts()
	{
		preg_match_all('/<[^>]+>/', $this->_tempString, $matches);
		
		foreach ($matches[0] as $optionalPart) {
			if(strpos($optionalPart, '{'))
				$this->_tempString = str_replace($optionalPart, '', $this->_tempString);
			else
				$this->_tempString = str_replace($optionalPart, trim($optionalPart, '<>'), $this->_tempString);
		}
	}

	/**
	 * Проверяет не остались ли не замененные метки, если да, то выбразывается исключение
	 */
	protected function checkNoPlaceholders()
	{
		if(preg_match_all('/\{[^}]+\}/', $this->_tempString, $matches))
			throw new StringGeneratorNoSubstitutionException($matches[0]);
	}

	/**
	 * Возвращает сгенерированную строку
	 * @return string
	 */
	public function getString()
	{
		if(!$this->_string) {
			$this->makeSubstitutions();
			$this->removeEmptyOptionalParts();
			$this->checkNoPlaceholders();
			$this->_string = $this->_tempString;
		}

		return $this->_string;
	}
}

/**
 * Исключение говорящее о том что не задана замена для какой-либо метки в шаблоне
 */
class StringGeneratorNoSubstitutionException extends CException {
	public function __construct($placeholders)
	{
		parent::__construct('Found no substitution for placeholders: '.implode(', ', $placeholders));
	}
}