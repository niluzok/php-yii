<?php

/**
 * Класс, представляющий список чисел.
 * Оснавная задача получать и выдавать список чисел в удобном для восприятия человеком виде
 * Например: 1-5, 7, 8, 10-12
 */
class NumbersList extends CList
{
	public function __construct($data=null, $readOnly = false)
	{
		if($data !== null) {
			if(is_string($data))
				$data = self::convertRangesStringToArray($data);
			elseif(is_array($data)) {
				foreach ($data as $item) {
					if((string)(int)$item !== (string)$item)
						throw new CException('Массив должен содержать только цифры');
				}
			}
			else
				throw new CException('Переданы данные неправильного формата');
		}

		parent::__construct($data, $readOnly);

	}

	public function __toString()
	{
		return $this->getRangesString();
	}

	/**
	 * Возвращает строку чисел, в которой последовательные цифры заменены
	 * диапазонами через тире
	 * Пример: array(1,2,3,4,5,7,8,10,11,12) => "1-5, 7, 8, 10-12"
	 * 
	 * @return String
	 */
	public function getRangesString()
	{	$numbersArr = $this->toArray();
		sort($numbersArr);

		$ranges = array();
		$startNumber = $endNumber = -1;

		for($i=0; $i < count($numbersArr); $i++) { 
			if($numbersArr[$i]>$endNumber) $startNumber = $numbersArr[$i];
			if(isset($numbersArr[$i+1]) and ($numbersArr[$i+1] == $numbersArr[$i] + 1)) {
				$endNumber = $numbersArr[$i+1];
				continue;
			} else {
				$endNumber = $numbersArr[$i];
			}

			if($startNumber == $endNumber)
				$ranges[] = $startNumber;
			elseif($endNumber-$startNumber == 1) {
				$ranges[] = $startNumber;
				$ranges[] = $endNumber;
			} else {
				$ranges[] = $startNumber.'-'.$endNumber;
			}
		}

		return count($ranges)>0 ? implode(', ', $ranges) : null;
	}

	/**
	 * Преобразует строку с числами и диапазонами в массив чисел
	 * Пример: "1-5, 7, 8, 10-12" => array(1,2,3,4,5,7,8,10,11,12)
	 * 
	 * @param  String $rangesString Строка с диапазонами
	 * @return Array
	 */
	private static function convertRangesStringToArray($rangesString)
	{
		$rangeParts = preg_split('/\s*,\s*|\s+/', $rangesString);
		$rangeParts = array_filter($rangeParts, function($item){
			$item = trim($item);
			return !empty($item);
		});

		$numbers = array();
		foreach ($rangeParts as $range) {
			// Если это просто число(не диапазон с дефисом)
			if((string)(int)$range === $range) {
				$numbers[] = (int)$range;
			} else if(preg_match('/\d+-\d+/', $range)) {
				list($startNumber, $endNumber) = explode('-', $range);
				$numbersRangeArr = range((int)$startNumber, (int)$endNumber);
				foreach ($numbersRangeArr as $number) {
					$numbers[] = (int)$number;
				}
			} else {
				throw new CException('Не правильная строка с диапазоном: "'.$range.'"');
			}
		}

		return $numbers;
	}

	public function isEmpty()
	{
		return $this->count == 0;
	}
}