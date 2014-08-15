<?php
	defined('VALID_ENTRY_POINT') or die('');

	require_once SITE_ROOT.'class/CourseGrade.class.php';
	require_once SITE_ROOT.'class/CourseAttendance.class.php';

	class StudentCourse
	{
		protected $name;
		protected $code;
		protected $grades;
		protected $attendance;
		
		function __construct($name, $code, $grades, $attendance)
		{
			if (!is_string($name)) { throw new Exception('$name must be a String'); }
			if (!is_string($code)) { throw new Exception('$code must be a String'); }
			
			if (is_array($grades))
			{
				foreach ($grades as $grade)
				{
					if (!$grade instanceof CourseGrade)
					{
						throw new Exception('$grades must be an Array of instances of CourseGrade');
					}
				}
			}
			elseif (!is_null($grades))
			{
				throw new Exception('$grades must be an Array or null');
			}
			
			if (!$attendance instanceof CourseAttendance)
			{
				throw new Exception('$attendance must be an instance of CourseAttendance');
			}
			
			$this->name = $name;
			$this->code = $code;
			$this->grades = $grades;
			$this->attendance = $attendance;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getCode()
		{
			return $this->code;
		}
		
		public function getGrades()
		{
			return $this->grades;
		}
		
		public function getAttendance()
		{
			return $this->attendance;
		}
		
		public function exportToXML()
		{
			$export = '<course name="'.$this->getName().'" code="'.$this->getCode().'">';
			$export .= $this->getAttendance()->exportToXML();
			
			if (!is_null($this->getGrades()))
			{
				foreach ($this->getGrades() as $grade)
				{
					$export .= $grade->exportToXML();
				}
			}
			
			$export .= '</course>';
			return $export;
				
		}
	}
?>