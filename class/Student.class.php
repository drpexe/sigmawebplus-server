<?php
	defined('VALID_ENTRY_POINT') or die('');

	require_once SITE_ROOT.'class/StudentCourse.class.php';
	
	class Student
	{
		protected $name;
		protected $courses; 
		
		function __construct($kwargs=null)
		{
			if (is_array($kwargs))
			{
				foreach ($kwargs as $key => $value)
				{
					switch ($key)
					{
						case 'name':
							$this->setName($value);
							break;
						case 'courses':
							$this->setCourses($value);
							break;
					}
				}
			}
			elseif (!is_null($kwargs))
			{
				throw new StudentException('$kwargs must be an array or null');
			}
		}
	
		public function getName()
		{
			return $this->name;
		}
		
		protected function setName($value)
		{
			if (!is_string($value)) 
			{ 
				throw new StudentException('setName: must be a string');
			}
			$this->name = $value;
		}
		
		public function getCourses()
		{
			return $this->courses;
		}
		
		protected function setCourses($value)
		{
			if (is_array($value))
			{
				foreach ($value as $item)
				{
					if (!$item instanceof StudentCourse)
					{
						throw new StudentException('setCourses: must be an Array of instances of StudentCourse or null');
					}
				}
			}
			elseif (!is_null($value))
			{
				throw new StudentException('setCourses: must be an Array of instances of StudentCourse or null');
			}
			$this->courses = $value;
		}
		
		public function exportToXML()
		{
			$export = '<student name="'.$this->getName().'">';
			
			if (!is_null($this->getCourses()))
			{
				foreach ($this->getCourses() as $course)
				{
					$export .= $course->exportToXML();
				}
			}
			
			$export .= '</student>';
			return $export;
		}
	}
	
	class StudentException extends Exception {}
?>