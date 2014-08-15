<?php
	defined('VALID_ENTRY_POINT') or die('');

	class CourseGrade
	{
		protected $description;
		protected $weight;
		protected $value;
		
		function __constructor($description, $weight, $value)
		{
			if (!is_string($description)) { throw new Exception('$description must be a String'); }
			if (!is_integer($weight) && !is_null($weight)) { throw new Exception('$weight must be an Integer or null'); }
			if (!is_integer($value) && !is_null($value)) { throw new Exception('$value must be an Integer or null'); }
			
			if ($weight < 0 || $weight > 100) { throw new Exception('$weight must be between 0 and 100'); }
			if ($value < 0 || $value > 100) { throw new Exception('$value must be between 0 and 100'); }
			
			$this->description = $description;
			$this->weight = $weight;
			$this->value = $value;
		}
		
		public function getDescription()
		{
			return $this->description;
		}
		
		public function getWeight()
		{
			return $this->weight;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		public function exportToXML()
		{
				
		}
	}