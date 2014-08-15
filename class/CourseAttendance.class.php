<?php
	defined('VALID_ENTRY_POINT') or die('');

	class CourseAttendance
	{
		protected $classes;
		protected $presences;
		protected $absenses;
		
		function __construct($classes, $presences, $absenses)
		{
			if (!is_integer($classes)) { throw new Exception('$classes must be an Integer'); }
			if (!is_integer($presences)) { throw new Exception('$presences must be an Integer'); }
			if (!is_integer($absenses)) { throw new Exception('$absenses must be an Integer'); }
			
			if ($classes < 0) { throw new Exception('$classes must be positive'); }
			if ($presences < 0) { throw new Exception('$presences must be positive'); }
			if ($absenses < 0) { throw new Exception('$absences must be positive'); }
			
			if ($classes < ($presences+$absenses)) { throw new Exception('$classes must be greater or equal $presences + $absenses'); }
			
			$this->classes = $classes;
			$this->presences = $presences;
			$this->absenses = $absenses;
		}
		
		function getClasses()
		{
			return $this->classes;
		}
		
		function getPresences()
		{
			return $this->presences;
		}
		
		function getAbsenses()
		{
			return $this->absenses;
		}
		
		public function exportToXML()
		{
			$export = '<attendance classes="'.$this->getClasses().'" presences="'.$this->getPresences().'" absenses="'.$this->getAbsenses().'">';
			return $export;	
		}
	}